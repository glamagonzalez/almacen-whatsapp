<?php
/**
 * MARCAR PEDIDO COMO ENVIADO
 * Endpoint para actualizar estado a "enviado" y notificar al cliente
 */

require_once '../config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);

$pedido_id = $data['pedido_id'] ?? null;
$tracking = $data['tracking'] ?? '';

if (!$pedido_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'ID de pedido requerido']);
    exit;
}

try {
    $conn = getDBConnection();
    
    // Obtener datos del pedido
    $stmt = $conn->prepare("
        SELECT id, cliente_nombre, cliente_telefono, cliente_email, total 
        FROM pedidos 
        WHERE id = ? AND estado = 'pagado'
    ");
    $stmt->execute([$pedido_id]);
    $pedido = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$pedido) {
        throw new Exception('Pedido no encontrado o no está pagado');
    }
    
    // Actualizar a enviado
    $stmtUpdate = $conn->prepare("
        UPDATE pedidos 
        SET estado = 'enviado',
            fecha_envio = NOW(),
            notas = CONCAT(COALESCE(notas, ''), '\nTracking: ', ?)
        WHERE id = ?
    ");
    $stmtUpdate->execute([$tracking, $pedido_id]);
    
    // Log
    $stmtLog = $conn->prepare("
        INSERT INTO log_actividades (pedido_id, accion, descripcion) 
        VALUES (?, 'pedido_enviado', ?)
    ");
    $stmtLog->execute([
        $pedido_id,
        'Pedido marcado como enviado' . ($tracking ? ". Tracking: $tracking" : '')
    ]);
    
    // 🔔 NOTIFICAR A N8N - PEDIDO ENVIADO
    try {
        $n8n_data = [
            'pedido_id' => $pedido['id'],
            'cliente_nombre' => $pedido['cliente_nombre'],
            'cliente_telefono' => $pedido['cliente_telefono'],
            'total' => $pedido['total'],
            'tracking' => $tracking,
            'fecha_envio' => date('Y-m-d H:i:s')
        ];
        
        $ch = curl_init('http://localhost:5678/webhook/pedido-enviado');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($n8n_data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_exec($ch);
        curl_close($ch);
    } catch (Exception $e) {
        error_log("Error al notificar n8n: " . $e->getMessage());
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Pedido marcado como enviado y cliente notificado'
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
