<?php
/**
 * FormularioController - Gestión de formularios
 */

class FormularioController {
    private $db;
    private $auth;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->auth = new AuthController();
    }

    /**
     * Listar formularios
     */
    public function index() {
        $this->auth->requireOperador();
        
        $formulario = new Formulario($this->db);
        $formularios = $formulario->obtenerTodos();
        
        // Pass user data and permissions to view
        $user = $this->auth->getCurrentUser();
        $isOperador = $this->auth->isOperador();
        
        $title = 'Formularios';
        include 'views/formularios/index.php';
    }

    /**
     * Mostrar formulario para crear nuevo formulario
     */
    public function crear() {
        $this->auth->requireOperador();
        
        // Pass user data and permissions to view
        $user = $this->auth->getCurrentUser();
        $isOperador = $this->auth->isOperador();
        
        $title = 'Crear Formulario';
        include 'views/formularios/crear.php';
    }

    /**
     * Guardar nuevo formulario
     */
    public function guardar() {
        $this->auth->requireOperador();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /formularios');
            exit;
        }

        $user = $this->auth->getCurrentUser();
        $formulario = new Formulario($this->db);
        
        $data = [
            'titulo' => $_POST['titulo'] ?? '',
            'descripcion' => $_POST['descripcion'] ?? '',
            'tipo' => $_POST['tipo'] ?? 'servicio',
            'usuario_id' => $user['id'],
            'configuracion' => json_encode($_POST['configuracion'] ?? [])
        ];

        if ($formulario->crear($data)) {
            header('Location: /formularios?success=1');
        } else {
            header('Location: /formularios/crear?error=1');
        }
        exit;
    }

    /**
     * Editar formulario
     */
    public function editar() {
        $this->auth->requireOperador();
        
        $id = $_GET['id'] ?? 0;
        $formulario = new Formulario($this->db);
        $form = $formulario->obtenerPorId($id);
        
        if (!$form) {
            header('Location: /formularios');
            exit;
        }

        $title = 'Editar Formulario';
        include 'views/formularios/editar.php';
    }

    /**
     * Mostrar formularios disponibles para responder
     */
    public function responder() {
        $this->auth->requireAuth();
        
        $formulario = new Formulario($this->db);
        $formularios = $formulario->obtenerActivos();
        
        $id = $_GET['id'] ?? null;
        $formSeleccionado = null;
        
        if ($id) {
            $formSeleccionado = $formulario->obtenerPorId($id);
            if ($formSeleccionado) {
                $campo = new Campo($this->db);
                $formSeleccionado['campos'] = $campo->obtenerPorFormulario($id);
            }
        }
        
        $title = 'Responder Formulario';
        include 'views/formularios/responder.php';
    }

    /**
     * Procesar respuesta de formulario
     */
    public function procesarRespuesta() {
        $this->auth->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /formularios/responder');
            exit;
        }

        $user = $this->auth->getCurrentUser();
        $formulario_id = $_POST['formulario_id'] ?? 0;
        $datos = $_POST['datos'] ?? [];
        
        $respuesta = new Respuesta($this->db);
        
        $data = [
            'formulario_id' => $formulario_id,
            'usuario_id' => $user['id'],
            'datos' => json_encode($datos),
            'total' => $this->calcularTotal($datos)
        ];

        if ($respuesta->crear($data)) {
            header('Location: /formularios/responder?success=1');
        } else {
            header('Location: /formularios/responder?error=1');
        }
        exit;
    }

    /**
     * Calcular total basado en productos seleccionados
     */
    private function calcularTotal($datos) {
        $total = 0;
        
        if (isset($datos['productos'])) {
            $producto = new Producto($this->db);
            foreach ($datos['productos'] as $prod_id => $cantidad) {
                $prod = $producto->obtenerPorId($prod_id);
                if ($prod && $cantidad > 0) {
                    $total += $prod['precio'] * $cantidad;
                }
            }
        }
        
        return $total;
    }

    /**
     * API: Obtener campos de formulario
     */
    public function getCampos() {
        $this->auth->requireAuth();
        
        $formulario_id = $_GET['formulario_id'] ?? 0;
        $campo = new Campo($this->db);
        $campos = $campo->obtenerPorFormulario($formulario_id);
        
        header('Content-Type: application/json');
        echo json_encode($campos);
    }
}