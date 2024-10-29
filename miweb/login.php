<?php
require 'db.php'; // Asegúrate de incluir la conexión a la base de datos

if (isset($_POST['login'])) {
    $nombre_usuario = $_POST['nombre_usuario'];
    $contraseña = $_POST['contraseña'];

    // Consulta para obtener el usuario por nombre de usuario
    $sql = "SELECT * FROM usuarios WHERE nombre_usuario = :nombre_usuario";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':nombre_usuario' => $nombre_usuario]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verificar la contraseña
    if ($user && password_verify($contraseña, $user['contrasena'])) {
        session_start(); // Iniciar sesión
        $_SESSION['usuario'] = $user['nombre_usuario'];
        $_SESSION['rol'] = $user['rol'];
        
        // Redirigir según el rol del usuario
        if ($user['rol'] === 'admin') {
            header('Location: admin.php');
        } else {
            header('Location: index.php'); // Redirigir a index.php si es usuario normal
        }
        exit; // Asegúrate de salir después de redirigir
    } else {
        echo "<p class='error'>Credenciales incorrectas.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container"> <!-- Contenedor para centrar el formulario -->
        <h1>Iniciar Sesión</h1>
        <form action="login.php" method="post">
            <input type="text" name="nombre_usuario" placeholder="Nombre de usuario" required>
            <input type="password" name="contraseña" placeholder="Contraseña" required>
            <button type="submit" name="login">Iniciar Sesión</button>
        </form>
        <p>¿No tienes una cuenta? <a href="register.php">Crea una aquí</a></p>
    </div> <!-- Fin del contenedor -->
</body>
</html>
