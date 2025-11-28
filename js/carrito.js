/**
 * CARRITO.JS - Gestión del carrito de compras
 */

// Carrito en memoria (localStorage)
let carrito = JSON.parse(localStorage.getItem('carrito')) || [];

/**
 * AGREGAR PRODUCTO AL CARRITO
 */
function agregarAlCarrito(id, nombre, precio, stockDisponible) {
    // Buscar si ya existe en el carrito
    const itemExistente = carrito.find(item => item.id === id);
    
    if (itemExistente) {
        // Verificar stock
        if (itemExistente.cantidad < stockDisponible) {
            itemExistente.cantidad++;
            mostrarNotificacion('success', '✅ Cantidad actualizada');
        } else {
            mostrarNotificacion('warning', '⚠️ No hay más stock disponible');
            return;
        }
    } else {
        // Agregar nuevo item
        carrito.push({
            id: id,
            nombre: nombre,
            precio: precio,
            cantidad: 1,
            stockDisponible: stockDisponible
        });
        mostrarNotificacion('success', '✅ Producto agregado al carrito');
    }
    
    guardarCarrito();
    actualizarCarrito();
}

/**
 * ELIMINAR DEL CARRITO
 */
function eliminarDelCarrito(id) {
    carrito = carrito.filter(item => item.id !== id);
    guardarCarrito();
    actualizarCarrito();
    mostrarNotificacion('info', 'Producto eliminado del carrito');
}

/**
 * CAMBIAR CANTIDAD
 */
function cambiarCantidad(id, cantidad) {
    const item = carrito.find(item => item.id === id);
    if (item) {
        if (cantidad > 0 && cantidad <= item.stockDisponible) {
            item.cantidad = cantidad;
            guardarCarrito();
            actualizarCarrito();
        } else if (cantidad > item.stockDisponible) {
            mostrarNotificacion('warning', '⚠️ Stock insuficiente');
        }
    }
}

/**
 * GUARDAR CARRITO EN LOCALSTORAGE
 */
function guardarCarrito() {
    localStorage.setItem('carrito', JSON.stringify(carrito));
}

/**
 * ACTUALIZAR VISTA DEL CARRITO
 */
function actualizarCarrito() {
    // Actualizar contador
    const totalItems = carrito.reduce((sum, item) => sum + item.cantidad, 0);
    document.getElementById('cartCount').textContent = totalItems;
    
    // Actualizar lista de items
    const cartItemsDiv = document.getElementById('cartItems');
    
    if (carrito.length === 0) {
        cartItemsDiv.innerHTML = '<p class="text-center text-muted">El carrito está vacío</p>';
        document.getElementById('btnCheckout').disabled = true;
    } else {
        document.getElementById('btnCheckout').disabled = false;
        let html = '<div class="table-responsive"><table class="table">';
        html += '<thead><tr><th>Producto</th><th>Precio</th><th>Cantidad</th><th>Subtotal</th><th></th></tr></thead><tbody>';
        
        carrito.forEach(item => {
            const subtotal = item.precio * item.cantidad;
            html += `
                <tr>
                    <td><strong>${item.nombre}</strong></td>
                    <td>$${item.precio.toFixed(2)}</td>
                    <td>
                        <div class="input-group" style="width: 120px;">
                            <button class="btn btn-sm btn-outline-secondary" onclick="cambiarCantidad(${item.id}, ${item.cantidad - 1})">-</button>
                            <input type="number" class="form-control form-control-sm text-center" 
                                   value="${item.cantidad}" min="1" max="${item.stockDisponible}"
                                   onchange="cambiarCantidad(${item.id}, parseInt(this.value))">
                            <button class="btn btn-sm btn-outline-secondary" onclick="cambiarCantidad(${item.id}, ${item.cantidad + 1})">+</button>
                        </div>
                    </td>
                    <td class="text-success"><strong>$${subtotal.toFixed(2)}</strong></td>
                    <td>
                        <button class="btn btn-sm btn-danger" onclick="eliminarDelCarrito(${item.id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
        });
        
        html += '</tbody></table></div>';
        cartItemsDiv.innerHTML = html;
    }
    
    // Calcular total
    const total = carrito.reduce((sum, item) => sum + (item.precio * item.cantidad), 0);
    document.getElementById('cartTotal').textContent = '$' + total.toFixed(2);
}

/**
 * IR A CHECKOUT (Página de pago)
 */
function irACheckout() {
    if (carrito.length === 0) {
        mostrarNotificacion('warning', '⚠️ El carrito está vacío');
        return;
    }
    
    // Guardar carrito y redirigir
    guardarCarrito();
    window.location.href = 'checkout.php';
}

/**
 * BÚSQUEDA Y FILTROS
 */
document.addEventListener('DOMContentLoaded', function() {
    // Cargar carrito al iniciar
    actualizarCarrito();
    
    // Búsqueda de productos
    const searchInput = document.getElementById('searchProduct');
    if (searchInput) {
        searchInput.addEventListener('input', filtrarProductos);
    }
    
    // Filtro por categoría
    const filterCategory = document.getElementById('filterCategory');
    if (filterCategory) {
        filterCategory.addEventListener('change', filtrarProductos);
    }
});

function filtrarProductos() {
    const searchTerm = document.getElementById('searchProduct').value.toLowerCase();
    const category = document.getElementById('filterCategory').value;
    const productItems = document.querySelectorAll('.product-item');
    
    productItems.forEach(item => {
        const productName = item.querySelector('.card-title').textContent.toLowerCase();
        const productCategory = item.getAttribute('data-categoria');
        
        const matchSearch = productName.includes(searchTerm);
        const matchCategory = !category || productCategory === category;
        
        if (matchSearch && matchCategory) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
}

/**
 * MOSTRAR NOTIFICACIÓN
 */
function mostrarNotificacion(tipo, mensaje) {
    const alertClass = tipo === 'success' ? 'alert-success' : 
                       tipo === 'warning' ? 'alert-warning' : 
                       'alert-info';
    
    const notif = document.createElement('div');
    notif.className = `alert ${alertClass} position-fixed`;
    notif.style.top = '20px';
    notif.style.right = '20px';
    notif.style.zIndex = '9999';
    notif.style.minWidth = '300px';
    notif.textContent = mensaje;
    
    document.body.appendChild(notif);
    
    setTimeout(() => {
        notif.remove();
    }, 3000);
}
