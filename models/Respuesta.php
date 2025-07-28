<?php
/**
 * Respuesta Model
 */

class Respuesta {
    private $conn;
    private $table_name = "respuestas";

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Crear nueva respuesta
     */
    public function crear($data) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (formulario_id, usuario_id, datos, total, fecha_entrega, metodo_entrega) 
                  VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        
        return $stmt->execute([
            $data['formulario_id'],
            $data['usuario_id'],
            $data['datos'],
            $data['total'] ?? 0,
            $data['fecha_entrega'] ?? null,
            $data['metodo_entrega'] ?? null
        ]);
    }

    /**
     * Obtener respuesta por ID
     */
    public function obtenerPorId($id) {
        $query = "SELECT r.*, f.titulo as formulario_titulo, f.tipo as formulario_tipo,
                         u.nombre as usuario_nombre, u.email as usuario_email,
                         ua.nombre as asignado_nombre
                  FROM " . $this->table_name . " r
                  LEFT JOIN formularios f ON r.formulario_id = f.id
                  LEFT JOIN usuarios u ON r.usuario_id = u.id
                  LEFT JOIN usuarios ua ON r.asignado_a = ua.id
                  WHERE r.id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        
        return $stmt->fetch();
    }

    /**
     * Obtener todas las respuestas
     */
    public function obtenerTodas($filtros = []) {
        $where = [];
        $params = [];
        
        if (isset($filtros['estatus'])) {
            $where[] = "r.estatus = ?";
            $params[] = $filtros['estatus'];
        }
        
        if (isset($filtros['formulario_id'])) {
            $where[] = "r.formulario_id = ?";
            $params[] = $filtros['formulario_id'];
        }
        
        if (isset($filtros['usuario_id'])) {
            $where[] = "r.usuario_id = ?";
            $params[] = $filtros['usuario_id'];
        }
        
        if (isset($filtros['asignado_a'])) {
            $where[] = "r.asignado_a = ?";
            $params[] = $filtros['asignado_a'];
        }
        
        $query = "SELECT r.*, f.titulo as formulario_titulo, f.tipo as formulario_tipo,
                         u.nombre as usuario_nombre, u.email as usuario_email,
                         ua.nombre as asignado_nombre
                  FROM " . $this->table_name . " r
                  LEFT JOIN formularios f ON r.formulario_id = f.id
                  LEFT JOIN usuarios u ON r.usuario_id = u.id
                  LEFT JOIN usuarios ua ON r.asignado_a = ua.id";
        
        if (!empty($where)) {
            $query .= " WHERE " . implode(" AND ", $where);
        }
        
        $query .= " ORDER BY r.fecha_creacion DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }

    /**
     * Actualizar respuesta
     */
    public function actualizar($id, $data) {
        $sets = [];
        $params = [];
        
        if (isset($data['estatus'])) {
            $sets[] = "estatus = ?";
            $params[] = $data['estatus'];
        }
        
        if (isset($data['asignado_a'])) {
            $sets[] = "asignado_a = ?";
            $params[] = $data['asignado_a'];
        }
        
        if (isset($data['total'])) {
            $sets[] = "total = ?";
            $params[] = $data['total'];
        }
        
        if (isset($data['fecha_entrega'])) {
            $sets[] = "fecha_entrega = ?";
            $params[] = $data['fecha_entrega'];
        }
        
        if (isset($data['metodo_entrega'])) {
            $sets[] = "metodo_entrega = ?";
            $params[] = $data['metodo_entrega'];
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
     * Obtener comentarios de una respuesta
     */
    public function obtenerComentarios($respuesta_id) {
        $query = "SELECT c.*, u.nombre as usuario_nombre 
                  FROM comentarios c
                  LEFT JOIN usuarios u ON c.usuario_id = u.id
                  WHERE c.respuesta_id = ?
                  ORDER BY c.fecha_creacion DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$respuesta_id]);
        
        return $stmt->fetchAll();
    }

    /**
     * Agregar comentario
     */
    public function agregarComentario($data) {
        $query = "INSERT INTO comentarios (respuesta_id, usuario_id, comentario, tipo) 
                  VALUES (?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        
        return $stmt->execute([
            $data['respuesta_id'],
            $data['usuario_id'],
            $data['comentario'],
            $data['tipo'] ?? 'comentario'
        ]);
    }

    /**
     * Obtener estadísticas de respuestas
     */
    public function obtenerEstadisticas() {
        $query = "SELECT 
                    estatus,
                    COUNT(*) as total,
                    SUM(total) as ingresos
                  FROM " . $this->table_name . " 
                  GROUP BY estatus";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
}