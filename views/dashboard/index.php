<?php 
// User data and permissions are now passed from the controller
// $user and $isOperador variables are available from DashboardController
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> - Sistema de Formularios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1rem;
        }
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
        }
        .chart-container {
            position: relative;
            height: 300px;
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
                            <a class="nav-link active" href="/dashboard">
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
                <!-- Welcome Header -->
                <div class="row mb-4">
                    <div class="col">
                        <h2>Bienvenido, <?= htmlspecialchars($user['nombre']) ?></h2>
                        <p class="text-muted">Panel de control del sistema de formularios</p>
                    </div>
                </div>

                <?php if ($isOperador): ?>
                <!-- Admin/Operator Dashboard -->
                <div class="row">
                    <!-- Statistics Cards -->
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="stat-card">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="stat-number"><?= $stats['total_formularios'] ?? 0 ?></div>
                                    <div>Formularios</div>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-file-alt fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="stat-card">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="stat-number"><?= $stats['total_respuestas'] ?? 0 ?></div>
                                    <div>Respuestas</div>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-inbox fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="stat-card">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="stat-number">$<?= number_format($stats['ingresos_totales'] ?? 0, 2) ?></div>
                                    <div>Ingresos</div>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-dollar-sign fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="stat-card">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="stat-number">
                                        <?php 
                                        $pendientes = 0;
                                        if (isset($stats['respuestas_por_estatus'])) {
                                            foreach ($stats['respuestas_por_estatus'] as $estatus) {
                                                if ($estatus['estatus'] === 'pendiente') {
                                                    $pendientes = $estatus['total'];
                                                    break;
                                                }
                                            }
                                        }
                                        echo $pendientes;
                                        ?>
                                    </div>
                                    <div>Pendientes</div>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-clock fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="row">
                    <div class="col-lg-6 mb-4">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-chart-pie me-2"></i>
                                    Respuestas por Estatus
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="statusChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-6 mb-4">
                        <div class="card">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-chart-line me-2"></i>
                                    Respuestas por Mes
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="monthlyChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Responses -->
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-list me-2"></i>
                            Respuestas Recientes
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($stats['respuestas_recientes'])): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Formulario</th>
                                        <th>Usuario</th>
                                        <th>Estatus</th>
                                        <th>Total</th>
                                        <th>Fecha</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($stats['respuestas_recientes'] as $respuesta): ?>
                                    <tr>
                                        <td>#<?= $respuesta['id'] ?></td>
                                        <td><?= htmlspecialchars($respuesta['formulario_titulo']) ?></td>
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
                                        <td><?= date('d/m/Y H:i', strtotime($respuesta['fecha_creacion'])) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <p class="text-muted text-center py-4">No hay respuestas registradas aún.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <?php else: ?>
                <!-- Client Dashboard -->
                <div class="row">
                    <div class="col-lg-6 mb-4">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-user me-2"></i>
                                    Mis Solicitudes
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="stat-card">
                                    <div class="text-center">
                                        <div class="stat-number"><?= $stats['mis_respuestas'] ?? 0 ?></div>
                                        <div>Total de Solicitudes</div>
                                    </div>
                                </div>
                                <a href="/formularios/responder" class="btn btn-primary w-100">
                                    <i class="fas fa-plus me-2"></i>
                                    Nueva Solicitud
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-6 mb-4">
                        <div class="card">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-history me-2"></i>
                                    Historial Reciente
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($stats['mis_respuestas_recientes'])): ?>
                                <div class="list-group list-group-flush">
                                    <?php foreach ($stats['mis_respuestas_recientes'] as $respuesta): ?>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1"><?= htmlspecialchars($respuesta['formulario_titulo']) ?></h6>
                                            <small class="text-muted"><?= date('d/m/Y H:i', strtotime($respuesta['fecha_creacion'])) ?></small>
                                        </div>
                                        <span class="badge bg-<?= 
                                            $respuesta['estatus'] === 'completado' ? 'success' : 
                                            ($respuesta['estatus'] === 'pendiente' ? 'warning' : 
                                            ($respuesta['estatus'] === 'en_proceso' ? 'info' : 'danger'))
                                        ?>">
                                            <?= ucfirst(str_replace('_', ' ', $respuesta['estatus'])) ?>
                                        </span>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php else: ?>
                                <p class="text-muted text-center py-4">No tienes solicitudes registradas.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <?php if ($isOperador): ?>
    <script>
        // Status Chart
        <?php if (!empty($stats['respuestas_por_estatus'])): ?>
        const statusData = <?= json_encode($stats['respuestas_por_estatus']) ?>;
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: statusData.map(item => item.estatus.charAt(0).toUpperCase() + item.estatus.slice(1).replace('_', ' ')),
                datasets: [{
                    data: statusData.map(item => item.total),
                    backgroundColor: ['#ff6384', '#36a2eb', '#ffce56', '#4bc0c0']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
        <?php endif; ?>

        // Monthly Chart
        <?php if (!empty($stats['respuestas_por_mes'])): ?>
        const monthlyData = <?= json_encode($stats['respuestas_por_mes']) ?>;
        const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
        new Chart(monthlyCtx, {
            type: 'line',
            data: {
                labels: monthlyData.map(item => item.mes),
                datasets: [{
                    label: 'Respuestas',
                    data: monthlyData.map(item => item.total),
                    borderColor: '#36a2eb',
                    backgroundColor: 'rgba(54, 162, 235, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
        <?php endif; ?>
    </script>
    <?php endif; ?>
</body>
</html>