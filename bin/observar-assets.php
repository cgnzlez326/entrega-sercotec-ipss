<?php
/**
 * Observa los fuentes CSS/JS del theme y regenera los .min automáticamente.
 * Cross-platform: solo requiere PHP CLI (igual que bin/minificar.php).
 *
 * Uso:
 *   php bin/observar-assets.php
 * (deja el proceso corriendo mientras editas; Ctrl+C para salir)
 *
 * @package cdn-santiago
 */

if ( PHP_SAPI !== 'cli' ) {
	exit( 'Debe ejecutarse por CLI: php bin/observar-assets.php' );
}

$root    = dirname( __DIR__ );
$minificar = __DIR__ . '/minificar.php';
$fuentes = array(
	$root . '/wp-content/themes/cdn-santiago/assets/css/main.css',
	$root . '/wp-content/themes/cdn-santiago/assets/js/cdn-app.js',
);

echo "Observando CSS/JS del theme. Edita y guarda para minificar. Ctrl+C para salir.\n";

$prev = array();
foreach ( $fuentes as $f ) {
	if ( file_exists( $f ) ) {
		$prev[ $f ] = filemtime( $f );
	}
}

while ( true ) {
	usleep( 600000 );
	$cambio = false;
	foreach ( $fuentes as $f ) {
		if ( ! file_exists( $f ) ) {
			continue;
		}
		$t = filemtime( $f );
		if ( isset( $prev[ $f ] ) && $t > $prev[ $f ] ) {
			$cambio = true;
		}
		$prev[ $f ] = $t;
	}
	if ( $cambio ) {
		echo '[' . gmdate( 'H:i:s' ) . '] Cambio detectado. Regenerando .min...' . "\n";
		$cmd = escapeshellarg( PHP_BINARY ) . ' ' . escapeshellarg( $minificar );
		passthru( $cmd, $codigo );
	}
}
