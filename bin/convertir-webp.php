<?php
/**
 * Convierte PNG/JPG de assets/img a WebP con GD (si está disponible).
 * Uso: php bin/convertir-webp.php [calidad]
 *
 * @package cdn-santiago
 */

if ( PHP_SAPI !== 'cli' ) {
	exit( "Debe ejecutarse por CLI\n" );
}
if ( ! extension_loaded( 'gd' ) || ! function_exists( 'imagewebp' ) ) {
	fwrite( STDERR, "GD/WebP no disponible. Instale/active ext-gd.\n" );
	exit( 1 );
}

$calidad = isset( $argv[1] ) ? (int) $argv[1] : 82;
$carpeta = __DIR__ . '/../wp-content/themes/cdn-santiago/assets/img';
$archivos = array_merge(
	glob( $carpeta . '/*.png' ) ?: array(),
	glob( $carpeta . '/*.jpg' ) ?: array(),
	glob( $carpeta . '/*.jpeg' ) ?: array()
);
sort( $archivos );
$n = 0;
foreach ( $archivos as $archivo ) {
	$ext  = strtolower( pathinfo( $archivo, PATHINFO_EXTENSION ) );
	$webp = substr( $archivo, 0, - ( strlen( $ext ) + 1 ) ) . '.webp';
	if ( file_exists( $webp ) ) {
		continue; // Ya convertido.
	}
	$img = ( 'png' === $ext ) ? @imagecreatefrompng( $archivo ) : @imagecreatefromjpeg( $archivo );
	if ( ! $img ) {
		fwrite( STDERR, 'No se pudo decodificar: ' . basename( $archivo ) . "\n" );
		continue;
	}
	// PNG puede traer transparencia: la mantenemos.
	if ( 'png' === $ext ) {
		imagepalettetotruecolor( $img );
		imagealphablending( $img, false );
		imagesavealpha( $img, true );
	}
	$ok = imagewebp( $img, $webp, $calidad );
	imagedestroy( $img );
	if ( $ok ) {
		printf( "WebP creado: %s (%d KB)\n", basename( $webp ), (int) ( filesize( $webp ) / 1024 ) );
		$n++;
	}
}
echo "Listo. $n archivos convertidos.\n";
