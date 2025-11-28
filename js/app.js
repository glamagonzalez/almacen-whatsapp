/**
 * APP.JS - JavaScript principal de la aplicación
 * 
 * Maneja la lógica del frontend:
 * - Configuración de Dropzone
 * - Actualización de estadísticas
 * - Carga de lista de archivos
 */

// Configurar Dropzone
Dropzone.options.myDropzone = {
    paramName: "file", // Nombre del parámetro que se envía al servidor
    maxFilesize: 50, // MB
    acceptedFiles: "image/*,application/pdf,.doc,.docx,.xls,.xlsx,.txt,.zip,.rar",
    addRemoveLinks: true,
    dictDefaultMessage: "Arrastra archivos aquí o haz clic",
    dictRemoveFile: "Eliminar",
    dictCancelUpload: "Cancelar",
    dictInvalidFileType: "Tipo de archivo no permitido",
    dictFileTooBig: "Archivo muy grande ({{filesize}}MB). Máximo: {{maxFilesize}}MB",
    
    // Cuando se agrega un archivo
    init: function() {
        this.on("success", function(file, response) {
            console.log("Archivo subido:", response);
            
            // Mostrar notificación de éxito
            showNotification('success', '✅ Archivo subido correctamente');
            
            // Actualizar estadísticas y lista
            updateStats();
            loadFilesList();
        });
        
        this.on("error", function(file, errorMessage) {
            console.error("Error:", errorMessage);
            showNotification('error', '❌ Error al subir archivo');
        });
        
        this.on("complete", function(file) {
            // Remover el archivo de la vista después de subirlo
            setTimeout(() => {
                this.removeFile(file);
            }, 2000);
        });
    }
};

/**
 * Cargar lista de archivos desde el servidor
 */
function loadFilesList() {
    fetch('api/get_files.php')
        .then(response => response.json())
        .then(data => {
            const filesList = document.getElementById('filesList');
            
            if (data.success && data.files.length > 0) {
                filesList.innerHTML = '';
                
                data.files.forEach(file => {
                    const fileItem = createFileItem(file);
                    filesList.appendChild(fileItem);
                });
            } else {
                filesList.innerHTML = '<p class="text-muted text-center">No hay archivos subidos aún</p>';
            }
        })
        .catch(error => {
            console.error('Error al cargar archivos:', error);
        });
}

/**
 * Crear elemento HTML para cada archivo
 */
function createFileItem(file) {
    const div = document.createElement('div');
    div.className = 'file-item';
    
    // Determinar icono según tipo de archivo
    const icon = getFileIcon(file.tipo);
    
    div.innerHTML = `
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <i class="${icon} fa-2x me-3"></i>
                <strong>${file.nombre_original}</strong>
                <br>
                <small class="text-muted">
                    Tamaño: ${formatFileSize(file.tamanio)} | 
                    Subido: ${formatDate(file.fecha_subida)}
                </small>
            </div>
            <div>
                <button class="btn btn-sm btn-primary me-2" onclick="downloadFile(${file.id})">
                    <i class="fas fa-download"></i> Descargar
                </button>
                <button class="btn btn-sm btn-success me-2" onclick="sendWhatsApp(${file.id})">
                    <i class="fab fa-whatsapp"></i> WhatsApp
                </button>
                <button class="btn btn-sm btn-danger" onclick="deleteFile(${file.id})">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    `;
    
    return div;
}

/**
 * Actualizar estadísticas
 */
function updateStats() {
    fetch('api/get_stats.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('totalFiles').textContent = data.stats.total;
                document.getElementById('totalImages').textContent = data.stats.images;
                document.getElementById('totalDocs').textContent = data.stats.documents;
            }
        })
        .catch(error => {
            console.error('Error al cargar estadísticas:', error);
        });
}

/**
 * Obtener icono según tipo de archivo
 */
function getFileIcon(type) {
    if (type.includes('image')) return 'fas fa-image text-success';
    if (type.includes('pdf')) return 'fas fa-file-pdf text-danger';
    if (type.includes('word')) return 'fas fa-file-word text-primary';
    if (type.includes('excel')) return 'fas fa-file-excel text-success';
    if (type.includes('zip') || type.includes('rar')) return 'fas fa-file-archive text-warning';
    return 'fas fa-file text-secondary';
}

/**
 * Formatear tamaño de archivo
 */
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
}

/**
 * Formatear fecha
 */
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

/**
 * Descargar archivo
 */
function downloadFile(fileId) {
    window.open(`api/download.php?id=${fileId}`, '_blank');
}

/**
 * Enviar por WhatsApp
 */
function sendWhatsApp(fileId) {
    // Aquí implementaremos la integración con WhatsApp
    alert('Función de WhatsApp en desarrollo para archivo ID: ' + fileId);
}

/**
 * Eliminar archivo
 */
function deleteFile(fileId) {
    if (confirm('¿Estás segura de eliminar este archivo?')) {
        fetch('api/delete.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id: fileId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('success', '✅ Archivo eliminado');
                loadFilesList();
                updateStats();
            } else {
                showNotification('error', '❌ Error al eliminar');
            }
        });
    }
}

/**
 * Mostrar notificación
 */
function showNotification(type, message) {
    // Crear elemento de notificación
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'success' ? 'success' : 'danger'} position-fixed`;
    notification.style.top = '20px';
    notification.style.right = '20px';
    notification.style.zIndex = '9999';
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    // Remover después de 3 segundos
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Cargar datos al iniciar la página
document.addEventListener('DOMContentLoaded', function() {
    loadFilesList();
    updateStats();
    
    // Actualizar cada 30 segundos
    setInterval(() => {
        loadFilesList();
        updateStats();
    }, 30000);
});
