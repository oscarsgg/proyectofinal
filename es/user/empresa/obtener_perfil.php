<?php
session_start();
include_once('../../../../Outsourcing/config.php');

// Verificar si el usuario está logueado y es una empresa
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'EMP') {
    header("Location: login.php");
    exit();
}

$prospecto_id = $_GET['prospecto_id'];
$sql = "SELECT p.*, u.foto FROM prospecto p JOIN usuario u ON p.usuario = u.numero WHERE p.numero = $prospecto_id";
$result = $conexion->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil del Prospecto</title>
    <link rel="stylesheet" href="css/perfilProspecto.css">
    <style>
        .profile-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        .profile-picture {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 20px;
            border: 3px solid #fff;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .profile-info {
            flex: 1;
        }
        .profile-name {
            font-size: 24px;
            margin-bottom: 10px;
        }
        .profile-details {
            display: flex;
            flex-wrap: wrap;
        }
        .profile-details p {
            margin-right: 20px;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>

<div class="profile-container">
    <?php if ($result->num_rows > 0): ?>
        <?php $prospecto = $result->fetch_assoc(); ?>
        
        <div class="profile-header">
            <img src="<?= $prospecto['foto'] ? '../../../../Outsourcing/img/' . htmlspecialchars($prospecto['foto']) : 'img/default.jpg'; ?>" alt="Foto de perfil" class="profile-picture">
            <div class="profile-info">
                <h2 class="profile-name"><?= $prospecto['nombre'] . ' ' . $prospecto['primerApellido'] . ' ' . $prospecto['segundoApellido']; ?></h2>
                <div class="profile-details">
                    <p class="dob">📅 Fecha de Nacimiento: <?= $prospecto['fechaNacimiento']; ?></p>
                    <p class="phone">📞 Teléfono: <?= $prospecto['numTel']; ?></p>
                    <?php if (!is_null($prospecto['aniosExperiencia'])): ?>
                        <p class="experience">⏏️ Experiencia: 
                            <?= ($prospecto['aniosExperiencia'] == floor($prospecto['aniosExperiencia'])) 
                                ? number_format($prospecto['aniosExperiencia'], 0) . ' años' 
                                : number_format($prospecto['aniosExperiencia'], 1) . ' años'; ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="profile-summary">
            <h3>Resumen Profesional</h3>
            <p class="summary">💼 <?= nl2br(htmlspecialchars($prospecto['resumen'])); ?></p>
        </div>

        <!-- Carreras Estudiadas -->
        <?php
        $sql_carreras = "SELECT c.nombre as nombre_carrera, ce.anioConcluido 
                         FROM carreras_estudiadas ce
                         INNER JOIN carrera c ON ce.carrera = c.codigo
                         WHERE ce.prospecto = $prospecto_id";
        $result_carreras = $conexion->query($sql_carreras);
        ?>
        <?php if ($result_carreras->num_rows > 0): ?>
            <div class="profile-section">
                <h3>🎓 Carreras Estudiadas</h3>
                <ul class="career-list">
                    <?php while($carrera = $result_carreras->fetch_assoc()): ?>
                        <li><?= $carrera['nombre_carrera'] . " (Año de conclusión: " . $carrera['anioConcluido'] . ")"; ?></li>
                    <?php endwhile; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Experiencia Laboral -->
        <?php
        $sql_experiencia = "SELECT * FROM experiencia WHERE prospecto = $prospecto_id ORDER BY fechaInicio DESC";
        $result_experiencia = $conexion->query($sql_experiencia);
        ?>
        <?php if ($result_experiencia->num_rows > 0): ?>
            <div class="profile-section">
                <h3>👔 Experiencia Laboral</h3>
                <?php while($exp = $result_experiencia->fetch_assoc()): ?>
                    <div class="experience-item">
                        <h4><?= $exp['puesto'] . " en " . $exp['nombreEmpresa']; ?></h4>
                        <p>📅 <?= $exp['fechaInicio'] . " - " . $exp['fechaFin']; ?></p>
                        <p class="exp-description"><?= $exp['descripcion']; ?></p>

                        <!-- Responsabilidades -->
                        <?php
                        $sql_responsabilidades = "SELECT * FROM responsabilidades WHERE experiencia = " . $exp['numero'];
                        $result_responsabilidades = $conexion->query($sql_responsabilidades);
                        ?>
                        <?php if ($result_responsabilidades->num_rows > 0): ?>
                            <h5>Responsabilidades:</h5>
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
        <p>No se encontró información del prospecto.</p>
    <?php endif; ?>

</div>

</body>
</html>

<?php $conexion->close(); ?>