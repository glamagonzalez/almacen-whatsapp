/**
 * CHECKOUT.JS - Procesar pago con Mercado Pago
 */

// Cargar carrito del localStorage
let carrito = JSON.parse(localStorage.getItem('carrito')) || [];

document.addEventListener('DOMContentLoaded', function() {
    // Verificar que hay productos
    if (carrito.length === 0) {
        alert('⚠️ El carrito está vacío');
        window.location.href = 'catalogo.php';
        return;
    }
    
    // Mostrar resumen
    mostrarResumen();
    
    // Manejar envío del formulario
    document.getElementById('formCheckout').addEventListener('submit', procesarCheckout);
});

/**
 * MOSTRAR RESUMEN DEL PEDIDO
 */
function mostrarResumen() {
    const summaryDiv = document.getElementById('orderSummary');
    let html = '<ul class="list-group">';
    
    carrito.forEach(item => {
        const subtotal = item.precio * item.cantidad;
        html += `
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                    <strong>${item.nombre}</strong><br>
                    <small class="text-muted">${item.cantidad} x $${item.precio.toFixed(2)}</small>
                </div>
                <span class="text-success"><strong>$${subtotal.toFixed(2)}</strong></span>
            </li>
        `;
    });
    
    html += '</ul>';
    summaryDiv.innerHTML = html;
    
    // Calcular total
    const total = carrito.reduce((sum, item) => sum + (item.precio * item.cantidad), 0);
    document.getElementById('orderTotal').textContent = '$' + total.toFixed(2);
}

/**
 * PROCESAR CHECKOUT Y CREAR PREFERENCIA DE MERCADO PAGO
 */
async function procesarCheckout(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const btnSubmit = e.target.querySelector('button[type="submit"]');
    
    // Deshabilitar botón
    btnSubmit.disabled = true;
    btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Procesando...';
    
    // Agregar productos al formData
    formData.append('productos', JSON.stringify(carrito));
    
    try {
        const response = await fetch('api/crear_pedido_mp.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Redirigir a Mercado Pago
            if (data.init_point) {
                window.location.href = data.init_point;
            } else {
                alert('✅ Pedido creado. Redirigiendo a pago...');
                // Limpiar carrito
                localStorage.removeItem('carrito');
                window.location.href = 'pedidos.php?pedido_id=' + data.pedido_id;
            }
        } else {
            alert('❌ Error: ' + data.message);
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = '<i class="fab fa-cc-mercadopago"></i> Ir a Mercado Pago';
        }
        
    } catch (error) {
        console.error('Error:', error);
        alert('❌ Error al procesar el pedido');
        btnSubmit.disabled = false;
        btnSubmit.innerHTML = '<i class="fab fa-cc-mercadopago"></i> Ir a Mercado Pago';
    }
}
