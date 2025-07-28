<?php
/**
 * Formulario Model
 */

class Formulario {
    private $conn;
    private $table_name = "formularios";

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Crear nuevo formulario
     */
    public function crear($data) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (titulo, descripcion, tipo, configuracion, usuario_id) 
                  VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        
        return $stmt->execute([
            $data['titulo'],
            $data['descripcion'],
            $data['tipo'],
            $data['configuracion'],
            $data['usuario_id']
        ]);
    }

    /**
     * Obtener formulario por ID
     */
    public function obtenerPorId($id) {
        $query = "SELECT f.*, u.nombre as creador_nombre 
                  FROM " . $this->table_name . " f
                  LEFT JOIN usuarios u ON f.usuario_id = u.id
                  WHERE f.id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        
        return $stmt->fetch();
    }

    /**
     * Obtener todos los formularios
     */
    public function obtenerTodos($filtros = []) {
        $where = [];
        $params = [];
        
        if (isset($filtros['tipo'])) {
            $where[] = "f.tipo = ?";
            $params[] = $filtros['tipo'];
        }
        
        if (isset($filtros['activo'])) {
            $where[] = "f.activo = ?";
            $params[] = $filtros['activo'];
        }
        
        $query = "SELECT f.*, u.nombre as creador_nombre,
                         (SELECT COUNT(*) FROM respuestas r WHERE r.formulario_id = f.id) as total_respuestas
                  FROM " . $this->table_name . " f
                  LEFT JOIN usuarios u ON f.usuario_id = u.id";
        
        if (!empty($where)) {
            $query .= " WHERE " . implode(" AND ", $where);
        }
        
        $query .= " ORDER BY f.fecha_creacion DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }

    /**
     * Obtener formularios activos
     */
    public function obtenerActivos() {
        return $this->obtenerTodos(['activo' => 1]);
    }

    /**
     * Actualizar formulario
     */
    public function actualizar($id, $data) {
        $sets = [];
        $params = [];
        
        if (isset($data['titulo'])) {
            $sets[] = "titulo = ?";
            $params[] = $data['titulo'];
        }
        
        if (isset($data['descripcion'])) {
            $sets[] = "descripcion = ?";
            $params[] = $data['descripcion'];
        }
        
        if (isset($data['tipo'])) {
            $sets[] = "tipo = ?";
            $params[] = $data['tipo'];
        }
        
        if (isset($data['configuracion'])) {
            $sets[] = "configuracion = ?";
            $params[] = $data['configuracion'];
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
     * Eliminar formulario (soft delete)
     */
    public function eliminar($id) {
        $query = "UPDATE " . $this->table_name . " 
                  SET activo = 0 
                  WHERE id = ?";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id]);
    }

    /**
     * Obtener estadísticas de formularios
     */
    public function obtenerEstadisticas() {
        $query = "SELECT 
                    tipo,
                    COUNT(*) as total,
                    SUM(CASE WHEN activo = 1 THEN 1 ELSE 0 END) as activos
                  FROM " . $this->table_name . " 
                  GROUP BY tipo";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
}