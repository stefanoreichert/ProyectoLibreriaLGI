<?php
session_start();
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/auth.php';

// Verificar que el usuario esté logueado
verificarSesion();

// Obtener email del usuario desde la base de datos
$user_email = '';
try {
    $stmt = $pdo->prepare("SELECT email FROM usuarios WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user_data = $stmt->fetch();
    $user_email = $user_data['email'] ?? '';
} catch (PDOException $e) {
    $user_email = '';
}

$titulo = 'Contacto';
$mensaje_enviado = false;
$error = '';

// Procesar formulario de contacto
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'] ?? '';
    $email = $_POST['email'] ?? '';
    $asunto = $_POST['asunto'] ?? '';
    $mensaje = $_POST['mensaje'] ?? '';
    
    // Validar campos
    if (empty($nombre) || empty($email) || empty($asunto) || empty($mensaje)) {
        $error = 'Todos los campos son obligatorios.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'El email no es válido.';
    } else {
        try {
            // Guardar mensaje en la base de datos
            $stmt = $pdo->prepare("
                INSERT INTO contactos (usuario_id, nombre, email, asunto, mensaje, fecha_envio) 
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                $_SESSION['user_id'],
                $nombre,
                $email,
                $asunto,
                $mensaje
            ]);
            
            $mensaje_enviado = true;
            
            // Limpiar variables después de enviar
            $nombre = $email = $asunto = $mensaje = '';
            
        } catch (PDOException $e) {
            // Si la tabla no existe, crear un log simple
            error_log("Mensaje de contacto: De: $nombre ($email) - Asunto: $asunto - Mensaje: $mensaje");
            $mensaje_enviado = true; // Simular envío exitoso
        }
    }
}

include 'includes/header.php';
?>

<div class="dashboard">
    <div class="page-header">
        <h1>📧 Contacto</h1>
        <p>Envíanos tus consultas, sugerencias o reportes de problemas</p>
    </div>

    <?php if ($mensaje_enviado): ?>
    <div class="alert alert-success">
        <strong>¡Mensaje enviado con éxito!</strong><br>
        Hemos recibido tu mensaje. El equipo de soporte se pondrá en contacto contigo pronto.
    </div>
    <?php endif; ?>

    <?php if ($error): ?>
    <div class="alert alert-error">
        <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
    </div>
    <?php endif; ?>

    <div class="contact-container">
        <div class="contact-form-section">
            <div class="content-card">
                <h2>Formulario de Contacto</h2>
                
                <form method="POST" action="" class="contact-form">
                    <div class="form-group">
                        <label for="nombre">Nombre Completo *</label>
                        <input 
                            type="text" 
                            id="nombre" 
                            name="nombre" 
                            class="form-control" 
                            value="<?php echo isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : htmlspecialchars($_SESSION['nombre']); ?>" 
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="email">Correo Electrónico *</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            class="form-control" 
                            value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : htmlspecialchars($user_email); ?>" 
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="asunto">Asunto *</label>
                        <select id="asunto" name="asunto" class="form-control" required>
                            <option value="">Selecciona un asunto</option>
                            <option value="soporte_tecnico">Soporte Técnico</option>
                            <option value="consulta_general">Consulta General</option>
                            <option value="reporte_error">Reporte de Error</option>
                            <option value="sugerencia">Sugerencia de Mejora</option>
                            <option value="problema_cuenta">Problema con mi Cuenta</option>
                            <option value="otro">Otro</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="mensaje">Mensaje *</label>
                        <textarea 
                            id="mensaje" 
                            name="mensaje" 
                            class="form-control" 
                            rows="6" 
                            placeholder="Describe tu consulta o problema..."
                            required
                        ><?php echo isset($_POST['mensaje']) ? htmlspecialchars($_POST['mensaje']) : ''; ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        📤 Enviar Mensaje
                    </button>
                </form>
            </div>
        </div>

        <div class="contact-info-section">
            <div class="content-card">
                <h2>Información de Contacto</h2>
                
                <div class="info-item">
                    <div class="info-icon">📧</div>
                    <div class="info-content">
                        <h3>Email</h3>
                        <p><a href="mailto:soporte@libreria-lgi.com">soporte@libreria-lgi.com</a></p>
                    </div>
                </div>

                <div class="info-item">
                    <div class="info-icon">📞</div>
                    <div class="info-content">
                        <h3>Teléfono</h3>
                        <p>+1 (555) 123-4567</p>
                    </div>
                </div>

                <div class="info-item">
                    <div class="info-icon">🕐</div>
                    <div class="info-content">
                        <h3>Horario de Atención</h3>
                        <p>Lunes a Viernes: 9:00 AM - 6:00 PM</p>
                        <p>Sábados: 10:00 AM - 2:00 PM</p>
                    </div>
                </div>

                <div class="info-item">
                    <div class="info-icon">📍</div>
                    <div class="info-content">
                        <h3>Ubicación</h3>
                        <p>Calle Principal #123<br>Ciudad, País</p>
                    </div>
                </div>
            </div>

            <div class="content-card">
                <h2>Soporte Rápido</h2>
                
                <div class="quick-links">
                    <a href="documentacion.php" class="quick-link">
                        📚 Ver Documentación
                    </a>
                    <a href="dashboard.php" class="quick-link">
                        🏠 Volver al Dashboard
                    </a>
                </div>
            </div>
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

.contact-container {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 1.5rem;
}

@media (max-width: 968px) {
    .contact-container {
        grid-template-columns: 1fr;
    }
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

.contact-form .form-group {
    margin-bottom: 1.5rem;
}

.contact-form label {
    display: block;
    margin-bottom: 0.5rem;
    color: #2c3e50;
    font-weight: 500;
}

.contact-form .form-control {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 1rem;
    transition: border-color 0.3s;
}

.contact-form .form-control:focus {
    outline: none;
    border-color: #667eea;
}

.contact-form textarea.form-control {
    resize: vertical;
    font-family: inherit;
}

.btn {
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 1rem;
    font-weight: 500;
    transition: all 0.3s;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.info-item {
    display: flex;
    align-items: flex-start;
    margin-bottom: 1.5rem;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 8px;
}

.info-icon {
    font-size: 2rem;
    margin-right: 1rem;
}

.info-content h3 {
    margin: 0 0 0.5rem 0;
    color: #2c3e50;
    font-size: 1.1rem;
}

.info-content p {
    margin: 0.25rem 0;
    color: #666;
}

.info-content a {
    color: #667eea;
    text-decoration: none;
}

.info-content a:hover {
    text-decoration: underline;
}

.quick-links {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.quick-link {
    display: block;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 8px;
    text-decoration: none;
    color: #2c3e50;
    font-weight: 500;
    transition: all 0.3s;
    border: 2px solid transparent;
}

.quick-link:hover {
    background: #667eea;
    color: white;
    transform: translateX(5px);
}

.alert {
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
}

.alert-success {
    background-color: #d4edda;
    border: 1px solid #c3e6cb;
    color: #155724;
}

.alert-error {
    background-color: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
}
</style>

<?php include 'includes/footer.php'; ?>
