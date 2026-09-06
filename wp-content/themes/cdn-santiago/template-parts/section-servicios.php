<?php
/**
 * Componente: Sección Servicios (tarjetas reutilizables vía REST /cdn/v1/servicios).
 *
 * @package cdn-santiago
 */
?>
<section id="servicios" class="seccion" aria-labelledby="servicios-titulo" data-seccion="servicios">
	<div class="contenedor">
		<header class="seccion__cabecera seccion__cabecera--centrada">
			<p class="seccion__sello">Servicios</p>
			<h2 class="seccion__titulo" id="servicios-titulo">¿Cómo te podemos ayudar?</h2>
			<p class="seccion__subtitulo">Servicios gratuitos de asesoría, capacitación y vinculación para tu negocio.</p>
		</header>

		<div class="servicios" id="contenido-servicios" data-area="servicios" aria-busy="true">
			<div class="filtros" role="group" aria-label="Filtrar servicios por área"></div>
			<?php cdn_plantilla_carga( 8 ); ?>
		</div>
		<noscript><p class="aviso-js">Para ver los servicios, activa JavaScript.</p></noscript>
	</div>
</section>
