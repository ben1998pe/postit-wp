<?php
/**
 * Debug de contextos para PostIt WP
 */

// Incluir WordPress
require_once('../../../wp-config.php');

echo "<h1>Debug de Contextos - PostIt WP</h1>";

// Verificar si las clases están disponibles
if (class_exists('PostIt_DB') && class_exists('PostIt_Render')) {
    echo "<h2>✅ Clases disponibles</h2>";
    
    $db = new PostIt_DB();
    $renderer = new PostIt_Render();
    
    // Obtener todas las notas
    $all_notes = $db->get_all_notes();
    echo "<h3>📝 Todas las notas en la base de datos:</h3>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Contexto</th><th>Texto</th></tr>";
    
    foreach ($all_notes as $note) {
        echo "<tr>";
        echo "<td>{$note['id']}</td>";
        echo "<td><code>{$note['page_context']}</code></td>";
        echo "<td>" . substr($note['note_text'], 0, 50) . "...</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Obtener contexto actual
    $current_context = $renderer->get_current_page_context();
    echo "<h3>🎯 Contexto actual de la página:</h3>";
    echo "<p><strong>Contexto detectado:</strong> <code>{$current_context}</code></p>";
    
    // Verificar si hay notas para este contexto
    $notes_for_context = $db->get_notes_by_context($current_context);
    echo "<h3>🔍 Notas para el contexto actual:</h3>";
    echo "<p><strong>Número de notas encontradas:</strong> " . count($notes_for_context) . "</p>";
    
    if (!empty($notes_for_context)) {
        echo "<ul>";
        foreach ($notes_for_context as $note) {
            echo "<li><strong>ID {$note['id']}:</strong> " . substr($note['note_text'], 0, 100) . "...</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color: red;'>❌ No se encontraron notas para este contexto</p>";
    }
    
    // Mostrar información de la URI
    echo "<h3>🌐 Información de la URI:</h3>";
    echo "<p><strong>REQUEST_URI:</strong> <code>{$_SERVER['REQUEST_URI']}</code></p>";
    echo "<p><strong>PHP_SELF:</strong> <code>{$_SERVER['PHP_SELF']}</code></p>";
    echo "<p><strong>SCRIPT_NAME:</strong> <code>{$_SERVER['SCRIPT_NAME']}</code></p>";
    
    // Probar diferentes métodos de obtener el contexto
    echo "<h3>🧪 Pruebas de diferentes métodos:</h3>";
    
    // Método 1: REQUEST_URI
    $path1 = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    echo "<p><strong>Método 1 (REQUEST_URI):</strong> <code>{$path1}</code></p>";
    
    // Método 2: PHP_SELF
    echo "<p><strong>Método 2 (PHP_SELF):</strong> <code>{$_SERVER['PHP_SELF']}</code></p>";
    
    // Método 3: SCRIPT_NAME
    echo "<p><strong>Método 3 (SCRIPT_NAME):</strong> <code>{$_SERVER['SCRIPT_NAME']}</code></p>";
    
    // Método 4: Combinación
    $path4 = $_SERVER['SCRIPT_NAME'];
    if (isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING'])) {
        $path4 .= '?' . $_SERVER['QUERY_STRING'];
    }
    echo "<p><strong>Método 4 (SCRIPT_NAME + QUERY_STRING):</strong> <code>{$path4}</code></p>";
    
} else {
    echo "<h2>❌ Clases no disponibles</h2>";
    echo "<p>Las clases PostIt_DB o PostIt_Render no están disponibles.</p>";
}

echo "<hr>";
echo "<h2>🎯 Instrucciones:</h2>";
echo "<ol>";
echo "<li>Ve a diferentes páginas del admin (Plugins, Usuarios, Configuración General)</li>";
echo "<li>Ejecuta este archivo de debug en cada página</li>";
echo "<li>Compara el contexto detectado con los contextos guardados en la base de datos</li>";
echo "</ol>"; 