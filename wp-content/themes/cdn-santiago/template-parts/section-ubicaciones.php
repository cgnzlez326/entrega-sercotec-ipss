<?php
/**
 * Componente: Ubicaciones y puntos de atención (vía REST /cdn/v1/sitio).
 *
 * @package cdn-santiago
 */
?>
<section id="ubicaciones" class="seccion seccion--gris" aria-labelledby="ubicaciones-titulo" data-seccion="ubicaciones">
	<div class="contenedor">
		<header class="seccion__cabecera seccion__cabecera--centrada">
			<p class="seccion__sello">Ubicaciones</p>
			<h2 class="seccion__titulo" id="ubicaciones-titulo">Dónde encontrarnos</h2>
			<p class="seccion__subtitulo">Visítanos en nuestro centro principal, satélite o en los puntos de atención móvil.</p>
		</header>

		<div class="ubicaciones" id="contenido-ubicaciones" data-area="ubicaciones" aria-busy="true">
			<?php cdn_plantilla_carga( 3 ); ?>
		</div>
		<noscript><p class="aviso-js">Para ver las ubicaciones, activa JavaScript.</p></noscript>
	</div>
</section>
