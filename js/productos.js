/**
 * PRODUCTOS.JS - Lógica para gestión de productos
 */

/**
 * CALCULAR PRECIO DE VENTA automáticamente
 * basado en precio de compra + margen
 */
function calcularPrecioVenta() {
    const precioCompra = parseFloat(document.getElementById('precio_compra').value) || 0;
    const margen = parseFloat(document.getElementById('margen_porcentaje').value) || 0;
    
    // Fórmula: Precio Venta = Precio Compra + (Precio Compra * Margen / 100)
    const precioVenta = precioCompra + (precioCompra * margen / 100);
    const ganancia = precioVenta - precioCompra;
    
    document.getElementById('precio_venta').value = precioVenta.toFixed(2);
    document.getElementById('gananciaTexto').textContent = `Ganancia: $${ganancia.toFixed(2)} por unidad`;
}

/**
 * GUARDAR PRODUCTO (nuevo o editar)
 */
function guardarProducto() {
    const form = document.getElementById('formProducto');
    const formData = new FormData(form);
    
    fetch('api/guardar_producto.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ Producto guardado correctamente');
            location.reload();
        } else {
            alert('❌ Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('❌ Error al guardar el producto');
    });
}

/**
 * EDITAR PRODUCTO
 */
function editarProducto(id) {
    fetch(`api/get_producto.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Llenar el formulario con los datos del producto
                const form = document.getElementById('formProducto');
                const producto = data.producto;
                
                form.querySelector('[name="nombre"]').value = producto.nombre;
                form.querySelector('[name="codigo_barras"]').value = producto.codigo_barras || '';
                form.querySelector('[name="categoria"]').value = producto.categoria;
                form.querySelector('[name="proveedor"]').value = producto.proveedor;
                form.querySelector('[name="precio_compra"]').value = producto.precio_compra;
                form.querySelector('[name="margen_porcentaje"]').value = producto.margen_porcentaje;
                form.querySelector('[name="precio_venta"]').value = producto.precio_venta;
                form.querySelector('[name="stock_actual"]').value = producto.stock_actual;
                form.querySelector('[name="stock_minimo"]').value = producto.stock_minimo;
                form.querySelector('[name="descripcion"]').value = producto.descripcion || '';
                
                // Mostrar modal
                const modal = new bootstrap.Modal(document.getElementById('modalProducto'));
                modal.show();
            }
        });
}

/**
 * ENVIAR POR WHATSAPP
 * Genera un mensaje con la info del producto
 */
function enviarWhatsApp(id) {
    fetch(`api/get_producto.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const p = data.producto;
                
                // Crear mensaje para WhatsApp
                const mensaje = `🛒 *${p.nombre}*\n\n` +
                              `📦 Categoría: ${p.categoria}\n` +
                              `💰 Precio: $${p.precio_venta}\n` +
                              `📊 Stock disponible: ${p.stock_actual} unidades\n\n` +
                              `${p.descripcion ? p.descripcion + '\n\n' : ''}` +
                              `¿Te interesa? ¡Contáctame!`;
                
                // Codificar para URL
                const mensajeCodificado = encodeURIComponent(mensaje);
                
                // Abrir WhatsApp (pide número)
                const urlWhatsApp = `https://wa.me/?text=${mensajeCodificado}`;
                window.open(urlWhatsApp, '_blank');
            }
        });
}

/**
 * EJEMPLO DE CÁLCULO DE MARGEN
 */
console.log(`
📊 EJEMPLOS DE MÁRGENES DE GANANCIA:

Producto comprado a $100
┌──────────┬─────────────┬──────────┐
│ Margen   │ Precio Venta│ Ganancia │
├──────────┼─────────────┼──────────┤
│ 20%      │ $120        │ $20      │
│ 30%      │ $130        │ $30      │
│ 40%      │ $140        │ $40      │
│ 50%      │ $150        │ $50      │
└──────────┴─────────────┴──────────┘

Fórmula: Precio Venta = Compra + (Compra × Margen%)
`);
