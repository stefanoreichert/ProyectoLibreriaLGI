<?php
// Verificar si hay sesión activa
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . ($_SERVER['REQUEST_URI'] === '/ProyectoLibreriaLGI/' ? '' : '../') . 'login.php');
    exit();
}

// Verificar timeout de sesión
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
    session_unset();
    session_destroy();
    header('Location: ' . ($_SERVER['REQUEST_URI'] === '/ProyectoLibreriaLGI/' ? '' : '../') . 'login.php?timeout=1');
    exit();
}

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
?>