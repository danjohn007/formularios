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
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=" . $this->charset;
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }

        return $this->conn;
    }

    /**
     * Setup database tables
     */
    public function setupTables() {
        $sql = "
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

        try {
            $this->conn->exec($sql);
            return true;
        } catch(PDOException $e) {
            error_log("Error creating tables: " . $e->getMessage());
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

        $sql = "INSERT IGNORE INTO usuarios (nombre, email, password, rol) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);

        foreach ($users as $user) {
            $stmt->execute([$user['nombre'], $user['email'], $user['password'], $user['rol']]);
        }
    }
}