=== PostIt WP ===
Contributors: benjaminoscco
Tags: admin, notes, post-it, sticky-notes, productivity
Requires at least: 5.0
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Plugin que permite a los administradores dejar notas estilo post-it virtuales en el panel de administración de WordPress.

== Description ==

PostIt WP es un plugin de WordPress que permite a los administradores crear y gestionar notas estilo post-it virtuales que aparecen en el panel de administración. Estas notas son visibles solo para usuarios con permisos de administrador y se muestran como cuadros flotantes en la esquina inferior derecha de las páginas del admin.

= Características principales =

* **Notas específicas por contexto**: Cada nota está asociada a una página específica del admin
* **Interfaz intuitiva**: Las notas se muestran como post-its amarillos con efecto de rotación
* **Solo para administradores**: Las notas son visibles únicamente para usuarios con permisos de administrador
* **Panel de administración**: Interfaz dedicada para gestionar todas las notas
* **Estadísticas**: Información sobre el uso del plugin
* **Responsive**: Funciona correctamente en dispositivos móviles
* **Modo oscuro**: Compatible con el modo oscuro de WordPress

= Funcionalidades del MVP =

* Creación automática de tabla de base de datos al activar
* Renderizado de notas existentes en el admin
* Menú de administración "Notas Globales"
* Estilos CSS para notas estilo post-it
* Estructura preparada para futuras funcionalidades

= Próximas funcionalidades =

* Crear nuevas notas desde la interfaz
* Editar notas existentes
* Eliminar notas
* Filtros y búsqueda avanzada
* Notas con prioridad y colores
* Notificaciones en tiempo real

== Installation ==

1. Sube el plugin a la carpeta `/wp-content/plugins/`
2. Activa el plugin a través del menú 'Plugins' en WordPress
3. Accede a "Notas Globales" en el menú de administración
4. Las notas aparecerán automáticamente en las páginas del admin

== Frequently Asked Questions ==

= ¿Quién puede ver las notas? =

Solo los usuarios con permisos de administrador pueden ver las notas.

= ¿Dónde aparecen las notas? =

Las notas aparecen en la esquina inferior derecha de las páginas del panel de administración de WordPress.

= ¿Puedo crear notas desde la interfaz? =

En esta versión MVP, las notas se renderizan desde la base de datos. La funcionalidad de creación se implementará en futuras versiones.

= ¿Las notas son específicas por página? =

Sí, cada nota está asociada a un contexto específico de página y solo se muestra en esa página.

= ¿El plugin es compatible con el modo oscuro? =

Sí, el plugin incluye estilos específicos para el modo oscuro de WordPress.

== Screenshots ==

1. Notas estilo post-it en el panel de administración
2. Panel de administración "Notas Globales"
3. Estadísticas del plugin
4. Lista de todas las notas

== Changelog ==

= 1.0.0 =
* Lanzamiento inicial del plugin
* Creación de tabla de base de datos
* Renderizado de notas en el admin
* Panel de administración básico
* Estilos CSS para notas estilo post-it
* Estructura preparada para futuras funcionalidades

== Upgrade Notice ==

= 1.0.0 =
Primera versión del plugin con funcionalidades básicas de renderizado de notas.

== Developer Information ==

= Estructura del plugin =

```
postit-wp/
├── postit-wp.php              # Archivo principal del plugin
├── includes/
│   ├── class-postit-db.php    # Clase para manejo de base de datos
│   └── class-postit-render.php # Clase para renderizado de notas
├── admin/
│   └── postit-list.php        # Página de administración
├── assets/
│   ├── css/
│   │   └── admin.css          # Estilos CSS
│   └── js/
│       └── admin.js           # JavaScript (preparado para futuro)
└── readme.txt                 # Este archivo
```

= Hooks disponibles =

* `postit_wp_after_note_render` - Se ejecuta después de renderizar una nota
* `postit_wp_before_note_render` - Se ejecuta antes de renderizar una nota

= Filtros disponibles =

* `postit_wp_note_data` - Filtra los datos de una nota antes de renderizar
* `postit_wp_page_context` - Filtra el contexto de la página actual

= Funciones disponibles =

* `PostIt_DB::get_notes_by_context($context)` - Obtener notas por contexto
* `PostIt_DB::get_all_notes()` - Obtener todas las notas
* `PostIt_Render::render_admin_notes()` - Renderizar notas en el admin

== License ==

Este plugin está licenciado bajo GPL v2 o posterior.

== Author ==

**Benjamin Oscco Arias**

* GitHub: https://github.com/benjaminoscco
* Email: benjamin.oscco@gmail.com

== Support ==

Para soporte técnico o reportar bugs, por favor contacta al autor a través de:

* GitHub Issues: https://github.com/benjaminoscco/postit-wp/issues
* Email: benjamin.oscco@gmail.com

== Credits ==

* Desarrollado por Benjamin Oscco Arias
* Inspirado en la funcionalidad de notas adhesivas
* Compatible con WordPress 5.0+
* Requiere PHP 7.4 o superior 