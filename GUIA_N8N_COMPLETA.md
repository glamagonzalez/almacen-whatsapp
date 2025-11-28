# 🚀 GUÍA COMPLETA: CONECTAR N8N + WHATSAPP

## 📋 PASO A PASO COMPLETO

### PASO 1: Instalar n8n (3 opciones)

#### Opción A: n8n Desktop (MÁS FÁCIL) ⭐
1. Descarga: https://n8n.io/download/
2. Instala el ejecutable
3. Abre n8n Desktop
4. Ya está corriendo en `http://localhost:5678`

#### Opción B: Docker (RECOMENDADO para producción)
```bash
docker run -it --rm \
  --name n8n \
  -p 5678:5678 \
  -v ~/.n8n:/home/node/.n8n \
  n8nio/n8n
```

#### Opción C: npm (Si tenés Node.js)
```bash
npm install n8n -g
n8n start
```

---

### PASO 2: Instalar Evolution API (WhatsApp)

#### Con Docker (FÁCIL):
```bash
# Crear archivo docker-compose.yml
version: '3'
services:
  evolution:
    image: atendai/evolution-api:latest
    ports:
      - "8080:8080"
    environment:
      - AUTHENTICATION_API_KEY=mi_clave_secreta_123
    volumes:
      - evolution_data:/evolution/instances

volumes:
  evolution_data:
```

```bash
# Iniciar Evolution API
docker-compose up -d
```

#### Sin Docker (Manual):
```bash
git clone https://github.com/EvolutionAPI/evolution-api.git
cd evolution-api
npm install
npm run build
npm start
```

---

### PASO 3: Conectar WhatsApp

1. **Abre Evolution API:**
   ```
   http://localhost:8080
   ```

2. **Crea una instancia:**
   - Click en "Create Instance"
   - Nombre: `almacen-whatsapp`
   - API Key: `mi_clave_secreta_123`

3. **Escanea el QR:**
   - Se muestra un QR code
   - Abre WhatsApp en tu celular
   - Ve a: Configuración → Dispositivos vinculados
   - Escanea el QR
   - ✅ WhatsApp conectado!

4. **Guarda el API Key** para usarlo en tu sistema

---

### PASO 4: Importar Workflow en n8n

1. **Abre n8n:**
   ```
   http://localhost:5678
   ```

2. **Importar workflow:**
   - Click en "Import Workflow"
   - Selecciona el archivo: `n8n_workflow.json`
   - Click en "Import"

3. **Configurar credenciales:**
   - Click en cada nodo "HTTP Request"
   - En "Headers" agregar:
     ```
     apikey: mi_clave_secreta_123
     ```

4. **Activar workflow:**
   - Toggle en "Active" (arriba a la derecha)
   - ✅ Workflow activo!

---

### PASO 5: Configurar tu Sistema PHP

#### 1. Editar `config/n8n.php`:
```php
// Cambiar estas líneas:
define('N8N_URL', 'http://localhost:5678');
define('EVOLUTION_API_URL', 'http://localhost:8080');
define('EVOLUTION_API_KEY', 'mi_clave_secreta_123'); // TU API KEY
```

#### 2. Editar `api/webhook_mp.php`:
Ya está listo, solo asegúrate que `config/mercadopago.php` tenga tu ACCESS_TOKEN

#### 3. Crear carpeta de logs:
```bash
mkdir c:\xampp\htdocs\almacen-whatsapp-1\logs
```

---

### PASO 6: Probar la Integración

#### Test 1: Nuevo Pedido
```php
// Crear archivo: test_n8n.php
<?php
require_once 'helpers/n8n_helper.php';

// Simular nuevo pedido
notificarNuevoPedido(1); // Pedido ID 1

echo "✅ Notificación enviada! Revisa WhatsApp";
?>
```

```bash
# Ejecutar:
php test_n8n.php
```

**Deberías recibir un WhatsApp con el pedido!** 📱

#### Test 2: Pago Confirmado
```php
<?php
require_once 'helpers/n8n_helper.php';

notificarPagoConfirmado(1, ['id' => 'MP123456']);

echo "✅ Confirmación enviada!";
?>
```

#### Test 3: WhatsApp Directo
```php
<?php
require_once 'config/n8n.php';

$resultado = enviarWhatsApp('5491112345678', '¡Hola! Mensaje de prueba 🚀');

if ($resultado['success']) {
    echo "✅ WhatsApp enviado!";
} else {
    echo "❌ Error: " . $resultado['error'];
}
?>
```

---

### PASO 7: Configurar Webhook de Mercado Pago

1. **Ve a tu panel de Mercado Pago:**
   https://www.mercadopago.com.ar/developers/panel/app

2. **Configurar Webhook:**
   - Sección: "Webhooks"
   - URL: `https://tu-dominio.com/api/webhook_mp.php`
   - Eventos: `payment`

3. **En localhost (para probar):**
   Usa ngrok para exponer tu localhost:
   ```bash
   ngrok http 80
   ```
   Te da una URL tipo: `https://abc123.ngrok.io`
   Webhook: `https://abc123.ngrok.io/almacen-whatsapp-1/api/webhook_mp.php`

---

## 🎯 FLUJO COMPLETO AUTOMATIZADO

```
1. Cliente hace pedido en tu catálogo
   ↓
2. Se crea pedido en BD
   ↓
3. Se envía a n8n (webhook nuevo-pedido)
   ↓
4. n8n envía WhatsApp al cliente: "Pedido recibido"
   ↓
5. Cliente paga con Mercado Pago
   ↓
6. Mercado Pago llama a webhook_mp.php
   ↓
7. webhook_mp.php envía a n8n (webhook pago-confirmado)
   ↓
8. n8n envía WhatsApp: "Pago confirmado ✅"
   ↓
9. Se actualiza stock automáticamente
   ↓
10. Si stock bajo, n8n envía alerta al admin
   ↓
11. Admin marca como "enviado" en el sistema
   ↓
12. Se envía a n8n (webhook pedido-enviado)
   ↓
13. n8n envía WhatsApp: "Tu pedido está en camino 🚚"
```

## ✅ TODO AUTOMÁTICO! 🎉

---

## 📱 MENSAJES QUE SE ENVÍAN AUTOMÁTICAMENTE

### 1. Nuevo Pedido (Cliente)
```
🛒 *NUEVO PEDIDO #123*

👤 Cliente: Juan Pérez
📱 Teléfono: +54911123456
📧 Email: juan@email.com
📍 Dirección: Av. Corrientes 1234

📦 *Productos:*
• Coca Cola 2.25L x2 = $390.00
• Aceite Girasol 900ml x1 = $845.00

💰 *TOTAL: $1235.00*

⏳ Estado: Esperando pago Mercado Pago
```

### 2. Pago Confirmado
```
✅ *PAGO CONFIRMADO*

Hola Juan! 🎉

💳 Tu pago de $1235.00 fue aprobado
📝 Pedido #123
🆔 Pago #MP123456

🚚 Preparando tu pedido para envío...
📦 Te avisaremos cuando salga.

¡Gracias por tu compra! 😊
```

### 3. Pedido Enviado
```
🚚 *PEDIDO EN CAMINO*

Hola Juan! 📦

✅ Tu pedido #123 fue despachado

📍 Dirección de entrega:
Av. Corrientes 1234

⏰ Tiempo estimado: 24-48 hs

📱 Cualquier consulta, respondé este mensaje.

¡Gracias por confiar en nosotros! 🙏
```

### 4. Alerta Stock Bajo (Admin)
```
⚠️ *ALERTA DE STOCK BAJO*

📦 Producto: Coca Cola 2.25L
📂 Categoría: Bebidas
📊 Stock actual: 5 unidades
🔴 Stock mínimo: 10 unidades

⚡ Acción requerida: Reabastecer producto
```

---

## 🔧 TROUBLESHOOTING

### ❌ "No se envía WhatsApp"
- Verifica que Evolution API esté corriendo: `http://localhost:8080`
- Verifica que WhatsApp esté conectado (QR escaneado)
- Revisa el API Key en `config/n8n.php`

### ❌ "n8n no recibe webhooks"
- Verifica que n8n esté corriendo: `http://localhost:5678`
- Verifica que el workflow esté ACTIVO (toggle verde)
- Revisa las URLs en `config/n8n.php`

### ❌ "Mercado Pago no llama al webhook"
- En localhost usa ngrok: `ngrok http 80`
- Configura la URL de ngrok en Mercado Pago
- Revisa logs: `logs/mp_webhook.log`

---

## 💡 EXTRAS OPCIONALES

### Agregar más notificaciones:
1. Pedido cancelado
2. Cambio de estado
3. Recordatorio de pago pendiente
4. Encuesta de satisfacción
5. Promociones automáticas

### Integrar con Google Sheets:
- Guardar cada pedido en una planilla
- Dashboard en tiempo real
- Reportes automáticos

### Integrar con Gmail:
- Enviar facturas por email
- Confirmaciones por email
- Alertas al admin

---

## 📞 SOPORTE

¿Problemas? Revisa:
- Logs de n8n: `~/.n8n/logs/`
- Logs de Mercado Pago: `logs/mp_webhook.log`
- Consola de Evolution API: `http://localhost:8080`

---

**¡LISTO! Sistema 100% automatizado con WhatsApp** 🚀
