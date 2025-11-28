<?php
/**
 * TEST N8N - Probar integración con n8n
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🧪 Test de Integración n8n + WhatsApp</h1>";
echo "<hr>";

// Test 1: Verificar archivos
echo "<h2>Test 1: Archivos de configuración</h2>";

if (file_exists('config/n8n.php')) {
    echo "✅ config/n8n.php existe<br>";
    require_once 'config/n8n.php';
    echo "✅ Archivo cargado correctamente<br>";
    echo "📍 n8n URL: " . N8N_URL . "<br>";
    echo "📍 Evolution API URL: " . EVOLUTION_API_URL . "<br>";
} else {
    echo "❌ config/n8n.php NO existe<br>";
}

if (file_exists('helpers/n8n_helper.php')) {
    echo "✅ helpers/n8n_helper.php existe<br>";
} else {
    echo "❌ helpers/n8n_helper.php NO existe<br>";
}

echo "<hr>";

// Test 2: Verificar conectividad con n8n
echo "<h2>Test 2: Conectividad con n8n</h2>";

$n8nUrl = 'http://localhost:5678';
$ch = curl_init($n8nUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 3);
curl_setopt($ch, CURLOPT_NOBODY, true);

$result = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode > 0) {
    echo "✅ n8n está corriendo en $n8nUrl<br>";
    echo "📊 HTTP Code: $httpCode<br>";
} else {
    echo "❌ n8n NO está corriendo en $n8nUrl<br>";
    echo "💡 Solución:<br>";
    echo "1. Descarga n8n Desktop desde: https://n8n.io/download/<br>";
    echo "2. O ejecuta: <code>npx n8n</code><br>";
}

echo "<hr>";

// Test 3: Verificar Evolution API (WhatsApp)
echo "<h2>Test 3: Evolution API (WhatsApp)</h2>";

$evolutionUrl = 'http://localhost:8080';
$ch = curl_init($evolutionUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 3);
curl_setopt($ch, CURLOPT_NOBODY, true);

$result = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode > 0) {
    echo "✅ Evolution API está corriendo en $evolutionUrl<br>";
    echo "📊 HTTP Code: $httpCode<br>";
} else {
    echo "⚠️ Evolution API NO está corriendo en $evolutionUrl<br>";
    echo "💡 Solución:<br>";
    echo "1. Instala Docker Desktop<br>";
    echo "2. Ejecuta: <code>docker run -p 8080:8080 atendai/evolution-api:latest</code><br>";
    echo "3. O sigue la guía: GUIA_N8N_COMPLETA.md<br>";
}

echo "<hr>";

// Test 4: Test de webhook (simulado)
echo "<h2>Test 4: Enviar test a n8n (simulado)</h2>";

if (function_exists('enviarEventoN8n')) {
    $testData = [
        'test' => true,
        'mensaje' => 'Hola desde PHP',
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    $webhook = N8N_URL . '/webhook-test/test';
    
    echo "📤 Enviando test a: $webhook<br>";
    echo "📦 Datos: <pre>" . json_encode($testData, JSON_PRETTY_PRINT) . "</pre>";
    
    $resultado = enviarEventoN8n($webhook, $testData);
    
    if ($resultado['success']) {
        echo "✅ Webhook enviado correctamente<br>";
        echo "📊 HTTP Code: " . $resultado['http_code'] . "<br>";
    } else {
        echo "❌ Error al enviar webhook<br>";
        if (isset($resultado['error'])) {
            echo "🐛 Error: " . $resultado['error'] . "<br>";
        }
    }
} else {
    echo "⚠️ Función enviarEventoN8n() no disponible<br>";
}

echo "<hr>";

// Test 5: Verificar base de datos
echo "<h2>Test 5: Base de datos</h2>";

if (file_exists('config/database.php')) {
    require_once 'config/database.php';
    
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM productos WHERE activo = 1");
        $result = $stmt->fetch();
        
        echo "✅ Conexión a base de datos OK<br>";
        echo "📊 Productos activos: " . $result['total'] . "<br>";
        
        // Ver si hay pedidos
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM pedidos");
        $result = $stmt->fetch();
        echo "📊 Pedidos en sistema: " . $result['total'] . "<br>";
        
    } catch (Exception $e) {
        echo "❌ Error en base de datos: " . $e->getMessage() . "<br>";
    }
} else {
    echo "❌ config/database.php NO existe<br>";
}

echo "<hr>";

// Resumen
echo "<h2>📋 Resumen</h2>";
echo "<ul>";
echo "<li><strong>Paso 1:</strong> Instala n8n Desktop → <a href='https://n8n.io/download/' target='_blank'>Descargar</a></li>";
echo "<li><strong>Paso 2:</strong> Instala Evolution API (WhatsApp) → Ver GUIA_N8N_COMPLETA.md</li>";
echo "<li><strong>Paso 3:</strong> Importa n8n_workflow.json en n8n</li>";
echo "<li><strong>Paso 4:</strong> Configura API Keys en config/n8n.php</li>";
echo "<li><strong>Paso 5:</strong> Prueba envío de WhatsApp</li>";
echo "</ul>";

echo "<hr>";
echo "<p><strong>📖 Documentación completa:</strong> <a href='GUIA_N8N_COMPLETA.md'>GUIA_N8N_COMPLETA.md</a></p>";
?>
