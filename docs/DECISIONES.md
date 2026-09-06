# Decisiones técnicas — CDN Santiago

Registro de decisiones de arquitectura y de los problemas complejos resueltos durante la
construcción de la landing (indicadores de la rúbrica: "resuelve problemas complejos",
"documenta y comparte conocimiento").

## D1. WordPress clásico + REST interna (no headless)
**Contexto:** el encargo exige "framework con componentes", "integración de CMS" y "consumo de API
REST". La rúbrica premia un framework de componentes en la UI.
**Decisión:** theme clásico PHP (`cdn-santiago`) donde las secciones dinámicas (Nosotros, Servicios,
FAQ, testimonios, ubicaciones) se renderizan con JS que consume la REST API interna
(`/wp-json/cdn/v1/*`). Hero y formulario se pintan en servidor para no degradar el primer render.
**Alternativas descartadas:** headless SPA (React/Vue) (mayor complejidad, duplica CMS y front y no
es "proyecto PHP"); plugin de page-builder (opaco para el desarrollo frontend).
**Consecuencia:** 100 % PHP/WP, CMS gestionable por el equipo editorial y evidencia clara de consumo
de endpoints y componentes.

## D2. Contenido editorial como CPTs + meta boxes propios
**Contexto:** necesitábamos datos estructurados (cargo, empresa, nota, dirección, cifras, etc.) sin
depender de plugins de terceros (ACF) para facilitar la instalación reproducible.
**Decisión:** plugin `cdn-contenidos` registra CPTs (`cdn_servicio`, `cdn_testimonio`, `cdn_faq`,
`cdn_ubicacion`, `cdn_nosotros`, `cdn_mensaje` privado), taxonomía `cdn_area` con color por término,
y meta boxes declarativos (texto/textarea/número/select) con guardado sanitizado. Los metadatos se
exponen en la REST vía `register_post_meta(..., show_in_rest)` para gestión editorial con Postman.

## D3. Captcha stateless con HMAC (sin sesión)
**Problema:** el endpoint de contacto es REST (sin estado) y en localhost no conviene depender de
sesiones PHP; además los captchas clásicos (texto/imagen) son frágiles en local.
**Decisión:** captcha aritmético "¿cuánto es A + B?" validado por un **HMAC** calculado con
`wp_hash` (secret) + salt + tiempo. El token viaja en inputs ocultos; el servidor recalcula y
compara con `hash_equals()`. Sumado a un **honeypot**, un **time-trap** (mínimo 2 s) y **rate
limit por IP** (transient de 1 hora).
**Resultado:** sin estado, sin tablas, robusto contra envíos automatizados; fácil de probar.

## D4. Endpoints de lectura agregados vs. gestión nativa
**Decisión:** se exponen endpoints propios legibles (`/cdn/v1/servicios`, etc.) que devuelven DTOs
preparados (con área, color, imagen) para no acoplar el front a la estructura interna de WP; la
gestión CRUD editorial usa los endpoints nativos `wp/v2/cdn_*`. Esto separa "presentación" de
"administración" y se documenta en Postman.

## D5. Carrusel accesible sin librerías
**Problema:** los carruseles de librerías suelen fallar en accesibilidad (autoplay sin pausa,
flechas no anunciadas, falta de live region).
**Decisión:** carrusel propio con scroll/transform, botones y puntos con `aria-label`, pausa al
hover/foco, respeto a `prefers-reduced-motion`, `aria-live` que anuncia "Testimonio X de N",
responsive (1 tarjeta en móvil, 2 en escritorio) y soporte táctil mediante eventos pointer.
**Métrica:** operación 100 % por teclado y anuncio de cambios para lectores de pantalla.

## D6. Autocompletado del formulario desde la tarjeta
**Problema:** el botón "Contáctanos" de cada tarjeta debe pre-seleccionar el servicio en el
formulario (requisito #1).
**Decisión:** el botón lleva `data-contactar-servicio="{titulo}"`; un único listener delegado
selecciona el `<option>`, lo añade si no existe, lo resalta, muestra aviso `aria-live` y desplaza
con scroll suave al formulario. Implementación única (delegación) reutilizable para cualquier
elemento futuro con ese atributo.

## D7. Rendimiento: WebP por GD, minificación propia y .htaccess
**Decisión:** `bin/convertir-webp.php` (GD) genera WebP (~50 % menor); logo/íconos SVG; `lazy` +
`decoding=async`; `bin/minificar.php` produce `.min`; `assets/.htaccess` y `uploads/.htaccess`
aplican cacheo y gzip; JS en `defer`; sin librerías externas.
**Resultado:** métricas registradas en REGISTRO-PROYECTO (imágenes 51 KB vs 109 KB, etc.).

## D8. Seguridad de headers y ejecución
**Decisión:** cabeceras `X-Content-Type-Options`, `X-Frame-Options: SAMEORIGIN`,
`Referrer-Policy` y `Permissions-Policy` vía `send_headers`; bloqueo de ejecución de scripts en
`uploads` por `.htaccess`; `DISALLOW_FILE_EDIT` y `FS_METHOD=direct` en wp-config local.
