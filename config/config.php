<?php
// -------------------------------------------------------------
// CONFIGURACIÓN GENERAL DEL SISTEMA
// -------------------------------------------------------------
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

// -------------------------------------------------------------
// ARRAY MULTIDIMENSIONAL DE CONFIGURACIÓN
// -------------------------------------------------------------
$config = [
    'app' => [
        'name' => SITE_NAME,
        'version' => SITE_VERSION,
        'developer' => DEVELOPED_BY,
        'year' => date('Y')
    ],
    'database' => [
        'host' => 'localhost',
        'name' => 'libreria',
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

// -------------------------------------------------------------
// FUNCIONES AUXILIARES
// -------------------------------------------------------------
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

// -------------------------------------------------------------
// CONEXIÓN A BASE DE DATOS (LOCAL o RAILWAY)
// -------------------------------------------------------------
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// --- CONEXIÓN DIRECTA A RAILWAY ---
$DB_URL = 'mysql://root:VHOSNLdxIIJJtfRLNxlgGMrAgrYTbJiZ@shortline.proxy.rlwy.net:44928/railway';
$parts = parse_url($DB_URL);

define('DB_HOST', $parts['host']);
define('DB_USER', $parts['user']);
define('DB_PASS', $parts['pass']);
define('DB_NAME', ltrim($parts['path'], '/'));
define('DB_PORT', $parts['port'] ?? 3306);


// Crear conexión
//$conexion = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
//$conexion->set_charset('utf8mb4');

try {
    $pdo = new PDO(
        "mysql:host=".DB_HOST.";port=".DB_PORT.";dbname=".DB_NAME.";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    die("❌ Error de conexión PDO: " . $e->getMessage());
}

// Verificar conexión
//if ($conexion->connect_errno) {
  //  die('❌ Error al conectar con la base de datos: ' . $conexion->connect_error);
//}
?>
