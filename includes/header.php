<?php
require_once __DIR__ . '/../config/config.php';

// Determinar ruta base del proyecto
$base_path = '/ProyectoLibreriaLGI/';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?><?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/dashboard.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="icon" type="image/png" href="<?php echo $base_path; ?>assets/img/logo.png">
</head>
<body>
    <?php if (isset($_SESSION['user_id'])): ?>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-brand">
                <i class="fas fa-book nav-logo"></i>
                <span class="nav-title"><?php echo SITE_NAME; ?></span>
            </div>
            
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="<?php echo $base_path; ?>dashboard.php" class="nav-link">
                        <i class="fas fa-home nav-icon"></i>
                        Dashboard
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle">
                        <i class="fas fa-book nav-icon"></i>
                        Libros
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="<?php echo $base_path; ?>libros/">Ver todos</a></li>
                        <li><a href="<?php echo $base_path; ?>libros/crear.php">Agregar nuevo</a></li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle">
                        <i class="fas fa-users nav-icon"></i>
                        Usuarios
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="<?php echo $base_path; ?>usuarios/">Ver todos</a></li>
                        <li><a href="<?php echo $base_path; ?>usuarios/crear.php">Crear usuario</a></li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle">
                        <i class="fas fa-handshake nav-icon"></i>
                        Préstamos
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="<?php echo $base_path; ?>prestamos/">Préstamos activos</a></li>
                        <li><a href="<?php echo $base_path; ?>prestamos/nuevo.php">Nuevo préstamo</a></li>
                        <li><a href="<?php echo $base_path; ?>reportes.php">Reportes</a></li>
                    </ul>
                </li>
                <?php if ($_SESSION['rol'] === 'admin'): ?>
                <li class="nav-item">
                    <a href="<?php echo $base_path; ?>configuracion.php" class="nav-link">
                        <i class="fas fa-cog nav-icon"></i>
                        Configuración
                    </a>
                </li>
                <?php endif; ?>
            </ul>
            
            <div class="nav-user">
                <div class="user-info">
                    <i class="fas fa-user user-icon"></i>
                    <span class="user-name"><?php echo htmlspecialchars($_SESSION['nombre']); ?></span>
                </div>
                <a href="<?php echo $base_path; ?>logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt logout-icon"></i>
                    Salir
                </a>
            </div>
        </div>
    </nav>
    <?php endif; ?>
    
    <main class="main-content">
        <div class="container">