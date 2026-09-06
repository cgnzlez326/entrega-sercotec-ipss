<?php
/**
 * Plantilla principal: landing one-page del Centro de Negocios.
 * Ensambla componentes reutilizables (template-parts).
 *
 * @package cdn-santiago
 */

get_header();

get_template_part( 'template-parts/section', 'hero' );
get_template_part( 'template-parts/section', 'nosotros' );
get_template_part( 'template-parts/section', 'servicios' );
get_template_part( 'template-parts/section', 'testimonios' );
get_template_part( 'template-parts/section', 'faq' );
get_template_part( 'template-parts/section', 'ubicaciones' );
get_template_part( 'template-parts/section', 'contacto' );

get_footer();
