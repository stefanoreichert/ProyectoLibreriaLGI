<?php
session_start();
require_once 'config/database.php';

$error = '';
$success = '';

if ($_POST) {
    $nombre_completo = trim($_POST['nombre_completo'] ?? '');
    $usuario = trim($_POST['usuario'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $password_confirm = trim($_POST['password_confirm'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $dni = trim($_POST['dni'] ?? '');
    
    // Validaciones
    $errors = [];
    
    if (empty($nombre_completo)) $errors[] = 'El nombre completo es requerido';
    if (empty($usuario)) $errors[] = 'El nombre de usuario es requerido';
    if (empty($email)) $errors[] = 'El email es requerido';
    if (empty($password)) $errors[] = 'La contraseña es requerida';
    
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'El email no es válido';
    }
    
    if (!empty($password) && strlen($password) < 6) {
        $errors[] = 'La contraseña debe tener al menos 6 caracteres';
    }
    
    if ($password !== $password_confirm) {
        $errors[] = 'Las contraseñas no coinciden';
    }
    
    // Verificar si el usuario ya existe
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE usuario = ? OR email = ?");
            $stmt->execute([$usuario, $email]);
            if ($stmt->fetch()) {
                $errors[] = 'El usuario o email ya están registrados';
            }
        } catch (PDOException $e) {
            $errors[] = 'Error al verificar el usuario';
        }
    }
    
    // Si no hay errores, crear el usuario
    if (empty($errors)) {
        try {
            // Guardar contraseña en texto plano (SOLO PARA DESARROLLO)
            
            $sql = "INSERT INTO usuarios (nombre_completo, usuario, email, password, rol, telefono, 
                    dni, fecha_registro, activo) 
                    VALUES (?, ?, ?, ?, 'usuario', ?, ?, NOW(), 1)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $nombre_completo, 
                $usuario, 
                $email, 
                $password,  // Contraseña en texto plano
                $telefono ?: null, 
                $dni ?: null
            ]);
            
            $success = 'Usuario registrado exitosamente. Ahora puedes iniciar sesión.';
            
            // Limpiar formulario
            $_POST = [];
            
        } catch (PDOException $e) {
            $errors[] = 'Error al crear el usuario: ' . $e->getMessage();
        }
    }
    
    if (!empty($errors)) {
        $error = implode('<br>', $errors);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Sistema de Librería</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .registro-form {
            max-width: 500px;
            width: 100%;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        @media (max-width: 600px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-form registro-form">
            <div class="logo">
                <img src="assets/img/logo.png" alt="Logo Librería" class="logo-img">
                <h2>Registro de Usuario</h2>
                <p style="font-size: 0.9rem; color: #666; margin-top: 5px;">Sistema de Librería LGI</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="nombre_completo">Nombre Completo *</label>
                    <input type="text" id="nombre_completo" name="nombre_completo" required 
                           value="<?php echo htmlspecialchars($_POST['nombre_completo'] ?? ''); ?>"
                           placeholder="Ej: Juan Pérez García">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="usuario">Usuario *</label>
                        <input type="text" id="usuario" name="usuario" required 
                               value="<?php echo htmlspecialchars($_POST['usuario'] ?? ''); ?>"
                               placeholder="usuario123">
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" required 
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                               placeholder="correo@ejemplo.com">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Contraseña *</label>
                        <input type="password" id="password" name="password" required
                               placeholder="Mínimo 6 caracteres">
                    </div>
                    
                    <div class="form-group">
                        <label for="password_confirm">Confirmar Contraseña *</label>
                        <input type="password" id="password_confirm" name="password_confirm" required
                               placeholder="Repite la contraseña">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="telefono">Teléfono</label>
                        <input type="text" id="telefono" name="telefono" 
                               value="<?php echo htmlspecialchars($_POST['telefono'] ?? ''); ?>"
                               placeholder="Opcional">
                    </div>
                    
                    <div class="form-group">
                        <label for="dni">DNI/Documento</label>
                        <input type="text" id="dni" name="dni" 
                               value="<?php echo htmlspecialchars($_POST['dni'] ?? ''); ?>"
                               placeholder="Opcional">
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary btn-full">Registrarse</button>
            </form>
            
            <div style="text-align: center; margin-top: 20px;">
                <p>¿Ya tienes una cuenta? <a href="login.php" style="color: #3498db; font-weight: 500;">Inicia sesión aquí</a></p>
            </div>
            
            <div class="login-footer">
                <p>Sistema de Gestión de Librería - LGI 2025</p>
            </div>
        </div>
    </div>
</body>
</html>
