<?php
/**
 * Clase para manejar las peticiones AJAX de PostIt WP
 * 
 * @package PostIt_WP
 * @since 1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

class PostIt_Ajax {
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->init_hooks();
    }
    
    /**
     * Inicializar hooks AJAX
     */
    private function init_hooks() {
        add_action('wp_ajax_postit_wp_add_note', array($this, 'add_note'));
        add_action('wp_ajax_postit_wp_edit_note', array($this, 'edit_note'));
        add_action('wp_ajax_postit_wp_delete_note', array($this, 'delete_note'));
    }
    
    /**
     * Agregar nueva nota
     */
    public function add_note() {
        // Verificar nonce
        if (!wp_verify_nonce($_POST['nonce'], 'postit_wp_nonce')) {
            wp_die('Error de seguridad');
        }
        
        // Verificar permisos
        if (!current_user_can('manage_options')) {
            wp_die('Permisos insuficientes');
        }
        
        // Obtener datos
        $note_data = array(
            'note_text' => sanitize_textarea_field($_POST['note_data']['note_text']),
            'page_context' => sanitize_text_field($_POST['note_data']['page_context']),
            'user_id' => get_current_user_id()
        );
        
        // Validar datos
        if (empty($note_data['note_text'])) {
            wp_send_json_error('El contenido de la nota no puede estar vacío');
        }
        
        if (empty($note_data['page_context'])) {
            wp_send_json_error('El contexto de la página no puede estar vacío');
        }
        
        // Insertar nota
        $db = new PostIt_DB();
        $note_id = $db->insert_note($note_data);
        
        if ($note_id) {
            // Obtener la nota completa para la respuesta
            $note = $db->get_note_by_id($note_id);
            if ($note) {
                // Agregar información del usuario
                $user_info = get_userdata($note['user_id']);
                $note['author_name'] = $user_info ? $user_info->display_name : __('Usuario desconocido', 'postit-wp');
                
                wp_send_json_success(array('note' => $note));
            } else {
                wp_send_json_error('Error al obtener la nota creada');
            }
        } else {
            wp_send_json_error('Error al crear la nota');
        }
    }
    
    /**
     * Editar nota existente
     */
    public function edit_note() {
        // Verificar nonce
        if (!wp_verify_nonce($_POST['nonce'], 'postit_wp_nonce')) {
            wp_die('Error de seguridad');
        }
        
        // Verificar permisos
        if (!current_user_can('manage_options')) {
            wp_die('Permisos insuficientes');
        }
        
        // Obtener datos
        $note_id = intval($_POST['note_id']);
        $note_data = array(
            'note_text' => sanitize_textarea_field($_POST['note_data']['note_text']),
            'page_context' => sanitize_text_field($_POST['note_data']['page_context'])
        );
        
        // Validar datos
        if (empty($note_data['note_text'])) {
            wp_send_json_error('El contenido de la nota no puede estar vacío');
        }
        
        if (empty($note_data['page_context'])) {
            wp_send_json_error('El contexto de la página no puede estar vacío');
        }
        
        // Actualizar nota
        $db = new PostIt_DB();
        $success = $db->update_note($note_id, $note_data);
        
        if ($success) {
            // Obtener la nota actualizada
            $note = $db->get_note_by_id($note_id);
            if ($note) {
                // Agregar información del usuario
                $user_info = get_userdata($note['user_id']);
                $note['author_name'] = $user_info ? $user_info->display_name : __('Usuario desconocido', 'postit-wp');
                
                wp_send_json_success(array('note' => $note));
            } else {
                wp_send_json_error('Error al obtener la nota actualizada');
            }
        } else {
            wp_send_json_error('Error al actualizar la nota');
        }
    }
    
    /**
     * Eliminar nota
     */
    public function delete_note() {
        // Verificar nonce
        if (!wp_verify_nonce($_POST['nonce'], 'postit_wp_nonce')) {
            wp_die('Error de seguridad');
        }
        
        // Verificar permisos
        if (!current_user_can('manage_options')) {
            wp_die('Permisos insuficientes');
        }
        
        // Obtener ID de la nota
        $note_id = intval($_POST['note_id']);
        
        if (!$note_id) {
            wp_send_json_error('ID de nota inválido');
        }
        
        // Eliminar nota
        $db = new PostIt_DB();
        $success = $db->delete_note($note_id);
        
        if ($success) {
            wp_send_json_success('Nota eliminada exitosamente');
        } else {
            wp_send_json_error('Error al eliminar la nota');
        }
    }
} 