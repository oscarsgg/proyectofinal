<?php
session_start();
include_once('../../../../Outsourcing/config.php');

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Procesar la búsqueda
$search_keyword = isset($_GET['search_keyword']) ? mysqli_real_escape_string($conexion, $_GET['search_keyword']) : '';
$search_city = isset($_GET['search_city']) ? mysqli_real_escape_string($conexion, $_GET['search_city']) : '';

$query_vacantes = "
    SELECT v.numero, v.estado, v.titulo, v.descripcion, v.salario, v.fechaInicio, v.fechaCierre,
           e.nombre AS empresa_nombre, e.ciudad, e.colonia, tc.nombre AS tipo_contrato
    FROM vacante as v
    INNER JOIN empresa as e ON v.empresa = e.numero
    INNER JOIN tipo_contrato as tc ON v.tipo_contrato = tc.codigo
    WHERE v.fechaCierre >= CURDATE() AND v.fechaInicio <= CURDATE() AND v.estado = true
";

if (!empty($search_keyword)) {
    $query_vacantes .= " AND (v.titulo LIKE '%$search_keyword%' OR v.descripcion LIKE '%$search_keyword%')";
}

if (!empty($search_city)) {
    $query_vacantes .= " AND e.ciudad LIKE '%$search_city%'";
}

$query_vacantes .= " ORDER BY v.fechaInicio DESC";

$result_vacantes = mysqli_query($conexion, $query_vacantes);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buscar Vacantes - Sistema de Outsourcing</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/buscarVacantes.css">
</head>

<body>
    <?php include 'incluides/sidebar.php'; ?>
    <div class="container">
        <h1>Buscar Vacantes</h1>

        <div class="search-form">
            <form action="" method="GET">
                <input type="text" name="search_keyword" placeholder="Buscar por título o descripción"
                    value="<?php echo htmlspecialchars($search_keyword); ?>">
                <input type="text" name="search_city" placeholder="Ciudad"
                    value="<?php echo htmlspecialchars($search_city); ?>">
                <button type="submit">Buscar</button>
            </form>
        </div>

        <div class="vacantes-grid">
            <?php
            if (mysqli_num_rows($result_vacantes) > 0) {
                while ($vacante = mysqli_fetch_assoc($result_vacantes)) {
                    ?>
                    <div class="vacante-card">
                        <div class="vacante-title"><?php echo htmlspecialchars($vacante['titulo']); ?></div>
                        <div class="vacante-company"><?php echo htmlspecialchars($vacante['empresa_nombre']); ?></div>
                        <div class="vacante-details">
                            <p><strong>Ubicación:</strong>
                                <?php echo htmlspecialchars($vacante['ciudad']) . ', ' . htmlspecialchars($vacante['colonia']); ?>
                            </p>
                            <p><strong>Salario:</strong>
                                <?php echo $vacante['salario'] ? '$' . number_format($vacante['salario'], 2) : 'No especificado'; ?>
                            <p><strong>Tipo de contrato:</strong> <?php echo htmlspecialchars($vacante['tipo_contrato']); ?></p>
                            <p><strong>Fecha de inicio:</strong>
                                <?php echo date('d/m/Y', strtotime($vacante['fechaInicio'])); ?></p>
                            <p><strong>Fecha de cierre:</strong>
                                <?php echo date('d/m/Y', strtotime($vacante['fechaCierre'])); ?></p>
                        </div>
                        
                        <a href="detalles_vacante.php?id=<?php echo $vacante['numero']; ?>" >
                            <button class="cta">
                            <span>Ver detalles</span>
                            <svg width="15px" height="10px" viewBox="0 0 13 10">
                                <path d="M1,5 L11,5"></path>
                                <polyline points="8 1 12 5 8 9"></polyline>
                            </svg>
                            </button>
                        </a>
                        
                    </div>
                    <?php
                }
            } else {
                echo "<p class='no-results'>No se encontraron vacantes que coincidan con tu búsqueda.</p>";
            }
            ?>
        </div>
    </div>
</body>

</html>