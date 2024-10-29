<?php
// Incluir el archivo de autenticación para verificar sesión
require 'auth.php';
require 'db.php';

// Obtener el usuario actual de la sesión
$nombre_usuario = $_SESSION['usuario'];

// Obtener los datos actuales del usuario de la base de datos
$sql = "SELECT * FROM usuarios WHERE nombre_usuario = :nombre_usuario";
$stmt = $conn->prepare($sql);
$stmt->execute([':nombre_usuario' => $nombre_usuario]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener datos del formulario de actualización
    $nuevo_nombre_usuario = $_POST['nombre_usuario'] ?? $usuario['nombre_usuario'];
    $nuevo_email = $_POST['correo'] ?? $usuario['correo'];
    $nuevo_nombre = $_POST['nombre'] ?? $usuario['nombre'];
    $nuevo_apellido = $_POST['apellido'] ?? $usuario['apellido'];
    $nuevo_dni = $usuario['dni']; // No se puede modificar el DNI
    $nueva_direccion = $_POST['direccion'] ?? $usuario['direccion'];
    $nuevo_telefono = $_POST['telefono'] ?? $usuario['telefono'];

    // Validar longitud de la nueva contraseña
    if (!empty($_POST['contraseña']) && strlen($_POST['contraseña']) < 4) {
        echo "Error: La nueva contraseña debe tener al menos 4 caracteres.";
        exit; // Detener la ejecución del script
    }

    // Actualizar la contraseña solo si se proporciona una nueva
    if (!empty($_POST['contraseña'])) {
        $nueva_contrasena = password_hash($_POST['contraseña'], PASSWORD_BCRYPT);
    } else {
        $nueva_contrasena = $usuario['contrasena']; // Mantener la contraseña actual si no se proporciona una nueva
    }

    // Consulta de actualización, no se puede modificar el DNI
    $sql = "UPDATE usuarios SET nombre_usuario = :nombre_usuario, correo = :correo, nombre = :nombre, apellido = :apellido, direccion = :direccion, telefono = :telefono, contrasena = :contrasena WHERE nombre_usuario = :nombre_usuario_actual";
    $stmt = $conn->prepare($sql);

    try {
        $stmt->execute([
            ':nombre_usuario' => $nuevo_nombre_usuario,
            ':correo' => $nuevo_email,
            ':nombre' => $nuevo_nombre,
            ':apellido' => $nuevo_apellido,
            ':direccion' => $nueva_direccion,
            ':telefono' => $nuevo_telefono,
            ':contrasena' => $nueva_contrasena,
            ':nombre_usuario_actual' => $nombre_usuario, // Mantener el usuario actual para la condición WHERE
        ]);
        echo "Datos actualizados correctamente.";
        // Actualizar la sesión con el nuevo nombre de usuario si ha cambiado
        if ($nuevo_nombre_usuario !== $nombre_usuario) {
            $_SESSION['usuario'] = $nuevo_nombre_usuario;
        }
    } catch (PDOException $e) {
        echo "Error al actualizar los datos: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualizar Perfil</title>
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
        <h1>Actualizar Datos del Usuario</h1>
        <form action="update.php" method="post" onsubmit="return validateForm()">
            <label for="nombre_usuario">Nombre de usuario:</label>
            <input type="text" name="nombre_usuario" id="nombre_usuario" value="<?= htmlspecialchars($usuario['nombre_usuario'] ?? '') ?>" required>

            <label for="correo">Correo:</label>
            <input type="email" name="correo" id="correo" value="<?= htmlspecialchars($usuario['correo'] ?? '') ?>" required>

            <label for="nombre">Nombre:</label>
            <input type="text" name="nombre" id="nombre" value="<?= htmlspecialchars($usuario['nombre'] ?? '') ?>" required>

            <label for="apellido">Apellido:</label>
            <input type="text" name="apellido" id="apellido" value="<?= htmlspecialchars($usuario['apellido'] ?? '') ?>" required>

            <label for="dni">DNI:</label>
            <input type="text" name="dni" id="dni" value="<?= htmlspecialchars($usuario['dni'] ?? '') ?>" readonly>

            <label for="direccion">Dirección:</label>
            <input type="text" name="direccion" id="direccion" value="<?= htmlspecialchars($usuario['direccion'] ?? '') ?>">

            <label for="telefono">Teléfono:</label>
            <input type="text" name="telefono" id="telefono" value="<?= htmlspecialchars($usuario['telefono'] ?? '') ?>">

            <label for="contraseña">Nueva Contraseña (dejar en blanco si no desea cambiarla):</label>
            <input type="password" name="contraseña" id="contraseña" minlength="4">

            <button type="submit">Actualizar</button>
        </form>
        <p><a href="index.php">Volver a Inicio</a></p>
    </div> <!-- Fin del contenedor -->
</body>
</html>
