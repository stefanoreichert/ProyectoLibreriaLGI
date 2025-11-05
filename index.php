<?php
session_start();

// Redireccionar según el estado de autenticación
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
} else {
    header('Location: login.php');
}
exit();
?>