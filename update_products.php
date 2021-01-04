<?php

/**
 * API para actualizar productos para una tienda OpenCart
 * En este caso se probó con el proveedor de servicios Fesh
 */

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

// obtenemos listado de modelos de productos de la tienda fesh
$csv_file = file_get_contents('https://coyotesmusicshop.com.mx/force404?get_products_model=1');
// test:
// $csv_file = demo_csv_file();
$csv_array = str_getcsv($csv_file, PHP_EOL);

foreach ($csv_array as $key => $_model){

    $ws_products_code = rtrim($_model);

    $ws_request['Articulo'] = $ws_products_code;

    $ws_products_stock_prices_response = $aasasoft_products->get_stock_prices_service($ws_request);

    // si no regresa exito el webservice, no proseguimos con la creación de archivos
    if ($ws_products_stock_prices_response['result'] != 'OK') {
        echo 'get_stock_prices_service: error al consultar codigo ' . $ws_products_code . PHP_EOL;
        continue;
    }
    // imprimimos cada iteracion
    echo $ws_products_stock_prices_response['Codigo'] . ' ';
    echo $ws_products_stock_prices_response['Precio_con_descuentos'] . PHP_EOL;

    // obtenemos estatus del producto
    $ws_products_status = ($ws_products_stock_prices_response['Disponible'] == 'Si' ? '1' : '0');

    // obtener primero precio con descuento
    $ws_products_price = (float) $ws_products_stock_prices_response['Precio_con_descuentos'];
    if ($ws_products_price < 0) {
        // si no existe entonces tomar Precio_distribuidor
        $ws_products_price = (float) $ws_products_stock_prices_response['Precio_distribuidor'];
    }

    // limitar a que solo se generen productos con precio mayor o igual a $500
    // if ($ws_products_price < 500) {
    //     continue;
    // }
    // si lo asignamos al archivo de carga, pero con estatus desactivado
    if ($ws_products_price < 500) {
        $ws_products_status = 'false';
    }

    // aumentar el precio de venta
    $ws_products_price = update_product_price($ws_products_price);
    // quitar valor de IVA incluido en el precio
    $ws_products_price = $ws_products_price / 1.16;

    /*
    // test
    $ws_products_code = 'model ' . $key;
    $ws_products_price = 'price ' . $key;
    $ws_products_status = 'status ' . $key;
    */
    $products_layout_fesh[$key] = json_encode(array(
        'model' => $ws_products_code,
        'price' => $ws_products_price,
        'status' => $ws_products_status,
        'stock_status_id' => ($ws_products_status == 'true' ? '7' : '5'), // 7= in stock 5 = out of stock
        'tax_class_id' => '1',
    ));

}

if(!empty($products_layout_fesh)){
    $url = 'https://coyotesmusicshop.com.mx/force404';

    $get_params = array(
        "update_products_model" => "1"
    );

    $headers = array(
        "Accept: application/json",
        "Content-Type: multipart/form-data",
    );
    $post_params = array();
    $step = 100;
    // enviar los datos recabados de 100 en 100
    echo "total: " . count($products_layout_fesh) . PHP_EOL;
    foreach($products_layout_fesh as $key=> $product){
        $post_params[] = $product;
        if(count($post_params) % $step == 0){
            $response = curl_sender($url, $get_params, $post_params, $headers);
            echo $response . PHP_EOL;
            $post_params = array();
        }
    }
    if(count($post_params) > 0){
        $response = curl_sender($url, $get_params, $post_params, $headers);
        echo $response . PHP_EOL;
    }
}

/**
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
 * @param [float] $produt_price
 * @return float
 */
function update_product_price($produt_price)
{
    $produt_price_updated = $produt_price;
    switch (true) {
        case $produt_price < 500:
            $produt_price_updated = $produt_price * 2;
            break;
        case $produt_price >= 500 && $produt_price <= 2000:
            $produt_price_updated = $produt_price * 1.4;
            break;
        case $produt_price > 2000 && $produt_price <= 5000:
            $produt_price_updated = $produt_price * 1.3;
            break;
        case $produt_price > 5000 && $produt_price <= 10000:
            $produt_price_updated = $produt_price * 1.25;
            break;
        case $produt_price > 10000 && $produt_price <= 20000:
            $produt_price_updated = $produt_price * 1.15;
            break;
        case $produt_price > 20000:
            $produt_price_updated = $produt_price * 1.10;
            break;
    }
    return $produt_price_updated;
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

function demo_csv_file(){
    return
"ISFLIDUS460AMA
ISFLIDUS430DAO
ISFLIDUS445ACA
ISFLIDUS320
ISFLIDUS322ZEZ
ISFLIDUS440KOA
ISFLITUSEESUNS
ISFLIDUS330REL
ISFLIDUS371MAH
ISFLIDUS410QAQ
ISFLIDUS445KOA
ISFLIMUS2
ISFLINUP310";
}