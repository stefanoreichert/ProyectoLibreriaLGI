<?php
session_start();
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/config.php';

// Solo bibliotecarios y admins pueden devolver libros
if (!isBibliotecario()) {
    header('Location: ../dashboard.php');
    exit();
}

$page_title = 'Devolver Libro';
$errors = [];
$success = '';
$prestamo = null;

// Obtener ID del préstamo
$prestamo_id = intval($_GET['id'] ?? 0);

if ($prestamo_id <= 0) {
    header('Location: index.php');
    exit();
}

// Obtener datos del préstamo
try {
    $stmt = $pdo->prepare("
        SELECT p.*, l.titulo, l.autor, l.isbn, u.nombre_completo as usuario_nombre
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
    
    // Verificar que no esté ya devuelto
    if ($prestamo['fecha_dev_real'] !== null) {
        $errors[] = 'Este préstamo ya ha sido devuelto';
    }
    
} catch (PDOException $e) {
    $errors[] = 'Error al obtener los datos del préstamo';
}

// Procesar devolución
if ($_POST && empty($errors)) {
    $observaciones = trim($_POST['observaciones'] ?? '');
    $fecha_devolucion_real = date('Y-m-d');
    
    try {
        // Actualizar préstamo con fecha de devolución real
        $sql = "UPDATE prestamos SET fecha_dev_real = ?, observaciones = CONCAT(COALESCE(observaciones, ''), '\n[Devolución] ', ?) WHERE id = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $fecha_devolucion_real,
            $observaciones,
            $prestamo_id
        ]);
        
        // Actualizar estado del préstamo
        $stmt = $pdo->prepare("UPDATE prestamos SET estado = 'devuelto' WHERE id = ?");
        $stmt->execute([$prestamo_id]);
        
        $success = 'Libro devuelto exitosamente';
        
        // Recargar datos
        $stmt = $pdo->prepare("
            SELECT p.*, l.titulo, l.autor, l.isbn, u.nombre_completo as usuario_nombre
            FROM prestamos p
            JOIN libros l ON p.libro_id = l.id
            JOIN usuarios u ON p.usuario_id = u.id
            WHERE p.id = ?
        ");
        $stmt->execute([$prestamo_id]);
        $prestamo = $stmt->fetch();
        
    } catch (PDOException $e) {
        $errors[] = 'Error al registrar la devolución: ' . $e->getMessage();
    }
}

include '../includes/header.php';
?>

<div class="page-header">
    <h1>↩️ Devolver Libro</h1>
    <a href="index.php" class="btn btn-secondary">
        <span class="btn-icon">←</span>
        Volver a Préstamos
    </a>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-error">
        <strong>Error:</strong>
        <ul style="margin: 10px 0 0 20px;">
            <?php foreach ($errors as $error): ?>
                <li><?php echo htmlspecialchars($error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success">
        <?php echo htmlspecialchars($success); ?>
        <div style="margin-top: 15px;">
            <a href="index.php" class="btn btn-primary">Ver Préstamos</a>
            <a href="devolver.php?id=" class="btn btn-secondary">Devolver Otro Libro</a>
        </div>
    </div>
<?php endif; ?>

<?php if ($prestamo): ?>
<!-- Información del Préstamo -->
<div class="detail-card">
    <h2>Información del Préstamo</h2>
    
    <div class="detail-grid">
        <div class="detail-item">
            <label>ID Préstamo:</label>
            <span><strong>#<?php echo $prestamo['id']; ?></strong></span>
        </div>
        
        <div class="detail-item">
            <label>Libro:</label>
            <span>
                <strong><?php echo htmlspecialchars($prestamo['titulo']); ?></strong>
                <br><small class="text-muted">
                    Por: <?php echo htmlspecialchars($prestamo['autor']); ?> (ISBN: <?php echo htmlspecialchars($prestamo['isbn']); ?>)
                </small>
            </span>
        </div>
        
        <div class="detail-item">
            <label>Usuario:</label>
            <span><?php echo htmlspecialchars($prestamo['usuario_nombre']); ?></span>
        </div>
        
        <div class="detail-item">
            <label>Fecha de Préstamo:</label>
            <span><?php echo date('d/m/Y', strtotime($prestamo['fecha_prestamo'])); ?></span>
        </div>
        
        <div class="detail-item">
            <label>Fecha Límite de Devolución:</label>
            <span>
                <?php echo date('d/m/Y', strtotime($prestamo['fecha_devolucion'])); ?>
                <?php 
                $dias_diferencia = floor((strtotime(date('Y-m-d')) - strtotime($prestamo['fecha_devolucion'])) / 86400);
                if ($dias_diferencia > 0): 
                ?>
                    <span class="badge badge-danger" style="margin-left: 10px;">
                        ⚠️ VENCIDO (<?php echo $dias_diferencia; ?> días de retraso)
                    </span>
                <?php elseif ($dias_diferencia == 0): ?>
                    <span class="badge badge-warning" style="margin-left: 10px;">Vence hoy</span>
                <?php endif; ?>
            </span>
        </div>
        
        <div class="detail-item">
            <label>Estado:</label>
            <span>
                <?php if ($prestamo['fecha_dev_real']): ?>
                    <span class="status-badge status-returned">✓ Devuelto el <?php echo date('d/m/Y', strtotime($prestamo['fecha_dev_real'])); ?></span>
                <?php else: ?>
                    <span class="status-badge status-active">Activo</span>
                <?php endif; ?>
            </span>
        </div>
        
        <?php if ($prestamo['observaciones']): ?>
        <div class="detail-item" style="grid-column: 1 / -1;">
            <label>Observaciones:</label>
            <span style="white-space: pre-wrap;"><?php echo htmlspecialchars($prestamo['observaciones']); ?></span>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Formulario de Devolución -->
<?php if ($prestamo['fecha_dev_real'] === null && empty($success)): ?>
<div class="form-container">
    <form method="POST" action="" class="form-modern">
        <h2>Registrar Devolución</h2>
        
        <div class="form-group">
            <label for="observaciones">Observaciones de Devolución (opcional)</label>
            <textarea name="observaciones" id="observaciones" rows="4" class="form-control" 
                      placeholder="Ej: Libro en buen estado, sin daños..."><?php echo htmlspecialchars($_POST['observaciones'] ?? ''); ?></textarea>
            <small class="form-help">Indica el estado del libro devuelto o cualquier novedad.</small>
        </div>
        
        <div class="alert alert-info">
            <strong>ℹ️ Información:</strong> La fecha de devolución será registrada como hoy (<?php echo date('d/m/Y'); ?>).
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-success" onclick="return confirm('¿Confirmar la devolución de este libro?')">
                <span class="btn-icon">✓</span>
                Confirmar Devolución
            </button>
            <a href="index.php" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
<?php endif; ?>

<?php endif; ?>

<?php include '../includes/footer.php'; ?>
