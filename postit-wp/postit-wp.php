<?php
/**
 * Plugin Name: PostIt WP
 * Plugin URI: https://github.com/benjaminoscco/postit-wp
 * Description: Plugin que permite a los administradores dejar notas estilo post-it virtuales en el panel de administración de WordPress.
 * Version: 1.0.0
 * Author: Benjamin Oscco Arias
 * Author URI: https://github.com/benjaminoscco
 * Text Domain: postit-wp
 * Domain Path: /languages
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

// Definir constantes del plugin
define('POSTIT_WP_VERSION', '1.0.0');
define('POSTIT_WP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('POSTIT_WP_PLUGIN_URL', plugin_dir_url(__FILE__));
define('POSTIT_WP_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Clase principal del plugin PostIt WP
 */
class PostIt_WP {
    
    /**
     * Constructor de la clase
     */
    public function __construct() {
        $this->init_hooks();
    }
    
    /**
     * Inicializar hooks del plugin
     */
    private function init_hooks() {
        // Hooks de activación y desactivación
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // Hooks de inicialización
        add_action('init', array($this, 'init'));
        add_action('admin_init', array($this, 'admin_init'));
        
        // Agregar menú de administración (debe ejecutarse antes que admin_init)
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Cargar archivos del plugin
        $this->load_dependencies();
    }
    
    /**
     * Cargar dependencias del plugin
     */
    private function load_dependencies() {
        // Cargar clases principales
        require_once POSTIT_WP_PLUGIN_DIR . 'includes/class-postit-db.php';
        require_once POSTIT_WP_PLUGIN_DIR . 'includes/class-postit-render.php';
        require_once POSTIT_WP_PLUGIN_DIR . 'includes/class-postit-ajax.php';
        
        // Cargar archivo de administración
        if (is_admin()) {
            require_once POSTIT_WP_PLUGIN_DIR . 'admin/postit-list.php';
        }
    }
    
    /**
     * Inicialización del plugin
     */
    public function init() {
        // Cargar text domain para internacionalización
        load_plugin_textdomain('postit-wp', false, dirname(POSTIT_WP_PLUGIN_BASENAME) . '/languages');
    }
    
    /**
     * Inicialización del admin
     */
    public function admin_init() {
        // Registrar scripts y estilos
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        
        // Renderizar notas en el admin
        add_action('admin_footer', array($this, 'render_admin_notes'));
        
        // Inicializar AJAX
        new PostIt_Ajax();
    }
    
    /**
     * Activar el plugin
     */
    public function activate() {
        // Crear tabla de base de datos
        $db = new PostIt_DB();
        $db->create_table();
        
        // Insertar datos de prueba
        $this->insert_sample_data();
        

        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Desactivar el plugin
     */
    public function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Registrar scripts y estilos del admin
     */
    public function enqueue_admin_scripts($hook) {
        // Solo cargar en páginas del admin
        if (!is_admin()) {
            return;
        }
        
        // Registrar y encolar CSS
        wp_register_style(
            'postit-wp-admin',
            POSTIT_WP_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            POSTIT_WP_VERSION
        );
        wp_enqueue_style('postit-wp-admin');
        
        // Registrar y encolar JS
        wp_register_script(
            'postit-wp-admin',
            POSTIT_WP_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            POSTIT_WP_VERSION,
            true
        );
        wp_enqueue_script('postit-wp-admin');
        
        // Localizar script con variables AJAX
        wp_localize_script('postit-wp-admin', 'postitWpAjax', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('postit_wp_nonce'),
            'isAdmin' => current_user_can('manage_options')
        ));
    }
    
    /**
     * Agregar menú de administración
     */
    public function add_admin_menu() {
        add_menu_page(
            __('Notas Globales', 'postit-wp'),
            __('Notas Globales', 'postit-wp'),
            'manage_options',
            'postit-notes',
            array($this, 'admin_page'),
            'dashicons-sticky',
            30
        );
        

    }
    
    /**
     * Página de administración
     */
    public function admin_page() {
        require_once POSTIT_WP_PLUGIN_DIR . 'admin/postit-list.php';
        PostIt_List_Page::render();
    }
    
    /**
     * Renderizar notas en el admin
     */
    public function render_admin_notes() {
        $renderer = new PostIt_Render();
        $renderer->render_admin_notes();
    }
    
    /**
     * Insertar datos de prueba
     */
    private function insert_sample_data() {
        $db = new PostIt_DB();
        
        // Verificar si ya existen datos de prueba
        $existing_notes = $db->get_all_notes();
        if (!empty($existing_notes)) {
            return; // Ya hay datos, no insertar de nuevo
        }
        
        // Datos de prueba
        $sample_notes = array(
            array(
                'note_text' => '¡Bienvenido al plugin PostIt WP! Esta es una nota de prueba que aparece en todas las páginas del admin.',
                'page_context' => '/wp-admin/',
                'user_id' => 1
            ),
            array(
                'note_text' => 'Recuerda revisar las configuraciones de seguridad del sitio.',
                'page_context' => '/wp-admin/options-general.php',
                'user_id' => 1
            ),
            array(
                'note_text' => 'Verifica que todos los plugins estén actualizados regularmente.',
                'page_context' => '/wp-admin/plugins.php',
                'user_id' => 1
            ),
            array(
                'note_text' => 'Esta nota aparece solo en la página de usuarios.',
                'page_context' => '/wp-admin/users.php',
                'user_id' => 1
            ),
            array(
                'note_text' => 'Revisa el contenido antes de publicar.',
                'page_context' => '/wp-admin/post-new.php',
                'user_id' => 1
            ),

        );
        
        // Insertar notas de prueba
        foreach ($sample_notes as $note_data) {
            $db->insert_note($note_data);
        }
    }
}

// Inicializar el plugin
new PostIt_WP(); 