<?php
/**
 * Seed del sitio CDN Santiago: activa theme/plugin, crea contenido editorial,
 * adjunta imágenes y genera un Application Password para Postman.
 *
 * Uso: php bin/seed.php
 *
 * @package cdn-santiago
 */

if ( PHP_SAPI !== 'cli' ) {
	exit( 'Debe ejecutarse por CLI: php bin/seed.php' );
}

$ABSPATH = dirname( __DIR__ ) . '/';
if ( ! defined( 'WP_CLI' ) ) {
	require_once $ABSPATH . 'wp-load.php';
}

echo "=== Seed CDN Santiago ===\n";

/* ---------- 0. Opciones base del sitio ---------- */
update_option( 'permalink_structure', '/%postname%/' );
update_option( 'blogname', 'Centro de Desarrollo de Negocios Sercotec Santiago' );
update_option( 'blogdescription', 'Apoyo y acompañamiento gratuito para micro, pequeñas empresas y cooperativas de Santiago y Providencia.' );
update_option( 'timezone_string', 'America/Santiago' );
update_option( 'date_format', 'j F Y' );
update_option( 'time_format', 'H:i' );
update_option( 'start_of_week', 1 );
update_option( 'show_on_front', 'posts' );
update_option( 'uploads_use_yearmonth_folders', 0 );
update_option( 'default_ping_status', 'closed' );
update_option( 'default_comment_status', 'closed' );

/* ---------- 1. Activar theme y plugin ---------- */
$theme_dir = 'cdn-santiago';
switch_theme( $theme_dir );
update_option( 'template', $theme_dir );
update_option( 'stylesheet', $theme_dir );
echo "Theme activo: $theme_dir\n";

$plugin_main = 'cdn-contenidos/cdn-contenidos.php';
$activos     = get_option( 'active_plugins', array() );
if ( ! in_array( $plugin_main, $activos, true ) ) {
	$activos[] = $plugin_main;
	update_option( 'active_plugins', $activos );
	echo "Plugin activado: $plugin_main\n";
} else {
	echo "Plugin ya activo\n";
}

// Cargar el plugin para usar sus funciones y registrar CPT en este proceso.
require_once ABSPATH . 'wp-content/plugins/' . $plugin_main;
cdn_contenidos_registrar_post_types();
cdn_contenidos_registrar_taxonomia();
cdn_contenidos_areas_por_defecto();
flush_rewrite_rules();

/* ---------- Helpers ---------- */
function cdn_seed_find( $tipo, $slug ) {
	$q = new WP_Query(
		array(
			'name'           => $slug,
			'post_type'      => $tipo,
			'post_status'    => 'any',
			'posts_per_page' => 1,
			'no_found_rows'  => true,
		)
	);
	return $q->have_posts() ? $q->posts[0] : null;
}

function cdn_seed_post( $args, $meta = array() ) {
	$existente = cdn_seed_find( $args['post_type'], $args['post_name'] );
	if ( $existente ) {
		$id = $existente->ID;
		$args['ID'] = $id;
		unset( $args['post_name'] );
		wp_update_post( $args );
	} else {
		$id = wp_insert_post( wp_parse_args( $args, array( 'post_status' => 'publish' ) ), true );
	}
	if ( is_wp_error( $id ) ) {
		echo "  ERROR " . $args['post_type'] . ': ' . $id->get_error_message() . "\n";
		return 0;
	}
	foreach ( $meta as $clave => $valor ) {
		if ( '' === $valor || null === $valor ) {
			delete_post_meta( $id, $clave );
		} else {
			update_post_meta( $id, $clave, $valor );
		}
	}
	return (int) $id;
}

/** Sube una imagen del theme a la biblioteca y la asigna como destacada. */
function cdn_seed_adjuntar( $archivo_rel, $post_id, $titulo ) {
	if ( get_post_thumbnail_id( $post_id ) ) {
		return (int) get_post_thumbnail_id( $post_id ); // Idempotente.
	}
	$base = dirname( __DIR__ ) . '/wp-content/themes/cdn-santiago/assets/img/';
	$origen = $base . $archivo_rel;
	if ( ! file_exists( $origen ) ) {
		echo "  Imagen no encontrada: $archivo_rel\n";
		return 0;
	}
	$subir = wp_upload_dir();
	$nombre = 'cdn-' . sanitize_title( $titulo ) . '-' . wp_generate_password( 4, false, false ) . '.' . strtolower( pathinfo( $origen, PATHINFO_EXTENSION ) );
	$destino = $subir['path'] . '/' . $nombre;
	copy( $origen, $destino );

	$tipo = wp_check_filetype( $destino );
	$attachment = array(
		'post_mime_type' => $tipo['type'],
		'post_title'     => sanitize_text_field( $titulo ),
		'post_status'    => 'inherit',
	);
	$aid = wp_insert_attachment( $attachment, $destino, $post_id );
	if ( is_wp_error( $aid ) || ! $aid ) {
		return 0;
	}
	require_once ABSPATH . 'wp-admin/includes/image.php';
	$meta = wp_generate_attachment_metadata( $aid, $destino );
	wp_update_attachment_metadata( $aid, $meta );
	set_post_thumbnail( $post_id, $aid );
	return (int) $aid;
}

/* ---------- 2. Áreas (términos + color) ---------- */
$areas = cdn_contenidos_areas_default();
$term_ids = array();
foreach ( $areas as $slug => $datos ) {
	$t = get_term_by( 'slug', $slug, 'cdn_area' );
	if ( $t ) {
		$term_ids[ $slug ] = (int) $t->term_id;
		update_term_meta( $t->term_id, 'cdn_color', $datos['color'] );
	}
}
echo 'Áreas listas: ' . implode( ', ', array_keys( $term_ids ) ) . "\n";

/* ---------- 3. Servicios (8 tarjetas) ---------- */
$servicios = array(
	array( 'slug' => 'diagnostico-empresarial', 'area' => 'acompanamiento', 'img' => 'nosotros-cdn.jpg', 'titulo' => 'Diagnóstico Empresarial', 'desc' => 'Evaluación inicial gratuita de tu negocio para detectar fortalezas, brechas y oportunidades de mejora.', 'orden' => 1 ),
	array( 'slug' => 'asesoria-acompanamiento-continuo', 'area' => 'acompanamiento', 'img' => 'equipo-cdn.jpg', 'titulo' => 'Asesoría de Acompañamiento Continuo', 'desc' => 'Sesiones periódicas con un asesor experto para sostener tu crecimiento y resolver desafíos operativos.', 'orden' => 2 ),
	array( 'slug' => 'capacitacion-talleres', 'area' => 'gestion', 'img' => 'header-la-florida.jpg', 'titulo' => 'Capacitación y Talleres', 'desc' => 'Formación práctica en administración, ventas y gestión empresarial para aplicar de inmediato en tu empresa.', 'orden' => 3 ),
	array( 'slug' => 'finanzas-marketing-digital', 'area' => 'gestion', 'img' => 'modelo-grafico.png', 'titulo' => 'Finanzas y Marketing Digital', 'desc' => 'Apoyo para ordenar tus números, formalizar tu gestión y mejorar tu presencia digital y tus ventas.', 'orden' => 4 ),
	array( 'slug' => 'transformacion-digital', 'area' => 'eficiencia', 'img' => 'hero-banner.png', 'titulo' => 'Transformación Digital', 'desc' => 'Adopción de herramientas tecnológicas para digitalizar procesos y ganar eficiencia operativa.', 'orden' => 5 ),
	array( 'slug' => 'optimizacion-procesos', 'area' => 'eficiencia', 'img' => 'modelo-centros.png', 'titulo' => 'Optimización de Procesos', 'desc' => 'Revisión y mejora de tus flujos de trabajo para reducir costos, tiempos y desperdicios.', 'orden' => 6 ),
	array( 'slug' => 'networking-articulacion', 'area' => 'vinculacion', 'img' => 'cristina-catalan.jpg', 'titulo' => 'Networking y Articulación', 'desc' => 'Instancias de vinculación con el ecosistema público, privado y académico para abrir nuevas oportunidades.', 'orden' => 7 ),
	array( 'slug' => 'postulacion-fondos-financiamiento', 'area' => 'vinculacion', 'img' => 'banner-2026.png', 'titulo' => 'Postulación a Fondos y Financiamiento', 'desc' => 'Te orientamos y acompañamos en la postulación a fondos públicos y financiamiento privado.', 'orden' => 8 ),
);

foreach ( $servicios as $s ) {
	$id = cdn_seed_post(
		array(
			'post_type'    => 'cdn_servicio',
			'post_name'    => $s['slug'],
			'post_title'   => $s['titulo'],
			'post_excerpt' => $s['desc'],
			'post_content' => $s['desc'] . ' Este servicio es parte del acompañamiento gratuito que entrega el Centro de Desarrollo de Negocios Sercotec Santiago.',
			'menu_order'   => $s['orden'],
			'post_status'  => 'publish',
		)
	);
	if ( $id ) {
		wp_set_post_terms( $id, array( $term_ids[ $s['area'] ] ), 'cdn_area' );
		cdn_seed_adjuntar( $s['img'], $id, $s['titulo'] );
		echo "Servicio: {$s['titulo']}\n";
	}
}

/* ---------- 4. Testimonios (6) ---------- */
$testimonios = array(
	array( 'slug' => 'testimonio-pasteleria', 'nombre' => 'María José Rojas', 'cargo' => 'Fundadora', 'empresa' => 'Pastelería Dulce Hogar', 'nota' => 5, 'cita' => 'Llegué con mi pastelería casi sin ordenar mis finanzas. Con el diagnóstico y el plan de mejora aumenté mis ventas y hoy contrato a dos personas más.' ),
	array( 'slug' => 'testimonio-constructora', 'nombre' => 'Patricio Fuentes', 'cargo' => 'Socio gerente', 'empresa' => 'Construcciones Andes', 'nota' => 5, 'cita' => 'El acompañamiento para postular a fondos fue clave: obtuvimos financiamiento que nos permitió renovar maquinaria.' ),
	array( 'slug' => 'testimonio-tecnologia', 'nombre' => 'Camila Soto', 'cargo' => 'CEO', 'empresa' => 'Sistemas Nova', 'nota' => 4, 'cita' => 'Gracias a los talleres de marketing digital pasamos de vender solo a clientes cercanos a cerrar ventas en otras regiones.' ),
	array( 'slug' => 'testimonio-artesania', 'nombre' => 'Jorge Miranda', 'cargo' => 'Artesano', 'empresa' => 'Artesanías del Valle', 'nota' => 5, 'cita' => 'Las asesorías periódicas me ayudaron a formalizar mi negocio y a participar en ferias donde nunca había llegado.' ),
	array( 'slug' => 'testimonio-restaurante', 'nombre' => 'Daniela Contreras', 'cargo' => 'Dueña', 'empresa' => 'Sabores de la Vega', 'nota' => 5, 'cita' => 'El networking del centro me conectó con proveedores y otros emprendedores. Hoy mi restaurante creció y siento que no estoy sola.' ),
	array( 'slug' => 'testimonio-cooperativa', 'nombre' => 'Rodrigo Antilef', 'cargo' => 'Presidente', 'empresa' => 'Cooperativa Raíces del Sur', 'nota' => 4, 'cita' => 'La asesoría en gestión nos permitió ordenar la administración de la cooperativa y postular a nuevos mercados.' ),
);
foreach ( $testimonios as $t ) {
	cdn_seed_post(
		array(
			'post_type'    => 'cdn_testimonio',
			'post_name'    => $t['slug'],
			'post_title'   => $t['nombre'],
			'post_content' => $t['cita'],
			'post_status'  => 'publish',
		),
		array(
			'_cdn_cargo'   => $t['cargo'],
			'_cdn_empresa' => $t['empresa'],
			'_cdn_nota'    => $t['nota'],
		)
	);
}
echo "Testimonios creados: " . count( $testimonios ) . "\n";

/* ---------- 5. FAQ (10) ---------- */
$faqs = array(
	array( 'slug' => 'faq-costo-servicios', 'grupo' => 'General', 'orden' => 1, 'pregunta' => '¿Los servicios del Centro tienen costo?', 'respuesta' => 'No. Los servicios de asesoría, capacitación y vinculación del Centro de Desarrollo de Negocios Sercotec Santiago son completamente gratuitos para micro, pequeñas empresas y cooperativas.' ),
	array( 'slug' => 'faq-quienes-pueden-atenderse', 'grupo' => 'General', 'orden' => 2, 'pregunta' => '¿Quiénes pueden atenderse en el Centro?', 'respuesta' => 'Micro y pequeñas empresas y cooperativas, formales o en proceso de formalización, que se encuentren en las comunas de Santiago y Providencia o que quieran desarrollar su negocio en el territorio.' ),
	array( 'slug' => 'faq-que-necesito-primera-visita', 'grupo' => 'Atención', 'orden' => 3, 'pregunta' => '¿Qué necesito para mi primera atención?', 'respuesta' => 'Solo debes agendar una hora y asistir con una idea clara de tu consulta. El equipo hará un diagnóstico y, si corresponde, te propondrá un plan de trabajo personalizado.' ),
	array( 'slug' => 'faq-horarios-atencion', 'grupo' => 'Atención', 'orden' => 4, 'pregunta' => '¿Cuáles son los horarios y dónde atienden?', 'respuesta' => 'Atenemos de lunes a viernes de 9:00 a 18:00 hrs. en Manuel Rodríguez Sur 749, Santiago (Metro Toesca). También contamos con un centro satélite en Providencia y puntos de atención móvil en convenios con universidades.' ),
	array( 'slug' => 'faq-duracion-acompanamiento', 'grupo' => 'Servicios', 'orden' => 5, 'pregunta' => '¿Cuánto dura el acompañamiento?', 'respuesta' => 'El acompañamiento es de mediano a largo plazo. Tras el diagnóstico se define un plan de trabajo consensuado y se realiza seguimiento periódico para medir resultados en ventas, empleo e inversión.' ),
	array( 'slug' => 'faq-que-es-diagnostico', 'grupo' => 'Servicios', 'orden' => 6, 'pregunta' => '¿Qué es el diagnóstico empresarial?', 'respuesta' => 'Es una evaluación inicial y gratuita de tu negocio que permite identificar fortalezas, brechas y oportunidades. A partir de ella se construye tu plan de mejora personalizado.' ),
	array( 'slug' => 'faq-capacitaciones-disponibles', 'grupo' => 'Servicios', 'orden' => 7, 'pregunta' => '¿Qué capacitaciones ofrecen?', 'respuesta' => 'Talleres y capacitaciones en administración, finanzas, marketing digital, formalización, ventas y otras materias prácticas orientadas a la gestión de tu empresa.' ),
	array( 'slug' => 'faq-ayuda-postular-fondos', 'grupo' => 'Financiamiento', 'orden' => 8, 'pregunta' => '¿Me ayudan a postular a fondos Sercotec u otros?', 'respuesta' => 'Sí. Te orientamos y acompañamos en la preparación de postulaciones a fondos públicos y privados, y en la articulación con financiamiento para tu negocio.' ),
	array( 'slug' => 'faq-puedo-ir-otra-comuna', 'grupo' => 'General', 'orden' => 9, 'pregunta' => '¿Puedo atenderme si mi empresa está en otra comuna?', 'respuesta' => 'El Centro atiende principalmente empresas de Santiago y Providencia. Si tu negocio está en otra comuna, te orientamos para derivarte al centro de tu territorio.' ),
	array( 'slug' => 'faq-como-agendar', 'grupo' => 'Atención', 'orden' => 10, 'pregunta' => '¿Cómo agendo una hora?', 'respuesta' => 'Puedes escribir al correo centro.santiago@centrossercotec.cl, llamar al +(56) 9 3927 5633 o completar el formulario de contacto de esta página.' ),
);
foreach ( $faqs as $f ) {
	cdn_seed_post(
		array(
			'post_type'    => 'cdn_faq',
			'post_name'    => $f['slug'],
			'post_title'   => $f['pregunta'],
			'post_content' => $f['respuesta'],
			'post_status'  => 'publish',
		),
		array(
			'_cdn_grupo' => $f['grupo'],
			'_cdn_orden' => $f['orden'],
		)
	);
}
echo 'FAQ creadas: ' . count( $faqs ) . "\n";

/* ---------- 6. Nosotros (contenido de la sección) ---------- */
$lead = "El Centro de Desarrollo de Negocios Sercotec Santiago es un espacio de apoyo estratégico donde micro, pequeñas empresas y cooperativas reciben asesoría técnica, capacitación y vinculación personalizada y sin costo, a través de un equipo experto con foco en resultados.\n\nCon un fuerte compromiso territorial, trabajamos junto a una red de socios públicos, privados y académicos para impulsar la profesionalización, el crecimiento sostenible y la competitividad empresarial. En sus 10 años de trayectoria, el Centro ha atendido directamente a más de 3.200 empresas e indirectamente a más de 20.000.";

$dirigido = "Micro y pequeñas empresas en busca de oportunidades o de mejorar su gestión para crecer.\nEmpresas con un problema específico o una necesidad que deba ser resuelta.\nCooperativas y emprendimientos que quieran innovar en sus productos o procesos.\nNegocios formales o en proceso de formalización de las comunas de Santiago y Providencia.";

$metodo = "Diagnóstico gratuito inicial para conocer tu negocio.\nPlan de trabajo personalizado y consensuado contigo.\nAsesoría especializada y acompañamiento periódico.\nCapacitación y talleres prácticos.\nVinculación con el ecosistema empresarial.\nMedición permanente de resultados en ventas, empleo e inversión.";

$stats = '{"prefijo":"","valor":3200,"sufijo":"+","texto":"empresas atendidas directamente"}' . "\n"
	. '{"prefijo":"$","valor":22450,"sufijo":" millones","texto":"en aumento de ventas generado"}' . "\n"
	. '{"prefijo":"","valor":1834,"sufijo":"","texto":"nuevos empleos generados"}' . "\n"
	. '{"prefijo":"$","valor":4752,"sufijo":" millones","texto":"en financiamiento privado aprobado"}';

$alianzas = "Municipalidad de Santiago :: Principal socio estratégico territorial del centro.\nMunicipalidad de Providencia :: Alianza para la atención del sector norte del territorio.\nHub Santiago :: Espacio de emprendimiento e innovación de la Municipalidad de Santiago.\nHub Providencia :: Espacio de emprendimiento e innovación de la Municipalidad de Providencia.\nSercotec :: Programa Centros de Desarrollo de Negocios.\nUniversidades y DUOC :: Puntos de atención móvil y articulación académica.";

$proposito = "Nuestro propósito es fortalecer la sostenibilidad y competitividad de los negocios, generando resultados concretos en ventas, empleo, productividad e impacto positivo para Santiago y Providencia. Acompañamos a las empresas y cooperativas durante todo su ciclo de desarrollo, desde la idea hasta el escalamiento, mediante diagnóstico, planes de trabajo personalizados, asesoría especializada, capacitación, mentoría y vinculación con el ecosistema.";

$nos = cdn_seed_post(
	array(
		'post_type'    => 'cdn_nosotros',
		'post_name'    => 'nosotros',
		'post_title'   => 'Nosotros - contenido',
		'post_content' => $proposito,
		'post_status'  => 'publish',
	),
	array(
		'_cdn_lead'     => $lead,
		'_cdn_dirigido' => $dirigido,
		'_cdn_metodo'   => $metodo,
		'_cdn_stats'    => $stats,
		'_cdn_alianzas' => $alianzas,
	)
);
if ( $nos ) {
	cdn_seed_adjuntar( 'nosotros-cdn.jpg', $nos, 'Equipo Centro Santiago' );
	echo "Nosotros creado\n";
}

/* ---------- 7. Ubicaciones (6) ---------- */
$ubicaciones = array(
	array( 'slug' => 'centro-principal-santiago', 'titulo' => 'Centro principal Santiago', 'tipo' => 'principal', 'dir' => 'Manuel Rodríguez Sur 749, Santiago (Metro Toesca)', 'tel' => '+(56) 9 3927 5633', 'correo' => 'centro.santiago@centrossercotec.cl', 'horario' => 'Lunes a Viernes de 9:00 a 18:00 hrs.', 'resp' => 'Christian Gacitúa L. - Jefe de Centro', 'orden' => 1 ),
	array( 'slug' => 'centro-satelite-providencia', 'titulo' => 'Centro Satélite Providencia', 'tipo' => 'satelite', 'dir' => 'Los Jesuitas 881, Providencia (primer piso)', 'tel' => '+(56) 9 3927 5633', 'correo' => 'centro.santiago@centrossercotec.cl', 'horario' => 'Martes y jueves de 09:00 a 13:00 hrs.', 'resp' => 'Francisco Ramirez - Asesor responsable', 'orden' => 2 ),
	array( 'slug' => 'punto-atencion-utem', 'titulo' => 'Punto de Atención Universidad Tecnológica Metropolitana', 'tipo' => 'punto', 'dir' => 'Dr. Hernán Alessandri 644, Providencia', 'horario' => '2 miércoles al mes de 09:00 a 13:00 hrs. (confirmar con su asesor/a)', 'resp' => 'Pablo Andrewartha - Asesor responsable', 'orden' => 3 ),
	array( 'slug' => 'punto-atencion-uautonoma', 'titulo' => 'Punto de Atención Universidad Autónoma de Chile', 'tipo' => 'punto', 'dir' => 'Pedro de Valdivia 425, Providencia', 'horario' => '2 miércoles al mes de 09:00 a 13:00 hrs. (confirmar con su asesora)', 'resp' => 'Tania Avillo - Asesora responsable', 'orden' => 4 ),
	array( 'slug' => 'punto-atencion-duoc', 'titulo' => 'Punto de Atención DUOC UC Alameda', 'tipo' => 'punto', 'dir' => 'España 8, Santiago', 'horario' => '2 jueves al mes de 09:00 a 13:00 hrs. (confirmar con su asesor)', 'resp' => 'Mauricio Vargas - Asesor responsable', 'orden' => 5 ),
	array( 'slug' => 'consultorio-empresarial', 'titulo' => 'Consultorio Empresarial', 'tipo' => 'consultorio', 'dir' => 'Atención rápida en línea', 'horario' => 'Respuesta de dudas generales en 20 minutos', 'resp' => 'Equipo del Centro Santiago', 'orden' => 6 ),
);
foreach ( $ubicaciones as $u ) {
	cdn_seed_post(
		array(
			'post_type'    => 'cdn_ubicacion',
			'post_name'    => $u['slug'],
			'post_title'   => $u['titulo'],
			'post_content' => '',
			'post_status'  => 'publish',
		),
		array(
			'_cdn_tipo'        => $u['tipo'],
			'_cdn_direccion'   => isset( $u['dir'] ) ? $u['dir'] : '',
			'_cdn_telefono'    => isset( $u['tel'] ) ? $u['tel'] : '',
			'_cdn_correo'      => isset( $u['correo'] ) ? $u['correo'] : '',
			'_cdn_horario'     => isset( $u['horario'] ) ? $u['horario'] : '',
			'_cdn_responsable' => isset( $u['resp'] ) ? $u['resp'] : '',
			'_cdn_orden'       => $u['orden'],
		)
	);
}
echo 'Ubicaciones creadas: ' . count( $ubicaciones ) . "\n";

/* ---------- 8. Application Password para Postman ---------- */
$clase_app = ABSPATH . 'wp-admin/includes/class-wp-application-passwords.php';
if ( file_exists( $clase_app ) ) {
	require_once $clase_app;
}
if ( class_exists( 'WP_Application_Passwords' ) ) {
	$usuario = get_user_by( 'login', 'admin' );
	if ( $usuario ) {
		$ya_existe = false;
		$existente = WP_Application_Passwords::get_user_application_passwords( $usuario->ID );
		foreach ( (array) $existente as $app ) {
			if ( isset( $app['name'] ) && 'Postman CDN' === $app['name'] ) {
				$ya_existe = true;
				break;
			}
		}
		if ( ! $ya_existe ) {
			$resultado = WP_Application_Passwords::create_new_application_password( $usuario->ID, array( 'name' => 'Postman CDN' ) );
			if ( is_array( $resultado ) && isset( $resultado[0] ) ) {
				$ruta = 'C:/Users/Camilo/AppData/Local/Temp/opencode/app-pass.txt';
				file_put_contents( $ruta, $resultado[0] );
				echo "Application Password creada y guardada para Postman.\n";
			} else {
				echo "No se pudo crear Application Password: " . ( is_wp_error( $resultado ) ? $resultado->get_error_message() : 'desconocido' ) . "\n";
			}
		} else {
			echo "Application Password 'Postman CDN' ya existe.\n";
		}
	}
} else {
	echo "WP_Application_Passwords no disponible.\n";
}

flush_rewrite_rules();
echo "=== Seed finalizado ===\n";
