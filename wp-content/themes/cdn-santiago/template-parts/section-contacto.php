<?php
/**
 * Componente: Formulario de contacto centrado en el usuario.
 * Envío asíncrono a POST /cdn/v1/contacto con captcha, honeypot y validación.
 *
 * @package cdn-santiago
 */

$lista_servicios = array();
if ( post_type_exists( 'cdn_servicio' ) ) {
	$q = new WP_Query(
		array(
			'post_type'      => 'cdn_servicio',
			'posts_per_page' => 100,
			'post_status'    => 'publish',
			'orderby'        => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
			'order'          => 'ASC',
			'fields'         => 'ids',
		)
	);
	foreach ( $q->posts as $pid ) {
		$lista_servicios[ get_the_title( $pid ) ] = get_the_title( $pid );
	}
	wp_reset_postdata();
}
$servicio_preseleccionado = isset( $_GET['servicio'] ) ? sanitize_text_field( wp_unslash( $_GET['servicio'] ) ) : '';
?>
<section id="contacto" class="seccion" aria-labelledby="contacto-titulo" data-seccion="contacto">
	<div class="contenedor">
		<header class="seccion__cabecera seccion__cabecera--centrada">
			<p class="seccion__sello">Contacto</p>
			<h2 class="seccion__titulo" id="contacto-titulo">Conversemos sobre tu negocio</h2>
			<p class="seccion__subtitulo">Completa el formulario y una persona de nuestro equipo te contactará sin costo.</p>
		</header>

		<div class="contacto">
			<div class="contacto__formulario">
				<div class="aviso-formulario" id="aviso-formulario" role="status" aria-live="polite" hidden></div>

				<form id="form-contacto" class="formulario" method="post" action="<?php echo esc_url( rest_url( 'cdn/v1/contacto' ) ); ?>" novalidate>
					<p class="trampa-robot" aria-hidden="true">
						<label for="website">No rellenar este campo</label>
						<input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
					</p>

					<div class="formulario__grid">
						<div class="form-campo">
							<label for="campo-nombre">Nombre completo <span class="requerido">*</span></label>
							<input type="text" id="campo-nombre" name="nombre" autocomplete="name" maxlength="120" required>
							<span class="form-error" id="error-nombre" aria-hidden="true"></span>
						</div>
						<div class="form-campo">
							<label for="campo-correo">Correo electrónico <span class="requerido">*</span></label>
							<input type="email" id="campo-correo" name="correo" autocomplete="email" maxlength="120" required>
							<span class="form-error" id="error-correo" aria-hidden="true"></span>
						</div>
						<div class="form-campo">
							<label for="campo-telefono">Teléfono</label>
							<input type="tel" id="campo-telefono" name="telefono" autocomplete="tel" maxlength="25" placeholder="+56 9 ...">
							<span class="form-error" id="error-telefono" aria-hidden="true"></span>
						</div>
						<div class="form-campo">
							<label for="campo-servicio">Servicio de interés</label>
							<select id="campo-servicio" name="servicio">
								<option value="">Selecciona un servicio…</option>
								<?php foreach ( $lista_servicios as $servicio ) : ?>
									<option value="<?php echo esc_attr( $servicio ); ?>" <?php selected( $servicio_preseleccionado, $servicio ); ?>><?php echo esc_html( $servicio ); ?></option>
								<?php endforeach; ?>
								<option value="Otro / No lo sé">Otro / No lo sé</option>
							</select>
							<span class="form-error" id="error-servicio" aria-hidden="true"></span>
						</div>
					</div>

					<div class="form-campo">
						<label for="campo-mensaje">Cuéntanos tu consulta <span class="requerido">*</span></label>
						<textarea id="campo-mensaje" name="mensaje" rows="5" maxlength="2000" required></textarea>
						<span class="form-error" id="error-mensaje" aria-hidden="true"></span>
					</div>

					<?php if ( function_exists( 'cdn_captcha_render' ) ) : ?>
						<?php cdn_captcha_render(); ?>
						<span class="form-error" id="error-captcha" aria-hidden="true"></span>
					<?php endif; ?>

					<div class="formulario__pie">
						<p class="formulario__privacidad">Tus datos se usan solo para responder tu consulta. Al enviar aceptas nuestra política de privacidad.</p>
						<button type="submit" class="boton boton--principal">Enviar mensaje</button>
					</div>
					<noscript><p class="aviso-js">El envío requiere JavaScript. Escríbenos a <?php echo esc_html( cdn_ajuste( 'contacto_correo' ) ); ?>.</p></noscript>
				</form>
			</div>

			<aside class="contacto__info" aria-label="Datos de contacto">
				<h3>Datos de contacto</h3>
				<ul class="contacto__lista">
					<li><?php echo cdn_icono( 'ubicacion' ); ?><div><strong>Dirección</strong><span><?php echo esc_html( cdn_ajuste( 'contacto_direccion' ) ); ?></span></div></li>
					<li><?php echo cdn_icono( 'telefono' ); ?><div><strong>Teléfono</strong><a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', (string) cdn_ajuste( 'contacto_telefono' ) ) ); ?>"><?php echo esc_html( cdn_ajuste( 'contacto_telefono' ) ); ?></a></div></li>
					<li><?php echo cdn_icono( 'correo' ); ?><div><strong>Correo</strong><a href="mailto:<?php echo esc_attr( cdn_ajuste( 'contacto_correo' ) ); ?>"><?php echo esc_html( cdn_ajuste( 'contacto_correo' ) ); ?></a></div></li>
					<li><?php echo cdn_icono( 'horario' ); ?><div><strong>Horario</strong><span><?php echo esc_html( cdn_ajuste( 'contacto_horario' ) ); ?></span></div></li>
				</ul>
				<p class="contacto__nota">¿No sabes qué servicio necesitas? Escríbenos igual: hacemos un diagnóstico gratuito.</p>
			</aside>
		</div>
	</div>
</section>
