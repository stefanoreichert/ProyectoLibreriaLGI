<?php
session_start();
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/config.php';

// Solo bibliotecarios y administradores pueden eliminar libros
if (!isBibliotecario()) {
    header('Location: ../dashboard.php');
    exit();
}

$page_title = 'Eliminar Libro';
$error = '';
$libro = null;

// Obtener ID del libro
$libro_id = intval($_GET['id'] ?? 0);

if ($libro_id <= 0) {
    header('Location: index.php');
    exit();
}

// Obtener datos del libro
try {
    $stmt = $pdo->prepare("SELECT * FROM libros WHERE id = ? AND activo = 1");
    $stmt->execute([$libro_id]);
    $libro = $stmt->fetch();
    
    if (!$libro) {
        header('Location: index.php');
        exit();
    }
    
    // Verificar si tiene préstamos activos
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM prestamos WHERE libro_id = ? AND fecha_devolucion IS NULL");
    $stmt->execute([$libro_id]);
    $prestamos_activos = $stmt->fetchColumn();
    
} catch (PDOException $e) {
    $error = 'Error al obtener los datos del libro';
}

// Procesar eliminación
if ($_POST && isset($_POST['confirmar']) && $libro) {
    if ($prestamos_activos > 0) {
        $error = 'No se puede eliminar el libro porque tiene préstamos activos';
    } else {
        try {
            // Eliminación lógica (marcar como inactivo)
            $stmt = $pdo->prepare("UPDATE libros SET activo = 0 WHERE id = ?");
            $stmt->execute([$libro_id]);
            
            // Redireccionar con mensaje de éxito
            header('Location: index.php?deleted=1');
            exit();
            
        } catch (PDOException $e) {
            $error = 'Error al eliminar el libro: ' . $e->getMessage();
        }
    }
}

include '../includes/header.php';
?>

<div class="page-header">
    <h1>🗑️ Eliminar Libro</h1>
    <a href="index.php" class="btn btn-secondary">
        <span class="btn-icon">←</span>
        Volver a Libros
    </a>
</div>

<?php if ($error): ?>
    <div class="alert alert-error">
        <?php echo htmlspecialchars($error); ?>
    </div>
<?php endif; ?>

<?php if ($libro): ?>
    <div class="delete-confirmation">
        <div class="warning-box">
            <div class="warning-icon">⚠️</div>
            <h3>¿Está seguro de que desea eliminar este libro?</h3>
            <p>Esta acción no se puede deshacer. El libro será marcado como inactivo y no aparecerá en las búsquedas.</p>
        </div>
        
        <div class="book-details">
            <h4>Detalles del libro:</h4>
            <table class="detail-table">
                <tr>
                    <td><strong>Título:</strong></td>
                    <td><?php echo htmlspecialchars($libro['titulo']); ?></td>
                </tr>
                <?php if ($libro['subtitulo']): ?>
                <tr>
                    <td><strong>Subtítulo:</strong></td>
                    <td><?php echo htmlspecialchars($libro['subtitulo']); ?></td>
                </tr>
                <?php endif; ?>
                <tr>
                    <td><strong>Autor:</strong></td>
                    <td><?php echo htmlspecialchars($libro['autor']); ?></td>
                </tr>
                <tr>
                    <td><strong>ISBN:</strong></td>
                    <td><?php echo htmlspecialchars($libro['isbn']); ?></td>
                </tr>
                <tr>
                    <td><strong>Categoría:</strong></td>
                    <td><?php echo htmlspecialchars($libro['categoria']); ?></td>
                </tr>
                <tr>
                    <td><strong>Stock:</strong></td>
                    <td><?php echo $libro['stock']; ?> unidades</td>
                </tr>
                <tr>
                    <td><strong>Fecha de registro:</strong></td>
                    <td><?php echo formatDateTime($libro['fecha_registro']); ?></td>
                </tr>
            </table>
        </div>
        
        <?php if ($prestamos_activos > 0): ?>
            <div class="alert alert-warning">
                <strong>⚠️ No se puede eliminar:</strong> Este libro tiene <?php echo $prestamos_activos; ?> 
                préstamo<?php echo $prestamos_activos > 1 ? 's' : ''; ?> activo<?php echo $prestamos_activos > 1 ? 's' : ''; ?>.
                Debe esperar a que se devuelvan todos los ejemplares antes de eliminar el libro.
            </div>
            
            <div class="form-actions">
                <a href="index.php" class="btn btn-secondary">Volver a la lista</a>
                <a href="../prestamos/?libro_id=<?php echo $libro_id; ?>" class="btn btn-primary">
                    Ver préstamos activos
                </a>
            </div>
        <?php else: ?>
            <form method="POST" action="" class="delete-form">
                <div class="form-actions">
                    <button type="submit" name="confirmar" value="1" class="btn btn-danger" 
                            onclick="return confirm('¿Está completamente seguro? Esta acción no se puede deshacer.')">
                        <span class="btn-icon">🗑️</span>
                        Sí, eliminar libro
                    </button>
                    <a href="index.php" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>