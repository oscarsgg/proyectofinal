<?php
header('Content-Type: application/json');

// Conexión a la base de datos
include 'config.php';

$type = $_GET['type'];
$id = $_GET['id'];

switch ($type) {
    case 'prospecto':
        $sql = "SELECT * FROM prospecto WHERE numero = ?";
        break;
    case 'empresa':
        $sql = "SELECT * FROM empresa WHERE numero = ?";
        break;
    case 'admin':
        $sql = "SELECT * FROM usuario WHERE numero = ? AND rol = 'ADM'";
        break;
    default:
        die(json_encode(['success' => false, 'message' => 'Tipo de usuario no válido']));
}

$stmt = $conexion->prepare($sql);
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    echo json_encode(['success' => true, 'user' => $user]);
} else {
    echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
}

$stmt->close();
$conexion->close();
?>