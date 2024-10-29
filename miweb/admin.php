<?php
// Incluir el archivo de autenticación para verificar sesión
require 'auth.php'; // Asegúrate de que este archivo tenga la verificación del rol de admin
require 'db.php';

// Obtener la lista de usuarios registrados
$sql = "SELECT * FROM usuarios";
$stmt = $conn->prepare($sql);
$stmt->execute();
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Manejar la creación de nuevos usuarios
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_usuario'])) {
    $nombre_usuario = $_POST['nombre_usuario'];
    $correo = $_POST['correo'];
    $contrasena = password_hash($_POST['contrasena'], PASSWORD_BCRYPT);
    $rol = $_POST['rol'];

    // Validar rol
    if (!in_array($rol, ['usuario', 'admin'])) {
        echo "Rol no válido.";
        exit;
    }

    // Verificar que el nombre de usuario no exista ya
    $sql = "SELECT COUNT(*) FROM usuarios WHERE nombre_usuario = :nombre_usuario";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':nombre_usuario' => $nombre_usuario]);
    if ($stmt->fetchColumn() > 0) {
        echo "El nombre de usuario ya está en uso.";
        exit;
    }

    $sql = "INSERT INTO usuarios (nombre_usuario, correo, contrasena, rol) VALUES (:nombre_usuario, :correo, :contrasena, :rol)";
    $stmt = $conn->prepare($sql);

    try {
        $stmt->execute([
            ':nombre_usuario' => $nombre_usuario,
            ':correo' => $correo,
            ':contrasena' => $contrasena,
            ':rol' => $rol,
        ]);
        header('Location: admin.php?mensaje=Usuario creado correctamente');
        exit;
    } catch (PDOException $e) {
        echo "Error al crear el usuario: " . $e->getMessage();
    }
}

// Manejar la eliminación de usuarios
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['borrar_usuario'])) {
    $nombre_usuario_borrar = $_POST['nombre_usuario_borrar'];

    // Verificar que el usuario a borrar no sea el mismo que el administrador logueado
    if ($nombre_usuario_borrar === $_SESSION['usuario']) {
        echo "No puedes borrar tu propia cuenta.";
        exit;
    }

    $sql = "DELETE FROM usuarios WHERE nombre_usuario = :nombre_usuario";
    $stmt = $conn->prepare($sql);
    
    try {
        $stmt->execute([':nombre_usuario' => $nombre_usuario_borrar]);
        header('Location: admin.php?mensaje=Usuario borrado correctamente');
        exit;
    } catch (PDOException $e) {
        echo "Error al borrar el usuario: " . $e->getMessage();
    }
}

// Manejar la actualización de roles
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_role'])) {
    $nombre_usuario = $_POST['nombre_usuario'];
    $nuevo_rol = $_POST['rol'];

    // Verificar que el usuario que se está modificando no sea el mismo que el administrador logueado
    if ($nombre_usuario === $_SESSION['usuario'] && $nuevo_rol !== 'admin') {
        echo "No puedes quitarte a ti mismo los permisos de administrador.";
        exit;
    }

    // Actualizar el rol en la base de datos
    $sql_update = "UPDATE usuarios SET rol = :rol WHERE nombre_usuario = :nombre_usuario";
    $stmt_update = $conn->prepare($sql_update);
    
    try {
        $stmt_update->execute([
            ':rol' => $nuevo_rol,
            ':nombre_usuario' => $nombre_usuario
        ]);
        header('Location: admin.php?mensaje=Rol actualizado correctamente');
        exit;
    } catch (PDOException $e) {
        echo "Error al actualizar el rol: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container"> <!-- Contenedor principal -->
        <h1>Panel de Administración</h1>

        <?php if (isset($_GET['mensaje'])): ?>
            <div style="color: green; margin-bottom: 20px;"><?= htmlspecialchars($_GET['mensaje']) ?></div>
        <?php endif; ?>

        <h2>Crear Nuevo Usuario</h2>
        <form action="admin.php" method="post">
            <input type="text" name="nombre_usuario" placeholder="Nombre de Usuario" required>
            <input type="email" name="correo" placeholder="Correo Electrónico" required>
            <input type="password" name="contrasena" placeholder="Contraseña" required>
            <select name="rol">
                <option value="usuario">Usuario</option>
                <option value="admin">Admin</option>
            </select>
            <button type="submit" name="crear_usuario">Crear Usuario</button>
        </form>

        <h2>Usuarios Registrados</h2>
        <table>
            <tr>
                <th>Nombre de Usuario</th>
                <th>Correo</th>
                <th>Rol</th>
                <th>Acciones</th>
            </tr>
            <?php foreach ($usuarios as $usuario): ?>
                <tr>
                    <td><?= htmlspecialchars($usuario['nombre_usuario']) ?></td>
                    <td><?= htmlspecialchars($usuario['correo']) ?></td>
                    <td><?= htmlspecialchars($usuario['rol']) ?></td>
                    <td>
                        <a href="edit.php?user=<?= urlencode($usuario['nombre_usuario']) ?>">Editar</a>
                        <form action="admin.php" method="post" style="display:inline;">
                            <input type="hidden" name="nombre_usuario" value="<?= htmlspecialchars($usuario['nombre_usuario']) ?>">
                            <select name="rol" style="margin-right: 5px;">
                                <option value="usuario" <?= $usuario['rol'] === 'usuario' ? 'selected' : '' ?>>Usuario</option>
                                <option value="admin" <?= $usuario['rol'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                            </select>
                            <button type="submit" name="update_role">Actualizar Rol</button>
                        </form>
                        <form action="admin.php" method="post" style="display:inline;">
                            <input type="hidden" name="nombre_usuario_borrar" value="<?= htmlspecialchars($usuario['nombre_usuario']) ?>">
                            <button type="submit" name="borrar_usuario" onclick="return confirm('¿Estás seguro de que deseas borrar este usuario?');">Borrar</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <p><a href="index.php">Volver al inicio</a></p>
    </div> <!-- Fin del contenedor principal -->
</body>
</html>
