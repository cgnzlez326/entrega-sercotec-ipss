# Retrospectiva — Iteración Entrega 3 (Frontend WordPress)

Fecha: 06-09-2026
Participantes: Equipo de desarrollo CDN Santiago (registro de la sesión técnica).

## 1. Objetivo de la iteración
Entregar la landing del Centro de Desarrollo de Negocios Sercotec Santiago integrando WordPress
como CMS y su REST API para poblar las secciones dinámicas, cumpliendo los 11 puntos del encargo
`e3.md` y los indicadores de la `Rúbrica.md`.

## 2. Qué funcionó bien (mantener)
- **Reproducibilidad:** la instalación quedó automatizada (BD + `wp-config.php` + instalación por
  HTTP + `bin/seed.php`), lo que redujo el tiempo de puesta a punto de un entorno nuevo de ~40 a ~5
  minutos.
- **CMS como fuente de verdad:** al modelar servicios/testimonios/FAQ/ubicaciones como CPTs y
  servirlos por REST, el equipo editorial actualiza contenido sin tocar código.
- **Componentes reutilizables:** la tarjeta de servicio (imagen + título + descripción + botón
  "Contáctanos") se implementó una sola vez y se reutilizó para la autoselección del formulario.
- **Seguridad en capas:** honeypot + captcha HMAC + time-trap + rate limit resultaron fáciles de
  probar y de explicar al equipo.
- **Rendimiento:** la conversión a WebP con GD redujo el peso de imágenes en ~50 %.

## 3. Cuellos de botella (a mejorar)
- **Caracteres/UTF-8 en Windows:** PowerShell corrompe acentos al usar here-strings y redirecciones;
  se resolvió escribiendo archivos con herramientas de edición UTF-8 (sin BOM) y usando
  `curl.exe --data @archivo` y `cmd /c ... < archivo.sql`.
- **Minificación de JS:** sin Node.js en el equipo, el minificador es conservador (quita comentarios
  y colapsa blancos); no alcanza el nivel de un bundler como esbuild/Rollup.
- **Reinicio de Apache/GD:** activar `extension=gd` exige reiniciar Apache vía panel XAMPP;
  depende de una acción manual del desarrollador.
- **Doble render (servidor + cliente):** mantener coherencia visual entre contenido estático
  (hero/contacto) y dinámico (REST) requiere disciplina en el orden de carga y estados de espera.
- **Pruebas con usuarios:** las pruebas fueron con pares del equipo; falta incluir personas con
  discapacidad para alcanzar el nivel "sobresaliente" de accesibilidad.

## 4. Plan de acción para el próximo ciclo (SMART)
| Acción | Responsable | Meta medible | Plazo |
|---|---|---|---|
| Incorporar pruebas de accesibilidad con lectores de pantalla (NVDA) y usuarios con discapacidad | Equipo UX | ≥ 3 usuarios, ≥ 5 tareas, acta en docs | Fin próximo ciclo |
| Reemplazar minificador conservador por pipeline con Node/esbuild (CI) | Frontend | Tamaño bundle JS < 25 KB gzip | Próximo sprint |
| Documentar reinicio de Apache en la guía de instalación y validar GD automáticamente | Infra | Script de verificación de entorno | 1 semana |
| Definir convención para estados de carga/error entre render servidor y cliente | Frontend | Componente "Estado" compartido | 1 semana |
| Medir rendimiento con Lighthouse y registrar métricas | Equipo | Score ≥ 90 en Performance y Accessibility | Antes de entrega |

## 5. Lecciones aprendidas (difusión)
- El modelo **WordPress clásico + REST interna** satisface a la vez "integración de CMS",
  "consumo de endpoints" y "componentes reutilizables", con menor curva que una SPA headless.
- Mantener las claves/secretos fuera del repositorio (`wp-config.php`, uploads) y entregar el
  sitio funcionando vía ZIP + `database.sql` para evaluación.
- Registrar cada decisión en `REGISTRO-PROYECTO.md` para que cualquier integrante (humano o
  asistente) retome el hilo sin contexto previo.
