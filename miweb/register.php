<?php
require 'db.php'; // Asegúrate de incluir la conexión a la base de datos

// Solo ejecutar si el formulario ha sido enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    // Obtener datos del formulario
    $username = $_POST['nombre_usuario'];
    $email = $_POST['correo'];
    $password = $_POST['contraseña'];
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $dni = $_POST['dni'];
    $direccion = $_POST['direccion'] ?? ''; // Opcional
    $telefono = $_POST['telefono'] ?? ''; // Opcional
    $rol = 'user'; // Asignar rol de usuario por defecto

    // Validar longitud de la contraseña
    if (strlen($password) < 4) {
        echo "Error: La contraseña debe tener al menos 4 caracteres.";
        exit; // Detener la ejecución del script
    }

    // Encriptar la contraseña
    $password = password_hash($password, PASSWORD_BCRYPT);

    // Verificar si el nombre de usuario ya existe
    $sql_check = "SELECT COUNT(*) FROM usuarios WHERE nombre_usuario = :username";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->execute([':username' => $username]);
    $count = $stmt_check->fetchColumn();

    if ($count > 0) {
        echo "Error: El nombre de usuario '$username' ya está en uso. Por favor elige otro.";
    } else {
        // Consulta SQL con marcadores de posición
        $sql = "INSERT INTO usuarios (nombre_usuario, correo, contrasena, nombre, apellido, dni, direccion, telefono, rol) 
                VALUES (:username, :email, :password, :nombre, :apellido, :dni, :direccion, :telefono, :rol)";
        $stmt = $conn->prepare($sql);

        // Ejecutar la consulta con un array de parámetros
        try {
            $stmt->execute([
                ':username' => $username,
                ':email' => $email,
                ':password' => $password,
                ':nombre' => $nombre,
                ':apellido' => $apellido,
                ':dni' => $dni,
                ':direccion' => $direccion,
                ':telefono' => $telefono,
                ':rol' => $rol, // Almacenar rol
            ]);
            echo "Registro exitoso.";
            header('Location: login.php'); // Redirigir después del registro exitoso
            exit; // Asegúrate de salir después de redirigir
        } catch (PDOException $e) {
            echo "Error en el registro: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro</title>
    <link rel="stylesheet" href="css/styles.css">
    <script>
        function validateForm() {
            const password = document.querySelector('input[name="contraseña"]').value;
            if (password.length < 4) {
                alert("La contraseña debe tener al menos 4 caracteres.");
                return false; // Evita el envío del formulario
            }
            return true; // Permite el envío del formulario
        }
    </script>
</head>
<body>
    <div class="container"> <!-- Contenedor para centrar el formulario -->
        <h1>Registro de Usuario</h1>
        <form action="register.php" method="post" onsubmit="return validateForm()">
            <input type="text" name="nombre_usuario" placeholder="Nombre de usuario" required>
            <input type="email" name="correo" placeholder="Correo electrónico" required>
            <input type="password" name="contraseña" placeholder="Contraseña" required minlength="4">
            <input type="text" name="nombre" placeholder="Nombre" required>
            <input type="text" name="apellido" placeholder="Apellido" required>
            <input type="text" name="dni" placeholder="DNI" required>
            <input type="text" name="direccion" placeholder="Dirección">
            <input type="text" name="telefono" placeholder="Teléfono">
            <button type="submit" name="register">Registrar</button>
        </form>
        <p>¿Ya tienes una cuenta? <a href="login.php">Inicia sesión aquí</a></p>
    </div> <!-- Fin del contenedor -->
</body>
</html>
