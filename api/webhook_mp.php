<?php
/**
 * WEBHOOK - Mercado Pago IPN
 * Recibe notificaciones de pago y dispara n8n
 */
require_once '../config/database.php';
require_once '../config/mercadopago.php';
require_once '../helpers/n8n_helper.php';

// Obtener notificación de Mercado Pago
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Log de la notificación
file_put_contents('../logs/mp_webhook.log', date('Y-m-d H:i:s') . ' - ' . $input . "\n", FILE_APPEND);

if (isset($data['type']) && $data['type'] == 'payment') {
    $paymentId = $data['data']['id'];
    
    // Consultar información del pago en Mercado Pago
    $ch = curl_init("https://api.mercadopago.com/v1/payments/{$paymentId}");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . MP_ACCESS_TOKEN
    ]);
    
    $response = curl_exec($ch);
    $payment = json_decode($response, true);
    curl_close($ch);
    
    if ($payment['status'] == 'approved') {
        // Buscar pedido por preference_id
        $preferenceId = $payment['additional_info']['items'][0]['id'] ?? null;
        
        if ($preferenceId) {
            $stmt = $pdo->prepare("SELECT id FROM pedidos WHERE mp_preference_id = ?");
            $stmt->execute([$preferenceId]);
            $pedido = $stmt->fetch();
            
            if ($pedido) {
                // Actualizar estado del pedido
                $stmt = $pdo->prepare("
                    UPDATE pedidos 
                    SET estado = 'pagado', 
                        mp_payment_id = ?,
                        fecha_pago = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([$paymentId, $pedido['id']]);
                
                // Notificar a n8n
                notificarPagoConfirmado($pedido['id'], $payment);
                
                // Enviar WhatsApp de confirmación
                enviarConfirmacionWhatsApp($pedido['id']);
                
                // Actualizar stock
                $pedidoData = $pdo->prepare("SELECT productos_json FROM pedidos WHERE id = ?");
                $pedidoData->execute([$pedido['id']]);
                $pedidoInfo = $pedidoData->fetch();
                
                $productos = json_decode($pedidoInfo['productos_json'], true);
                foreach ($productos as $prod) {
                    $stmt = $pdo->prepare("
                        UPDATE productos 
                        SET stock_actual = stock_actual - ? 
                        WHERE id = ?
                    ");
                    $stmt->execute([$prod['cantidad'], $prod['id']]);
                    
                    // Verificar si stock está bajo
                    $stmt = $pdo->prepare("
                        SELECT id, stock_actual, stock_minimo 
                        FROM productos 
                        WHERE id = ? AND stock_actual <= stock_minimo
                    ");
                    $stmt->execute([$prod['id']]);
                    if ($stmt->fetch()) {
                        notificarStockBajo($prod['id']);
                    }
                }
            }
        }
    }
}

http_response_code(200);
echo json_encode(['success' => true]);
?>
