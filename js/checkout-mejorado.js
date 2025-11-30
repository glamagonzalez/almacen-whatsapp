/**
 * CHECKOUT MEJORADO v2.0 - JavaScript
 * Sistema completo de checkout con pasos, validación y Mercado Pago
 */

let checkoutData = {
    carrito: [],
    cliente: {},
    envio: { tipo: null, costo: 0 },
    cupon: null,
    totales: { subtotal: 0, descuento: 0, envio: 0, total: 0 }
};

// Cargar datos al iniciar
document.addEventListener('DOMContentLoaded', function() {
    cargarDatosCheckout();
    mostrarResumenProductos();
    calcularTotales();
});

// Cargar datos del localStorage
function cargarDatosCheckout() {
    const datosGuardados = localStorage.getItem('checkoutData');
    if (datosGuardados) {
        const datos = JSON.parse(datosGuardados);
        checkoutData.carrito = datos.carrito || [];
        checkoutData.cupon = datos.cupon || null;
        
        if (checkoutData.carrito.length === 0) {
            mostrarError('Tu carrito está vacío');
            setTimeout(() => window.location.href = 'catalogo.php', 2000);
        }
    } else {
        mostrarError('No hay datos de compra');
        setTimeout(() => window.location.href = 'catalogo.php', 2000);
    }
}

// Mostrar productos en el resumen
function mostrarResumenProductos() {
    const container = document.getElementById('resumenProductos');
    if (!container) return;

    let html = '';
    checkoutData.carrito.forEach(item => {
        const subtotal = item.precio * item.cantidad;
        html += `
            <div class="product-mini">
                <img src="${item.imagen || 'https://via.placeholder.com/60'}" alt="${item.nombre}">
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between">
                        <strong>${item.nombre}</strong>
                        <span class="text-success">$${subtotal.toFixed(2)}</span>
                    </div>
                    <small class="text-muted">$${item.precio.toFixed(2)} x ${item.cantidad}</small>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

// Calcular totales
function calcularTotales() {
    // Subtotal
    checkoutData.totales.subtotal = checkoutData.carrito.reduce((total, item) => 
        total + (item.precio * item.cantidad), 0
    );

    // Descuento
    if (checkoutData.cupon) {
        switch(checkoutData.cupon.tipo) {
            case 'porcentaje':
                checkoutData.totales.descuento = checkoutData.totales.subtotal * 
                    (checkoutData.cupon.valor / 100);
                break;
            case 'fijo':
                checkoutData.totales.descuento = checkoutData.cupon.valor;
                break;
            case 'envio':
                checkoutData.totales.descuento = 0;
                break;
        }
    } else {
        checkoutData.totales.descuento = 0;
    }

    // Envío (se aplica envío gratis si hay cupón de envío o si el subtotal >= 5000)
    if (checkoutData.cupon && checkoutData.cupon.tipo === 'envio') {
        checkoutData.totales.envio = 0;
    } else if (checkoutData.totales.subtotal >= 5000) {
        checkoutData.totales.envio = 0;
    } else {
        checkoutData.totales.envio = checkoutData.envio.costo;
    }

    // Total
    checkoutData.totales.total = checkoutData.totales.subtotal - 
        checkoutData.totales.descuento + checkoutData.totales.envio;

    mostrarTotales();
}

// Mostrar totales en el resumen
function mostrarTotales() {
    const container = document.getElementById('resumenTotales');
    if (!container) return;

    let html = `
        <div class="summary-line">
            <span>Subtotal:</span>
            <strong>$${checkoutData.totales.subtotal.toFixed(2)}</strong>
        </div>
    `;

    if (checkoutData.totales.descuento > 0) {
        html += `
            <div class="summary-line text-success">
                <span><i class="fas fa-tag"></i> Descuento (${checkoutData.cupon.codigo}):</span>
                <strong>-$${checkoutData.totales.descuento.toFixed(2)}</strong>
            </div>
        `;
    }

    html += `
        <div class="summary-line">
            <span>Envío:</span>
            <strong>${checkoutData.totales.envio === 0 ? 
                '<span class="text-success">GRATIS 🎉</span>' : 
                '$' + checkoutData.totales.envio.toFixed(2)
            }</strong>
        </div>
    `;

    if (checkoutData.totales.subtotal < 5000 && checkoutData.totales.envio > 0) {
        const faltante = 5000 - checkoutData.totales.subtotal;
        html += `
            <small class="text-muted d-block mb-2">
                <i class="fas fa-info-circle"></i> 
                Falta $${faltante.toFixed(2)} para envío gratis
            </small>
        `;
    }

    html += `
        <div class="summary-line total text-success">
            <span>TOTAL:</span>
            <span>$${checkoutData.totales.total.toFixed(2)}</span>
        </div>
    `;

    container.innerHTML = html;
}

// Validar formulario de datos
function validarDatos() {
    const form = document.getElementById('formCheckout');
    if (!form.checkValidity()) {
        form.reportValidity();
        return false;
    }

    // Guardar datos del cliente
    checkoutData.cliente = {
        nombre: document.getElementById('nombre').value,
        telefono: document.getElementById('telefono').value,
        email: document.getElementById('email').value,
        direccion: document.getElementById('direccion').value,
        codigo_postal: document.getElementById('codigo_postal').value,
        ciudad: document.getElementById('ciudad').value,
        provincia: document.getElementById('provincia').value,
        aclaraciones: document.getElementById('aclaraciones').value
    };

    return true;
}

// Calcular envío desde código postal
function calcularEnvioDesdeCP() {
    const cp = document.getElementById('codigo_postal').value;
    if (!cp || cp.length < 4) return;

    const primerDigito = parseInt(cp.charAt(0));
    const esInterior = primerDigito >= 5;
    
    const costoBase = 500;
    const costoInterior = 700;
    
    const nuevoCosto = esInterior ? costoInterior : costoBase;
    
    document.getElementById('costoEstandar').textContent = '$' + nuevoCosto;
    
    if (checkoutData.envio.tipo === 'estandar') {
        checkoutData.envio.costo = nuevoCosto;
        calcularTotales();
    }
}

// Continuar al paso de envío
function continuarAEnvio() {
    if (!validarDatos()) return;

    // Cambiar pasos
    document.getElementById('step2').classList.remove('active');
    document.getElementById('step2').classList.add('completed');
    document.getElementById('step3').classList.add('active');

    // Mostrar sección de envío
    document.getElementById('seccionDatos').classList.add('d-none');
    document.getElementById('seccionEnvio').classList.remove('d-none');

    // Scroll to top
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// Volver a datos
function volverADatos() {
    document.getElementById('step3').classList.remove('active');
    document.getElementById('step2').classList.add('active');
    document.getElementById('step2').classList.remove('completed');

    document.getElementById('seccionEnvio').classList.add('d-none');
    document.getElementById('seccionDatos').classList.remove('d-none');

    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// Seleccionar método de envío
function seleccionarEnvio(tipo, costo) {
    // Remover selección anterior
    document.querySelectorAll('.shipping-option').forEach(opt => {
        opt.classList.remove('selected');
    });

    // Seleccionar nuevo
    const opcionSeleccionada = event.currentTarget;
    opcionSeleccionada.classList.add('selected');
    opcionSeleccionada.querySelector('input[type="radio"]').checked = true;

    // Guardar datos
    checkoutData.envio = { tipo: tipo, costo: costo };
    
    // Si subtotal >= 5000, envío gratis
    if (checkoutData.totales.subtotal >= 5000) {
        checkoutData.envio.costo = 0;
    }

    calcularTotales();
}

// Continuar al pago
function continuarAPago() {
    if (!checkoutData.envio.tipo) {
        mostrarError('Selecciona un método de envío');
        return;
    }

    // Cambiar pasos
    document.getElementById('step3').classList.remove('active');
    document.getElementById('step3').classList.add('completed');
    document.getElementById('step4').classList.add('active');

    // Mostrar sección de pago
    document.getElementById('seccionEnvio').classList.add('d-none');
    document.getElementById('seccionPago').classList.remove('d-none');

    // Habilitar botón móvil
    const btnMobile = document.getElementById('btnPagarMobile');
    if (btnMobile) btnMobile.disabled = false;

    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// Volver a envío
function volverAEnvio() {
    document.getElementById('step4').classList.remove('active');
    document.getElementById('step3').classList.add('active');
    document.getElementById('step3').classList.remove('completed');

    document.getElementById('seccionPago').classList.add('d-none');
    document.getElementById('seccionEnvio').classList.remove('d-none');

    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// Procesar pago con Mercado Pago
async function procesarPago() {
    if (!validarDatos() || !checkoutData.envio.tipo) {
        mostrarError('Completa todos los pasos');
        return;
    }

    // Mostrar loading
    const btn = event.target;
    const textoOriginal = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Procesando...';

    try {
        // Preparar datos para enviar al servidor
        const datosVenta = {
            cliente: checkoutData.cliente,
            productos: checkoutData.carrito,
            envio: checkoutData.envio,
            cupon: checkoutData.cupon,
            totales: checkoutData.totales
        };

        // Enviar a procesar_pago.php
        const response = await fetch('api/procesar_pago.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(datosVenta)
        });

        const result = await response.json();

        if (result.success && result.init_point) {
            // Redirigir a Mercado Pago
            window.location.href = result.init_point;
        } else {
            throw new Error(result.error || 'Error al procesar el pago');
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarError('Error al procesar el pago: ' + error.message);
        btn.disabled = false;
        btn.innerHTML = textoOriginal;
    }
}

// Mostrar error
function mostrarError(mensaje) {
    const toast = document.createElement('div');
    toast.className = 'toast align-items-center text-white bg-danger border-0 position-fixed top-0 end-0 m-3';
    toast.style.zIndex = '9999';
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                <i class="fas fa-exclamation-circle"></i> ${mensaje}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    
    document.body.appendChild(toast);
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
    
    toast.addEventListener('hidden.bs.toast', () => toast.remove());
}
