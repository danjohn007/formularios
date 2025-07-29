<?php
/**
 * Database Configuration
 * 
 * Configuration settings for MySQL database connection
 */

class Database {
    private $host = 'localhost';
    private $db_name = 'formularios_db';
    private $username = 'root';
    private $password = '';
    private $charset = 'utf8mb4';
    private $conn;

    /**
     * Get database connection
     */
    public function getConnection() {
        $this->conn = null;
        
        try {
            // For demo purposes, use SQLite if MySQL is not available
            if (!extension_loaded('pdo_mysql') || !$this->testMySQLConnection()) {
                $dsn = "sqlite:" . __DIR__ . "/../data/formularios.db";
                // Create data directory if it doesn't exist
                $dataDir = __DIR__ . "/../data";
                if (!is_dir($dataDir)) {
                    mkdir($dataDir, 0755, true);
                }
            } else {
                $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=" . $this->charset;
            }
            
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }

        return $this->conn;
    }

    /**
     * Test MySQL connection
     */
    private function testMySQLConnection() {
        try {
            $dsn = "mysql:host=" . $this->host . ";charset=" . $this->charset;
            $testConn = new PDO($dsn, $this->username, $this->password);
            $testConn = null; // Close connection
            return true;
        } catch(PDOException $e) {
            return false;
        }
    }

    /**
     * Setup database tables
     */
    public function setupTables() {
        // Check if we're using SQLite or MySQL
        $driver = $this->conn->getAttribute(PDO::ATTR_DRIVER_NAME);
        
        if ($driver === 'sqlite') {
            $sql = $this->getSQLiteSchema();
        } else {
            $sql = $this->getMySQLSchema();
        }

        try {
            $this->conn->exec($sql);
            return true;
        } catch(PDOException $e) {
            error_log("Error creating tables: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get MySQL schema
     */
    private function getMySQLSchema() {
        return "
        -- Tabla de usuarios
        CREATE TABLE IF NOT EXISTS usuarios (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nombre VARCHAR(100) NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            rol ENUM('admin', 'operador', 'cliente') DEFAULT 'cliente',
            activo TINYINT(1) DEFAULT 1,
            fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        );

        -- Tabla de formularios
        CREATE TABLE IF NOT EXISTS formularios (
            id INT AUTO_INCREMENT PRIMARY KEY,
            titulo VARCHAR(200) NOT NULL,
            descripcion TEXT,
            tipo ENUM('reservacion', 'compra', 'servicio') NOT NULL,
            configuracion JSON,
            activo TINYINT(1) DEFAULT 1,
            usuario_id INT NOT NULL,
            fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
        );

        -- Tabla de campos de formulario
        CREATE TABLE IF NOT EXISTS campos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            formulario_id INT NOT NULL,
            etiqueta VARCHAR(200) NOT NULL,
            tipo ENUM('text', 'textarea', 'select', 'radio', 'checkbox', 'email', 'tel', 'date', 'time', 'datetime', 'number', 'file') NOT NULL,
            opciones JSON,
            requerido TINYINT(1) DEFAULT 0,
            orden INT DEFAULT 0,
            configuracion JSON,
            fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (formulario_id) REFERENCES formularios(id) ON DELETE CASCADE
        );

        -- Tabla de respuestas
        CREATE TABLE IF NOT EXISTS respuestas (
            id INT AUTO_INCREMENT PRIMARY KEY,
            formulario_id INT NOT NULL,
            usuario_id INT,
            datos JSON NOT NULL,
            estatus ENUM('pendiente', 'en_proceso', 'completado', 'cancelado') DEFAULT 'pendiente',
            asignado_a INT,
            total DECIMAL(10,2) DEFAULT 0,
            fecha_entrega DATE,
            metodo_entrega ENUM('pickup', 'domicilio'),
            fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (formulario_id) REFERENCES formularios(id) ON DELETE CASCADE,
            FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
            FOREIGN KEY (asignado_a) REFERENCES usuarios(id) ON DELETE SET NULL
        );

        -- Tabla de comentarios
        CREATE TABLE IF NOT EXISTS comentarios (
            id INT AUTO_INCREMENT PRIMARY KEY,
            respuesta_id INT NOT NULL,
            usuario_id INT NOT NULL,
            comentario TEXT NOT NULL,
            tipo ENUM('comentario', 'cambio_estatus', 'asignacion') DEFAULT 'comentario',
            fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (respuesta_id) REFERENCES respuestas(id) ON DELETE CASCADE,
            FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
        );

        -- Tabla de productos
        CREATE TABLE IF NOT EXISTS productos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nombre VARCHAR(200) NOT NULL,
            descripcion TEXT,
            precio DECIMAL(10,2) NOT NULL DEFAULT 0,
            stock INT DEFAULT 0,
            activo TINYINT(1) DEFAULT 1,
            fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        );

        -- Tabla de archivos adjuntos
        CREATE TABLE IF NOT EXISTS archivos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            respuesta_id INT NOT NULL,
            nombre_original VARCHAR(255) NOT NULL,
            nombre_archivo VARCHAR(255) NOT NULL,
            ruta VARCHAR(500) NOT NULL,
            tipo_mime VARCHAR(100),
            tamano INT,
            fecha_subida TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (respuesta_id) REFERENCES respuestas(id) ON DELETE CASCADE
        );
        ";
    }

    /**
     * Get SQLite schema
     */
    private function getSQLiteSchema() {
        return "
        -- Tabla de usuarios
        CREATE TABLE IF NOT EXISTS usuarios (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nombre VARCHAR(100) NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            rol VARCHAR(20) DEFAULT 'cliente' CHECK (rol IN ('admin', 'operador', 'cliente')),
            activo INTEGER DEFAULT 1,
            fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
            fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        -- Tabla de formularios
        CREATE TABLE IF NOT EXISTS formularios (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            titulo VARCHAR(200) NOT NULL,
            descripcion TEXT,
            tipo VARCHAR(20) NOT NULL CHECK (tipo IN ('reservacion', 'compra', 'servicio')),
            configuracion TEXT,
            activo INTEGER DEFAULT 1,
            usuario_id INTEGER NOT NULL,
            fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
            fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
        );

        -- Tabla de campos de formulario
        CREATE TABLE IF NOT EXISTS campos (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            formulario_id INTEGER NOT NULL,
            etiqueta VARCHAR(200) NOT NULL,
            tipo VARCHAR(20) NOT NULL CHECK (tipo IN ('text', 'textarea', 'select', 'radio', 'checkbox', 'email', 'tel', 'date', 'time', 'datetime', 'number', 'file')),
            opciones TEXT,
            requerido INTEGER DEFAULT 0,
            orden INTEGER DEFAULT 0,
            configuracion TEXT,
            fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (formulario_id) REFERENCES formularios(id) ON DELETE CASCADE
        );

        -- Tabla de respuestas
        CREATE TABLE IF NOT EXISTS respuestas (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            formulario_id INTEGER NOT NULL,
            usuario_id INTEGER,
            datos TEXT NOT NULL,
            estatus VARCHAR(20) DEFAULT 'pendiente' CHECK (estatus IN ('pendiente', 'en_proceso', 'completado', 'cancelado')),
            asignado_a INTEGER,
            total DECIMAL(10,2) DEFAULT 0,
            fecha_entrega DATE,
            metodo_entrega VARCHAR(20) CHECK (metodo_entrega IN ('pickup', 'domicilio')),
            fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
            fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (formulario_id) REFERENCES formularios(id) ON DELETE CASCADE,
            FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
            FOREIGN KEY (asignado_a) REFERENCES usuarios(id) ON DELETE SET NULL
        );

        -- Tabla de comentarios
        CREATE TABLE IF NOT EXISTS comentarios (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            respuesta_id INTEGER NOT NULL,
            usuario_id INTEGER NOT NULL,
            comentario TEXT NOT NULL,
            tipo VARCHAR(20) DEFAULT 'comentario' CHECK (tipo IN ('comentario', 'cambio_estatus', 'asignacion')),
            fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (respuesta_id) REFERENCES respuestas(id) ON DELETE CASCADE,
            FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
        );

        -- Tabla de productos
        CREATE TABLE IF NOT EXISTS productos (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nombre VARCHAR(200) NOT NULL,
            descripcion TEXT,
            precio DECIMAL(10,2) NOT NULL DEFAULT 0,
            stock INTEGER DEFAULT 0,
            activo INTEGER DEFAULT 1,
            fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
            fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        -- Tabla de archivos adjuntos
        CREATE TABLE IF NOT EXISTS archivos (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            respuesta_id INTEGER NOT NULL,
            nombre_original VARCHAR(255) NOT NULL,
            nombre_archivo VARCHAR(255) NOT NULL,
            ruta VARCHAR(500) NOT NULL,
            tipo_mime VARCHAR(100),
            tamano INTEGER,
            fecha_subida DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (respuesta_id) REFERENCES respuestas(id) ON DELETE CASCADE
        );
        ";
    }

    /**
     * Insert demo forms with fields
     */
    public function insertDemoForms() {
        try {
            $driver = $this->conn->getAttribute(PDO::ATTR_DRIVER_NAME);
            
            // Get admin user ID for forms
            $stmt = $this->conn->prepare("SELECT id FROM usuarios WHERE rol = 'admin' LIMIT 1");
            $stmt->execute();
            $admin = $stmt->fetch();
            if (!$admin) {
                return false;
            }
            $admin_id = $admin['id'];
            
            // Demo forms to create
            $forms = [
                [
                    'titulo' => 'Reserva de Salón de Eventos',
                    'descripcion' => 'Formulario para reservar nuestro salón de eventos con todos los servicios incluidos.',
                    'tipo' => 'reservacion',
                    'configuracion' => '{"requiere_pago": true, "notificaciones": true}',
                    'campos' => [
                        ['etiqueta' => 'Nombre Completo', 'tipo' => 'text', 'requerido' => 1, 'orden' => 1],
                        ['etiqueta' => 'Email', 'tipo' => 'email', 'requerido' => 1, 'orden' => 2],
                        ['etiqueta' => 'Teléfono', 'tipo' => 'tel', 'requerido' => 1, 'orden' => 3],
                        ['etiqueta' => 'Fecha del Evento', 'tipo' => 'date', 'requerido' => 1, 'orden' => 4],
                        ['etiqueta' => 'Hora de Inicio', 'tipo' => 'time', 'requerido' => 1, 'orden' => 5],
                        ['etiqueta' => 'Número de Invitados', 'tipo' => 'number', 'requerido' => 1, 'orden' => 6],
                        ['etiqueta' => 'Tipo de Evento', 'tipo' => 'select', 'opciones' => '["Boda", "Cumpleaños", "Corporativo", "Social", "Otro"]', 'requerido' => 1, 'orden' => 7],
                        ['etiqueta' => 'Servicios Adicionales', 'tipo' => 'checkbox', 'opciones' => '["Catering", "Decoración", "Sonido", "Fotografía"]', 'requerido' => 0, 'orden' => 8],
                        ['etiqueta' => 'Comentarios Especiales', 'tipo' => 'textarea', 'requerido' => 0, 'orden' => 9]
                    ]
                ],
                [
                    'titulo' => 'Pedido de Productos y Servicios',
                    'descripcion' => 'Realiza tu pedido de productos y servicios disponibles en nuestro catálogo.',
                    'tipo' => 'compra',
                    'configuracion' => '{"calcula_total": true, "inventario": true}',
                    'campos' => [
                        ['etiqueta' => 'Nombre del Cliente', 'tipo' => 'text', 'requerido' => 1, 'orden' => 1],
                        ['etiqueta' => 'Email de Contacto', 'tipo' => 'email', 'requerido' => 1, 'orden' => 2],
                        ['etiqueta' => 'Productos Deseados', 'tipo' => 'checkbox', 'opciones' => '["Reserva de Salón", "Servicio de Catering", "Decoración Básica", "Sonido y Audio"]', 'requerido' => 1, 'orden' => 3],
                        ['etiqueta' => 'Cantidad', 'tipo' => 'number', 'requerido' => 1, 'orden' => 4],
                        ['etiqueta' => 'Fecha de Entrega', 'tipo' => 'date', 'requerido' => 1, 'orden' => 5],
                        ['etiqueta' => 'Método de Entrega', 'tipo' => 'radio', 'opciones' => '["Pickup en tienda", "Entrega a domicilio"]', 'requerido' => 1, 'orden' => 6],
                        ['etiqueta' => 'Dirección de Entrega', 'tipo' => 'textarea', 'requerido' => 0, 'orden' => 7],
                        ['etiqueta' => 'Notas del Pedido', 'tipo' => 'textarea', 'requerido' => 0, 'orden' => 8]
                    ]
                ],
                [
                    'titulo' => 'Solicitud de Servicio Técnico',
                    'descripcion' => 'Solicita asistencia técnica o mantenimiento para eventos y equipos.',
                    'tipo' => 'servicio',
                    'configuracion' => '{"urgencia": true, "seguimiento": true}',
                    'campos' => [
                        ['etiqueta' => 'Nombre de Contacto', 'tipo' => 'text', 'requerido' => 1, 'orden' => 1],
                        ['etiqueta' => 'Email', 'tipo' => 'email', 'requerido' => 1, 'orden' => 2],
                        ['etiqueta' => 'Teléfono', 'tipo' => 'tel', 'requerido' => 1, 'orden' => 3],
                        ['etiqueta' => 'Tipo de Servicio', 'tipo' => 'select', 'opciones' => '["Mantenimiento de Audio", "Instalación de Equipos", "Soporte Técnico", "Reparación", "Consultoría"]', 'requerido' => 1, 'orden' => 4],
                        ['etiqueta' => 'Urgencia', 'tipo' => 'radio', 'opciones' => '["Baja", "Media", "Alta", "Crítica"]', 'requerido' => 1, 'orden' => 5],
                        ['etiqueta' => 'Fecha Preferida', 'tipo' => 'date', 'requerido' => 1, 'orden' => 6],
                        ['etiqueta' => 'Descripción del Problema', 'tipo' => 'textarea', 'requerido' => 1, 'orden' => 7],
                        ['etiqueta' => 'Adjuntar Imagen', 'tipo' => 'file', 'requerido' => 0, 'orden' => 8]
                    ]
                ]
            ];
            
            foreach ($forms as $form_data) {
                // Insert form
                if ($driver === 'sqlite') {
                    $sql = "INSERT OR IGNORE INTO formularios (titulo, descripcion, tipo, configuracion, usuario_id) VALUES (?, ?, ?, ?, ?)";
                } else {
                    $sql = "INSERT IGNORE INTO formularios (titulo, descripcion, tipo, configuracion, usuario_id) VALUES (?, ?, ?, ?, ?)";
                }
                
                $stmt = $this->conn->prepare($sql);
                $success = $stmt->execute([
                    $form_data['titulo'],
                    $form_data['descripcion'],
                    $form_data['tipo'],
                    $form_data['configuracion'],
                    $admin_id
                ]);
                
                if (!$success) {
                    continue;
                }
                
                // Get the form ID
                $form_id = $this->conn->lastInsertId();
                if (!$form_id) {
                    // Try to get existing form ID
                    $stmt = $this->conn->prepare("SELECT id FROM formularios WHERE titulo = ? LIMIT 1");
                    $stmt->execute([$form_data['titulo']]);
                    $existing = $stmt->fetch();
                    if ($existing) {
                        $form_id = $existing['id'];
                    } else {
                        continue;
                    }
                }
                
                // Insert fields for this form
                foreach ($form_data['campos'] as $campo) {
                    if ($driver === 'sqlite') {
                        $field_sql = "INSERT OR IGNORE INTO campos (formulario_id, etiqueta, tipo, opciones, requerido, orden) VALUES (?, ?, ?, ?, ?, ?)";
                    } else {
                        $field_sql = "INSERT IGNORE INTO campos (formulario_id, etiqueta, tipo, opciones, requerido, orden) VALUES (?, ?, ?, ?, ?, ?)";
                    }
                    
                    $field_stmt = $this->conn->prepare($field_sql);
                    $field_stmt->execute([
                        $form_id,
                        $campo['etiqueta'],
                        $campo['tipo'],
                        $campo['opciones'] ?? null,
                        $campo['requerido'],
                        $campo['orden']
                    ]);
                }
            }
            
            return true;
        } catch(PDOException $e) {
            error_log("Error inserting demo forms: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Insert demo responses
     */
    public function insertDemoResponses() {
        try {
            $driver = $this->conn->getAttribute(PDO::ATTR_DRIVER_NAME);
            
            // Get user IDs
            $stmt = $this->conn->prepare("SELECT id, rol FROM usuarios");
            $stmt->execute();
            $users = $stmt->fetchAll();
            
            if (empty($users)) {
                return false;
            }
            
            $admin_id = null;
            $client_id = null;
            foreach ($users as $user) {
                if ($user['rol'] === 'admin') $admin_id = $user['id'];
                if ($user['rol'] === 'cliente') $client_id = $user['id'];
            }
            
            // Get form IDs
            $stmt = $this->conn->prepare("SELECT id, titulo, tipo FROM formularios");
            $stmt->execute();
            $forms = $stmt->fetchAll();
            
            if (empty($forms)) {
                return false;
            }
            
            // Sample responses to create
            $responses = [
                [
                    'formulario_titulo' => 'Reserva de Salón de Eventos',
                    'usuario_id' => $client_id,
                    'datos' => '{"nombre_completo": "María García", "email": "maria@example.com", "telefono": "555-0123", "fecha_evento": "2024-03-15", "hora_inicio": "18:00", "numero_invitados": "150", "tipo_evento": "Boda", "servicios_adicionales": ["Catering", "Decoración", "Sonido"], "comentarios": "Necesitamos decoración en colores pasteles"}',
                    'estatus' => 'completado',
                    'asignado_a' => $admin_id,
                    'total' => 2500.00,
                    'fecha_entrega' => '2024-03-15',
                    'metodo_entrega' => 'pickup',
                    'fecha_creacion' => date('Y-m-d H:i:s', strtotime('-10 days'))
                ],
                [
                    'formulario_titulo' => 'Pedido de Productos y Servicios',
                    'usuario_id' => $client_id,
                    'datos' => '{"nombre_cliente": "Juan Pérez", "email": "juan@example.com", "productos": ["Servicio de Catering", "Sonido y Audio"], "cantidad": "2", "fecha_entrega": "2024-03-20", "metodo_entrega": "Entrega a domicilio", "direccion": "Calle Principal #123", "notas": "Confirmar horario de entrega"}',
                    'estatus' => 'en_proceso',
                    'asignado_a' => $admin_id,
                    'total' => 900.00,
                    'fecha_entrega' => '2024-03-20',
                    'metodo_entrega' => 'domicilio',
                    'fecha_creacion' => date('Y-m-d H:i:s', strtotime('-5 days'))
                ],
                [
                    'formulario_titulo' => 'Solicitud de Servicio Técnico',
                    'usuario_id' => $client_id,
                    'datos' => '{"nombre_contacto": "Ana López", "email": "ana@example.com", "telefono": "555-0456", "tipo_servicio": "Mantenimiento de Audio", "urgencia": "Media", "fecha_preferida": "2024-03-18", "descripcion": "Equipo de sonido presenta interferencias durante eventos"}',
                    'estatus' => 'pendiente',
                    'asignado_a' => null,
                    'total' => 250.00,
                    'fecha_entrega' => '2024-03-18',
                    'metodo_entrega' => 'pickup',
                    'fecha_creacion' => date('Y-m-d H:i:s', strtotime('-2 days'))
                ],
                [
                    'formulario_titulo' => 'Reserva de Salón de Eventos',
                    'usuario_id' => $client_id,
                    'datos' => '{"nombre_completo": "Carlos Martínez", "email": "carlos@example.com", "telefono": "555-0789", "fecha_evento": "2024-04-05", "hora_inicio": "20:00", "numero_invitados": "80", "tipo_evento": "Cumpleaños", "servicios_adicionales": ["Catering", "Decoración"], "comentarios": "Fiesta sorpresa, decoración temática"}',
                    'estatus' => 'pendiente',
                    'asignado_a' => $admin_id,
                    'total' => 1800.00,
                    'fecha_entrega' => '2024-04-05',
                    'metodo_entrega' => 'pickup',
                    'fecha_creacion' => date('Y-m-d H:i:s', strtotime('-1 day'))
                ]
            ];
            
            foreach ($responses as $response_data) {
                // Find the form ID by title
                $form_id = null;
                foreach ($forms as $form) {
                    if ($form['titulo'] === $response_data['formulario_titulo']) {
                        $form_id = $form['id'];
                        break;
                    }
                }
                
                if (!$form_id) {
                    continue;
                }
                
                // Insert response
                if ($driver === 'sqlite') {
                    $sql = "INSERT OR IGNORE INTO respuestas (formulario_id, usuario_id, datos, estatus, asignado_a, total, fecha_entrega, metodo_entrega, fecha_creacion) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                } else {
                    $sql = "INSERT IGNORE INTO respuestas (formulario_id, usuario_id, datos, estatus, asignado_a, total, fecha_entrega, metodo_entrega, fecha_creacion) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                }
                
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([
                    $form_id,
                    $response_data['usuario_id'],
                    $response_data['datos'],
                    $response_data['estatus'],
                    $response_data['asignado_a'],
                    $response_data['total'],
                    $response_data['fecha_entrega'],
                    $response_data['metodo_entrega'],
                    $response_data['fecha_creacion']
                ]);
            }
            
            return true;
        } catch(PDOException $e) {
            error_log("Error inserting demo responses: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Insert demo users
     */
    public function insertDemoUsers() {
        $users = [
            [
                'nombre' => 'Administrador Demo',
                'email' => 'admin@demo.com',
                'password' => password_hash('Danjohn007', PASSWORD_DEFAULT),
                'rol' => 'admin'
            ],
            [
                'nombre' => 'Operador Demo',
                'email' => 'operador@demo.com',
                'password' => password_hash('Danjohn007', PASSWORD_DEFAULT),
                'rol' => 'operador'
            ],
            [
                'nombre' => 'Cliente Demo',
                'email' => 'cliente@demo.com',
                'password' => password_hash('Danjohn007', PASSWORD_DEFAULT),
                'rol' => 'cliente'
            ]
        ];

        try {
            $driver = $this->conn->getAttribute(PDO::ATTR_DRIVER_NAME);
            if ($driver === 'sqlite') {
                $sql = "INSERT OR IGNORE INTO usuarios (nombre, email, password, rol) VALUES (?, ?, ?, ?)";
            } else {
                $sql = "INSERT IGNORE INTO usuarios (nombre, email, password, rol) VALUES (?, ?, ?, ?)";
            }
            
            $stmt = $this->conn->prepare($sql);

            foreach ($users as $user) {
                if (!$stmt->execute([$user['nombre'], $user['email'], $user['password'], $user['rol']])) {
                    return false;
                }
            }
            return true;
        } catch(PDOException $e) {
            error_log("Error inserting demo users: " . $e->getMessage());
            return false;
        }
    }
}