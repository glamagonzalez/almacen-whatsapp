const { default: makeWASocket, useMultiFileAuthState, DisconnectReason, fetchLatestBaileysVersion } = require('@whiskeysockets/baileys');
const express = require('express');
const qrcode = require('qrcode-terminal');
const pino = require('pino');
const fs = require('fs');

const app = express();
app.use(express.json());

let sock = null;
let isConnected = false;
let currentQR = null;

// Configuración de autenticación
async function connectToWhatsApp() {
    let state, saveCreds;
    
    try {
        const authState = await useMultiFileAuthState('./auth_info');
        state = authState.state;
        saveCreds = authState.saveCreds;
    } catch (error) {
        console.error('❌ Error cargando auth:', error.message);
        setTimeout(connectToWhatsApp, 5000);
        return;
    }

    try {
        sock = makeWASocket({
            auth: state,
            printQRInTerminal: false,
            logger: pino({ level: 'silent' }),
            browser: ['AlmacenWhatsApp', 'Chrome', '110.0.0'],
            syncFullHistory: false,
            markOnlineOnConnect: false
        });
        
        console.log('🔌 Socket creado, esperando conexión...');
    } catch (error) {
        console.error('❌ Error creando socket:', error.message);
        setTimeout(connectToWhatsApp, 5000);
        return;
    }

    sock.ev.on('creds.update', saveCreds);

    sock.ev.on('connection.update', (update) => {
        const { connection, lastDisconnect, qr } = update;
        
        if (qr) {
            currentQR = qr;
            console.log('\n📱 QR GENERADO - Ve a: http://localhost:8080/qr\n');
            console.log('🔄 Actualizando página del QR...\n');
        }

        if (connection === 'close') {
            isConnected = false;
            currentQR = null;
            const shouldReconnect = lastDisconnect?.error?.output?.statusCode !== DisconnectReason.loggedOut;
            console.log('❌ Conexión cerrada. Reconectando:', shouldReconnect);
            if (shouldReconnect) {
                setTimeout(connectToWhatsApp, 3000);
            }
        } else if (connection === 'open') {
            isConnected = true;
            currentQR = null;
            console.log('\n✅✅✅ WhatsApp conectado exitosamente! ✅✅✅\n');
        }
    });
}

// API: Mostrar QR en el navegador
app.get('/qr', (req, res) => {
    if (!currentQR) {
        return res.send(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>WhatsApp QR</title>
                <meta http-equiv="refresh" content="2">
                <style>
                    body { font-family: Arial; text-align: center; padding: 50px; background: #128C7E; color: white; }
                    h1 { margin-bottom: 30px; }
                </style>
            </head>
            <body>
                <h1>⏳ Generando QR...</h1>
                <p>Esperando conexión con WhatsApp...</p>
                ${isConnected ? '<h2>✅ YA ESTÁS CONECTADO!</h2>' : ''}
            </body>
            </html>
        `);
    }

    const QRCode = require('qrcode');
    QRCode.toDataURL(currentQR, (err, url) => {
        res.send(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>WhatsApp QR</title>
                <style>
                    body { font-family: Arial; text-align: center; padding: 50px; background: #128C7E; color: white; }
                    img { border: 10px solid white; border-radius: 20px; box-shadow: 0 0 30px rgba(0,0,0,0.3); }
                    h1 { margin-bottom: 20px; }
                    .instructions { background: white; color: #128C7E; padding: 20px; border-radius: 10px; margin: 30px auto; max-width: 500px; }
                </style>
            </head>
            <body>
                <h1>📱 ESCANEA ESTE QR CON WHATSAPP</h1>
                <img src="${url}" alt="QR Code" />
                <div class="instructions">
                    <h2>📋 Instrucciones:</h2>
                    <ol style="text-align: left;">
                        <li>Abre WhatsApp en tu celular</li>
                        <li>Ve a <strong>Configuración</strong></li>
                        <li>Toca <strong>Dispositivos vinculados</strong></li>
                        <li>Toca <strong>Vincular dispositivo</strong></li>
                        <li>Escanea este código QR</li>
                    </ol>
                </div>
            </body>
            </html>
        `);
    });
});

// API: Verificar estado
app.get('/status', (req, res) => {
    res.json({
        success: true,
        connected: isConnected,
        message: isConnected ? 'WhatsApp conectado' : 'WhatsApp desconectado'
    });
});

// API: Enviar mensaje
app.post('/send', async (req, res) => {
    try {
        const { number, message } = req.body;

        if (!isConnected) {
            return res.status(503).json({
                success: false,
                error: 'WhatsApp no está conectado'
            });
        }

        if (!number || !message) {
            return res.status(400).json({
                success: false,
                error: 'Faltan parámetros: number y message son requeridos'
            });
        }

        // Formatear número (agregar código de país si no tiene)
        let formattedNumber = number.replace(/\D/g, '');
        if (!formattedNumber.startsWith('549')) {
            formattedNumber = '549' + formattedNumber;
        }
        formattedNumber += '@s.whatsapp.net';

        // Enviar mensaje
        await sock.sendMessage(formattedNumber, { text: message });

        res.json({
            success: true,
            message: 'Mensaje enviado exitosamente',
            to: formattedNumber
        });
    } catch (error) {
        console.error('Error enviando mensaje:', error);
        res.status(500).json({
            success: false,
            error: error.message
        });
    }
});

// API: Verificar autenticación
app.get('/auth', (req, res) => {
    const apiKey = req.headers['apikey'] || req.headers['authorization'];
    
    if (apiKey === 'mi_clave_secreta_123') {
        res.json({ success: true, authenticated: true });
    } else {
        res.status(401).json({ success: false, error: 'API Key inválida' });
    }
});

// Iniciar servidor
const PORT = 8080;
app.listen(PORT, () => {
    console.log(`\n🚀 WhatsApp API Server corriendo en http://localhost:${PORT}\n`);
    console.log('📋 Endpoints disponibles:');
    console.log(`   GET  http://localhost:${PORT}/status`);
    console.log(`   POST http://localhost:${PORT}/send`);
    console.log(`   GET  http://localhost:${PORT}/auth`);
    console.log('\n⏳ Conectando a WhatsApp...\n');
    connectToWhatsApp();
});
