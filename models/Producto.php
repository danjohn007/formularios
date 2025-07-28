<?php
/**
 * Producto Model
 */

class Producto {
    private $conn;
    private $table_name = "productos";

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Crear nuevo producto
     */
    public function crear($data) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (nombre, descripcion, precio, stock) 
                  VALUES (?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        
        return $stmt->execute([
            $data['nombre'],
            $data['descripcion'],
            $data['precio'],
            $data['stock'] ?? 0
        ]);
    }

    /**
     * Obtener producto por ID
     */
    public function obtenerPorId($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        
        return $stmt->fetch();
    }

    /**
     * Obtener todos los productos
     */
    public function obtenerTodos($filtros = []) {
        $where = [];
        $params = [];
        
        if (isset($filtros['activo'])) {
            $where[] = "activo = ?";
            $params[] = $filtros['activo'];
        }
        
        $query = "SELECT * FROM " . $this->table_name;
        
        if (!empty($where)) {
            $query .= " WHERE " . implode(" AND ", $where);
        }
        
        $query .= " ORDER BY nombre";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }

    /**
     * Obtener productos activos
     */
    public function obtenerActivos() {
        return $this->obtenerTodos(['activo' => 1]);
    }

    /**
     * Actualizar producto
     */
    public function actualizar($id, $data) {
        $sets = [];
        $params = [];
        
        if (isset($data['nombre'])) {
            $sets[] = "nombre = ?";
            $params[] = $data['nombre'];
        }
        
        if (isset($data['descripcion'])) {
            $sets[] = "descripcion = ?";
            $params[] = $data['descripcion'];
        }
        
        if (isset($data['precio'])) {
            $sets[] = "precio = ?";
            $params[] = $data['precio'];
        }
        
        if (isset($data['stock'])) {
            $sets[] = "stock = ?";
            $params[] = $data['stock'];
        }
        
        if (isset($data['activo'])) {
            $sets[] = "activo = ?";
            $params[] = $data['activo'];
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
     * Eliminar producto (soft delete)
     */
    public function eliminar($id) {
        $query = "UPDATE " . $this->table_name . " 
                  SET activo = 0 
                  WHERE id = ?";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id]);
    }
}