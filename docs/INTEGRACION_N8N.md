# 🔗 INTEGRACIÓN CON N8N - AUTOMATIZACIÓN WHATSAPP

## 🎯 ¿Qué vamos a automatizar?

1. **Cliente hace pedido** → WhatsApp automático
2. **Pago confirmado** → WhatsApp de confirmación
3. **Producto enviado** → WhatsApp de seguimiento
4. **Stock bajo** → Alerta automática

---

## 📋 REQUISITOS PREVIOS

### 1. Instalar n8n
```bash
# Opción 1: Docker (Recomendado)
docker run -it --rm --name n8n -p 5678:5678 -v ~/.n8n:/home/node/.n8n n8nio/n8n

# Opción 2: npm
npm install n8n -g
n8n start

# Opción 3: XAMPP local
# Descargar n8n Desktop: https://n8n.io/download/
```

### 2. Acceder a n8n
```
http://localhost:5678
```

### 3. Conectar WhatsApp
Opciones:
- **Evolution API** (Recomendado) - Gratis
- **Twilio** - Pago
- **WhatsApp Business API** - Pago

---

## 🚀 PASO 1: CREAR WEBHOOK EN TU SISTEMA

Voy a crear los endpoints para que n8n reciba notificaciones:

### Archivo: `webhooks/pedido_creado.php`
```php
<?php
/**
 * WEBHOOK - Nuevo pedido creado
 * Envía datos a n8n cuando hay un pedido nuevo
 */
require_once '../config/database.php';

// Datos del pedido
$pedidoId = $_POST['pedido_id'] ?? 0;

if ($pedidoId) {
    $stmt = $pdo->prepare("
        SELECT p.*, 
               CONCAT(c.nombre, ' ', c.apellido) as cliente_nombre,
               c.telefono, c.email
        FROM pedidos p
        LEFT JOIN clientes c ON p.cliente_id = c.id
        WHERE p.id = ?
    ");
    $stmt->execute([$pedidoId]);
    $pedido = $stmt->fetch();
    
    if ($pedido) {
        // Enviar a n8n
        $n8nWebhook = 'http://localhost:5678/webhook/nuevo-pedido';
        
        $data = [
            'pedido_id' => $pedido['id'],
            'cliente_nombre' => $pedido['cliente_nombre'],
            'cliente_telefono' => $pedido['telefono'],
            'cliente_email' => $pedido['email'],
            'total' => $pedido['total'],
            'productos' => json_decode($pedido['productos_json'], true),
            'fecha' => $pedido['fecha_creacion'],
            'estado' => $pedido['estado']
        ];
        
        $ch = curl_init($n8nWebhook);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        echo json_encode(['success' => true, 'message' => 'Webhook enviado a n8n']);
    }
}
?>
```

---

## 📱 PASO 2: CONFIGURAR EVOLUTION API (WhatsApp)

### Instalar Evolution API con Docker
```bash
# Crear docker-compose.yml
version: '3'
services:
  evolution:
    image: atendai/evolution-api:latest
    ports:
      - "8080:8080"
    environment:
      - AUTHENTICATION_API_KEY=tu_clave_secreta_123
      - DATABASE_ENABLED=true
    volumes:
      - evolution_data:/evolution/instances

volumes:
  evolution_data:
```

### Iniciar Evolution API
```bash
docker-compose up -d
```

### Conectar WhatsApp
1. Abre: `http://localhost:8080`
2. Escanea el QR con tu WhatsApp
3. Copia el `API Key` que te da

---

## 🔧 PASO 3: CREAR FLUJO EN N8N

### Workflow: "Sistema de Pedidos WhatsApp"

#### Nodo 1: Webhook Trigger
```json
{
  "name": "Webhook - Nuevo Pedido",
  "type": "n8n-nodes-base.webhook",
  "position": [250, 300],
  "parameters": {
    "httpMethod": "POST",
    "path": "nuevo-pedido",
    "responseMode": "responseNode"
  }
}
```

#### Nodo 2: Formatear Mensaje
```json
{
  "name": "Formatear Mensaje WhatsApp",
  "type": "n8n-nodes-base.function",
  "position": [450, 300],
  "parameters": {
    "functionCode": "const pedido = $input.item.json;\n\nconst mensaje = `🛒 *NUEVO PEDIDO #${pedido.pedido_id}*\\n\\n👤 Cliente: ${pedido.cliente_nombre}\\n📱 Teléfono: ${pedido.cliente_telefono}\\n\\n📦 *Productos:*\\n${pedido.productos.map(p => `- ${p.nombre} x${p.cantidad} = $${p.subtotal}`).join('\\n')}\\n\\n💰 *TOTAL: $${pedido.total}*\\n\\n✅ Pago: Pendiente Mercado Pago\\n🚚 Estado: ${pedido.estado}`;\n\nreturn {\n  json: {\n    telefono: pedido.cliente_telefono,\n    mensaje: mensaje\n  }\n};"
  }
}
```

#### Nodo 3: Enviar WhatsApp
```json
{
  "name": "Enviar WhatsApp",
  "type": "n8n-nodes-base.httpRequest",
  "position": [650, 300],
  "parameters": {
    "method": "POST",
    "url": "http://localhost:8080/message/sendText",
    "authentication": "genericCredentialType",
    "genericAuthType": "httpHeaderAuth",
    "sendBody": true,
    "bodyParameters": {
      "parameters": [
        {
          "name": "number",
          "value": "={{$json.telefono}}"
        },
        {
          "name": "text",
          "value": "={{$json.mensaje}}"
        }
      ]
    }
  }
}
```

---

## 💻 PASO 4: MODIFICAR TU SISTEMA PHP

Te creo los archivos para conectar con n8n:

