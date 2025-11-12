<?php
session_start();
require_once 'includes/auth.php';
require_once __DIR__ . '/config/config.php';




verificarSesion();
verificarRol(['admin']);

$titulo = 'Configuración del Sistema';
include 'includes/header.php';

$mensaje = '';
$error = '';

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $configuraciones = $_POST['config'] ?? [];
        
        foreach ($configuraciones as $clave => $valor) {
            // Validar configuraciones específicas
            switch ($clave) {
                case 'max_libros_usuario':
                case 'dias_prestamo_defecto':
                case 'dias_renovacion':
                case 'max_renovaciones':
                case 'session_timeout':
                    if (!is_numeric($valor) || $valor < 1) {
                        throw new Exception("El valor para '$clave' debe ser un número positivo");
                    }
                    break;
                    
                case 'email_sistema':
                    if (!filter_var($valor, FILTER_VALIDATE_EMAIL)) {
                        throw new Exception("Email del sistema no es válido");
                    }
                    break;
                    
                case 'nombre_biblioteca':
                case 'direccion_biblioteca':
                case 'telefono_biblioteca':
                    if (empty(trim($valor))) {
                        throw new Exception("El campo '$clave' no puede estar vacío");
                    }
                    break;
            }
            
            // Actualizar o insertar configuración
            $stmt = $pdo->prepare("
                INSERT INTO configuracion (clave, valor, actualizado_por, fecha_actualizacion) 
                VALUES (?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE 
                valor = VALUES(valor), 
                actualizado_por = VALUES(actualizado_por), 
                fecha_actualizacion = VALUES(fecha_actualizacion)
            ");
            $stmt->execute([$clave, $valor, $_SESSION['user_id']]);
        }
        
        // Registrar en logs
        registrarLog($_SESSION['user_id'], 'configuracion', 'actualizar', 'Configuración del sistema actualizada');
        
        $mensaje = 'Configuración actualizada correctamente';
        
        // Recargar configuración
        cargarConfiguracion();
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Obtener configuración actual
try {
    $stmt = $pdo->query("SELECT clave, valor FROM configuracion");
    $config_actual = [];
    while ($row = $stmt->fetch()) {
        $config_actual[$row['clave']] = $row['valor'];
    }
} catch (Exception $e) {
    $error = "Error al cargar la configuración: " . $e->getMessage();
}

// Configuraciones por defecto si no existen
$configuraciones_defecto = [
    'nombre_biblioteca' => 'Biblioteca Central',
    'direccion_biblioteca' => 'Av. Principal 123',
    'telefono_biblioteca' => '123-456-7890',
    'email_sistema' => 'biblioteca@ejemplo.com',
    'max_libros_usuario' => '3',
    'dias_prestamo_defecto' => '15',
    'dias_renovacion' => '7',
    'max_renovaciones' => '2',
    'multa_por_dia' => '1.00',
    'session_timeout' => '30',
    'permitir_registro_usuarios' => '1',
    'notificaciones_email' => '1',
    'backup_automatico' => '1',
    'dias_recordatorio' => '3'
];

// Combinar configuración actual con valores por defecto
$config = array_merge($configuraciones_defecto, $config_actual);
?>

<div class="content-wrapper">
    <div class="content-header">
        <h1><i class="fas fa-cog"></i> Configuración del Sistema</h1>
        <p>Gestiona los parámetros de funcionamiento de la biblioteca</p>
    </div>

    <?php if ($mensaje): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <?php echo htmlspecialchars($mensaje); ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <form method="POST" class="config-form">
        <!-- Información de la Biblioteca -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-building"></i> Información de la Biblioteca</h3>
            </div>
            <div class="card-content">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="nombre_biblioteca">Nombre de la Biblioteca:</label>
                        <input type="text" name="config[nombre_biblioteca]" id="nombre_biblioteca" 
                               value="<?php echo htmlspecialchars($config['nombre_biblioteca']); ?>" 
                               class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="direccion_biblioteca">Dirección:</label>
                        <input type="text" name="config[direccion_biblioteca]" id="direccion_biblioteca" 
                               value="<?php echo htmlspecialchars($config['direccion_biblioteca']); ?>" 
                               class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="telefono_biblioteca">Teléfono:</label>
                        <input type="text" name="config[telefono_biblioteca]" id="telefono_biblioteca" 
                               value="<?php echo htmlspecialchars($config['telefono_biblioteca']); ?>" 
                               class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="email_sistema">Email del Sistema:</label>
                        <input type="email" name="config[email_sistema]" id="email_sistema" 
                               value="<?php echo htmlspecialchars($config['email_sistema']); ?>" 
                               class="form-control" required>
                    </div>
                </div>
            </div>
        </div>

        <!-- Configuración de Préstamos -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-handshake"></i> Configuración de Préstamos</h3>
            </div>
            <div class="card-content">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="max_libros_usuario">Máximo de libros por usuario:</label>
                        <input type="number" name="config[max_libros_usuario]" id="max_libros_usuario" 
                               value="<?php echo $config['max_libros_usuario']; ?>" 
                               class="form-control" min="1" max="10" required>
                        <small class="form-help">Número máximo de libros que puede tener prestados un usuario</small>
                    </div>

                    <div class="form-group">
                        <label for="dias_prestamo_defecto">Días de préstamo por defecto:</label>
                        <input type="number" name="config[dias_prestamo_defecto]" id="dias_prestamo_defecto" 
                               value="<?php echo $config['dias_prestamo_defecto']; ?>" 
                               class="form-control" min="1" max="90" required>
                        <small class="form-help">Duración en días del préstamo</small>
                    </div>

                    <div class="form-group">
                        <label for="dias_renovacion">Días de renovación:</label>
                        <input type="number" name="config[dias_renovacion]" id="dias_renovacion" 
                               value="<?php echo $config['dias_renovacion']; ?>" 
                               class="form-control" min="1" max="30" required>
                        <small class="form-help">Días adicionales al renovar un préstamo</small>
                    </div>

                    <div class="form-group">
                        <label for="max_renovaciones">Máximo de renovaciones:</label>
                        <input type="number" name="config[max_renovaciones]" id="max_renovaciones" 
                               value="<?php echo $config['max_renovaciones']; ?>" 
                               class="form-control" min="0" max="5" required>
                        <small class="form-help">Número máximo de veces que se puede renovar un préstamo</small>
                    </div>

                    <div class="form-group">
                        <label for="multa_por_dia">Multa por día de retraso:</label>
                        <input type="number" name="config[multa_por_dia]" id="multa_por_dia" 
                               value="<?php echo $config['multa_por_dia']; ?>" 
                               class="form-control" min="0" step="0.01">
                        <small class="form-help">Importe de la multa por cada día de retraso</small>
                    </div>

                    <div class="form-group">
                        <label for="dias_recordatorio">Días para recordatorio:</label>
                        <input type="number" name="config[dias_recordatorio]" id="dias_recordatorio" 
                               value="<?php echo $config['dias_recordatorio']; ?>" 
                               class="form-control" min="1" max="10" required>
                        <small class="form-help">Días antes del vencimiento para enviar recordatorio</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Configuración del Sistema -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-server"></i> Configuración del Sistema</h3>
            </div>
            <div class="card-content">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="session_timeout">Tiempo de sesión (minutos):</label>
                        <input type="number" name="config[session_timeout]" id="session_timeout" 
                               value="<?php echo $config['session_timeout']; ?>" 
                               class="form-control" min="5" max="120" required>
                        <small class="form-help">Tiempo de inactividad antes de cerrar sesión automáticamente</small>
                    </div>

                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="hidden" name="config[permitir_registro_usuarios]" value="0">
                            <input type="checkbox" name="config[permitir_registro_usuarios]" value="1" 
                                   <?php echo $config['permitir_registro_usuarios'] ? 'checked' : ''; ?>>
                            Permitir registro de usuarios
                        </label>
                        <small class="form-help">Permitir que nuevos usuarios se registren en el sistema</small>
                    </div>

                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="hidden" name="config[notificaciones_email]" value="0">
                            <input type="checkbox" name="config[notificaciones_email]" value="1" 
                                   <?php echo $config['notificaciones_email'] ? 'checked' : ''; ?>>
                            Notificaciones por email
                        </label>
                        <small class="form-help">Enviar notificaciones automáticas por correo electrónico</small>
                    </div>

                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="hidden" name="config[backup_automatico]" value="0">
                            <input type="checkbox" name="config[backup_automatico]" value="1" 
                                   <?php echo $config['backup_automatico'] ? 'checked' : ''; ?>>
                            Backup automático
                        </label>
                        <small class="form-help">Realizar copias de seguridad automáticas de la base de datos</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Guardar Configuración
            </button>
            <a href="dashboard.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver al Dashboard
            </a>
            <button type="button" class="btn btn-warning" onclick="resetearConfiguracion()">
                <i class="fas fa-undo"></i> Restaurar Valores por Defecto
            </button>
        </div>
    </form>

    <!-- Información del Sistema -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-info-circle"></i> Información del Sistema</h3>
        </div>
        <div class="card-content">
            <div class="system-info">
                <div class="info-item">
                    <strong>Versión del Sistema:</strong>
                    <span>1.0.0</span>
                </div>
                <div class="info-item">
                    <strong>Versión de PHP:</strong>
                    <span><?php echo PHP_VERSION; ?></span>
                </div>
                <div class="info-item">
                    <strong>Base de Datos:</strong>
                    <span>
                        <?php
                        try {
                            $version = $pdo->query('SELECT VERSION()')->fetchColumn();
                            echo "MySQL " . $version;
                        } catch (Exception $e) {
                            echo "No disponible";
                        }
                        ?>
                    </span>
                </div>
                <div class="info-item">
                    <strong>Última actualización de configuración:</strong>
                    <span>
                        <?php
                        try {
                            $stmt = $pdo->query("SELECT MAX(fecha_actualizacion) FROM configuracion");
                            $fecha = $stmt->fetchColumn();
                            echo $fecha ? date('d/m/Y H:i:s', strtotime($fecha)) : 'No disponible';
                        } catch (Exception $e) {
                            echo "No disponible";
                        }
                        ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function resetearConfiguracion() {
    if (confirm('¿Estás seguro de que quieres restaurar todos los valores por defecto? Esta acción no se puede deshacer.')) {
        // Valores por defecto
        document.getElementById('nombre_biblioteca').value = 'Biblioteca Central';
        document.getElementById('direccion_biblioteca').value = 'Av. Principal 123';
        document.getElementById('telefono_biblioteca').value = '123-456-7890';
        document.getElementById('email_sistema').value = 'biblioteca@ejemplo.com';
        document.getElementById('max_libros_usuario').value = '3';
        document.getElementById('dias_prestamo_defecto').value = '15';
        document.getElementById('dias_renovacion').value = '7';
        document.getElementById('max_renovaciones').value = '2';
        document.getElementById('multa_por_dia').value = '1.00';
        document.getElementById('session_timeout').value = '30';
        document.getElementById('dias_recordatorio').value = '3';
        
        // Checkboxes
        document.querySelector('input[name="config[permitir_registro_usuarios]"][type="checkbox"]').checked = true;
        document.querySelector('input[name="config[notificaciones_email]"][type="checkbox"]').checked = true;
        document.querySelector('input[name="config[backup_automatico]"][type="checkbox"]').checked = true;
    }
}

// Validación del formulario
document.querySelector('.config-form').addEventListener('submit', function(e) {
    const maxLibros = parseInt(document.getElementById('max_libros_usuario').value);
    const diasPrestamo = parseInt(document.getElementById('dias_prestamo_defecto').value);
    const diasRenovacion = parseInt(document.getElementById('dias_renovacion').value);
    const sessionTimeout = parseInt(document.getElementById('session_timeout').value);
    
    if (maxLibros < 1 || maxLibros > 10) {
        e.preventDefault();
        alert('El máximo de libros por usuario debe estar entre 1 y 10');
        return;
    }
    
    if (diasPrestamo < 1 || diasPrestamo > 90) {
        e.preventDefault();
        alert('Los días de préstamo deben estar entre 1 y 90');
        return;
    }
    
    if (diasRenovacion < 1 || diasRenovacion > 30) {
        e.preventDefault();
        alert('Los días de renovación deben estar entre 1 y 30');
        return;
    }
    
    if (sessionTimeout < 5 || sessionTimeout > 120) {
        e.preventDefault();
        alert('El timeout de sesión debe estar entre 5 y 120 minutos');
        return;
    }
});
</script>

<?php include 'includes/footer.php'; ?>