<?php
session_start();
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';

// Verificar que el usuario esté logueado
verificarSesion();

// Obtener estadísticas
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM libros WHERE activo = 1");
    $total_libros = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE activo = 1");
    $total_usuarios = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM prestamos WHERE fecha_dev_real IS NULL");
    $prestamos_activos = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM prestamos WHERE fecha_dev_real IS NULL AND fecha_devolucion < CURDATE()");
    $prestamos_vencidos = $stmt->fetchColumn();
    
} catch (PDOException $e) {
    $total_libros = $total_usuarios = $prestamos_activos = $prestamos_vencidos = 0;
}

include 'includes/header.php';
?>

<div class="dashboard">
    <div class="welcome-section dark">
                <h1 style="color: #2c3e50 !important; background: rgba(255,255,255,0.15) !important; padding: 12px 20px !important; border-radius: 25px !important; display: inline-block !important; text-shadow: 0 1px 3px rgba(255,255,255,0.8) !important; backdrop-filter: blur(10px) !important; border: 1px solid rgba(255,255,255,0.3) !important;">Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre']); ?></h1>
        <p>Sistema de Gestión de Librería - Panel de Control</p>
    </div>
    
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">📚</div>
            <div class="stat-content">
                <h3><?php echo $total_libros; ?></h3>
                <p>Total de Libros</p>
            </div>
            <a href="libros/" class="stat-link">Ver todos</a>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">👥</div>
            <div class="stat-content">
                <h3><?php echo $total_usuarios; ?></h3>
                <p>Total de Usuarios</p>
            </div>
            <a href="usuarios/" class="stat-link">Ver todos</a>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">📖</div>
            <div class="stat-content">
                <h3><?php echo $prestamos_activos; ?></h3>
                <p>Préstamos Activos</p>
            </div>
            <a href="prestamos/" class="stat-link">Ver todos</a>
        </div>
        
        <div class="stat-card alert">
            <div class="stat-icon">⚠️</div>
            <div class="stat-content">
                <h3><?php echo $prestamos_vencidos; ?></h3>
                <p>Préstamos Vencidos</p>
            </div>
            <a href="prestamos/historial.php?vencidos=1" class="stat-link">Ver vencidos</a>
        </div>
    </div>
    
    <div class="quick-actions">
        <h2>Acciones Rápidas</h2>
        <div class="actions-grid">
            <a href="libros/" class="action-btn">
                <span class="action-icon">📚</span>
                <span>Ver Catálogo</span>
            </a>
            
            <a href="prestamos/" class="action-btn">
                <span class="action-icon">📖</span>
                <span><?php echo $_SESSION['rol'] === 'usuario' ? 'Mis Préstamos' : 'Todos los Préstamos'; ?></span>
            </a>
            
            <a href="prestamos/solicitar.php" class="action-btn">
                <span class="action-icon">�</span>
                <span>Solicitar Préstamo</span>
            </a>
            
            <?php if (isBibliotecario()): ?>
            <a href="libros/crear.php" class="action-btn">
                <span class="action-icon">➕</span>
                <span>Agregar Libro</span>
            </a>
            <a href="prestamos/nuevo.php" class="action-btn">
                <span class="action-icon">�</span>
                <span>Gestionar Préstamos</span>
            </a>
            <?php endif; ?>
            
            <?php if (isAdmin()): ?>
            <a href="usuarios/crear.php" class="action-btn">
                <span class="action-icon">�</span>
                <span>Nuevo Usuario</span>
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>