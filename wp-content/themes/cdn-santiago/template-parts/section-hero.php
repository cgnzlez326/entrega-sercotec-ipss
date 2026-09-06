<?php
/**
 * Componente: Hero (renderizado en servidor desde ajustes del sitio).
 *
 * @package cdn-santiago
 */

$hero_titulo  = cdn_ajuste( 'hero_titulo' );
$hero_region  = cdn_ajuste( 'hero_region' );
$hero_comunas = cdn_ajuste( 'hero_comunas' );
$hero_texto   = cdn_ajuste( 'hero_texto' );
$btn_primario = cdn_ajuste( 'hero_btn_principal' );
$btn_sec      = cdn_ajuste( 'hero_btn_secundario' );
?>
<section id="inicio" class="hero" aria-labelledby="titulo-pagina">
	<div class="hero__fondo" aria-hidden="true"></div>
	<div class="contenedor hero__interior">
		<div class="hero__texto">
			<p class="hero__region"><?php echo esc_html( $hero_region ); ?></p>
			<h1 class="hero__titulo" id="titulo-pagina"><?php echo esc_html( $hero_titulo ); ?></h1>
			<p class="hero__comunas"><strong><?php echo esc_html( $hero_comunas ); ?></strong></p>
			<p class="hero__descripcion"><?php echo esc_html( $hero_texto ); ?></p>
			<div class="hero__botones">
				<a class="boton boton--principal" href="#contacto"><?php echo esc_html( $btn_primario ); ?></a>
				<a class="boton boton--secundario" href="#servicios"><?php echo esc_html( $btn_sec ); ?></a>
			</div>
			<ul class="hero__confianza" aria-label="Cobertura del centro">
				<li><strong>3.200+</strong> empresas atendidas</li>
				<li><strong>62</strong> centros en Chile</li>
				<li><strong>100%</strong> gratuito</li>
			</ul>
		</div>
		<figure class="hero__figura">
			<img src="<?php echo esc_url( cdn_img( 'hero-banner' ) ); ?>" alt="" width="1200" height="624" fetchpriority="high" decoding="async">
		</figure>
	</div>
</section>
