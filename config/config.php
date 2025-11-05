<?php
// Configuraciones generales del sistema
define('SITE_NAME', 'Sistema de Librería LGI');
define('SITE_VERSION', '1.0.0');
define('DEVELOPED_BY', 'Equipo LGI');

// Configuraciones de fecha y hora
date_default_timezone_set('America/Mexico_City');

// Configuraciones de préstamos
define('DIAS_PRESTAMO_DEFAULT', 15); // Días por defecto para un préstamo
define('MAX_LIBROS_POR_USUARIO', 3); // Máximo de libros que puede tener un usuario

// Configuraciones de paginación
define('REGISTROS_POR_PAGINA', 10);

// Configuraciones de archivos
define('UPLOAD_PATH', 'assets/uploads/');
define('MAX_FILE_SIZE', 2097152); // 2MB en bytes

// Configuraciones de seguridad
define('SESSION_TIMEOUT', 7200); // 2 horas en segundos

// Configuraciones de la aplicación
$config = [
    'app' => [
        'name' => SITE_NAME,
        'version' => SITE_VERSION,
        'developer' => DEVELOPED_BY,
        'year' => date('Y')
    ],
    'database' => [
        'host' => 'localhost',
        'name' => 'libreria_lgi',
        'charset' => 'utf8mb4'
    ],
    'prestamos' => [
        'dias_default' => DIAS_PRESTAMO_DEFAULT,
        'max_libros' => MAX_LIBROS_POR_USUARIO
    ],
    'pagination' => [
        'per_page' => REGISTROS_POR_PAGINA
    ]
];

// Función para obtener configuración
function getConfig($key = null) {
    global $config;
    
    if ($key === null) {
        return $config;
    }
    
    $keys = explode('.', $key);
    $value = $config;
    
    foreach ($keys as $k) {
        if (isset($value[$k])) {
            $value = $value[$k];
        } else {
            return null;
        }
    }
    
    return $value;
}

// Funciones auxiliares
function formatDate($date, $format = 'd/m/Y') {
    return date($format, strtotime($date));
}

function formatDateTime($datetime, $format = 'd/m/Y H:i') {
    return date($format, strtotime($datetime));
}

function calculateDaysDifference($date1, $date2) {
    $datetime1 = new DateTime($date1);
    $datetime2 = new DateTime($date2);
    $interval = $datetime1->diff($datetime2);
    return $interval->days;
}
?>