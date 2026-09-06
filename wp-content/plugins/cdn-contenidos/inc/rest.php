<?php
/**
 * API REST personalizada del plugin: namespace `cdn/v1`.
 *
 * Endpoints públicos de lectura para poblar la landing de forma dinámica y un
 * endpoint protegido de contacto con validación/sanitización en servidor.
 *
 * @package cdn_contenidos
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * URL destacada de un post (imagen destacada o null).
 *
 * @param int  $post_id ID del post.
 * @param string $tamano Tamaño de imagen.
 * @return string
 */
function cdn_contenidos_imagen_destacada( $post_id, $tamano = 'medium_large' ) {
	$url = get_the_post_thumbnail_url( $post_id, $tamano );
	return $url ? $url : '';
}

/**
 * Prepara un servicio para la API.
 *
 * @param WP_Post $post Post de tipo cdn_servicio.
 * @return array
 */
function cdn_contenidos_preparar_servicio( $post ) {
	$areas  = get_the_terms( $post->ID, 'cdn_area' );
	$area   = null;
	if ( $areas && ! is_wp_error( $areas ) ) {
		$termo = reset( $areas );
		$area  = array(
			'slug'  => $termo->slug,
			'nombre' => $termo->name,
			'color' => cdn_contenidos_color_area( $termo->term_id ),
		);
	}
	$descripcion = ! empty( $post->post_excerpt ) ? $post->post_excerpt : wp_strip_all_tags( $post->post_content );
	return array(
		'id'          => $post->ID,
		'slug'        => $post->post_name,
		'titulo'      => get_the_title( $post ),
		'descripcion' => trim( $descripcion ),
		'imagen'      => cdn_contenidos_imagen_destacada( $post->ID ),
		'area'        => $area,
		'enlace'      => get_permalink( $post->ID ),
	);
}

/**
 * Prepara un testimonio para la API.
 *
 * @param WP_Post $post Post de tipo cdn_testimonio.
 * @return array
 */
function cdn_contenidos_preparar_testimonio( $post ) {
	return array(
		'id'       => $post->ID,
		'nombre'   => get_the_title( $post ),
		'cargo'    => get_post_meta( $post->ID, '_cdn_cargo', true ),
		'empresa'  => get_post_meta( $post->ID, '_cdn_empresa', true ),
		'nota'     => (int) get_post_meta( $post->ID, '_cdn_nota', true ),
		'texto'    => wp_strip_all_tags( $post->post_content ),
		'imagen'   => cdn_contenidos_imagen_destacada( $post->ID, 'thumbnail' ),
	);
}

/**
 * Prepara una pregunta frecuente.
 *
 * @param WP_Post $post Post de tipo cdn_faq.
 * @return array
 */
function cdn_contenidos_preparar_faq( $post ) {
	return array(
		'id'       => $post->ID,
		'pregunta' => get_the_title( $post ),
		'respuesta'=> wp_strip_all_tags( $post->post_content ),
		'grupo'    => get_post_meta( $post->ID, '_cdn_grupo', true ),
		'orden'    => (int) get_post_meta( $post->ID, '_cdn_orden', true ),
	);
}

/**
 * Prepara una ubicación.
 *
 * @param WP_Post $post Post de tipo cdn_ubicacion.
 * @return array
 */
function cdn_contenidos_preparar_ubicacion( $post ) {
	return array(
		'id'         => $post->ID,
		'titulo'     => get_the_title( $post ),
		'tipo'       => get_post_meta( $post->ID, '_cdn_tipo', true ) ?: 'punto',
		'direccion'  => get_post_meta( $post->ID, '_cdn_direccion', true ),
		'telefono'   => get_post_meta( $post->ID, '_cdn_telefono', true ),
		'correo'     => get_post_meta( $post->ID, '_cdn_correo', true ),
		'horario'    => get_post_meta( $post->ID, '_cdn_horario', true ),
		'responsable'=> get_post_meta( $post->ID, '_cdn_responsable', true ),
		'orden'      => (int) get_post_meta( $post->ID, '_cdn_orden', true ),
	);
}

/**
 * Convierte "Nombre :: Descripción" de una línea.
 *
 * @param string $linea Línea cruda.
 * @return array
 */
function cdn_contenidos_parsear_separador( $linea ) {
	$linea = trim( $linea );
	if ( '' === $linea ) {
		return null;
	}
	if ( strpos( $linea, '::' ) !== false ) {
		$partes = array_map( 'trim', explode( '::', $linea, 2 ) );
		return array( 'nombre' => $partes[0], 'descripcion' => isset( $partes[1] ) ? $partes[1] : '' );
	}
	return array( 'nombre' => $linea, 'descripcion' => '' );
}

/**
 * Preparar contenido de la sección Nosotros desde el último post de su CPT.
 *
 * @return array
 */
function cdn_contenidos_preparar_nosotros() {
	$query = new WP_Query(
		array(
			'post_type'      => 'cdn_nosotros',
			'posts_per_page' => 1,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'post_status'    => 'publish',
		)
	);
	$vacio = array(
		'lead'     => array(),
		'proposito'=> '',
		'dirigido' => array(),
		'metodo'   => array(),
		'stats'    => array(),
		'alianzas' => array(),
	);
	if ( ! $query->have_posts() ) {
		return $vacio;
	}
	$post = $query->posts[0];

	$lead = get_post_meta( $post->ID, '_cdn_lead', true );
	$lead_parrafos = array_values( array_filter( array_map( 'trim', preg_split( '/\r?\n\s*\r?\n/', (string) $lead ) ) ) );

	$stats = array();
	$stats_raw = get_post_meta( $post->ID, '_cdn_stats', true );
	foreach ( preg_split( '/\r?\n/', (string) $stats_raw ) as $linea ) {
		$linea = trim( $linea );
		if ( '' === $linea ) {
			continue;
		}
		$obj = json_decode( $linea, true );
		if ( is_array( $obj ) && isset( $obj['valor'] ) ) {
			$stats[] = array(
				'prefijo' => isset( $obj['prefijo'] ) ? $obj['prefijo'] : '',
				'valor'  => isset( $obj['valor'] ) ? $obj['valor'] : 0,
				'sufijo' => isset( $obj['sufijo'] ) ? $obj['sufijo'] : '',
				'texto'  => isset( $obj['texto'] ) ? $obj['texto'] : '',
			);
		}
	}

	$lineas_a = function ( $meta ) {
		$salida = array();
		foreach ( preg_split( '/\r?\n/', (string) $meta ) as $linea ) {
			$linea = trim( $linea );
			if ( '' !== $linea ) {
				$salida[] = $linea;
			}
		}
		return $salida;
	};

	$alianzas = array();
	foreach ( $lineas_a( get_post_meta( $post->ID, '_cdn_alianzas', true ) ) as $linea ) {
		$par = cdn_contenidos_parsear_separador( $linea );
		if ( $par ) {
			$alianzas[] = $par;
		}
	}

	return array(
		'lead'      => $lead_parrafos,
		'proposito' => wp_strip_all_tags( $post->post_content ),
		'dirigido'  => $lineas_a( get_post_meta( $post->ID, '_cdn_dirigido', true ) ),
		'metodo'    => $lineas_a( get_post_meta( $post->ID, '_cdn_metodo', true ) ),
		'stats'     => $stats,
		'alianzas'  => $alianzas,
	);
}

/**
 * Lista de ubicaciones ordenadas.
 *
 * @return array
 */
function cdn_contenidos_ubicaciones() {
	$query = new WP_Query(
		array(
			'post_type'      => 'cdn_ubicacion',
			'posts_per_page' => 30,
			'post_status'    => 'publish',
			'orderby'        => 'meta_value_num',
			'meta_key'       => '_cdn_orden',
			'order'          => 'ASC',
		)
	);
	$items = array();
	foreach ( $query->posts as $post ) {
		$items[] = cdn_contenidos_preparar_ubicacion( $post );
	}
	return $items;
}

/**
 * Registra las rutas del namespace cdn/v1.
 */
function cdn_contenidos_rest_rutas() {
	register_rest_route(
		'cdn/v1',
		'/sitio',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'cdn_contenidos_rest_sitio',
			'permission_callback' => '__return_true',
		)
	);

	register_rest_route(
		'cdn/v1',
		'/nosotros',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => function () {
				return rest_ensure_response( cdn_contenidos_preparar_nosotros() );
			},
			'permission_callback' => '__return_true',
		)
	);

	register_rest_route(
		'cdn/v1',
		'/servicios',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'cdn_contenidos_rest_servicios',
			'permission_callback' => '__return_true',
			'args'                => array(
				'area' => array(
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_key',
					'required'          => false,
				),
			),
		)
	);

	register_rest_route(
		'cdn/v1',
		'/testimonios',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'cdn_contenidos_rest_testimonios',
			'permission_callback' => '__return_true',
		)
	);

	register_rest_route(
		'cdn/v1',
		'/faqs',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'cdn_contenidos_rest_faqs',
			'permission_callback' => '__return_true',
		)
	);

	register_rest_route(
		'cdn/v1',
		'/contacto',
		array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => 'cdn_contenidos_rest_contacto',
			'permission_callback' => '__return_true',
		)
	);
}
add_action( 'rest_api_init', 'cdn_contenidos_rest_rutas' );

/**
 * GET /cdn/v1/sitio — hero, contacto, redes y ubicaciones.
 *
 * @return WP_REST_Response
 */
function cdn_contenidos_rest_sitio() {
	$aj = cdn_contenidos_ajuste();
	$hero_imagen = $aj['hero_imagen'];
	if ( is_numeric( $hero_imagen ) ) {
		$hero_imagen = wp_get_attachment_image_url( (int) $hero_imagen, 'full' );
	}
	return rest_ensure_response(
		array(
			'hero'      => array(
				'titulo'         => $aj['hero_titulo'],
				'region'         => $aj['hero_region'],
				'comunas'        => $aj['hero_comunas'],
				'texto'          => $aj['hero_texto'],
				'btn_principal'  => $aj['hero_btn_principal'],
				'btn_secundario' => $aj['hero_btn_secundario'],
				'imagen'         => $hero_imagen,
			),
			'contacto'  => array(
				'telefono'  => $aj['contacto_telefono'],
				'correo'    => $aj['contacto_correo'],
				'direccion' => $aj['contacto_direccion'],
				'horario'   => $aj['contacto_horario'],
			),
			'redes'     => array(
				'facebook'  => $aj['red_facebook'],
				'instagram' => $aj['red_instagram'],
				'whatsapp'  => $aj['red_whatsapp'],
			),
			'ubicaciones' => cdn_contenidos_ubicaciones(),
		)
	);
}

/**
 * GET /cdn/v1/servicios — lista de tarjetas de servicio, filtrable por área.
 *
 * @param WP_REST_Request $request Request.
 * @return WP_REST_Response
 */
function cdn_contenidos_rest_servicios( $request ) {
	$args = array(
		'post_type'      => 'cdn_servicio',
		'posts_per_page' => 50,
		'post_status'    => 'publish',
		'orderby'        => 'menu_order',
		'order'          => 'ASC',
	);
	$area = isset( $request['area'] ) ? $request['area'] : '';
	if ( '' !== $area ) {
		$args['tax_query'] = array(
			array(
				'taxonomy' => 'cdn_area',
				'field'    => 'slug',
				'terms'    => $area,
			),
		);
	}
	$query = new WP_Query( $args );
	$items = array();
	foreach ( $query->posts as $post ) {
		$items[] = cdn_contenidos_preparar_servicio( $post );
	}
	return rest_ensure_response( $items );
}

/**
 * GET /cdn/v1/testimonios.
 *
 * @return WP_REST_Response
 */
function cdn_contenidos_rest_testimonios() {
	$query = new WP_Query(
		array(
			'post_type'      => 'cdn_testimonio',
			'posts_per_page' => 30,
			'post_status'    => 'publish',
			'orderby'        => 'date',
			'order'          => 'DESC',
		)
	);
	$items = array();
	foreach ( $query->posts as $post ) {
		$items[] = cdn_contenidos_preparar_testimonio( $post );
	}
	return rest_ensure_response( $items );
}

/**
 * GET /cdn/v1/faqs.
 *
 * @return WP_REST_Response
 */
function cdn_contenidos_rest_faqs() {
	$query = new WP_Query(
		array(
			'post_type'      => 'cdn_faq',
			'posts_per_page' => 100,
			'post_status'    => 'publish',
			'orderby'        => 'meta_value_num',
			'meta_key'       => '_cdn_orden',
			'order'          => 'ASC',
		)
	);
	$items = array();
	foreach ( $query->posts as $post ) {
		$items[] = cdn_contenidos_preparar_faq( $post );
	}
	return rest_ensure_response( $items );
}

/**
 * POST /cdn/v1/contacto — guarda el mensaje en un CPT privado con seguridad.
 *
 * @param WP_REST_Request $request Request.
 * @return WP_REST_Response
 */
function cdn_contenidos_rest_contacto( $request ) {
	// Rate limit por IP (máx. 5 envíos / hora).
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '0.0.0.0';
	$limite_clave = 'cdn_contacto_' . md5( $ip );
	$enviados = (int) get_transient( $limite_clave );
	if ( $enviados >= 5 ) {
		return new WP_REST_Response(
			array(
				'success' => false,
				'code'    => 'demasiados_envios',
				'message' => 'Has superado el límite de envíos. Intenta nuevamente más tarde.',
			),
			429
		);
	}

	$params = $request->get_params();
	$website = isset( $params['website'] ) ? sanitize_text_field( (string) $params['website'] ) : '';

	// Honeypot: si un bot rellena el campo oculto, respondemos éxito sin guardar.
	if ( '' !== $website ) {
		return new WP_REST_Response(
			array(
				'success' => true,
				'message' => 'Gracias, tu mensaje ha sido enviado.',
			),
			200
		);
	}

	// Captcha aritmético + time-trap.
	$cap_ok = cdn_captcha_verificar(
		isset( $params['cdn_cap_r'] ) ? (string) $params['cdn_cap_r'] : '',
		isset( $params['cdn_cap_a'] ) ? (string) $params['cdn_cap_a'] : '',
		isset( $params['cdn_cap_b'] ) ? (string) $params['cdn_cap_b'] : '',
		isset( $params['cdn_cap_salt'] ) ? (string) $params['cdn_cap_salt'] : '',
		isset( $params['cdn_cap_t'] ) ? (string) $params['cdn_cap_t'] : '',
		isset( $params['cdn_cap_tok'] ) ? (string) $params['cdn_cap_tok'] : ''
	);
	if ( ! $cap_ok ) {
		return new WP_REST_Response(
			array(
				'success' => false,
				'code'    => 'captcha_invalido',
				'message' => 'La verificación de seguridad no es válida. Intenta nuevamente.',
			),
			400
		);
	}

	// Validación del lado servidor.
	$nombre  = isset( $params['nombre'] ) ? sanitize_text_field( (string) $params['nombre'] ) : '';
	$correo  = isset( $params['correo'] ) ? sanitize_email( (string) $params['correo'] ) : '';
	$telefono = isset( $params['telefono'] ) ? sanitize_text_field( (string) $params['telefono'] ) : '';
	$servicio = isset( $params['servicio'] ) ? sanitize_text_field( (string) $params['servicio'] ) : '';
	$mensaje  = isset( $params['mensaje'] ) ? sanitize_textarea_field( (string) $params['mensaje'] ) : '';

	$errores = array();
	if ( mb_strlen( $nombre ) < 2 || mb_strlen( $nombre ) > 120 ) {
		$errores['nombre'] = 'Ingresa tu nombre (entre 2 y 120 caracteres).';
	}
	if ( ! is_email( $correo ) ) {
		$errores['correo'] = 'Ingresa un correo electrónico válido.';
	}
	if ( $telefono !== '' && ! preg_match( '/^[+()\-\s0-9]{7,25}$/', $telefono ) ) {
		$errores['telefono'] = 'El teléfono no tiene un formato válido.';
	}
	if ( $servicio !== '' && mb_strlen( $servicio ) > 200 ) {
		$errores['servicio'] = 'El servicio seleccionado no es válido.';
	}
	if ( mb_strlen( $mensaje ) < 10 || mb_strlen( $mensaje ) > 2000 ) {
		$errores['mensaje'] = 'El mensaje debe tener entre 10 y 2000 caracteres.';
	}

	if ( ! empty( $errores ) ) {
		return new WP_REST_Response(
			array(
				'success' => false,
				'code'    => 'validacion',
				'message' => 'Revisa los campos marcados.',
				'errores' => $errores,
			),
			422
		);
	}

	// Almacenamiento en CPT privado.
	$post_id = wp_insert_post(
		array(
			'post_type'    => 'cdn_mensaje',
			'post_status'  => 'private',
			'post_title'   => sprintf( 'Contacto de %s (%s)', $nombre, $correo ),
			'post_content' => $mensaje,
		),
		true
	);
	if ( is_wp_error( $post_id ) ) {
		return new WP_REST_Response(
			array(
				'success' => false,
				'code'    => 'error_interno',
				'message' => 'No se pudo procesar tu mensaje. Intenta nuevamente.',
			),
			500
		);
	}

	update_post_meta( $post_id, 'cdn_msg_telefono', $telefono );
	update_post_meta( $post_id, 'cdn_msg_servicio', $servicio );
	update_post_meta( $post_id, 'cdn_msg_ip', $ip );
	update_post_meta( $post_id, 'cdn_msg_user_agent', isset( $_SERVER['HTTP_USER_AGENT'] ) ? substr( sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ), 0, 255 ) : '' );

	set_transient( $limite_clave, $enviados + 1, HOUR_IN_SECONDS );

	return new WP_REST_Response(
		array(
			'success' => true,
			'message' => 'Gracias, tu mensaje ha sido enviado. Te contactaremos a la brevedad.',
		),
		201
	);
}
