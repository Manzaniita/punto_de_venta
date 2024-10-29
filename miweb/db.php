<?php
$host = 'localhost';
$db = 'web_profe';
$user = 'root';  // Usuario por defecto en XAMPP
$password = '';  // Contraseña vacía por defecto

try {
    $conn = new PDO("mysql:host=$host;dbname=$db", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Error en la conexión: " . $e->getMessage();
}
?>
