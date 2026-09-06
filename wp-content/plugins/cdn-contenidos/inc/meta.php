<?php
/**
 * Meta boxes y metadatos del plugin CDN Contenidos.
 * Un solo descriptor por CPT mantiene la lógica simple y reutilizable.
 *
 * @package cdn_contenidos
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Definición declarativa de los campos por tipo de contenido.
 * type: text | textarea | number | select | json
 */
function cdn_contenidos_campos() {
	return array(
		'cdn_testimonio' => array(
			'_cdn_cargo'    => array( 'label' => 'Cargo', 'type' => 'text', 'max' => 120 ),
			'_cdn_empresa'  => array( 'label' => 'Empresa / Rubro', 'type' => 'text', 'max' => 120 ),
			'_cdn_nota'     => array( 'label' => 'Valoración (1 a 5)', 'type' => 'select', 'opciones' => array( '5', '4', '3', '2', '1' ) ),
		),
		'cdn_faq'        => array(
			'_cdn_grupo'    => array( 'label' => 'Grupo', 'type' => 'text', 'max' => 80 ),
			'_cdn_orden'    => array( 'label' => 'Orden (menor primero)', 'type' => 'number' ),
		),
		'cdn_ubicacion'  => array(
			'_cdn_tipo'       => array(
				'label'    => 'Tipo',
				'type'     => 'select',
				'opciones' => array( 'principal' => 'Centro principal', 'satelite' => 'Centro satélite', 'punto' => 'Punto de atención', 'consultorio' => 'Consultorio empresarial' ),
			),
			'_cdn_direccion'  => array( 'label' => 'Dirección', 'type' => 'text', 'max' => 200 ),
			'_cdn_telefono'   => array( 'label' => 'Teléfono', 'type' => 'text', 'max' => 60 ),
			'_cdn_correo'     => array( 'label' => 'Correo', 'type' => 'text', 'max' => 120 ),
			'_cdn_horario'    => array( 'label' => 'Horario de atención', 'type' => 'text', 'max' => 160 ),
			'_cdn_responsable'=> array( 'label' => 'Asesor/a responsable', 'type' => 'text', 'max' => 160 ),
			'_cdn_orden'      => array( 'label' => 'Orden (menor primero)', 'type' => 'number' ),
		),
		'cdn_nosotros'   => array(
			'_cdn_lead'      => array( 'label' => 'Texto introductorio (párrafos separados por línea en blanco)', 'type' => 'textarea' ),
			'_cdn_dirigido'  => array( 'label' => 'A quién está dirigido (una línea por ítem)', 'type' => 'textarea' ),
			'_cdn_metodo'    => array( 'label' => 'Cómo acompañamos (una línea por ítem)', 'type' => 'textarea' ),
			'_cdn_stats'     => array( 'label' => 'Cifras destacadas (JSON por línea: {"valor":3200,"sufijo":"+","texto":"empresas atendidas"})', 'type' => 'textarea' ),
			'_cdn_alianzas'  => array( 'label' => 'Alianzas estratégicas (una línea por ítem: "Nombre :: Descripción")', 'type' => 'textarea' ),
		),
	);
}

/**
 * Adjunta los meta boxes a los CPT correspondientes.
 */
function cdn_contenidos_meta_boxes() {
	foreach ( cdn_contenidos_campos() as $cpt => $campos ) {
		add_meta_box(
			'cdn_campos_' . $cpt,
			'Campos del contenido',
			'cdn_contenidos_render_meta_box',
			$cpt,
			'normal',
			'high',
			array( 'campos' => $campos )
		);
	}
}
add_action( 'add_meta_boxes', 'cdn_contenidos_meta_boxes' );

/**
 * Render del meta box.
 *
 * @param WP_Post $post  Post actual.
 * @param array   $args  Argumentos con los campos.
 */
function cdn_contenidos_render_meta_box( $post, $args ) {
	wp_nonce_field( 'cdn_contenidos_meta', 'cdn_contenidos_nonce' );
	$campos = $args['args']['campos'];
	echo '<table class="form-table">';
	foreach ( $campos as $key => $campo ) {
		$valor  = get_post_meta( $post->ID, $key, true );
		$id     = 'cdn_field_' . sanitize_key( $key );
		echo '<tr><th scope="row"><label for="' . esc_attr( $id ) . '">' . esc_html( $campo['label'] ) . '</label></th><td>';
		switch ( $campo['type'] ) {
			case 'textarea':
				echo '<textarea class="large-text" rows="6" id="' . esc_attr( $id ) . '" name="' . esc_attr( $key ) . '">' . esc_textarea( $valor ) . '</textarea>';
				break;
			case 'select':
				echo '<select id="' . esc_attr( $id ) . '" name="' . esc_attr( $key ) . '">';
				foreach ( $campo['opciones'] as $opc_val => $opc_label ) {
					$etiqueta = is_numeric( $opc_val ) ? $opc_label : $opc_label;
					$seleccionado = selected( (string) $valor, (string) $opc_val, false );
					echo '<option value="' . esc_attr( $opc_val ) . '"' . $seleccionado . '>' . esc_html( $etiqueta ) . '</option>';
				}
				echo '</select>';
				break;
			case 'number':
				echo '<input type="number" class="small-text" id="' . esc_attr( $id ) . '" name="' . esc_attr( $key ) . '" value="' . esc_attr( $valor ) . '" min="0" step="1">';
				break;
			default:
				echo '<input type="text" class="large-text" id="' . esc_attr( $id ) . '" name="' . esc_attr( $key ) . '" value="' . esc_attr( $valor ) . '" maxlength="' . esc_attr( $campo['max'] ) . '">';
		}
		echo '</td></tr>';
	}
	echo '</table>';
}

/**
 * Guarda los metadatos al guardar el post.
 *
 * @param int $post_id ID del post.
 */
function cdn_contenidos_guardar_meta( $post_id ) {
	if ( ! isset( $_POST['cdn_contenidos_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['cdn_contenidos_nonce'] ), 'cdn_contenidos_meta' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$campos = cdn_contenidos_campos();
	$post_type = get_post_type( $post_id );
	if ( empty( $campos[ $post_type ] ) ) {
		return;
	}
	foreach ( $campos[ $post_type ] as $key => $campo ) {
		if ( ! isset( $_POST[ $key ] ) ) {
			continue;
		}
		$valor = wp_unslash( $_POST[ $key ] );
		if ( 'textarea' === $campo['type'] ) {
			$limpio = sanitize_textarea_field( $valor );
		} elseif ( 'number' === $campo['type'] ) {
			$limpio = absint( $valor );
		} elseif ( 'select' === $campo['type'] ) {
			$limpio = sanitize_key( (string) $valor );
		} else {
			$limpio = sanitize_text_field( $valor );
		}
		if ( '' === $limpio ) {
			delete_post_meta( $post_id, $key );
		} else {
			update_post_meta( $post_id, $key, $limpio );
		}
	}
}
add_action( 'save_post', 'cdn_contenidos_guardar_meta' );

/**
 * Expone los metadatos de cada CPT en la REST API (wp/v2) para gestión editorial.
 */
function cdn_contenidos_rest_meta() {
	$tipos = array( 'cdn_testimonio', 'cdn_faq', 'cdn_ubicacion', 'cdn_nosotros', 'cdn_mensaje' );
	$campos = cdn_contenidos_campos();
	foreach ( $tipos as $tipo ) {
		if ( empty( $campos[ $tipo ] ) ) {
			continue;
		}
		foreach ( $campos[ $tipo ] as $key => $campo ) {
			$tipo_schema = ( 'number' === $campo['type'] ) ? 'integer' : 'string';
			$sanitize    = ( 'textarea' === $campo['type'] ) ? 'sanitize_textarea_field' : 'sanitize_text_field';
			register_post_meta(
				$tipo,
				$key,
				array(
					'show_in_rest'      => true,
					'single'            => true,
					'type'              => $tipo_schema,
					'sanitize_callback' => $sanitize,
					'auth_callback'     => function () {
						return current_user_can( 'edit_posts' );
					},
				)
			);
		}
	}
}
add_action( 'init', 'cdn_contenidos_rest_meta', 20 );

/**
 * Registra metadatos de color en los términos de área y los expone en REST.
 */
function cdn_contenidos_term_meta() {
	register_term_meta(
		'cdn_area',
		'cdn_color',
		array(
			'type'              => 'string',
			'single'            => true,
			'sanitize_callback' => 'sanitize_hex_color',
			'show_in_rest'      => true,
		)
	);
}
add_action( 'init', 'cdn_contenidos_term_meta', 20 );
