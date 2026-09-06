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
2. Entra a `http://localhost/sercotec-cdn/wp-admin` (o `/wp-login.php`).
3. Credenciales:
   - Usuario: `admin`
   - Clave: ver `REGISTRO-PROYECTO.md` (en el ZIP, ver `LEEME_INSTALACION.txt`).

No existe un menú contenedor "CDN Contenidos": cada tipo de contenido aparece como su **propio
menú** en la barra lateral.

| Menú en wp-admin | Tipo | ¿Qué publica? |
|---|---|---|
| Servicios | `cdn_servicio` | Tarjetas de servicio (imagen + título + descripción + botón Contáctanos) |
| Testimonios | `cdn_testimonio` | Diapositivas del carrusel de testimonios |
| Preguntas frecuentes | `cdn_faq` | Acordeón de preguntas/respuestas |
| Ubicaciones | `cdn_ubicacion` | Tarjetas de puntos de atención |
| Sección Nosotros | `cdn_nosotros` | Contenido completo de la sección "Nosotros" (un único post) |
| Mensajes de contacto | `cdn_mensaje` | Mensajes del formulario (privados, solo lectura) |
| Áreas de servicio | taxonomía `cdn_area` | Etiqueta + color con que se agrupan/filtran los servicios |

---

## 2. Servicios (tarjetas)

Cada servicio es una tarjeta reutilizable con botón **"Contáctanos"** que autocompleta el
formulario de contacto.

- **Título**: nombre del servicio (ej. "Diagnóstico Empresarial").
- **Extracto** *(descripción de la tarjeta)*: texto breve que se muestra en la tarjeta.
  Recomendado: 1 o 2 frases (≤ ~200 caracteres). Si se deja vacío, se usa el inicio del Contenido.
- **Contenido**: texto adicional del servicio (no se muestra en la tarjeta; sirve como ficha).
- **Imagen destacada** (panel lateral → *Portada*): imagen ancha tipo banner que se muestra
  arriba de la tarjeta. Recomendado: ≥ 1200 px de ancho (proporción ~3:1).
- **Área de servicio** (panel lateral): asigna una de las 4 áreas → define el color/borde de la
  tarjeta y su filtro en la sección.

> **Orden de los servicios**: se ordenan por `menu_order`. WordPress no expone ese campo en la
> pantalla de edición; para reordenarlos se usa la API/Postman o la semilla (`bin/seed.php`).
> Si solo editas contenido, mantén el orden actual.

---

## 3. Testimonios (carrusel)

- **Título**: nombre de la persona/empresa.
- **Contenido**: texto de la cita/testimonio.
- **Imagen destacada** (opcional): foto del autor. Si no hay, se muestra un avatar con iniciales.
- **Campos del contenido** (meta box *Campos del contenido*):
  - Cargo (ej. "Fundadora").
  - Empresa / Rubro (ej. "Pastelería Dulce Hogar").
  - Valoración (1 a 5): cantidad de estrellas del carrusel.

Se muestran ordenados por fecha (más reciente primero).

---

## 4. Preguntas frecuentes (FAQ)

- **Título**: la pregunta (ej. "¿Los servicios del Centro tienen costo?").
- **Contenido**: la respuesta.
- **Campos del contenido**:
  - **Grupo**: etiqueta que agrupa las preguntas (General, Atención, Servicios, Financiamiento…).
  - **Orden**: número (menor primero) para ordenar dentro del grupo.

---

## 5. Ubicaciones / puntos de atención

- **Título**: nombre del punto (ej. "Centro Satélite Providencia").
- **Campos del contenido**:
  - **Tipo** (select): Centro principal / Centro satélite / Punto de atención / Consultorio
    empresarial. El "Centro principal" recibe estilo destacado.
  - **Dirección**: se usa para el botón "Cómo llegar" (Google Maps).
  - **Teléfono**, **Correo**, **Horario de atención**, **Asesor/a responsable**.
  - **Orden** (menor primero).

---

## 6. Sección "Nosotros"

Es **un único post** (creado por la semilla). **No lo dupliques ni lo borres**: edita el que ya
existe ("Nosotros - contenido").

- **Contenido**: párrafo de *propósito* de la sección.
- **Campos del contenido** (textarea):
  - **Texto introductorio** (`_cdn_lead`): párrafos separados por una **línea en blanco**
    (cada párrafo se muestra como bloque).
  - **A quién está dirigido** (`_cdn_dirigido`): una línea por ítem.
  - **Cómo acompañamos** (`_cdn_metodo`): una línea por ítem.
  - **Cifras destacadas** (`_cdn_stats`): un **JSON por línea**. Ejemplo:

```
{"prefijo":"","valor":3200,"sufijo":"+","texto":"empresas atendidas directamente"}
{"prefijo":"$","valor":22450,"sufijo":" millones","texto":"en aumento de ventas generado"}
```

  - **Alianzas estratégicas** (`_cdn_alianzas`): una línea por alianza, con formato
    `Nombre :: Descripción`. Ejemplo:

```
Sercotec :: Programa Centros de Desarrollo de Negocios
Hub Santiago :: Espacio de emprendimiento de la Municipalidad de Santiago
```

---

## 7. Mensajes de contacto (solo lectura)

Los mensajes enviados por el formulario se guardan como entradas **privadas** del tipo
`cdn_mensaje` (menú **Mensajes de contacto**). No se pueden crear desde el panel.

- **Título**: `Contacto de <nombre> (<correo>)`.
- **Contenido**: texto del mensaje.
- El **teléfono** y el **servicio de interés** que eligió el visitante se guardan como metadatos
  del mensaje (no se muestran en el editor por defecto). La IP y el agente del navegador también
  quedan registrados con fines de auditoría.

Para que teléfono/servicio sean visibles al revisar mensajes habría que añadir columnas o un
meta box en el panel (fuera del alcance de este manual).

---

## 8. Configuración global del sitio

Menú **Ajustes → Contenido del sitio CDN** (pantalla de opciones del plugin).

- **Hero / portada**: título, región, comunas, texto de bienvenida y textos de los botones.
- **Contacto y redes**: teléfono, correo, dirección, horario y URLs de Facebook, Instagram y
  WhatsApp (se usan en la barra superior, el pie y la sección de contacto).

Estos valores se consumen en `GET /wp-json/cdn/v1/sitio`.

---

## 9. Cómo comprobar tus cambios

Tras publicar, recarga la portada con **Ctrl+Shift+R** (para evitar caché del navegador).

Para validar lo que sirve la API, abre en el navegador (base
`http://localhost/sercotec-cdn/wp-json`):

| Endpoint | Devuelve |
|---|---|
| `/cdn/v1/sitio` | Hero, contacto, redes y ubicaciones |
| `/cdn/v1/nosotros` | Contenido de la sección Nosotros |
| `/cdn/v1/servicios` | Servicios (agregar `?area=acompanamiento` filtra por área) |
| `/cdn/v1/testimonios` | Testimonios |
| `/cdn/v1/faqs` | Preguntas frecuentes |

El envío del formulario usa `POST /cdn/v1/contacto` (con captcha, honeypot y límite por IP).

La gestión editorial (CRUD) de los tipos de contenido también está disponible vía la REST
nativa `wp/v2/cdn_*` usando la colección de **Postman** de `postman/` y el Application Password
**"Postman CDN"**.

---

## 10. Reseteo del contenido (semilla)

Para regenerar el contenido de ejemplo de forma idempotente (no duplica si ya existe):

```
C:\xampp\php\php.exe bin\seed.php
```

Crea o actualiza: 8 servicios (con imágenes), 6 testimonios, 10 preguntas frecuentes, la sección
Nosotros, 6 ubicaciones y el Application Password de Postman. También activa theme/plugin si
hace falta.

---

## 11. Solución de problemas

- **Una sección queda en blanco**: abre su endpoint REST (sección 9). Si responde vacío o con
  error, revisa que el plugin **CDN Contenidos** esté activo (*Plugins*) y guarda los Permalinks
  en *Ajustes → Enlaces permanentes → Nombre de la entrada*.
- **Los cambios no se ven**: recarga con **Ctrl+Shift+R**. Las secciones se obtienen por
  `fetch` a la API en cada carga; no hay caché de contenido.
- **No puedo subir imágenes**: verifica que `extension=gd` esté habilitada en
  `C:\xampp\php\php.ini` y reinicia Apache.
- **¿Dónde está la contraseña?** Solo en `REGISTRO-PROYECTO.md` (local) o en
  `LEEME_INSTALACION.txt` (ZIP docente).

---
