<?php
session_start();
include_once($_SERVER['DOCUMENT_ROOT'] . '/Outsourcing/config.php');

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Obtener información del prospecto
$query_prospecto = "SELECT nombre, primerApellido, segundoApellido FROM Prospecto WHERE usuario = $user_id";
$result_prospecto = mysqli_query($conexion, $query_prospecto);
$prospecto = mysqli_fetch_assoc($result_prospecto);

// Obtener las últimas solicitudes del prospecto
$query_solicitudes = "
    SELECT s.*, v.titulo, e.nombre AS nombre_estatus
    FROM Solicitud s
    JOIN Vacante v ON s.vacante = v.numero
    JOIN Estatus_Solicitud e ON s.estatus = e.codigo
    WHERE s.prospecto = (SELECT numero FROM Prospecto WHERE usuario = $user_id)
    ORDER BY s.vacante DESC
    LIMIT 5
";
$result_solicitudes = mysqli_query($conexion, $query_solicitudes);

// Obtener las últimas vacantes
$query_vacantes = "
    SELECT v.numero, v.titulo, e.ciudad, e.colonia
    FROM Vacante v
    JOIN Empresa e ON v.empresa = e.numero
    WHERE v.fechaCierre >= CURDATE()
    ORDER BY v.fechaInicio DESC
    LIMIT 5
";
$result_vacantes = mysqli_query($conexion, $query_vacantes);

// Procesar la búsqueda si se envió el formulario
$search_keyword = isset($_GET['search_keyword']) ? mysqli_real_escape_string($conexion, $_GET['search_keyword']) : '';
$search_city = isset($_GET['search_city']) ? mysqli_real_escape_string($conexion, $_GET['search_city']) : '';

if (!empty($search_keyword) || !empty($search_city)) {
    $query_vacantes = "
        SELECT v.numero, v.titulo, e.ciudad, e.colonia
        FROM Vacante v
        JOIN Empresa e ON v.empresa = e.numero
        WHERE v.fechaCierre >= CURDATE()
    ";
    
    if (!empty($search_keyword)) {
        $query_vacantes .= " AND (v.titulo LIKE '%$search_keyword%' OR v.descripcion LIKE '%$search_keyword%')";
    }
    
    if (!empty($search_city)) {
        $query_vacantes .= " AND e.ciudad LIKE '%$search_city%'";
    }
    
    $query_vacantes .= " ORDER BY v.fechaInicio DESC LIMIT 5";
    $result_vacantes = mysqli_query($conexion, $query_vacantes);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Prospect</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/css.css">
</head>
<body>
    <?php include 'incluides/sidebar.php'; ?>
    <!-- Main content -->
    <div class="container">
        <div class="welcome-message">
            <h1>Welcome, <?php echo $prospecto['nombre'] . ' ' . $prospecto['primerApellido'] . ' ' . $prospecto['segundoApellido']; ?></h1>
        </div>

        <div class="dashboard-grid">
            <!-- Requests Section -->
            <div class="dashboard-section">
                <h2>Latest Updates on Your Applications</h2>
                <?php if (mysqli_num_rows($result_solicitudes) > 0): ?>
                    <?php while ($solicitud = mysqli_fetch_assoc($result_solicitudes)): ?>
                        <div class="item">
                            <h3><?php echo htmlspecialchars($solicitud['titulo']); ?></h3>
                            <p>
                                <?php 
                                    switch ($solicitud['estatus']) {
                                        case 'PEND': echo "Your application is pending review."; break;
                                        case 'APRO': echo "Congratulations! Your application has been approved."; break;
                                        case 'RECH': echo "We’re sorry, your application was rejected."; break;
                                        case 'PFRM': echo "Your contract is pending signature."; break;
                                        case 'CERR': echo "This application has been closed."; break;
                                    }
                                ?>
                            </p>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No recent applications.</p>
                <?php endif; ?>
                <a href="ver_solicitudes.php" class="btn">View All My Applications</a>
            </div>

            <!-- Job Vacancies Section -->
            <div class="dashboard-section">
                <h2>Available Job Vacancies</h2>
                <form action="" method="GET" class="search-form">
                    <input type="text" name="search_keyword" placeholder="Search job vacancies..." value="<?php echo htmlspecialchars($search_keyword); ?>">
                    <input type="text" name="search_city" placeholder="City" value="<?php echo htmlspecialchars($search_city); ?>">
                    <button type="submit">Search</button>
                </form>
                <?php if (mysqli_num_rows($result_vacantes) > 0): ?>
                    <?php while ($vacante = mysqli_fetch_assoc($result_vacantes)): ?>
                        <div class="item">
                            <h3><a href="detalles_vacante.php?id=<?php echo $vacante['numero']; ?>" style="color: #333; text-decoration: none;"><?php echo htmlspecialchars($vacante['titulo']); ?></a></h3>
                            <p><?php echo htmlspecialchars($vacante['ciudad']) . ", " . htmlspecialchars($vacante['colonia']); ?></p>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No available job vacancies match your search.</p>
                <?php endif; ?>
                <a href="buscar_vacantes.php" class="btn">View More Job Vacancies</a>
            </div>
        </div>
    </div>
</body>
</html>