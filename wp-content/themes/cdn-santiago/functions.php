<?php
/**
 * Funciones del theme CDN Santiago.
 *
 * @package cdn-santiago
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CDN_THEME_VERSION', '1.0.2' );
define( 'CDN_THEME_DIR', get_template_directory() );
define( 'CDN_THEME_URI', get_template_directory_uri() );

/**
 * URL de un asset del theme (con versionado).
 *
 * @param string $rel Ruta relativa dentro de assets.
 * @return string
 */
function cdn_asset( $rel ) {
	return CDN_THEME_URI . '/assets/' . ltrim( $rel, '/' );
}

/**
 * URL de una imagen de assets/img prefiriendo la versión WebP optimizada.
 *
 * @param string $nombre Nombre base del archivo (sin extensión).
 * @return string
 */
function cdn_img( $nombre ) {
	$webp = CDN_THEME_DIR . '/assets/img/' . $nombre . '.webp';
	if ( file_exists( $webp ) ) {
		return cdn_asset( 'img/' . $nombre . '.webp' );
	}
	foreach ( array( 'png', 'jpg', 'jpeg' ) as $ext ) {
		if ( file_exists( CDN_THEME_DIR . '/assets/img/' . $nombre . '.' . $ext ) ) {
			return cdn_asset( 'img/' . $nombre . '.' . $ext );
		}
	}
	return cdn_asset( 'img/' . $nombre . '.png' );
}

/**
 * Configuración básica del theme.
 */
function cdn_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' )
	);
	add_theme_support( 'customize-selective-refresh-widgets' );
	register_nav_menus(
		array(
			'principal' => 'Menú principal',
		)
	);
}
add_action( 'after_setup_theme', 'cdn_setup' );

/**
 * Hoja de estilos principal (usa versión minificada si existe).
 */
function cdn_styles() {
	$min   = file_exists( CDN_THEME_DIR . '/assets/css/main.min.css' ) && ! ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG );
	$css   = $min ? 'css/main.min.css' : 'css/main.css';
	wp_enqueue_style( 'cdn-main', cdn_asset( $css ), array(), $min ? CDN_THEME_VERSION : CDN_THEME_VERSION . '.dev' );
}
add_action( 'wp_enqueue_scripts', 'cdn_styles' );

/**
 * Script principal (defer). Se sirve minificado si existe.
 */
function cdn_scripts() {
	$min = file_exists( CDN_THEME_DIR . '/assets/js/cdn-app.min.js' ) && ! ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG );
	$js  = $min ? 'js/cdn-app.min.js' : 'js/cdn-app.js';
	wp_enqueue_script(
		'cdn-app',
		cdn_asset( $js ),
		array(),
		$min ? CDN_THEME_VERSION : CDN_THEME_VERSION . '.dev',
		array(
			'in_footer' => true,
			'strategy'  => 'defer',
		)
	);

	$rest_base = function_exists( 'rest_url' ) ? rest_url( 'cdn/v1/' ) : '/wp-json/cdn/v1/';
	wp_localize_script(
		'cdn-app',
		'cdnApp',
		array(
			'rest'       => $rest_base,
			'esFront'    => is_front_page(),
			'homeUrl'    => home_url( '/' ),
			'contactoId' => 'contacto',
		)
	);
}
add_action( 'wp_enqueue_scripts', 'cdn_scripts' );

/**
 * Quita emojis y artefactos innecesarios del head para rendimiento.
 */
function cdn_limpiar_head() {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
}
add_action( 'init', 'cdn_limpiar_head' );

/**
 * Cabeceras de seguridad en respuestas del front.
 */
function cdn_headers_seguridad() {
	if ( is_admin() ) {
		return;
	}
	header( 'X-Content-Type-Options: nosniff' );
	header( 'X-Frame-Options: SAMEORIGIN' );
	header( 'Referrer-Policy: strict-origin-when-cross-origin' );
	header( 'Permissions-Policy: geolocation=(), microphone=(), camera=()' );
}
add_action( 'send_headers', 'cdn_headers_seguridad' );

/**
 * Acceso a los ajustes del sitio definidos por el plugin (con respaldo propio).
 *
 * @param string|null $key Clave.
 * @return mixed
 */
function cdn_ajuste( $key = null ) {
	if ( function_exists( 'cdn_contenidos_ajuste' ) ) {
		return cdn_contenidos_ajuste( $key );
	}
	$def = array(
		'contacto_telefono' => '+(56) 9 3927 5633',
		'contacto_correo'   => 'centro.santiago@centrossercotec.cl',
		'contacto_direccion'=> 'Manuel Rodríguez Sur 749, Santiago (Metro Toesca)',
		'contacto_horario'  => 'Lunes a Viernes, 9:00 a 18:00 hrs.',
	);
	return null === $key ? $def : ( isset( $def[ $key ] ) ? $def[ $key ] : '' );
}

/**
 * Menú de anclas cuando no existe menú asignado.
 */
function cdn_menu_fallback() {
	$items = array(
		'#inicio'     => 'Inicio',
		'#nosotros'   => 'Nosotros',
		'#servicios'  => 'Servicios',
		'#testimonios'=> 'Testimonios',
		'#preguntas'  => 'Preguntas frecuentes',
		'#ubicaciones'=> 'Ubicaciones',
		'#contacto'   => 'Contacto',
	);
	echo '<ul id="menu-principal" class="menu">';
	foreach ( $items as $href => $label ) {
		printf( '<li class="menu-item"><a href="%s">%s</a></li>', esc_url( $href ), esc_html( $label ) );
	}
	echo '</ul>';
}

/**
 * Marcado esqueleto mientras cargan las secciones dinámicas.
 *
 * @param int $cantidad Número de bloques.
 */
function cdn_plantilla_carga( $cantidad = 3 ) {
	echo '<div class="plantilla-carga" aria-hidden="true">';
	for ( $i = 0; $i < $cantidad; $i++ ) {
		echo '<div class="plantilla-carga__bloque"><span></span></div>';
	}
	echo '</div>';
}

/**
 * Número de secciones (navegación principal).
 */
function cdn_secciones() {
	return array(
		'nosotros'    => array( 'id' => 'nosotros', 'titulo' => 'Nosotros' ),
		'servicios'   => array( 'id' => 'servicios', 'titulo' => 'Servicios' ),
		'testimonios' => array( 'id' => 'testimonios', 'titulo' => 'Testimonios' ),
		'preguntas'   => array( 'id' => 'preguntas', 'titulo' => 'Preguntas frecuentes' ),
		'ubicaciones' => array( 'id' => 'ubicaciones', 'titulo' => 'Ubicaciones' ),
		'contacto'    => array( 'id' => 'contacto', 'titulo' => 'Contacto' ),
	);
}

/**
 * SVG inline reutilizable de un ícono (evita requests y permite colorear).
 *
 * @param string $nombre Nombre del ícono.
 * @return string
 */
function cdn_icono( $nombre ) {
	$iconos = array(
		'telefono' => '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M6.6 10.8a15.1 15.1 0 0 0 6.6 6.6l2.2-2.2a1 1 0 0 1 1-.24 11.4 11.4 0 0 0 3.6.57 1 1 0 0 1 1 1V20a1 1 0 0 1-1 1A17 17 0 0 1 3 4a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1 1 11.4 11.4 0 0 0 .57 3.6 1 1 0 0 1-.25 1z"/></svg>',
		'correo'   => '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M20 4H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2zm0 4-8 5-8-5V6l8 5 8-5z"/></svg>',
		'ubicacion'=> '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M12 2a7 7 0 0 0-7 7c0 5.25 7 13 7 13s7-7.75 7-13a7 7 0 0 0-7-7zm0 9.5A2.5 2.5 0 1 1 12 6.5a2.5 2.5 0 0 1 0 5z"/></svg>',
		'horario'  => '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2zm1 10.6 4.5 2.7-.9 1.5-5.6-3.3V7h2z"/></svg>',
		'facebook' => '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M14 13.5h2.5l1-4H14v-2a1 1 0 0 1 1-1h2V2.5h-2.5a4.5 4.5 0 0 0-4.5 4.5v2.5H8v4h2V22h4z"/></svg>',
		'instagram'=> '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M12 2.2c3.2 0 3.6 0 4.9.1 3.3.1 4.8 1.7 4.9 4.9.1 1.3.1 1.6.1 4.8s0 3.6-.1 4.8c-.1 3.2-1.7 4.8-4.9 4.9-1.3.1-1.6.1-4.9.1s-3.6 0-4.9-.1c-3.3-.1-4.8-1.7-4.9-4.9C2.2 15.6 2.2 15.2 2.2 12s0-3.6.1-4.8c.1-3.2 1.7-4.8 4.9-4.9C8.4 2.2 8.8 2.2 12 2.2zm0 3.6A6.2 6.2 0 1 0 18.2 12 6.2 6.2 0 0 0 12 5.8zm0 10.2A4 4 0 1 1 16 12a4 4 0 0 1-4 4zm6.4-11.8a1.4 1.4 0 1 0 1.4 1.4 1.4 1.4 0 0 0-1.4-1.4z"/></svg>',
		'whatsapp' => '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M12 2a10 10 0 0 0-8.6 15L2 22l5.2-1.4A10 10 0 1 0 12 2zm5 13.6c-.2.7-1.2 1.3-2 1.4-.5.1-1.2.1-1.9-.1a16 16 0 0 1-5-3.2 15 15 0 0 1-3.2-4.6A5 5 0 0 1 5 7c.1-.8.7-1.8 1.4-2s1.1-.1 1.4.1l.8 1.8c.1.3.1.7-.1 1l-.4.5a6 6 0 0 0 3.7 3.7l.5-.4c.3-.2.7-.2 1-.1l1.8.8c.2.3.4 1 .2 1.4z"/></svg>',
		'estrella' => '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="m12 2 3 6.6 7 .8-5.2 4.8 1.4 7L12 18l-6.2 3.2 1.4-7L2 9.4l7-.8z"/></svg>',
	);
	if ( isset( $iconos[ $nombre ] ) ) {
		return '<span class="icono icono-' . esc_attr( $nombre ) . '" aria-hidden="true">' . $iconos[ $nombre ] . '</span>';
	}
	return '';
}

/**
 * Tipo de cambio del día (indicadores USD, EUR, UF y UTM).
 *
 * Consume la API pública "Todas las monedas" de Gael Cloud (Banco Central
 * de Chile) una sola vez al día: la respuesta se cachea en un transient de
 * 24 h. Si la API no responde se devuelven valores de referencia marcados,
 * para que la cabecera nunca quede vacía.
 *
 * @return array
 */
function cdn_monedas_gael() {
	static $resuelto = null;

	if ( null !== $resuelto ) {
		return $resuelto;
	}

	$clave = 'cdn_monedas_gael_1';
	$cache = get_transient( $clave );
	if ( is_array( $cache ) && ! empty( $cache ) ) {
		$resuelto = $cache;
		return $cache;
	}

	$referencia = array(
		'USD' => array( 'Codigo' => 'USD', 'Nombre' => 'Dólar americano', 'Valor' => '965,00', 'Referencia' => true ),
		'EUR' => array( 'Codigo' => 'EUR', 'Nombre' => 'Euro', 'Valor' => '1.048,00', 'Referencia' => true ),
		'UF'  => array( 'Codigo' => 'UF', 'Nombre' => 'Unidad de Fomento', 'Valor' => '40.881,68', 'Referencia' => true ),
		'UTM' => array( 'Codigo' => 'UTM', 'Nombre' => 'Unidad Tributaria Mensual', 'Valor' => '69.265,00', 'Referencia' => true ),
	);
	$orden = array( 'USD', 'EUR', 'UF', 'UTM' );

	$respuesta = wp_remote_get(
		'https://api.gael.cloud/general/public/monedas',
		array(
			'timeout'     => 6,
			'user-agent'  => 'CDN Santiago (WordPress) ' . home_url( '/' ),
			'headers'     => array( 'Accept' => 'application/json' ),
			'httpversion' => '1.1',
		)
	);

	$disponibles = array();
	if ( ! is_wp_error( $respuesta ) && 200 === (int) wp_remote_retrieve_response_code( $respuesta ) ) {
		$json = json_decode( wp_remote_retrieve_body( $respuesta ), true );
		if ( is_array( $json ) ) {
			foreach ( $json as $moneda ) {
				if ( ! empty( $moneda['Codigo'] ) ) {
					$disponibles[ trim( (string) $moneda['Codigo'] ) ] = $moneda;
				}
			}
		}
	}

	$salida = array();
	foreach ( $orden as $codigo ) {
		$item = $referencia[ $codigo ];
		if ( isset( $disponibles[ $codigo ] ) && 'ND' !== trim( (string) $disponibles[ $codigo ]['Valor'] ) ) {
			$item = array(
				'Codigo' => $codigo,
				'Nombre' => isset( $disponibles[ $codigo ]['Nombre'] ) ? $disponibles[ $codigo ]['Nombre'] : $codigo,
				'Valor'  => trim( (string) $disponibles[ $codigo ]['Valor'] ),
			);
		}
		$salida[ $codigo ] = $item;
	}

	set_transient( $clave, $salida, DAY_IN_SECONDS );
	$resuelto = $salida;
	return $salida;
}

/**
 * Normaliza y formatea un valor monetario a es-CL (p. ej. "40881,68" → "40.881,68").
 *
 * @param string $valor Valor crudo de la API.
 * @return string
 */
function cdn_monedas_valor( $valor ) {
	$limpio = str_replace( '.', '', trim( (string) $valor ) );
	$limpio = str_replace( ',', '.', $limpio );
	if ( '' === $limpio || ! is_numeric( $limpio ) ) {
		return '';
	}
	return number_format( (float) $limpio, 2, ',', '.' );
}

/**
 * Widget de tipo de cambio para la barra superior (cabecera).
 */
function cdn_barra_monedas() {
	$monedas = cdn_monedas_gael();
	if ( empty( $monedas ) ) {
		return;
	}
	echo '<div class="barra-monedas" role="group" aria-label="Tipo de cambio del día, Banco Central de Chile vía API Gael Cloud">';
	echo '<span class="barra-monedas__titulo">Tipo de cambio</span>';
	echo '<ul class="barra-monedas__lista">';
	foreach ( $monedas as $moneda ) {
		$referencia = ! empty( $moneda['Referencia'] );
		$nombre     = isset( $moneda['Nombre'] ) ? (string) $moneda['Nombre'] : (string) $moneda['Codigo'];
		$titulo     = $referencia ? $nombre . ' (valor de referencia)' : $nombre;
		$valor      = cdn_monedas_valor( isset( $moneda['Valor'] ) ? $moneda['Valor'] : '' );
		echo '<li class="barra-monedas__item' . ( $referencia ? ' barra-monedas__item--referencia' : '' ) . '">';
		echo '<abbr class="barra-monedas__codigo" title="' . esc_attr( $titulo ) . '">' . esc_html( $moneda['Codigo'] ) . '</abbr>';
		if ( '' !== $valor ) {
			echo '<span class="barra-monedas__valor">$ ' . esc_html( $valor ) . '</span>';
		}
		echo '</li>';
	}
	echo '</ul>';
	echo '<a class="barra-monedas__fuente" href="https://api.gael.cloud/general/public/monedas" target="_blank" rel="noopener noreferrer">api.gael.cloud</a>';
	echo '</div>';
}
