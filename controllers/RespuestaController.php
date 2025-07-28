<?php
/**
 * RespuestaController - Gestión de respuestas
 */

class RespuestaController {
    private $db;
    private $auth;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->auth = new AuthController();
    }

    /**
     * Listar respuestas
     */
    public function index() {
        $this->auth->requireOperador();
        
        $filtros = [];
        if (isset($_GET['estatus'])) {
            $filtros['estatus'] = $_GET['estatus'];
        }
        
        $respuesta = new Respuesta($this->db);
        $respuestas = $respuesta->obtenerTodas($filtros);
        
        // Obtener operadores para asignación
        $usuario = new Usuario($this->db);
        $operadores = $usuario->obtenerOperadores();
        
        $title = 'Respuestas';
        include 'views/respuestas/index.php';
    }

    /**
     * Ver detalle de respuesta
     */
    public function ver() {
        $this->auth->requireOperador();
        
        $id = $_GET['id'] ?? 0;
        $respuesta = new Respuesta($this->db);
        $resp = $respuesta->obtenerPorId($id);
        
        if (!$resp) {
            header('Location: /respuestas');
            exit;
        }

        // Obtener comentarios
        $comentarios = $respuesta->obtenerComentarios($id);
        
        // Obtener operadores para asignación
        $usuario = new Usuario($this->db);
        $operadores = $usuario->obtenerOperadores();
        
        $title = 'Ver Respuesta #' . $id;
        include 'views/respuestas/ver.php';
    }

    /**
     * Actualizar estatus de respuesta
     */
    public function actualizarEstatus() {
        $this->auth->requireOperador();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /respuestas');
            exit;
        }

        $id = $_POST['id'] ?? 0;
        $estatus = $_POST['estatus'] ?? '';
        $asignado_a = $_POST['asignado_a'] ?? null;
        
        $respuesta = new Respuesta($this->db);
        $user = $this->auth->getCurrentUser();
        
        $data = [];
        if ($estatus) {
            $data['estatus'] = $estatus;
        }
        if ($asignado_a) {
            $data['asignado_a'] = $asignado_a;
        }
        
        if ($respuesta->actualizar($id, $data)) {
            // Agregar comentario de cambio
            $comentario = '';
            if ($estatus) {
                $comentario .= "Estatus cambiado a: " . ucfirst(str_replace('_', ' ', $estatus));
            }
            if ($asignado_a) {
                $usuario = new Usuario($this->db);
                $asignado = $usuario->obtenerPorId($asignado_a);
                if ($asignado) {
                    $comentario .= ($comentario ? '. ' : '') . "Asignado a: " . $asignado['nombre'];
                }
            }
            
            if ($comentario) {
                $respuesta->agregarComentario([
                    'respuesta_id' => $id,
                    'usuario_id' => $user['id'],
                    'comentario' => $comentario,
                    'tipo' => 'cambio_estatus'
                ]);
            }
            
            header('Location: /respuestas/ver?id=' . $id . '&success=1');
        } else {
            header('Location: /respuestas/ver?id=' . $id . '&error=1');
        }
        exit;
    }

    /**
     * Agregar comentario a respuesta
     */
    public function agregarComentario() {
        $this->auth->requireOperador();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /respuestas');
            exit;
        }

        $respuesta_id = $_POST['respuesta_id'] ?? 0;
        $comentario = $_POST['comentario'] ?? '';
        
        if (empty($comentario)) {
            header('Location: /respuestas/ver?id=' . $respuesta_id . '&error=2');
            exit;
        }
        
        $respuesta = new Respuesta($this->db);
        $user = $this->auth->getCurrentUser();
        
        $data = [
            'respuesta_id' => $respuesta_id,
            'usuario_id' => $user['id'],
            'comentario' => $comentario,
            'tipo' => 'comentario'
        ];
        
        if ($respuesta->agregarComentario($data)) {
            header('Location: /respuestas/ver?id=' . $respuesta_id . '&success=2');
        } else {
            header('Location: /respuestas/ver?id=' . $respuesta_id . '&error=3');
        }
        exit;
    }
}