<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/Outsourcing/config.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recoger los datos del formulario
    $correo = mysqli_real_escape_string($conexion, $_POST['correo']);
    $contrasenia = mysqli_real_escape_string($conexion, $_POST['contrasenia']);
    $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $primerApellido = mysqli_real_escape_string($conexion, $_POST['primerApellido']);
    $segundoApellido = mysqli_real_escape_string($conexion, $_POST['segundoApellido']);
    $fechaNacimiento = mysqli_real_escape_string($conexion, $_POST['fechaNacimiento']);
    $resumen = mysqli_real_escape_string($conexion, $_POST['resumen']);

    // Iniciar transacción
    mysqli_begin_transaction($conexion);

    try {
        // Insertar en la tabla Usuario
        $query_usuario = "INSERT INTO Usuario (correo, contrasenia, rol) VALUES (?, ?, 'PRO')";
        $stmt_usuario = mysqli_prepare($conexion, $query_usuario);
        mysqli_stmt_bind_param($stmt_usuario, "ss", $correo, $contrasenia);
        mysqli_stmt_execute($stmt_usuario);
        $id_usuario = mysqli_insert_id($conexion);

        // Insertar en la tabla Prospecto
        $query_prospecto = "INSERT INTO Prospecto (nombre, primerApellido, segundoApellido, resumen, fechaNacimiento, usuario) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt_prospecto = mysqli_prepare($conexion, $query_prospecto);
        mysqli_stmt_bind_param($stmt_prospecto, "sssssi", $nombre, $primerApellido, $segundoApellido, $resumen, $fechaNacimiento, $id_usuario);
        mysqli_stmt_execute($stmt_prospecto);
        $id_prospecto = mysqli_insert_id($conexion);

        // Insertar carreras estudiadas
        $query_carrera = "INSERT INTO Carreras_estudiadas (prospecto, carrera, anioConcluido) VALUES (?, ?, ?)";
        $stmt_carrera = mysqli_prepare($conexion, $query_carrera);
        
        foreach ($_POST['carreras'] as $index => $carrera) {
            $anio = $_POST['anios'][$index];
            mysqli_stmt_bind_param($stmt_carrera, "isi", $id_prospecto, $carrera, $anio);
            mysqli_stmt_execute($stmt_carrera);
        }

        // Confirmar la transacción
        mysqli_commit($conexion);
        header("Location: registro_prospecto.php?status=success");
        exit();
    } catch (Exception $e) {
        // Revertir la transacción en caso de error
        mysqli_rollback($conexion);
        header("Location: registro_prospecto.php?status=error");
        exit();
    }
} else {
    // Si alguien intenta acceder directamente a este archivo, redirigir al formulario
    header("Location: registro_prospecto.php");
    exit();
}
?>