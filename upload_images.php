<?php

/**
 * Carga multiples imagenes a la plataforma fesh
 */

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
    $query = http_build_query($get_params);
    $query = preg_replace('/%5B[0-9]+%5D/simU', '', $query);
    $url .= '?' . $query;

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
 * Undocumented function
 *
 * @param [array] $images
 * @return string
 */
function send_images($images)
{
    foreach ($images as $key => $image) {
        $post_params['file[' . $key . ']'] =  new CURLFILE($image['filename'], $image['mime']);
    }

    $url = 'https://coyotesmusicshop.fesh.store/admin/index.php';

    // TODO: poner cookies y token como constantes o como parametros
    $cookies = array(
        "PHPSESSID" => "ooe1qq64umr5t6g6tfpjip73e3",
        "default" => "583688a2b1d020f3b3795bbc4a",
    );
    $token = "mfj61cTCdxuPnwHkUZLOHonjNpMmsiG6";

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
    if (is_dir($dir)) {
        if ($dh = opendir($dir)) {
            $i = 0;
            $j = 0;
            while (($file = readdir($dh)) !== false) {
                // $j++;
                // if($j < 11440){
                //     continue;
                // }
                $filename = $dir . '/' . $file;
                $image = @getimagesize($filename);
                if ($image !== false && is_array($image) && isset($image['mime'])) {
                    $files[] = array(
                        "filename" => $filename,
                        "mime" => $image['mime']
                    );
                    // TODO: hace falta validar si el listado restante de imagenes es menor a 10.
                    // si es menor a 10 ya no va a intentar subir las imagenes
                    if (count($files) == 10) {
                        $response = json_decode(send_images($files), true);
                        if (isset($response['success'])) {
                            $i += count($files);
                            echo 'imagenes subidas: ' . $i . PHP_EOL;
                            $files = array();
                        } else if (isset($response['error'])) {
                            echo print_r($files);
                            break;
                        }
                    }
                }
            }
            closedir($dh);
        }
    }
}

read_images('downloaded_images');
