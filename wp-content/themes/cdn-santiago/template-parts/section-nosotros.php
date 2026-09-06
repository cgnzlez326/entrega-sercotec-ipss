<?php
/**
 * Componente: Sección Nosotros (contenido dinámico vía REST /cdn/v1/nosotros).
 *
 * @package cdn-santiago
 */
?>
<section id="nosotros" class="seccion seccion--gris" aria-labelledby="nosotros-titulo" data-seccion="nosotros">
	<div class="contenedor">
		<header class="seccion__cabecera seccion__cabecera--centrada">
			<p class="seccion__sello">Nosotros</p>
			<h2 class="seccion__titulo" id="nosotros-titulo">Acerca del Centro</h2>
			<p class="seccion__subtitulo">Conoce quiénes somos y a quién apoyamos.</p>
		</header>
		<div class="nosotros" id="contenido-nosotros" data-area="nosotros" aria-busy="true">
			<?php cdn_plantilla_carga( 4 ); ?>
		</div>
		<noscript><p class="aviso-js">Para ver esta sección, activa JavaScript o escribe a <?php echo esc_html( cdn_ajuste( 'contacto_correo' ) ); ?>.</p></noscript>
	</div>
</section>
