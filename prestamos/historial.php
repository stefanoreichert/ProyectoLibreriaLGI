<?php
session_start();
require_once '../includes/auth.php';
require_once '../config/database.php';

$page_title = 'Detalle del Préstamo';
$prestamo = null;

// Obtener ID del préstamo
$prestamo_id = intval($_GET['prestamo_id'] ?? 0);

if ($prestamo_id <= 0) {
    header('Location: index.php');
    exit();
}

// Obtener datos del préstamo
try {
    $stmt = $pdo->prepare("
        SELECT p.*, l.titulo, l.subtitulo, l.autor, l.isbn, l.editorial, l.categoria,
               u.nombre_completo as usuario_nombre, u.email as usuario_email, u.telefono as usuario_telefono
        FROM prestamos p
        JOIN libros l ON p.libro_id = l.id
        JOIN usuarios u ON p.usuario_id = u.id
        WHERE p.id = ?
    ");
    $stmt->execute([$prestamo_id]);
    $prestamo = $stmt->fetch();
    
    if (!$prestamo) {
        header('Location: index.php');
        exit();
    }
    
    // Verificar permisos: usuarios normales solo pueden ver sus propios préstamos
    if ($_SESSION['rol'] === 'usuario' && $prestamo['usuario_id'] != $_SESSION['user_id']) {
        header('Location: index.php');
        exit();
    }
    
} catch (PDOException $e) {
    $error = 'Error al obtener los datos del préstamo';
}

include '../includes/header.php';
?>

<div class="page-header">
    <h1>📋 Detalle del Préstamo #<?php echo $prestamo['id']; ?></h1>
    <div class="header-actions">
        <a href="index.php" class="btn btn-secondary">
            <span class="btn-icon">←</span>
            Volver
        </a>
        <?php if ($prestamo['fecha_dev_real'] === null && isBibliotecario()): ?>
            <a href="devolver.php?id=<?php echo $prestamo['id']; ?>" class="btn btn-success">
                <span class="btn-icon">↩️</span>
                Registrar Devolución
            </a>
        <?php endif; ?>
    </div>
</div>

<?php if (isset($error)): ?>
    <div class="alert alert-error"><?php echo $error; ?></div>
<?php endif; ?>

<?php if ($prestamo): ?>

<!-- Estado del Préstamo -->
<div class="detail-card">
    <?php
    $estado_class = '';
    $estado_texto = '';
    $estado_icono = '';
    
    if ($prestamo['fecha_dev_real'] !== null) {
        $estado_class = 'success';
        $estado_texto = 'Devuelto';
        $estado_icono = '✓';
    } elseif ($prestamo['fecha_devolucion'] < date('Y-m-d')) {
        $estado_class = 'danger';
        $estado_texto = 'Vencido';
        $estado_icono = '⚠️';
        $dias_vencido = floor((strtotime(date('Y-m-d')) - strtotime($prestamo['fecha_devolucion'])) / 86400);
    } else {
        $estado_class = 'info';
        $estado_texto = 'Activo';
        $estado_icono = '📖';
    }
    ?>
    
    <div class="status-banner status-<?php echo $estado_class; ?>">
        <h2><?php echo $estado_icono; ?> Estado: <?php echo $estado_texto; ?></h2>
        <?php if (isset($dias_vencido) && $dias_vencido > 0): ?>
            <p>Este préstamo tiene <?php echo $dias_vencido; ?> día<?php echo $dias_vencido > 1 ? 's' : ''; ?> de retraso</p>
        <?php endif; ?>
    </div>
</div>

<!-- Información del Libro -->
<div class="detail-card">
    <h2>📚 Información del Libro</h2>
    
    <div class="detail-grid">
        <div class="detail-item">
            <label>Título:</label>
            <span>
                <strong><?php echo htmlspecialchars($prestamo['titulo']); ?></strong>
                <?php if ($prestamo['subtitulo']): ?>
                    <br><small class="text-muted"><?php echo htmlspecialchars($prestamo['subtitulo']); ?></small>
                <?php endif; ?>
            </span>
        </div>
        
        <div class="detail-item">
            <label>Autor:</label>
            <span><?php echo htmlspecialchars($prestamo['autor']); ?></span>
        </div>
        
        <div class="detail-item">
            <label>ISBN:</label>
            <span><?php echo htmlspecialchars($prestamo['isbn']); ?></span>
        </div>
        
        <div class="detail-item">
            <label>Editorial:</label>
            <span><?php echo htmlspecialchars($prestamo['editorial'] ?: '-'); ?></span>
        </div>
        
        <div class="detail-item">
            <label>Categoría:</label>
            <span><?php echo htmlspecialchars($prestamo['categoria'] ?: '-'); ?></span>
        </div>
    </div>
</div>

<!-- Información del Usuario -->
<?php if (isBibliotecario()): ?>
<div class="detail-card">
    <h2>👤 Información del Usuario</h2>
    
    <div class="detail-grid">
        <div class="detail-item">
            <label>Nombre:</label>
            <span><strong><?php echo htmlspecialchars($prestamo['usuario_nombre']); ?></strong></span>
        </div>
        
        <div class="detail-item">
            <label>Email:</label>
            <span>
                <a href="mailto:<?php echo htmlspecialchars($prestamo['usuario_email']); ?>">
                    <?php echo htmlspecialchars($prestamo['usuario_email']); ?>
                </a>
            </span>
        </div>
        
        <div class="detail-item">
            <label>Teléfono:</label>
            <span>
                <?php if ($prestamo['usuario_telefono']): ?>
                    <a href="tel:<?php echo htmlspecialchars($prestamo['usuario_telefono']); ?>">
                        <?php echo htmlspecialchars($prestamo['usuario_telefono']); ?>
                    </a>
                <?php else: ?>
                    -
                <?php endif; ?>
            </span>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Fechas del Préstamo -->
<div class="detail-card">
    <h2>📅 Fechas y Plazos</h2>
    
    <div class="detail-grid">
        <div class="detail-item">
            <label>Fecha de Préstamo:</label>
            <span><?php echo date('d/m/Y', strtotime($prestamo['fecha_prestamo'])); ?></span>
        </div>
        
        <div class="detail-item">
            <label>Fecha Límite de Devolución:</label>
            <span>
                <?php echo date('d/m/Y', strtotime($prestamo['fecha_devolucion'])); ?>
                <?php 
                if ($prestamo['fecha_dev_real'] === null) {
                    $dias_restantes = floor((strtotime($prestamo['fecha_devolucion']) - strtotime(date('Y-m-d'))) / 86400);
                    if ($dias_restantes < 0): 
                ?>
                    <span class="badge badge-danger" style="margin-left: 10px;">
                        ⚠️ Vencido hace <?php echo abs($dias_restantes); ?> día<?php echo abs($dias_restantes) > 1 ? 's' : ''; ?>
                    </span>
                <?php elseif ($dias_restantes == 0): ?>
                    <span class="badge badge-warning" style="margin-left: 10px;">⚠️ Vence hoy</span>
                <?php elseif ($dias_restantes <= 3): ?>
                    <span class="badge badge-info" style="margin-left: 10px;">
                        Vence en <?php echo $dias_restantes; ?> día<?php echo $dias_restantes > 1 ? 's' : ''; ?>
                    </span>
                <?php endif;
                }
                ?>
            </span>
        </div>
        
        <div class="detail-item">
            <label>Fecha de Devolución Real:</label>
            <span>
                <?php if ($prestamo['fecha_dev_real']): ?>
                    <strong><?php echo date('d/m/Y', strtotime($prestamo['fecha_dev_real'])); ?></strong>
                    <?php
                    $dias_prestamo = floor((strtotime($prestamo['fecha_dev_real']) - strtotime($prestamo['fecha_prestamo'])) / 86400);
                    ?>
                    <small class="text-muted">(<?php echo $dias_prestamo; ?> días de préstamo)</small>
                <?php else: ?>
                    <span class="badge badge-info">Aún no devuelto</span>
                <?php endif; ?>
            </span>
        </div>
        
        <div class="detail-item">
            <label>Duración del Préstamo:</label>
            <span>
                <?php
                $duracion = floor((strtotime($prestamo['fecha_devolucion']) - strtotime($prestamo['fecha_prestamo'])) / 86400);
                echo $duracion . ' día' . ($duracion > 1 ? 's' : '');
                ?>
            </span>
        </div>
    </div>
</div>

<!-- Observaciones -->
<?php if ($prestamo['observaciones']): ?>
<div class="detail-card">
    <h2>📝 Observaciones</h2>
    <div class="observaciones-box">
        <?php echo nl2br(htmlspecialchars($prestamo['observaciones'])); ?>
    </div>
</div>
<?php endif; ?>

<?php endif; ?>

<?php include '../includes/footer.php'; ?>
