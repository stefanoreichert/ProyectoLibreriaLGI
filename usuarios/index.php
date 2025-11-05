<?php
session_start();
require_once '../includes/auth.php';
require_once '../config/database.php';

$page_title = 'Gestión de Usuarios';

// Parámetros de búsqueda y paginación
$busqueda = $_GET['busqueda'] ?? '';
$pagina = max(1, intval($_GET['pagina'] ?? 1));
$registros_por_pagina = 10;
$offset = ($pagina - 1) * $registros_por_pagina;

// Construir consulta
$where_clause = "WHERE u.activo = 1";
$params = [];

if (!empty($busqueda)) {
    $where_clause .= " AND (u.nombre_completo LIKE ? OR u.email LIKE ? OR u.telefono LIKE ? OR u.documento LIKE ?)";
    $busqueda_param = "%$busqueda%";
    $params = array_fill(0, 4, $busqueda_param);
}

try {
    // Contar total de registros
    $count_sql = "SELECT COUNT(*) FROM usuarios u $where_clause";
    $stmt = $pdo->prepare($count_sql);
    $stmt->execute($params);
    $total_registros = $stmt->fetchColumn();
    $total_paginas = ceil($total_registros / $registros_por_pagina);
    
    // Obtener usuarios
    $sql = "SELECT u.*, 
            (SELECT COUNT(*) FROM prestamos p WHERE p.usuario_id = u.id AND p.fecha_devolucion IS NULL) as prestamos_activos,
            (SELECT COUNT(*) FROM prestamos p WHERE p.usuario_id = u.id) as total_prestamos
            FROM usuarios u 
            $where_clause 
            ORDER BY u.nombre_completo 
            LIMIT $registros_por_pagina OFFSET $offset";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $usuarios = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $error = "Error al obtener los usuarios: " . $e->getMessage();
    $usuarios = [];
}

include '../includes/header.php';
?>

<div class="page-header">
    <h1>👥 Gestión de Usuarios</h1>
    <?php if (isBibliotecario()): ?>
        <a href="crear.php" class="btn btn-primary">
            <span class="btn-icon">➕</span>
            Nuevo Usuario
        </a>
    <?php endif; ?>
</div>

<!-- Barra de búsqueda -->
<div class="search-section">
    <form method="GET" action="" class="search-form">
        <div class="search-input-group">
            <input type="text" name="busqueda" placeholder="Buscar por nombre, email, teléfono o documento..." 
                   value="<?php echo htmlspecialchars($busqueda); ?>" class="search-input">
            <button type="submit" class="search-btn">🔍 Buscar</button>
            <?php if (!empty($busqueda)): ?>
                <a href="?" class="btn btn-secondary">Limpiar</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<?php if (isset($error)): ?>
    <div class="alert alert-error"><?php echo $error; ?></div>
<?php endif; ?>

<div class="results-info">
    <p>Mostrando <?php echo count($usuarios); ?> de <?php echo $total_registros; ?> usuarios</p>
</div>

<!-- Tabla de usuarios -->
<div class="table-container">
    <table class="data-table">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Email</th>
                <th>Teléfono</th>
                <th>Documento</th>
                <th>Rol</th>
                <th>Préstamos</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($usuarios)): ?>
                <tr>
                    <td colspan="8" class="text-center">
                        <?php if (!empty($busqueda)): ?>
                            No se encontraron usuarios que coincidan con "<?php echo htmlspecialchars($busqueda); ?>"
                        <?php else: ?>
                            No hay usuarios registrados
                        <?php endif; ?>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($usuarios as $usuario): ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($usuario['nombre_completo']); ?></strong>
                            <br><small class="text-muted">@<?php echo htmlspecialchars($usuario['usuario']); ?></small>
                        </td>
                        <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                        <td><?php echo htmlspecialchars($usuario['telefono'] ?: 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($usuario['documento'] ?: 'N/A'); ?></td>
                        <td>
                            <span class="role-badge role-<?php echo $usuario['rol']; ?>">
                                <?php echo ucfirst($usuario['rol']); ?>
                            </span>
                        </td>
                        <td>
                            <div class="prestamos-info">
                                <span class="prestamos-activos">
                                    <?php echo $usuario['prestamos_activos']; ?> activos
                                </span>
                                <br>
                                <small class="text-muted">
                                    <?php echo $usuario['total_prestamos']; ?> total
                                </small>
                            </div>
                        </td>
                        <td>
                            <span class="status-badge status-active">Activo</span>
                        </td>
                        <td class="actions">
                            <a href="detalle.php?id=<?php echo $usuario['id']; ?>" class="btn btn-sm btn-info" title="Ver detalle">
                                👁️
                            </a>
                            <?php if (isBibliotecario()): ?>
                                <a href="editar.php?id=<?php echo $usuario['id']; ?>" class="btn btn-sm btn-secondary" title="Editar">
                                    ✏️
                                </a>
                            <?php endif; ?>
                            <?php if (isAdmin() && $usuario['id'] != $_SESSION['user_id']): ?>
                                <a href="eliminar.php?id=<?php echo $usuario['id']; ?>" class="btn btn-sm btn-danger" 
                                   title="Eliminar" onclick="return confirm('¿Está seguro de eliminar este usuario?')">
                                    🗑️
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Paginación -->
<?php if ($total_paginas > 1): ?>
    <div class="pagination">
        <?php if ($pagina > 1): ?>
            <a href="?pagina=<?php echo $pagina - 1; ?>&busqueda=<?php echo urlencode($busqueda); ?>" 
               class="pagination-btn">« Anterior</a>
        <?php endif; ?>
        
        <?php for ($i = max(1, $pagina - 2); $i <= min($total_paginas, $pagina + 2); $i++): ?>
            <a href="?pagina=<?php echo $i; ?>&busqueda=<?php echo urlencode($busqueda); ?>" 
               class="pagination-btn <?php echo $i === $pagina ? 'active' : ''; ?>">
                <?php echo $i; ?>
            </a>
        <?php endfor; ?>
        
        <?php if ($pagina < $total_paginas): ?>
            <a href="?pagina=<?php echo $pagina + 1; ?>&busqueda=<?php echo urlencode($busqueda); ?>" 
               class="pagination-btn">Siguiente »</a>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php 
$include_search_js = true;
include '../includes/footer.php'; 
?>