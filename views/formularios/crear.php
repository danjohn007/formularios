<?php 
// Get current user info
$user = (new AuthController())->getCurrentUser();
$isOperador = (new AuthController())->isOperador();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> - Sistema de Formularios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }
        .sidebar {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            padding: 1rem;
            margin-bottom: 1rem;
        }
        .nav-link {
            color: #495057;
            border-radius: 10px;
            margin: 0.25rem 0;
        }
        .nav-link:hover, .nav-link.active {
            background-color: #667eea;
            color: white;
        }
        .field-builder {
            border: 2px dashed #dee2e6;
            border-radius: 10px;
            padding: 2rem;
            text-align: center;
            margin: 1rem 0;
        }
        .field-preview {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 1rem;
            margin: 0.5rem 0;
            background: #f8f9fa;
        }
        .field-controls {
            display: flex;
            gap: 0.5rem;
            justify-content: flex-end;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="/dashboard">
                <i class="fas fa-clipboard-list me-2"></i>
                Sistema de Formularios
            </a>
            
            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user me-1"></i>
                        <?= htmlspecialchars($user['nombre']) ?>
                        <span class="badge bg-light text-dark ms-1"><?= ucfirst($user['rol']) ?></span>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="/logout">
                            <i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión
                        </a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2">
                <div class="sidebar">
                    <ul class="nav nav-pills flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="/dashboard">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a>
                        </li>
                        <?php if ($isOperador): ?>
                        <li class="nav-item">
                            <a class="nav-link active" href="/formularios">
                                <i class="fas fa-file-alt me-2"></i>Formularios
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/respuestas">
                                <i class="fas fa-inbox me-2"></i>Respuestas
                            </a>
                        </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/formularios/responder">
                                <i class="fas fa-plus-circle me-2"></i>Nueva Solicitud
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2><?= $title ?></h2>
                        <p class="text-muted">Crear un nuevo formulario personalizado</p>
                    </div>
                    <a href="/formularios" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>
                        Volver
                    </a>
                </div>

                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Error al crear el formulario. Inténtalo de nuevo.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" action="/formularios/guardar" id="formCreator">
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="card">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0">
                                        <i class="fas fa-cog me-2"></i>
                                        Configuración
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="titulo" class="form-label">Título del Formulario *</label>
                                        <input type="text" class="form-control" id="titulo" name="titulo" required 
                                               placeholder="Ej: Reserva de Salón de Eventos">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="descripcion" class="form-label">Descripción</label>
                                        <textarea class="form-control" id="descripcion" name="descripcion" rows="3"
                                                  placeholder="Breve descripción del formulario..."></textarea>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="tipo" class="form-label">Tipo de Formulario *</label>
                                        <select class="form-select" id="tipo" name="tipo" required>
                                            <option value="">Seleccionar tipo...</option>
                                            <option value="reservacion">Reservación</option>
                                            <option value="compra">Compra de Productos</option>
                                            <option value="servicio">Solicitud de Servicio</option>
                                        </select>
                                    </div>
                                    
                                    <hr>
                                    
                                    <h6>Agregar Campos</h6>
                                    <div class="d-grid gap-2">
                                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="addField('text')">
                                            <i class="fas fa-font me-1"></i>Texto
                                        </button>
                                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="addField('email')">
                                            <i class="fas fa-envelope me-1"></i>Email
                                        </button>
                                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="addField('tel')">
                                            <i class="fas fa-phone me-1"></i>Teléfono
                                        </button>
                                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="addField('date')">
                                            <i class="fas fa-calendar me-1"></i>Fecha
                                        </button>
                                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="addField('time')">
                                            <i class="fas fa-clock me-1"></i>Hora
                                        </button>
                                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="addField('number')">
                                            <i class="fas fa-hashtag me-1"></i>Número
                                        </button>
                                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="addField('select')">
                                            <i class="fas fa-list me-1"></i>Lista
                                        </button>
                                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="addField('textarea')">
                                            <i class="fas fa-align-left me-1"></i>Texto Largo
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-8">
                            <div class="card">
                                <div class="card-header bg-success text-white">
                                    <h5 class="mb-0">
                                        <i class="fas fa-eye me-2"></i>
                                        Vista Previa del Formulario
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div id="formPreview">
                                        <div class="field-builder">
                                            <i class="fas fa-plus-circle fa-2x text-muted mb-2"></i>
                                            <h5 class="text-muted">Agrega campos a tu formulario</h5>
                                            <p class="text-muted">Usa los botones de la izquierda para agregar diferentes tipos de campos</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <div class="d-flex justify-content-between">
                                        <button type="button" class="btn btn-outline-danger" onclick="clearForm()">
                                            <i class="fas fa-trash me-2"></i>
                                            Limpiar Todo
                                        </button>
                                        <button type="submit" class="btn btn-success">
                                            <i class="fas fa-save me-2"></i>
                                            Guardar Formulario
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let fieldCounter = 0;
        let formFields = [];

        function addField(type) {
            fieldCounter++;
            const fieldId = `field_${fieldCounter}`;
            
            const field = {
                id: fieldId,
                type: type,
                label: getDefaultLabel(type),
                required: false,
                options: type === 'select' ? ['Opción 1', 'Opción 2'] : null
            };
            
            formFields.push(field);
            renderPreview();
        }

        function getDefaultLabel(type) {
            const labels = {
                'text': 'Campo de Texto',
                'email': 'Correo Electrónico',
                'tel': 'Teléfono',
                'date': 'Fecha',
                'time': 'Hora',
                'number': 'Número',
                'select': 'Lista de Opciones',
                'textarea': 'Texto Largo'
            };
            return labels[type] || 'Campo';
        }

        function renderPreview() {
            const preview = document.getElementById('formPreview');
            
            if (formFields.length === 0) {
                preview.innerHTML = `
                    <div class="field-builder">
                        <i class="fas fa-plus-circle fa-2x text-muted mb-2"></i>
                        <h5 class="text-muted">Agrega campos a tu formulario</h5>
                        <p class="text-muted">Usa los botones de la izquierda para agregar diferentes tipos de campos</p>
                    </div>
                `;
                return;
            }

            let html = '';
            formFields.forEach((field, index) => {
                html += `
                    <div class="field-preview" data-field-id="${field.id}">
                        <div class="field-controls">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="editField(${index})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeField(${index})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                        <label class="form-label">${field.label} ${field.required ? '*' : ''}</label>
                        ${getFieldHTML(field)}
                        <input type="hidden" name="configuracion[campos][${index}][tipo]" value="${field.type}">
                        <input type="hidden" name="configuracion[campos][${index}][etiqueta]" value="${field.label}">
                        <input type="hidden" name="configuracion[campos][${index}][requerido]" value="${field.required ? 1 : 0}">
                        ${field.options ? `<input type="hidden" name="configuracion[campos][${index}][opciones]" value="${JSON.stringify(field.options)}">` : ''}
                    </div>
                `;
            });
            
            preview.innerHTML = html;
        }

        function getFieldHTML(field) {
            switch (field.type) {
                case 'select':
                    let options = field.options.map(opt => `<option>${opt}</option>`).join('');
                    return `<select class="form-select" disabled>${options}</select>`;
                case 'textarea':
                    return `<textarea class="form-control" rows="3" disabled placeholder="Escribe aquí..."></textarea>`;
                case 'date':
                    return `<input type="date" class="form-control" disabled>`;
                case 'time':
                    return `<input type="time" class="form-control" disabled>`;
                case 'number':
                    return `<input type="number" class="form-control" disabled placeholder="0">`;
                case 'email':
                    return `<input type="email" class="form-control" disabled placeholder="usuario@ejemplo.com">`;
                case 'tel':
                    return `<input type="tel" class="form-control" disabled placeholder="+1 (555) 123-4567">`;
                default:
                    return `<input type="text" class="form-control" disabled placeholder="Escribe aquí...">`;
            }
        }

        function removeField(index) {
            formFields.splice(index, 1);
            renderPreview();
        }

        function editField(index) {
            const field = formFields[index];
            const newLabel = prompt('Etiqueta del campo:', field.label);
            if (newLabel !== null && newLabel.trim() !== '') {
                formFields[index].label = newLabel.trim();
                renderPreview();
            }
        }

        function clearForm() {
            if (confirm('¿Estás seguro de que quieres limpiar todos los campos?')) {
                formFields = [];
                fieldCounter = 0;
                renderPreview();
            }
        }

        // Validación del formulario
        document.getElementById('formCreator').addEventListener('submit', function(e) {
            if (formFields.length === 0) {
                e.preventDefault();
                alert('Debes agregar al menos un campo al formulario.');
                return;
            }
        });
    </script>
</body>
</html>