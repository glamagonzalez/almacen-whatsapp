/// <reference path="c:/Users/maglo/.vscode/extensions/nur.script-0.2.1/@types/api.global.d.ts" />
/// <reference path="c:/Users/maglo/.vscode/extensions/nur.script-0.2.1/@types/vscode.global.d.ts" />
//  @ts-check
//  API: https://code.visualstudio.com/api/references/vscode-api

function activate(_context) {
   // Inicializar Dropzone cuando se active la extensión
   const panel = window.createWebviewPanel(
      'almacenWhatsapp',
      'Almacén WhatsApp - Subir Archivos',
      1, // ViewColumn.One
      {
         enableScripts: true
      }
   );

   panel.webview.html = getWebviewContent();

   // Manejar mensajes desde el webview
   panel.webview.onDidReceiveMessage(
      message => {
         switch (message.command) {
            case 'uploadFile':
               window.showInformationMessage(`Archivo subido: ${message.fileName}`);
               // Aquí puedes agregar la lógica para guardar el archivo
               break;
         }
      },
      undefined,
      _context.subscriptions
   );
}

function getWebviewContent() {
   return `<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Almacén WhatsApp</title>
    <link rel="stylesheet" href="https://unpkg.com/dropzone@5/dist/min/dropzone.min.css" type="text/css" />
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background-color: #1e1e1e;
            color: #cccccc;
        }
        h1 {
            color: #4ec9b0;
            margin-bottom: 20px;
        }
        .dropzone {
            border: 2px dashed #569cd6;
            border-radius: 8px;
            background: #252526;
            padding: 40px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .dropzone:hover {
            border-color: #4ec9b0;
            background: #2d2d30;
        }
        .dropzone .dz-message {
            font-size: 18px;
            color: #cccccc;
        }
        .dropzone .dz-preview {
            margin: 20px;
        }
        .file-list {
            margin-top: 30px;
        }
        .file-item {
            background: #252526;
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
            border-left: 3px solid #4ec9b0;
        }
    </style>
</head>
<body>
    <h1>📦 Almacén WhatsApp - Gestor de Archivos</h1>
    <div id="myDropzone" class="dropzone">
        <div class="dz-message">
            <h3>Arrastra archivos aquí o haz clic para seleccionar</h3>
            <p>Puedes subir imágenes, documentos, PDFs, etc.</p>
        </div>
    </div>
    <div id="fileList" class="file-list"></div>

    <script src="https://unpkg.com/dropzone@5/dist/min/dropzone.min.js"></script>
    <script>
        const vscode = acquireVsCodeApi();
        
        // Configurar Dropzone
        Dropzone.options.myDropzone = {
            url: '#', // No usamos URL real, lo manejamos con mensaje
            autoProcessQueue: false,
            maxFilesize: 50, // MB
            acceptedFiles: 'image/*,application/pdf,.doc,.docx,.xls,.xlsx,.txt',
            addRemoveLinks: true,
            dictDefaultMessage: "Arrastra archivos aquí o haz clic para seleccionar",
            dictRemoveFile: "Eliminar",
            dictCancelUpload: "Cancelar",
            
            init: function() {
                const myDropzone = this;
                
                this.on("addedfile", function(file) {
                    console.log("Archivo agregado:", file.name);
                    
                    // Leer el archivo como base64
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        // Enviar mensaje a la extensión
                        vscode.postMessage({
                            command: 'uploadFile',
                            fileName: file.name,
                            fileSize: file.size,
                            fileType: file.type,
                            fileData: e.target.result
                        });
                        
                        // Agregar a la lista
                        addFileToList(file);
                    };
                    reader.readAsDataURL(file);
                });
                
                this.on("error", function(file, errorMessage) {
                    console.error("Error:", errorMessage);
                });
            }
        };
        
        function addFileToList(file) {
            const fileList = document.getElementById('fileList');
            const fileItem = document.createElement('div');
            fileItem.className = 'file-item';
            fileItem.innerHTML = \`
                <strong>📄 \${file.name}</strong><br>
                <small>Tamaño: \${formatFileSize(file.size)} | Tipo: \${file.type}</small>
            \`;
            fileList.appendChild(fileItem);
        }
        
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
        }
    </script>
</body>
</html>`;
}

function deactivate() {}

module.exports = { activate, deactivate }
