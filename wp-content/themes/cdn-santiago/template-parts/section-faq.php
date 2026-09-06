<?php
/**
 * Componente: Preguntas frecuentes (acordeón vía REST /cdn/v1/faqs).
 *
 * @package cdn-santiago
 */
?>
<section id="preguntas" class="seccion" aria-labelledby="preguntas-titulo" data-seccion="preguntas">
	<div class="contenedor contenedor--angosto">
		<header class="seccion__cabecera seccion__cabecera--centrada">
			<p class="seccion__sello">Preguntas frecuentes</p>
			<h2 class="seccion__titulo" id="preguntas-titulo">Resolvemos tus dudas</h2>
			<p class="seccion__subtitulo">Información útil antes de tu primera visita.</p>
		</header>

		<div class="faq" id="contenido-faq" data-area="faq" aria-busy="true">
			<?php cdn_plantilla_carga( 4 ); ?>
		</div>
		<noscript><p class="aviso-js">Para ver las preguntas frecuentes, activa JavaScript.</p></noscript>
	</div>
</section>
