<?php
/**
 * Plantilla genérica de respaldo (archivos/individuales) y 404.
 *
 * @package cdn-santiago
 */

get_header();

if ( is_home() && ! is_front_page() ) :
	?>
	<section class="seccion seccion--gris">
		<div class="contenedor">
			<header class="seccion__cabecera seccion__cabecera--centrada">
				<p class="seccion__sello">Blog</p>
				<h1 class="seccion__titulo"><?php single_post_title(); ?></h1>
			</header>
		</div>
	</section>
	<?php
endif;

if ( have_posts() ) :
	while ( have_posts() ) :
		the_post();
		?>
		<article <?php post_class( 'seccion' ); ?>>
			<div class="contenedor contenedor--angosto">
				<header class="entrada__cabecera">
					<?php if ( is_singular() ) : ?>
						<h1 class="entrada__titulo"><?php the_title(); ?></h1>
					<?php else : ?>
						<h2 class="entrada__titulo"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
					<?php endif; ?>
				</header>
				<div class="entrada__contenido">
					<?php the_content(); ?>
				</div>
			</div>
		</article>
		<?php
	endwhile;
else :
	?>
	<section class="seccion seccion--gris">
		<div class="contenedor contenedor--angosto">
			<h1 class="entrada__titulo">Página no encontrada</h1>
			<p>El contenido que buscas no existe o fue movido.</p>
			<p><a class="boton boton--principal" href="<?php echo esc_url( home_url( '/' ) ); ?>">Volver al inicio</a></p>
		</div>
	</section>
	<?php
endif;

get_footer();
