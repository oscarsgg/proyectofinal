<?php
session_start();
include_once($_SERVER['DOCUMENT_ROOT'] . '/Outsourcing/config.php');

// Verificar si el usuario está logueado y es una empresa
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'EMP') {
    header("Location: login.php");
    exit();
}

$vacante_id = $_GET['vacante_id'];
$estatus = isset($_GET['estatus']) ? $_GET['estatus'] : null;

$sql = "SELECT s.*, p.nombre, p.primerApellido, p.segundoApellido, p.numTel, e.nombre as nombre_estatus
        FROM Solicitud s
        JOIN Prospecto p ON s.prospecto = p.numero
        JOIN Estatus_Solicitud e ON s.estatus = e.codigo
        WHERE s.vacante = $vacante_id";
if ($estatus) {
    $sql .= " AND s.estatus = '$estatus'";
}

$result = $conexion->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "<div class='solicitud'>";
        echo "<h3>" . $row['nombre'] . " " . $row['primerApellido'] . " " . $row['segundoApellido'] . "</h3>";
        echo "<p>Teléfono: " . $row['numTel'] . "</p>";
        echo "<p>Estatus: " . $row['nombre_estatus'] . "</p>";
        echo "<button class='btn ver-perfil' data-prospecto='" . $row['prospecto'] . "'>View profile</button>";
        
        // Opciones de cambio de estatus
        if ($row['estatus'] == 'PEND') {
            echo "<button class='btn cambiar-estatus' data-prospecto='" . $row['prospecto'] . "' data-vacante='" . $row['vacante'] . "' data-estatus='APRO'>Aprobar</button>";
            echo "<button class='btn btn-secondary cambiar-estatus' data-prospecto='" . $row['prospecto'] . "' data-vacante='" . $row['vacante'] . "' data-estatus='RECH'>Rechazar</button>";
        } elseif ($row['estatus'] == 'APRO') {
            echo "<button class='btn cambiar-estatus' data-prospecto='" . $row['prospecto'] . "' data-vacante='" . $row['vacante'] . "' data-estatus='PFRM'>Generar Contrato</button>";
        } elseif ($row['estatus'] == 'PFRM') {
            echo "<button class='btn btn-secondary cambiar-estatus' data-prospecto='" . $row['prospecto'] . "' data-vacante='" . $row['vacante'] . "' data-estatus='RECH'>Rechazar</button>";
        }
        
        echo "</div>";
    }
} else {
    echo "<p>No applications found for this vacancy.</p>";
}

$conexion->close();
?>