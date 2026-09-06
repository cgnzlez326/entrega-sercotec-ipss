<?php
/**
 * Registro de tipos de contenido (CPT) y taxonomía del plugin CDN Contenidos.
 *
 * @package cdn_contenidos
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registra los tipos de contenido personalizados.
 */
function cdn_contenidos_registrar_post_types() {

	// Servicios del centro (tarjetas reutilizables).
	register_post_type(
		'cdn_servicio',
		array(
			'labels'       => array(
				'name'          => 'Servicios',
				'singular_name' => 'Servicio',
				'add_new_item'  => 'Añadir nuevo servicio',
				'edit_item'     => 'Editar servicio',
			),
			'public'       => true,
			'has_archive'  => false,
			'rewrite'      => array( 'slug' => 'servicio' ),
			'menu_icon'    => 'dashicons-portfolio',
			'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail' ),
			'show_in_rest' => true,
		)
	);

	// Testimonios (carrusel).
	register_post_type(
		'cdn_testimonio',
		array(
			'labels'       => array(
				'name'          => 'Testimonios',
				'singular_name' => 'Testimonio',
				'add_new_item'  => 'Añadir nuevo testimonio',
				'edit_item'     => 'Editar testimonio',
			),
			'public'       => true,
			'has_archive'  => false,
			'rewrite'      => array( 'slug' => 'testimonio' ),
			'menu_icon'    => 'dashicons-format-quote',
			'supports'     => array( 'title', 'editor', 'thumbnail' ),
			'show_in_rest' => true,
		)
	);

	// Preguntas frecuentes (FAQ).
	register_post_type(
		'cdn_faq',
		array(
			'labels'       => array(
				'name'          => 'Preguntas frecuentes',
				'singular_name' => 'Pregunta frecuente',
				'add_new_item'  => 'Añadir nueva pregunta',
				'edit_item'     => 'Editar pregunta',
			),
			'public'       => true,
			'has_archive'  => false,
			'rewrite'      => array( 'slug' => 'pregunta-frecuente' ),
			'menu_icon'    => 'dashicons-editor-help',
			'supports'     => array( 'title', 'editor' ),
			'show_in_rest' => true,
		)
	);

	// Ubicaciones / puntos de atención.
	register_post_type(
		'cdn_ubicacion',
		array(
			'labels'       => array(
				'name'          => 'Ubicaciones',
				'singular_name' => 'Ubicación',
				'add_new_item'  => 'Añadir ubicación',
				'edit_item'     => 'Editar ubicación',
			),
			'public'       => true,
			'has_archive'  => false,
			'rewrite'      => array( 'slug' => 'ubicacion' ),
			'menu_icon'    => 'dashicons-location-alt',
			'supports'     => array( 'title', 'editor', 'thumbnail' ),
			'show_in_rest' => true,
		)
	);

	// Nosotros: un único "post" con la sección completa (bloques editoriales).
	register_post_type(
		'cdn_nosotros',
		array(
			'labels'       => array(
				'name'          => 'Sección Nosotros',
				'singular_name' => 'Nosotros',
			),
			'public'       => false,
			'show_ui'      => true,
			'show_in_rest' => true,
			'menu_icon'    => 'dashicons-groups',
			'supports'     => array( 'title', 'editor', 'thumbnail' ),
			'rewrite'      => false,
		)
	);

	// Mensajes privados del formulario de contacto.
	register_post_type(
		'cdn_mensaje',
		array(
			'labels'       => array(
				'name'          => 'Mensajes de contacto',
				'singular_name' => 'Mensaje de contacto',
			),
			'public'       => false,
			'show_ui'      => true,
			'menu_icon'    => 'dashicons-email-alt',
			'supports'     => array( 'title', 'editor' ),
			'rewrite'      => false,
			'capabilities' => array( 'create_posts' => 'do_not_allow' ),
			'map_meta_cap' => true,
		)
	);
}

/**
 * Registra la taxonomía de áreas de servicio.
 */
function cdn_contenidos_registrar_taxonomia() {
	register_taxonomy(
		'cdn_area',
		array( 'cdn_servicio' ),
		array(
			'labels'            => array(
				'name'          => 'Áreas de servicio',
				'singular_name' => 'Área de servicio',
			),
			'hierarchical'      => true,
			'public'            => true,
			'show_in_rest'      => true,
			'show_admin_column' => true,
			'rewrite'           => array( 'slug' => 'area-servicio' ),
		)
	);
}

/**
 * Crea términos por defecto para el área (si no existen).
 */
function cdn_contenidos_areas_por_defecto() {
	$areas = cdn_contenidos_areas_default();
	foreach ( $areas as $slug => $datos ) {
		if ( ! term_exists( $datos['nombre'], 'cdn_area' ) ) {
			wp_insert_term( $datos['nombre'], 'cdn_area', array( 'slug' => $slug ) );
		}
	}
}

/**
 * Catálogo de áreas con color de acento asociado.
 */
function cdn_contenidos_areas_default() {
	return array(
		'acompanamiento' => array(
			'nombre' => 'Acompañamiento Preventivo y Correctivo',
			'color'  => '#0c6cb5',
		),
		'gestion'        => array(
			'nombre' => 'Gestión de Negocios',
			'color'  => '#1f8a70',
		),
		'eficiencia'     => array(
			'nombre' => 'Eficiencia y Sostenibilidad',
			'color'  => '#f08a24',
		),
		'vinculacion'    => array(
			'nombre' => 'Vinculación Empresarial',
			'color'  => '#7b5ea7',
		),
	);
}

/**
 * Devuelve el color (hex) de un término de área.
 */
function cdn_contenidos_color_area( $term_id ) {
	$color = get_term_meta( $term_id, 'cdn_color', true );
	return $color ? $color : '#0c6cb5';
}
