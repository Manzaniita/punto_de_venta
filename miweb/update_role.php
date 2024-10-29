<?php
require 'auth.php'; // Verificación de sesión
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_usuario = $_POST['nombre_usuario'];
    $nuevo_rol = $_POST['rol'];

    // Validar rol
    if (!in_array($nuevo_rol, ['usuario', 'admin'])) {
        echo "Rol no válido.";
        exit;
    }

    $sql = "UPDATE usuarios SET rol = :rol WHERE nombre_usuario = :nombre_usuario";
    $stmt = $conn->prepare($sql);
    
    try {
        $stmt->execute([
            ':rol' => $nuevo_rol,
            ':nombre_usuario' => $nombre_usuario,
        ]);
        header('Location: admin.php?mensaje=Rol actualizado correctamente'); // Redirigir de vuelta al panel de administración
        exit;
    } catch (PDOException $e) {
        echo "Error al actualizar el rol: " . $e->getMessage();
    }
}
