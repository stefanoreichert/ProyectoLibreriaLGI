<?php
require_once __DIR__ . '/config/config.php'; // Ajusta la ruta según tu proyecto

// --- Seguridad opcional (para evitar que cualquiera lo ejecute desde navegador) ---
$token = isset($_GET['token']) ? $_GET['token'] : '';
if ($token !== 'TU_TOKEN_SECRETO') {
    http_response_code(403);
    die('Acceso denegado');
}

// Ruta del JSON de actualizaciones
$json_file = __DIR__ . '/actualizaciones.json';
if (!file_exists($json_file)) {
    die('Archivo de actualizaciones no encontrado.');
}

// Leer JSON
$actualizaciones = json_decode(file_get_contents($json_file), true);
if (!$actualizaciones) {
    die('JSON inválido.');
}

// Conectar a la base de datos
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($mysqli->connect_error) {
    die('Error de conexión: ' . $mysqli->connect_error);
}

// Aplicar cada actualización
foreach ($actualizaciones as $act) {
    $tabla = $mysqli->real_escape_string($act['tabla']);
    $accion = $act['accion'];
    $campos = $act['campos'];

    if ($accion === 'insert') {
        $cols = implode(',', array_keys($campos));
        $vals = implode(',', array_map(function($v) use ($mysqli) {
            return is_null($v) ? 'NULL' : "'" . $mysqli->real_escape_string($v) . "'";
        }, $campos));
        $sql = "INSERT INTO $tabla ($cols) VALUES ($vals)";
    } elseif ($accion === 'update' && isset($act['id'])) {
        $set = [];
        foreach ($campos as $k => $v) {
            $set[] = $k . "=" . (is_null($v) ? 'NULL' : "'" . $mysqli->real_escape_string($v) . "'");
        }
        $sql = "UPDATE $tabla SET " . implode(',', $set) . " WHERE id=" . intval($act['id']);
    } elseif ($accion === 'delete' && isset($act['id'])) {
        $sql = "DELETE FROM $tabla WHERE id=" . intval($act['id']);
    } else {
        continue; // ignorar acciones inválidas
    }

    if (!$mysqli->query($sql)) {
        echo "Error en $accion tabla $tabla: " . $mysqli->error . "\n";
    }
}

// Cerrar conexión
$mysqli->close();

echo "Actualizaciones aplicadas correctamente.\n";

