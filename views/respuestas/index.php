<?php 
// User data and permissions are passed from the controller
// $user and $isOperador variables are available from RespuestaController
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
        .card-header {
            border-radius: 15px 15px 0 0 !important;
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
                            <a class="nav-link" href="/formularios">
                                <i class="fas fa-file-alt me-2"></i>Formularios
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="/respuestas">
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
                <!-- Header -->
                <div class="row mb-4">
                    <div class="col-md-8">
                        <h2>Respuestas y Solicitudes</h2>
                        <p class="text-muted">Gestión de respuestas recibidas</p>
                    </div>
                    <div class="col-md-4 text-end">
                        <a href="/formularios/responder" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>
                            Nueva Solicitud
                        </a>
                    </div>
                </div>

                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <label for="estatus" class="form-label">Estatus</label>
                                <select class="form-select" id="estatus" name="estatus">
                                    <option value="">Todos</option>
                                    <option value="pendiente" <?= ($_GET['estatus'] ?? '') === 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                                    <option value="en_proceso" <?= ($_GET['estatus'] ?? '') === 'en_proceso' ? 'selected' : '' ?>>En Proceso</option>
                                    <option value="completado" <?= ($_GET['estatus'] ?? '') === 'completado' ? 'selected' : '' ?>>Completado</option>
                                    <option value="cancelado" <?= ($_GET['estatus'] ?? '') === 'cancelado' ? 'selected' : '' ?>>Cancelado</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="formulario" class="form-label">Formulario</label>
                                <select class="form-select" id="formulario" name="formulario_id">
                                    <option value="">Todos</option>
                                    <?php if (isset($formularios)): ?>
                                        <?php foreach ($formularios as $form): ?>
                                            <option value="<?= $form['id'] ?>" <?= ($_GET['formulario_id'] ?? '') == $form['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($form['titulo']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <div>
                                    <button type="submit" class="btn btn-outline-primary">
                                        <i class="fas fa-search me-1"></i>Filtrar
                                    </button>
                                    <a href="/respuestas" class="btn btn-outline-secondary">
                                        <i class="fas fa-times me-1"></i>Limpiar
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Responses List -->
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-list me-2"></i>
                            Lista de Respuestas
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($respuestas)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Formulario</th>
                                        <th>Usuario</th>
                                        <th>Estatus</th>
                                        <th>Total</th>
                                        <th>Asignado</th>
                                        <th>Fecha</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($respuestas as $respuesta): ?>
                                    <tr>
                                        <td>#<?= $respuesta['id'] ?></td>
                                        <td>
                                            <strong><?= htmlspecialchars($respuesta['formulario_titulo']) ?></strong>
                                            <br>
                                            <small class="text-muted">
                                                <span class="badge bg-secondary"><?= ucfirst($respuesta['formulario_tipo']) ?></span>
                                            </small>
                                        </td>
                                        <td><?= htmlspecialchars($respuesta['usuario_nombre'] ?? 'Anónimo') ?></td>
                                        <td>
                                            <span class="badge bg-<?= 
                                                $respuesta['estatus'] === 'completado' ? 'success' : 
                                                ($respuesta['estatus'] === 'pendiente' ? 'warning' : 
                                                ($respuesta['estatus'] === 'en_proceso' ? 'info' : 'danger'))
                                            ?>">
                                                <?= ucfirst(str_replace('_', ' ', $respuesta['estatus'])) ?>
                                            </span>
                                        </td>
                                        <td>$<?= number_format($respuesta['total'], 2) ?></td>
                                        <td><?= htmlspecialchars($respuesta['asignado_nombre'] ?? 'Sin asignar') ?></td>
                                        <td><?= date('d/m/Y H:i', strtotime($respuesta['fecha_creacion'])) ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="/respuestas/ver?id=<?= $respuesta['id'] ?>" 
                                                   class="btn btn-outline-primary" title="Ver detalles">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <?php if ($isOperador): ?>
                                                <a href="/respuestas/editar?id=<?= $respuesta['id'] ?>" 
                                                   class="btn btn-outline-warning" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                            <h5 class="text-muted">No hay respuestas disponibles</h5>
                            <p class="text-muted">Las respuestas aparecerán aquí cuando se envíen formularios.</p>
                            <a href="/formularios/responder" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>
                                Crear Nueva Solicitud
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>