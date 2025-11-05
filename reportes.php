<?php
session_start();
require_once '../includes/auth.php';
require_once '../config/database.php';

verificarSesion();
verificarRol(['admin', 'bibliotecario']);

$titulo = 'Reportes del Sistema';
include '../includes/header.php';

// Obtener parámetros de filtro
$fecha_desde = $_GET['fecha_desde'] ?? date('Y-m-01');
$fecha_hasta = $_GET['fecha_hasta'] ?? date('Y-m-d');
$tipo_reporte = $_GET['tipo'] ?? 'general';

// Validar fechas
$fecha_desde = date('Y-m-d', strtotime($fecha_desde));
$fecha_hasta = date('Y-m-d', strtotime($fecha_hasta));

try {
    // Estadísticas generales
    $stats = obtenerEstadisticas($fecha_desde, $fecha_hasta);
    
    // Datos específicos según el tipo de reporte
    switch ($tipo_reporte) {
        case 'libros':
            $datos = obtenerReporteLibros($fecha_desde, $fecha_hasta);
            break;
        case 'usuarios':
            $datos = obtenerReporteUsuarios($fecha_desde, $fecha_hasta);
            break;
        case 'prestamos':
            $datos = obtenerReportePrestamos($fecha_desde, $fecha_hasta);
            break;
        case 'general':
        default:
            $datos = obtenerReporteGeneral($fecha_desde, $fecha_hasta);
            break;
    }
} catch (Exception $e) {
    $error = "Error al generar el reporte: " . $e->getMessage();
}

function obtenerEstadisticas($fecha_desde, $fecha_hasta) {
    global $pdo;
    
    $sql = "
        SELECT 
            (SELECT COUNT(*) FROM usuarios WHERE activo = 1) as total_usuarios,
            (SELECT COUNT(*) FROM libros WHERE activo = 1) as total_libros,
            (SELECT COALESCE(SUM(stock), 0) FROM libros WHERE activo = 1) as total_ejemplares,
            (SELECT COUNT(*) FROM prestamos WHERE fecha_devolucion IS NULL) as prestamos_activos,
            (SELECT COUNT(*) FROM prestamos WHERE fecha_prestamo BETWEEN ? AND ?) as prestamos_periodo,
            (SELECT COUNT(*) FROM prestamos WHERE fecha_devolucion BETWEEN ? AND ?) as devoluciones_periodo
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$fecha_desde, $fecha_hasta, $fecha_desde, $fecha_hasta]);
    return $stmt->fetch();
}

function obtenerReporteGeneral($fecha_desde, $fecha_hasta) {
    global $pdo;
    
    // Libros más prestados
    $sql_libros = "
        SELECT l.titulo, l.autor, COUNT(p.id) as prestamos
        FROM libros l
        JOIN prestamos p ON l.id = p.libro_id
        WHERE p.fecha_prestamo BETWEEN ? AND ?
        GROUP BY l.id, l.titulo, l.autor
        ORDER BY prestamos DESC
        LIMIT 10
    ";
    $stmt = $pdo->prepare($sql_libros);
    $stmt->execute([$fecha_desde, $fecha_hasta]);
    $libros_populares = $stmt->fetchAll();
    
    // Usuarios más activos
    $sql_usuarios = "
        SELECT u.nombre_completo, u.email, COUNT(p.id) as prestamos
        FROM usuarios u
        JOIN prestamos p ON u.id = p.usuario_id
        WHERE p.fecha_prestamo BETWEEN ? AND ?
        GROUP BY u.id, u.nombre_completo, u.email
        ORDER BY prestamos DESC
        LIMIT 10
    ";
    $stmt = $pdo->prepare($sql_usuarios);
    $stmt->execute([$fecha_desde, $fecha_hasta]);
    $usuarios_activos = $stmt->fetchAll();
    
    // Préstamos por día
    $sql_prestamos_dia = "
        SELECT DATE(fecha_prestamo) as fecha, COUNT(*) as prestamos
        FROM prestamos
        WHERE fecha_prestamo BETWEEN ? AND ?
        GROUP BY DATE(fecha_prestamo)
        ORDER BY fecha DESC
    ";
    $stmt = $pdo->prepare($sql_prestamos_dia);
    $stmt->execute([$fecha_desde, $fecha_hasta]);
    $prestamos_por_dia = $stmt->fetchAll();
    
    return [
        'libros_populares' => $libros_populares,
        'usuarios_activos' => $usuarios_activos,
        'prestamos_por_dia' => $prestamos_por_dia
    ];
}

function obtenerReporteLibros($fecha_desde, $fecha_hasta) {
    global $pdo;
    
    $sql = "
        SELECT 
            l.id,
            l.titulo,
            l.autor,
            l.categoria,
            l.stock,
            l.fecha_adquisicion,
            COUNT(p.id) as total_prestamos,
            COUNT(CASE WHEN p.fecha_devolucion IS NULL THEN 1 END) as prestamos_activos,
            MAX(p.fecha_prestamo) as ultimo_prestamo
        FROM libros l
        LEFT JOIN prestamos p ON l.id = p.libro_id
        WHERE l.activo = 1
        GROUP BY l.id
        ORDER BY total_prestamos DESC, l.titulo
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll();
}

function obtenerReporteUsuarios($fecha_desde, $fecha_hasta) {
    global $pdo;
    
    $sql = "
        SELECT 
            u.id,
            u.nombre_completo,
            u.email,
            u.telefono,
            u.rol,
            u.fecha_registro,
            COUNT(p.id) as total_prestamos,
            COUNT(CASE WHEN p.fecha_devolucion IS NULL THEN 1 END) as prestamos_activos,
            MAX(p.fecha_prestamo) as ultimo_prestamo
        FROM usuarios u
        LEFT JOIN prestamos p ON u.id = p.usuario_id
        WHERE u.activo = 1
        GROUP BY u.id
        ORDER BY total_prestamos DESC, u.nombre
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll();
}

function obtenerReportePrestamos($fecha_desde, $fecha_hasta) {
    global $pdo;
    
    $sql = "
        SELECT 
            p.id,
            u.nombre_completo as usuario,
            l.titulo as libro,
            l.autor,
            p.fecha_prestamo,
            p.fecha_devolucion_prevista,
            p.fecha_devolucion,
            CASE 
                WHEN p.fecha_devolucion IS NULL AND p.fecha_devolucion_prevista < CURDATE() THEN 'Vencido'
                WHEN p.fecha_devolucion IS NULL THEN 'Activo'
                WHEN p.fecha_devolucion > p.fecha_devolucion_prevista THEN 'Devuelto tarde'
                ELSE 'Devuelto a tiempo'
            END as estado
        FROM prestamos p
        JOIN usuarios u ON p.usuario_id = u.id
        JOIN libros l ON p.libro_id = l.id
        WHERE p.fecha_prestamo BETWEEN ? AND ?
        ORDER BY p.fecha_prestamo DESC
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$fecha_desde, $fecha_hasta]);
    return $stmt->fetchAll();
}
?>

<div class="content-wrapper">
    <div class="content-header">
        <h1><i class="fas fa-chart-bar"></i> Reportes del Sistema</h1>
        <p>Análisis y estadísticas de la biblioteca</p>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <!-- Filtros -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-filter"></i> Filtros de Reporte</h3>
        </div>
        <div class="card-content">
            <form method="GET" class="filter-form">
                <div class="form-group">
                    <label for="tipo">Tipo de Reporte:</label>
                    <select name="tipo" id="tipo" class="form-control">
                        <option value="general" <?php echo $tipo_reporte === 'general' ? 'selected' : ''; ?>>General</option>
                        <option value="libros" <?php echo $tipo_reporte === 'libros' ? 'selected' : ''; ?>>Libros</option>
                        <option value="usuarios" <?php echo $tipo_reporte === 'usuarios' ? 'selected' : ''; ?>>Usuarios</option>
                        <option value="prestamos" <?php echo $tipo_reporte === 'prestamos' ? 'selected' : ''; ?>>Préstamos</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="fecha_desde">Fecha Desde:</label>
                    <input type="date" name="fecha_desde" id="fecha_desde" value="<?php echo $fecha_desde; ?>" class="form-control">
                </div>

                <div class="form-group">
                    <label for="fecha_hasta">Fecha Hasta:</label>
                    <input type="date" name="fecha_hasta" id="fecha_hasta" value="<?php echo $fecha_hasta; ?>" class="form-control">
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Generar Reporte
                    </button>
                    <a href="reportes.php" class="btn btn-secondary">
                        <i class="fas fa-undo"></i> Limpiar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <?php if (isset($stats) && isset($datos)): ?>
        <!-- Estadísticas Generales -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon usuarios">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo number_format($stats['total_usuarios']); ?></h3>
                    <p>Usuarios Activos</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon libros">
                    <i class="fas fa-book"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo number_format($stats['total_libros']); ?></h3>
                    <p>Libros Registrados</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon prestamos">
                    <i class="fas fa-hand-holding"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo number_format($stats['prestamos_activos']); ?></h3>
                    <p>Préstamos Activos</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon periodo">
                    <i class="fas fa-calendar-day"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo number_format($stats['prestamos_periodo']); ?></h3>
                    <p>Préstamos del Período</p>
                </div>
            </div>
        </div>

        <!-- Contenido del Reporte -->
        <?php if ($tipo_reporte === 'general'): ?>
            <div class="report-grid">
                <!-- Libros más populares -->
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-trophy"></i> Libros Más Prestados</h3>
                    </div>
                    <div class="card-content">
                        <?php if (!empty($datos['libros_populares'])): ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Posición</th>
                                            <th>Título</th>
                                            <th>Autor</th>
                                            <th>Préstamos</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($datos['libros_populares'] as $index => $libro): ?>
                                            <tr>
                                                <td>
                                                    <span class="ranking"><?php echo $index + 1; ?></span>
                                                </td>
                                                <td><?php echo htmlspecialchars($libro['titulo']); ?></td>
                                                <td><?php echo htmlspecialchars($libro['autor']); ?></td>
                                                <td>
                                                    <span class="badge badge-success"><?php echo $libro['prestamos']; ?></span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="no-data">No hay datos de préstamos para el período seleccionado.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Usuarios más activos -->
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-user-check"></i> Usuarios Más Activos</h3>
                    </div>
                    <div class="card-content">
                        <?php if (!empty($datos['usuarios_activos'])): ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Posición</th>
                                            <th>Usuario</th>
                                            <th>Email</th>
                                            <th>Préstamos</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($datos['usuarios_activos'] as $index => $usuario): ?>
                                            <tr>
                                                <td>
                                                    <span class="ranking"><?php echo $index + 1; ?></span>
                                                </td>
                                                <td><?php echo htmlspecialchars($usuario['nombre_completo']); ?></td>
                                                <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                                                <td>
                                                    <span class="badge badge-info"><?php echo $usuario['prestamos']; ?></span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="no-data">No hay datos de usuarios para el período seleccionado.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Gráfico de préstamos por día -->
            <?php if (!empty($datos['prestamos_por_dia'])): ?>
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-chart-line"></i> Préstamos por Día</h3>
                    </div>
                    <div class="card-content">
                        <div class="chart-container">
                            <canvas id="prestamosChart"></canvas>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

        <?php elseif ($tipo_reporte === 'libros'): ?>
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-book"></i> Reporte de Libros</h3>
                    <div class="card-actions">
                        <button onclick="exportarTabla('reporteLibros')" class="btn btn-secondary">
                            <i class="fas fa-download"></i> Exportar
                        </button>
                    </div>
                </div>
                <div class="card-content">
                    <div class="table-responsive">
                        <table id="reporteLibros" class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Título</th>
                                    <th>Autor</th>
                                    <th>Categoría</th>
                                    <th>Stock</th>
                                    <th>Total Préstamos</th>
                                    <th>Préstamos Activos</th>
                                    <th>Último Préstamo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($datos as $libro): ?>
                                    <tr>
                                        <td><?php echo $libro['id']; ?></td>
                                        <td><?php echo htmlspecialchars($libro['titulo']); ?></td>
                                        <td><?php echo htmlspecialchars($libro['autor']); ?></td>
                                        <td><?php echo htmlspecialchars($libro['categoria']); ?></td>
                                        <td><?php echo $libro['stock']; ?></td>
                                        <td><?php echo $libro['total_prestamos']; ?></td>
                                        <td><?php echo $libro['prestamos_activos']; ?></td>
                                        <td><?php echo $libro['ultimo_prestamo'] ? date('d/m/Y', strtotime($libro['ultimo_prestamo'])) : 'Nunca'; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        <?php elseif ($tipo_reporte === 'usuarios'): ?>
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-users"></i> Reporte de Usuarios</h3>
                    <div class="card-actions">
                        <button onclick="exportarTabla('reporteUsuarios')" class="btn btn-secondary">
                            <i class="fas fa-download"></i> Exportar
                        </button>
                    </div>
                </div>
                <div class="card-content">
                    <div class="table-responsive">
                        <table id="reporteUsuarios" class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Email</th>
                                    <th>Teléfono</th>
                                    <th>Rol</th>
                                    <th>Fecha Registro</th>
                                    <th>Total Préstamos</th>
                                    <th>Préstamos Activos</th>
                                    <th>Último Préstamo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($datos as $usuario): ?>
                                    <tr>
                                        <td><?php echo $usuario['id']; ?></td>
                                        <td><?php echo htmlspecialchars($usuario['nombre_completo']); ?></td>
                                        <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                                        <td><?php echo htmlspecialchars($usuario['telefono']); ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo $usuario['rol'] === 'admin' ? 'danger' : ($usuario['rol'] === 'bibliotecario' ? 'warning' : 'info'); ?>">
                                                <?php echo ucfirst($usuario['rol']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('d/m/Y', strtotime($usuario['fecha_registro'])); ?></td>
                                        <td><?php echo $usuario['total_prestamos']; ?></td>
                                        <td><?php echo $usuario['prestamos_activos']; ?></td>
                                        <td><?php echo $usuario['ultimo_prestamo'] ? date('d/m/Y', strtotime($usuario['ultimo_prestamo'])) : 'Nunca'; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        <?php elseif ($tipo_reporte === 'prestamos'): ?>
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-handshake"></i> Reporte de Préstamos</h3>
                    <div class="card-actions">
                        <button onclick="exportarTabla('reportePrestamos')" class="btn btn-secondary">
                            <i class="fas fa-download"></i> Exportar
                        </button>
                    </div>
                </div>
                <div class="card-content">
                    <div class="table-responsive">
                        <table id="reportePrestamos" class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Usuario</th>
                                    <th>Libro</th>
                                    <th>Autor</th>
                                    <th>Fecha Préstamo</th>
                                    <th>Fecha Prevista</th>
                                    <th>Fecha Devolución</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($datos as $prestamo): ?>
                                    <tr>
                                        <td><?php echo $prestamo['id']; ?></td>
                                        <td><?php echo htmlspecialchars($prestamo['usuario']); ?></td>
                                        <td><?php echo htmlspecialchars($prestamo['libro']); ?></td>
                                        <td><?php echo htmlspecialchars($prestamo['autor']); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($prestamo['fecha_prestamo'])); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($prestamo['fecha_devolucion_prevista'])); ?></td>
                                        <td><?php echo $prestamo['fecha_devolucion'] ? date('d/m/Y', strtotime($prestamo['fecha_devolucion'])) : '-'; ?></td>
                                        <td>
                                            <span class="badge badge-<?php 
                                                echo $prestamo['estado'] === 'Vencido' ? 'danger' : 
                                                     ($prestamo['estado'] === 'Activo' ? 'info' : 
                                                      ($prestamo['estado'] === 'Devuelto tarde' ? 'warning' : 'success')); 
                                            ?>">
                                                <?php echo $prestamo['estado']; ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Gráfico de préstamos por día
<?php if ($tipo_reporte === 'general' && !empty($datos['prestamos_por_dia'])): ?>
    const ctx = document.getElementById('prestamosChart').getContext('2d');
    const prestamosChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: [
                <?php foreach ($datos['prestamos_por_dia'] as $dia): ?>
                    '<?php echo date('d/m', strtotime($dia['fecha'])); ?>',
                <?php endforeach; ?>
            ],
            datasets: [{
                label: 'Préstamos',
                data: [
                    <?php foreach ($datos['prestamos_por_dia'] as $dia): ?>
                        <?php echo $dia['prestamos']; ?>,
                    <?php endforeach; ?>
                ],
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
<?php endif; ?>

// Función para exportar tabla
function exportarTabla(tablaId) {
    const tabla = document.getElementById(tablaId);
    let csv = '';
    
    // Obtener encabezados
    const headers = tabla.querySelectorAll('thead th');
    const headerRow = Array.from(headers).map(th => th.textContent.trim()).join(',');
    csv += headerRow + '\n';
    
    // Obtener filas
    const rows = tabla.querySelectorAll('tbody tr');
    rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        const rowData = Array.from(cells).map(td => {
            // Limpiar el contenido de badges y otros elementos HTML
            let text = td.textContent || td.innerText;
            return '"' + text.trim().replace(/"/g, '""') + '"';
        }).join(',');
        csv += rowData + '\n';
    });
    
    // Descargar archivo
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = `reporte_${tablaId}_${new Date().toISOString().split('T')[0]}.csv`;
    link.click();
}

// Configurar fechas máximas
document.addEventListener('DOMContentLoaded', function() {
    const fechaDesde = document.getElementById('fecha_desde');
    const fechaHasta = document.getElementById('fecha_hasta');
    
    fechaDesde.addEventListener('change', function() {
        fechaHasta.min = this.value;
    });
    
    fechaHasta.addEventListener('change', function() {
        fechaDesde.max = this.value;
    });
});
</script>

<?php include '../includes/footer.php'; ?>