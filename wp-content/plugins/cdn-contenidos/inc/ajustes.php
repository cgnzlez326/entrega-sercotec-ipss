<?php
/**
 * Ajustes del sitio (hero, contacto y redes). Un solo option array `cdn_ajustes`.
 *
 * @package cdn_contenidos
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Valores por defecto (Ficha técnica del cliente).
 */
function cdn_contenidos_ajustes_default() {
	return array(
		// Hero.
		'hero_titulo'        => 'Centro de Desarrollo de Negocios Sercotec Santiago',
		'hero_region'        => 'Región Metropolitana',
		'hero_comunas'       => 'Santiago y Providencia',
		'hero_texto'         => 'Apoyo y acompañamiento gratuito para micro, pequeñas empresas y cooperativas. Asesoría técnica, capacitación y vinculación personalizada, con un equipo experto con foco en resultados.',
		'hero_btn_principal' => 'Solicita una asesoría gratuita',
		'hero_btn_secundario'=> 'Conoce nuestros servicios',
		'hero_imagen'        => '',
		// Contacto.
		'contacto_telefono'  => '+(56) 9 3927 5633',
		'contacto_correo'    => 'centro.santiago@centrossercotec.cl',
		'contacto_direccion' => 'Manuel Rodríguez Sur 749, Santiago (Metro Toesca)',
		'contacto_horario'   => 'Lunes a Viernes, 9:00 a 18:00 hrs.',
		'red_facebook'       => 'https://www.facebook.com/centrodnsantiago',
		'red_instagram'      => 'https://www.instagram.com/centrodnsantiago/',
		'red_whatsapp'       => 'https://wa.me/56939275633',
	);
}

/**
 * Devuelve un ajuste o todos.
 *
 * @param string|null $key Clave del ajuste.
 * @return mixed
 */
function cdn_contenidos_ajuste( $key = null ) {
	$opts = wp_parse_args( get_option( 'cdn_ajustes', array() ), cdn_contenidos_ajustes_default() );
	if ( null === $key ) {
		return $opts;
	}
	return isset( $opts[ $key ] ) ? $opts[ $key ] : '';
}

/**
 * Agrega la página de ajustes bajo "CDN Contenidos".
 */
function cdn_contenidos_menu_ajustes() {
	add_options_page(
		'Contenido del sitio CDN',
		'Contenido del sitio CDN',
		'manage_options',
		'cdn-ajustes',
		'cdn_contenidos_render_ajustes'
	);
}
add_action( 'admin_menu', 'cdn_contenidos_menu_ajustes' );

/**
 * Registra la opción.
 */
function cdn_contenidos_registrar_ajustes() {
	register_setting( 'cdn_ajustes_grupo', 'cdn_ajustes', 'cdn_contenidos_sanitizar_ajustes' );
}
add_action( 'admin_init', 'cdn_contenidos_registrar_ajustes' );

/**
 * Sanitización de los ajustes.
 *
 * @param array $in Entrada.
 * @return array
 */
function cdn_contenidos_sanitizar_ajustes( $in ) {
	$salida = array();
	$def    = cdn_contenidos_ajustes_default();
	foreach ( $def as $clave => $vacias ) {
		$valor = isset( $in[ $clave ] ) ? $in[ $clave ] : '';
		if ( false !== strpos( $clave, 'red_' ) || false !== strpos( $clave, 'correo' ) ) {
			$salida[ $clave ] = esc_url_raw( $valor );
		} elseif ( 'hero_texto' === $clave ) {
			$salida[ $clave ] = sanitize_textarea_field( $valor );
		} else {
			$salida[ $clave ] = sanitize_text_field( $valor );
		}
	}
	return $salida;
}

/**
 * Render de la página de ajustes.
 */
function cdn_contenidos_render_ajustes() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$o = cdn_contenidos_ajuste();
	?>
	<div class="wrap">
		<h1>Contenido del sitio CDN Santiago</h1>
		<p>Los campos de la portada y el bloque de contacto se muestran en la landing y se consumen desde la API REST <code>/wp-json/cdn/v1/sitio</code>.</p>
		<form method="post" action="options.php">
			<?php settings_fields( 'cdn_ajustes_grupo' ); ?>
			<h2 class="title">Hero / portada</h2>
			<table class="form-table" role="presentation">
				<tr><th><label for="hero_titulo">Título principal</label></th><td><input class="large-text" id="hero_titulo" name="cdn_ajustes[hero_titulo]" value="<?php echo esc_attr( $o['hero_titulo'] ); ?>"></td></tr>
				<tr><th><label for="hero_region">Región</label></th><td><input class="large-text" id="hero_region" name="cdn_ajustes[hero_region]" value="<?php echo esc_attr( $o['hero_region'] ); ?>"></td></tr>
				<tr><th><label for="hero_comunas">Comunas atendidas</label></th><td><input class="large-text" id="hero_comunas" name="cdn_ajustes[hero_comunas]" value="<?php echo esc_attr( $o['hero_comunas'] ); ?>"></td></tr>
				<tr><th><label for="hero_texto">Texto de bienvenida</label></th><td><textarea class="large-text" rows="4" id="hero_texto" name="cdn_ajustes[hero_texto]"><?php echo esc_textarea( $o['hero_texto'] ); ?></textarea></td></tr>
				<tr><th><label for="hero_btn_principal">Texto botón principal</label></th><td><input class="large-text" id="hero_btn_principal" name="cdn_ajustes[hero_btn_principal]" value="<?php echo esc_attr( $o['hero_btn_principal'] ); ?>"></td></tr>
				<tr><th><label for="hero_btn_secundario">Texto botón secundario</label></th><td><input class="large-text" id="hero_btn_secundario" name="cdn_ajustes[hero_btn_secundario]" value="<?php echo esc_attr( $o['hero_btn_secundario'] ); ?>"></td></tr>
			</table>
			<h2 class="title">Contacto y redes</h2>
			<table class="form-table" role="presentation">
				<tr><th><label for="contacto_telefono">Teléfono</label></th><td><input class="large-text" id="contacto_telefono" name="cdn_ajustes[contacto_telefono]" value="<?php echo esc_attr( $o['contacto_telefono'] ); ?>"></td></tr>
				<tr><th><label for="contacto_correo">Correo</label></th><td><input type="email" class="large-text" id="contacto_correo" name="cdn_ajustes[contacto_correo]" value="<?php echo esc_attr( $o['contacto_correo'] ); ?>"></td></tr>
				<tr><th><label for="contacto_direccion">Dirección</label></th><td><input class="large-text" id="contacto_direccion" name="cdn_ajustes[contacto_direccion]" value="<?php echo esc_attr( $o['contacto_direccion'] ); ?>"></td></tr>
				<tr><th><label for="contacto_horario">Horario</label></th><td><input class="large-text" id="contacto_horario" name="cdn_ajustes[contacto_horario]" value="<?php echo esc_attr( $o['contacto_horario'] ); ?>"></td></tr>
				<tr><th><label for="red_facebook">Facebook</label></th><td><input type="url" class="large-text" id="red_facebook" name="cdn_ajustes[red_facebook]" value="<?php echo esc_attr( $o['red_facebook'] ); ?>"></td></tr>
				<tr><th><label for="red_instagram">Instagram</label></th><td><input type="url" class="large-text" id="red_instagram" name="cdn_ajustes[red_instagram]" value="<?php echo esc_attr( $o['red_instagram'] ); ?>"></td></tr>
				<tr><th><label for="red_whatsapp">WhatsApp</label></th><td><input type="url" class="large-text" id="red_whatsapp" name="cdn_ajustes[red_whatsapp]" value="<?php echo esc_attr( $o['red_whatsapp'] ); ?>"></td></tr>
			</table>
			<?php submit_button( 'Guardar cambios' ); ?>
		</form>
	</div>
	<?php
}
