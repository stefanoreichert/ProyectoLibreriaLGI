<?php
// Incluir archivos de configuración si no están incluidos
if (!isset($pdo)) {
    require_once __DIR__ . '/../config/config.php';


}

if (!defined('SITE_NAME')) {
    require_once(__DIR__ . '/../config/config.php');
}

// No verificar sesión automáticamente aquí, lo harán las funciones específicas

// Actualizar última actividad
$_SESSION['last_activity'] = time();

// Función para verificar rol de usuario
function hasPermission($required_role) {
    $roles_hierarchy = [
        'usuario' => 1,
        'bibliotecario' => 2,
        'admin' => 3
    ];
    
    $user_level = $roles_hierarchy[$_SESSION['rol']] ?? 0;
    $required_level = $roles_hierarchy[$required_role] ?? 999;
    
    return $user_level >= $required_level;
}

// Función para verificar si es admin
function isAdmin() {
    return $_SESSION['rol'] === 'admin';
}

// Función para verificar si es bibliotecario o superior
function isBibliotecario() {
    return in_array($_SESSION['rol'], ['bibliotecario', 'admin']);
}

// Función para obtener información del usuario actual
function getCurrentUser() {
    return [
        'id' => $_SESSION['user_id'],
        'usuario' => $_SESSION['usuario'],
        'nombre' => $_SESSION['nombre'],
        'rol' => $_SESSION['rol']
    ];
}

// Función para verificar sesión (sin redirección automática)
function verificarSesion() {
    if (!isset($_SESSION['user_id'])) {
        $login_url = (strpos($_SERVER['REQUEST_URI'], '/ProyectoLibreriaLGI/') === false) ? '../login.php' : 'login.php';
        header('Location: ' . $login_url);
        exit();
    }
    
    // Verificar timeout (usar valor por defecto si no existe la constante)
    $timeout = defined('SESSION_TIMEOUT') ? SESSION_TIMEOUT : 1800; // 30 minutos por defecto
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
        session_unset();
        session_destroy();
        $login_url = (strpos($_SERVER['REQUEST_URI'], '/ProyectoLibreriaLGI/') === false) ? '../login.php' : 'login.php';
        header('Location: ' . $login_url . '?timeout=1');
        exit();
    }
    
    $_SESSION['last_activity'] = time();
}

// Función para verificar rol específico
function verificarRol($roles_permitidos) {
    verificarSesion(); // Verificar sesión primero
    
    if (!isset($_SESSION['rol']) || !in_array($_SESSION['rol'], $roles_permitidos)) {
        header('HTTP/1.0 403 Forbidden');
        die('Acceso denegado. No tienes permisos para acceder a esta página.');
    }
}

// Función para registrar logs del sistema
function registrarLog($usuario_id, $modulo, $accion, $descripcion = '') {
    global $pdo;
    
    try {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        
        $stmt = $pdo->prepare("INSERT INTO logs_sistema (usuario_id, modulo, accion, descripcion, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$usuario_id, $modulo, $accion, $descripcion, $ip, $user_agent]);
    } catch (Exception $e) {
        // Log silencioso - no interrumpir el flujo de la aplicación
        error_log("Error al registrar log: " . $e->getMessage());
    }
}

// Función para cargar configuración desde la base de datos
function cargarConfiguracion() {
    global $pdo;
    
    // Verificar que $pdo esté disponible
    if (!isset($pdo) || $pdo === null) {
        // Usar valores por defecto si no hay conexión
        if (!defined('MAX_LIBROS_POR_USUARIO')) define('MAX_LIBROS_POR_USUARIO', 3);
        if (!defined('DIAS_PRESTAMO_DEFAULT')) define('DIAS_PRESTAMO_DEFAULT', 15);
        if (!defined('SESSION_TIMEOUT_CONFIG')) define('SESSION_TIMEOUT_CONFIG', 1800);
        return;
    }
    
    try {
        $stmt = $pdo->query("SELECT clave, valor FROM configuracion");
        while ($row = $stmt->fetch()) {
            switch ($row['clave']) {
                case 'max_libros_usuario':
                    // Solo definir si la constante no existe (evitar redefinición)
                    if (!defined('MAX_LIBROS_USUARIO_DB')) {
                        define('MAX_LIBROS_USUARIO_DB', (int)$row['valor']);
                    }
                    break;
                case 'dias_prestamo_defecto':
                    if (!defined('DIAS_PRESTAMO_DB')) {
                        define('DIAS_PRESTAMO_DB', (int)$row['valor']);
                    }
                    break;
                case 'session_timeout':
                    if (!defined('SESSION_TIMEOUT_DB')) {
                        define('SESSION_TIMEOUT_DB', (int)$row['valor'] * 60); // convertir minutos a segundos
                    }
                    break;
            }
        }
    } catch (Exception $e) {
        // Si hay error, las constantes ya están definidas en config.php
        // No hacer nada aquí
    }
}

// Cargar configuración al incluir este archivo
cargarConfiguracion();
?>