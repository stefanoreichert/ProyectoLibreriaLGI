<?php
session_start();
require_once '../includes/auth.php';
require_once __DIR__ . '/../config/config.php';



// Solo administradores pueden eliminar usuarios
if (!isAdmin()) {
    header('Location: ../dashboard.php');
    exit();
}

$page_title = 'Eliminar Usuario';
$error = '';
$usuario_data = null;

// Obtener ID del usuario
$usuario_id = intval($_GET['id'] ?? 0);

if ($usuario_id <= 0 || $usuario_id == $_SESSION['user_id']) {
    header('Location: index.php');
    exit();
}

// Obtener datos del usuario
try {
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ? AND activo = 1");
    $stmt->execute([$usuario_id]);
    $usuario_data = $stmt->fetch();
    
    if (!$usuario_data) {
        header('Location: index.php');
        exit();
    }
    
    // Verificar si tiene préstamos activos
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM prestamos WHERE usuario_id = ? AND fecha_devolucion IS NULL");
    $stmt->execute([$usuario_id]);
    $prestamos_activos = $stmt->fetchColumn();
    
    // Contar total de préstamos históricos
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM prestamos WHERE usuario_id = ?");
    $stmt->execute([$usuario_id]);
    $total_prestamos = $stmt->fetchColumn();
    
} catch (PDOException $e) {
    $error = 'Error al obtener los datos del usuario';
}

// Procesar eliminación
if ($_POST && isset($_POST['confirmar']) && $usuario_data) {
    if ($prestamos_activos > 0) {
        $error = 'No se puede eliminar el usuario porque tiene préstamos activos';
    } else {
        try {
            // Eliminación lógica (marcar como inactivo)
            $stmt = $pdo->prepare("UPDATE usuarios SET activo = 0 WHERE id = ?");
            $stmt->execute([$usuario_id]);
            
            // Redireccionar con mensaje de éxito
            header('Location: index.php?deleted=1');
            exit();
            
        } catch (PDOException $e) {
            $error = 'Error al eliminar el usuario: ' . $e->getMessage();
        }
    }
}

include '../includes/header.php';
?>

<div class="page-header">
    <h1>🗑️ Eliminar Usuario</h1>
    <a href="index.php" class="btn btn-secondary">
        <span class="btn-icon">←</span>
        Volver a Usuarios
    </a>
</div>

<?php if ($error): ?>
    <div class="alert alert-error">
        <?php echo htmlspecialchars($error); ?>
    </div>
<?php endif; ?>

<?php if ($usuario_data): ?>
    <div class="delete-confirmation">
        <div class="warning-box">
            <div class="warning-icon">⚠️</div>
            <h3>¿Está seguro de que desea eliminar este usuario?</h3>
            <p>Esta acción no se puede deshacer. El usuario será marcado como inactivo y no podrá acceder al sistema.</p>
        </div>
        
        <div class="user-details">
            <h4>Detalles del usuario:</h4>
            <table class="detail-table">
                <tr>
                    <td><strong>Nombre:</strong></td>
                    <td><?php echo htmlspecialchars($usuario_data['nombre_completo']); ?></td>
                </tr>
                <tr>
                    <td><strong>Usuario:</strong></td>
                    <td>@<?php echo htmlspecialchars($usuario_data['usuario']); ?></td>
                </tr>
                <tr>
                    <td><strong>Email:</strong></td>
                    <td><?php echo htmlspecialchars($usuario_data['email']); ?></td>
                </tr>
                <tr>
                    <td><strong>Rol:</strong></td>
                    <td>
                        <span class="role-badge role-<?php echo $usuario_data['rol']; ?>">
                            <?php echo ucfirst($usuario_data['rol']); ?>
                        </span>
                    </td>
                </tr>
                <tr>
                    <td><strong>Fecha de registro:</strong></td>
                    <td><?php echo formatDateTime($usuario_data['fecha_registro']); ?></td>
                </tr>
                <tr>
                    <td><strong>Total de préstamos:</strong></td>
                    <td><?php echo $total_prestamos; ?></td>
                </tr>
                <tr>
                    <td><strong>Préstamos activos:</strong></td>
                    <td><?php echo $prestamos_activos; ?></td>
                </tr>
            </table>
        </div>
        
        <?php if ($prestamos_activos > 0): ?>
            <div class="alert alert-warning">
                <strong>⚠️ No se puede eliminar:</strong> Este usuario tiene <?php echo $prestamos_activos; ?> 
                préstamo<?php echo $prestamos_activos > 1 ? 's' : ''; ?> activo<?php echo $prestamos_activos > 1 ? 's' : ''; ?>.
                Debe esperar a que se devuelvan todos los libros antes de eliminar el usuario.
            </div>
            
            <div class="form-actions">
                <a href="index.php" class="btn btn-secondary">Volver a la lista</a>
                <a href="../prestamos/?usuario_id=<?php echo $usuario_id; ?>" class="btn btn-primary">
                    Ver préstamos activos
                </a>
            </div>
        <?php else: ?>
            <?php if ($total_prestamos > 0): ?>
                <div class="alert alert-info">
                    <strong>ℹ️ Información:</strong> Este usuario tiene un historial de <?php echo $total_prestamos; ?> 
                    préstamo<?php echo $total_prestamos > 1 ? 's' : ''; ?>. El historial se mantendrá para fines de auditoría.
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" class="delete-form">
                <div class="form-actions">
                    <button type="submit" name="confirmar" value="1" class="btn btn-danger" 
                            onclick="return confirm('¿Está completamente seguro? Esta acción no se puede deshacer.')">
                        <span class="btn-icon">🗑️</span>
                        Sí, eliminar usuario
                    </button>
                    <a href="index.php" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>