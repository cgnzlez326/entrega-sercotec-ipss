# Guía de Buenas Prácticas Frontend — CDN Santiago

Documento que formaliza las convenciones y prácticas del equipo para el desarrollo frontend de
este proyecto (requisito #4 del encargo e3.md). Cada práctica incluye su porqué, cómo se aplica
aquí, herramientas y una métrica observable.

## Índice de prácticas

1. [Nomenclatura CSS: BEM + propiedades personalizadas](#1-nomenclatura-css-bem)
2. [Estructura modular de archivos](#2-estructura-modular)
3. [Componentes reutilizables (PHP y JS)](#3-componentes-reutilizables)
4. [Variables y configuración centralizada](#4-variables-y-configuracion)
5. [HTML semántico y accesibilidad WCAG 2.1 AA](#5-accesibilidad)
6. [Diseño centrado en el usuario (DCU) y usabilidad](#6-dcuy-usabilidad)
7. [Consumo de APIs con manejo de errores](#7-consumo-de-apis)
8. [Seguridad en formularios](#8-seguridad-en-formularios)
9. [Rendimiento web](#9-rendimiento-web)
10. [JavaScript modular y progresivo](#10-javascript)
11. [Control de versiones con ramas y PRs](#11-control-de-versiones)
12. [Documentación viva y mejora continua](#12-documentacion)

---

## 1. Nomenclatura CSS: BEM
**Qué:** nombrar clases con la convención BEM (Bloque, Elemento, Modificador) y colores/tipografía
en variables CSS (`:root`).
**Acción:** `.tarjeta-servicio`, `.tarjeta-servicio__titulo`, `.tarjeta-servicio__cta`;
variables `--azul`, `--naranja`, `--sombra` definidas en `assets/css/main.css`.
**Métrica:** 100 % de las clases nuevas con prefijo de bloque y cero colores "mágicos" en reglas.

## 2. Estructura modular
**Qué:** organizar el código por responsabilidad y reutilización.
**Acción:** las secciones son `template-parts/*.php`; los estilos agrupados por componente
(secciones, tarjetas, carrusel, formulario); los scripts por funcionalidad dentro de `cdn-app.js`.
**Métrica:** cada componente nuevo = 1 archivo de template + bloque CSS; sin lógica duplicada
entre secciones.

## 3. Componentes reutilizables
**Qué:** crear una única fuente de verdad para un elemento visual y usarla en todos los lugares.
**Acción:** `template-parts/tarjeta-*` en PHP y funciones puras JS (`componenteTarjetaServicio`,
`componenteTestimonio`, `componenteFaq`) que retornan HTML a partir de datos; la tarjeta de
servicio se usa en la grilla completa y, con el mismo marcado, se reutiliza para el caso de
"Contáctanos" que autocompleta el formulario.
**Métrica:** una tarjeta de servicio tiene **una sola implementación** de marcado; cualquier cambio
visual se hace en un lugar y se refleja en todas sus instancias.

## 4. Variables y configuración centralizada
**Qué:** evitar valores repetidos y URLs "hardcodeadas".
**Acción:** CSS variables en `:root`; la base de la REST API se inyecta con `wp_localize_script`
(`cdnApp.rest`) y los datos institucionales (teléfono, correo, redes, hero) viven en los ajustes
del sitio (plugin) accesibles por `cdn_ajuste()`.
**Métrica:** cero URLs completas del sitio dentro del JS; cada valor editable está en un solo lugar.

## 5. Accesibilidad
**Qué:** cumplir WCAG 2.1 nivel AA y probar con teclado y lectores.
**Acción:** HTML semántico y landmarks, enlace "Saltar al contenido", foco visible (`:focus-visible`),
contraste AA (azul `#0c6cb5` sobre blanco, blanco sobre `#0b2240`), `alt` descriptivos,
`aria-expanded`/`aria-controls` en menú y acordeón, carrusel con botones/puntos `aria-label`,
pausa al hover/foco, respeto a `prefers-reduced-motion`, y **toolbar de accesibilidad** en la página
(aumentar/disminuir texto, tonos de gris, contraste negativo, subrayar enlaces, fuente legible).
**Métrica:** 100 % de las funciones interactivas operan con teclado; contraste AA validado en
paletas principales; toolbar persistente vía `localStorage`.

## 6. DCU y usabilidad
**Qué:** diseñar centrado en el usuario y validar con pruebas.
**Acción:** tareas críticas en 1 clic (agendar/contactar), formulario con etiquetas visibles,
errores inline con `aria-live`, navegación fija con scrollspy, menú móvil accesible, "volver
arriba", y pantallas probadas en 3 tamaños (móvil/tablet/escritorio).
**Métrica:** prueba de tareas con ≥ 3 usuarios (registro en `docs/RETROSPECTIVA.md`); la tarea
"enviar consulta" no requiere más de 3 interacciones.

## 7. Consumo de APIs
**Qué:** consumir endpoints REST con `fetch`, validar respuestas y manejar errores/retry.
**Acción:** todas las secciones dinámicas leen `/wp-json/cdn/v1/*`; se usa `AbortController`
(timeout 10 s), se distingue `res.ok`, se muestra mensaje con botón "Reintentar" si falla y se
desactiva `aria-busy` cuando termina.
**Métrica:** ningún `fetch` sin control de error; indicador de carga visible durante la petición.

## 8. Seguridad en formularios
**Qué:** defender la capa servidor además del cliente.
**Acción:** validación y sanitización en servidor, honeypot, captcha aritmético HMAC stateless,
time-trap, rate limit por IP y almacenamiento privado de mensajes.
**Métrica:** el endpoint `POST /cdn/v1/contacto` rechaza (400/422/429) entradas inválidas, bots y
envíos rápidos; pruebas automatizadas en `F7` del registro.

## 9. Rendimiento web
**Qué:** optimizar el peso y la entrega de recursos.
**Acción:** WebP + SVG, `loading="lazy"`, minificación (`bin/minificar.php`), JS `defer`, cache y
gzip por `.htaccess`.
**Métrica:** tamaño de imágenes WebP < 50 % del original (medido en `REGISTRO-PROYECTO.md`).

## 10. JavaScript
**Qué:** JS modular, sin dependencias globales, con degradación elegante.
**Acción:** un solo bundle IIFE con módulos por función; detección de APIs (`IntersectionObserver`)
con fallback; contenido clave (hero/contacto) renderizado en servidor para funcionar sin JS.
**Métrica:** el hero y el formulario funcionan sin JavaScript; el resto degrada con aviso.

## 11. Control de versiones
**Qué:** trabajo colaborativo con ramas por funcionalidad y Pull Requests.
**Acción:** ramas `feature/*`, commits descriptivos en español, PRs revisadas antes de merge
(ver README §7); `main` no se modifica directamente.
**Métrica:** 1 rama por funcionalidad, 100 % de los cambios integrados vía PR.

## 12. Documentación
**Qué:** documentar decisiones y difundir el conocimiento.
**Acción:** `README.md` (arquitectura, instalación, componentes), `docs/DECISIONES.md`,
`REGISTRO-PROYECTO.md` y retrospectiva en `docs/RETROSPECTIVA.md`.
**Métrica:** todo cambio relevante queda registrado en la sesión de trabajo (bitácora).
