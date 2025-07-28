<?php
/**
 * Sistema de Formularios - Punto de entrada principal
 * PHP 8.2 + MySQL 5.7
 */

session_start();

// Error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database configuration
require_once 'config/database.php';

// Simple autoloader for classes
spl_autoload_register(function ($class) {
    $directories = ['controllers', 'models'];
    
    foreach ($directories as $directory) {
        $file = __DIR__ . '/' . $directory . '/' . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Simple router
class Router {
    private $routes = [];
    
    public function get($path, $callback) {
        $this->routes['GET'][$path] = $callback;
    }
    
    public function post($path, $callback) {
        $this->routes['POST'][$path] = $callback;
    }
    
    public function dispatch() {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Remove base path if running in subdirectory
        $basePath = str_replace('/index.php', '', $_SERVER['SCRIPT_NAME']);
        if ($basePath && strpos($path, $basePath) === 0) {
            $path = substr($path, strlen($basePath));
        }
        
        if (!$path || $path === '/') {
            $path = '/dashboard';
        }
        
        if (isset($this->routes[$method][$path])) {
            call_user_func($this->routes[$method][$path]);
        } else {
            $this->handle404();
        }
    }
    
    private function handle404() {
        http_response_code(404);
        echo "404 - Página no encontrada";
    }
}

// Initialize router
$router = new Router();

// Authentication routes
$router->get('/login', function() {
    $auth = new AuthController();
    $auth->showLogin();
});

$router->post('/login', function() {
    $auth = new AuthController();
    $auth->processLogin();
});

$router->get('/logout', function() {
    $auth = new AuthController();
    $auth->logout();
});

// Dashboard routes
$router->get('/dashboard', function() {
    $dashboard = new DashboardController();
    $dashboard->index();
});

// Form routes
$router->get('/formularios', function() {
    $formulario = new FormularioController();
    $formulario->index();
});

$router->get('/formularios/crear', function() {
    $formulario = new FormularioController();
    $formulario->crear();
});

$router->post('/formularios/guardar', function() {
    $formulario = new FormularioController();
    $formulario->guardar();
});

$router->get('/formularios/editar', function() {
    $formulario = new FormularioController();
    $formulario->editar();
});

$router->get('/formularios/responder', function() {
    $formulario = new FormularioController();
    $formulario->responder();
});

$router->post('/formularios/responder', function() {
    $formulario = new FormularioController();
    $formulario->procesarRespuesta();
});

// Response routes
$router->get('/respuestas', function() {
    $respuesta = new RespuestaController();
    $respuesta->index();
});

$router->get('/respuestas/ver', function() {
    $respuesta = new RespuestaController();
    $respuesta->ver();
});

$router->post('/respuestas/actualizar-estatus', function() {
    $respuesta = new RespuestaController();
    $respuesta->actualizarEstatus();
});

$router->post('/respuestas/agregar-comentario', function() {
    $respuesta = new RespuestaController();
    $respuesta->agregarComentario();
});

// Installation routes
$router->get('/install', function() {
    include 'install.php';
});

$router->post('/install', function() {
    include 'install.php';
});

// API routes for AJAX calls
$router->get('/api/dashboard/stats', function() {
    $dashboard = new DashboardController();
    $dashboard->getStats();
});

$router->get('/api/formularios/campos', function() {
    $formulario = new FormularioController();
    $formulario->getCampos();
});

// Dispatch the router
$router->dispatch();