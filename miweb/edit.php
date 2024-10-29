<?php
require 'auth.php'; // Asegúrate de que esto verifica que el usuario tenga acceso
require 'db.php';

if (isset($_GET['user'])) {
    $nombre_usuario = $_GET['user'];

    // Consulta para obtener datos del usuario
    $sql = "SELECT * FROM usuarios WHERE nombre_usuario = :nombre_usuario";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':nombre_usuario' => $nombre_usuario]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        die("Usuario no encontrado."); // Mensaje de error si no se encuentra el usuario
    }

    // Manejar la actualización de usuario
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nuevo_nombre_usuario = $_POST['nombre_usuario'] ?? $usuario['nombre_usuario'];
        $nuevo_correo = $_POST['correo'] ?? $usuario['correo'];
        $nuevo_nombre = $_POST['nombre'] ?? $usuario['nombre'];
        $nuevo_apellido = $_POST['apellido'] ?? $usuario['apellido'];
        $nuevo_dni = $usuario['dni']; // No se puede modificar el DNI
        $nueva_direccion = $_POST['direccion'] ?? $usuario['direccion'];
        $nuevo_telefono = $_POST['telefono'] ?? $usuario['telefono'];

        // Actualizar la contraseña solo si se proporciona una nueva
        if (!empty($_POST['contraseña'])) {
            $nueva_contrasena = password_hash($_POST['contraseña'], PASSWORD_BCRYPT);
        } else {
            $nueva_contrasena = $usuario['contrasena']; // Mantener la contraseña actual si no se proporciona una nueva
        }

        // Consulta de actualización
        $sql = "UPDATE usuarios SET nombre_usuario = :nombre_usuario, correo = :correo, nombre = :nombre, apellido = :apellido, direccion = :direccion, telefono = :telefono, contrasena = :contrasena WHERE nombre_usuario = :nombre_usuario_actual";
        $stmt = $conn->prepare($sql);

        try {
            $stmt->execute([
                ':nombre_usuario' => $nuevo_nombre_usuario,
                ':correo' => $nuevo_correo,
                ':nombre' => $nuevo_nombre,
                ':apellido' => $nuevo_apellido,
                ':direccion' => $nueva_direccion,
                ':telefono' => $nuevo_telefono,
                ':contrasena' => $nueva_contrasena,
                ':nombre_usuario_actual' => $nombre_usuario, // Mantener el usuario actual para la condición WHERE
            ]);
            header('Location: admin.php?mensaje=Usuario actualizado correctamente');
            exit;
        } catch (PDOException $e) {
            echo "Error al actualizar el usuario: " . $e->getMessage();
        }
    }
} else {
    die("No se ha proporcionado un usuario.");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuario</title>
    <link rel="stylesheet" href="css/styles.css">
    <script>
        function validateForm() {
            const newPassword = document.querySelector('input[name="contraseña"]').value;
            if (newPassword && newPassword.length < 4) {
                alert("La nueva contraseña debe tener al menos 4 caracteres.");
                return false; // Evita el envío del formulario
            }
            return true; // Permite el envío del formulario
        }
    </script>
</head>
<body>
    <div class="container"> <!-- Contenedor para centrar el formulario -->
        <h1>Editar Usuario: <?= htmlspecialchars($usuario['nombre_usuario']) ?></h1>
        <form action="editar.php?user=<?= urlencode($usuario['nombre_usuario']) ?>" method="post" onsubmit="return validateForm()">
            <label for="nombre_usuario">Nombre de usuario:</label>
            <input type="text" name="nombre_usuario" id="nombre_usuario" value="<?= htmlspecialchars($usuario['nombre_usuario']) ?>" required>

            <label for="correo">Correo:</label>
            <input type="email" name="correo" id="correo" value="<?= htmlspecialchars($usuario['correo']) ?>" required>

            <label for="nombre">Nombre:</label>
            <input type="text" name="nombre" id="nombre" value="<?= htmlspecialchars($usuario['nombre']) ?>" required>

            <label for="apellido">Apellido:</label>
            <input type="text" name="apellido" id="apellido" value="<?= htmlspecialchars($usuario['apellido']) ?>" required>

            <label for="dni">DNI:</label>
            <input type="text" name="dni" id="dni" value="<?= htmlspecialchars($usuario['dni']) ?>" readonly>

            <label for="direccion">Dirección:</label>
            <input type="text" name="direccion" id="direccion" value="<?= htmlspecialchars($usuario['direccion']) ?>">

            <label for="telefono">Teléfono:</label>
            <input type="text" name="telefono" id="telefono" value="<?= htmlspecialchars($usuario['telefono']) ?>">

            <label for="contraseña">Nueva Contraseña (dejar en blanco si no desea cambiarla):</label>
            <input type="password" name="contraseña" id="contraseña" minlength="4">

            <button type="submit">Actualizar</button>
        </form>
        <p><a href="admin.php">Volver al Panel de Administración</a></p>
    </div> <!-- Fin del contenedor -->
</body>
</html>
