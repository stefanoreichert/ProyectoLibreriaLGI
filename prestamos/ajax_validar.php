<?php
session_start();
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'buscar_usuarios':
            buscarUsuarios();
            break;
            
        case 'buscar_libros':
            buscarLibros();
            break;
            
        case 'get_usuario':
            getUsuario();
            break;
            
        case 'get_libro':
            getLibro();
            break;
            
        case 'validar_isbn':
            validarISBN();
            break;
            
        case 'validar_email':
            validarEmail();
            break;
            
        case 'validar_usuario':
            validarUsuario();
            break;
            
        case 'check_disponibilidad':
            checkDisponibilidad();
            break;
            
        default:
            throw new Exception('Acción no válida');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}

function buscarUsuarios() {
    global $pdo;
    
    $query = $_GET['q'] ?? '';
    $limite = min(10, intval($_GET['limit'] ?? 10));
    
    if (strlen($query) < 2) {
        echo json_encode(['resultados' => [], 'total' => 0]);
        return;
    }
    
    $sql = "SELECT u.id, u.nombre_completo, u.email, u.telefono,
            (SELECT COUNT(*) FROM prestamos p WHERE p.usuario_id = u.id AND p.fecha_devolucion IS NULL) as prestamos_activos
            FROM usuarios u 
            WHERE u.activo = 1 
            AND (u.nombre_completo LIKE ? OR u.email LIKE ? OR u.documento LIKE ?)
            ORDER BY u.nombre_completo 
            LIMIT ?";
    
    $busqueda_param = "%$query%";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$busqueda_param, $busqueda_param, $busqueda_param, $limite]);
    
    $resultados = [];
    while ($usuario = $stmt->fetch()) {
        $resultados[] = [
            'id' => $usuario['id'],
            'nombre' => $usuario['nombre_completo'],
            'email' => $usuario['email'],
            'telefono' => $usuario['telefono'],
            'prestamos_activos' => (int)$usuario['prestamos_activos'],
            'puede_prestar' => $usuario['prestamos_activos'] < MAX_LIBROS_POR_USUARIO
        ];
    }
    
    echo json_encode([
        'resultados' => $resultados,
        'total' => count($resultados)
    ]);
}

function buscarLibros() {
    global $pdo;
    
    $query = $_GET['q'] ?? '';
    $limite = min(10, intval($_GET['limit'] ?? 10));
    
    if (strlen($query) < 2) {
        echo json_encode(['resultados' => [], 'total' => 0]);
        return;
    }
    
    $sql = "SELECT l.id, l.titulo, l.autor, l.isbn, l.stock,
            (SELECT COUNT(*) FROM prestamos p WHERE p.libro_id = l.id AND p.fecha_devolucion IS NULL) as prestados
            FROM libros l 
            WHERE l.activo = 1 
            AND (l.titulo LIKE ? OR l.autor LIKE ? OR l.isbn LIKE ?)
            ORDER BY l.titulo 
            LIMIT ?";
    
    $busqueda_param = "%$query%";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$busqueda_param, $busqueda_param, $busqueda_param, $limite]);
    
    $resultados = [];
    while ($libro = $stmt->fetch()) {
        $disponibles = $libro['stock'] - $libro['prestados'];
        
        $resultados[] = [
            'id' => $libro['id'],
            'titulo' => $libro['titulo'],
            'autor' => $libro['autor'],
            'isbn' => $libro['isbn'],
            'stock' => (int)$libro['stock'],
            'prestados' => (int)$libro['prestados'],
            'disponibles' => $disponibles,
            'disponible' => $disponibles > 0
        ];
    }
    
    echo json_encode([
        'resultados' => $resultados,
        'total' => count($resultados)
    ]);
}

function getUsuario() {
    global $pdo;
    
    $id = intval($_GET['id'] ?? 0);
    
    if ($id <= 0) {
        throw new Exception('ID de usuario no válido');
    }
    
    $stmt = $pdo->prepare("
        SELECT u.id, u.nombre_completo, u.email, u.telefono,
        (SELECT COUNT(*) FROM prestamos p WHERE p.usuario_id = u.id AND p.fecha_devolucion IS NULL) as prestamos_activos
        FROM usuarios u 
        WHERE u.id = ? AND u.activo = 1
    ");
    $stmt->execute([$id]);
    $usuario = $stmt->fetch();
    
    if (!$usuario) {
        throw new Exception('Usuario no encontrado');
    }
    
    echo json_encode([
        'usuario' => [
            'id' => $usuario['id'],
            'nombre' => $usuario['nombre_completo'],
            'email' => $usuario['email'],
            'telefono' => $usuario['telefono'],
            'prestamos_activos' => (int)$usuario['prestamos_activos'],
            'puede_prestar' => $usuario['prestamos_activos'] < MAX_LIBROS_POR_USUARIO
        ]
    ]);
}

function getLibro() {
    global $pdo;
    
    $id = intval($_GET['id'] ?? 0);
    
    if ($id <= 0) {
        throw new Exception('ID de libro no válido');
    }
    
    $stmt = $pdo->prepare("
        SELECT l.id, l.titulo, l.autor, l.isbn, l.stock,
        (SELECT COUNT(*) FROM prestamos p WHERE p.libro_id = l.id AND p.fecha_devolucion IS NULL) as prestados
        FROM libros l 
        WHERE l.id = ? AND l.activo = 1
    ");
    $stmt->execute([$id]);
    $libro = $stmt->fetch();
    
    if (!$libro) {
        throw new Exception('Libro no encontrado');
    }
    
    $disponibles = $libro['stock'] - $libro['prestados'];
    
    echo json_encode([
        'libro' => [
            'id' => $libro['id'],
            'titulo' => $libro['titulo'],
            'autor' => $libro['autor'],
            'isbn' => $libro['isbn'],
            'stock' => (int)$libro['stock'],
            'prestados' => (int)$libro['prestados'],
            'disponibles' => $disponibles,
            'disponible' => $disponibles > 0
        ]
    ]);
}

function validarISBN() {
    global $pdo;
    
    $isbn = $_GET['isbn'] ?? '';
    $excluir_id = intval($_GET['excluir_id'] ?? 0);
    
    if (empty($isbn)) {
        echo json_encode(['valido' => false, 'mensaje' => 'ISBN requerido']);
        return;
    }
    
    // Validar formato de ISBN
    $isbn_limpio = preg_replace('/[^0-9X]/', '', $isbn);
    if (strlen($isbn_limpio) !== 10 && strlen($isbn_limpio) !== 13) {
        echo json_encode(['valido' => false, 'mensaje' => 'Formato de ISBN no válido']);
        return;
    }
    
    // Verificar si ya existe
    $sql = "SELECT id FROM libros WHERE isbn = ? AND activo = 1";
    $params = [$isbn];
    
    if ($excluir_id > 0) {
        $sql .= " AND id != ?";
        $params[] = $excluir_id;
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    if ($stmt->fetch()) {
        echo json_encode(['valido' => false, 'mensaje' => 'Este ISBN ya existe']);
    } else {
        echo json_encode(['valido' => true, 'mensaje' => 'ISBN disponible']);
    }
}

function validarEmail() {
    global $pdo;
    
    $email = $_GET['email'] ?? '';
    $excluir_id = intval($_GET['excluir_id'] ?? 0);
    
    if (empty($email)) {
        echo json_encode(['valido' => false, 'mensaje' => 'Email requerido']);
        return;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['valido' => false, 'mensaje' => 'Formato de email no válido']);
        return;
    }
    
    // Verificar si ya existe
    $sql = "SELECT id FROM usuarios WHERE email = ? AND activo = 1";
    $params = [$email];
    
    if ($excluir_id > 0) {
        $sql .= " AND id != ?";
        $params[] = $excluir_id;
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    if ($stmt->fetch()) {
        echo json_encode(['valido' => false, 'mensaje' => 'Este email ya está registrado']);
    } else {
        echo json_encode(['valido' => true, 'mensaje' => 'Email disponible']);
    }
}

function validarUsuario() {
    global $pdo;
    
    $usuario = $_GET['usuario'] ?? '';
    $excluir_id = intval($_GET['excluir_id'] ?? 0);
    
    if (empty($usuario)) {
        echo json_encode(['valido' => false, 'mensaje' => 'Nombre de usuario requerido']);
        return;
    }
    
    if (strlen($usuario) < 3) {
        echo json_encode(['valido' => false, 'mensaje' => 'El nombre de usuario debe tener al menos 3 caracteres']);
        return;
    }
    
    if (!preg_match('/^[a-zA-Z0-9._-]+$/', $usuario)) {
        echo json_encode(['valido' => false, 'mensaje' => 'Solo se permiten letras, números, puntos, guiones y guiones bajos']);
        return;
    }
    
    // Verificar si ya existe
    $sql = "SELECT id FROM usuarios WHERE usuario = ? AND activo = 1";
    $params = [$usuario];
    
    if ($excluir_id > 0) {
        $sql .= " AND id != ?";
        $params[] = $excluir_id;
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    if ($stmt->fetch()) {
        echo json_encode(['valido' => false, 'mensaje' => 'Este nombre de usuario ya existe']);
    } else {
        echo json_encode(['valido' => true, 'mensaje' => 'Nombre de usuario disponible']);
    }
}

function checkDisponibilidad() {
    global $pdo;
    
    $libro_id = intval($_GET['libro_id'] ?? 0);
    $usuario_id = intval($_GET['usuario_id'] ?? 0);
    
    if ($libro_id <= 0) {
        throw new Exception('ID de libro no válido');
    }
    
    // Verificar disponibilidad del libro
    $stmt = $pdo->prepare("
        SELECT l.titulo, l.stock,
        (SELECT COUNT(*) FROM prestamos p WHERE p.libro_id = l.id AND p.fecha_devolucion IS NULL) as prestados
        FROM libros l 
        WHERE l.id = ? AND l.activo = 1
    ");
    $stmt->execute([$libro_id]);
    $libro = $stmt->fetch();
    
    if (!$libro) {
        throw new Exception('Libro no encontrado');
    }
    
    $disponibles = $libro['stock'] - $libro['prestados'];
    
    $response = [
        'disponible' => $disponibles > 0,
        'titulo' => $libro['titulo'],
        'stock' => (int)$libro['stock'],
        'prestados' => (int)$libro['prestados'],
        'disponibles' => $disponibles
    ];
    
    // Si se proporciona usuario, verificar restricciones adicionales
    if ($usuario_id > 0) {
        // Verificar límite de préstamos del usuario
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM prestamos WHERE usuario_id = ? AND fecha_devolucion IS NULL");
        $stmt->execute([$usuario_id]);
        $prestamos_activos = $stmt->fetchColumn();
        
        if ($prestamos_activos >= MAX_LIBROS_POR_USUARIO) {
            $response['disponible'] = false;
            $response['razon'] = 'Usuario ha alcanzado el límite máximo de préstamos';
        }
        
        // Verificar si el usuario ya tiene este libro
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM prestamos WHERE usuario_id = ? AND libro_id = ? AND fecha_devolucion IS NULL");
        $stmt->execute([$usuario_id, $libro_id]);
        if ($stmt->fetchColumn() > 0) {
            $response['disponible'] = false;
            $response['razon'] = 'El usuario ya tiene este libro prestado';
        }
        
        $response['prestamos_activos'] = (int)$prestamos_activos;
        $response['limite_prestamos'] = MAX_LIBROS_POR_USUARIO;
    }
    
    echo json_encode($response);
}
?>