<?php

/**
 * Carga multiples imagenes a la plataforma fesh
 * 
 */
// Actualizar los valores de estas constantes deacuerdo a los valores que regrese el navegador desde la consola de desarrollo
define('COOKIE_PHPSESSID', 'm479nc0jhjab5gu50ma6hd1g60');
define('COOKIE_DEFAULT', '86394511a5959182f1efd38a35');
define('TOKEN_GET_PARAM', 'dlEksTXNOtoaT1m06LR7Ob164DS2oUmk');

/**
 * Undocumented function
 *
 * @param [string] $url
 * @param [array] $get_params
 * @param [array] $post_params
 * @param [array] $headers
 * @return string
 */
function curl_sender($url, $get_params, $post_params, $headers)
{
    if (!empty($get_params)) {
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

/**
 * Sube imagenes a fesh
 * para obtener las cookies y token, abrir https://coyotesmusicshop.com.mx/admin/?user56571
 * iniciar sesion
 * obtener el valor del token desde la url (viene como parametro GET)
 * obtener los valores de cookie desde la consola del navegador
 *
 * @param [array] $images
 * @return string
 */
function send_images($images)
{
    foreach ($images as $key => $image) {
        $post_params['file[' . $key . ']'] =  new CURLFILE($image['filename'], $image['mime']);
    }

    $url = 'https://coyotesmusicshop.com.mx/admin/index.php';

    $cookies = array(
        "PHPSESSID" => COOKIE_PHPSESSID,
        "default" => COOKIE_DEFAULT,
    );
    $token = TOKEN_GET_PARAM;

    $get_params = array(
        "route" => "common/filemanager/upload",
        "token" => $token,
        "directory" => "product",
    );

    $cookies_string = http_build_query($cookies, '', '; ');
    $headers = array(
        "Accept: application/json, text/javascript, */*; q=0.01",
        "Content-Type: multipart/form-data",
        "Cookie: " . $cookies_string . ";"
    );
    $response = curl_sender($url, $get_params, $post_params, $headers);
    echo $response . PHP_EOL;
    return $response;
}

/**
 * Undocumented function
 *
 * @param [string] $dir
 * @return void
 */
function read_images($dir)
{
    $files = array();
    if (is_dir($dir)) {
        if ($dh = opendir($dir)) {
            $i = 0;
            $j = 0;
            while (($file = readdir($dh)) !== false) {
                // $j++;
                // if($j < 3180){
                //     continue;
                // }
                $filename = $dir . '/' . $file;
                $image = @getimagesize($filename);
                if ($image !== false && is_array($image) && isset($image['mime'])) {
                    $files[] = array(
                        "filename" => $filename,
                        "mime" => $image['mime']
                    );
                    if (count($files) == 30) {
                        $response = json_decode(send_images($files), true);
                        if (isset($response['success'])) {
                            $i += count($files);
                            echo 'imagenes subidas: ' . $i . PHP_EOL;
                        } else if (isset($response['error'])) {
                            echo print_r($files);
                            break;
                        } else {
                            echo 'Se obtuvo una respuesta inesperada: ';
                            echo print_r($files);
                            break;
                        }
                        // Independientemente del resultado, se reinicia array
                        $files = array();
                    }
                }
            }
            closedir($dh);
            // mandar el resto de archivos
            if (count($files) > 0) {
                $response = json_decode(send_images($files), true);
                if (isset($response['success'])) {
                    $i += count($files);
                    echo 'imagenes subidas: ' . $i . PHP_EOL;
                } else if (isset($response['error'])) {
                    echo print_r($files);
                } else {
                    echo 'Se obtuvo una respuesta inesperada: ';
                    echo print_r($files);
                }
            }
        }
    }
}

read_images('downloaded_images');
