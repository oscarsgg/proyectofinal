<?php
session_start();
include_once('../../../../Outsourcing/config.php');
// Set headers to always return JSON
header('Content-Type: application/json');

// Error handling function
function returnError($message) {
    echo json_encode(['success' => false, 'message' => $message]);
    exit();
}

// Verificar si el usuario está logueado y es una empresa
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'EMP') {
    returnError('No autorizado');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prospecto = $_POST['prospecto'];
    $vacante = $_POST['vacante'];
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_cierre = $_POST['fecha_cierre'];
    $salario = $_POST['salario'];
    $horas_diarias = $_POST['horas_diarias'];
    $horario = $_POST['horario'];
    $tipo_contrato = $_POST['tipo_contrato'];

    // Obtener el ID de la empresa asociada a la vacante
    $sql_empresa = "SELECT empresa FROM vacante WHERE numero = ?";
    $stmt_empresa = $conexion->prepare($sql_empresa);
    $stmt_empresa->bind_param("i", $vacante);
    $stmt_empresa->execute();
    $result_empresa = $stmt_empresa->get_result();
    
    if ($result_empresa->num_rows === 0) {
        returnError('No se encontró la empresa asociada a la vacante');
    }
    
    $empresa_id = $result_empresa->fetch_assoc()['empresa'];
    $stmt_empresa->close();

    // Verificar si el tipo de contrato existe
    $stmt_check = $conexion->prepare("SELECT codigo FROM tipo_contrato WHERE codigo = ?");
    $stmt_check->bind_param("s", $tipo_contrato);
    $stmt_check->execute();
    $result = $stmt_check->get_result();
    if ($result->num_rows == 0) {
        returnError('El tipo de contrato seleccionado no es válido');
    }
    $stmt_check->close();

    // Procesar y guardar la firma
    $firma_ruta = '';
    if (isset($_FILES['firma']) && $_FILES['firma']['error'] == 0) {
        $firma_nombre = 'firma_' . time() . '.png';
        $firma_ruta = '../../../../Outsourcing/firmas/' . $firma_nombre;
        
        // Asegurarse de que la carpeta 'firmas' existe
        if (!file_exists('../../../../Outsourcing/firmas')) {
            if (!mkdir('../../../../Outsourcing/firmas', 0777, true)) {
                returnError('No se pudo crear el directorio para las firmas');
            }
        }
        
        if (!move_uploaded_file($_FILES['firma']['tmp_name'], $firma_ruta)) {
            returnError('Error al guardar la firma: ' . error_get_last()['message']);
        }
        $firma_ruta = '/Outsourcing/firmas/' . $firma_nombre; // Ruta relativa para guardar en la base de datos
    } else {
        returnError('Error al subir la firma');
    }

    $sql = "INSERT INTO contrato (fechaInicio, fechaCierre, salario, horasDiarias, horario, prospecto, vacante, tipo_contrato, firma_empresa, empresa) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ssdiisissi", $fecha_inicio, $fecha_cierre, $salario, $horas_diarias, $horario, $prospecto, $vacante, $tipo_contrato, $firma_ruta, $empresa_id);

    if ($stmt->execute()) {
        // Actualizar el estatus de la solicitud a 'CONT' (Contratado)
        $sql_update = "UPDATE solicitud SET estatus = 'PFRM' WHERE prospecto = ? AND vacante = ?";
        $stmt_update = $conexion->prepare($sql_update);
        $stmt_update->bind_param("ii", $prospecto, $vacante);
        $stmt_update->execute();
        $stmt_update->close();

        echo json_encode(['success' => true, 'message' => 'Contrato generado correctamente']);
    } else {
        returnError('Error al generar el contrato: ' . $stmt->error);
    }

    $stmt->close();
} else {
    returnError('Método no permitido');
}

$conexion->close();
?>