<?php
require_once __DIR__ . '/config/config.php'; // Ajusta la ruta según tu proyecto   

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($mysqli->connect_error) {
    die('Error de conexión: ' . $mysqli->connect_error);
}

// Archivo para guardar la fecha de última actualización
$fecha_ultima_actualizacion_file = __DIR__ . '/ultima_actualizacion.txt';
if (file_exists($fecha_ultima_actualizacion_file)) {
    $fecha_ultima = file_get_contents($fecha_ultima_actualizacion_file);
} else {
    $fecha_ultima = '2000-01-01 00:00:00'; // si nunca se ejecutó
}

// Tablas a sincronizar
$tablas = ['usuarios', 'libros', 'categorias', 'prestamos'];

$actualizaciones = [];

foreach ($tablas as $tabla) {
    $sql = "SELECT * FROM $tabla WHERE updated_at >= '$fecha_ultima'";
    $res = $mysqli->query($sql);
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $id = $row['id'];
            unset($row['id']);
            unset($row['updated_at']); // no necesitamos incluirlo en el JSON

            $actualizaciones[] = [
                'tabla' => $tabla,
                'accion' => 'update', // todos los cambios detectados se consideran update
                'id' => $id,
                'campos' => $row
            ];
        }
    }
}

// Guardar JSON
$json_file = __DIR__ . '/actualizaciones.json';
file_put_contents($json_file, json_encode($actualizaciones, JSON_PRETTY_PRINT));

// Actualizar fecha de última ejecución
file_put_contents($fecha_ultima_actualizacion_file, date('Y-m-d H:i:s'));

echo "JSON generado correctamente con " . count($actualizaciones) . " actualizaciones.\n";

$mysqli->close();
