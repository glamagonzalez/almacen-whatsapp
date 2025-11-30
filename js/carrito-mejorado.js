/**
 * CARRITO DE COMPRAS MEJORADO v2.0
 * Sistema completo con localStorage, cupones, envío, y más
 */

class CarritoCompras {
    constructor() {
        this.carrito = this.cargarCarrito();
        this.cuponAplicado = null;
        this.costoEnvio = 0;
        this.actualizarUI();
    }

    cargarCarrito() {
        const carritoGuardado = localStorage.getItem('carrito');
        return carritoGuardado ? JSON.parse(carritoGuardado) : [];
    }

    guardarCarrito() {
        localStorage.setItem('carrito', JSON.stringify(this.carrito));
        this.actualizarUI();
    }

    agregarProducto(id, nombre, precio, stock, imagen = null) {
        const itemExistente = this.carrito.find(item => item.id === id);
        
        if (itemExistente) {
            if (itemExistente.cantidad < stock) {
                itemExistente.cantidad++;
                this.mostrarNotificacion('✅ Cantidad actualizada', 'success');
            } else {
                this.mostrarNotificacion('⚠️ No hay más stock disponible', 'warning');
                return;
            }
        } else {
            this.carrito.push({
                id: id,
                nombre: nombre,
                precio: precio,
                cantidad: 1,
                stock: stock,
                imagen: imagen
            });
            this.mostrarNotificacion('✅ Producto agregado al carrito', 'success');
        }
        
        this.guardarCarrito();
    }

    actualizarCantidad(id, cantidad) {
        const item = this.carrito.find(i => i.id === id);
        if (item) {
            if (cantidad > 0 && cantidad <= item.stock) {
                item.cantidad = cantidad;
                this.guardarCarrito();
            } else if (cantidad > item.stock) {
                this.mostrarNotificacion('⚠️ Stock insuficiente', 'warning');
            } else if (cantidad <= 0) {
                this.eliminarProducto(id);
            }
        }
    }

    eliminarProducto(id) {
        this.carrito = this.carrito.filter(item => item.id !== id);
        this.guardarCarrito();
        this.mostrarNotificacion('🗑️ Producto eliminado', 'info');
    }

    vaciarCarrito() {
        if (confirm('¿Estás seguro de vaciar el carrito?')) {
            this.carrito = [];
            this.cuponAplicado = null;
            this.costoEnvio = 0;
            this.guardarCarrito();
            this.mostrarNotificacion('🗑️ Carrito vaciado', 'info');
        }
    }

    calcularSubtotal() {
        return this.carrito.reduce((total, item) => total + (item.precio * item.cantidad), 0);
    }

    async aplicarCupon(codigo) {
        const cupones = {
            'PRIMERACOMPRA': { tipo: 'porcentaje', valor: 10, descripcion: '10% de descuento' },
            'VERANO2025': { tipo: 'porcentaje', valor: 15, descripcion: '15% OFF Verano' },
            'ENVIOGRATIS': { tipo: 'envio', valor: 100, descripcion: 'Envío gratis' },
            'DESCUENTO50': { tipo: 'fijo', valor: 50, descripcion: '$50 de descuento' },
            'BIENVENIDO': { tipo: 'porcentaje', valor: 5, descripcion: '5% de bienvenida' }
        };

        const cupon = cupones[codigo.toUpperCase()];
        
        if (cupon) {
            this.cuponAplicado = { codigo: codigo.toUpperCase(), ...cupon };
            this.actualizarUI();
            this.mostrarNotificacion('🎉 Cupón aplicado: ' + cupon.descripcion, 'success');
            return true;
        } else {
            this.mostrarNotificacion('❌ Cupón inválido', 'danger');
            return false;
        }
    }

    removerCupon() {
        this.cuponAplicado = null;
        this.actualizarUI();
        this.mostrarNotificacion('Cupón removido', 'info');
    }

    calcularDescuento() {
        if (!this.cuponAplicado) return 0;
        
        const subtotal = this.calcularSubtotal();
        
        switch(this.cuponAplicado.tipo) {
            case 'porcentaje':
                return subtotal * (this.cuponAplicado.valor / 100);
            case 'fijo':
                return this.cuponAplicado.valor;
            case 'envio':
                return 0;
            default:
                return 0;
        }
    }

    calcularEnvio(codigoPostal = null) {
        if (this.cuponAplicado && this.cuponAplicado.tipo === 'envio') {
            return 0;
        }

        const subtotal = this.calcularSubtotal();
        
        if (subtotal >= 5000) {
            return 0;
        }

        let costoBase = 500;

        if (codigoPostal) {
            const primerDigito = parseInt(codigoPostal.charAt(0));
            if (primerDigito >= 5) {
                costoBase += 200;
            }
        }

        this.costoEnvio = costoBase;
        return costoBase;
    }

    calcularTotal() {
        const subtotal = this.calcularSubtotal();
        const descuento = this.calcularDescuento();
        const envio = this.costoEnvio;
        
        return subtotal - descuento + envio;
    }

    obtenerCantidadItems() {
        return this.carrito.reduce((total, item) => total + item.cantidad, 0);
    }

    actualizarUI() {
        this.actualizarContador();
        this.actualizarListaCarrito();
        this.actualizarTotales();
    }

    actualizarContador() {
        const contador = document.getElementById('cartCount');
        if (contador) {
            const total = this.obtenerCantidadItems();
            contador.textContent = total;
            contador.style.display = total > 0 ? 'flex' : 'none';
        }
    }

    actualizarListaCarrito() {
        const container = document.getElementById('cartItems');
        if (!container) return;

        if (this.carrito.length === 0) {
            container.innerHTML = `
                <div class="text-center text-muted py-5">
                    <i class="fas fa-shopping-cart fa-3x mb-3"></i>
                    <p>Tu carrito está vacío</p>
                    <button class="btn btn-primary" data-bs-dismiss="modal">
                        <i class="fas fa-shopping-bag"></i> Comenzar a comprar
                    </button>
                </div>
            `;
            return;
        }

        let html = '<div class="list-group mb-3">';
        
        this.carrito.forEach(item => {
            const subtotalItem = item.precio * item.cantidad;
            html += `
                <div class="list-group-item">
                    <div class="row align-items-center g-2">
                        <div class="col-md-2 col-3">
                            <img src="${item.imagen || 'https://via.placeholder.com/100'}" 
                                 class="img-fluid rounded" alt="${item.nombre}">
                        </div>
                        <div class="col-md-4 col-9">
                            <h6 class="mb-1">${item.nombre}</h6>
                            <small class="text-muted">$${item.precio.toFixed(2)} c/u</small>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="input-group input-group-sm">
                                <button class="btn btn-outline-secondary" type="button" 
                                        onclick="carrito.actualizarCantidad(${item.id}, ${item.cantidad - 1})">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <input type="text" class="form-control text-center" 
                                       value="${item.cantidad}" readonly style="max-width: 50px;">
                                <button class="btn btn-outline-secondary" type="button"
                                        onclick="carrito.actualizarCantidad(${item.id}, ${item.cantidad + 1})">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                            <small class="text-muted d-block text-center">Stock: ${item.stock}</small>
                        </div>
                        <div class="col-md-3 col-6 text-end">
                            <strong class="d-block">$${subtotalItem.toFixed(2)}</strong>
                            <button class="btn btn-sm btn-danger mt-1" 
                                    onclick="carrito.eliminarProducto(${item.id})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
        });
        
        html += '</div>';
        
        html += `
            <div class="card mb-3 border-primary">
                <div class="card-body">
                    <h6 class="card-title">
                        <i class="fas fa-tag text-primary"></i> ¿Tenés un cupón de descuento?
                    </h6>
                    <div class="input-group">
                        <input type="text" class="form-control" id="inputCupon" 
                               placeholder="Ingresá tu código" ${this.cuponAplicado ? 'disabled' : ''}>
                        <button class="btn btn-primary" type="button" 
                                onclick="aplicarCuponDesdeInput()" 
                                ${this.cuponAplicado ? 'disabled' : ''}>
                            Aplicar
                        </button>
                    </div>
                    ${this.cuponAplicado ? `
                        <div class="alert alert-success mt-2 mb-0 d-flex justify-content-between align-items-center">
                            <span>
                                <i class="fas fa-check-circle"></i> 
                                <strong>${this.cuponAplicado.codigo}</strong>: ${this.cuponAplicado.descripcion}
                            </span>
                            <button class="btn btn-sm btn-danger" onclick="carrito.removerCupon()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    ` : `
                        <small class="text-muted d-block mt-2">
                            <strong>Cupones disponibles:</strong><br>
                            • PRIMERACOMPRA (10% OFF)<br>
                            • VERANO2025 (15% OFF)<br>
                            • ENVIOGRATIS (Envío gratis)<br>
                            • DESCUENTO50 ($50 OFF)<br>
                            • BIENVENIDO (5% OFF)
                        </small>
                    `}
                </div>
            </div>
            
            <div class="d-flex justify-content-between mb-2">
                <button class="btn btn-outline-danger" onclick="carrito.vaciarCarrito()">
                    <i class="fas fa-trash"></i> Vaciar carrito
                </button>
            </div>
        `;
        
        container.innerHTML = html;
    }

    actualizarTotales() {
        const subtotal = this.calcularSubtotal();
        const descuento = this.calcularDescuento();
        const envio = this.costoEnvio;
        const total = this.calcularTotal();

        const totalElement = document.getElementById('cartTotal');
        if (totalElement) {
            totalElement.innerHTML = `
                <div class="text-end">
                    <div class="mb-2">
                        <span class="text-muted">Subtotal:</span>
                        <strong class="ms-3">$${subtotal.toFixed(2)}</strong>
                    </div>
                    ${descuento > 0 ? `
                        <div class="mb-2 text-success">
                            <span>Descuento:</span>
                            <strong class="ms-3">-$${descuento.toFixed(2)}</strong>
                        </div>
                    ` : ''}
                    <div class="mb-2">
                        <span class="text-muted">Envío:</span>
                        <strong class="ms-3">${envio === 0 ? '<span class="text-success">GRATIS 🎉</span>' : '$' + envio.toFixed(2)}</strong>
                    </div>
                    ${subtotal < 5000 && envio > 0 ? `
                        <small class="text-muted d-block mb-2">
                            Falta $${(5000 - subtotal).toFixed(2)} para envío gratis
                        </small>
                    ` : ''}
                    <hr>
                    <h4 class="text-success mb-0">
                        Total: $${total.toFixed(2)}
                    </h4>
                </div>
            `;
        }

        const btnCheckout = document.getElementById('btnCheckout');
        if (btnCheckout) {
            btnCheckout.disabled = this.carrito.length === 0;
        }
    }

    mostrarNotificacion(mensaje, tipo = 'info') {
        const toastContainer = document.getElementById('toastContainer') || this.crearToastContainer();
        
        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white bg-${tipo} border-0`;
        toast.setAttribute('role', 'alert');
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">${mensaje}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" 
                        data-bs-dismiss="toast"></button>
            </div>
        `;
        
        toastContainer.appendChild(toast);
        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();
        
        toast.addEventListener('hidden.bs.toast', () => toast.remove());
    }

    crearToastContainer() {
        const container = document.createElement('div');
        container.id = 'toastContainer';
        container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        container.style.zIndex = '9999';
        document.body.appendChild(container);
        return container;
    }

    irACheckout() {
        if (this.carrito.length === 0) {
            this.mostrarNotificacion('⚠️ Tu carrito está vacío', 'warning');
            return;
        }

        localStorage.setItem('checkoutData', JSON.stringify({
            carrito: this.carrito,
            cupon: this.cuponAplicado,
            envio: this.costoEnvio,
            subtotal: this.calcularSubtotal(),
            descuento: this.calcularDescuento(),
            total: this.calcularTotal()
        }));

        window.location.href = 'checkout.php';
    }
}

// Instancia global
const carrito = new CarritoCompras();

// Funciones helper
function agregarAlCarrito(id, nombre, precio, stock, imagen = null) {
    carrito.agregarProducto(id, nombre, precio, stock, imagen);
}

function aplicarCuponDesdeInput() {
    const input = document.getElementById('inputCupon');
    if (input && input.value.trim()) {
        carrito.aplicarCupon(input.value.trim());
    }
}

function irACheckout() {
    carrito.irACheckout();
}

// Búsqueda y filtros
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchProduct');
    const filterCategory = document.getElementById('filterCategory');
    const productItems = document.querySelectorAll('.product-item');

    function filtrarProductos() {
        const searchTerm = searchInput ? searchInput.value.toLowerCase() : '';
        const categoryFilter = filterCategory ? filterCategory.value : '';

        productItems.forEach(item => {
            const productName = item.querySelector('.card-title').textContent.toLowerCase();
            const productCategory = item.dataset.categoria;

            const matchSearch = productName.includes(searchTerm);
            const matchCategory = !categoryFilter || productCategory === categoryFilter;

            item.style.display = (matchSearch && matchCategory) ? 'block' : 'none';
        });
    }

    if (searchInput) searchInput.addEventListener('input', filtrarProductos);
    if (filterCategory) filterCategory.addEventListener('change', filtrarProductos);
});
