# 📸 GUÍA RÁPIDA: Importar Imágenes de Productos

## 🎯 Tienes 3 opciones para importar imágenes

---

## **OPCIÓN 1: Desde PDF de Ofertas (Maxi Consumo)**

### Método A: Extraer Automáticamente ⚡
1. Abrí `importar_imagenes.php` en tu navegador
2. Elegí **"Desde PDF"**
3. Sube el PDF de ofertas
4. El sistema extrae todas las imágenes automáticamente

⚠️ **Nota:** Necesita ImageMagick instalado. Si no funciona, usá el Método B.

### Método B: Manual (El más fácil) ✋
1. Abrí el PDF con **Adobe Reader** o **Foxit Reader**
2. **Click derecho** sobre cada imagen → **"Copiar imagen"**
3. Abrí **Paint** (Win + R → `mspaint`)
4. **Pegar** (Ctrl + V)
5. **Guardar** con nombre del producto (ejemplo: `coca_cola.jpg`)
6. Repetir para cada producto

💡 **Tip:** Guardá todas en una carpeta, después usá OPCIÓN 2.

---

## **OPCIÓN 2: Desde Carpeta (Si ya las descargaste)** 📁

1. Abrí `importar_imagenes.php`
2. Elegí **"Desde Carpeta"**
3. Click en **"Seleccionar múltiples imágenes"**
4. Seleccioná TODAS las imágenes a la vez (Ctrl + A)
5. **Subir Imágenes**
6. Listo! Ahora andá a `gestionar_imagenes.php` para asignarlas

---

## **OPCIÓN 3: Buscar en Internet (Automático)** 🌐

1. Abrí `importar_imagenes.php`
2. Elegí **"Buscar Online"**
3. Click en **"Buscar Imágenes Automáticamente"**
4. El sistema busca imágenes para productos sin foto
5. Las descarga y asigna automáticamente

⚠️ **Nota:** Busca imágenes genéricas. No siempre son exactas.

---

## 🔗 Asignar Imágenes a Productos

Después de subir las imágenes:

1. Andá a **`gestionar_imagenes.php`**
2. Vas a ver:
   - Tus productos (izquierda)
   - Imágenes disponibles (derecha)
3. **Click en la imagen** que querés asignar
4. Se asigna automáticamente al producto

---

## 📋 Resumen de Archivos

| Archivo | Función |
|---------|---------|
| `importar_imagenes.php` | Pantalla principal con 3 opciones |
| `gestionar_imagenes.php` | Asignar imágenes a productos |
| `tutorial_imagenes.php` | Tutorial detallado paso a paso |
| `api/subir_imagenes_masivo.php` | Backend para subir múltiples imágenes |
| `api/extraer_pdf.php` | Backend para extraer del PDF |
| `api/buscar_imagenes_automatico.php` | Backend para buscar en internet |

---

## ✅ Recomendaciones

- **Formato:** JPG (más liviano) o PNG (mejor calidad)
- **Tamaño:** 400x400 px mínimo
- **Nombre:** Igual al producto (facilita búsqueda)
- **Calidad:** Buena resolución para que se vea bien

---

## 🚀 Flujo Completo

```
1. Conseguir PDF de Maxi Consumo
   ↓
2. Elegir método:
   - Manual: Copiar c/u en Paint → Carpeta
   - Automático: Subir PDF → Extraer
   ↓
3. Subir imágenes:
   - importar_imagenes.php → Desde Carpeta
   ↓
4. Asignar a productos:
   - gestionar_imagenes.php → Click para asignar
   ↓
5. Ver resultado:
   - catalogo.php → Productos con imágenes
```

---

## ❓ Problemas Comunes

**No se extrae del PDF:**
- Usá método manual (copiar/pegar)
- O descargá PDF-XChange Editor

**Imágenes muy pesadas:**
- Comprimí con TinyPNG.com
- O usá Paint → Guardar con menos calidad

**No aparecen las imágenes:**
- Verificá que la carpeta `uploads/` tenga permisos de escritura
- Revisá que las imágenes estén en `uploads/`

---

## 📞 Próximos Pasos

Una vez que tengas las imágenes asignadas:

1. ✅ Productos con imágenes
2. ➡️ Configurar Mercado Pago (ver `GUIA_MERCADO_PAGO.md`)
3. ➡️ Compartir catálogo por WhatsApp
4. ➡️ Recibir pagos y enviar pedidos

---

**¿Dudas?** Abrí `tutorial_imagenes.php` en tu navegador para una guía visual detallada.
