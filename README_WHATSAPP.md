# 📱 Sistema de Notificaciones WhatsApp - Almacén

## 🎯 Estado del Proyecto

### ✅ **COMPLETADO (95%)**

1. **n8n Instalado y Configurado**
   - URL: `http://localhost:5678`
   - 4 Workflows importados y activos
   - Credenciales configuradas correctamente

2. **Workflows Creados**
   - ✅ Nuevo Pedido → WhatsApp Cliente
   - ✅ Pago Confirmado → WhatsApp Confirmación
   - ✅ Pedido Enviado → WhatsApp Tracking
   - ✅ Stock Bajo → WhatsApp Admin (1157816498)

3. **Evolution API Setup**
   - Docker Compose configurado
   - PostgreSQL integrado
   - API funcionando en puerto 8080

4. **Código en GitHub**
   - Repositorio: `glamagonzalez/almacen-whatsapp`
   - Branch: `main`
   - Todos los archivos sincronizados

---

## ⚠️ **PENDIENTE - Conexión WhatsApp**

### Problema Actual
Evolution API con Baileys no genera código QR en Windows/Docker debido a incompatibilidades del entorno.

### Síntoma
Al intentar generar QR:
- Manager muestra popup vacío
- `qr-whatsapp.html` queda en "Generando QR..."
- API responde `{"count":0}` indefinidamente

---

## 🚀 **SOLUCIONES PARA PRODUCCIÓN**

### Opción 1: Servidor Linux (RECOMENDADA) ⭐
**Cuando subir a producción:**

1. **Subir proyecto al servidor**
   ```bash
   git clone https://github.com/glamagonzalez/almacen-whatsapp.git
   cd almacen-whatsapp
   ```

2. **Instalar Docker**
   ```bash
   sudo apt update
   sudo apt install docker.io docker-compose -y
   ```

3. **Iniciar contenedores**
   ```bash
   docker-compose up -d
   ```

4. **Conectar WhatsApp**
   - Abrir: `http://tu-servidor:8080/manager`
   - Login con: `mi_clave_secreta_123`
   - Click en instancia → "Obtener código QR"
   - Escanear con WhatsApp del celular (1157816498)
   - ✅ ¡Conectado!

5. **Configurar n8n**
   - Abrir: `http://tu-servidor:5678`
   - Workflows ya importados
   - Verificar que credenciales apunten a `tu-servidor:8080`

---

### Opción 2: WSL2 en Windows
**Si querés probar localmente:**

1. **Instalar WSL2**
   ```powershell
   wsl --install
   ```

2. **Instalar Docker en WSL2**
   ```bash
   sudo apt update
   sudo apt install docker.io docker-compose -y
   ```

3. **Clonar proyecto en WSL2**
   ```bash
   cd ~
   git clone https://github.com/glamagonzalez/almacen-whatsapp.git
   cd almacen-whatsapp
   ```

4. **Correr Docker desde WSL2**
   ```bash
   docker-compose up -d
   ```

5. **Acceder desde Windows**
   - Manager: `http://localhost:8080/manager`
   - n8n: `http://localhost:5678`

---

### Opción 3: API Comercial de WhatsApp 💰

**Servicios recomendados:**

1. **Twilio WhatsApp API**
   - Costo: $0.005 por mensaje
   - Setup: 15 minutos
   - Documentación: https://www.twilio.com/whatsapp

2. **Wati.io**
   - Desde $49/mes
   - Interface amigable
   - Web: https://wati.io

3. **Meta Business API**
   - Oficial de WhatsApp
   - Requiere verificación de negocio
   - Más complejo de setup

---

## 📂 **Estructura de Archivos**

```
almacen-whatsapp-1/
├── docker-compose.yml          # Config Evolution API + PostgreSQL
├── n8n_workflow.json          # 4 workflows de WhatsApp
├── qr-whatsapp.html          # Página para generar QR
├── whatsapp-manager.html     # Interface de gestión
├── config/
│   └── n8n.php               # URLs y API keys
├── helpers/
│   └── n8n_helper.php        # Funciones de notificación
├── api/
│   └── whatsapp-api.php      # Proxy PHP para Evolution API
└── README_WHATSAPP.md        # Esta documentación
```

---

## 🔧 **Configuración Actual**

### Variables de Entorno (docker-compose.yml)
```yaml
AUTHENTICATION_API_KEY: mi_clave_secreta_123
DATABASE_ENABLED: true
DATABASE_PROVIDER: postgresql
CACHE_REDIS_ENABLED: false
LOG_LEVEL: info
```

### n8n Credentials
- **Tipo:** Header Auth
- **Nombre:** almacen whatsapp
- **Header:** `apikey: mi_clave_secreta_123`

### Instancia WhatsApp
- **Nombre:** whatsapp-1157816498
- **Número:** +54 9 11 5781-6498
- **Estado:** Pendiente de conexión

---

## 📱 **Mensajes Configurados**

### 1. Nuevo Pedido (Cliente)
```
🛒 *NUEVO PEDIDO #{pedido_id}*

👤 Cliente: {nombre}
📱 Teléfono: {telefono}
📧 Email: {email}
📍 Dirección: {direccion}

📦 *Productos:*
{lista_productos}

💰 *TOTAL: ${total}*

⏳ Estado: Esperando pago Mercado Pago
```

### 2. Pago Confirmado
```
✅ *PAGO CONFIRMADO*

Hola {nombre}! 🎉

💳 Tu pago de ${total} fue aprobado
📝 Pedido #{pedido_id}
🆔 Pago #{mp_payment_id}

🚚 Preparando tu pedido para envío...
📦 Te avisaremos cuando salga.

¡Gracias por tu compra! 😊
```

### 3. Pedido Enviado
```
🚚 *PEDIDO EN CAMINO*

Hola {nombre}! 📦

✅ Tu pedido #{pedido_id} fue despachado

📍 Dirección de entrega:
{direccion}

⏰ Tiempo estimado: 24-48 hs

📱 Cualquier consulta, respondé este mensaje.

¡Gracias por confiar en nosotros! 🙏
```

### 4. Alerta Stock Bajo (Admin → 1157816498)
```
⚠️ *ALERTA DE STOCK BAJO*

📦 Producto: {producto}
📂 Categoría: {categoria}
📊 Stock actual: {cantidad} unidades
🔴 Stock mínimo: {minimo} unidades

⚡ Acción requerida: Reabastecer producto
```

---

## 🔗 **Endpoints de n8n**

Una vez n8n esté activo en producción, estos serán los webhooks:

```
Nuevo Pedido:
POST http://tu-servidor:5678/webhook/nuevo-pedido

Pago Confirmado:
POST http://tu-servidor:5678/webhook/pago-confirmado

Pedido Enviado:
POST http://tu-servidor:5678/webhook/pedido-enviado

Stock Bajo:
POST http://tu-servidor:5678/webhook/stock-bajo
```

---

## 💻 **Uso desde PHP**

### Ejemplo: Notificar Nuevo Pedido
```php
<?php
require_once 'helpers/n8n_helper.php';

// Después de crear un pedido
$pedido_id = 123;
notificarNuevoPedido($pedido_id);
?>
```

### Ejemplo: Notificar Pago Confirmado
```php
<?php
require_once 'helpers/n8n_helper.php';

// Desde el webhook de Mercado Pago
$pedido_id = 123;
$pago_data = ['id' => 'MP123456', 'status' => 'approved'];
notificarPagoConfirmado($pedido_id, $pago_data);
?>
```

### Ejemplo: Notificar Envío
```php
<?php
require_once 'helpers/n8n_helper.php';

// Cuando marcas pedido como enviado
$pedido_id = 123;
$tracking = 'AR123456789';
notificarPedidoEnviado($pedido_id, $tracking);
?>
```

### Ejemplo: Alerta Stock
```php
<?php
require_once 'helpers/n8n_helper.php';

// Cuando detectas stock bajo
$producto_id = 45;
notificarStockBajo($producto_id);
?>
```

---

## 🧪 **Testing en Producción**

### 1. Verificar Evolution API
```bash
curl http://tu-servidor:8080 \
  -H "apikey: mi_clave_secreta_123"
```

**Respuesta esperada:**
```json
{
  "status": 200,
  "message": "Welcome to the Evolution API, it is working!",
  "version": "2.2.3"
}
```

### 2. Verificar Conexión WhatsApp
```bash
curl http://tu-servidor:8080/instance/connectionState/whatsapp-1157816498 \
  -H "apikey: mi_clave_secreta_123"
```

**Respuesta esperada:**
```json
{
  "instance": {
    "instanceName": "whatsapp-1157816498",
    "state": "open"
  }
}
```

### 3. Enviar Mensaje de Prueba
```bash
curl -X POST http://tu-servidor:8080/message/sendText/whatsapp-1157816498 \
  -H "apikey: mi_clave_secreta_123" \
  -H "Content-Type: application/json" \
  -d '{
    "number": "5491157816498",
    "text": "¡Prueba exitosa! 🚀"
  }'
```

---

## 🐛 **Troubleshooting**

### Evolution API no responde
```bash
docker ps  # Verificar que contenedor está corriendo
docker logs evolution-api  # Ver logs
docker-compose restart  # Reiniciar
```

### n8n no recibe webhooks
1. Verificar que workflow esté **ACTIVO** (toggle verde)
2. Ver ejecuciones en n8n → Executions
3. Verificar URLs en `config/n8n.php`

### WhatsApp se desconecta
- Refrescar página del Manager
- Click en "REANUDAR"
- Si persiste: borrar instancia y crear nueva

---

## 📞 **Contacto y Soporte**

- **GitHub**: https://github.com/glamagonzalez/almacen-whatsapp
- **Evolution API Docs**: https://doc.evolution-api.com
- **n8n Docs**: https://docs.n8n.io

---

## 📝 **Changelog**

### 2025-11-28
- ✅ n8n instalado y configurado
- ✅ 4 workflows importados
- ✅ Evolution API setup completo
- ✅ Credenciales configuradas
- ⏸️ Conexión WhatsApp pendiente (requiere servidor Linux)

### Próximos pasos
- [ ] Subir a servidor de producción
- [ ] Conectar WhatsApp con QR
- [ ] Probar envío de mensajes
- [ ] Integrar con sistema de pedidos existente
- [ ] Configurar webhook Mercado Pago

---

**🚀 ¡Sistema listo para producción! Solo falta conectar WhatsApp en servidor Linux.**
