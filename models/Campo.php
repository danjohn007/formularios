<?php
/**
 * Campo Model
 */

class Campo {
    private $conn;
    private $table_name = "campos";

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Crear nuevo campo
     */
    public function crear($data) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (formulario_id, etiqueta, tipo, opciones, requerido, orden, configuracion) 
                  VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        
        return $stmt->execute([
            $data['formulario_id'],
            $data['etiqueta'],
            $data['tipo'],
            $data['opciones'],
            $data['requerido'] ?? 0,
            $data['orden'] ?? 0,
            $data['configuracion'] ?? null
        ]);
    }

    /**
     * Obtener campos por formulario
     */
    public function obtenerPorFormulario($formulario_id) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE formulario_id = ? 
                  ORDER BY orden, id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$formulario_id]);
        
        return $stmt->fetchAll();
    }

    /**
     * Obtener campo por ID
     */
    public function obtenerPorId($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        
        return $stmt->fetch();
    }

    /**
     * Actualizar campo
     */
    public function actualizar($id, $data) {
        $sets = [];
        $params = [];
        
        if (isset($data['etiqueta'])) {
            $sets[] = "etiqueta = ?";
            $params[] = $data['etiqueta'];
        }
        
        if (isset($data['tipo'])) {
            $sets[] = "tipo = ?";
            $params[] = $data['tipo'];
        }
        
        if (isset($data['opciones'])) {
            $sets[] = "opciones = ?";
            $params[] = $data['opciones'];
        }
        
        if (isset($data['requerido'])) {
            $sets[] = "requerido = ?";
            $params[] = $data['requerido'];
        }
        
        if (isset($data['orden'])) {
            $sets[] = "orden = ?";
            $params[] = $data['orden'];
        }
        
        if (isset($data['configuracion'])) {
            $sets[] = "configuracion = ?";
            $params[] = $data['configuracion'];
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
     * Eliminar campo
     */
    public function eliminar($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id]);
    }

    /**
     * Eliminar todos los campos de un formulario
     */
    public function eliminarPorFormulario($formulario_id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE formulario_id = ?";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$formulario_id]);
    }
}