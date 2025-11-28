# 💳 GUÍA COMPLETA - SISTEMA DE PAGOS CON MERCADO PAGO

## 🎯 FLUJO COMPLETO DEL PROCESO

```
1. CLIENTE ve productos → catalogo.php
2. Agrega al CARRITO
3. Va a CHECKOUT → checkout.php
4. Completa sus datos
5. Sistema crea pedido en BD
6. Sistema genera link de MERCADO PAGO
7. Cliente es redirigido a MERCADO PAGO
8. Cliente PAGA con tarjeta/débito/crédito
9. Mercado Pago notifica al sistema → webhook_mp.php
10. Sistema actualiza estado del pedido a "PAGADO"
11. VOS recibes notificación y ENVÍAS el producto
12. Marcas como ENVIADO
13. Cliente recibe su pedido
```

---

## 📋 PASOS PARA CONFIGURAR MERCADO PAGO

### **Paso 1: Crear cuenta en Mercado Pago**

1. Ve a: https://www.mercadopago.com.ar/
2. Crea una cuenta (si no tienes)
3. Completa tu perfil de vendedor

### **Paso 2: Obtener credenciales**

1. Ve a: https://www.mercadopago.com.ar/developers/panel/app
2. Click en "Crear aplicación"
3. Completa los datos:
   - **Nombre**: Almacén WhatsApp
   - **Tipo**: Pagos online
4. Una vez creada, ve a **"Credenciales"**
5. Copia:
   - **Access Token** (empie
za con APP_USR-...)
   - **Public Key** (empieza con APP_USR-...)

### **Paso 3: Configurar en tu sistema**

Abre el archivo: `config/mercadopago.php`

```php
// REEMPLAZA ESTOS VALORES:
define('MP_ACCESS_TOKEN', 'APP_USR-1234567890-XXXXXXXXXXX');
define('MP_PUBLIC_KEY', 'APP_USR-XXXXXXXXXXX-XXXXXXXXXXX');
```

---

## 🧪 MODO DE PRUEBA (TESTING)

### **Credenciales de prueba**

1. En el panel de Mercado Pago, ve a "Credenciales de prueba"
2. Copia las credenciales de TEST
3. Úsalas en `config/mercadopago.php`
4. Configura: `define('MP_MODO_PRUEBA', true);`

### **Tarjetas de prueba**

Para probar pagos sin dinero real:

```
✅ APROBADO:
Tarjeta: 5031 7557 3453 0604
CVV: 123
Vencimiento: 11/25
Nombre: APRO

❌ RECHAZADO:
Tarjeta: 5031 4332 1540 6351
CVV: 123
Vencimiento: 11/25
Nombre: OTHE
```

Más tarjetas de prueba: https://www.mercadopago.com.ar/developers/es/docs/sdks-library/server-side/php/integration-test/test-cards

---

## 💰 CÓMO FUNCIONA EL COBRO

### **Comisiones de Mercado Pago**

- Mercado Pago cobra una comisión por cada venta
- **Aproximadamente 4-6%** del monto
- Ejemplo: Vendes $1000 → Recibes ~$950

### **Cuándo recibes el dinero**

- **Inmediato**: Si usas "Mercado Pago Point" (lector de tarjetas)
- **14 días**: Si es tu primera venta
- **2-3 días**: Después de las primeras ventas

### **Dónde ves tu dinero**

1. Entra a tu cuenta de Mercado Pago
2. Ve a "Actividad" → "Ventas"
3. Ahí ves todos los pagos recibidos
4. Puedes transferirlo a tu cuenta bancaria

---

## 🔔 SISTEMA DE NOTIFICACIONES (WEBHOOK)

El webhook es cómo Mercado Pago te avisa cuando hay un pago.

### **Configurar Webhook**

1. En el panel de MP, ve a "Webhooks"
2. Agrega esta URL:
   ```
   http://TU-DOMINIO.com/almacen-whatsapp-1/webhook_mp.php
   ```
3. Selecciona eventos: "Pagos"

**IMPORTANTE:** Para desarrollo local, necesitas exponer tu localhost con:
- ngrok: https://ngrok.com/
- LocalTunnel: https://localtunnel.github.io/www/

---

## 📱 POLÍTICA: SOLO MERCADO PAGO

### **Por qué solo Mercado Pago y no efectivo:**

✅ **Seguridad**: El dinero llega antes de enviar
✅ **Comprobante**: Todo queda registrado
✅ **Protección**: Mercado Pago protege a ambos
✅ **Trazabilidad**: Sabes quién pagó y cuándo
✅ **Sin riesgos**: No manejas efectivo

### **Mensaje para tus clientes:**

```
🔒 POLÍTICA DE PAGO

✅ Aceptamos: Mercado Pago (tarjetas, débito, crédito)
❌ NO aceptamos: Efectivo, transferencias directas

📦 ENVÍO: Se realiza después de confirmar el pago
💳 SEGURO: Tu pago está protegido por Mercado Pago

¿Por qué? Para garantizar seguridad para ambos.
```

---

## 📊 GESTIÓN DE PEDIDOS

### **Estados del pedido:**

1. **PENDIENTE** → Creado, esperando pago
2. **PAGADO** → Pago confirmado, listo para enviar
3. **ENVIADO** → Producto despachado
4. **ENTREGADO** → Cliente recibió el producto
5. **CANCELADO** → Pedido cancelado

### **Panel de pedidos** (crear próximamente)

Un panel donde verás:
- Pedidos pendientes de pago
- Pedidos pagados (para enviar)
- Pedidos enviados
- Historial completo

---

## 🚀 CÓMO USAR EL SISTEMA

### **1. Cliente hace su pedido:**

```
catalogo.php → Agrega productos → checkout.php
```

### **2. Sistema genera link de pago:**

```
Sistema crea preferencia en Mercado Pago
Cliente es redirigido a página de pago de MP
```

### **3. Cliente paga:**

```
Ingresa datos de tarjeta en Mercado Pago (no en tu sitio)
MP procesa el pago
```

### **4. Confirmación automática:**

```
MP notifica a tu sistema vía webhook
Estado cambia a "PAGADO"
Tú recibes alerta para enviar
```

### **5. Tú envías el producto:**

```
Marcas pedido como "ENVIADO"
Cliente recibe notificación (WhatsApp)
```

---

## 🔐 SEGURIDAD

### **Datos sensibles:**

- ❌ NUNCA guardes datos de tarjetas
- ✅ Mercado Pago maneja toda la seguridad
- ✅ Tu sitio solo recibe confirmación de pago

### **Certificado SSL (HTTPS):**

Para producción, necesitas HTTPS:
- Compra un certificado SSL
- O usa Let's Encrypt (gratis)
- Mercado Pago lo requiere para webhooks

---

## 📝 ARCHIVOS CREADOS

```
config/mercadopago.php     → Configuración de credenciales
catalogo.php               → Catálogo de productos
checkout.php               → Página de checkout
js/carrito.js              → Lógica del carrito
js/checkout.js             → Lógica de checkout
api/crear_pedido_mp.php    → Crear preferencia de MP
webhook_mp.php             → Recibir notificaciones (próximo)
pago_exitoso.php           → Página de éxito (próximo)
pago_fallido.php           → Página de error (próximo)
```

---

## ✅ CHECKLIST ANTES DE EMPEZAR

- [ ] Cuenta de Mercado Pago creada
- [ ] Credenciales obtenidas (Access Token + Public Key)
- [ ] Credenciales configuradas en `config/mercadopago.php`
- [ ] Base de datos actualizada (ejecutar `instalar.php`)
- [ ] Productos cargados en el sistema
- [ ] Imágenes de productos subidas
- [ ] Probar con tarjetas de prueba
- [ ] Verificar que funciona el flujo completo

---

## 💡 PRÓXIMOS PASOS

1. Ejecuta `instalar.php` para crear tablas
2. Configura tus credenciales de Mercado Pago
3. Carga productos en `productos.php`
4. Prueba el flujo en `catalogo.php`
5. Haz una compra de prueba

---

## 🆘 PROBLEMAS COMUNES

### **"Error al crear preferencia"**
- Verifica que las credenciales sean correctas
- Asegúrate que sean de la misma cuenta
- Revisa que curl esté habilitado en PHP

### **"No recibo notificaciones"**
- Webhook necesita URL pública (no localhost)
- Usa ngrok para desarrollo local
- Verifica que la URL esté configurada en MP

### **"El pago no se confirma"**
- Revisa el webhook_mp.php
- Mira los logs de Mercado Pago
- Verifica el estado manualmente en tu cuenta MP

---

¿Listo para probarlo? 🚀
