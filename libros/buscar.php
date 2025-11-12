<?php
session_start();
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

$busqueda = $_GET['q'] ?? '';
$limite = min(10, intval($_GET['limit'] ?? 10));

$resultados = [];

if (strlen($busqueda) >= 2) {
    try {
        $sql = "SELECT id, titulo, autor, isbn, stock,
                (SELECT COUNT(*) FROM prestamos p WHERE p.libro_id = l.id AND p.fecha_devolucion IS NULL) as prestado
                FROM libros l 
                WHERE l.activo = 1 
                AND (l.titulo LIKE ? OR l.autor LIKE ? OR l.isbn LIKE ?)
                ORDER BY l.titulo 
                LIMIT ?";
        
        $busqueda_param = "%$busqueda%";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$busqueda_param, $busqueda_param, $busqueda_param, $limite]);
        
        while ($libro = $stmt->fetch()) {
            $disponible = $libro['stock'] > $libro['prestado'];
            
            $resultados[] = [
                'id' => $libro['id'],
                'titulo' => $libro['titulo'],
                'autor' => $libro['autor'],
                'isbn' => $libro['isbn'],
                'stock' => $libro['stock'],
                'prestado' => $libro['prestado'],
                'disponible' => $disponible,
                'disponibles' => $libro['stock'] - $libro['prestado'],
                'texto_completo' => $libro['titulo'] . ' - ' . $libro['autor'] . ' (ISBN: ' . $libro['isbn'] . ')',
                'estado' => $disponible ? 'Disponible' : 'No disponible'
            ];
        }
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error en la búsqueda']);
        exit();
    }
}

echo json_encode([
    'resultados' => $resultados,
    'total' => count($resultados),
    'busqueda' => $busqueda
]);
?>