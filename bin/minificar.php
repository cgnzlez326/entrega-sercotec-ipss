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

/**
 * Minificación segura para JS: conserva los saltos de línea (la semántica y
 * las expresiones regulares quedan intactas) y solo elimina sangría, espacios
 * sobrantes al inicio/fin de cada línea y líneas vacías.
 */
function cdn_mini_js( $codigo ) {
	$lineas = preg_split( '/\r\n|\r|\n/', $codigo );
	$salida = array();
	foreach ( $lineas as $linea ) {
		$linea = rtrim( $linea, " \t" );
		$linea = ltrim( $linea, " \t" );
		if ( '' !== $linea ) {
			$salida[] = $linea;
		}
	}
	return implode( "\n", $salida ) . "\n";
}

$base = dirname( __DIR__ ) . '/wp-content/themes/cdn-santiago/assets';

$pares = array(
	$base . '/css/main.css'  => array( $base . '/css/main.min.css', 'cdn_mini' ),
	$base . '/js/cdn-app.js' => array( $base . '/js/cdn-app.min.js', 'cdn_mini_js' ),
);

foreach ( $pares as $origen => $config ) {
	if ( ! file_exists( $origen ) ) {
		fwrite( STDERR, 'No existe: ' . $origen . "\n" );
		continue;
	}
	list( $destino, $minificador ) = $config;
	$min = call_user_func( $minificador, file_get_contents( $origen ) );
	file_put_contents( $destino, $min );
	printf( "Minificado: %s (%d KB -> %d KB)\n", basename( $destino ), (int) ( filesize( $origen ) / 1024 ), (int) ( strlen( $min ) / 1024 ) );
}
echo "Listo.\n";
