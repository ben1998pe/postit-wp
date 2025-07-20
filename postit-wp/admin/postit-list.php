<?php
/**
 * Página de administración de PostIt WP
 * 
 * @package PostIt_WP
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

class PostIt_List_Page {
    
    /**
     * Renderizar la página de administración
     */
    public static function render() {
        // Verificar permisos
        if (!current_user_can('manage_options')) {
            wp_die(__('No tienes permisos para acceder a esta página.', 'postit-wp'));
        }
        
        // Obtener datos
        $db = new PostIt_DB();
        $renderer = new PostIt_Render();
        
        // Obtener notas para la lista
        $notes = $db->get_all_notes();
        
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">
                <?php _e('Notas Globales', 'postit-wp'); ?>
            </h1>
            
            <hr class="wp-header-end">
            
            <div class="postit-admin-container">
                <!-- Estadísticas -->
                <div class="postit-admin-section">
                    <?php $renderer->render_stats(); ?>
                </div>
                
                <!-- Lista de notas -->
                <div class="postit-admin-section">
                    <h2><?php _e('Todas las Notas', 'postit-wp'); ?></h2>
                    <?php $renderer->render_notes_list($notes); ?>
                </div>
                
                <!-- Información del plugin -->
                <div class="postit-admin-section">
                    <h2><?php _e('Información del Plugin', 'postit-wp'); ?></h2>
                    <div class="postit-info">
                        <p>
                            <strong><?php _e('Versión:', 'postit-wp'); ?></strong> 
                            <?php echo POSTIT_WP_VERSION; ?>
                        </p>
                        <p>
                            <strong><?php _e('Autor:', 'postit-wp'); ?></strong> 
                            Benjamin Oscco Arias
                        </p>
                        <p>
                            <strong><?php _e('Descripción:', 'postit-wp'); ?></strong> 
                            <?php _e('Plugin que permite a los administradores dejar notas estilo post-it virtuales en el panel de administración de WordPress.', 'postit-wp'); ?>
                        </p>
                        <p>
                            <strong><?php _e('Funcionalidades:', 'postit-wp'); ?></strong>
                        </p>
                        <ul>
                            <li><?php _e('Notas visibles solo para administradores', 'postit-wp'); ?></li>
                            <li><?php _e('Notas específicas por contexto de página', 'postit-wp'); ?></li>
                            <li><?php _e('Interfaz de administración para gestionar notas', 'postit-wp'); ?></li>
                            <li><?php _e('Estadísticas de uso del plugin', 'postit-wp'); ?></li>
                        </ul>
                    </div>
                </div>
                
                <!-- Instrucciones de uso -->
                <div class="postit-admin-section">
                    <h2><?php _e('Instrucciones de Uso', 'postit-wp'); ?></h2>
                    <div class="postit-instructions">
                        <h3><?php _e('Cómo usar las notas:', 'postit-wp'); ?></h3>
                        <ol>
                            <li>
                                <?php _e('Las notas aparecerán automáticamente en la esquina inferior derecha de las páginas del admin.', 'postit-wp'); ?>
                            </li>
                            <li>
                                <?php _e('Cada nota está asociada a un contexto específico de página.', 'postit-wp'); ?>
                            </li>
                            <li>
                                <?php _e('Las notas se muestran como post-its amarillos flotantes.', 'postit-wp'); ?>
                            </li>
                            <li>
                                <?php _e('Solo los usuarios con permisos de administrador pueden ver las notas.', 'postit-wp'); ?>
                            </li>
                        </ol>
                        
                        <h3><?php _e('Próximas funcionalidades:', 'postit-wp'); ?></h3>
                        <ul>
                            <li><?php _e('Crear nuevas notas desde la interfaz', 'postit-wp'); ?></li>
                            <li><?php _e('Editar notas existentes', 'postit-wp'); ?></li>
                            <li><?php _e('Eliminar notas', 'postit-wp'); ?></li>
                            <li><?php _e('Filtros y búsqueda avanzada', 'postit-wp'); ?></li>
                            <li><?php _e('Notas con prioridad y colores', 'postit-wp'); ?></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        
        <style>
            .postit-admin-container {
                margin-top: 20px;
            }
            
            .postit-admin-section {
                background: #fff;
                border: 1px solid #ccd0d4;
                border-radius: 4px;
                padding: 20px;
                margin-bottom: 20px;
                box-shadow: 0 1px 1px rgba(0,0,0,.04);
            }
            
            .postit-admin-section h2 {
                margin-top: 0;
                color: #23282d;
                border-bottom: 1px solid #eee;
                padding-bottom: 10px;
            }
            
            .postit-info p {
                margin: 8px 0;
            }
            
            .postit-info ul {
                margin-left: 20px;
            }
            
            .postit-instructions ol,
            .postit-instructions ul {
                margin-left: 20px;
            }
            
            .postit-instructions li {
                margin-bottom: 8px;
            }
            
            .postit-stats-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 20px;
                margin-top: 15px;
            }
            
            .postit-stat-item {
                background: #f9f9f9;
                padding: 15px;
                border-radius: 4px;
                border-left: 4px solid #0073aa;
            }
            
            .postit-stat-number {
                display: block;
                font-size: 2em;
                font-weight: bold;
                color: #0073aa;
            }
            
            .postit-stat-label {
                display: block;
                color: #666;
                font-size: 0.9em;
            }
            
            .note-content-preview {
                max-width: 300px;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }
            
            .row-actions {
                visibility: hidden;
            }
            
            tr:hover .row-actions {
                visibility: visible;
            }
            
            .row-actions span {
                margin-right: 10px;
            }
            
            .row-actions a {
                color: #0073aa;
                text-decoration: none;
            }
            
            .row-actions a:hover {
                color: #005a87;
            }
        </style>
        <?php
    }
} 