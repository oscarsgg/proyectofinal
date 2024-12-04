<?php
session_start();
include_once('../../../../Outsourcing/config.php');

// Verificar si el usuario está logueado y es una empresa
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'EMP') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$vacante_id = isset($_GET['vacante_id']) ? $_GET['vacante_id'] : null;
$estatus = isset($_GET['estatus']) ? $_GET['estatus'] : null;


// Obtener el número de la empresa asociada al usuario
$query_empresa = "SELECT numero FROM empresa WHERE usuario = $user_id";
$result_empresa = mysqli_query($conexion, $query_empresa);

if (mysqli_num_rows($result_empresa) == 0) {
    die("Error: No se encontró una empresa asociada a este usuario.");
}

$empresa = mysqli_fetch_assoc($result_empresa);
$empresa_id = $empresa['numero'];


// Consulta inicial
$sql = "SELECT s.*, p.nombre, p.primerApellido, p.segundoApellido, p.numTel, v.titulo as titulovacante, 
        e.nombre as nombre_estatus, s.fechaSolicitud as fecha_solicitud
        FROM solicitud s
        INNER JOIN prospecto AS p ON s.prospecto = p.numero
        INNER JOIN vacante AS v on s.vacante = v.numero
        INNER JOIN estatus_solicitud AS e ON s.estatus = e.codigo
        WHERE v.empresa = $empresa_id AND s.es_cancelada = false
        ORDER BY s.fechaSolicitud DESC";

// Filtrar por vacante si se especifica
if ($vacante_id) {
    $sql .= " AND s.vacante = $vacante_id";
}

// Filtrar por estatus si se especifica
if ($estatus) {
    $sql .= " AND";
    $sql .= " s.estatus = '$estatus'";
}

// Limitar resultados por defecto a 10 si no hay filtros
if (!$vacante_id && !$estatus) {
    $sql .= " LIMIT 10";
}

$result = $conexion->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<div class='solicitud'>";
        echo "<h3>" . $row['nombre'] . " " . $row['primerApellido'] . " " . $row['segundoApellido'] . "</h3>";
        echo "<p>Vacante: " . $row['titulovacante'] . "</p>";
        echo "<p>Fecha de solicitud: " . $row['fecha_solicitud'] . "</p>";
        echo "<p>Teléfono: " . $row['numTel'] . "</p>";
        echo "<p>Estatus: " . $row['nombre_estatus'] . "</p>";
        echo "<button class='btn ver-perfil' data-prospecto='" . $row['prospecto'] . "'>Ver Perfil</button>";
        
        // Opciones de cambio de estatus
        if ($row['estatus'] == 'PEND') {
            echo "<button class='btn cambiar-estatus' data-prospecto='" . $row['prospecto'] . "' data-vacante='" . $row['vacante'] . "' data-estatus='APRO'>Aprobar</button>";
            echo "<button class='btn btn-secondary cambiar-estatus' data-prospecto='" . $row['prospecto'] . "' data-vacante='" . $row['vacante'] . "' data-estatus='RECH'>Rechazar</button>";
        } elseif ($row['estatus'] == 'APRO') {
            echo "<button class='btn generar-contrato' data-prospecto='" . $row['prospecto'] . "' data-vacante='" . $row['vacante'] . "'>Generar Contrato</button>";
        } elseif ($row['estatus'] == 'PFRM') {
            echo "<button class='btn btn-secondary cambiar-estatus' data-prospecto='" . $row['prospecto'] . "' data-vacante='" . $row['vacante'] . "' data-estatus='RECH'>Rechazar</button>";
        }
        
        echo "</div>";
    }
} else {
    echo "<p>No se encontraron solicitudes.</p>";
}

$conexion->close();
?>
