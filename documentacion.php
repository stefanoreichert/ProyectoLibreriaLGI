<?php
session_start();
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/auth.php';

// Verificar que el usuario esté logueado
verificarSesion();

$titulo = 'Documentación del Sistema';
include 'includes/header.php';
?>

<div class="dashboard">
    <div class="page-header">
        <h1>📚 Documentación del Sistema</h1>
        <p>Guías y recursos para el uso del Sistema de Gestión de Librería</p>
    </div>

    <div class="content-card">
        <h2>Guía de Usuario</h2>
        
        <div class="doc-section">
            <h3>🔐 Gestión de Usuarios</h3>
            <ul>
                <li><strong>Roles disponibles:</strong> Administrador, Bibliotecario, Empleado, Cliente</li>
                <li><strong>Crear usuario:</strong> Ir a Usuarios > Nuevo Usuario</li>
                <li><strong>Editar usuario:</strong> Seleccionar usuario de la lista y hacer clic en Editar</li>
                <li><strong>Desactivar usuario:</strong> Usar el botón Eliminar (no borra, solo desactiva)</li>
            </ul>
        </div>

        <div class="doc-section">
            <h3>📖 Gestión de Libros</h3>
            <ul>
                <li><strong>Agregar libro:</strong> Ir a Libros > Nuevo Libro</li>
                <li><strong>Editar información:</strong> Seleccionar libro y hacer clic en Editar</li>
                <li><strong>Búsqueda:</strong> Usar el buscador por título, autor o ISBN</li>
                <li><strong>Stock:</strong> El sistema controla automáticamente la disponibilidad</li>
            </ul>
        </div>

        <div class="doc-section">
            <h3>🔄 Gestión de Préstamos</h3>
            <ul>
                <li><strong>Nuevo préstamo:</strong> Ir a Préstamos > Nuevo Préstamo</li>
                <li><strong>Devolución:</strong> Buscar el préstamo activo y hacer clic en Devolver</li>
                <li><strong>Historial:</strong> Ver todos los préstamos realizados en Historial</li>
                <li><strong>Alertas:</strong> El sistema notifica préstamos vencidos en el dashboard</li>
            </ul>
        </div>

        <div class="doc-section">
            <h3>📊 Reportes</h3>
            <ul>
                <li><strong>Reportes disponibles:</strong> General, Libros, Usuarios, Préstamos</li>
                <li><strong>Filtros:</strong> Por rango de fechas</li>
                <li><strong>Exportación:</strong> Disponible en formato PDF e impresión</li>
            </ul>
        </div>

        <div class="doc-section">
            <h3>⚙️ Configuración</h3>
            <ul>
                <li><strong>Cambiar contraseña:</strong> Ir a Configuración</li>
                <li><strong>Datos personales:</strong> Actualizar desde el panel de configuración</li>
                <li><strong>Preferencias:</strong> Configurar notificaciones y opciones del sistema</li>
            </ul>
        </div>
    </div>

    <div class="content-card">
        <h2>Permisos por Rol</h2>
        
        <div class="doc-section">
            <h3>👑 Administrador</h3>
            <ul>
                <li>Acceso completo al sistema</li>
                <li>Gestión de usuarios (crear, editar, eliminar)</li>
                <li>Gestión de libros y préstamos</li>
                <li>Acceso a todos los reportes</li>
                <li>Configuración del sistema</li>
            </ul>
        </div>

        <div class="doc-section">
            <h3>📚 Bibliotecario</h3>
            <ul>
                <li>Gestión de libros (crear, editar)</li>
                <li>Gestión de préstamos completa</li>
                <li>Consulta de usuarios</li>
                <li>Acceso a reportes de libros y préstamos</li>
            </ul>
        </div>

        <div class="doc-section">
            <h3>👤 Empleado</h3>
            <ul>
                <li>Consulta de libros</li>
                <li>Gestión básica de préstamos</li>
                <li>Consulta de usuarios</li>
            </ul>
        </div>

        <div class="doc-section">
            <h3>🛒 Cliente</h3>
            <ul>
                <li>Consulta de catálogo de libros</li>
                <li>Solicitud de préstamos</li>
                <li>Ver historial personal de préstamos</li>
            </ul>
        </div>
    </div>

    <div class="content-card">
        <h2>Información Técnica</h2>
        
        <div class="doc-section">
            <h3>🔧 Tecnologías Utilizadas</h3>
            <ul>
                <li><strong>Backend:</strong> PHP 7.4+</li>
                <li><strong>Base de datos:</strong> MySQL 5.7+</li>
                <li><strong>Frontend:</strong> HTML5, CSS3, JavaScript</li>
                <li><strong>Servidor:</strong> Apache/XAMPP</li>
            </ul>
        </div>

        <div class="doc-section">
            <h3>📋 Versión del Sistema</h3>
            <ul>
                <li><strong>Versión actual:</strong> <?php echo SITE_VERSION; ?></li>
                <li><strong>Desarrollado por:</strong> <?php echo DEVELOPED_BY; ?></li>
                <li><strong>Última actualización:</strong> <?php echo date('d/m/Y'); ?></li>
            </ul>
        </div>
    </div>

    <div class="content-card">
        <h2>Preguntas Frecuentes</h2>
        
        <div class="doc-section">
            <h3>❓ ¿Cómo recupero mi contraseña?</h3>
            <p>Contacta al administrador del sistema para restablecer tu contraseña.</p>
        </div>

        <div class="doc-section">
            <h3>❓ ¿Qué hago si un libro no aparece en el sistema?</h3>
            <p>Verifica que el libro esté activo. Si el problema persiste, contacta al administrador o bibliotecario para agregarlo.</p>
        </div>

        <div class="doc-section">
            <h3>❓ ¿Cómo extiendo un préstamo?</h3>
            <p>Contacta al bibliotecario o administrador para solicitar una extensión de préstamo antes de la fecha de vencimiento.</p>
        </div>

        <div class="doc-section">
            <h3>❓ ¿Necesitas más ayuda?</h3>
            <p>Para soporte adicional, visita la sección de <a href="contacto.php">Contacto</a>.</p>
        </div>
    </div>
</div>

<style>
.page-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 2rem;
    border-radius: 10px;
    margin-bottom: 2rem;
    text-align: center;
}

.page-header h1 {
    margin: 0 0 0.5rem 0;
    font-size: 2rem;
}

.page-header p {
    margin: 0;
    opacity: 0.9;
}

.content-card {
    background: white;
    padding: 2rem;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 1.5rem;
}

.content-card h2 {
    color: #2c3e50;
    margin-top: 0;
    margin-bottom: 1.5rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #667eea;
}

.doc-section {
    margin-bottom: 1.5rem;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 8px;
    border-left: 4px solid #667eea;
}

.doc-section h3 {
    color: #2c3e50;
    margin-top: 0;
    margin-bottom: 1rem;
}

.doc-section ul {
    margin: 0;
    padding-left: 1.5rem;
}

.doc-section ul li {
    margin-bottom: 0.5rem;
    line-height: 1.6;
}

.doc-section p {
    margin: 0.5rem 0;
    line-height: 1.6;
}

.doc-section a {
    color: #667eea;
    text-decoration: none;
    font-weight: 500;
}

.doc-section a:hover {
    text-decoration: underline;
}
</style>

<?php include 'includes/footer.php'; ?>
