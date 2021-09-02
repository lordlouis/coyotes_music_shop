<?php

/**
 * API para generar archivo de carga masiva para una tienda OpenCart
 * En este caso se probó con el proveedor de servicios Fesh
 */

require 'vendor/autoload.php';
require 'class.ws_dispath.php';
require 'classes/class.ws_products.php';
require 'classes/class.aasasoft_request_factory.php';


use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader\Xls;


$aasasoft_products = new aasasoft_products();
// Tkn=9C00BA3DA9EF47998863E9CC8B8A0B99&Auth=B6E4743F920D41139D14F059B31CC1F4&Articulo=RAMNDMN105
$ws_request = array(
    'Tkn' => '9C00BA3DA9EF47998863E9CC8B8A0B99',
    'Auth' => 'B6E4743F920D41139D14F059B31CC1F4'
);
// ruta en la cual se van a guardar las imagenes de productos
$products_image_path_fesh = 'catalog/product/';
$products_layout_fesh = [
    [
        'product_id' => 'product_id',
        'name(es-es)' => 'name(es-es)',
        'categories' => 'categories',
        'sku' => 'sku',
        'upc' => 'upc',
        'ean' => 'ean',
        'jan' => 'jan',
        'isbn' => 'isbn',
        'mpn' => 'mpn',
        'location' => 'location',
        'quantity' => 'quantity',
        'model' => 'model',
        'manufacturer' => 'manufacturer',
        // 'image_url' => 'image_url',
        'image_name' => 'image_name',
        'shipping' => 'shipping',
        'price' => 'price',
        'points' => 'points',
        'date_added' => 'date_added',
        'date_modified' => 'date_modified',
        'date_available' => 'date_available',
        'weight' => 'weight',
        'weight_unit' => 'weight_unit',
        'length' => 'length',
        'width' => 'width',
        'height' => 'height',
        'length_unit' => 'length_unit',
        'status' => 'status',
        'tax_class_id' => 'tax_class_id',
        'seo_keyword' => 'seo_keyword',
        'description(es-es)' => 'description(es-es)',
        'meta_title(es-es)' => 'meta_title(es-es)',
        'meta_description(es-es)' => 'meta_description(es-es)',
        'meta_keywords(es-es)' => 'meta_keywords(es-es)',
        'stock_status_id' => 'stock_status_id',
        'store_ids' => 'store_ids',
        'layout' => 'layout',
        'related_ids' => 'related_ids',
        'tags(es-es)' => 'tags(es-es)',
        'sort_order' => 'sort_order',
        'subtract' => 'subtract',
        'minimum' => 'minimum'
    ],
];

// categorias
$categories_layout_fesh = array();
$categories_layout_fesh_header[] = array(
    'category_id' => 'category_id',
    'parent_id' => 'parent_id',
    'name(es-es)' => 'name(es-es)',
    'top' => 'top',
    'columns' => 'columns',
    'sort_order' => 'sort_order',
    'image_name' => 'image_name',
    'date_added' => 'date_added',
    'date_modified' => 'date_modified',
    'seo_keyword' => 'seo_keyword',
    'description(es-es)' => 'description(es-es)',
    'meta_title(es-es)' => 'meta_title(es-es)',
    'meta_description(es-es)' => 'meta_description(es-es)',
    'meta_keywords(es-es)' => 'meta_keywords(es-es)',
    'store_ids' => 'store_ids',
    'layout' => 'layout',
    'status' => 'status',
);

// multiples imagenes de producto
$products_images_layout_fesh = array();
$products_images_layout_fesh_header[] = array(
    'product_id' => 'product_id',
    // 'image_url' => 'image_url',
    'image' => 'image',
    'sort_order' => 'sort_order',
);

// atributos de producto
$attribute_groups_layout_fesh = array();
$attribute_groups_layout_fesh_header[] = array(
    'attribute_group_id' => 'attribute_group_id',
    'sort_order' => 'sort_order',
    'name(es-es)' => 'name(es-es)',
);
$attributes_layout_fesh = array();
$attributes_layout_fesh_header[] = array(
    'attribute_id' => 'attribute_id',
    'attribute_group_id' => 'attribute_group_id',
    'sort_order' => 'sort_order',
    'name(es-es)' => 'name(es-es)',
);
$product_attributes_layout_fesh = array();
$product_attributes_layout_fesh_header[] = array(
    'product_id' => 'product_id',
    'attribute_group' => 'attribute_group',
    'attribute' => 'attribute',
    'text(es-es)' => 'text(es-es)',
);

// descarga de imagenes
$download_images = array();

// leemos el listado de productos del catalogo en excel de Gonher
// en el se debe agregar los productos de otras lisas de precios, como la de Inovaudio
// descargar el archivo desde https://www.grupogonher.mx/Distribuidores/Existencias
$reader = new Xls();
$spreadsheet = $reader->load("./ListaDePrecios.xls");
$worksheet = $spreadsheet->getActiveSheet();

// obtenemos listado de modelos de productos de la tienda fesh
$csv_file = file_get_contents('https://coyotesmusicshop.com.mx/force404?get_products_model=1');
// test:
// $csv_file = file_get_contents('get_products_model.txt');
$csv_array = str_getcsv($csv_file, PHP_EOL);
foreach ($csv_array as $key => &$_model){
    $_model = rtrim($_model);
}

// Get the highest row and column numbers referenced in the worksheet

$highestRow = $worksheet->getHighestRow(); // e.g. 10
// $highestRow = 30; // limitar los registros de Lista de precios Gonher

// contadores para iterar valores
$pl_coun = 2;

// para agregar nuevos productos, se aumenta el identificador
// $pl_coun = 2 + 4995;
$cl_coun = 2;
$pil_coun = 2;
$agl_coun = 2;
$al_coun = 2;
$pal_coun = 2;

for ($row = 2; $row <= $highestRow; ++$row) {
    $col = 1;
    $ws_products_code = $worksheet->getCellByColumnAndRow($col, $row)->getValue();

    // si el modelo ya existe en la tienda fesh_lo ignoramos
    if (in_array($ws_products_code, $csv_array)) {
        echo 'ya existe el codigo en la tienda, no se agrega al archivo generado ' . $ws_products_code . PHP_EOL;
        continue;
    }

    $ws_request['Articulo'] = $ws_products_code;

    // Obtencion de detalle de productos
    $ws_products_response = $aasasoft_products->get_products_service($ws_request);

    // $ws_products_response = get_products_service_demo($row);
    // si no regresa exito el webservice, no proseguimos con la creación de archivos
    if ($ws_products_response['result'] != 'OK') {
        echo 'get_products_service: error al consultar codigo ' . $ws_products_code . PHP_EOL;
        continue;
    }
    // imprimimos cada iteracion
    // echo $ws_products_response['Codigo'] . ' ';

    $ws_products_stock_prices_response = $aasasoft_products->get_stock_prices_service($ws_request);

    // llenar con datos dummy si no se quiere consumir el webservice:

    // $ws_products_stock_prices_response = array(
    //     'result' => 'OK',
    //     'Disponible' => 'Si',
    //     'Precio_con_descuentos' => '1',
    // );

    // si no regresa exito el webservice, no proseguimos con la creación de archivos
    if ($ws_products_stock_prices_response['result'] != 'OK') {
        echo 'get_stock_prices_service: error al consultar codigo ' . $ws_products_code . PHP_EOL;
        continue;
    }
    // imprimimos cada iteracion
    // echo $ws_products_stock_prices_response['Precio_con_descuentos'] . PHP_EOL;

    // obtenemos estatus del producto
    $ws_products_status = ($ws_products_stock_prices_response['Disponible'] == 'Si' ? 'true' : 'false');

    // obtener primero precio con descuento
    $ws_products_price = (float) $ws_products_stock_prices_response['Precio_con_descuentos'];
    if ($ws_products_price < 0){
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

    $category_size = count($ws_products_response['Categorias']);
    // si no contiene categorias el producto, lo asignamos a una categoria generica
    if ($category_size == 0) {
        $ws_products_response['Categorias'][] = array(
            'Id' => '1',
            'PadreId' => '0',
            'Categoria' => 'General',
        );
        $category_size = 1;
    }
    $category_index = $category_size;
    $category_deep_limit = 2;
    // limitar el arbol de categorias para que no descargue mas subcategorias
    if ($category_index > $category_deep_limit) {
        $category_index = $category_deep_limit;
    }
    $category_index = $category_index - 1;
    // poner el ultimo id categorias
    $category_id = $ws_products_response['Categorias'][$category_index]['Id']; // TODO: PHP Notice:  Undefined offset: -1 in /home/lgarcia/lagonezs/gonher_api_fesh.php on line 164
    // escritura de productos:
    $products_image = rtrim($ws_products_response['Imagen_principal']);
    $products_layout_fesh[$pl_coun]['product_id'] = $pl_coun - 1;
    $products_layout_fesh[$pl_coun]['name(es-es)'] = sanitize_name($ws_products_response['Nombre']);
    $products_layout_fesh[$pl_coun]['categories'] = $category_id;
    $products_layout_fesh[$pl_coun]['sku'] = $ws_products_response['Codigo'];
    $products_layout_fesh[$pl_coun]['upc'] = '';
    $products_layout_fesh[$pl_coun]['ean'] = '';
    $products_layout_fesh[$pl_coun]['jan'] = '';
    $products_layout_fesh[$pl_coun]['isbn'] = '';
    $products_layout_fesh[$pl_coun]['mpn'] = '';
    $products_layout_fesh[$pl_coun]['location'] = '';
    $products_layout_fesh[$pl_coun]['quantity'] = '10'; // para gonher se pone 10 existencias, ya que no nos regresan existencias
    $products_layout_fesh[$pl_coun]['model'] = $ws_products_response['Codigo'];
    $products_layout_fesh[$pl_coun]['manufacturer'] = $ws_products_response['Marca'];
    $products_layout_fesh[$pl_coun]['image_url'] = $products_image;
    // al subir las imagenes a fesh, las pone en minusculas
    // entonces de una vez actualizamos las imagenes de productos a minusculas
    // para evitar problemas, las imagenes descargadas se deben renombrar a minusculas
    $products_layout_fesh[$pl_coun]['image_name'] = $products_image_path_fesh . strtolower(basename($products_image));
    $products_layout_fesh[$pl_coun]['shipping'] = 'yes';
    $products_layout_fesh[$pl_coun]['price'] = $ws_products_price;
    $products_layout_fesh[$pl_coun]['points'] = '0';
    $products_layout_fesh[$pl_coun]['date_added'] = '';
    $products_layout_fesh[$pl_coun]['date_modified'] = '';
    $products_layout_fesh[$pl_coun]['date_available'] = '';
    $products_layout_fesh[$pl_coun]['weight'] = '0';
    $products_layout_fesh[$pl_coun]['weight_unit'] = 'g';
    $products_layout_fesh[$pl_coun]['length'] = '0';
    $products_layout_fesh[$pl_coun]['width'] = '0';
    $products_layout_fesh[$pl_coun]['height'] = '0';
    $products_layout_fesh[$pl_coun]['length_unit'] = 'cm';
    $products_layout_fesh[$pl_coun]['status'] = $ws_products_status;
    $products_layout_fesh[$pl_coun]['tax_class_id'] = '1';  // - los precios que regresa gonher ya traen iva en el precio (previamente se le quita),se agrega bandera de IVA
    $products_layout_fesh[$pl_coun]['seo_keyword'] = sanitize_name($ws_products_response['Nombre']);
    $products_layout_fesh[$pl_coun]['description(es-es)'] = $ws_products_response['Descripcion'];
    $products_layout_fesh[$pl_coun]['meta_title(es-es)'] = sanitize_name($ws_products_response['Nombre']);
    $products_layout_fesh[$pl_coun]['meta_description(es-es)'] = '';
    $products_layout_fesh[$pl_coun]['meta_keywords(es-es)'] = '';
    $products_layout_fesh[$pl_coun]['stock_status_id'] = ($ws_products_status == 'true' ? '7' : '5'); // 7= in stock 5 = out of stock
    $products_layout_fesh[$pl_coun]['store_ids'] = '0';
    $products_layout_fesh[$pl_coun]['layout'] = '';
    $products_layout_fesh[$pl_coun]['related_ids'] = '';
    $products_layout_fesh[$pl_coun]['tags(es-es)'] = '';
    $products_layout_fesh[$pl_coun]['sort_order'] = '0';
    $products_layout_fesh[$pl_coun]['subtract'] = 'false'; // para gonher no se restan existencias, ya que no nos regresan existencias
    $products_layout_fesh[$pl_coun]['minimum'] = (int)$ws_products_response['Multiplo']; // tomar valor de Multiplo

    // escritura de imagenes de productos:
    foreach ($ws_products_response['Imagenes_secundarias'] as $key => $additional_image) {
        $products_image = rtrim($additional_image);
        $products_images_layout_fesh[$pil_coun]['product_id'] = $products_layout_fesh[$pl_coun]['product_id'];
        $products_images_layout_fesh[$pil_coun]['image_url'] =  $products_image;
        // al subir las imagenes a fesh, las pone en minusculas
        // entonces de una vez actualizamos las imagenes de productos a minusculas
        // para evitar problemas, las imagenes descargadas se deben renombrar a minusculas
        $products_images_layout_fesh[$pil_coun]['image'] =  $products_image_path_fesh . strtolower(basename($products_image));
        $products_images_layout_fesh[$pil_coun]['sort_order'] = '0';
        // incrementa contador
        $pil_coun++;
    }
    // escritura de atributos de productos:
    foreach ($ws_products_response['Especs'] as $attributes) {
        // hay nombres y valores que NO vienen definidos, no se agregan valores vacios
        if($attributes['espec'] == "" || $attributes['valor'] == ""){
            continue;
        }
        // validar que no se agreguen atributos si esos valores vienen vacios

        // ponemos el nombre y Id de la categoria del producto como grupo de atributos
        $attribute_group = $ws_products_response['Categorias'][$category_index]['Categoria'];
        $attribute_group_key = strtolower($attribute_group);
        $attribute_group_id_array[$attribute_group_key] = $ws_products_response['Categorias'][$category_index]['Id'];
        $attribute_group_id = array_search($attribute_group_key, array_keys($attribute_group_id_array));
        $attribute_group_id = (int) $attribute_group_id + 1;

        // --- grupos de atributos ---
        // ignoramos nombres de grupos de atributos repetidos
        // if (!in_array($attribute_group, array_column($attribute_groups_layout_fesh, 'name(es-es)'))) {
        if (!in_array($attribute_group_id, array_column($attribute_groups_layout_fesh, 'attribute_group_id'))) {
            $attribute_groups_layout_fesh[$agl_coun]['attribute_group_id'] = $attribute_group_id;
            $attribute_groups_layout_fesh[$agl_coun]['sort_order'] = '0';
            $attribute_groups_layout_fesh[$agl_coun]['name(es-es)'] = $attribute_group;
            $agl_coun++;
        }

        // --- atributos ---
        if (!in_array($attributes['espec'] . $attribute_group, array_column($attributes_layout_fesh, 'tmp_attribute_group_name'))) {
            $attributes_layout_fesh[$al_coun]['attribute_id'] = $al_coun;
            $attributes_layout_fesh[$al_coun]['attribute_group_id'] = $attribute_group_id;
            $attributes_layout_fesh[$al_coun]['sort_order'] = '0';
            $attributes_layout_fesh[$al_coun]['name(es-es)'] = $attributes['espec'];
            $attributes_layout_fesh[$al_coun]['tmp_attribute_group_name'] = $attributes['espec'] . $attribute_group;
            $al_coun++;
        }

        // --- relacionar atributos a productos ---
        $product_attributes_layout_fesh[$pal_coun]['product_id'] = $products_layout_fesh[$pl_coun]['product_id'];
        $product_attributes_layout_fesh[$pal_coun]['attribute_group'] = $attribute_group;
        $product_attributes_layout_fesh[$pal_coun]['attribute'] = $attributes['espec'];
        $product_attributes_layout_fesh[$pal_coun]['text(es-es)'] = sanitize_attribute_name($attributes['valor']);
        // incrementa contador
        $pal_coun++;
    }
    // escritura de categorias:
    foreach ($ws_products_response['Categorias'] as $key => $categoria) {
        if (!in_array($categoria['Id'], array_column($categories_layout_fesh, 'category_id'))) {
            // ignorar las siguientes categorias padre:
            if ($key == 0 && $categoria['Categoria'] == 'Exhibidores') {
                continue;
            }
            // limitar el arbol de categorias
            if ($key == $category_deep_limit) {
                continue;
            }
            $categories_layout_fesh[$cl_coun]['category_id'] = $categoria['Id'];
            $categories_layout_fesh[$cl_coun]['parent_id'] = $categoria['PadreId'];
            $categories_layout_fesh[$cl_coun]['name(es-es)'] = $categoria['Categoria'];
            $categories_layout_fesh[$cl_coun]['top'] = 'true';
            $categories_layout_fesh[$cl_coun]['columns'] = '0';
            $categories_layout_fesh[$cl_coun]['sort_order'] = '0';
            $categories_layout_fesh[$cl_coun]['image_name'] = '';
            $categories_layout_fesh[$cl_coun]['date_added'] = '';
            $categories_layout_fesh[$cl_coun]['date_modified'] = '';
            $categories_layout_fesh[$cl_coun]['seo_keyword'] = $categoria['Categoria'];
            $categories_layout_fesh[$cl_coun]['description(es-es)'] = '';
            $categories_layout_fesh[$cl_coun]['meta_title(es-es)'] = $categoria['Categoria'];
            $categories_layout_fesh[$cl_coun]['meta_description(es-es)'] = '';
            $categories_layout_fesh[$cl_coun]['meta_keywords(es-es)'] = '';
            $categories_layout_fesh[$cl_coun]['store_ids'] = '0';
            $categories_layout_fesh[$cl_coun]['layout'] = '';
            $categories_layout_fesh[$cl_coun]['status'] = 'true';
            // incrementa contador
            $cl_coun++;
        }
    }
    // incrementa contador
    $pl_coun++;
}
// eliminar columna temporal de products_layout_fesh y agregarla a download_images
foreach ($products_layout_fesh as $key => $subArr) {
    if (isset($subArr['image_url'])) {
        $download_images[] = $subArr['image_url'];
        unset($subArr['image_url']);
        $products_layout_fesh[$key] = $subArr;
    }
}

// eliminar columna temporal de products_images_layout_fesh y agregarla a download_images
foreach ($products_images_layout_fesh as $key => $subArr) {
    if (isset($subArr['image_url'])) {
        $download_images[] = $subArr['image_url'];
        unset($subArr['image_url']);
        $products_images_layout_fesh[$key] = $subArr;
    }
}

// eliminar columna temporal de attributes_layout_fesh
foreach ($attributes_layout_fesh as $key => $subArr) {
    unset($subArr['tmp_attribute_group_name']);
    $attributes_layout_fesh[$key] = $subArr;
}

// ordenar categorias por id
usort($categories_layout_fesh, function ($a, $b) {
    return $a['category_id'] <=> $b['category_id'];
});
// ordenar atributos por id
usort($attribute_groups_layout_fesh, function ($a, $b) {
    return $a['attribute_group_id'] <=> $b['attribute_group_id'];
});
usort($attributes_layout_fesh, function ($a, $b) {
    return $a['attribute_id'] <=> $b['attribute_id'];
});

// poner headers
$categories_layout_fesh = array_merge($categories_layout_fesh_header, $categories_layout_fesh);
$products_images_layout_fesh = array_merge($products_images_layout_fesh_header, $products_images_layout_fesh);
$attribute_groups_layout_fesh = array_merge($attribute_groups_layout_fesh_header, $attribute_groups_layout_fesh);
$attributes_layout_fesh = array_merge($attributes_layout_fesh_header, $attributes_layout_fesh);
$product_attributes_layout_fesh = array_merge($product_attributes_layout_fesh_header, $product_attributes_layout_fesh);

// archivo products

$spreadsheet_write = new Spreadsheet();
// crear la pestaña Categories
$spreadsheet_write->createSheet();
// crear la pestaña AdditionalImages
$spreadsheet_write->createSheet();
// crear la pestaña ProductAttributes
$spreadsheet_write->createSheet();

$spreadsheet_write
    ->getSheet(0)
    ->setTitle('Products') // poner titulo a la hoja
    ->fromArray(
        $products_layout_fesh,  // The data to set
        NULL,        // Array values with this value will not be set
        'A1'         // Top left coordinate of the worksheet range where
        //    we want to set these values (default is A1)
    );

$spreadsheet_write
    ->getSheet(1)
    ->setTitle('Categories') // poner titulo a la hoja
    ->fromArray(
        $categories_layout_fesh,  // The data to set
        NULL,        // Array values with this value will not be set
        'A1'         // Top left coordinate of the worksheet range where
        //    we want to set these values (default is A1)
    );

$spreadsheet_write
    ->getSheet(2)
    ->setTitle('AdditionalImages') // poner titulo a la hoja
    ->fromArray(
        $products_images_layout_fesh,  // The data to set
        NULL,        // Array values with this value will not be set
        'A1'         // Top left coordinate of the worksheet range where
        //    we want to set these values (default is A1)
    );

$spreadsheet_write
    ->getSheet(3)
    ->setTitle('ProductAttributes') // poner titulo a la hoja
    ->fromArray(
        $product_attributes_layout_fesh,  // The data to set
        NULL,        // Array values with this value will not be set
        'A1'         // Top left coordinate of the worksheet range where
        //    we want to set these values (default is A1)
    );

$writer = new Xlsx($spreadsheet_write);
$writer->save('products.xlsx');

// archivo attributes

$spreadsheet_write = new Spreadsheet();
// crear la pestaña Attributes
$spreadsheet_write->createSheet();

$spreadsheet_write
    ->getSheet(0)
    ->setTitle('AttributeGroups') // poner titulo a la hoja
    ->fromArray(
        $attribute_groups_layout_fesh,  // The data to set
        NULL,        // Array values with this value will not be set
        'A1'         // Top left coordinate of the worksheet range where
        //    we want to set these values (default is A1)
    );

$spreadsheet_write
    ->getSheet(1)
    ->setTitle('Attributes') // poner titulo a la hoja
    ->fromArray(
        $attributes_layout_fesh,  // The data to set
        NULL,        // Array values with this value will not be set
        'A1'         // Top left coordinate of the worksheet range where
        //    we want to set these values (default is A1)
    );

$writer = new Xlsx($spreadsheet_write);
$writer->save('attributes.xlsx');

// archivo download_images

$fp = fopen("download_images.txt", "w+");
foreach ($download_images as $content) {
    $filename = $content;
    fwrite($fp, $filename . PHP_EOL);
}
fclose($fp);

/**
 * funcion para sanitizar el valor del nombre de producto
 *
 * @param [type] $text
 * @return void
 */
function sanitize_name($text)
{
    // sustituir diagonales por guion medio
    $text = str_replace('/', '-', $text);
    // quitar multiples espacios
    $text = preg_replace('/\s+/', ' ', $text);
    return $text;
}

function sanitize_attribute_name($text)
{
    // quitar multiples espacios
    $text = preg_replace('/\s+/', ' ', $text);
    return $text;
}

/**
 * funcion para sanitizar el nombre de la imagen a guardar
 *
 * @param [type] $text
 * @return void
 */
function sanitize_image_name($text)
{
    // $text = str_replace('$', '-', $text);
    return $text;
}

/**
 * Generamos un json de ejemplo, para no tener que consultar el webservice
 *
 * @param [int] $row
 * @return array
 */
function get_products_service_demo($row)
{
    $json_text = '{"result":"OK", "Codigo":"ISADMSARAEC", "Nombre":"GUITARRA ADMIRA  E/ACUSTICA SARA-EC","Marca":"ADMIRA", "Descripcion_corta":"Guitarra electro acústica con caja clásica, serie: Junior, tapa de pino de Oregón, aros y fondo de nogal, brazo de caoba africana, diapasón y puente de palo santo, escala: 650 mm, ecualizador de 4 bandas, resaque." ,"Imagen principal":"http://201.107.4.57/Archivo/Articulo/ISADMSARAEC.jpg" ,"Imagen_principal":"http://201.107.4.57/Archivo/Articulo/ISADMSARAEC.jpg" ,"Empresa_propietaria":"gonher" , "Completo_web":"SI" ,"Descripcion":"<div style=\"text-align: justify; list-style: circle;\"><p><div><h3>La mejor para iniciarse en la música.</h3></div><div>La guitarra clásica Sara-EC de Admira incluye una tapa de pino de Oregón, aros y fondo de nogal, brazo de caoba africana, diapasón y puente de palo santo, resaque y preamplificador con ecualizador de 4 bandas y es ideal para estudiantes. Una bella pieza fabricada a mano y con gran calidad sonora. Con la guitarra clásica Sara-EC tendrás en tus manos fineza extraordinaria, resonancia natural y toda la tradición en la fabricación de guitarras clásicas. Obtendrás un tono cálido y percusivo. Es ideal para baladas, obras hechas para guitarra clásica, boleros, flamenco  y música del mundo. Sara-EC está pensada para tener una buena práctica, es importante tener una buena guitarra desde el inicio de nuestra educación musical, adquirirás toda la calidad y durabilidad que estás buscando. Fundada a finales de 1944 por Enrique Keller Fritsch, Admira ocupa hoy en día un puesto destacado entre las empresas españolas dedicadas a la fabricación de instrumentos musicales. La guitarra clásica española tiene su futuro asegurado gracias a la labor diaria de un centenar de hombres y mujeres que trabajan para  la existencia de estas piezas artesanales.</div><div><h4>Vistazo rapido</h4></div><ul style=\"margin: 0px 0px 0px 25px;\"><li style=\"list-style-type: circle; margin: 5px;\">Serie.</li><li style=\"list-style-type: circle; margin: 5px;\">Diseño tradicional.</li><li style=\"list-style-type: circle; margin: 5px;\">Tapa de pino de Oregón.</li><li style=\"list-style-type: circle; margin: 5px;\">Aros y fondo de nogal.</li><li style=\"list-style-type: circle; margin: 5px;\">Brazo de caoba africana.</li><li style=\"list-style-type: circle; margin: 5px;\">Diapasón y puente de palosanto.</li><li style=\"list-style-type: circle; margin: 5px;\">Escala.</li></ul><div><div style=\"font-weight: bold;\">Serie.</div><div><p>Junior.</p></div><div style=\"font-weight: bold;\">Diseño tradicional.</div><div><p>Está pensada para músicos clásicos, pero su gran equilibrio entre claridad y calidez la hace atractiva para muchos otros, además de la comodidad en su cuerpo tienes un sonido amplificado acorde por acorde sin perder la fidelidad que caracteriza a las guitarras Admira.  </p></div><div style=\"font-weight: bold;\">Tapa de pino de Oregón.</div><div><p>El pino de Oregón es una madera con veta pareja que cuenta con grandes propiedades de resonancia.</p></div><div style=\"font-weight: bold;\">Aros y fondo de nogal.</div><div><p>El nogal es una madera oscura excelente para la construcción de instrumentos musicales por su dureza y resistencia, cuenta con una veta recta  y un agran acabado.</p></div><div style=\"font-weight: bold;\">Brazo de caoba africana.</div><div><p>Excelente madera con propiedades de transmisión sonora. Gran estabilidad estructural. Tus digitaciones serán más cómodas y placenteras, ya que la caoba es la madera más fina, ligera y duradera que una guitarra puede tener.</p></div><div style=\"font-weight: bold;\">Diapasón y puente de palosanto.</div><div><p>El palosanto es una madera de extraordinaria resistencia y durabilidad, que ofrece gran resistencia a los elementos. Es una madera de muy alta calidad y durabilidad además de excelentes propiedades acústicas.</p></div><div style=\"font-weight: bold;\">Escala.</div><div><p>650 mm.</p></div></div><div><h4>Caracteristicas</h4></div><div><ul style=\"margin: 0px 0px 0px 25px;\"><li style=\"list-style-type: circle; margin: 5px;\">Preamplificador con ecualizador de 4 bandas.</li><li style=\"list-style-type: circle; margin: 5px;\">Resaque.</li></ul></div></p></div>","Imagenes_secundarias": ["http://201.107.4.57/Archivo/Extra/ISADMSARAEC$001.jpg ","http://201.107.4.57/Archivo/Extra/ISADMSARAEC$002.jpg ","http://201.107.4.57/Archivo/Extra/ISADMSARAEC$003.jpg "] ,"Categorias": [{ "Categoria":"Cuerdas", "Id":"752",  "PadreId":"0" },{ "Categoria":"Guitarras", "Id":"785",  "PadreId":"752" },{ "Categoria":"Acusticas", "Id":"786",  "PadreId":"785" },{ "Categoria":"Guitarras Electro-Clasicas", "Id":"806",  "PadreId":"786" }] ,"Especs": [{ "espec":"Acabado", "valor":"NATURAL CON BRILLO" },{ "espec":"Aros", "valor":"ACABADO NOGAL" },{ "espec":"Brazo", "valor":"CAOBA AFRICANA" },{ "espec":"Canto Delgado", "valor":"NO" },{ "espec":"Diapason", "valor":"PALO SANTO" },{ "espec":"Fondo", "valor":"ACABADO NOGAL" },{ "espec":"Maquinaria", "valor":"NIQUEL" },{ "espec":"Puente", "valor":"PALO SANTO" },{ "espec":"Resaque", "valor":"SI" },{ "espec":"Tapa Armonica", "valor":"PINO DE OREGON" },{ "espec":"Trastes", "valor":"19" },{ "espec":"Disco Tutorial", "valor":"NO" },{ "espec":"En Paquete Incluye: Funda", "valor":"NO" },{ "espec":"Encordadura Extra", "valor":"NO" },{ "espec":"Incluye Cable", "valor":"NO" },{ "espec":"Incluye Estuche Rigido", "valor":"NO" },{ "espec":"Incluye Funda", "valor":"NO" },{ "espec":"Incluye Llave De Ajuste", "valor":"NO" },{ "espec":"TamaÑo/tipo", "valor":"44" },{ "espec":"Preamp", "valor":"ACTIVO CON EQ DE 4 BANDAS" }] , "Cajas":"1", "Multiplo":"1" }';

    $json_object = json_decode($json_text, true);
    $json_object['Codigo'] .= '_' . $row;
    return $json_object;
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
// 35797
