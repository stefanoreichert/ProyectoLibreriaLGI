<?php
require_once __DIR__ . '/../config/config.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?><?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo $_SERVER['REQUEST_URI'] === '/ProyectoLibreriaLGI/' ? '' : '../'; ?>assets/css/style.css">
    <link rel="stylesheet" href="<?php echo $_SERVER['REQUEST_URI'] === '/ProyectoLibreriaLGI/' ? '' : '../'; ?>assets/css/dashboard.css">
    <link rel="icon" type="image/png" href="<?php echo $_SERVER['REQUEST_URI'] === '/ProyectoLibreriaLGI/' ? '' : '../'; ?>assets/img/logo.png">
</head>
<body>
    <?php if (isset($_SESSION['user_id'])): ?>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-brand">
                <img src="<?php echo $_SERVER['REQUEST_URI'] === '/ProyectoLibreriaLGI/' ? '' : '../'; ?>assets/img/logo.png" alt="Logo" class="nav-logo">
                <span class="nav-title"><?php echo SITE_NAME; ?></span>
            </div>
            
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="<?php echo $_SERVER['REQUEST_URI'] === '/ProyectoLibreriaLGI/' ? '' : '../'; ?>dashboard.php" class="nav-link">
                        <span class="nav-icon">🏠</span>
                        Dashboard
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle">
                        <span class="nav-icon">📚</span>
                        Libros
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="<?php echo $_SERVER['REQUEST_URI'] === '/ProyectoLibreriaLGI/' ? '' : '../'; ?>libros/">Ver todos</a></li>
                        <li><a href="<?php echo $_SERVER['REQUEST_URI'] === '/ProyectoLibreriaLGI/' ? '' : '../'; ?>libros/crear.php">Agregar nuevo</a></li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle">
                        <span class="nav-icon">👥</span>
                        Usuarios
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="<?php echo $_SERVER['REQUEST_URI'] === '/ProyectoLibreriaLGI/' ? '' : '../'; ?>usuarios/">Ver todos</a></li>
                        <li><a href="<?php echo $_SERVER['REQUEST_URI'] === '/ProyectoLibreriaLGI/' ? '' : '../'; ?>usuarios/crear.php">Crear usuario</a></li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle">
                        <span class="nav-icon">📋</span>
                        Préstamos
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="<?php echo $_SERVER['REQUEST_URI'] === '/ProyectoLibreriaLGI/' ? '' : '../'; ?>prestamos/">Préstamos activos</a></li>
                        <li><a href="<?php echo $_SERVER['REQUEST_URI'] === '/ProyectoLibreriaLGI/' ? '' : '../'; ?>prestamos/nuevo.php">Nuevo préstamo</a></li>
                        <li><a href="<?php echo $_SERVER['REQUEST_URI'] === '/ProyectoLibreriaLGI/' ? '' : '../'; ?>prestamos/devolver.php">Devolver libro</a></li>
                        <li><a href="<?php echo $_SERVER['REQUEST_URI'] === '/ProyectoLibreriaLGI/' ? '' : '../'; ?>prestamos/historial.php">Historial</a></li>
                    </ul>
                </li>
            </ul>
            
            <div class="nav-user">
                <div class="user-info">
                    <span class="user-icon">👤</span>
                    <span class="user-name"><?php echo htmlspecialchars($_SESSION['nombre']); ?></span>
                    <span class="user-role">(<?php echo htmlspecialchars($_SESSION['rol']); ?>)</span>
                </div>
                <a href="<?php echo $_SERVER['REQUEST_URI'] === '/ProyectoLibreriaLGI/' ? '' : '../'; ?>logout.php" class="logout-btn">
                    <span class="logout-icon">🚪</span>
                    Salir
                </a>
            </div>
        </div>
    </nav>
    <?php endif; ?>
    
    <main class="main-content">
        <div class="container">