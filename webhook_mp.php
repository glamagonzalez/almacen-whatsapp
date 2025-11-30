<?php
/**
 * WEBHOOK MERCADO PAGO
 * Recibe notificaciones automáticas de Mercado Pago cuando hay un pago
 */

require_once 'config/config.php';
require_once 'vendor/autoload.php';

// Log para debugging
file_put_contents('logs/webhook-mp.log', date('Y-m-d H:i:s') . " - Webhook recibido\n", FILE_APPEND);

// Obtener datos del POST
$input = file_get_contents('php://input');
file_put_contents('logs/webhook-mp.log', "POST: " . $input . "\n", FILE_APPEND);

$data = json_decode($input, true);

// Verificar que sea una notificación de pago
if (!isset($data['type']) || $data['type'] !== 'payment') {
    file_put_contents('logs/webhook-mp.log', "No es notificación de pago\n", FILE_APPEND);
    http_response_code(200);
    exit;
}

try {
    // Obtener el payment_id
    $payment_id = $data['data']['id'] ?? null;
    
    if (!$payment_id) {
        throw new Exception('No hay payment_id en la notificación');
    }
    
    file_put_contents('logs/webhook-mp.log', "Payment ID: $payment_id\n", FILE_APPEND);
    
    // Configurar SDK de Mercado Pago
    MercadoPago\MercadoPagoConfig::setAccessToken(MERCADOPAGO_ACCESS_TOKEN);
    $client = new MercadoPago\Client\Payment\PaymentClient();
    
    // Obtener información del pago desde Mercado Pago
    $payment = $client->get($payment_id);
    
    file_put_contents('logs/webhook-mp.log', "Payment status: " . $payment->status . "\n", FILE_APPEND);
    file_put_contents('logs/webhook-mp.log', "External reference: " . $payment->external_reference . "\n", FILE_APPEND);
    
    // Solo procesar si el pago fue aprobado
    if ($payment->status === 'approved') {
        
        $conn = getDBConnection();
        
        // Buscar pedido por external_reference o preference_id
        $external_ref = $payment->external_reference;
        
        $stmt = $conn->prepare("
            SELECT id, cliente_nombre, cliente_telefono, cliente_email, total, estado 
            FROM pedidos 
            WHERE mp_external_reference = ? OR mp_preference_id = ?
            LIMIT 1
        ");
        $stmt->execute([$external_ref, $payment->preference_id]);
        $pedido = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($pedido) {
            file_put_contents('logs/webhook-mp.log', "Pedido encontrado: ID " . $pedido['id'] . "\n", FILE_APPEND);
            
            // Actualizar pedido a PAGADO
            $stmtUpdate = $conn->prepare("
                UPDATE pedidos 
                SET mp_payment_id = ?,
                    mp_status = 'approved',
                    estado = 'pagado',
                    fecha_pago = NOW()
                WHERE id = ?
            ");
            $stmtUpdate->execute([$payment_id, $pedido['id']]);
            
            file_put_contents('logs/webhook-mp.log', "Pedido actualizado a PAGADO\n", FILE_APPEND);
            
            // Log en la tabla de actividades
            $stmtLog = $conn->prepare("
                INSERT INTO log_actividades (pedido_id, accion, descripcion) 
                VALUES (?, 'pago_confirmado', ?)
            ");
            $stmtLog->execute([
                $pedido['id'],
                "Pago aprobado por Mercado Pago. Payment ID: $payment_id"
            ]);
            
            // 🔔 NOTIFICAR A N8N - PAGO CONFIRMADO
            try {
                $n8n_webhook = "http://localhost:5678/webhook/pago-confirmado";
                $notificacion_data = [
                    'pedido_id' => $pedido['id'],
                    'cliente_nombre' => $pedido['cliente_nombre'],
                    'cliente_telefono' => $pedido['cliente_telefono'],
                    'cliente_email' => $pedido['cliente_email'],
                    'total' => $pedido['total'],
                    'payment_id' => $payment_id,
                    'fecha_pago' => date('Y-m-d H:i:s')
                ];
                
                $ch = curl_init($n8n_webhook);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($notificacion_data));
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                curl_setopt($ch, CURLOPT_TIMEOUT, 5);
                $response = curl_exec($ch);
                curl_close($ch);
                
                file_put_contents('logs/webhook-mp.log', "Notificación enviada a n8n: $response\n", FILE_APPEND);
            } catch (Exception $e) {
                file_put_contents('logs/webhook-mp.log', "Error al notificar n8n: " . $e->getMessage() . "\n", FILE_APPEND);
            }
            
        } else {
            file_put_contents('logs/webhook-mp.log', "ERROR: Pedido no encontrado\n", FILE_APPEND);
        }
    }
    
    // Responder OK a Mercado Pago
    http_response_code(200);
    echo json_encode(['status' => 'ok']);
    
} catch (Exception $e) {
    file_put_contents('logs/webhook-mp.log', "ERROR: " . $e->getMessage() . "\n", FILE_APPEND);
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
