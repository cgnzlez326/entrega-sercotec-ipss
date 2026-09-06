<?php
/**
 * Plugin Name: CDN Habilitaciones locales
 * Description: Habilita Application Passwords en entorno local (HTTP) para consumir la REST API con Postman.
 * Version: 1.0.0
 * Author: Equipo CDN Santiago
 *
 * @package cdn_habilitaciones
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * En localhost (XAMPP, HTTP) WordPress considera inseguro el entorno para
 * Application Passwords. En un entorno local declarado esto es aceptable y
 * necesario para demostrar la gestión de contenido con Postman.
 */
add_filter(
	'wp_is_application_passwords_supported',
	function ( $supported ) {
		if ( defined( 'WP_ENVIRONMENT_TYPE' ) && 'local' === WP_ENVIRONMENT_TYPE ) {
			return true;
		}
		return $supported;
	}
);

add_filter(
	'wp_is_application_passwords_available',
	function ( $available ) {
		if ( defined( 'WP_ENVIRONMENT_TYPE' ) && 'local' === WP_ENVIRONMENT_TYPE ) {
			return true;
		}
		return $available;
	}
);

/**
 * Evita que el descubrimiento de la REST API añada cabeceras de link en páginas
 * públicas no necesarias y fuerza flush de reglas si faltan.
 */
add_action(
	'admin_init',
	function () {
		$flush = get_option( 'cdn_rewrite_flushed' );
		if ( ! $flush ) {
			flush_rewrite_rules();
			update_option( 'cdn_rewrite_flushed', 1 );
		}
	}
);
