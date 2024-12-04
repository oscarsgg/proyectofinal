<?php
session_start();
include '../../../config.php';
// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Obtener información del prospecto
$query_prospecto = "SELECT nombre, primerApellido, segundoApellido FROM prospecto WHERE usuario = $user_id";
$result_prospecto = mysqli_query($conexion, $query_prospecto);
$prospecto = mysqli_fetch_assoc($result_prospecto);

// Obtener las últimas solicitudes del prospecto
$query_solicitudes = "
    SELECT s.*, v.titulo, e.nombre AS nombre_estatus
    FROM solicitud as s
    INNER JOIN vacante as v ON s.vacante = v.numero
    INNER JOIN estatus_solicitud as e ON s.estatus = e.codigo
    WHERE s.prospecto = (SELECT numero FROM prospecto WHERE usuario = $user_id)
    ORDER BY s.vacante DESC
    LIMIT 5
";
$result_solicitudes = mysqli_query($conexion, $query_solicitudes);

// Obtener las últimas vacantes
$query_vacantes = "
    SELECT v.numero, v.titulo, e.ciudad, e.colonia
    FROM vacante as v
    INNER JOIN empresa as e ON v.empresa = e.numero
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
        FROM vacante as v
        INNER JOIN empresa as e ON v.empresa = e.numero
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
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Prospecto</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/css.css">
</head>
<body>
    <button class="sidebar-toggle" id="sidebarToggle">☰</button>
    <?php include 'incluides/sidebar.php'; ?>
    <!-- Main content -->
    <div class="main-content">
        <div class="container">
            <div class="welcome-message">
                <h1>Bienvenido, <?php echo $prospecto['nombre'] . ' ' . $prospecto['primerApellido'] . ' ' . $prospecto['segundoApellido']; ?></h1>
            </div>

            <div class="dashboard-grid">
                <!-- Solicitudes Section -->
                <div class="dashboard-section">
                    <h2>Últimas novedades de tus solicitudes</h2>
                    <?php if (mysqli_num_rows($result_solicitudes) > 0): ?>
                        <?php while ($solicitud = mysqli_fetch_assoc($result_solicitudes)): ?>
                            <div class="item">
                                <h3><?php echo htmlspecialchars($solicitud['titulo']); ?></h3>
                                <p>
                                    <?php 
                                        switch ($solicitud['estatus']) {
                                            case 'PEND': echo "Su solicitud está pendiente de revisión."; break;
                                            case 'APRO': echo "¡Felicidades! Su solicitud ha sido aprobada."; break;
                                            case 'RECH': echo "Lo sentimos, su solicitud ha sido rechazada."; break;
                                            case 'PFRM': echo "Su contrato está pendiente de firma."; break;
                                            case 'CERR': echo "Esta solicitud ha sido cerrada."; break;
                                        }
                                    ?>
                                </p>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p>No hay solicitudes recientes.</p>
                    <?php endif; ?>
                    <a href="gestionar_solicitudes.php" class="btn">Ver todas mis solicitudes</a>
                </div>

                <!-- Vacantes Section -->
                <div class="dashboard-section">
                    <h2>Vacantes disponibles</h2>
                    <form action="" method="GET" class="search-form">
                        <input type="text" name="search_keyword" placeholder="Buscar vacantes..." value="<?php echo htmlspecialchars($search_keyword); ?>">
                        <input type="text" name="search_city" placeholder="Ciudad" value="<?php echo htmlspecialchars($search_city); ?>">
                        <button type="submit">Buscar</button>
                    </form>
                    <?php if (mysqli_num_rows($result_vacantes) > 0): ?>
                        <?php while ($vacante = mysqli_fetch_assoc($result_vacantes)): ?>
                            <div class="item">
                                <h3><a href="detalles_vacante.php?id=<?php echo $vacante['numero']; ?>" style="color: #333; text-decoration: none;"><?php echo htmlspecialchars($vacante['titulo']); ?></a></h3>
                                <p><?php echo htmlspecialchars($vacante['ciudad']) . ", " . htmlspecialchars($vacante['colonia']); ?></p>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p>No hay vacantes disponibles que coincidan con tu búsqueda.</p>
                    <?php endif; ?>
                    <a href="buscar_vacantes.php" class="btn">Ver más vacantes</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
        });
    </script>
</body>
</html>