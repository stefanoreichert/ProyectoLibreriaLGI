<?php
require_once __DIR__ . '/config/config.php';

echo "<h3>✅ Conexión establecida correctamente con la base de datos</h3>";
echo "Host: " . DB_HOST . "<br>";
echo "Base de datos: " . DB_NAME . "<br>";

$result = $conexion->query("SHOW TABLES");
echo "<h4>Tablas encontradas:</h4>";
while ($row = $result->fetch_array()) {
    echo "- " . $row[0] . "<br>";
}
?>
