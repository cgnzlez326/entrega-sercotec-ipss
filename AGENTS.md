# AGENTS.md — Sesión: Entrega 3 Frontend (WordPress + XAMPP)

## Propósito
Construir a completitud el encargo `e3.md` (Evaluado por `Rúbrica.md`) replicando la landing
de referencia: https://sitios.sercotec.cl/centros-de-negocios/centro-de-desarrollo-de-negocios-santiago/
Stack: WordPress en XAMPP (Windows), theme clásico PHP + REST API interna de WordPress.

## Datos fijos (NO cambiar sin preguntar)
- Sitio local: `C:\xampp\htdocs\sercotec-cdn` → URL `http://localhost/sercotec-cdn`
- BD: `sercotec_cdn` · MySQL root sin password · charset utf8mb4
- WordPress core: es_ES (es.wordpress.org/latest-es_ES.zip)
- Admin WP: `admin` + password AUTOGENERADA (documentar en REGISTRO-PROYECTO.md y LEEME)
- Permalinks: `/%postname%/`
- Repo público: `github.com/cgnzlez326/entrega-sercotec-ipss`
- Git: NO tocar/pushear `main`. Trabajo en ramas `feature/*`, abro PRs con `gh`, NO hago merge (revisa el usuario).
- ZIP docente autocontenido (asume XAMPP MySQL root sin password) al final.

## Arquitectura acordada
- Theme `cdn-santiago` clásico PHP: componentes reutilizables = template-parts + funciones PHP.
- Secciones Nosotros, Servicios y FAQ se poblan DINÁMICAMENTE con JS `fetch` a la REST interna
  de WordPress (`/wp-json/cdn/v1/*`). Requisito e3 #3 (CMS) y #9 (endpoints).
- Landing one-page con anclas (hero, nosotros, servicios, testimonios carrusel, FAQ,
  ubicaciones/alianzas, contacto) + reutilización de componentes.
- Plugin `cdn-contenidos`: CPTs (cdn_servicio, cdn_testimonio, cdn_faq, cdn_ubicacion,
  cdn_mensaje privado), taxonomía `cdn_area`, endpoints `/cdn/v1/{sitio,servicios,testimonios,faqs,contacto}`.
- mu-plugin: habilita Application Passwords en localhost (http) + flush rewrite.
- Seguridad contacto: validación cliente/servidor, sanitize, honeypot, captcha aritmético
  stateless (HMAC con wp_hash + salt) y time-trap; guarda en CPT cdn_mensaje.
- Rendimiento: WebP (GD), SVG logo/íconos, loading=lazy, minificar CSS/JS, cache+gzip .htaccess.
- Accesibilidad WCAG 2.1 AA + toolbar accesibilidad estilo sitio real (localStorage).

## Entregables
1. Repo GitHub (solo código): theme, plugin, mu-plugin, bin/, docs/, postman/, README, AGENTS.md.
2. ZIP docente: WP completo + theme + plugin + uploads + wp-config preconfigurado +
   database.sql (seed) + instalar.bat + LEEME_INSTALACION.txt.

## Entorno / comandos Windows (¡importante!)
- PHP CLI: `C:\xampp\php\php.exe` (NO está en PATH). `php -l` para lint.
- MySQL: `C:\xampp\mysql\bin\mysql.exe`. Usar `cmd /c "mysql.exe ... < archivo.sql"` (PowerShell no soporta `<`).
- Acentos/UTF-8: PowerShell corrompe caracteres; usar archivos UTF-8 sin BOM vía tools de edición,
  no here-strings de PS. Para JSON inline usar `curl.exe --data @archivo.json`.
- Apache corre en puerto 80. GD estaba deshabilitado (`;extension=gd`) en C:\xampp\php\php.ini → habilitado.
- gh CLI instalado (usuario cgnzlez326). Pedir credenciales al usuario si `gh auth` falla.

## Estructura destino del repo (raíz = C:\xampp\htdocs\sercotec-cdn)
```
wp-content/themes/cdn-santiago/   theme
wp-content/plugins/cdn-contenidos/ plugin
wp-content/mu-plugins/cdn-habilitaciones.php
bin/ (instalar-wordpress.ps1, seed.php, convertir-webp.php, importar-bd.bat)
docs/ (BUENAS_PRACTICAS, RETROSPECTIVA, DECISIONES)
postman/ (colección + environment)
README.md · AGENTS.md · REGISTRO-PROYECTO.md · .gitignore
```
WP core, uploads y wp-config.php quedan EXCLUIDOS de git pero DENTRO del ZIP.

## Requisitos e3 (resumen a cumplir)
1 Tarjeta servicio reutilizable (img+título+desc+btn Contáctanos → autocompleta formulario)
2 Carrusel testimonios accesible/responsive · 3 Integración CMS (+Postman)
4 Guía de buenas prácticas · 5 Git/GitHub ramas+PRs+README · 6 Nav y formulario centrado usuario
7 Optimización rendimiento · 8 Interactividad JS avanzada · 9 Consumo API REST dinámico
10 Seguridad formularios · 11 Retrospectiva y mejora continua

## Estado actual (06-09-2026)
- F0-F5 y docs listos: WP es_ES instalado, plugin/theme activos, seed corrido (8 servicios,
  6 testimonios, 10 FAQs, Nosotros, 6 ubicaciones, App Password Postman).
- GD habilitado y Apache reiniciado (probe: bool true). Assets min .min + WebP generados.
- REST /cdn/v1/* responden 200; contacto 201/400/200(honeypot) probados.
- Widget "Tipo de cambio" en barra superior (cabecera): consume API pública Gael Cloud
  `/general/public/monedas` 1 vez/día vía transient 24h (`cdn_monedas_gael_1`); muestra USD, EUR, UF,
  UTM con valores de referencia marcados si la API no responde. Funciones en `functions.php` del theme.
- Siguiente: git init en C:\xampp\htdocs\sercotec-cdn, ramas feature + gh PR (NO merge, NO push main),
  database.sql (mysqldump) y ZIP docente final.
- Credenciales reales SOLO en REGISTRO-PROYECTO.md (local, gitignored) y LEEME del ZIP.

## Fases (orden)
F0 prep (GD+Apache, BD, WP es_ES, wp-config, install) → F1 base código (theme/plugin/mu-plugin)
→ F2 landing+JS dinámico → F3 seguridad+rendimiento → F4 accesibilidad
→ F5 seed contenido → F6 docs+Postman+Git/PRs → F7 verificación+ZIP.
Avanzar actualizando ESTE archivo con decisiones/cambios relevantes.
