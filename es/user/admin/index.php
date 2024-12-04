<?php
session_start();
include_once('../../../../Outsourcing/config.php');

// Verificar si el usuario está autenticado y es un administrador
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'ADM') {
    header("Location: login.php");
    exit();
}

if ($conexion->connect_error) {
    die("Conexión fallida: " . $conexion->connect_error);
}


// Consulta SQL para obtener el número de usuarios registrados por día
$sql = "SELECT DATE(fechaRegistro) as fecha, COUNT(*) as total 
        FROM usuario 
        GROUP BY DATE(fechaRegistro) 
        ORDER BY DATE(fechaRegistro) DESC
        LIMIT 30";

$result = $conexion->query($sql);

$fechas = [];
$totales = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $fechas[] = $row["fecha"];
        $totales[] = $row["total"];
    }
}

// Invertir los arrays para que estén en orden cronológico
$fechas = array_reverse($fechas);
$totales = array_reverse($totales);

// Consulta para obtener la actividad reciente
$activity_query = "
    (SELECT 
        'Registro de Usuario' AS actividad,
        correo AS detalle,
        fechaRegistro AS fecha,
        'Usuario' AS entidad
    FROM usuario)
    UNION
    (SELECT 
        'Publicación de Vacante' AS actividad,
        titulo AS detalle,
        fechaInicio AS fecha,
        'Vacante' AS entidad
    FROM vacante)
    ORDER BY fecha DESC
    LIMIT 7
";

$activity_result = $conexion->query($activity_query);
$recent_activity = [];

if ($activity_result->num_rows > 0) {
    while($row = $activity_result->fetch_assoc()) {
        $recent_activity[] = $row;
    }
}

$conexion->close();

// Convertir los datos a formato JSON para usar en JavaScript
$fechasJSON = json_encode($fechas);
$totalesJSON = json_encode($totales);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard de Administrador</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
    <?php include 'incluides/sidebar.php'; ?>
    <div class="container">
        <h1>Dashboard de Administrador</h1>
        <div class="welcome-message">
            Bienvenido de nuevo!
        </div>
        
        <h2>Estadísticas de Registro de Usuarios</h2>
        <div class="chart-container">
            <canvas id="registroUsuarios"></canvas>
        </div>
        
        <div class="activity-container">
            <div class="activity-header">
                <h2>Actividad Reciente</h2>
                <div class="button-container">
                    <a href="gestionar_vacantes.php" class="button">Ver todas las VACANTES</a>
                    <a href="administrarUsuario.php" class="button">Ver todos los USUARIOS</a>
                </div>
            </div>
            <?php foreach ($recent_activity as $activity): ?>
                <div class="activity-item">
                    <div>
                        <h3><?php echo htmlspecialchars($activity['actividad']); ?></h3>
                        <p><?php echo htmlspecialchars($activity['detalle']); ?></p>
                    </div>
                    <div class="activity-date">
                        <?php 
                        $fecha = new DateTime($activity['fecha']);
                        $ahora = new DateTime();
                        $diff = $ahora->diff($fecha);
                        
                        if ($diff->days == 0) {
                            echo "Hoy";
                        } elseif ($diff->days == 1) {
                            echo "Ayer";
                        } else {
                            echo "Hace " . $diff->days . " días";
                        }
                        ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
        // Datos obtenidos de PHP
        var fechas = <?php echo $fechasJSON; ?>;
        var totales = <?php echo $totalesJSON; ?>;

        // Configuración de la gráfica
        var ctx = document.getElementById('registroUsuarios').getContext('2d');
        var chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: fechas,
                datasets: [{
                    label: 'Usuarios Registrados',
                    data: totales,
                    backgroundColor: 'rgba(0, 123, 255, 0.5)',
                    borderColor: 'rgba(0, 123, 255, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Número de Usuarios'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Fecha de Registro'
                        }
                    }
                },
                plugins: {
                    title: {
                        display: true,
                        text: 'Usuarios Registrados por Día (Últimos 30 días)'
                    }
                }
            }
        });
    </script>
</body>
</html>