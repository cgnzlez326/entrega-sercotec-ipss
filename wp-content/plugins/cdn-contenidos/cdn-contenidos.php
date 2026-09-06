<?php
/**
 * Plugin Name: CDN Contenidos
 * Description: Tipos de contenido y API REST (/cdn/v1/*) para el Centro de Desarrollo de Negocios Sercotec Santiago.
 * Version: 1.0.0
 * Author: Equipo CDN Santiago
 * License: GPL-2.0-or-later
 * Text Domain: cdn-contenidos
 *
 * @package cdn_contenidos
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CDN_CONTENIDOS_VERSION', '1.0.0' );
define( 'CDN_CONTENIDOS_DIR', plugin_dir_path( __FILE__ ) );
define( 'CDN_CONTENIDOS_URL', plugin_dir_url( __FILE__ ) );

require_once CDN_CONTENIDOS_DIR . 'inc/post-types.php';
require_once CDN_CONTENIDOS_DIR . 'inc/meta.php';
require_once CDN_CONTENIDOS_DIR . 'inc/captcha.php';
require_once CDN_CONTENIDOS_DIR . 'inc/rest.php';
require_once CDN_CONTENIDOS_DIR . 'inc/ajustes.php';

/**
 * Registra tipos de contenido al iniciar WordPress.
 */
function cdn_contenidos_init() {
	cdn_contenidos_registrar_post_types();
	cdn_contenidos_registrar_taxonomia();
}
add_action( 'init', 'cdn_contenidos_init', 0 );

/**
 * Al activar: registra los CPT y reescribe reglas.
 */
function cdn_contenidos_activar() {
	cdn_contenidos_registrar_post_types();
	cdn_contenidos_registrar_taxonomia();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'cdn_contenidos_activar' );
