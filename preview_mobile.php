<?php
/**
 * PREVIEW_MOBILE.PHP - Vista previa del catálogo en móvil
 */
require_once 'config/database.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vista Previa Móvil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: #f0f0f0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .preview-container {
            display: flex;
            gap: 30px;
            max-width: 1400px;
            flex-wrap: wrap;
            justify-content: center;
        }
        .device {
            background: #1a1a1a;
            border-radius: 40px;
            padding: 15px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.5);
            position: relative;
        }
        .device::before {
            content: '';
            position: absolute;
            top: 8px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 6px;
            background: #333;
            border-radius: 3px;
        }
        .device::after {
            content: '';
            position: absolute;
            bottom: 8px;
            left: 50%;
            transform: translateX(-50%);
            width: 50px;
            height: 50px;
            border: 2px solid #333;
            border-radius: 50%;
        }
        .device-screen {
            background: white;
            border-radius: 30px;
            overflow: hidden;
            width: 375px;
            height: 667px;
            position: relative;
        }
        .device-screen iframe {
            width: 100%;
            height: 100%;
            border: none;
        }
        .device-label {
            text-align: center;
            margin-top: 15px;
            color: white;
            font-weight: bold;
        }
        .controls {
            position: fixed;
            top: 20px;
            right: 20px;
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
            z-index: 1000;
        }
        .qr-container {
            text-align: center;
            margin-top: 20px;
        }
        #qrcode {
            display: inline-block;
            padding: 10px;
            background: white;
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <div class="controls">
        <h5><i class="fas fa-mobile-alt"></i> Vista Previa Móvil</h5>
        <hr>
        
        <div class="mb-3">
            <label class="form-label"><strong>Ver página:</strong></label>
            <select class="form-select" id="page-select" onchange="cambiarPagina()">
                <option value="catalogo.php">📱 Catálogo (Cliente)</option>
                <option value="checkout.php">💳 Checkout</option>
                <option value="productos.php">⚙️ Admin - Productos</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label"><strong>Dispositivo:</strong></label>
            <select class="form-select" id="device-select" onchange="cambiarDispositivo()">
                <option value="iphone">iPhone 13 (375x667)</option>
                <option value="android">Android (360x640)</option>
                <option value="tablet">Tablet (768x1024)</option>
            </select>
        </div>

        <button class="btn btn-primary w-100 mb-2" onclick="recargarPreview()">
            <i class="fas fa-sync"></i> Recargar
        </button>

        <button class="btn btn-success w-100 mb-2" onclick="abrirEnNuevaPestaña()">
            <i class="fas fa-external-link-alt"></i> Abrir en Nueva Pestaña
        </button>

        <button class="btn btn-info w-100 mb-2" onclick="mostrarQR()">
            <i class="fas fa-qrcode"></i> Generar QR
        </button>

        <hr>

        <div class="qr-container" id="qr-container" style="display: none;">
            <p><small>Escanea con tu celular:</small></p>
            <div id="qrcode"></div>
            <small class="text-muted">O ingresa a:<br><strong id="url-text"></strong></small>
        </div>

        <hr>
        
        <a href="index.php" class="btn btn-secondary w-100">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>

    <div class="preview-container">
        <div class="device" id="device-frame">
            <div class="device-screen">
                <iframe id="preview-frame" src="catalogo.php"></iframe>
            </div>
            <div class="device-label">Vista Móvil</div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        function cambiarPagina() {
            const select = document.getElementById('page-select');
            const iframe = document.getElementById('preview-frame');
            iframe.src = select.value;
        }

        function cambiarDispositivo() {
            const select = document.getElementById('device-select');
            const screen = document.querySelector('.device-screen');
            
            switch(select.value) {
                case 'iphone':
                    screen.style.width = '375px';
                    screen.style.height = '667px';
                    break;
                case 'android':
                    screen.style.width = '360px';
                    screen.style.height = '640px';
                    break;
                case 'tablet':
                    screen.style.width = '768px';
                    screen.style.height = '1024px';
                    break;
            }
        }

        function recargarPreview() {
            const iframe = document.getElementById('preview-frame');
            iframe.src = iframe.src;
        }

        function abrirEnNuevaPestaña() {
            const iframe = document.getElementById('preview-frame');
            window.open(iframe.src, '_blank');
        }

        function mostrarQR() {
            const container = document.getElementById('qr-container');
            const qrcodeDiv = document.getElementById('qrcode');
            const urlText = document.getElementById('url-text');
            
            if (container.style.display === 'none') {
                // Obtener la URL actual
                const iframe = document.getElementById('preview-frame');
                const url = window.location.origin + '/' + iframe.src.split('/').pop();
                
                // Limpiar QR anterior
                qrcodeDiv.innerHTML = '';
                
                // Generar nuevo QR
                new QRCode(qrcodeDiv, {
                    text: url,
                    width: 150,
                    height: 150
                });
                
                urlText.textContent = url;
                container.style.display = 'block';
            } else {
                container.style.display = 'none';
            }
        }

        // Atajos de teclado
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'r') {
                e.preventDefault();
                recargarPreview();
            }
        });
    </script>
</body>
</html>
