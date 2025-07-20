<?php
/**
 * Clase para renderizar las notas de PostIt WP
 * 
 * @package PostIt_WP
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

class PostIt_Render {
    
    /**
     * Instancia de la base de datos
     */
    private $db;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->db = new PostIt_DB();
    }
    
    /**
     * Renderizar notas en el admin
     */
    public function render_admin_notes() {
        // Solo mostrar para usuarios con permisos de administrador
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Obtener el contexto de la página actual
        $page_context = $this->get_current_page_context();
        
        // Obtener notas para este contexto
        $notes = $this->db->get_notes_by_context($page_context);
        
        // Si no hay notas para el contexto exacto, intentar con contextos similares
        if (empty($notes)) {
            // Intentar con diferentes variaciones del contexto
            $alternative_contexts = $this->get_alternative_contexts($page_context);
            
            foreach ($alternative_contexts as $alt_context) {
                $notes = $this->db->get_notes_by_context($alt_context);
                if (!empty($notes)) {
                    break;
                }
            }
        }
        
        // Renderizar las notas
        $this->render_notes_container($notes);
    }
    
    /**
     * Obtener el contexto de la página actual
     * 
     * @return string Contexto de la página
     */
    public function get_current_page_context() {
        // Obtener la URI actual
        $request_uri = $_SERVER['REQUEST_URI'];
        
        // Limpiar la URI
        $clean_uri = esc_url_raw($request_uri);
        
        // Remover parámetros de query string
        $path = parse_url($clean_uri, PHP_URL_PATH);
        
        // Si no hay path, usar la URI completa
        if (empty($path)) {
            $path = $clean_uri;
        }
        
        // Asegurar que el path comience con /
        if (!empty($path) && $path[0] !== '/') {
            $path = '/' . $path;
        }
        
        // Sanitizar el contexto
        $context = sanitize_text_field($path);
        
        return $context;
    }
    
    /**
     * Obtener contextos alternativos para buscar notas
     * 
     * @param string $page_context Contexto principal
     * @return array Array de contextos alternativos
     */
    private function get_alternative_contexts($page_context) {
        $alternatives = array();
        
        // Remover parámetros de query string si existen
        $base_path = parse_url($page_context, PHP_URL_PATH);
        if ($base_path !== $page_context) {
            $alternatives[] = $base_path;
        }
        
        // Intentar con diferentes variaciones
        $path_parts = explode('/', trim($page_context, '/'));
        
        // Si es una página de admin, intentar con wp-admin/
        if (in_array('wp-admin', $path_parts)) {
            $admin_index = array_search('wp-admin', $path_parts);
            if ($admin_index !== false) {
                // Intentar con wp-admin/ + el archivo
                if (isset($path_parts[$admin_index + 1])) {
                    $alternatives[] = '/wp-admin/' . $path_parts[$admin_index + 1];
                }
                // Intentar con wp-admin/ (página principal)
                $alternatives[] = '/wp-admin/';
            }
        }
        
        // Intentar con el path completo sin parámetros
        $alternatives[] = $base_path;
        
        return array_unique($alternatives);
    }
    
    /**
     * Renderizar el contenedor de notas
     * 
     * @param array $notes Array de notas
     */
    private function render_notes_container($notes) {
        ?>
        <div id="postit-wp-container" class="postit-wp-container">
            <?php foreach ($notes as $note): ?>
                <?php $this->render_single_note($note); ?>
            <?php endforeach; ?>
        </div>
        
        <!-- Botón flotante para crear nueva nota -->
        <div id="postit-wp-add-button" class="postit-wp-add-button" title="<?php _e('Agregar Nueva Nota', 'postit-wp'); ?>">
            <span class="dashicons dashicons-plus-alt2"></span>
        </div>
        
        <!-- Modal para crear/editar notas -->
        <div id="postit-wp-modal" class="postit-wp-modal">
            <div class="postit-wp-modal-content">
                <div class="postit-wp-modal-header">
                    <h3 id="postit-wp-modal-title"><?php _e('Nueva Nota', 'postit-wp'); ?></h3>
                    <span class="postit-wp-modal-close">&times;</span>
                </div>
                <form id="postit-wp-note-form">
                    <input type="hidden" id="postit-wp-note-id" name="note_id" value="">
                    <input type="hidden" id="postit-wp-note-context" name="page_context" value="<?php echo esc_attr($this->get_current_page_context()); ?>">
                    
                    <div class="postit-wp-form-field">
                        <label for="postit-wp-note-text"><?php _e('Contenido de la Nota:', 'postit-wp'); ?></label>
                        <textarea 
                            id="postit-wp-note-text" 
                            name="note_text" 
                            rows="4" 
                            required
                            placeholder="<?php esc_attr_e('Escribe tu nota aquí...', 'postit-wp'); ?>"
                        ></textarea>
                    </div>
                    
                    <div class="postit-wp-form-field">
                        <label for="postit-wp-note-context-display"><?php _e('Página donde aparecerá:', 'postit-wp'); ?></label>
                        <input 
                            type="text" 
                            id="postit-wp-note-context-display" 
                            value="<?php echo esc_attr($this->get_current_page_context()); ?>"
                            readonly
                        >
                    </div>
                    
                    <div class="postit-wp-form-actions">
                        <button type="submit" class="button button-primary">
                            <?php _e('Guardar Nota', 'postit-wp'); ?>
                        </button>
                        <button type="button" class="button postit-wp-modal-cancel">
                            <?php _e('Cancelar', 'postit-wp'); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <?php
    }
    
    /**
     * Renderizar una nota individual
     * 
     * @param array $note Datos de la nota
     */
    private function render_single_note($note) {
        $note_id = intval($note['id']);
        $note_text = esc_html($note['note_text']);
        $user_id = intval($note['user_id']);
        $created_at = $note['created_at'];
        
        // Obtener información del usuario
        $user_info = get_userdata($user_id);
        $author_name = $user_info ? esc_html($user_info->display_name) : __('Usuario desconocido', 'postit-wp');
        
        // Formatear fecha
        $formatted_date = date_i18n(
            get_option('date_format') . ' ' . get_option('time_format'),
            strtotime($created_at)
        );
        
        ?>
        <div class="postit-note" data-note-id="<?php echo $note_id; ?>">
            <div class="postit-note-header">
                <span class="postit-author"><?php echo $author_name; ?></span>
                <span class="postit-date"><?php echo $formatted_date; ?></span>
                <div class="postit-note-actions">
                    <button class="postit-edit-btn" title="<?php _e('Editar Nota', 'postit-wp'); ?>" data-note-id="<?php echo $note_id; ?>">
                        <span class="dashicons dashicons-edit"></span>
                    </button>
                    <button class="postit-delete-btn" title="<?php _e('Eliminar Nota', 'postit-wp'); ?>" data-note-id="<?php echo $note_id; ?>">
                        <span class="dashicons dashicons-trash"></span>
                    </button>
                </div>
            </div>
            <div class="postit-note-content">
                <?php echo wp_kses_post(wpautop($note_text)); ?>
            </div>
            <div class="postit-note-footer">
                <span class="postit-context"><?php echo esc_html($note['page_context']); ?></span>
            </div>
        </div>
        <?php
    }
    
    /**
     * Renderizar estadísticas de notas
     * 
     * @return void
     */
    public function render_stats() {
        $stats = $this->db->get_stats();
        
        if (empty($stats)) {
            return;
        }
        
        ?>
        <div class="postit-stats">
            <h3><?php _e('Estadísticas de Notas', 'postit-wp'); ?></h3>
            
            <div class="postit-stats-grid">
                <div class="postit-stat-item">
                    <span class="postit-stat-number"><?php echo intval($stats['total_notes']); ?></span>
                    <span class="postit-stat-label"><?php _e('Total de Notas', 'postit-wp'); ?></span>
                </div>
                
                <?php if (!empty($stats['notes_by_context'])): ?>
                <div class="postit-stat-item">
                    <h4><?php _e('Notas por Contexto', 'postit-wp'); ?></h4>
                    <ul>
                        <?php foreach (array_slice($stats['notes_by_context'], 0, 5) as $context_stat): ?>
                            <li>
                                <span class="context-name"><?php echo esc_html($context_stat['page_context']); ?></span>
                                <span class="context-count"><?php echo intval($context_stat['count']); ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($stats['notes_by_user'])): ?>
                <div class="postit-stat-item">
                    <h4><?php _e('Notas por Usuario', 'postit-wp'); ?></h4>
                    <ul>
                        <?php foreach (array_slice($stats['notes_by_user'], 0, 5) as $user_stat): ?>
                            <?php 
                            $user_info = get_userdata($user_stat['user_id']);
                            $user_name = $user_info ? $user_info->display_name : __('Usuario desconocido', 'postit-wp');
                            ?>
                            <li>
                                <span class="user-name"><?php echo esc_html($user_name); ?></span>
                                <span class="user-count"><?php echo intval($user_stat['count']); ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
    
    /**
     * Renderizar formulario de nueva nota (preparado para el futuro)
     * 
     * @param string $page_context Contexto de la página
     * @return void
     */
    public function render_note_form($page_context = '') {
        if (empty($page_context)) {
            $page_context = $this->get_current_page_context();
        }
        
        ?>
        <div class="postit-form-container">
            <h3><?php _e('Agregar Nueva Nota', 'postit-wp'); ?></h3>
            <form class="postit-note-form" method="post" action="">
                <?php wp_nonce_field('postit_wp_add_note', 'postit_wp_nonce'); ?>
                <input type="hidden" name="page_context" value="<?php echo esc_attr($page_context); ?>">
                
                <div class="form-field">
                    <label for="note_text"><?php _e('Contenido de la Nota:', 'postit-wp'); ?></label>
                    <textarea 
                        name="note_text" 
                        id="note_text" 
                        rows="4" 
                        cols="50" 
                        required
                        placeholder="<?php esc_attr_e('Escribe tu nota aquí...', 'postit-wp'); ?>"
                    ></textarea>
                </div>
                
                <div class="form-field">
                    <label for="note_context"><?php _e('Contexto de la Página:', 'postit-wp'); ?></label>
                    <input 
                        type="text" 
                        name="note_context" 
                        id="note_context" 
                        value="<?php echo esc_attr($page_context); ?>"
                        readonly
                    >
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="button button-primary">
                        <?php _e('Guardar Nota', 'postit-wp'); ?>
                    </button>
                </div>
            </form>
        </div>
        <?php
    }
    
    /**
     * Renderizar lista de notas para administración
     * 
     * @param array $notes Array de notas
     * @return void
     */
    public function render_notes_list($notes) {
        if (empty($notes)) {
            echo '<p>' . __('No hay notas disponibles.', 'postit-wp') . '</p>';
            return;
        }
        
        ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php _e('ID', 'postit-wp'); ?></th>
                    <th><?php _e('Autor', 'postit-wp'); ?></th>
                    <th><?php _e('Contenido', 'postit-wp'); ?></th>
                    <th><?php _e('Contexto', 'postit-wp'); ?></th>
                    <th><?php _e('Fecha', 'postit-wp'); ?></th>
                    <th><?php _e('Acciones', 'postit-wp'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($notes as $note): ?>
                    <?php $this->render_notes_list_row($note); ?>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php
    }
    
    /**
     * Renderizar una fila de la lista de notas
     * 
     * @param array $note Datos de la nota
     * @return void
     */
    private function render_notes_list_row($note) {
        $note_id = intval($note['id']);
        $note_text = esc_html($note['note_text']);
        $user_id = intval($note['user_id']);
        $page_context = esc_html($note['page_context']);
        $created_at = $note['created_at'];
        
        // Obtener información del usuario
        $user_info = get_userdata($user_id);
        $author_name = $user_info ? esc_html($user_info->display_name) : __('Usuario desconocido', 'postit-wp');
        
        // Formatear fecha
        $formatted_date = date_i18n(
            get_option('date_format') . ' ' . get_option('time_format'),
            strtotime($created_at)
        );
        
        ?>
        <tr>
            <td><?php echo $note_id; ?></td>
            <td><?php echo $author_name; ?></td>
            <td>
                <div class="note-content-preview">
                    <?php echo wp_trim_words($note_text, 20); ?>
                </div>
            </td>
            <td><?php echo $page_context; ?></td>
            <td><?php echo $formatted_date; ?></td>
            <td>
                <div class="row-actions">
                    <span class="edit">
                        <a href="#" class="edit-note" data-note-id="<?php echo $note_id; ?>">
                            <?php _e('Editar', 'postit-wp'); ?>
                        </a>
                    </span>
                    <span class="delete">
                        <a href="#" class="delete-note" data-note-id="<?php echo $note_id; ?>">
                            <?php _e('Eliminar', 'postit-wp'); ?>
                        </a>
                    </span>
                </div>
            </td>
        </tr>
        <?php
    }
} 