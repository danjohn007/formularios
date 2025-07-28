<?php
/**
 * AuthController - Manejo de autenticación y sesiones
 */

class AuthController {
    private $db;
    private $usuario;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->usuario = new Usuario($this->db);
    }

    /**
     * Mostrar formulario de login
     */
    public function showLogin() {
        if ($this->isAuthenticated()) {
            $this->redirect('/dashboard');
            return;
        }

        $title = 'Iniciar Sesión';
        include 'views/auth/login.php';
    }

    /**
     * Procesar login
     */
    public function processLogin() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/login');
            return;
        }

        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $error = '';

        if (empty($email) || empty($password)) {
            $error = 'Email y contraseña son requeridos';
        } else {
            $user = $this->usuario->login($email, $password);
            
            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['nombre'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['rol'];
                
                $this->redirect('/dashboard');
                return;
            } else {
                $error = 'Credenciales incorrectas';
            }
        }

        $title = 'Iniciar Sesión';
        include 'views/auth/login.php';
    }

    /**
     * Cerrar sesión
     */
    public function logout() {
        session_destroy();
        $this->redirect('/login');
    }

    /**
     * Verificar si el usuario está autenticado
     */
    public function isAuthenticated() {
        return isset($_SESSION['user_id']);
    }

    /**
     * Verificar si el usuario tiene un rol específico
     */
    public function hasRole($role) {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
    }

    /**
     * Verificar si el usuario es admin
     */
    public function isAdmin() {
        return $this->hasRole('admin');
    }

    /**
     * Verificar si el usuario es operador o admin
     */
    public function isOperador() {
        return $this->hasRole('operador') || $this->hasRole('admin');
    }

    /**
     * Middleware para requerir autenticación
     */
    public function requireAuth() {
        if (!$this->isAuthenticated()) {
            $this->redirect('/login');
            exit;
        }
    }

    /**
     * Middleware para requerir rol admin
     */
    public function requireAdmin() {
        $this->requireAuth();
        if (!$this->isAdmin()) {
            http_response_code(403);
            echo "403 - Acceso denegado";
            exit;
        }
    }

    /**
     * Middleware para requerir rol operador o admin
     */
    public function requireOperador() {
        $this->requireAuth();
        if (!$this->isOperador()) {
            http_response_code(403);
            echo "403 - Acceso denegado";
            exit;
        }
    }

    /**
     * Obtener datos del usuario actual
     */
    public function getCurrentUser() {
        if (!$this->isAuthenticated()) {
            return null;
        }

        return [
            'id' => $_SESSION['user_id'],
            'nombre' => $_SESSION['user_name'],
            'email' => $_SESSION['user_email'],
            'rol' => $_SESSION['user_role']
        ];
    }

    /**
     * Redireccionar
     */
    private function redirect($path) {
        $basePath = str_replace('/index.php', '', $_SERVER['SCRIPT_NAME']);
        header('Location: ' . $basePath . $path);
        exit;
    }
}