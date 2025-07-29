<?php 
// User data and permissions are now passed from the controller
// $user and $isOperador variables are available from FormularioController
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
                        <p class="text-muted">Gestión de formularios del sistema</p>
                    </div>
                    <a href="/formularios/crear" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>
                        Crear Formulario
                    </a>
                </div>

                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fas fa-check-circle me-2"></i>
                        Formulario guardado correctamente.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-list me-2"></i>
                            Lista de Formularios
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($formularios)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Título</th>
                                        <th>Tipo</th>
                                        <th>Creador</th>
                                        <th>Respuestas</th>
                                        <th>Estado</th>
                                        <th>Fecha</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($formularios as $form): ?>
                                    <tr>
                                        <td>#<?= $form['id'] ?></td>
                                        <td>
                                            <strong><?= htmlspecialchars($form['titulo']) ?></strong>
                                            <?php if ($form['descripcion']): ?>
                                            <br><small class="text-muted"><?= htmlspecialchars(substr($form['descripcion'], 0, 50)) ?>...</small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= 
                                                $form['tipo'] === 'reservacion' ? 'info' : 
                                                ($form['tipo'] === 'compra' ? 'success' : 'warning')
                                            ?>">
                                                <?= ucfirst($form['tipo']) ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($form['creador_nombre']) ?></td>
                                        <td>
                                            <span class="badge bg-secondary"><?= $form['total_respuestas'] ?></span>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= $form['activo'] ? 'success' : 'danger' ?>">
                                                <?= $form['activo'] ? 'Activo' : 'Inactivo' ?>
                                            </span>
                                        </td>
                                        <td><?= date('d/m/Y', strtotime($form['fecha_creacion'])) ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="/formularios/editar?id=<?= $form['id'] ?>" 
                                                   class="btn btn-outline-primary" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="/formularios/responder?id=<?= $form['id'] ?>" 
                                                   class="btn btn-outline-success" title="Responder">
                                                    <i class="fas fa-reply"></i>
                                                </a>
                                                <?php if ($form['total_respuestas'] > 0): ?>
                                                <a href="/respuestas?formulario_id=<?= $form['id'] ?>" 
                                                   class="btn btn-outline-info" title="Ver Respuestas">
                                                    <i class="fas fa-eye"></i>
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
                            <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No hay formularios creados</h5>
                            <p class="text-muted">Comienza creando tu primer formulario personalizado.</p>
                            <a href="/formularios/crear" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>
                                Crear Primer Formulario
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