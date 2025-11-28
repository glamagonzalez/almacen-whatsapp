<?php
header('Content-Type: application/json');

$API_URL = 'http://localhost:8080';
$API_KEY = 'mi_clave_secreta_123';
$INSTANCE = 'almacen-whatsapp';

// Determinar acción
$action = $_GET['action'] ?? $_POST['action'] ?? 'status';

function callAPI($url, $method = 'GET', $data = null) {
    global $API_KEY;
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'apikey: ' . $API_KEY,
        'Content-Type: application/json'
    ]);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'code' => $httpCode,
        'data' => json_decode($response, true)
    ];
}

switch ($action) {
    case 'status':
        $result = callAPI("$API_URL/instance/connectionState/$INSTANCE");
        echo json_encode($result);
        break;
        
    case 'create':
        $data = [
            'instanceName' => $INSTANCE,
            'qrcode' => true,
            'integration' => 'WHATSAPP-BAILEYS'
        ];
        $result = callAPI("$API_URL/instance/create", 'POST', $data);
        echo json_encode($result);
        break;
        
    case 'connect':
        $result = callAPI("$API_URL/instance/connect/$INSTANCE");
        echo json_encode($result);
        break;
        
    case 'send':
        $phone = $_POST['phone'] ?? '';
        $message = $_POST['message'] ?? '';
        
        if (empty($phone) || empty($message)) {
            echo json_encode(['code' => 400, 'data' => ['error' => 'Faltan parámetros']]);
            exit;
        }
        
        $data = [
            'number' => $phone,
            'text' => $message
        ];
        $result = callAPI("$API_URL/message/sendText/$INSTANCE", 'POST', $data);
        echo json_encode($result);
        break;
        
    default:
        echo json_encode(['code' => 400, 'data' => ['error' => 'Acción no válida']]);
}
