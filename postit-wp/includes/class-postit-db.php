<?php
/**
 * Clase para manejar la base de datos de PostIt WP
 * 
 * @package PostIt_WP
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

class PostIt_DB {
    
    /**
     * Nombre de la tabla
     */
    private $table_name;
    
    /**
     * Constructor
     */
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'postit_notes';
    }
    
    /**
     * Crear tabla en la base de datos
     */
    public function create_table() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE {$this->table_name} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            note_text text NOT NULL,
            page_context varchar(255) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY page_context (page_context)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // Verificar si la tabla se creó correctamente
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$this->table_name}'") === $this->table_name;
        
        if (!$table_exists) {
            error_log('PostIt WP: Error al crear la tabla de base de datos');
            return false;
        }
        
        return true;
    }
    
    /**
     * Obtener notas por contexto de página
     * 
     * @param string $page_context Contexto de la página
     * @param int $user_id ID del usuario (opcional)
     * @return array Array de notas
     */
    public function get_notes_by_context($page_context, $user_id = null) {
        global $wpdb;
        
        $where_conditions = array();
        $where_values = array();
        
        // Filtrar por contexto de página
        $where_conditions[] = 'page_context = %s';
        $where_values[] = sanitize_text_field($page_context);
        
        // Filtrar por usuario si se especifica
        if ($user_id) {
            $where_conditions[] = 'user_id = %d';
            $where_values[] = intval($user_id);
        }
        
        $where_clause = implode(' AND ', $where_conditions);
        
        $sql = $wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE {$where_clause} ORDER BY created_at DESC",
            $where_values
        );
        
        return $wpdb->get_results($sql, ARRAY_A);
    }
    
    /**
     * Obtener todas las notas
     * 
     * @param int $limit Límite de resultados
     * @param int $offset Offset para paginación
     * @return array Array de notas
     */
    public function get_all_notes($limit = 50, $offset = 0) {
        global $wpdb;
        
        $sql = $wpdb->prepare(
            "SELECT n.*, u.display_name as author_name 
             FROM {$this->table_name} n 
             LEFT JOIN {$wpdb->users} u ON n.user_id = u.ID 
             ORDER BY n.created_at DESC 
             LIMIT %d OFFSET %d",
            $limit,
            $offset
        );
        
        return $wpdb->get_results($sql, ARRAY_A);
    }
    
    /**
     * Obtener una nota por ID
     * 
     * @param int $note_id ID de la nota
     * @return array|false Datos de la nota o false si no existe
     */
    public function get_note_by_id($note_id) {
        global $wpdb;
        
        $sql = $wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE id = %d",
            intval($note_id)
        );
        
        return $wpdb->get_row($sql, ARRAY_A);
    }
    
    /**
     * Insertar una nueva nota
     * 
     * @param array $note_data Datos de la nota
     * @return int|false ID de la nota insertada o false si falla
     */
    public function insert_note($note_data) {
        global $wpdb;
        
        $defaults = array(
            'user_id' => get_current_user_id(),
            'note_text' => '',
            'page_context' => '',
            'created_at' => current_time('mysql')
        );
        
        $note_data = wp_parse_args($note_data, $defaults);
        
        // Sanitizar datos
        $note_data['user_id'] = intval($note_data['user_id']);
        $note_data['note_text'] = sanitize_textarea_field($note_data['note_text']);
        $note_data['page_context'] = sanitize_text_field($note_data['page_context']);
        
        $result = $wpdb->insert(
            $this->table_name,
            $note_data,
            array('%d', '%s', '%s', '%s')
        );
        
        if ($result === false) {
            return false;
        }
        
        return $wpdb->insert_id;
    }
    
    /**
     * Actualizar una nota
     * 
     * @param int $note_id ID de la nota
     * @param array $note_data Datos a actualizar
     * @return bool True si se actualizó correctamente
     */
    public function update_note($note_id, $note_data) {
        global $wpdb;
        
        // Sanitizar datos
        if (isset($note_data['note_text'])) {
            $note_data['note_text'] = sanitize_textarea_field($note_data['note_text']);
        }
        if (isset($note_data['page_context'])) {
            $note_data['page_context'] = sanitize_text_field($note_data['page_context']);
        }
        
        $result = $wpdb->update(
            $this->table_name,
            $note_data,
            array('id' => intval($note_id)),
            array('%s', '%s'),
            array('%d')
        );
        
        return $result !== false;
    }
    
    /**
     * Eliminar una nota
     * 
     * @param int $note_id ID de la nota
     * @return bool True si se eliminó correctamente
     */
    public function delete_note($note_id) {
        global $wpdb;
        
        $result = $wpdb->delete(
            $this->table_name,
            array('id' => intval($note_id)),
            array('%d')
        );
        
        return $result !== false;
    }
    
    /**
     * Contar notas por contexto
     * 
     * @param string $page_context Contexto de la página
     * @return int Número de notas
     */
    public function count_notes_by_context($page_context) {
        global $wpdb;
        
        $sql = $wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table_name} WHERE page_context = %s",
            sanitize_text_field($page_context)
        );
        
        return intval($wpdb->get_var($sql));
    }
    
    /**
     * Obtener estadísticas de notas
     * 
     * @return array Estadísticas
     */
    public function get_stats() {
        global $wpdb;
        
        $stats = array();
        
        // Total de notas
        $stats['total_notes'] = intval($wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name}"));
        
        // Notas por contexto
        $stats['notes_by_context'] = $wpdb->get_results(
            "SELECT page_context, COUNT(*) as count 
             FROM {$this->table_name} 
             GROUP BY page_context 
             ORDER BY count DESC",
            ARRAY_A
        );
        
        // Notas por usuario
        $stats['notes_by_user'] = $wpdb->get_results(
            "SELECT user_id, COUNT(*) as count 
             FROM {$this->table_name} 
             GROUP BY user_id 
             ORDER BY count DESC",
            ARRAY_A
        );
        
        return $stats;
    }
} 