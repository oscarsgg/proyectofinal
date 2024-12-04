<?php
session_start();
include_once('../../../../Outsourcing/config.php');


// Verificar si el usuario está autenticado y es un prospecto
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'PRO') {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['firma_prospecto']) && isset($_POST['contrato_id'])) {
    $firma_prospecto = $_POST['firma_prospecto'];
    $contrato_id = intval($_POST['contrato_id']);

    // Generar un nombre único para el archivo de firma
    $firma_nombre = 'firma_prospecto_' . time() . '.png';
    $firma_ruta = '../../../../Outsourcing/firmas/' . $firma_nombre;

    // Asegurarse de que la carpeta 'firmas' existe
    if (!file_exists('../../../../Outsourcing/firmas')) {
        if (!mkdir('../../../../Outsourcing/firmas', 0777, true)) {
            echo json_encode(['success' => false, 'message' => 'No se pudo crear el directorio para las firmas']);
            exit();
        }
    }

    // Decodificar la imagen base64 y guardarla
    $firma_data = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $firma_prospecto));
    if (file_put_contents($firma_ruta, $firma_data) === false) {
        echo json_encode(['success' => false, 'message' => 'Error al guardar la firma']);
        exit();
    }

    // Actualizar la base de datos con la ruta de la firma
    $firma_ruta_db = '/Outsourcing/firmas/' . $firma_nombre;
    $query = "UPDATE contrato SET firma_prospecto = ? WHERE numero = ?";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("si", $firma_ruta_db, $contrato_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Firma guardada exitosamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar la base de datos']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
}