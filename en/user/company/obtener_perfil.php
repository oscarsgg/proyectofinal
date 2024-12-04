<?php
session_start();
include_once($_SERVER['DOCUMENT_ROOT'] . '/Outsourcing/config.php');

// Verificar si el usuario está logueado y es una empresa
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'EMP') {
    header("Location: login.php");
    exit();
}

$prospecto_id = $_GET['prospecto_id'];
$sql = "SELECT * FROM Prospecto WHERE numero = $prospecto_id";
$result = $conexion->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prospect Profile</title>
    <link rel="stylesheet" href="css/perfilProspecto.css">
</head>
<body>

<div class="profile-container">
    <?php if ($result->num_rows > 0): ?>
        <?php $prospecto = $result->fetch_assoc(); ?>
        
        <div class="profile-header">
            <h2><?= $prospecto['nombre'] . ' ' . $prospecto['primerApellido'] . ' ' . $prospecto['segundoApellido']; ?></h2>
            <p class="dob">📅 Date of Birth: <?= $prospecto['fechaNacimiento']; ?></p>
            <p class="phone">📞 Phone: <?= $prospecto['numTel']; ?></p>
            <p class="summary">💼 <?= $prospecto['resumen']; ?></p>
            <?php if (!is_null($prospecto['aniosExperiencia'])): ?>
                <p class="experience">⏏️ Experience: 
                    <?= ($prospecto['aniosExperiencia'] == floor($prospecto['aniosExperiencia'])) 
                        ? number_format($prospecto['aniosExperiencia'], 0) . ' years' 
                        : number_format($prospecto['aniosExperiencia'], 1) . ' years'; ?>
                </p>
            <?php endif; ?>
        </div>

        <!-- Studied Degrees -->
        <?php
        $sql_carreras = "SELECT c.nombre as nombre_carrera, ce.anioConcluido 
                         FROM Carreras_estudiadas ce
                         JOIN Carrera c ON ce.carrera = c.codigo
                         WHERE ce.prospecto = $prospecto_id";
        $result_carreras = $conexion->query($sql_carreras);
        ?>
        <?php if ($result_carreras->num_rows > 0): ?>
            <div class="profile-section">
                <h3>🎓 Studied Degrees</h3>
                <ul class="career-list">
                    <?php while($carrera = $result_carreras->fetch_assoc()): ?>
                        <li><?= $carrera['nombre_carrera'] . " (Year of Completion: " . $carrera['anioConcluido'] . ")"; ?></li>
                    <?php endwhile; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Work Experience -->
        <?php
        $sql_experiencia = "SELECT * FROM Experiencia WHERE prospecto = $prospecto_id ORDER BY fechaInicio DESC";
        $result_experiencia = $conexion->query($sql_experiencia);
        ?>
        <?php if ($result_experiencia->num_rows > 0): ?>
            <div class="profile-section">
                <h3>👔 Work Experience</h3>
                <?php while($exp = $result_experiencia->fetch_assoc()): ?>
                    <div class="experience-item">
                        <h4><?= $exp['puesto'] . " at " . $exp['nombreEmpresa']; ?></h4>
                        <p>📅 <?= $exp['fechaInicio'] . " - " . $exp['fechaFin'] ; ?></p>
                        <p class="exp-description"><?= $exp['descripcion']; ?></p>

                        <!-- Responsibilities -->
                        <?php
                        $sql_responsabilidades = "SELECT * FROM Responsabilidades WHERE experiencia = " . $exp['numero'];
                        $result_responsabilidades = $conexion->query($sql_responsabilidades);
                        ?>
                        <?php if ($result_responsabilidades->num_rows > 0): ?>
                            <h5>Responsibilities:</h5>
                            <ul class="responsibility-list">
                                <?php while($resp = $result_responsabilidades->fetch_assoc()): ?>
                                    <li><?= $resp['descripcion']; ?></li>
                                <?php endwhile; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <p>No prospect information found.</p>
    <?php endif; ?>

</div>

</body>
</html>

<?php $conexion->close(); ?>
