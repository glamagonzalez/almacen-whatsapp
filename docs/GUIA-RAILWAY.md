# 🚂 GUÍA PARA DESPLEGAR EN RAILWAY

## ¿Por qué Railway?

Railway es SUPERIOR a InfinityFree porque:
- ✅ Soporta PHP, MySQL, Node.js (n8n), Docker (WAHA)
- ✅ Todo el sistema funciona completo (incluyendo WhatsApp)
- ✅ Fácil de configurar
- ✅ Plan gratuito: $5 crédito/mes (suficiente para empezar)
- ✅ Escala automáticamente

---

## 💰 Costo Estimado

- **Plan gratuito:** $5 USD de crédito/mes
- **Consumo estimado:** $3-4/mes para este sistema
- Si necesitas más: $5/mes adicionales

---

## 🚀 MÉTODO 1: Desplegar desde GitHub (RECOMENDADO)

### Paso 1: Subir código a GitHub

Tu código ya está en GitHub: `glamagonzalez/almacen-whatsapp`

### Paso 2: Crear cuenta en Railway

1. Ve a: https://railway.app
2. Haz clic en **"Login"**
3. Inicia sesión con tu cuenta de **GitHub**
4. Autoriza Railway

### Paso 3: Crear Nuevo Proyecto

1. Clic en **"New Project"**
2. Selecciona **"Deploy from GitHub repo"**
3. Busca y selecciona: `glamagonzalez/almacen-whatsapp`
4. Railway detectará automáticamente que es PHP

### Paso 4: Agregar Base de Datos MySQL

1. En tu proyecto, clic en **"+ New"**
2. Selecciona **"Database"**
3. Elige **"MySQL"**
4. Railway creará la base de datos automáticamente
5. Anota las credenciales (las verás en "Variables")

### Paso 5: Configurar Variables de Entorno

1. Clic en tu servicio PHP
2. Ve a **"Variables"** tab
3. Agrega estas variables:

```bash
# Base de Datos
DB_HOST=mysql.railway.internal
DB_NAME=railway
DB_USER=root
DB_PASSWORD=[Railway lo genera automáticamente]

# Mercado Pago
MP_ACCESS_TOKEN=APP_USR-7544114614777894-112915-efb36d1a0152e91909406f8f3710edfc-62732469
MP_PUBLIC_KEY=APP_USR-3c847c3f-cc9c-4aba-b2a9-62899023373f
MP_MODO_PRUEBA=false

# URL del Sistema
APP_URL=https://tu-proyecto.up.railway.app
```

### Paso 6: Importar Base de Datos

1. Ve al servicio **MySQL** en Railway
2. Clic en **"Data"** tab
3. Clic en **"Import"**
4. Sube el archivo: `almacen_digital.sql`

O usa el cliente MySQL:

```bash
mysql -h containers-us-west-XXX.railway.app -P XXXX -u root -p railway < almacen_digital.sql
```

### Paso 7: Configurar Dominio

Railway te da un dominio automático:
```
https://almacen-whatsapp-production.up.railway.app
```

Si quieres tu propio dominio:
1. Clic en **"Settings"**
2. Sección **"Domains"**
3. Clic en **"Custom Domain"**
4. Ingresa tu dominio

---

## 🚀 MÉTODO 2: Desplegar Directamente (Alternativo)

### Paso 1: Preparar Archivos

Necesitamos crear algunos archivos para Railway:

#### `railway.json`
```json
{
  "$schema": "https://railway.app/railway.schema.json",
  "build": {
    "builder": "NIXPACKS"
  },
  "deploy": {
    "startCommand": "php -S 0.0.0.0:$PORT -t .",
    "restartPolicyType": "ON_FAILURE",
    "restartPolicyMaxRetries": 10
  }
}
```

#### `.nixpacks.toml`
```toml
[phases.setup]
nixPkgs = ["php82", "php82Packages.composer"]

[phases.install]
cmds = ["composer install --no-dev --optimize-autoloader"]

[start]
cmd = "php -S 0.0.0.0:$PORT -t ."
```

### Paso 2: Subir a Railway

1. Instala Railway CLI:
```bash
npm install -g @railway/cli
```

2. Inicia sesión:
```bash
railway login
```

3. Inicializa proyecto:
```bash
cd C:\xampp\htdocs\almacen-whatsapp-1
railway init
```

4. Despliega:
```bash
railway up
```

---

## 📱 PASO EXTRA: Desplegar n8n + WAHA

### Opción A: n8n en Railway

1. En tu proyecto Railway, clic **"+ New"**
2. Selecciona **"Template"**
3. Busca **"n8n"**
4. Despliega

O manual:

1. Clic **"+ New"** → **"GitHub Repo"**
2. Usa el repo oficial: `n8n-io/n8n`
3. Variables de entorno:
```bash
N8N_BASIC_AUTH_ACTIVE=true
N8N_BASIC_AUTH_USER=admin
N8N_BASIC_AUTH_PASSWORD=tu_contraseña_segura
WEBHOOK_URL=https://tu-n8n.up.railway.app
```

### Opción B: WAHA en Railway

1. Clic **"+ New"** → **"Docker Image"**
2. Imagen: `devlikeapro/waha`
3. Variables:
```bash
WHATSAPP_API_KEY=changeme
WHATSAPP_SWAGGER_ENABLED=true
```

### Conectar Todo

1. **PHP App** → Llama webhooks de n8n
2. **n8n** → Se conecta a WAHA
3. **WAHA** → Envía mensajes de WhatsApp

Actualiza URLs en tu código:
- `n8n URL`: `https://tu-n8n.up.railway.app/webhook/`
- `WAHA URL`: `https://tu-waha.up.railway.app/api/`

---

## ⚙️ Configuración Post-Despliegue

### 1. Actualizar config/database.php

Railway inyecta las variables automáticamente. Modifica el archivo:

```php
<?php
// Usar variables de entorno de Railway
$host = getenv('MYSQLHOST') ?: 'localhost';
$dbname = getenv('MYSQLDATABASE') ?: 'railway';
$username = getenv('MYSQLUSER') ?: 'root';
$password = getenv('MYSQLPASSWORD') ?: '';
$port = getenv('MYSQLPORT') ?: '3306';

try {
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
```

### 2. Actualizar Webhook de Mercado Pago

1. Ve a: https://www.mercadopago.com.ar/developers/panel/app
2. Webhooks → Agregar URL:
```
https://almacen-whatsapp-production.up.railway.app/webhook_mp.php
```

### 3. Configurar SSL (HTTPS)

Railway incluye SSL automáticamente ✅

---

## ✅ Checklist de Despliegue

- [ ] Código subido a GitHub
- [ ] Proyecto creado en Railway
- [ ] Base de datos MySQL creada
- [ ] Variables de entorno configuradas
- [ ] Base de datos importada
- [ ] Aplicación desplegada y funcionando
- [ ] n8n desplegado (opcional pero recomendado)
- [ ] WAHA desplegado (opcional)
- [ ] Webhook configurado en Mercado Pago
- [ ] Dominio configurado
- [ ] Prueba de pago realizada

---

## 🎯 URLs Finales

Después del despliegue tendrás:

```
PHP App:    https://almacen-whatsapp-production.up.railway.app
Catálogo:   https://almacen-whatsapp-production.up.railway.app/catalogo.php
Checkout:   https://almacen-whatsapp-production.up.railway.app/checkout-mejorado.php

n8n:        https://n8n-production.up.railway.app
WAHA:       https://waha-production.up.railway.app
MySQL:      mysql.railway.internal (interno)
```

---

## 💡 Ventajas de Railway vs InfinityFree

| Característica | InfinityFree | Railway |
|----------------|--------------|---------|
| PHP | ✅ | ✅ |
| MySQL | ✅ | ✅ |
| Node.js (n8n) | ❌ | ✅ |
| Docker (WAHA) | ❌ | ✅ |
| SSL/HTTPS | ✅ | ✅ |
| Recursos | Limitado | Escalable |
| Costo | Gratis | $5/mes gratis |
| WhatsApp | ❌ | ✅ |

---

## 🆘 Problemas Comunes

### "Build failed"
- Verifica que `composer.json` exista
- Asegúrate que todas las dependencias están en el repo

### "Cannot connect to MySQL"
- Verifica que usas `mysql.railway.internal` como host
- Verifica que las variables de entorno estén configuradas

### "502 Bad Gateway"
- Espera 2-3 minutos después del despliegue
- Revisa los logs en Railway

### Composer no encuentra autoload
```bash
# Ejecuta en Railway CLI:
railway run composer install
```

---

## 📞 Recursos

- **Railway Docs:** https://docs.railway.app
- **Railway Discord:** https://discord.gg/railway
- **n8n on Railway:** https://n8n.io/hosting/#railway

---

## 🎉 ¡Listo para Producción!

Con Railway tienes TODO el sistema funcionando:
✅ Catálogo y checkout
✅ Pagos con Mercado Pago
✅ Base de datos MySQL
✅ n8n para automatizaciones
✅ WAHA para WhatsApp
✅ SSL incluido
✅ Escalable

**¡Tu almacén digital está listo para vender! 🚀**
