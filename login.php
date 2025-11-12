<?php
session_start();
require_once __DIR__ . '/config/config.php';

$error = '';

if ($_POST) {
    $usuario = trim($_POST['usuario'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($usuario) && !empty($password)) {
        try {
            $stmt = $pdo->prepare("SELECT id, usuario, password, nombre_completo, rol FROM usuarios WHERE usuario = ? AND activo = 1");
            $stmt->execute([$usuario]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                // Contraseña correcta → iniciar sesión
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['usuario'] = $user['usuario'];
                $_SESSION['nombre'] = $user['nombre_completo'];
                $_SESSION['rol'] = $user['rol'];

                header('Location: dashboard.php');
                exit();
            } else {
                $error = 'Usuario o contraseña incorrectos';
            }
        } catch (PDOException $e) {
            $error = 'Error de conexión a la base de datos: ' . $e->getMessage();
        }
    } else {
        $error = 'Por favor complete todos los campos';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Librería</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-form">
            <div class="logo">
                <img src="assets/img/logo.png" alt="Logo Librería" class="logo-img">
                <h2>Sistema de Librería LGI</h2>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="usuario">Usuario:</label>
                    <input type="text" id="usuario" name="usuario" required 
                           value="<?php echo htmlspecialchars($_POST['usuario'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="password">Contraseña:</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <button type="submit" class="btn btn-primary btn-full">Iniciar Sesión</button>
            </form>

            <div style="text-align: center; margin-top: 20px;">
                <p>¿No tienes una cuenta? <a href="registro.php" style="color: #3498db; font-weight: 500;">Regístrate aquí</a></p>
            </div>

            <div class="login-footer">
                <p>Sistema de Gestión de Librería - LGI 2025</p>
            </div>
        </div>
    </div>
</body>
</html>
