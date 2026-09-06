# Manual de uso del CMS — CDN Santiago

Guía para administrar el contenido de la landing **Centro de Desarrollo de Negocios
Sercotec Santiago** desde WordPress. Está orientada a editores/as de contenido (no requiere
programación).

> Las secciones **Nosotros, Servicios, Testimonios, Preguntas frecuentes y Ubicaciones** se
> publican desde este CMS y la portada las muestra de forma **dinámica vía la API REST interna**
> (`/wp-json/cdn/v1/*`). Es decir: **guardas → recargas la página → el cambio ya está visible**.

---

## 1. Acceso al panel

1. Inicia XAMPP (Apache + MySQL).
2. Entra a: `http://localhost/sercotec-cdn/wp-admin` (o `/wp-login.php`).
3. Credenciales:
   - Usuario: `admin`
   - Clave: ver `REGISTRO-PROYECTO.md` (en el ZIP, ver `LEEME_INSTALACION.txt`).
4. Menú del plugin **CDN Contenidos** no existe como tal: cada tipo aparece como su propio
   menú en la barra lateral (Servicios, Testimonios, Preguntas frecuentes, etc.).

**Tipos de contenido (qué es cada menú):**

| Menú en wp-admin | CPT | ¿Qué publica? |
|---|---|---|
| Servicios | `cdn_servicio` | Tarjetas de servicio (imagen + título + descripción + botón Contáctanos) |
| Testimonios | `cdn_testimonio` | Diapositivas del carrusel de testimonios |
| Preguntas frecuentes | `cdn_faq` | Acordeón de preguntas/respuestas |
| Ubicaciones | `cdn_ubicacion` | Tarjetas de puntos de atención |
| Sección Nosotros | `cdn_nosotros` | Contenido completo de la sección "Nosotros" (un único post) |
| Mensajes de contacto | `cdn_mensaje` | Mensajes enviados por el formulario (privado, solo lectura) |
| Áreas de servicio | taxonomía `cdn_area` | Etiqueta + color con que se agrupan/filtran los servicios |

---

## 2. Servicios (tarjetas)

Cada servicio es una tarjeta reutilizable con botón **"Contáctanos"** que autocompleta el
formulario de contacto.

- **Título**: nombre del servicio (ej. "Diagnóstico Empresarial").
- **Extracto** *(descripción de la tarjeta)*: texto breve que se muestra en la tarjeta.
  Recomendado: 1 o 2 frases (≤ ~200 caracteres).
- **Contenido**: texto adicional del servicio (no se muestra en la tarjeta; sirve como ficha).
- **Imagen destacada** (panel lateral → *Portada*): imagen ancha tipo banner (se muestra arriba
  de la tarjeta). Recomendado: ≥ 1200 px de ancho, formato 3:1 aprox.
- **Área de servicio** (panel lateral): asigna una de las 4 áreas → define el color/borde de la
  tarjeta y el filtro "Todos / área" de la sección.

**Orden**: los servicios se listan por `menu_order` (valor numérico). WordPress no muestra ese
campo por defecto en la pantalla de edición; para reordenar se usa la API REST/Postman o la
semilla (`bin/seed.php`). Si solo editas contenido, mantén el orden actual.

**Nota**: si dejas el *Extracto* vacío, la tarjeta usa el inicio del *Contenido* como descripción.

---

## 3. Testimonios (carrusel)

- **Título**: nombre de la persona/empresa que opina.
- **Contenido**: texto de la cita/testimonio.
- **Imagen destacada** (opcional): foto del autor; si no hay, se muestra un avatar con las
  iniciales.
- **Campos del contenido** (meta box *Campos del contenido*):
  - Cargo (ej. "Fundadora").
  - Empresa / Rubro (ej. "Pastelería Dulce Hogar").
  - Valoración (1 a 5): estrellas del carrusel.

Se muestran ordenados por fecha (más reciente primero).

---

## 4. Preguntas frecuentes (FAQ)

- **Título**: la pregunta (ej. "¿Los servicios del Centro tienen costo?").
- **Contenido**: la respuesta.
- **Campos del contenido**:
  - **Grupo**: etiqueta de agrupación (General, Atención, Servicios, Financiamiento…). El front
    agrupa las preguntas por este texto.
  - **Orden**: número (menor primero) para ordenar dentro del grupo.

---

## 5. Ubicaciones / puntos de atención

- **Título**: nombre del punto (ej. "Centro Satélite Providencia").
- **Campos del contenido**:
  - **Tipo** (select): Centro principal / Centro satélite / Punto de atención / Consultorio
    empresarial. El principal lleva estilo destacado.
  - **Dirección** (se usa para el botón "Cómo llegar" con Google Maps).
  - **Teléfono**, **Correo**, **Horario de atención**, **Asesor/a responsable**.
  - **Orden** (menor primero).

---

## 6. Sección "Nosotros"

Es **un único post** (creado por la semilla). **No lo dupliques ni lo borres**: edita el que
existe ("Nosotros - contenido").

- **Contenido**: párrafo de *propósito* de la sección.
- **Campos del contenido** (textarea):
  - **Texto introductorio** (`_cdn_lead`): párrafos separados por una **línea en blanco**
    (cada párrafo es un bloque del front).
  - **A quién está dirigido** (`_cdn_dirigido`): una línea por ítem.
  - **Cómo acompañamos** (`_cdn_metodo`): una línea por ítem.
  - **Cifras destacadas** (`_cdn_stats`): un **JSON por línea**, ej.:
    ```
    {"prefijo":"","valor":3200,"sufijo":"+","texto":"empresas atendidas directamente"}
    {"prefijo":"$","valor":22450,"sufijo":" millones","texto":"en aumento de ventas generado"}
    ```
    (`prefijo`/`sufijo`/`valor`/`texto`).
  - **Alianzas estratégicas** (`_cdn_alianzas`): una línea por alianza con formato
    `Nombre :: Descripción` (ej. `Sercotec :: Programa Centros de Desarrollo de Negocios`).

---

## 7. Mensajes de contacto (solo lectura)

Los mensajes del formulario quedan como entradas **privadas** de tipo `cdn_mensaje` (menú
**Mensajes de contacto**). No se pueden crear desde el panel.

- **Título**: `Contacto de <nombre> (<correo>)`.
- **Contenido**: mensaje del visitante.
- Metadatos en la ficha (columnas/edición): teléfono, servicio de interés, IP y agente del
  navegador (estos últimos con fines de auditoría).

El formulario aplica validación cliente/servidor, captcha, honeypot y límite de 5 envíos/hora
por IP.

---

## 8. Ajustes globales (hero + contacto + redes)

Menú **Ajustes → Contenido del sitio CDN**.

- **Hero / portada**: título, región, comunas, texto de bienvenida y textos de botones.
- **Contacto y redes**: teléfono, correo, dirección, horario, y URLs de Facebook/Instagram/
  WhatsApp (se pintan en la barra superior, el pie y la sección de contacto).

Se consumen en `GET /wp-json/cdn/v1/sitio`.

---

## 9. Cómo comprobar tus cambios

Tras publicar, recarga la portada (`Ctrl+Shift+R` en el navegador para evitar caché).

Para validar el contenido servido por la API abre en el navegador:

| Endpoint | Devuelve |
|---|---|
| `http://localhost/sercotec-cdn/wp-json/cdn/v1/sitio` | Hero, contacto, redes y ubicaciones |
| `.../cdn/v1/nosotros` | Contenido de Nosotros |
| `.../cdn/v1/servicios` | Servicios (agrega `?area=acompanamiento` para filtrar) |
| `.../cdn/v1/testimonios` | Testimonios |
| `.../cdn/v1/faqs` | Preguntas frecuentes |
| `POST .../cdn/v1/contacto` | Envío del formulario (con campos de seguridad) |

Gestión editorial (CRUD) sobre los CPT también disponible vía la REST nativa `wp/v2/cdn_*`
(colección de **Postman** incluida en `postman/`, usando el Application Password **"Postman CDN"**).

---

## 10. Reseteo del contenido (semilla)

Para regenerar todo el contenido de ejemplo de forma idempotente (no duplica si ya existe):

```
C:\xampp\php\php.exe bin\seed.php
```

Crea/actualiza: 8 servicios (con imágenes), 6 testimonios, 10 FAQs, la sección Nosotros,
6 ubicaciones y el Application Password de Postman. Activa theme/plugin si fuera necesario.

---

## 11. Solución de problemas

- **Una sección queda en blanco**: abre su endpoint REST (sección 9). Si responde vacío o
  error, revisa que el plugin **CDN Contenidos** esté activo (Plugins) y guarda los Permalinks
  en *Ajustes → Enlaces permanentes → Nombre de la entrada*.
- **Cambios no se ven**: recarga con `Ctrl+Shift+R`. Las secciones se traen por `fetch` a la
  API en cada carga, no hay caché de contenido.
- **No puedo subir imagen**: verifica `extension=gd` habilitada en `C:\xampp\php\php.ini` y
  reinicia Apache.
- **¿Dónde está la contraseña?** Solo en `REGISTRO-PROYECTO.md` (local) o en
  `LEEME_INSTALACION.txt` (ZIP docente).

---

*Manual generado para el proyecto académico. Marca y contenidos institucionales de SERCOTEC
usados con fines educativos.*
