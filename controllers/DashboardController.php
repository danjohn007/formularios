<?php
/**
 * DashboardController - Panel de control principal
 */

class DashboardController {
    private $db;
    private $auth;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->auth = new AuthController();
    }

    /**
     * Mostrar dashboard principal
     */
    public function index() {
        $this->auth->requireAuth();
        
        $user = $this->auth->getCurrentUser();
        $isOperador = $this->auth->isOperador();
        $stats = $this->obtenerEstadisticas($user);
        
        $title = 'Dashboard';
        include 'views/dashboard/index.php';
    }

    /**
     * Obtener estadísticas para el dashboard
     */
    private function obtenerEstadisticas($user) {
        $stats = [];
        
        try {
            // Estadísticas generales
            if ($this->auth->isOperador()) {
                // Total de formularios
                $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM formularios WHERE activo = 1");
                $stmt->execute();
                $stats['total_formularios'] = $stmt->fetch()['total'];
                
                // Total de respuestas
                $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM respuestas");
                $stmt->execute();
                $stats['total_respuestas'] = $stmt->fetch()['total'];
                
                // Respuestas por estatus
                $stmt = $this->db->prepare("
                    SELECT estatus, COUNT(*) as total 
                    FROM respuestas 
                    GROUP BY estatus
                ");
                $stmt->execute();
                $stats['respuestas_por_estatus'] = $stmt->fetchAll();
                
                // Ingresos totales
                $stmt = $this->db->prepare("
                    SELECT SUM(total) as ingresos 
                    FROM respuestas 
                    WHERE estatus = 'completado'
                ");
                $stmt->execute();
                $stats['ingresos_totales'] = $stmt->fetch()['ingresos'] ?? 0;
                
                // Respuestas recientes
                $stmt = $this->db->prepare("
                    SELECT r.*, f.titulo as formulario_titulo, u.nombre as usuario_nombre
                    FROM respuestas r
                    LEFT JOIN formularios f ON r.formulario_id = f.id
                    LEFT JOIN usuarios u ON r.usuario_id = u.id
                    ORDER BY r.fecha_creacion DESC
                    LIMIT 10
                ");
                $stmt->execute();
                $stats['respuestas_recientes'] = $stmt->fetchAll();
                
                // Gráfica de respuestas por mes
                $stmt = $this->db->prepare("
                    SELECT 
                        DATE_FORMAT(fecha_creacion, '%Y-%m') as mes,
                        COUNT(*) as total
                    FROM respuestas
                    WHERE fecha_creacion >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                    GROUP BY DATE_FORMAT(fecha_creacion, '%Y-%m')
                    ORDER BY mes
                ");
                $stmt->execute();
                $stats['respuestas_por_mes'] = $stmt->fetchAll();
                
            } else {
                // Para clientes, solo sus propias respuestas
                $stmt = $this->db->prepare("
                    SELECT COUNT(*) as total 
                    FROM respuestas 
                    WHERE usuario_id = ?
                ");
                $stmt->execute([$user['id']]);
                $stats['mis_respuestas'] = $stmt->fetch()['total'];
                
                // Mis respuestas recientes
                $stmt = $this->db->prepare("
                    SELECT r.*, f.titulo as formulario_titulo
                    FROM respuestas r
                    LEFT JOIN formularios f ON r.formulario_id = f.id
                    WHERE r.usuario_id = ?
                    ORDER BY r.fecha_creacion DESC
                    LIMIT 5
                ");
                $stmt->execute([$user['id']]);
                $stats['mis_respuestas_recientes'] = $stmt->fetchAll();
            }
            
        } catch (Exception $e) {
            error_log("Error obteniendo estadísticas: " . $e->getMessage());
        }
        
        return $stats;
    }

    /**
     * API endpoint para obtener estadísticas en JSON
     */
    public function getStats() {
        $this->auth->requireAuth();
        
        header('Content-Type: application/json');
        
        $user = $this->auth->getCurrentUser();
        $stats = $this->obtenerEstadisticas($user);
        
        echo json_encode($stats);
    }

    /**
     * Obtener datos para gráficas
     */
    public function getChartData() {
        $this->auth->requireOperador();
        
        header('Content-Type: application/json');
        
        $type = $_GET['type'] ?? '';
        $data = [];
        
        switch ($type) {
            case 'estatus':
                $stmt = $this->db->prepare("
                    SELECT 
                        estatus,
                        COUNT(*) as total
                    FROM respuestas
                    GROUP BY estatus
                ");
                $stmt->execute();
                $data = $stmt->fetchAll();
                break;
                
            case 'ingresos':
                $stmt = $this->db->prepare("
                    SELECT 
                        DATE_FORMAT(fecha_creacion, '%Y-%m') as mes,
                        SUM(total) as ingresos
                    FROM respuestas
                    WHERE estatus = 'completado' 
                    AND fecha_creacion >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                    GROUP BY DATE_FORMAT(fecha_creacion, '%Y-%m')
                    ORDER BY mes
                ");
                $stmt->execute();
                $data = $stmt->fetchAll();
                break;
                
            case 'tipos':
                $stmt = $this->db->prepare("
                    SELECT 
                        f.tipo,
                        COUNT(r.id) as total
                    FROM formularios f
                    LEFT JOIN respuestas r ON f.id = r.formulario_id
                    WHERE f.activo = 1
                    GROUP BY f.tipo
                ");
                $stmt->execute();
                $data = $stmt->fetchAll();
                break;
                
            case 'personal':
                $stmt = $this->db->prepare("
                    SELECT 
                        u.nombre,
                        COUNT(r.id) as asignadas
                    FROM usuarios u
                    LEFT JOIN respuestas r ON u.id = r.asignado_a
                    WHERE u.rol IN ('admin', 'operador') AND u.activo = 1
                    GROUP BY u.id, u.nombre
                    ORDER BY asignadas DESC
                ");
                $stmt->execute();
                $data = $stmt->fetchAll();
                break;
        }
        
        echo json_encode($data);
    }
}