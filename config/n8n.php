<?php
/**
 * CONFIG N8N - Configuración de webhooks
 */

// URL base de n8n
define('N8N_URL', 'http://localhost:5678');

// Webhooks disponibles
define('N8N_WEBHOOK_NUEVO_PEDIDO', N8N_URL . '/webhook/nuevo-pedido');
define('N8N_WEBHOOK_PAGO_CONFIRMADO', N8N_URL . '/webhook/pago-confirmado');
define('N8N_WEBHOOK_PEDIDO_ENVIADO', N8N_URL . '/webhook/pedido-enviado');
define('N8N_WEBHOOK_STOCK_BAJO', N8N_URL . '/webhook/stock-bajo');

// Evolution API (WhatsApp)
define('EVOLUTION_API_URL', 'http://localhost:8080');
define('EVOLUTION_API_KEY', 'tu_clave_secreta_123'); // Cambiar por tu API Key

/**
 * Enviar evento a n8n
 */
function enviarEventoN8n($webhook, $data) {
    try {
        $ch = curl_init($webhook);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return [
            'success' => ($httpCode >= 200 && $httpCode < 300),
            'response' => $response,
            'http_code' => $httpCode
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

/**
 * Enviar mensaje directo por WhatsApp (sin n8n)
 */
function enviarWhatsApp($telefono, $mensaje) {
    try {
        // Limpiar número de teléfono (solo dígitos)
        $telefono = preg_replace('/[^0-9]/', '', $telefono);
        
        // Agregar código de país si no lo tiene
        if (strlen($telefono) == 10) {
            $telefono = '549' . $telefono; // Argentina
        }
        
        $url = EVOLUTION_API_URL . '/message/sendText';
        
        $data = [
            'number' => $telefono,
            'text' => $mensaje
        ];
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'apikey: ' . EVOLUTION_API_KEY
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return [
            'success' => ($httpCode >= 200 && $httpCode < 300),
            'response' => json_decode($response, true)
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}
?>
