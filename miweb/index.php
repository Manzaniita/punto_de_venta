<?php
require 'auth.php'; // Verifica si el usuario está autenticado

$usuario = $_SESSION['usuario'];
$rol = $_SESSION['rol']; // Asegúrate de que el rol también esté en la sesión
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página Principal</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container"> <!-- Contenedor principal -->
        <h1>Bienvenido, <?= htmlspecialchars($usuario); ?>!</h1>

        <!-- Enlaces de navegación -->
        <a href="logout.php">Cerrar sesión</a>
        <a href="update.php">Actualizar mis datos</a>

        <?php if ($rol === 'admin'): // Muestra el enlace solo si el usuario es admin ?>
            <a href="admin.php">Panel de Administración</a>
        <?php endif; ?>
    </div> <!-- Fin del contenedor principal -->
</body>
</html>
