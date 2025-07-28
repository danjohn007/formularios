<?php
/**
 * Usuario Model - Manejo de usuarios
 */

class Usuario {
    private $conn;
    private $table_name = "usuarios";

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Autenticar usuario
     */
    public function login($email, $password) {
        $query = "SELECT id, nombre, email, password, rol, activo 
                  FROM " . $this->table_name . " 
                  WHERE email = ? AND activo = 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$email]);
        
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            unset($user['password']); // No devolver la contraseña
            return $user;
        }
        
        return false;
    }

    /**
     * Crear nuevo usuario
     */
    public function crear($data) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (nombre, email, password, rol) 
                  VALUES (?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        
        $password_hash = password_hash($data['password'], PASSWORD_DEFAULT);
        
        return $stmt->execute([
            $data['nombre'],
            $data['email'], 
            $password_hash,
            $data['rol'] ?? 'cliente'
        ]);
    }

    /**
     * Obtener usuario por ID
     */
    public function obtenerPorId($id) {
        $query = "SELECT id, nombre, email, rol, activo, fecha_creacion 
                  FROM " . $this->table_name . " 
                  WHERE id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        
        return $stmt->fetch();
    }

    /**
     * Obtener todos los usuarios
     */
    public function obtenerTodos($filtros = []) {
        $where = [];
        $params = [];
        
        if (isset($filtros['rol'])) {
            $where[] = "rol = ?";
            $params[] = $filtros['rol'];
        }
        
        if (isset($filtros['activo'])) {
            $where[] = "activo = ?";
            $params[] = $filtros['activo'];
        }
        
        $query = "SELECT id, nombre, email, rol, activo, fecha_creacion 
                  FROM " . $this->table_name;
        
        if (!empty($where)) {
            $query .= " WHERE " . implode(" AND ", $where);
        }
        
        $query .= " ORDER BY fecha_creacion DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }

    /**
     * Actualizar usuario
     */
    public function actualizar($id, $data) {
        $sets = [];
        $params = [];
        
        if (isset($data['nombre'])) {
            $sets[] = "nombre = ?";
            $params[] = $data['nombre'];
        }
        
        if (isset($data['email'])) {
            $sets[] = "email = ?";
            $params[] = $data['email'];
        }
        
        if (isset($data['rol'])) {
            $sets[] = "rol = ?";
            $params[] = $data['rol'];
        }
        
        if (isset($data['activo'])) {
            $sets[] = "activo = ?";
            $params[] = $data['activo'];
        }
        
        if (isset($data['password'])) {
            $sets[] = "password = ?";
            $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        if (empty($sets)) {
            return false;
        }
        
        $params[] = $id;
        
        $query = "UPDATE " . $this->table_name . " 
                  SET " . implode(", ", $sets) . " 
                  WHERE id = ?";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute($params);
    }

    /**
     * Eliminar usuario (soft delete)
     */
    public function eliminar($id) {
        $query = "UPDATE " . $this->table_name . " 
                  SET activo = 0 
                  WHERE id = ?";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id]);
    }

    /**
     * Verificar si email existe
     */
    public function emailExiste($email, $excluir_id = null) {
        $query = "SELECT id FROM " . $this->table_name . " WHERE email = ?";
        $params = [$email];
        
        if ($excluir_id) {
            $query .= " AND id != ?";
            $params[] = $excluir_id;
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        
        return $stmt->rowCount() > 0;
    }

    /**
     * Obtener operadores activos
     */
    public function obtenerOperadores() {
        $query = "SELECT id, nombre, email 
                  FROM " . $this->table_name . " 
                  WHERE rol IN ('admin', 'operador') AND activo = 1 
                  ORDER BY nombre";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Obtener estadísticas de usuarios
     */
    public function obtenerEstadisticas() {
        $query = "SELECT 
                    rol,
                    COUNT(*) as total,
                    SUM(CASE WHEN activo = 1 THEN 1 ELSE 0 END) as activos
                  FROM " . $this->table_name . " 
                  GROUP BY rol";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
}