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

echo "reducir las imagenes de manera proporcional a 900x900 pixeles"
find -name '*.jpg' -type f -print0 | xargs -0 identify -format '%h %i\n'| awk '$1>900' | cut -d' ' -f2- | xargs -I {} -n 1 -P 4 mogrify -resize 900x900\> {}

echo "comprimir todas las imagenes jpg a 75 porciento"
find -name '*.jpg' -type f | xargs -I {} -n 1 -P 4 mogrify -quality 75% {}

echo "optimizar imagenes"
find -name '*.jpg' -type f | xargs -I {} -n 1 -P 4 jpegoptim -o --strip-all --all-progressive {}

echo "renombrar archivos residuales"
for f in `find -name "*.jpg~"`; do mv -v "$f" $(echo "$f" | tr -d '\~'); done

# descargar multiples imagenes desde un archivo con 10 hilos de descarga:
# sort -u ../download_images.txt |  xargs -I {} -n 1 -P 10  wget -c -i- {}