<?php
/**
 * Descarga imagenes de un archivo de texto de imagenes
 */

$filename = "download_images.txt";
$images = file($filename, FILE_IGNORE_NEW_LINES);

foreach ($images as $image) {
    if (empty($image)) {
        continue;
    }
    $image = rtrim($image);
    $name = sanitize_image_name(basename($image));

    // no hace nada si el archivo ya existe
    if (file_exists("downloaded_images/" . $name)) {
        continue;
    }
    //get image
    $imageData = @file_get_contents($image); //$image variable is the url from your array
    if($imageData !== false){
        $handle = fopen("downloaded_images/" . $name, "x+");
        fwrite($handle, $imageData);
        fclose($handle);
        echo 'imagen descargada: ' . $name . PHP_EOL;
    }
    else{
        echo 'la imagen no existe: ' . $image . PHP_EOL;
    }

}

// funcion para sanitizar el nombre de la imagen a guardar
function sanitize_image_name($text)
{
    // $text = str_replace('$', '-', $text);
    return $text;
}
