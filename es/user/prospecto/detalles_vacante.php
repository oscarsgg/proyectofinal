<?php
session_start();
include_once('../../../../Outsourcing/config.php');


// Verificar si el usuario está logueado y es un prospecto
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'PRO') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Obtener el prospecto_id si no está en la sesión
if (!isset($_SESSION['prospecto_id'])) {
    $query_prospecto = "SELECT numero FROM prospecto WHERE usuario = ?";
    $stmt_prospecto = mysqli_prepare($conexion, $query_prospecto);
    mysqli_stmt_bind_param($stmt_prospecto, "i", $user_id);
    mysqli_stmt_execute($stmt_prospecto);
    $resultado_prospecto = mysqli_stmt_get_result($stmt_prospecto);
    $prospecto = mysqli_fetch_assoc($resultado_prospecto);
    
    if ($prospecto) {
        $_SESSION['prospecto_id'] = $prospecto['numero'];
    } else {
        // Si no se encuentra el prospecto, redirigir a una página de error o al login
        header("Location: error.php?mensaje=No se encontró el perfil de prospecto");
        exit();
    }
}

$prospecto_id = $_SESSION['prospecto_id'];

// Obtener el ID de la vacante de la URL
$vacante_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($vacante_id === 0) {
    header("Location: buscar_vacantes.php");
    exit();
}

// Obtener los detalles de la vacante
$query_vacante = "SELECT v.*, tc.nombre AS tipo_contrato_nombre, tc.descripcion AS tipo_contrato_descripcion, 
                         e.nombre AS nombre_empresa, e.ciudad, e.colonia
                  FROM vacante AS v
                  INNER JOIN tipo_contrato AS tc ON v.tipo_contrato = tc.codigo
                  INNER JOIN empresa AS e ON v.empresa = e.numero
                  WHERE v.numero = ?";
$stmt_vacante = mysqli_prepare($conexion, $query_vacante);
mysqli_stmt_bind_param($stmt_vacante, "i", $vacante_id);
mysqli_stmt_execute($stmt_vacante);
$resultado_vacante = mysqli_stmt_get_result($stmt_vacante);
$vacante = mysqli_fetch_assoc($resultado_vacante);

if (!$vacante) {
    header("Location: buscar_vacantes.php");
    exit();
}

// Obtener los dias restantes de la vacante
$stmt = $conexion->prepare("CALL SP_calcularDiasVacante(?, @diasRestantes);");
$stmt->bind_param("i", $vacante_id);
$stmt->execute();
$stmt->close();

$result = $conexion->query("SELECT @diasRestantes AS diasRestantes;");
$diasRest = $result->fetch_assoc()['diasRestantes'];

// Obtener carreras solicitadas
$query_carreras = "SELECT c.nombre
                   FROM carreras_solicitadas AS cs
                   INNER JOIN carrera as c ON cs.carrera = c.codigo
                   WHERE cs.vacante = ?";
$stmt_carreras = mysqli_prepare($conexion, $query_carreras);
mysqli_stmt_bind_param($stmt_carreras, "i", $vacante_id);
mysqli_stmt_execute($stmt_carreras);
$resultado_carreras = mysqli_stmt_get_result($stmt_carreras);
$carreras = mysqli_fetch_all($resultado_carreras, MYSQLI_ASSOC);

// Obtener requerimientos
$query_requerimientos = "SELECT descripcion
                         FROM requerimiento
                         WHERE vacante = ?";
$stmt_requerimientos = mysqli_prepare($conexion, $query_requerimientos);
mysqli_stmt_bind_param($stmt_requerimientos, "i", $vacante_id);
mysqli_stmt_execute($stmt_requerimientos);
$resultado_requerimientos = mysqli_stmt_get_result($stmt_requerimientos);
$requerimientos = mysqli_fetch_all($resultado_requerimientos, MYSQLI_ASSOC);

// Verificar si ya existe una solicitud para esta vacante
$query_solicitud_existente = "SELECT * FROM solicitud WHERE prospecto = ? AND vacante = ?";
$stmt_solicitud_existente = mysqli_prepare($conexion, $query_solicitud_existente);
mysqli_stmt_bind_param($stmt_solicitud_existente, "ii", $prospecto_id, $vacante_id);
mysqli_stmt_execute($stmt_solicitud_existente);
$resultado_solicitud_existente = mysqli_stmt_get_result($stmt_solicitud_existente);
$solicitud_existente = mysqli_fetch_assoc($resultado_solicitud_existente);

// Procesar la solicitud si se envió el formulario
$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enviar_solicitud'])) {
    $fecha_actual = date('Y-m-d');
    if ($solicitud_existente) {
        $mensaje = "Ya has enviado una solicitud para esta vacante.";
    } elseif ($fecha_actual < $vacante['fechaInicio'] || $fecha_actual > $vacante['fechaCierre']) {
        $mensaje = "No se puede enviar una solicitud porque la vacante no está activa en este momento.";
    } else {
        $query_insert_solicitud = "INSERT INTO solicitud (prospecto, vacante, estatus, es_cancelada) VALUES (?, ?, 'PEND', 0)";
        $stmt_insert_solicitud = mysqli_prepare($conexion, $query_insert_solicitud);
        mysqli_stmt_bind_param($stmt_insert_solicitud, "ii", $prospecto_id, $vacante_id);
        
        if (mysqli_stmt_execute($stmt_insert_solicitud)) {
            $mensaje = "Tu solicitud ha sido enviada con éxito.";
            $solicitud_existente = true; // Actualizar el estado para reflejar la nueva solicitud
        } else {
            $mensaje = "Error al enviar la solicitud: " . mysqli_error($conexion);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle de Vacante - TalentBridge</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="css/detalleVacante.css">
</head>
<body>
    <?php include 'incluides/sidebar.php'; ?>
    <div class="container">
        <div class="vacante-details">
            <div class="vacante-header">
                <h1 class="vacante-title"><?php echo htmlspecialchars($vacante['titulo']); ?></h1>
                <div class="vacante-company"><?php echo htmlspecialchars($vacante['nombre_empresa']); ?></div>
            </div>
            <div class="vacante-info">
                <div class="info-item">
                    <div class="info-label">Ubicación:</div>
                    <div class="info-value"><?php echo htmlspecialchars($vacante['ciudad'] . ', ' . $vacante['colonia']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Salario:</div>
                    <div class="info-value">
                        <?php echo $vacante['salario'] ? '$' . number_format($vacante['salario'], 2) : 'No especificado'; ?>
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-label">Tipo de Contratación:</div>
                    <div class="info-value"><?php echo $vacante['es_directo'] ? 'Directa' : 'Indirecta'; ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Fecha de Inicio:</div>
                    <div class="info-value"><?php echo date('d/m/Y', strtotime($vacante['fechaInicio'])); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Fecha de Cierre:</div>
                    <div class="info-value"><?php echo date('d/m/Y', strtotime($vacante['fechaCierre'])); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Dias restantes:</div>
                    <div class="info-value"><?php echo $diasRest; ?> dias</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Tipo de Contrato:</div>
                    <div class="info-value"><?php echo htmlspecialchars($vacante['tipo_contrato_nombre']); ?></div>
                </div>
            </div>
            <div class="info-item">
                <div class="info-label">Descripción:</div>
                <div class="info-value"><?php echo nl2br(htmlspecialchars($vacante['descripcion'])); ?></div>
            </div>
            <?php if (!empty($carreras)): ?>
                <h2 class="section-title">Carreras Solicitadas</h2>
                <ul>
                    <?php foreach ($carreras as $carrera): ?>
                        <li class="list-item"><?php echo htmlspecialchars($carrera['nombre']); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <?php if (!empty($requerimientos)): ?>
                <h2 class="section-title">Requerimientos Adicionales</h2>
                <ul>
                    <?php foreach ($requerimientos as $req): ?>
                        <li class="list-item"><?php echo htmlspecialchars($req['descripcion']); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <div class="application-section">
                <?php if ($mensaje): ?>
                    <div class="message <?php echo strpos($mensaje, 'éxito') !== false ? 'success' : 'error'; ?>">
                        <?php echo $mensaje; ?>
                    </div>
                <?php endif; ?>
                <button id="btnAplicar" class="btn-apply" <?php echo $solicitud_existente ? 'disabled' : ''; ?>>
                    <?php echo $solicitud_existente ? 'Solicitud Enviada' : 'Aplicar a esta vacante'; ?>
                </button>
            </div>
        </div>
    </div>

    <div id="confirmModal" class="modal">
        <div class="modal-content">
            <h2>Confirmar Solicitud</h2>
            <p>¿Estás seguro de que deseas enviar una solicitud para esta vacante?</p>
            <div class="modal-buttons">
                <button id="btnConfirmar" class="modal-btn modal-btn-confirm">Confirmar</button>
                <button id="btnCancelar" class="modal-btn modal-btn-cancel">Cancelar</button>
            </div>
        </div>
    </div>

    <script>
        var modal = document.getElementById("confirmModal");
        var btn = document.getElementById("btnAplicar");
        var btnConfirmar = document.getElementById("btnConfirmar");
        var btnCancelar = document.getElementById("btnCancelar");

        btn.onclick = function() {
            modal.style.display = "block";
        }

        btnCancelar.onclick = function() {
            
            modal.style.display = "none";
        }

        btnConfirmar.onclick = function() {
            document.getElementById("formSolicitud").submit();
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>

    <form id="formSolicitud" method="POST" style="display: none;">
        <input type="hidden" name="enviar_solicitud" value="1">
    </form>
</body>
</html>