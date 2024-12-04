<?php
session_start();
include_once('../../../../Outsourcing/config.php');


// Verificar si el usuario está logueado y es un prospecto
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'PRO') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Obtener el número de página actual
$pagina = isset($_GET['pagina']) ? (int) $_GET['pagina'] : 1;
$limite = 10;
$offset = ($pagina - 1) * $limite;

// Filtros
$filtro_estatus = isset($_GET['estatus']) ? $_GET['estatus'] : '';
$filtro_fecha = isset($_GET['fecha']) ? $_GET['fecha'] : '';

// Construir la consulta base
$query_base = "
    SELECT s.*, v.titulo, e.nombre AS nombre_estatus
    FROM solicitud as s
    INNER JOIN vacante as v ON s.vacante = v.numero
    INNER JOIN estatus_solicitud as e ON s.estatus = e.codigo
    WHERE s.prospecto = (SELECT numero FROM prospecto WHERE usuario = ?)
";

// Agregar filtros a la consulta
$params = [$user_id];
$types = "i";
if ($filtro_estatus) {
    $query_base .= " AND s.estatus = ?";
    $params[] = $filtro_estatus;
    $types .= "s";
}
// if ($filtro_fecha) {
//     $query_base .= " AND DATE(s.fecha_solicitud) = ?";
//     $params[] = $filtro_fecha;
//     $types .= "s";
// }

// Consulta para obtener el total de solicitudes
$query_count = "SELECT COUNT(*) as total FROM (" . $query_base . ") AS subquery";
$stmt_count = $conexion->prepare($query_count);
$stmt_count->bind_param($types, ...$params);
$stmt_count->execute();
$result_count = $stmt_count->get_result();
$total_solicitudes = $result_count->fetch_assoc()['total'];

// Calcular el número total de páginas
$total_paginas = ceil($total_solicitudes / $limite);

// Consulta final con paginación
$query_final = $query_base . " LIMIT ? OFFSET ?";
$params[] = $limite;
$params[] = $offset;
$types .= "ii";

$stmt = $conexion->prepare($query_final);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$solicitudes = $result->fetch_all(MYSQLI_ASSOC);

// Obtener lista de estatus para el filtro
$query_estatus = "SELECT codigo, nombre FROM estatus_solicitud";
$result_estatus = $conexion->query($query_estatus);
$lista_estatus = $result_estatus->fetch_all(MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Solicitudes</title>
    <link rel="stylesheet" href="css/solicitudes.css">
</head>

<body>
    <?php include 'incluides/sidebar.php'; ?>

    <div class="solicitudes-container">
        <h1>Gestionar Solicitudes</h1>

        <h2>Últimas novedades de tus solicitudes</h2>

        <div class="filtros">
            <form action="" method="GET" id="filtro-form">
                <label for="estatus">Filtrar por estatus:</label>
                <select name="estatus" id="estatus" onchange="document.getElementById('filtro-form').submit();">
                    <option value="">Todos</option>
                    <?php foreach ($lista_estatus as $estatus): ?>
                        <option value="<?php echo $estatus['codigo']; ?>" <?php echo ($filtro_estatus == $estatus['codigo']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($estatus['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>

        <?php if (count($solicitudes) > 0): ?>
            <?php foreach ($solicitudes as $solicitud): ?>

                <div class="card">
                    <a class="card2" href="detalles_vacante.php?id=<?php echo urlencode($solicitud['vacante']); ?>">
                        <p><?php echo htmlspecialchars($solicitud['titulo']); ?></p>
                        <p class="small">
                            <?php
                            switch ($solicitud['estatus']) {
                                case 'PEND':
                                    echo "Su solicitud está pendiente de revisión.";
                                    break;
                                case 'APRO':
                                    echo "¡Felicidades! Su solicitud ha sido aprobada.";
                                    break;
                                case 'RECH':
                                    echo "Lo sentimos, su solicitud ha sido rechazada.";
                                    break;
                                case 'PFRM':
                                    echo "Su contrato está pendiente de firma.";
                                    break;
                                case 'CERR':
                                    echo "Esta solicitud ha sido cerrada.";
                                    break;
                                default:
                                    echo "Estado desconocido.";
                                    break;
                            }
                            ?>
                        </p>
                        <div class="go-corner" href="#">
                            <div class="go-arrow">
                                →
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>

            <div class="paginacion">
                <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                    <a href="?pagina=<?php echo $i; ?>&estatus=<?php echo $filtro_estatus; ?>"
                        class="<?php echo ($pagina == $i) ? 'actual' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php else: ?>
            <p>No se encontraron solicitudes.</p>
        <?php endif; ?>

    </div>
</body>

</html>