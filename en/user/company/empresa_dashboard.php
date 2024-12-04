<?php
session_start();
include_once($_SERVER['DOCUMENT_ROOT'] . '/Outsourcing/config.php');
require_once 'check_membership.php';

// Verificar si el usuario está logueado y es una empresa
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'EMP') {
    header("Location: login.php");
    exit();
}

// Obtener información de la empresa
$query_empresa = "SELECT * FROM Empresa WHERE usuario = ?";
$stmt_empresa = mysqli_prepare($conexion, $query_empresa);
mysqli_stmt_bind_param($stmt_empresa, "i", $_SESSION['user_id']);
mysqli_stmt_execute($stmt_empresa);
$resultado_empresa = mysqli_stmt_get_result($stmt_empresa);
$empresa = mysqli_fetch_assoc($resultado_empresa);

// Obtener estadísticas de la empresa
$query_stats = "CALL obtenerDatosEmpresa(?)";
$stmt_stats = mysqli_prepare($conexion, $query_stats);
mysqli_stmt_bind_param($stmt_stats, "i", $empresa['numero']);
mysqli_stmt_execute($stmt_stats);
$resultado_stats = mysqli_stmt_get_result($stmt_stats);
$stats = mysqli_fetch_assoc($resultado_stats);

// Cerrar el statement y limpiar los resultados pendientes
mysqli_stmt_close($stmt_stats);
while (mysqli_next_result($conexion)) {
    if ($res = mysqli_store_result($conexion)) {
        mysqli_free_result($res);
    }
}

// Obtener solicitudes recientes
$query_solicitudes = "SELECT s.*, p.nombre, p.primerApellido, p.segundoApellido, v.titulo
                      FROM Solicitud s
                      JOIN Prospecto p ON s.prospecto = p.numero
                      JOIN Vacante v ON s.vacante = v.numero
                      WHERE v.empresa = ?
                      ORDER BY s.vacante DESC
                      LIMIT 5";
$stmt_solicitudes = mysqli_prepare($conexion, $query_solicitudes);
mysqli_stmt_bind_param($stmt_solicitudes, "i", $empresa['numero']);
mysqli_stmt_execute($stmt_solicitudes);
$resultado_solicitudes = mysqli_stmt_get_result($stmt_solicitudes);

// Obtener vacantes recientes
$query_vacantes = "SELECT * FROM Vacante WHERE empresa = ? ORDER BY fechaInicio DESC LIMIT 5";
$stmt_vacantes = mysqli_prepare($conexion, $query_vacantes);
mysqli_stmt_bind_param($stmt_vacantes, "i", $empresa['numero']);
mysqli_stmt_execute($stmt_vacantes);
$resultado_vacantes = mysqli_stmt_get_result($stmt_vacantes);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Dashboard - TalentBridge</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
</head>
<body>
    <div class="dashboard-container">
        <?php include 'incluides/sidebar.php'; ?>
        <!-- <header class="header">
            <h1>Company Dashboard</h1>
        </header> -->
        <main class="main-content">
            <div class="welcome-message">
                Welcome, <?php echo htmlspecialchars($empresa['nombre']); ?>
            </div>
            <div class="dashboard-grid">
                <div class="dashboard-card">
                    <h2>Statistics</h2>
                    <div class="stats-grid">
                        <div class="stat-item">
                            <h3><?php echo $stats['VacantesActivas']; ?></h3>
                            <p>Active Vacancies</p>
                        </div>
                        <div class="stat-item">
                            <h3><?php echo $stats['TotalCandidatos']; ?></h3>
                            <p>Total Candidates</p>
                        </div>
                        <div class="stat-item">
                            <h3><?php echo $stats['AplicacionesPorRevisar']; ?></h3>
                            <p>Applications to Review</p>
                        </div>
                    </div>
                </div>
                <div class="dashboard-card">
                    <h2>Quick Actions</h2>
                    <div>
                        <a href="publicar_vacante.php" class="btn view-all-btn">Post Vacancy</a>
                    </div>
                    <div>
                        <a href="perfil_empresa.php" class="btn view-all-btn">View Company Profile</a>
                    </div>
                </div>
                <div class="dashboard-card">
                    <h2>Recent Applications</h2>
                    <?php while ($solicitud = mysqli_fetch_assoc($resultado_solicitudes)): ?>
                        <div class="list-item">
                            <p><strong><?php echo htmlspecialchars($solicitud['nombre'] . ' ' . $solicitud['primerApellido'] . ' ' . $solicitud['segundoApellido']); ?></strong></p>
                            <p>Vacancy: <?php echo htmlspecialchars($solicitud['titulo']); ?></p>
                        </div>
                    <?php endwhile; ?>
                    <a href="revisar_solicitudes.php" class="btn view-all-btn">View More Applications</a>
                </div>
                <div class="dashboard-card">
                    <h2>Recent Vacancies</h2>
                    <?php while ($vacante = mysqli_fetch_assoc($resultado_vacantes)): ?>
                        <div class="list-item">
                            <a href="detalle_vacante.php?id=<?php echo $vacante['numero']; ?>">
                                <?php echo htmlspecialchars($vacante['titulo']); ?>
                            </a>
                        </div>
                    <?php endwhile; ?>
                    <a href="gestionar_vacantes.php" class="btn view-all-btn">View All Vacancies</a>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
