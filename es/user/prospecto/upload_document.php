<?php
session_start();
include '../../../config.php';
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Usuario no autenticado']);
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $docType = $_POST['docType'];
    $file = $_FILES['file'];

    $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png'];
    if (!in_array($file['type'], $allowedTypes)) {
        echo json_encode(['error' => 'Tipo de archivo no permitido. Se permiten PDF, JPG y PNG.']);
        exit;
    }

    $uploadDir = 'documentacion/';
    if (!file_exists($uploadDir)) {
        if (!mkdir($uploadDir, 0777, true)) {
            echo json_encode(['error' => 'No se pudo crear el directorio de subida']);
            exit;
        }
    }

    $fileName = uniqid() . '_' . $file['name'];
    $filePath = $uploadDir . $fileName;

    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        $columnName = '';
        switch ($docType) {
            case 'rfc':
                $columnName = 'rfc';
                break;
            case 'actaNacimiento':
                $columnName = 'acta_nacimiento';
                break;
            case 'gradosAcademicos':
                $columnName = 'grados_academicos';
                break;
            default:
                echo json_encode(['error' => 'Tipo de documento no válido']);
                exit;
        }

        $stmt = $conexion->prepare("UPDATE prospecto SET $columnName = ? WHERE usuario = ?");
        $stmt->bind_param('si', $fileName, $user_id);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'fileName' => $fileName]);
        } else {
            unlink($filePath); // Eliminar el archivo si no se pudo actualizar la base de datos
            echo json_encode(['error' => 'Error al actualizar la base de datos: ' . $stmt->error]);
        }
    } else {
        echo json_encode(['error' => 'Error al mover el archivo subido. Código de error: ' . $file['error']]);
    }
} else {
    echo json_encode(['error' => 'Solicitud no válida o archivo no recibido']);
}
?>

