<?php
session_start();
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/auth.php';

// Verificar que el usuario esté logueado
verificarSesion();

// Obtener estadísticas
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM libros WHERE activo = 1");
    $total_libros = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE activo = 1");
    $total_usuarios = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM prestamos WHERE fecha_devolucion IS NULL");
    $prestamos_activos = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM prestamos WHERE fecha_devolucion IS NULL AND fecha_limite < CURDATE()");
    $prestamos_vencidos = $stmt->fetchColumn();
    
} catch (PDOException $e) {
    $total_libros = $total_usuarios = $prestamos_activos = $prestamos_vencidos = 0;
}

include 'includes/header.php';
?>

<div class="dashboard">
    <div class="welcome-section">
        <h1>Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre']); ?></h1>
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
            <a href="libros/crear.php" class="action-btn">
                <span class="action-icon">➕</span>
                <span>Agregar Libro</span>
            </a>
            <a href="usuarios/crear.php" class="action-btn">
                <span class="action-icon">👤</span>
                <span>Nuevo Usuario</span>
            </a>
            <a href="prestamos/nuevo.php" class="action-btn">
                <span class="action-icon">📋</span>
                <span>Nuevo Préstamo</span>
            </a>
            <a href="prestamos/devolver.php" class="action-btn">
                <span class="action-icon">↩️</span>
                <span>Registrar Devolución</span>
            </a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>