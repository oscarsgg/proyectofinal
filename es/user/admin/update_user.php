<?php
header('Content-Type: application/json');

// Conexión a la base de datos
include 'config.php';
$id = $_POST['id'];
$userType = $_POST['userType'];

// Obtener el nombre del campo y su valor
$fieldName = array_keys($_POST)[2];  // El tercer elemento es el campo que se está actualizando
$fieldValue = $_POST[$fieldName];

switch ($userType) {
    case 'prospecto':
        $table = 'prospecto';
        break;
    case 'empresa':
        $table = 'empresa';
        break;
    case 'admin':
        $table = 'usuario';
        break;
    default:
        die(json_encode(['success' => false, 'message' => 'Tipo de usuario no válido']));
}

// Preparar la consulta SQL
$sql = "UPDATE $table SET $fieldName = ? WHERE numero = ?";
$stmt = $conexion->prepare($sql);

if ($stmt === false) {
    die(json_encode(['success' => false, 'message' => 'Error al preparar la consulta: ' . $conexion->error]));
}

// Determinar el tipo de dato para bind_param
$type = 's';  // Por defecto, asumimos que es una cadena
if ($fieldName === 'anios_experiencia') {
    $type = 'i';  // Si es años de experiencia, es un entero
}

$stmt->bind_param($type . 'i', $fieldValue, $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al actualizar: ' . $stmt->error]);
}

$stmt->close();
$conexion->close();
?>