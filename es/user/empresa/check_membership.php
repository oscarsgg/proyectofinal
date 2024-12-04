<?php
// check_membership.php

include_once('../../../../Outsourcing/config.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Obtener el número de la empresa asociada al usuario
$query_empresa = "SELECT numero FROM empresa WHERE usuario = $user_id";
$result_empresa = mysqli_query($conexion, $query_empresa);

if (mysqli_num_rows($result_empresa) == 0) {
    die("Error: No se encontró una empresa asociada a este usuario.");
}

$empresa = mysqli_fetch_assoc($result_empresa);
$empresa_id = $empresa['numero'];

// Verificar el estado de la membresía
$query = "SELECT fechaVencimiento FROM membresia WHERE empresa = $empresa_id ORDER BY fechaVencimiento DESC LIMIT 1";
$result = mysqli_query($conexion, $query);
$membresia = mysqli_fetch_assoc($result);

if (!$membresia || strtotime($membresia['fechaVencimiento']) < time()) {
    // Redirigir a la página de membresía si ha expirado
    header("Location: membresia.php");
    exit();
}