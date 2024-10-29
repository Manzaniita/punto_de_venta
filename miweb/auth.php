<?php
session_start();

// Verifica si el usuario está autenticado
if (!isset($_SESSION['usuario'])) {
    $_SESSION['error'] = "Necesitas iniciar sesión.";
    header('Location: login.php'); // Redirigir si no está autenticado
    exit;
}
?>
