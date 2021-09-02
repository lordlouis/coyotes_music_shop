#bin/bash
# este script optimiza las imagenes para poder presentarlas de la mejor manera en web
# convierte imagenes png a jpg
# aplica algoritmo de compresión a 85%
# optimiza imagenes
# reduce el tamaño de las imagenes de manera proporcional a 1500x1500 pixeles

#se usa la libreria imagemagick para este script
#TODO: hacer validaciones para que no truene si no existen imagenes, no existen librerias, etc



#directorio de imagenes
IMAGE_DIR="downloaded_images/"
WATERMARK_IMG="logo-horizontal-watermark.png"
cd $IMAGE_DIR
echo "renombrar imagenes que tengan extension .jpg pero que realmente son PNG, a que tengan la extension png"

for f in *.jpg ; do
  if [[ $(file -b --mime-type "$f") = image/png ]] ; then
    mv "$f" "${f/%.jpg/.png}"
  fi
done

echo "convertir todas las imagenes png a formato jpg"
find -name '*.png' -type f | xargs -I {} -n 1 -P 4 mogrify -format jpg -background white -alpha remove {}

echo "eliminar archivos convertidos a png( ya no se necesitan)"
rm *.png

echo "reducir las imagenes a 900x900 pixeles y rellenar espacios con fondo blanco a 900x900 pixeles"
# esta configuración de medidas es especial para la plataforma fesh
find -name '*.jpg' -type f | xargs -I {} -n 1 -P 4 mogrify -resize 900x900 -extent 900x900 -gravity Center -fill white {}

echo "agregar marca de agua"
find -name '*.jpg' -type f | xargs -I {} -n 1 -P 4 composite -dissolve 6 -tile ../$WATERMARK_IMG {} {}

echo "comprimir todas las imagenes jpg a 60 porciento"
find -name '*.jpg' -type f | xargs -I {} -n 1 -P 4 mogrify -quality 60% {}

echo "optimizar imagenes"
find -name '*.jpg' -type f | xargs -I {} -n 1 -P 4 jpegoptim -o --strip-all --all-progressive {}

echo "renombrar archivos residuales"
for f in `find -name "*.jpg~"`; do mv -v "$f" $(echo "$f" | tr -d '\~'); done

echo "cambiar mayusculas a minusculas"
for i in $( ls | grep [A-Z] ); do mv -i $i `echo $i | tr 'A-Z' 'a-z'`; done

# descargar multiples imagenes desde un archivo con 20 hilos de descarga:
# sort -u ../download_images.txt |  xargs -I {} -n 1 -P 20  wget -c -i- {}