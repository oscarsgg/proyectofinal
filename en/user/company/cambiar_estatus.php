<?php
session_start();
include_once($_SERVER['DOCUMENT_ROOT'] . '/Outsourcing/config.php');

// Verificar si el usuario está logueado y es una empresa
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'EMP') {
    header("Location: login.php");
    exit();
}

$solicitud_prospecto = $_POST['solicitud_prospecto'];
$solicitud_vacante = $_POST['solicitud_vacante'];
$nuevo_estatus = $_POST['nuevo_estatus'];

$sql_update = "UPDATE Solicitud SET estatus = '$nuevo_estatus' 
               WHERE prospecto = $solicitud_prospecto AND vacante = $solicitud_vacante";

if ($conexion->query($sql_update) === TRUE) {
    echo "Successfully updated status";
} else {
    echo "Error updating status: " . $conexion->error;
}

$conexion->close();
?>