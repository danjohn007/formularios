<?php
/**
 * Instalador Web para Sistema de Formularios
 * Configuración automática de base de datos y usuarios demo
 */

// Check if already installed
$config_file = __DIR__ . '/config/database.php';
$installation_complete = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db_host = $_POST['db_host'] ?? 'localhost';
    $db_name = $_POST['db_name'] ?? 'formularios_db';
    $db_user = $_POST['db_user'] ?? 'root';
    $db_pass = $_POST['db_pass'] ?? '';
    
    $errors = [];
    $success = false;
    
    try {
        // Include the database class
        require_once $config_file;
        
        // Initialize database (will auto-fallback to SQLite if MySQL unavailable)
        $database = new Database();
        $conn = $database->getConnection();
        
        if ($conn) {
            // Update config file with provided settings (even if using SQLite)
            $config_content = file_get_contents($config_file);
            $config_content = str_replace("'localhost'", "'{$db_host}'", $config_content);
            $config_content = str_replace("'formularios_db'", "'{$db_name}'", $config_content);
            $config_content = str_replace("'root'", "'{$db_user}'", $config_content);
            $config_content = str_replace("'password' => ''", "'password' => '{$db_pass}'", $config_content);
            
            file_put_contents($config_file, $config_content);
            
            // Setup tables
            $tables_created = $database->setupTables();
            if (!$tables_created) {
                $errors[] = 'Error al crear las tablas de la base de datos';
            }
            
            // Insert demo users
            $users_created = $database->insertDemoUsers();
            if (!$users_created) {
                $errors[] = 'Error al insertar usuarios de demostración';
            }
            
            // Insert some demo products (SQLite compatible)
            $products_created = false;
            try {
                $driver = $conn->getAttribute(PDO::ATTR_DRIVER_NAME);
                if ($driver === 'sqlite') {
                    $products_sql = "INSERT OR IGNORE INTO productos (nombre, descripcion, precio, stock) VALUES 
                        ('Reserva de Salón', 'Reserva de salón de eventos por 4 horas', 500.00, 10),
                        ('Servicio de Catering', 'Servicio de catering para eventos', 150.00, 50),
                        ('Decoración Básica', 'Paquete básico de decoración', 200.00, 20),
                        ('Sonido y Audio', 'Equipo de sonido profesional', 300.00, 5)";
                } else {
                    $products_sql = "INSERT IGNORE INTO productos (nombre, descripcion, precio, stock) VALUES 
                        ('Reserva de Salón', 'Reserva de salón de eventos por 4 horas', 500.00, 10),
                        ('Servicio de Catering', 'Servicio de catering para eventos', 150.00, 50),
                        ('Decoración Básica', 'Paquete básico de decoración', 200.00, 20),
                        ('Sonido y Audio', 'Equipo de sonido profesional', 300.00, 5)";
                }
                $products_created = $conn->exec($products_sql) !== false;
                if (!$products_created) {
                    $errors[] = 'Error al insertar productos de demostración';
                }
            } catch (Exception $e) {
                $errors[] = 'Error al insertar productos: ' . $e->getMessage();
            }
            
            // Insert demo forms with fields
            $forms_created = $database->insertDemoForms();
            if (!$forms_created) {
                $errors[] = 'Error al insertar formularios de demostración';
            }
            
            // Insert demo responses
            $responses_created = $database->insertDemoResponses();
            if (!$responses_created) {
                $errors[] = 'Error al insertar respuestas de demostración';
            }
            
            // Only mark as successful if all operations succeeded
            if ($tables_created && $users_created && $products_created && $forms_created && $responses_created) {
                $success = true;
                $installation_complete = true;
                
                // Store which database type was actually used
                $database_type = $conn->getAttribute(PDO::ATTR_DRIVER_NAME);
                if ($database_type === 'sqlite') {
                    $success_message = 'Sistema instalado correctamente usando SQLite (fallback)';
                } else {
                    $success_message = 'Sistema instalado correctamente usando MySQL';
                }
            } else {
                $errors[] = 'La instalación no se completó debido a errores en las operaciones de base de datos';
            }
            
        } else {
            $errors[] = 'No se pudo conectar a la base de datos';
        }
        
    } catch (Exception $e) {
        $errors[] = 'Error: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalador - Sistema de Formularios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 2rem 0;
        }
        .install-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            margin: 1rem;
        }
        .install-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .install-header h1 {
            color: #333;
            margin-bottom: 0.5rem;
        }
        .step {
            margin-bottom: 1.5rem;
            padding: 1rem;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
        }
        .step-number {
            background: #667eea;
            color: white;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
        }
        .form-control {
            border-radius: 10px;
            border: 1px solid #ddd;
            padding: 12px 15px;
        }
        .btn-install {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 12px 30px;
            font-weight: 600;
            color: white;
        }
        .success-box {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            border-radius: 10px;
            padding: 1rem;
            margin: 1rem 0;
        }
        .demo-users {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1rem;
            margin-top: 1rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="install-card">
                    <div class="install-header">
                        <i class="fas fa-cog fa-3x text-primary mb-3"></i>
                        <h1>Instalador del Sistema</h1>
                        <p class="text-muted">Sistema de Formularios Personalizados</p>
                    </div>

                    <?php if ($installation_complete): ?>
                        <div class="success-box">
                            <h4 class="text-success">
                                <i class="fas fa-check-circle me-2"></i>
                                ¡Instalación Completada!
                            </h4>
                            <p><?= isset($success_message) ? htmlspecialchars($success_message) : 'El sistema se ha instalado correctamente.' ?></p>
                            <p>Se han creado las siguientes tablas con datos de demostración:</p>
                            <ul>
                                <li>Usuarios (3 usuarios demo creados)</li>
                                <li>Formularios (3 formularios demo creados)</li>
                                <li>Campos (campos para cada formulario)</li>
                                <li>Respuestas (4 respuestas demo creadas)</li>
                                <li>Comentarios</li>
                                <li>Productos (4 productos demo creados)</li>
                                <li>Archivos</li>
                            </ul>
                            
                            <div class="demo-users">
                                <h6><i class="fas fa-users me-2"></i>Usuarios de Prueba Creados:</h6>
                                <ul class="list-unstyled">
                                    <li><strong>Admin:</strong> admin@demo.com / Danjohn007</li>
                                    <li><strong>Operador:</strong> operador@demo.com / Danjohn007</li>
                                    <li><strong>Cliente:</strong> cliente@demo.com / Danjohn007</li>
                                </ul>
                            </div>
                            
                            <div class="mt-3">
                                <a href="/" class="btn btn-install">
                                    <i class="fas fa-sign-in-alt me-2"></i>
                                    Ir al Sistema
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        
                        <?php if (isset($errors) && !empty($errors)): ?>
                            <div class="alert alert-danger">
                                <h6><i class="fas fa-exclamation-triangle me-2"></i>Errores encontrados:</h6>
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?= htmlspecialchars($error) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="step">
                                <h5>
                                    <span class="step-number">1</span>
                                    Configuración de Base de Datos
                                </h5>
                                <p class="text-muted">Configura la conexión a tu base de datos MySQL</p>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="db_host" class="form-label">Servidor</label>
                                        <input type="text" class="form-control" id="db_host" name="db_host" 
                                               value="<?= htmlspecialchars($_POST['db_host'] ?? 'localhost') ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="db_name" class="form-label">Base de Datos</label>
                                        <input type="text" class="form-control" id="db_name" name="db_name" 
                                               value="<?= htmlspecialchars($_POST['db_name'] ?? 'formularios_db') ?>" required>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="db_user" class="form-label">Usuario</label>
                                        <input type="text" class="form-control" id="db_user" name="db_user" 
                                               value="<?= htmlspecialchars($_POST['db_user'] ?? 'root') ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="db_pass" class="form-label">Contraseña</label>
                                        <input type="password" class="form-control" id="db_pass" name="db_pass" 
                                               value="<?= htmlspecialchars($_POST['db_pass'] ?? '') ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="step">
                                <h5>
                                    <span class="step-number">2</span>
                                    Verificación de Requisitos
                                </h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="mb-1">
                                            <i class="fas fa-check text-success me-2"></i>
                                            PHP <?= phpversion() ?> 
                                            <?= version_compare(phpversion(), '8.0.0', '>=') ? '✓' : '❌' ?>
                                        </p>
                                        <p class="mb-1">
                                            <i class="fas fa-check text-success me-2"></i>
                                            PDO MySQL <?= extension_loaded('pdo_mysql') ? '✓' : '❌' ?>
                                        </p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-1">
                                            <i class="fas fa-check text-success me-2"></i>
                                            JSON <?= function_exists('json_encode') ? '✓' : '❌' ?>
                                        </p>
                                        <p class="mb-1">
                                            <i class="fas fa-check text-success me-2"></i>
                                            Uploads <?= is_writable(__DIR__ . '/public/uploads') || mkdir(__DIR__ . '/public/uploads', 0755, true) ? '✓' : '❌' ?>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="text-center">
                                <button type="submit" class="btn btn-install">
                                    <i class="fas fa-download me-2"></i>
                                    Instalar Sistema
                                </button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>