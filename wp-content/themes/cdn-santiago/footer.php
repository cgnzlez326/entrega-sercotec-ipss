<?php
/**
 * Cierre de contenido + pie de página y toolbar de accesibilidad.
 *
 * @package cdn-santiago
 */

?>
</main>

<footer class="pie">
	<div class="contenedor pie__columnas">
		<div class="pie__col">
			<img class="pie__logo" src="<?php echo esc_url( cdn_asset( 'img/logo-cdn.png' ) ); ?>" alt="Centros de Desarrollo de Negocios Sercotec" width="196" height="68" loading="lazy">
			<p>Apoyamos la sostenibilidad y competitividad de micro, pequeñas empresas y cooperativas de Santiago y Providencia.</p>
		</div>
		<div class="pie__col">
			<h2 class="pie__titulo">Contacto</h2>
			<ul class="pie__lista">
				<li><?php echo cdn_icono( 'ubicacion' ); ?><span><?php echo esc_html( cdn_ajuste( 'contacto_direccion' ) ); ?></span></li>
				<li><?php echo cdn_icono( 'telefono' ); ?><a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', (string) cdn_ajuste( 'contacto_telefono' ) ) ); ?>"><?php echo esc_html( cdn_ajuste( 'contacto_telefono' ) ); ?></a></li>
				<li><?php echo cdn_icono( 'correo' ); ?><a href="mailto:<?php echo esc_attr( cdn_ajuste( 'contacto_correo' ) ); ?>"><?php echo esc_html( cdn_ajuste( 'contacto_correo' ) ); ?></a></li>
				<li><?php echo cdn_icono( 'horario' ); ?><span><?php echo esc_html( cdn_ajuste( 'contacto_horario' ) ); ?></span></li>
			</ul>
		</div>
		<div class="pie__col">
			<h2 class="pie__titulo">Síguenos</h2>
			<ul class="pie__redes">
				<?php if ( cdn_ajuste( 'red_facebook' ) ) : ?>
					<li><a class="boton-red" href="<?php echo esc_url( cdn_ajuste( 'red_facebook' ) ); ?>" target="_blank" rel="noopener" aria-label="Facebook del centro"><?php echo cdn_icono( 'facebook' ); ?></a></li>
				<?php endif; ?>
				<?php if ( cdn_ajuste( 'red_instagram' ) ) : ?>
					<li><a class="boton-red" href="<?php echo esc_url( cdn_ajuste( 'red_instagram' ) ); ?>" target="_blank" rel="noopener" aria-label="Instagram del centro"><?php echo cdn_icono( 'instagram' ); ?></a></li>
				<?php endif; ?>
				<?php if ( cdn_ajuste( 'red_whatsapp' ) ) : ?>
					<li><a class="boton-red" href="<?php echo esc_url( cdn_ajuste( 'red_whatsapp' ) ); ?>" target="_blank" rel="noopener" aria-label="WhatsApp del centro"><?php echo cdn_icono( 'whatsapp' ); ?></a></li>
				<?php endif; ?>
			</ul>
		</div>
	</div>
	<div class="pie__legal">
		<div class="contenedor">
			<p>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>. Todos los derechos reservados.</p>
			<p class="pie__wp">Proyecto académico construido con <a href="https://wordpress.org" target="_blank" rel="noopener">WordPress</a>.</p>
		</div>
	</div>
</footer>

<button class="toolbar-accesibilidad__boton" id="boton-accesibilidad" aria-expanded="false" aria-controls="panel-accesibilidad">
	<span aria-hidden="true">&#9841;</span><span class="texto-accesible">Abrir barra de herramientas de accesibilidad</span>
</button>

<section class="toolbar-accesibilidad" id="panel-accesibilidad" aria-label="Opciones de accesibilidad" hidden>
	<h2 class="toolbar-accesibilidad__titulo">Accesibilidad</h2>
	<ul class="toolbar-accesibilidad__opciones">
		<li><button type="button" data-a11y="aumentar">Aumentar texto</button></li>
		<li><button type="button" data-a11y="disminuir">Disminuir texto</button></li>
		<li><button type="button" data-a11y="grises">Tonos de gris</button></li>
		<li><button type="button" data-a11y="contraste">Contraste negativo</button></li>
		<li><button type="button" data-a11y="subrayar">Subrayar enlaces</button></li>
		<li><button type="button" data-a11y="fuente">Fuente legible</button></li>
		<li><button type="button" data-a11y="restablecer">Restablecer</button></li>
	</ul>
</section>

<div class="ir-arriba" hidden>
	<button type="button" id="boton-arriba" aria-label="Volver arriba" aria-hidden="false">&#9650;<span class="texto-accesible">Volver arriba</span></button>
</div>

<?php wp_footer(); ?>
</body>
</html>
