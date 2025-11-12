<?php
session_start();
require_once '../includes/auth.php';
require_once __DIR__ . '/../config/config.php';



// Solo administradores pueden crear usuarios
if (!isAdmin()) {
    header('Location: ../dashboard.php');
    exit();
}

$page_title = 'Crear Nuevo Usuario';
$errors = [];
$success = '';

if ($_POST) {
    // Validar campos requeridos
    $nombre = trim($_POST['nombre'] ?? '');
    $usuario = trim($_POST['usuario'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmar_password = $_POST['confirmar_password'] ?? '';
    $rol = $_POST['rol'] ?? 'usuario';
    $telefono = trim($_POST['telefono'] ?? '');
    $documento = trim($_POST['documento'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    
    // Validaciones básicas
    if (empty($nombre)) $errors[] = 'El nombre es requerido';
    if (empty($usuario)) $errors[] = 'El nombre de usuario es requerido';
    if (empty($email)) $errors[] = 'El email es requerido';
    if (empty($password)) $errors[] = 'La contraseña es requerida';
    if (strlen($password) < 6) $errors[] = 'La contraseña debe tener al menos 6 caracteres';
    if ($password !== $confirmar_password) $errors[] = 'Las contraseñas no coinciden';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'El email no es válido';
    if (!in_array($rol, ['usuario', 'bibliotecario', 'admin'])) $errors[] = 'Rol no válido';
    
    // Solo admin puede crear otros admins
    if ($rol === 'admin' && !isAdmin()) {
        $errors[] = 'No tiene permisos para crear administradores';
    }
    
    // Validar usuario único
    if (!empty($usuario)) {
        try {
            $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE usuario = ? AND activo = 1");
            $stmt->execute([$usuario]);
            if ($stmt->fetch()) {
                $errors[] = 'Ya existe un usuario con este nombre de usuario';
            }
        } catch (PDOException $e) {
            $errors[] = 'Error al validar usuario';
        }
    }
    
    // Validar email único
    if (!empty($email)) {
        try {
            $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ? AND activo = 1");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $errors[] = 'Ya existe un usuario con este email';
            }
        } catch (PDOException $e) {
            $errors[] = 'Error al validar email';
        }
    }
    
    // Si no hay errores, insertar
    if (empty($errors)) {
        try {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            $sql = "INSERT INTO usuarios (nombre_completo, usuario, email, password, rol, telefono, 
                    documento, direccion, fecha_registro, activo) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), 1)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $nombre, $usuario, $email, $password_hash, $rol, 
                $telefono ?: null, $documento ?: null, $direccion ?: null
            ]);
            
            $success = 'Usuario creado exitosamente';
            
            // Limpiar formulario
            $_POST = [];
            
        } catch (PDOException $e) {
            $errors[] = 'Error al crear el usuario: ' . $e->getMessage();
        }
    }
}

include '../includes/header.php';
?>

<div class="page-header">
    <h1>👥 Crear Nuevo Usuario</h1>
    <a href="index.php" class="btn btn-secondary">
        <span class="btn-icon">←</span>
        Volver a Usuarios
    </a>
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
        <a href="index.php" class="btn btn-sm btn-primary" style="margin-left: 10px;">Ver todos los usuarios</a>
    </div>
<?php endif; ?>

<form method="POST" action="" class="form-container">
    <div class="form-section">
        <h3>Información Personal</h3>
        <div class="form-grid">
            <div class="form-group">
                <label for="nombre">Nombre Completo *</label>
                <input type="text" id="nombre" name="nombre" required 
                       value="<?php echo htmlspecialchars($_POST['nombre'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label for="email">Email *</label>
                <input type="email" id="email" name="email" required 
                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label for="telefono">Teléfono</label>
                <input type="tel" id="telefono" name="telefono" 
                       value="<?php echo htmlspecialchars($_POST['telefono'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label for="documento">Documento de Identidad</label>
                <input type="text" id="documento" name="documento" 
                       value="<?php echo htmlspecialchars($_POST['documento'] ?? ''); ?>">
            </div>
        </div>
        
        <div class="form-group">
            <label for="direccion">Dirección</label>
            <textarea id="direccion" name="direccion" rows="2" 
                      placeholder="Dirección completa..."><?php echo htmlspecialchars($_POST['direccion'] ?? ''); ?></textarea>
        </div>
    </div>
    
    <div class="form-section">
        <h3>Información de Acceso</h3>
        <div class="form-grid">
            <div class="form-group">
                <label for="usuario">Nombre de Usuario *</label>
                <input type="text" id="usuario" name="usuario" required 
                       value="<?php echo htmlspecialchars($_POST['usuario'] ?? ''); ?>"
                       placeholder="usuario123">
            </div>
            
            <div class="form-group">
                <label for="rol">Rol *</label>
                <select id="rol" name="rol" required>
                    <option value="usuario" <?php echo ($_POST['rol'] ?? 'usuario') === 'usuario' ? 'selected' : ''; ?>>
                        Usuario
                    </option>
                    <option value="bibliotecario" <?php echo ($_POST['rol'] ?? '') === 'bibliotecario' ? 'selected' : ''; ?>>
                        Bibliotecario
                    </option>
                    <?php if (isAdmin()): ?>
                        <option value="admin" <?php echo ($_POST['rol'] ?? '') === 'admin' ? 'selected' : ''; ?>>
                            Administrador
                        </option>
                    <?php endif; ?>
                </select>
                <small class="form-help">
                    Usuario: Solo puede ver sus préstamos<br>
                    Bibliotecario: Puede gestionar libros y préstamos<br>
                    Administrador: Acceso completo al sistema
                </small>
            </div>
            
            <div class="form-group">
                <label for="password">Contraseña *</label>
                <input type="password" id="password" name="password" required minlength="6">
                <small class="form-help">Mínimo 6 caracteres</small>
            </div>
            
            <div class="form-group">
                <label for="confirmar_password">Confirmar Contraseña *</label>
                <input type="password" id="confirmar_password" name="confirmar_password" required minlength="6">
            </div>
        </div>
    </div>
    
    <div class="form-actions">
        <button type="submit" class="btn btn-primary">
            <span class="btn-icon">💾</span>
            Crear Usuario
        </button>
        <a href="index.php" class="btn btn-secondary">Cancelar</a>
    </div>
</form>

<script>
// Validar que las contraseñas coincidan
document.getElementById('confirmar_password').addEventListener('input', function() {
    const password = document.getElementById('password').value;
    const confirmar = this.value;
    
    if (password !== confirmar) {
        this.setCustomValidity('Las contraseñas no coinciden');
    } else {
        this.setCustomValidity('');
    }
});
</script>

<?php include '../includes/footer.php'; ?>