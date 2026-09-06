# Centro de Desarrollo de Negocios Sercotec Santiago — Frontend (Entrega 3)

Landing page construida con **WordPress (XAMPP)** que replica la estructura y servicios del
sitio de referencia: https://sitios.sercotec.cl/centros-de-negocios/centro-de-desarrollo-de-negocios-santiago/

Repositorio público: https://github.com/cgnzlez326/entrega-sercotec-ipss

## 1. Contexto y alcance

El **Centro de Desarrollo de Negocios Sercotec Santiago** apoya a micro, pequeñas empresas y
cooperativas con asesoría gratuita, capacitación y vinculación. Este proyecto entrega una landing
one-page que integra un CMS (WordPress) y consume su propia **API REST** para poblar de forma
dinámica las secciones **Nosotros**, **Servicios** y **Preguntas frecuentes (FAQ)**, junto con
testimonios en carrusel, ubicaciones y un formulario de contacto seguro.

Requisitos cubiertos (encargo `e3.md`): ver checklist en `docs/DECISIONES.md` y `REGISTRO-PROYECTO.md`.

## 2. Arquitectura

```
htdocs/sercotec-cdn/                     (WordPress + repo de código)
├─ wp-content/
│  ├─ themes/cdn-santiago/               Theme clásico PHP
│  │  ├─ template-parts/                 Componentes reutilizables (PHP)
│  │  │  ├─ section-hero.php · section-nosotros.php · section-servicios.php
│  │  │  ├─ section-testimonios.php · section-faq.php
│  │  │  ├─ section-ubicaciones.php · section-contacto.php
│  │  └─ assets/ (css, js, img + versiones .min y .webp)
│  ├─ plugins/cdn-contenidos/            Plugin: CPTs + taxonomía + API REST + ajustes
│  │  └─ inc/ (post-types, meta, captcha, rest, ajustes)
│  └─ mu-plugins/cdn-habilitaciones.php  Application Passwords (local) + flush
├─ bin/
│  ├─ instalar-wordpress.ps1             Instalación reproducible para otro colaborador
│  ├─ seed.php                           Contenido editorial (idempotente)
│  ├─ minificar.php                      Genera .min de CSS/JS
│  ├─ convertir-webp.php                 Convierte imágenes a WebP (GD)
│  └─ importar-bd.bat                    Importa database.sql en el ZIP docente
├─ docs/  (BUENAS_PRACTICAS.md · DECISIONES.md · RETROSPECTIVA.md)
├─ postman/  (colección + environment)
└─ README.md · AGENTS.md · REGISTRO-PROYECTO.md · .gitignore
```

**Flujo de datos:** el theme pinta la estructura (hero, nav, contacto) y cada sección dinámica
hace `fetch()` a los endpoints `/wp-json/cdn/v1/*`; el contenido vive en tipos de contenido
editoriales (`cdn_servicio`, `cdn_testimonio`, `cdn_faq`, `cdn_ubicacion`, `cdn_nosotros`).

### Tipos de contenido (CMS)
| CPT | Uso | Rest |
|---|---|---|
| `cdn_servicio` | Tarjetas de servicio (imagen, título, descripción, botón Contáctanos) | `GET /cdn/v1/servicios` |
| `cdn_testimonio` | Carrusel de testimonios | `GET /cdn/v1/testimonios` |
| `cdn_faq` | Acordeón de preguntas | `GET /cdn/v1/faqs` |
| `cdn_ubicacion` | Puntos de atención | parte de `GET /cdn/v1/sitio` |
| `cdn_nosotros` | Sección Nosotros (textos, cifras, alianzas) | `GET /cdn/v1/nosotros` |
| `cdn_mensaje` | Mensajes del formulario (privado) | `POST /cdn/v1/contacto` |

Taxonomía `cdn_area` (4 áreas) con color asociado; se usa para filtrar servicios.

### Endpoints de la API REST (namespace `cdn/v1`)
| Método | Ruta | Descripción |
|---|---|---|
| GET | `/wp-json/cdn/v1/sitio` | Hero, contacto, redes y ubicaciones |
| GET | `/wp-json/cdn/v1/nosotros` | Contenido de la sección Nosotros |
| GET | `/wp-json/cdn/v1/servicios?area={slug}` | Servicios (filtrable por área) |
| GET | `/wp-json/cdn/v1/testimonios` | Testimonios |
| GET | `/wp-json/cdn/v1/faqs` | Preguntas frecuentes |
| POST | `/wp-json/cdn/v1/contacto` | Envía mensaje (validado y seguro) |

Además quedan expuestos los endpoints nativos `wp/v2/cdn_*` para la gestión editorial CRUD
(ver colección de Postman).

## 3. Instalación (paso a paso)

Prerrequisitos: **XAMPP** (Apache + MySQL + PHP ≥ 7.4 con `extension=gd`), Windows.

### A) Sitio ya armado (recomendado para docente/evaluación)
Ver `LEEME_INSTALACION.txt` incluido en el ZIP.

### B) Reproducir desde el repositorio (colaboración)
1. Clonar el repo dentro de `C:\xampp\htdocs\`:
   ```powershell
   git clone https://github.com/cgnzlez326/entrega-sercotec-ipss sercotec-cdn
   cd sercotec-cdn
   ```
2. Ejecutar el instalador (descarga WordPress es_ES, crea la BD, configura e instala):
   ```powershell
   powershell -ExecutionPolicy Bypass -File .\bin\instalar-wordpress.ps1
   ```
3. Sembrar el contenido editorial:
   ```powershell
   C:\xampp\php\php.exe .\bin\seed.php
   ```
4. Abrir http://localhost/sercotec-cdn

Acceso wp-admin: usuario `admin`. La contraseña autogenerada queda indicada al final del instalador
y se documenta localmente en `REGISTRO-PROYECTO.md`.

## 4. Uso de componentes (guía con ejemplos)

### Componente PHP reutilizable (template part)
Las secciones son partials en `template-parts/`. Para usarlas en una página/template:

```php
<?php get_header(); ?>
<?php get_template_part( 'template-parts/section', 'hero' ); ?>       <!-- Hero -->
<?php get_template_part( 'template-parts/section', 'contacto' ); ?>   <!-- Formulario -->
<?php get_footer(); ?>
```

Helpers de theme (en `functions.php`):
```php
cdn_asset( 'css/main.min.css' );   // URL con versión
cdn_img( 'hero-banner' );          // Devuelve WebP si existe
cdn_icono( 'telefono' );           // SVG inline accesible
cdn_ajuste( 'contacto_correo' );   // Lee ajustes del sitio (plugin)
cdn_plantilla_carga( 4 );          // Esqueleto de carga (skeleton)
```

### Componente JS reutilizable (función pura que retorna HTML)
Definidas en `assets/js/cdn-app.js`:

```js
// Renderiza una tarjeta de servicio (imagen, título, descripción, CTA)
var html = componenteTarjetaServicio({
    titulo: "Diagnóstico Empresarial",
    descripcion: "Evaluación inicial gratuita de tu negocio.",
    imagen: "http://localhost/sercotec-cdn/wp-content/uploads/...",
    area: { nombre: "Acompañamiento", color: "#0c6cb5" }
});
document.getElementById("grid-servicios").innerHTML += html;
```

Las tarjetas con `data-contactar-servicio` autocompletan el `<select>` del formulario de contacto
y desplazan la vista hasta él (requisito #1 del encargo).

### Consumir la API desde JavaScript
```js
fetch("/wp-json/cdn/v1/servicios?area=gestion")
  .then(function (r) { return r.json(); })
  .then(function (datos) { console.log(datos); });
```

## 5. Seguridad del formulario de contacto
- Validación en cliente (HTML5 + JS) y en servidor (endpoint `POST /cdn/v1/contacto`).
- Sanitización con `sanitize_text_field`/`sanitize_email`/`sanitize_textarea_field`.
- **Honeypot** (campo oculto) y **captcha aritmético stateless** (HMAC con `wp_hash` + salt) y
  **time-trap** (mínimo 2 s de llenado humano).
- **Rate limit** por IP (5 envíos/hora) y almacenamiento en CPT privado `cdn_mensaje`.
- Cabeceras de seguridad en el front (`X-Content-Type-Options`, `X-Frame-Options`, etc.).

## 6. Rendimiento
- Imágenes WebP generadas con GD (`bin/convertir-webp.php`) + SVG para íconos/logo.
- `loading="lazy"` y `decoding="async"` en imágenes, `fetchpriority="high"` solo en hero.
- CSS/JS minificados (`bin/minificar.php`) y servidos como `.min`.
- JS diferido (`defer`) y sin librerías externas pesadas.
- Cache de recursos + gzip vía `.htaccess` (theme `assets/` y `uploads/`).

## 7. Control de versiones
Flujo Git/GitHub con ramas por funcionalidad y Pull Requests:
```
main (protegida)
 └─ feature/wordpress-setup
 └─ feature/cpt-api-rest
 └─ feature/landing-hero-nosotros
 └─ feature/servicios-contacto
 └─ feature/testimonios-faq
 └─ feature/seguridad-rendimiento
 └─ feature/documentacion-postman
```
Convención de commits en español, descriptivos (ver `docs/BUENAS_PRACTICAS.md`).

## 8. Documentación adicional
- `docs/BUENAS_PRACTICAS.md` — guía de buenas prácticas frontend (requisito #4).
- `docs/RETROSPECTIVA.md` — retrospectiva del equipo (requisito #11).
- `docs/DECISIONES.md` — decisiones técnicas y problemas complejos resueltos.
- `REGISTRO-PROYECTO.md` — bitácora de instalación y avance.
- `postman/` — colección de Postman para gestionar y probar el CMS (requisito #3).

## 9. Licencia y atribución
Proyecto académico. Los textos y gráficos institucionales de SERCOTEC se usan con fines
educativos; marca y materiales pertenecen a SERCOTEC.
