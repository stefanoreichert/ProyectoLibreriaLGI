<?php
session_start();
require_once '../includes/auth.php';
require_once '../config/database.php';

$page_title = 'Gestión de Libros';

// Parámetros de búsqueda y paginación
$busqueda = $_GET['busqueda'] ?? '';
$pagina = max(1, intval($_GET['pagina'] ?? 1));
$registros_por_pagina = 10;
$offset = ($pagina - 1) * $registros_por_pagina;

// Construir consulta
$where_clause = "WHERE l.activo = 1";
$params = [];

if (!empty($busqueda)) {
    $where_clause .= " AND (l.titulo LIKE ? OR l.autor LIKE ? OR l.isbn LIKE ?)";
    $busqueda_param = "%$busqueda%";
    $params = array_fill(0, 3, $busqueda_param);
}

try {
    // Contar total de registros
    $count_sql = "SELECT COUNT(*) FROM libros l $where_clause";
    $stmt = $pdo->prepare($count_sql);
    $stmt->execute($params);
    $total_registros = $stmt->fetchColumn();
    $total_paginas = ceil($total_registros / $registros_por_pagina);
    
    // Obtener libros
    $sql = "SELECT l.*, 
            (SELECT COUNT(*) FROM prestamos p WHERE p.libro_id = l.id AND p.fecha_devolucion IS NULL) as prestado
            FROM libros l 
            $where_clause 
            ORDER BY l.titulo 
            LIMIT $registros_por_pagina OFFSET $offset";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $libros = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $error = "Error al obtener los libros: " . $e->getMessage();
    $libros = [];
    $total_registros = 0;
    $total_paginas = 0;
}

include '../includes/header.php';
?>

<div class="page-header">
    <h1>📚 <?php echo $_SESSION['rol'] === 'usuario' ? 'Catálogo de Libros' : 'Gestión de Libros'; ?></h1>
    <?php if (isBibliotecario()): ?>
    <a href="crear.php" class="btn btn-primary">
        <span class="btn-icon">➕</span>
        Agregar Libro
    </a>
    <?php endif; ?>
</div>

<!-- Barra de búsqueda -->
<div class="search-section">
    <form method="GET" action="" class="search-form">
        <div class="search-input-group">
            <input type="text" name="busqueda" placeholder="Buscar por título, autor o ISBN..." 
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
    <p>Mostrando <?php echo count($libros); ?> de <?php echo $total_registros; ?> libros</p>
</div>

<!-- Tabla de libros -->
<div class="table-container">
    <table class="data-table">
        <thead>
            <tr>
                <th>Título</th>
                <th>Autor</th>
                <th>ISBN</th>
                <th>Categoría</th>
                <th>Stock</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($libros)): ?>
                <tr>
                    <td colspan="7" class="text-center">
                        <?php if (!empty($busqueda)): ?>
                            No se encontraron libros que coincidan con "<?php echo htmlspecialchars($busqueda); ?>"
                        <?php else: ?>
                            No hay libros registrados
                        <?php endif; ?>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($libros as $libro): ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($libro['titulo']); ?></strong>
                            <?php if ($libro['subtitulo']): ?>
                                <br><small class="text-muted"><?php echo htmlspecialchars($libro['subtitulo']); ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($libro['autor']); ?></td>
                        <td><?php echo htmlspecialchars($libro['isbn']); ?></td>
                        <td><?php echo htmlspecialchars($libro['categoria']); ?></td>
                        <td>
                            <span class="stock-badge <?php echo $libro['stock'] > 0 ? 'stock-available' : 'stock-empty'; ?>">
                                <?php echo $libro['stock']; ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($libro['prestado'] > 0): ?>
                                <span class="status-badge status-borrowed">Prestado</span>
                            <?php elseif ($libro['stock'] > 0): ?>
                                <span class="status-badge status-available">Disponible</span>
                            <?php else: ?>
                                <span class="status-badge status-unavailable">No disponible</span>
                            <?php endif; ?>
                        </td>
                        <td class="actions">
                            <?php if ($libro['stock'] > $libro['prestado']): ?>
                                <a href="../prestamos/solicitar.php?libro_id=<?php echo $libro['id']; ?>" 
                                   class="btn btn-sm btn-primary" title="Solicitar préstamo">
                                    📋 Solicitar
                                </a>
                            <?php endif; ?>
                            
                            <?php if (isBibliotecario()): ?>
                            <a href="editar.php?id=<?php echo $libro['id']; ?>" class="btn btn-sm btn-secondary" title="Editar">
                                ✏️
                            </a>
                            <a href="eliminar.php?id=<?php echo $libro['id']; ?>" class="btn btn-sm btn-danger" 
                               title="Eliminar" onclick="return confirm('¿Está seguro de eliminar este libro?')">
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