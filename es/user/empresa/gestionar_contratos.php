<?php
// gestionar_contratos.php

session_start();
include_once('../../../../Outsourcing/config.php');
require_once 'check_membership.php';

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Obtener el número de la empresa asociada al usuario
$query_empresa = "SELECT numero FROM empresa WHERE usuario = ?";
$stmt_empresa = mysqli_prepare($conexion, $query_empresa);
mysqli_stmt_bind_param($stmt_empresa, "i", $user_id);
mysqli_stmt_execute($stmt_empresa);
$result_empresa = mysqli_stmt_get_result($stmt_empresa);

if (mysqli_num_rows($result_empresa) == 0) {
    die("Error: No se encontró una empresa asociada a este usuario.");
}

$empresa = mysqli_fetch_assoc($result_empresa);
$empresa_id = $empresa['numero'];

// Obtener el término de búsqueda y el filtro de estatus
$search = isset($_GET['search']) ? $_GET['search'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

// Preparar la consulta base
$query = "SELECT c.numero, c.fechaInicio, c.fechaCierre, tc.nombre AS nombre_contrato, 
                 v.titulo AS vacante_titulo, 
                 p.nombre AS prospecto_nombre, p.primerApellido, p.segundoApellido
          FROM contrato AS c
          INNER JOIN vacante AS v ON c.vacante = v.numero
          INNER JOIN prospecto AS p ON c.prospecto = p.numero
          INNER JOIN tipo_contrato AS tc ON c.tipo_contrato = tc.codigo
          WHERE c.empresa = ?";

$params = array($empresa_id);
$types = "i";

if (!empty($search)) {
    $query .= " AND (v.titulo LIKE ? OR p.nombre LIKE ? OR p.primerApellido LIKE ? OR p.segundoApellido LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, array($search_param, $search_param, $search_param, $search_param));
    $types .= "ssss";
}

if (!empty($status_filter)) {
    switch ($status_filter) {
        case 'activo':
            $query .= " AND c.fechaInicio <= CURDATE() AND (c.fechaCierre >= CURDATE() OR c.fechaCierre IS NULL)";
            break;
        case 'terminado':
            $query .= " AND c.fechaCierre < CURDATE()";
            break;
        case 'pendiente':
            $query .= " AND c.fechaInicio > CURDATE()";
            break;
    }
}

$query .= " ORDER BY c.fechaInicio DESC";

$stmt = mysqli_prepare($conexion, $query);
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Función para determinar el estatus del contrato
function getContractStatus($fechaInicio, $fechaCierre) {
    $today = new DateTime();
    $start = new DateTime($fechaInicio);
    $end = $fechaCierre ? new DateTime($fechaCierre) : null;

    if ($start > $today) {
        return ['status' => 'Pendiente', 'class' => 'status-pending'];
    } elseif (!$end || $end >= $today) {
        return ['status' => 'Activo', 'class' => 'status-active'];
    } else {
        return ['status' => 'Terminado', 'class' => 'status-inactive'];
    }
}

// Función para formatear la fecha
function formatearFecha($fecha) {
    $meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
    $fechaObj = new DateTime($fecha);
    $dia = $fechaObj->format('j');
    $mes = $meses[(int)$fechaObj->format('n') - 1];
    $año = $fechaObj->format('Y');
    return "$dia de $mes de $año";
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Contratos - Sistema de Outsourcing</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&family=Poppins:wght@500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/gestionarContratos.css">
</head>
<body>
    <?php include 'incluides/sidebar.php'; ?>
    <div class="container">
        <h1>Gestionar Contratos</h1>
        
        <form action="" method="GET" class="search-container">
            <input type="text" name="search" placeholder="Buscar contratos..." value="<?php echo htmlspecialchars($search); ?>">
            <select name="status">
                <option value="">Todos los estados</option>
                <option value="activo" <?php echo $status_filter === 'activo' ? 'selected' : ''; ?>>Activos</option>
                <option value="terminado" <?php echo $status_filter === 'terminado' ? 'selected' : ''; ?>>Terminados</option>
                <option value="pendiente" <?php echo $status_filter === 'pendiente' ? 'selected' : ''; ?>>Pendientes</option>
            </select>
            <button type="submit">Buscar</button>
        </form>

        <div class="contratos-list">
            <?php
            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $status = getContractStatus($row['fechaInicio'], $row['fechaCierre']);
                    ?>
                    <div class="contrato-item">
                        <div class="contrato-details">
                            <div class="contrato-title"><?php echo htmlspecialchars($row['vacante_titulo']); ?></div>
                            <div class="contrato-info">
                                Prospecto: <?php echo htmlspecialchars($row['prospecto_nombre'] . ' ' . $row['primerApellido'] . ' ' . $row['segundoApellido']); ?><br>
                                Fecha de inicio: <?php echo htmlspecialchars(formatearFecha($row['fechaInicio'])); ?><br>
                                Fecha de cierre: <?php echo $row['fechaCierre'] ? htmlspecialchars(formatearFecha($row['fechaCierre'])) : 'No especificada'; ?><br>
                                Tipo de contrato: <?php echo htmlspecialchars($row['nombre_contrato']); ?>
                            </div>
                            <span class="contrato-status <?php echo $status['class']; ?>">
                                <?php echo $status['status']; ?>
                            </span>
                        </div>
                        <a href="detalle_contrato.php?id=<?php echo $row['numero']; ?>" class="contrato-link">Ver Detalles</a>
                    </div>
                    <?php
                }
            } else {
                echo "<p>No se encontraron contratos.</p>";
            }
            ?>
        </div>
    </div>
</body>
</html>