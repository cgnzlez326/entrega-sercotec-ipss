<?php
/**
 * Cabecera del theme CDN Santiago.
 *
 * @package cdn-santiago
 */

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="profile" href="https://gmpg.org/xfn/11">
<?php wp_head(); ?>
</head>
<body <?php body_class( 'cdn-body' ); ?>>
<?php wp_body_open(); ?>

<a class="saltar-contenido" href="#contenido">Saltar al contenido principal</a>

<div class="barra-superior">
	<div class="contenedor barra-superior__contenido">
		<ul class="barra-superior__datos">
			<li>
				<a href="https://maps.google.com/?q=<?php echo rawurlencode( (string) cdn_ajuste( 'contacto_direccion' ) ); ?>" target="_blank" rel="noopener">
					<?php echo cdn_icono( 'ubicacion' ); ?><span><?php echo esc_html( cdn_ajuste( 'contacto_direccion' ) ); ?></span>
				</a>
			</li>
			<li><a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', (string) cdn_ajuste( 'contacto_telefono' ) ) ); ?>"><?php echo cdn_icono( 'telefono' ); ?><span><?php echo esc_html( cdn_ajuste( 'contacto_telefono' ) ); ?></span></a></li>
			<li><a href="mailto:<?php echo esc_attr( cdn_ajuste( 'contacto_correo' ) ); ?>"><?php echo cdn_icono( 'correo' ); ?><span><?php echo esc_html( cdn_ajuste( 'contacto_correo' ) ); ?></span></a></li>
		</ul>
	</div>
</div>

<header class="cabecera" id="cabecera">
	<div class="contenedor cabecera__interior">
		<div class="cabecera__marca">
			<a class="cabecera__logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="Ir al inicio">
				<img src="<?php echo esc_url( cdn_asset( 'img/logo-cdn.png' ) ); ?>" alt="Centros de Desarrollo de Negocios Sercotec" width="196" height="68">
			</a>
		</div>

		<button class="cabecera__alternar" id="boton-menu" aria-controls="menu-principal" aria-expanded="false" aria-label="Abrir menú de navegación">
			<span class="cabecera__hamburguesa" aria-hidden="true"></span>
			<span class="texto-accesible">Menú</span>
		</button>

		<nav class="cabecera__nav" id="menu-principal" aria-label="Menú principal">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'principal',
					'container'      => false,
					'menu_class'     => 'menu',
					'fallback_cb'    => 'cdn_menu_fallback',
					'depth'          => 1,
				)
			);
			?>
			<a class="boton boton--cta cabecera__cta" href="#contacto">Asesoría gratuita</a>
		</nav>
	</div>
</header>

<main id="contenido" class="contenido" tabindex="-1">
