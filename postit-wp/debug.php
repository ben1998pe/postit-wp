<?php
/**
 * Archivo de debug temporal para PostIt WP
 * 
 * Este archivo se puede ejecutar directamente para verificar el estado del plugin
 */

// Incluir WordPress
require_once('../../../wp-config.php');

// Verificar si las clases están disponibles
if (class_exists('PostIt_DB')) {
    echo "✅ Clase PostIt_DB está disponible\n";
    
    $db = new PostIt_DB();
    
    // Verificar si la tabla existe
    global $wpdb;
    $table_name = $wpdb->prefix . 'postit_notes';
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") === $table_name;
    
    if ($table_exists) {
        echo "✅ Tabla {$table_name} existe\n";
        
        // Contar notas
        $notes = $db->get_all_notes();
        echo "📝 Total de notas en la base de datos: " . count($notes) . "\n";
        
        if (!empty($notes)) {
            echo "📋 Primeras 3 notas:\n";
            foreach (array_slice($notes, 0, 3) as $note) {
                echo "  - ID: {$note['id']}, Contexto: {$note['page_context']}, Texto: " . substr($note['note_text'], 0, 50) . "...\n";
            }
        }
    } else {
        echo "❌ Tabla {$table_name} NO existe\n";
    }
} else {
    echo "❌ Clase PostIt_DB NO está disponible\n";
}

if (class_exists('PostIt_Render')) {
    echo "✅ Clase PostIt_Render está disponible\n";
} else {
    echo "❌ Clase PostIt_Render NO está disponible\n";
}

// Verificar constantes
if (defined('POSTIT_WP_VERSION')) {
    echo "✅ Constante POSTIT_WP_VERSION: " . POSTIT_WP_VERSION . "\n";
} else {
    echo "❌ Constante POSTIT_WP_VERSION NO está definida\n";
}

if (defined('POSTIT_WP_PLUGIN_DIR')) {
    echo "✅ Constante POSTIT_WP_PLUGIN_DIR: " . POSTIT_WP_PLUGIN_DIR . "\n";
} else {
    echo "❌ Constante POSTIT_WP_PLUGIN_DIR NO está definida\n";
}

// Verificar permisos del usuario actual
$current_user = wp_get_current_user();
if ($current_user->ID) {
    echo "👤 Usuario actual: {$current_user->display_name} (ID: {$current_user->ID})\n";
    echo "🔐 Puede gestionar opciones: " . (current_user_can('manage_options') ? 'Sí' : 'No') . "\n";
} else {
    echo "❌ No hay usuario logueado\n";
}

// Verificar hooks registrados
global $wp_filter;
if (isset($wp_filter['admin_menu'])) {
    echo "✅ Hook 'admin_menu' está registrado\n";
    echo "📋 Callbacks registrados en admin_menu: " . count($wp_filter['admin_menu']->callbacks) . "\n";
} else {
    echo "❌ Hook 'admin_menu' NO está registrado\n";
}

echo "\n🎯 Para verificar el plugin:\n";
echo "1. Desactiva el plugin desde WordPress Admin > Plugins\n";
echo "2. Reactiva el plugin\n";
echo "3. Ve a cualquier página del admin y busca notas en la esquina inferior derecha\n";
echo "4. Busca el menú 'Notas Globales' en el sidebar izquierdo\n"; 