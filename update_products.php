<?php

/**
 * API para actualizar productos para una tienda OpenCart
 * En este caso se probó con el proveedor de servicios Fesh
 */
echo 'date start: '. date('Y-m-d H:i:s') . PHP_EOL;

// define('AASASOFT_LOGS_WEBSERVICE_ENABLED', 'true');
require 'class.ws_dispath.php';
require 'classes/class.ws_products.php';
require 'classes/class.aasasoft_request_factory.php';

$aasasoft_products = new aasasoft_products();
// Tkn=9C00BA3DA9EF47998863E9CC8B8A0B99&Auth=B6E4743F920D41139D14F059B31CC1F4&Articulo=RAMNDMN105
$ws_request = array(
    'Tkn' => '9C00BA3DA9EF47998863E9CC8B8A0B99',
    'Auth' => 'B6E4743F920D41139D14F059B31CC1F4'
);
// ruta en la cual se van a guardar las imagenes de productos
$products_layout_fesh = array();

echo 'get_all_stock_service...'. PHP_EOL;
$ws_response = $aasasoft_products->get_all_stock_service($ws_request);
if (isset($ws_response['Articulos'])) {
    foreach($ws_response['Articulos'] as $articulos){
        $ws_products_status = ($articulos['Disponible'] == 'Si' ? '1' : '0');
        $ws_products_code = rtrim($articulos['Codigo']);
        $products_layout_fesh[$ws_products_code] = array(
            'model' => $ws_products_code,
            'status' => $ws_products_status,
            'stock_status_id' => ($ws_products_status == '1' ? '7' : '5'), // 7= in stock 5 = out of stock
            'tax_class_id' => '1',
        );
    }
}
else{
    echo 'get_all_stock_service: error al consultar.'. PHP_EOL;
    exit();
}
echo 'get_all_prices_service...'. PHP_EOL;
$ws_response = $aasasoft_products->get_all_prices_service($ws_request);
if (isset($ws_response['Articulos'])) {
    foreach($ws_response['Articulos'] as $articulos){
        $ws_products_code = rtrim($articulos['Codigo']);
            // obtener primero precio con descuento
        $ws_products_price = (float) $articulos['PrecioDistribuidor'];

        // si lo asignamos al archivo de carga, pero con estatus desactivado
        /*
        if ($ws_products_price < 500) {
            $ws_products_status = '0';
            $products_layout_fesh[$ws_products_code]['status'] = $ws_products_status;
            $products_layout_fesh[$ws_products_code]['stock_status_id'] = ($ws_products_status == '1' ? '7' : '5'); // 7= in stock 5 = out of stock
        }
        */

        // aumentar el precio de venta
        $ws_products_price = update_product_price($ws_products_price);
        // quitar valor de IVA incluido en el precio
        $ws_products_price = $ws_products_price / 1.16;
        $products_layout_fesh[$ws_products_code]['price'] = $ws_products_price;
    }
}
else{
    echo 'get_all_prices_service: error al consultar.'. PHP_EOL;
    exit();
}

if(!empty($products_layout_fesh)){
    echo "listo para enviar. total: " . count($products_layout_fesh) . PHP_EOL;

    $url = 'https://coyotesmusicshop.com.mx/force404';

    $get_params = array(
        "update_products_model" => "1"
    );

    $headers = array(
        "Accept: application/json",
        "Content-Type: multipart/form-data",
    );
    $post_params = array();
    $step = 2000;
    // enviar los datos recabados de 100 en 100
    foreach($products_layout_fesh as $key=> $product){
        $post_params[] = $product;
        if(count($post_params) % $step == 0){
            $response = curl_sender($url, $get_params, json_encode($post_params), $headers);
            echo $response . PHP_EOL;
            $post_params = array();
        }
    }
    if(count($post_params) > 0){
        $response = curl_sender($url, $get_params, json_encode($post_params), $headers);
        echo $response . PHP_EOL;
    }
    // echo $response . PHP_EOL;
    echo 'date end: '. date('Y-m-d H:i:s') . PHP_EOL;

}

/*
    Aumentar precio deacuerdo al valor del precio de gonher:
    porcentaje_articulo = 
        precio_gonher < 500: 100%
        precio_gonher > 500 o < 2000: 40%
        precio_gonher > 2000 o < 5000: 30%
        precio_gonher > 5000 o < 10000: 25%
        precio_gonher > 10000 o < 20000: 15%
        precio_gonher > 20000: 10%
    ;
    variable = precio_gonher * porcentaje_articulo
    precio_final = precio_gonher + variable
 *
 * @param float $product_price
 * @return float
 */
function update_product_price($product_price)
{
    $product_price_updated = $product_price;
    switch (true) {
        case $product_price < 500:
            $product_price_updated = $product_price * 2;
            break;
        case $product_price >= 500 && $product_price <= 2000:
            $product_price_updated = $product_price * 1.4;
            break;
        case $product_price > 2000 && $product_price <= 5000:
            $product_price_updated = $product_price * 1.3;
            break;
        case $product_price > 5000 && $product_price <= 10000:
            $product_price_updated = $product_price * 1.25;
            break;
        case $product_price > 10000 && $product_price <= 20000:
            $product_price_updated = $product_price * 1.15;
            break;
        case $product_price > 20000:
            $product_price_updated = $product_price * 1.10;
            break;
    }
    return $product_price_updated;
}

function curl_sender($url, $get_params, $post_params, $headers)
{
    if(!empty($get_params)){
        $query = http_build_query($get_params);
        $query = preg_replace('/%5B[0-9]+%5D/simU', '', $query);
        $url .= '?' . $query;
    }

    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => $post_params,
        CURLOPT_HTTPHEADER => $headers,
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    return $response;
}


/*
cobrar envio en productos menores a 500, se cobra 100 de envio;
propuesta: ventas por comision en la pagina web al empleado de puga
o darle $500 por semana por x ventas semanales y administracion y redes sociales (banners, promociones)

*/
