<?php
session_start();
require_once '../includes/auth.php';
require_once __DIR__ . '/../config/config.php';



// Solo administradores pueden editar usuarios
if (!isAdmin()) {
    header('Location: ../dashboard.php');
    exit();
}

$page_title = 'Editar Usuario';
$errors = [];
$success = '';
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
    
    // Solo admin puede editar otros admins, y no puede editarse a sí mismo si es el único admin
    if ($usuario_data['rol'] === 'admin' && !isAdmin()) {
        header('Location: index.php');
        exit();
    }
    
} catch (PDOException $e) {
    $errors[] = 'Error al obtener los datos del usuario';
}

if ($_POST && $usuario_data) {
    // Validar campos requeridos
    $nombre = trim($_POST['nombre'] ?? '');
    $usuario = trim($_POST['usuario'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmar_password = $_POST['confirmar_password'] ?? '';
    $rol = $_POST['rol'] ?? 'usuario';
    $telefono = trim($_POST['telefono'] ?? '');
    $documento = trim($_POST['dni'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    
    // Validaciones básicas
    if (empty($nombre)) $errors[] = 'El nombre es requerido';
    if (empty($usuario)) $errors[] = 'El nombre de usuario es requerido';
    if (empty($email)) $errors[] = 'El email es requerido';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'El email no es válido';
    if (!in_array($rol, ['usuario', 'bibliotecario', 'admin'])) $errors[] = 'Rol no válido';
    
    // Validar contraseña si se proporciona
    if (!empty($password)) {
        if (strlen($password) < 6) $errors[] = 'La contraseña debe tener al menos 6 caracteres';
        if ($password !== $confirmar_password) $errors[] = 'Las contraseñas no coinciden';
    }
    
    // Solo admin puede cambiar roles de admin
    if ($usuario_data['rol'] === 'admin' && $rol !== 'admin' && !isAdmin()) {
        $errors[] = 'No tiene permisos para cambiar el rol de un administrador';
    }
    if ($rol === 'admin' && !isAdmin()) {
        $errors[] = 'No tiene permisos para crear administradores';
    }
    
    // Validar usuario único (excepto el usuario actual)
    if (!empty($usuario)) {
        try {
            $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE usuario = ? AND id != ? AND activo = 1");
            $stmt->execute([$usuario, $usuario_id]);
            if ($stmt->fetch()) {
                $errors[] = 'Ya existe otro usuario con este nombre de usuario';
            }
        } catch (PDOException $e) {
            $errors[] = 'Error al validar usuario';
        }
    }
    
    // Validar email único (excepto el usuario actual)
    if (!empty($email)) {
        try {
            $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ? AND activo = 1");
            $stmt->execute([$email, $usuario_id]);
            if ($stmt->fetch()) {
                $errors[] = 'Ya existe otro usuario con este email';
            }
        } catch (PDOException $e) {
            $errors[] = 'Error al validar email';
        }
    }
    
    // Si no hay errores, actualizar
    if (empty($errors)) {
        try {
            if (!empty($password)) {
                // Actualizar con nueva contraseña
                $password_hash = password($password, PASSWORD_DEFAULT);
                $sql = "UPDATE usuarios SET nombre_completo = ?, usuario = ?, email = ?, password = ?, 
                        rol = ?, telefono = ?, dni = ?, direccion = ? WHERE id = ?";
                $params = [
                    $nombre, $usuario, $email, $password_hash, $rol,
                    $telefono ?: null, $dni ?: null, $direccion ?: null, $usuario_id
                ];
            } else {
                // Actualizar sin cambiar contraseña
                $sql = "UPDATE usuarios SET nombre_completo = ?, usuario = ?, email = ?, rol = ?, 
                        telefono = ?, dni = ?, direccion = ? WHERE id = ?";
                $params = [
                    $nombre, $usuario, $email, $rol,
                    $telefono ?: null, $documento ?: null, $direccion ?: null, $usuario_id
                ];
            }
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            
            $success = 'Usuario actualizado exitosamente';
            
            // Recargar datos del usuario
            $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
            $stmt->execute([$usuario_id]);
            $usuario_data = $stmt->fetch();
            
            // Actualizar sesión si es el usuario actual
            if ($usuario_id == $_SESSION['user_id']) {
                $_SESSION['nombre'] = $usuario_data['nombre_completo'];
                $_SESSION['usuario'] = $usuario_data['usuario'];
                $_SESSION['rol'] = $usuario_data['rol'];
            }
            
        } catch (PDOException $e) {
            $errors[] = 'Error al actualizar el usuario: ' . $e->getMessage();
        }
    }
}

include '../includes/header.php';
?>

<div class="page-header">
    <h1>👥 Editar Usuario</h1>
    <div class="header-actions">
        <a href="detalle.php?id=<?php echo $usuario_id; ?>" class="btn btn-info">
            <span class="btn-icon">👁️</span>
            Ver Detalle
        </a>
        <a href="index.php" class="btn btn-secondary">
            <span class="btn-icon">←</span>
            Volver a Usuarios
        </a>
    </div>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-error">
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?php echo htmlspecialchars($error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success">
        <?php echo htmlspecialchars($success); ?>
    </div>
<?php endif; ?>

<?php if ($usuario_data): ?>
<form method="POST" action="" class="form-container">
    <div class="form-section">
        <h3>Información Personal</h3>
        <div class="form-grid">
            <div class="form-group">
                <label for="nombre">Nombre Completo *</label>
                <input type="text" id="nombre" name="nombre" required 
                       value="<?php echo htmlspecialchars($_POST['nombre'] ?? $usuario_data['nombre_completo']); ?>">
            </div>
            
            <div class="form-group">
                <label for="email">Email *</label>
                <input type="email" id="email" name="email" required 
                       value="<?php echo htmlspecialchars($_POST['email'] ?? $usuario_data['email']); ?>">
            </div>
            
            <div class="form-group">
                <label for="telefono">Teléfono</label>
                <input type="tel" id="telefono" name="telefono" 
                       value="<?php echo htmlspecialchars($_POST['telefono'] ?? $usuario_data['telefono']); ?>">
            </div>
            
            <div class="form-group">
                <label for="dni">Documento de Identidad</label>
                <input type="text" id="dni" name="dni" 
                       value="<?php echo htmlspecialchars($_POST['dni'] ?? $usuario_data['dni']); ?>">
            </div>
        </div>
        
        <div class="form-group">
            <label for="direccion">Dirección</label>
            <textarea id="direccion" name="direccion" rows="2" 
                      placeholder="Dirección completa..."><?php echo htmlspecialchars($_POST['direccion'] ?? $usuario_data['direccion']); ?></textarea>
        </div>
    </div>
    
    <div class="form-section">
        <h3>Información de Acceso</h3>
        <div class="form-grid">
            <div class="form-group">
                <label for="usuario">Nombre de Usuario *</label>
                <input type="text" id="usuario" name="usuario" required 
                       value="<?php echo htmlspecialchars($_POST['usuario'] ?? $usuario_data['usuario']); ?>">
            </div>
            
            <div class="form-group">
                <label for="rol">Rol *</label>
                <select id="rol" name="rol" required>
                    <?php 
                    $rol_actual = $_POST['rol'] ?? $usuario_data['rol'];
                    $roles = ['usuario' => 'Usuario', 'bibliotecario' => 'Bibliotecario'];
                    if (isAdmin()) {
                        $roles['admin'] = 'Administrador';
                    }
                    ?>
                    <?php foreach ($roles as $rol_key => $rol_label): ?>
                        <option value="<?php echo $rol_key; ?>" <?php echo $rol_actual === $rol_key ? 'selected' : ''; ?>>
                            <?php echo $rol_label; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <div class="form-section">
            <h4>Cambiar Contraseña (opcional)</h4>
            <div class="form-grid">
                <div class="form-group">
                    <label for="password">Nueva Contraseña</label>
                    <input type="password" id="password" name="password" minlength="6">
                    <small class="form-help">Dejar en blanco para mantener la contraseña actual</small>
                </div>
                
                <div class="form-group">
                    <label for="confirmar_password">Confirmar Nueva Contraseña</label>
                    <input type="password" id="confirmar_password" name="confirmar_password" minlength="6">
                </div>
            </div>
        </div>
    </div>
    
    <div class="user-info">
        <p><strong>Fecha de registro:</strong> <?php echo formatDateTime($usuario_data['fecha_registro']); ?></p>
        <p><strong>ID del usuario:</strong> #<?php echo $usuario_data['id']; ?></p>
        <?php if ($usuario_data['estado']): ?>
            <p><strong>Última actividad:</strong> <?php echo formatDateTime($usuario_data['estado']); ?></p>
        <?php endif; ?>
    </div>
    
    <div class="form-actions">
        <button type="submit" class="btn btn-primary">
            <span class="btn-icon">💾</span>
            Actualizar Usuario
        </button>
        <a href="index.php" class="btn btn-secondary">Cancelar</a>
    </div>
</form>
<?php endif; ?>

<script>
// Validar que las contraseñas coincidan
document.getElementById('confirmar_password').addEventListener('input', function() {
    const password = document.getElementById('password').value;
    const confirmar = this.value;
    
    if (password && password !== confirmar) {
        this.setCustomValidity('Las contraseñas no coinciden');
    } else {
        this.setCustomValidity('');
    }
});
</script>

<?php include '../includes/footer.php'; ?>