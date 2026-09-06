<?php
/**
 * Genera versiones minificadas de assets (CSS/JS) del theme.
 * Uso: php bin/minificar.php
 *
 * Nota: minificación conservadora (quita comentarios y colapsa blancos).
 *
 * @package cdn-santiago
 */

if ( PHP_SAPI !== 'cli' ) {
	exit( 'CLI only' );
}

$base = dirname( __DIR__ ) . '/wp-content/themes/cdn-santiago/assets';

function cdn_mini( $codigo ) {
	$codigo = preg_replace( '#/\*.*?\*/#s', '', $codigo );
	$codigo = preg_replace( '/\s+/', ' ', $codigo );
	return trim( $codigo );
}

$pares = array(
	$base . '/css/main.css'   => $base . '/css/main.min.css',
	$base . '/js/cdn-app.js'  => $base . '/js/cdn-app.min.js',
);

foreach ( $pares as $origen => $destino ) {
	if ( ! file_exists( $origen ) ) {
		fwrite( STDERR, 'No existe: ' . $origen . "\n" );
		continue;
	}
	$min = cdn_mini( file_get_contents( $origen ) );
	file_put_contents( $destino, $min );
	printf( "Minificado: %s (%d KB -> %d KB)\n", basename( $destino ), (int) ( filesize( $origen ) / 1024 ), (int) ( strlen( $min ) / 1024 ) );
}
echo "Listo.\n";
