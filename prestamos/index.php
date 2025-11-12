<?php
session_start();
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/config.php';

$page_title = 'Gestión de Préstamos';

// Parámetros de búsqueda y filtros
$busqueda = $_GET['busqueda'] ?? '';
$filtro_estado = $_GET['estado'] ?? 'activos';
$pagina = max(1, intval($_GET['pagina'] ?? 1));
$registros_por_pagina = 15;
$offset = ($pagina - 1) * $registros_por_pagina;

// Construir consulta base
$where_conditions = [];
$params = [];

// Si es usuario normal, solo ver sus préstamos
if ($_SESSION['rol'] === 'usuario') {
    $where_conditions[] = "p.usuario_id = ?";
    $params[] = $_SESSION['user_id'];
}

// Filtro por estado
switch ($filtro_estado) {
    case 'activos':
        $where_conditions[] = "p.fecha_dev_real IS NULL";
        break;
    case 'vencidos':
        $where_conditions[] = "p.fecha_dev_real IS NULL AND p.fecha_devolucion < CURDATE()";
        break;
    case 'devueltos':
        $where_conditions[] = "p.fecha_dev_real IS NOT NULL";
        break;
    case 'todos':
        // Sin filtro adicional
        break;
}

// Filtro por búsqueda
if (!empty($busqueda)) {
    $where_conditions[] = "(l.titulo LIKE ? OR l.autor LIKE ? OR u.nombre_completo LIKE ? OR l.isbn LIKE ?)";
    $busqueda_param = "%$busqueda%";
    $params = array_merge($params, array_fill(0, 4, $busqueda_param));
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

try {
    // Contar total de registros
    $count_sql = "SELECT COUNT(*) FROM prestamos p 
                  JOIN libros l ON p.libro_id = l.id 
                  JOIN usuarios u ON p.usuario_id = u.id 
                  $where_clause";
    $stmt = $pdo->prepare($count_sql);
    $stmt->execute($params);
    $total_registros = $stmt->fetchColumn();
    $total_paginas = ceil($total_registros / $registros_por_pagina);
    
    // Obtener préstamos
    $sql = "SELECT p.*, l.titulo, l.autor, l.isbn, u.nombre_completo as usuario_nombre,
            CASE 
                WHEN p.fecha_dev_real IS NOT NULL THEN 'devuelto'
                WHEN p.fecha_devolucion < CURDATE() THEN 'vencido'
                ELSE 'activo'
            END as estado_prestamo,
            DATEDIFF(CURDATE(), p.fecha_devolucion) as dias_vencido
            FROM prestamos p
            JOIN libros l ON p.libro_id = l.id
            JOIN usuarios u ON p.usuario_id = u.id
            $where_clause
            ORDER BY 
                CASE WHEN p.fecha_dev_real IS NULL THEN 0 ELSE 1 END,
                p.fecha_devolucion ASC,
                p.fecha_prestamo DESC
            LIMIT $registros_por_pagina OFFSET $offset";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $prestamos = $stmt->fetchAll();
    
    // Obtener estadísticas generales
    $stats_sql = "SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN fecha_dev_real IS NULL THEN 1 ELSE 0 END) as activos,
        SUM(CASE WHEN fecha_dev_real IS NULL AND fecha_devolucion < CURDATE() THEN 1 ELSE 0 END) as vencidos,
        SUM(CASE WHEN fecha_dev_real IS NOT NULL THEN 1 ELSE 0 END) as devueltos
        FROM prestamos";
    $stmt = $pdo->query($stats_sql);
    $estadisticas = $stmt->fetch();
    
} catch (PDOException $e) {
    $error = "Error al obtener los préstamos: " . $e->getMessage();
    $prestamos = [];
    $estadisticas = ['total' => 0, 'activos' => 0, 'vencidos' => 0, 'devueltos' => 0];
}

include '../includes/header.php';
?>

<div class="page-header">
    <h1>📋 <?php echo $_SESSION['rol'] === 'usuario' ? 'Mis Préstamos' : 'Gestión de Préstamos'; ?></h1>
    <?php if (isBibliotecario()): ?>
        <div class="header-actions">
            <a href="nuevo.php" class="btn btn-primary">
                <span class="btn-icon">➕</span>
                Nuevo Préstamo
            </a>
        </div>
    <?php endif; ?>
</div>

<!-- Estadísticas rápidas -->
<div class="stats-summary">
    <div class="stat-item">
        <span class="stat-number"><?php echo $estadisticas['total']; ?></span>
        <span class="stat-label">Total</span>
    </div>
    <div class="stat-item active">
        <span class="stat-number"><?php echo $estadisticas['activos']; ?></span>
        <span class="stat-label">Activos</span>
    </div>
    <div class="stat-item warning">
        <span class="stat-number"><?php echo $estadisticas['vencidos']; ?></span>
        <span class="stat-label">Vencidos</span>
    </div>
    <div class="stat-item success">
        <span class="stat-number"><?php echo $estadisticas['devueltos']; ?></span>
        <span class="stat-label">Devueltos</span>
    </div>
</div>

<!-- Filtros y búsqueda -->
<div class="filters-section">
    <form method="GET" action="" class="filters-form">
        <div class="filter-group">
            <label for="estado">Estado:</label>
            <select name="estado" id="estado" onchange="this.form.submit()">
                <option value="activos" <?php echo $filtro_estado === 'activos' ? 'selected' : ''; ?>>Préstamos Activos</option>
                <option value="vencidos" <?php echo $filtro_estado === 'vencidos' ? 'selected' : ''; ?>>Préstamos Vencidos</option>
                <option value="devueltos" <?php echo $filtro_estado === 'devueltos' ? 'selected' : ''; ?>>Préstamos Devueltos</option>
                <option value="todos" <?php echo $filtro_estado === 'todos' ? 'selected' : ''; ?>>Todos los Préstamos</option>
            </select>
        </div>
        
        <div class="search-group">
            <input type="text" name="busqueda" placeholder="Buscar libro, usuario o ISBN..." 
                   value="<?php echo htmlspecialchars($busqueda); ?>" class="search-input">
            <button type="submit" class="search-btn">🔍</button>
            <?php if (!empty($busqueda)): ?>
                <a href="?estado=<?php echo urlencode($filtro_estado); ?>" class="btn btn-secondary btn-sm">Limpiar</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<?php if (isset($error)): ?>
    <div class="alert alert-error"><?php echo $error; ?></div>
<?php endif; ?>

<div class="results-info">
    <p>Mostrando <?php echo count($prestamos); ?> de <?php echo $total_registros; ?> préstamos</p>
</div>

<!-- Tabla de préstamos -->
<div class="table-container">
    <table class="data-table">
        <thead>
            <tr>
                <th>Libro</th>
                <th>Usuario</th>
                <th>F. Préstamo</th>
                <th>F. Límite</th>
                <th>F. Devolución</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($prestamos)): ?>
                <tr>
                    <td colspan="7" class="text-center">
                        <?php if (!empty($busqueda)): ?>
                            No se encontraron préstamos que coincidan con "<?php echo htmlspecialchars($busqueda); ?>"
                        <?php else: ?>
                            No hay préstamos registrados con el filtro seleccionado
                        <?php endif; ?>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($prestamos as $prestamo): ?>
                    <tr class="<?php echo $prestamo['estado_prestamo'] === 'vencido' ? 'row-warning' : ''; ?>">
                        <td>
                            <strong><?php echo htmlspecialchars($prestamo['titulo']); ?></strong>
                            <br><small class="text-muted">
                                <?php echo htmlspecialchars($prestamo['autor']); ?>
                                <br>ISBN: <?php echo htmlspecialchars($prestamo['isbn']); ?>
                            </small>
                        </td>
                        <td><?php echo htmlspecialchars($prestamo['usuario_nombre']); ?></td>
                        <td><?php echo formatDate($prestamo['fecha_prestamo']); ?></td>
                        <td>
                            <?php echo formatDate($prestamo['fecha_devolucion']); ?>
                            <?php if ($prestamo['estado_prestamo'] === 'vencido'): ?>
                                <br><small class="text-danger">
                                    (<?php echo $prestamo['dias_vencido']; ?> días vencido)
                                </small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php echo $prestamo['fecha_dev_real'] ? formatDate($prestamo['fecha_dev_real']) : '-'; ?>
                        </td>
                        <td>
                            <?php
                            $badge_class = '';
                            $estado_texto = '';
                            switch ($prestamo['estado_prestamo']) {
                                case 'activo':
                                    $badge_class = 'status-active';
                                    $estado_texto = 'Activo';
                                    break;
                                case 'vencido':
                                    $badge_class = 'status-overdue';
                                    $estado_texto = 'Vencido';
                                    break;
                                case 'devuelto':
                                    $badge_class = 'status-returned';
                                    $estado_texto = 'Devuelto';
                                    break;
                            }
                            ?>
                            <span class="status-badge <?php echo $badge_class; ?>">
                                <?php echo $estado_texto; ?>
                            </span>
                        </td>
                        <td class="actions">
                            <?php if ($prestamo['fecha_dev_real'] === null && isBibliotecario()): ?>
                                <a href="devolver.php?id=<?php echo $prestamo['id']; ?>" 
                                   class="btn btn-sm btn-success" title="Devolver">
                                    ↩️
                                </a>
                            <?php endif; ?>
                            <a href="historial.php?prestamo_id=<?php echo $prestamo['id']; ?>" 
                               class="btn btn-sm btn-info" title="Ver detalle">
                                👁️
                            </a>
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
            <a href="?pagina=<?php echo $pagina - 1; ?>&estado=<?php echo urlencode($filtro_estado); ?>&busqueda=<?php echo urlencode($busqueda); ?>" 
               class="pagination-btn">« Anterior</a>
        <?php endif; ?>
        
        <?php for ($i = max(1, $pagina - 2); $i <= min($total_paginas, $pagina + 2); $i++): ?>
            <a href="?pagina=<?php echo $i; ?>&estado=<?php echo urlencode($filtro_estado); ?>&busqueda=<?php echo urlencode($busqueda); ?>" 
               class="pagination-btn <?php echo $i === $pagina ? 'active' : ''; ?>">
                <?php echo $i; ?>
            </a>
        <?php endfor; ?>
        
        <?php if ($pagina < $total_paginas): ?>
            <a href="?pagina=<?php echo $pagina + 1; ?>&estado=<?php echo urlencode($filtro_estado); ?>&busqueda=<?php echo urlencode($busqueda); ?>" 
               class="pagination-btn">Siguiente »</a>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php 
$include_search_js = true;
include '../includes/footer.php'; 
?>