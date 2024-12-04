<?php
session_start();
include_once('../../../../Outsourcing/config.php');

// Verificar si el usuario está logueado y es una empresa
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'EMP') {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prospecto = isset($_POST['prospecto']) ? $_POST['prospecto'] : null;
    $vacante = isset($_POST['vacante']) ? $_POST['vacante'] : null;
    $nuevo_estatus = isset($_POST['nuevo_estatus']) ? $_POST['nuevo_estatus'] : null;

    if ($prospecto && $vacante && $nuevo_estatus) {
        $sql_update = "UPDATE solicitud SET estatus = ? WHERE prospecto = ? AND vacante = ?";
        $stmt = $conexion->prepare($sql_update);
        $stmt->bind_param("sii", $nuevo_estatus, $prospecto, $vacante);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Estatus actualizado correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar el estatus: ' . $stmt->error]);
        }

        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}

$conexion->close();
?>