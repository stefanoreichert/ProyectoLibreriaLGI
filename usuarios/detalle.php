<?php
session_start();
require_once '../includes/auth.php';
require_once '../config/database.php';

$page_title = 'Detalle del Usuario';
$usuario_data = null;

// Obtener ID del usuario
$usuario_id = intval($_GET['id'] ?? 0);

if ($usuario_id <= 0) {
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
    
    // Obtener estadísticas de préstamos
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_prestamos,
            SUM(CASE WHEN fecha_devolucion IS NULL THEN 1 ELSE 0 END) as prestamos_activos,
            SUM(CASE WHEN fecha_devolucion IS NULL AND fecha_limite < CURDATE() THEN 1 ELSE 0 END) as prestamos_vencidos
        FROM prestamos 
        WHERE usuario_id = ?
    ");
    $stmt->execute([$usuario_id]);
    $estadisticas = $stmt->fetch();
    
    // Obtener préstamos recientes
    $stmt = $pdo->prepare("
        SELECT p.*, l.titulo, l.autor, l.isbn
        FROM prestamos p
        JOIN libros l ON p.libro_id = l.id
        WHERE p.usuario_id = ?
        ORDER BY p.fecha_prestamo DESC
        LIMIT 10
    ");
    $stmt->execute([$usuario_id]);
    $prestamos_recientes = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $error = 'Error al obtener los datos del usuario';
}

include '../includes/header.php';
?>

<div class="page-header">
    <h1>👤 Detalle del Usuario</h1>
    <div class="header-actions">
        <?php if (isBibliotecario()): ?>
            <a href="editar.php?id=<?php echo $usuario_id; ?>" class="btn btn-secondary">
                <span class="btn-icon">✏️</span>
                Editar
            </a>
        <?php endif; ?>
        <a href="index.php" class="btn btn-secondary">
            <span class="btn-icon">←</span>
            Volver a Usuarios
        </a>
    </div>
</div>

<?php if (isset($error)): ?>
    <div class="alert alert-error"><?php echo $error; ?></div>
<?php endif; ?>

<?php if ($usuario_data): ?>
    <div class="user-detail-container">
        <!-- Información Personal -->
        <div class="detail-section">
            <h3>Información Personal</h3>
            <div class="detail-grid">
                <div class="detail-item">
                    <label>Nombre Completo:</label>
                    <span><?php echo htmlspecialchars($usuario_data['nombre']); ?></span>
                </div>
                
                <div class="detail-item">
                    <label>Email:</label>
                    <span><?php echo htmlspecialchars($usuario_data['email']); ?></span>
                </div>
                
                <div class="detail-item">
                    <label>Teléfono:</label>
                    <span><?php echo htmlspecialchars($usuario_data['telefono'] ?: 'No registrado'); ?></span>
                </div>
                
                <div class="detail-item">
                    <label>Documento:</label>
                    <span><?php echo htmlspecialchars($usuario_data['documento'] ?: 'No registrado'); ?></span>
                </div>
                
                <div class="detail-item full-width">
                    <label>Dirección:</label>
                    <span><?php echo htmlspecialchars($usuario_data['direccion'] ?: 'No registrada'); ?></span>
                </div>
            </div>
        </div>
        
        <!-- Información del Sistema -->
        <div class="detail-section">
            <h3>Información del Sistema</h3>
            <div class="detail-grid">
                <div class="detail-item">
                    <label>Usuario:</label>
                    <span>@<?php echo htmlspecialchars($usuario_data['usuario']); ?></span>
                </div>
                
                <div class="detail-item">
                    <label>Rol:</label>
                    <span class="role-badge role-<?php echo $usuario_data['rol']; ?>">
                        <?php echo ucfirst($usuario_data['rol']); ?>
                    </span>
                </div>
                
                <div class="detail-item">
                    <label>Fecha de Registro:</label>
                    <span><?php echo formatDateTime($usuario_data['fecha_registro']); ?></span>
                </div>
                
                <div class="detail-item">
                    <label>Última Actividad:</label>
                    <span><?php echo $usuario_data['ultima_actividad'] ? formatDateTime($usuario_data['ultima_actividad']) : 'Nunca'; ?></span>
                </div>
            </div>
        </div>
        
        <!-- Estadísticas de Préstamos -->
        <div class="detail-section">
            <h3>Estadísticas de Préstamos</h3>
            <div class="stats-grid-small">
                <div class="stat-card-small">
                    <div class="stat-number"><?php echo $estadisticas['total_prestamos']; ?></div>
                    <div class="stat-label">Total de Préstamos</div>
                </div>
                
                <div class="stat-card-small">
                    <div class="stat-number"><?php echo $estadisticas['prestamos_activos']; ?></div>
                    <div class="stat-label">Préstamos Activos</div>
                </div>
                
                <div class="stat-card-small alert">
                    <div class="stat-number"><?php echo $estadisticas['prestamos_vencidos']; ?></div>
                    <div class="stat-label">Préstamos Vencidos</div>
                </div>
            </div>
        </div>
        
        <!-- Préstamos Recientes -->
        <div class="detail-section">
            <h3>Historial de Préstamos Recientes</h3>
            
            <?php if (empty($prestamos_recientes)): ?>
                <p class="no-data">Este usuario no tiene préstamos registrados.</p>
            <?php else: ?>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Libro</th>
                                <th>Fecha Préstamo</th>
                                <th>Fecha Límite</th>
                                <th>Fecha Devolución</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($prestamos_recientes as $prestamo): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($prestamo['titulo']); ?></strong>
                                        <br><small class="text-muted">
                                            <?php echo htmlspecialchars($prestamo['autor']); ?> 
                                            (ISBN: <?php echo htmlspecialchars($prestamo['isbn']); ?>)
                                        </small>
                                    </td>
                                    <td><?php echo formatDate($prestamo['fecha_prestamo']); ?></td>
                                    <td><?php echo formatDate($prestamo['fecha_limite']); ?></td>
                                    <td>
                                        <?php echo $prestamo['fecha_devolucion'] ? formatDate($prestamo['fecha_devolucion']) : '-'; ?>
                                    </td>
                                    <td>
                                        <?php if ($prestamo['fecha_devolucion']): ?>
                                            <span class="status-badge status-returned">Devuelto</span>
                                        <?php elseif ($prestamo['fecha_limite'] < date('Y-m-d')): ?>
                                            <span class="status-badge status-overdue">Vencido</span>
                                        <?php else: ?>
                                            <span class="status-badge status-active">Activo</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if ($estadisticas['total_prestamos'] > 10): ?>
                    <div class="text-center" style="margin-top: 15px;">
                        <a href="../prestamos/historial.php?usuario_id=<?php echo $usuario_id; ?>" class="btn btn-secondary">
                            Ver historial completo
                        </a>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        
        <!-- Acciones Rápidas -->
        <?php if (isBibliotecario()): ?>
            <div class="detail-section">
                <h3>Acciones Rápidas</h3>
                <div class="actions-grid">
                    <a href="../prestamos/nuevo.php?usuario_id=<?php echo $usuario_id; ?>" class="action-btn">
                        <span class="action-icon">📋</span>
                        <span>Nuevo Préstamo</span>
                    </a>
                    <a href="../prestamos/?usuario_id=<?php echo $usuario_id; ?>" class="action-btn">
                        <span class="action-icon">📚</span>
                        <span>Ver Préstamos</span>
                    </a>
                    <a href="../prestamos/historial.php?usuario_id=<?php echo $usuario_id; ?>" class="action-btn">
                        <span class="action-icon">📜</span>
                        <span>Ver Historial</span>
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>