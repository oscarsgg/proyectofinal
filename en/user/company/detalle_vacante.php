<?php
session_start();
include_once($_SERVER['DOCUMENT_ROOT'] . '/Outsourcing/config.php');

// Verificar si el usuario está logueado y es una empresa
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'EMP') {
    header("Location: login.php");
    exit();
}

// Obtener el ID de la vacante de la URL
$vacante_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($vacante_id === 0) {
    header("Location: gestionar_vacantes.php");
    exit();
}

// Obtener los detalles de la vacante
$query_vacante = "SELECT v.*, tc.nombre AS tipo_contrato_nombre, tc.descripcion AS tipo_contrato_descripcion, 
                         e.nombre AS nombre_empresa
                  FROM Vacante v
                  JOIN Tipo_Contrato tc ON v.tipo_contrato = tc.codigo
                  JOIN Empresa e ON v.empresa = e.numero
                  WHERE v.numero = ?";
$stmt_vacante = mysqli_prepare($conexion, $query_vacante);
mysqli_stmt_bind_param($stmt_vacante, "i", $vacante_id);
mysqli_stmt_execute($stmt_vacante);
$resultado_vacante = mysqli_stmt_get_result($stmt_vacante);
$vacante = mysqli_fetch_assoc($resultado_vacante);

if (!$vacante) {
    header("Location: gestionar_vacantes.php");
    exit();
}

// Obtener carreras solicitadas
$query_carreras = "SELECT c.nombre
                   FROM Carreras_solicitadas cs
                   JOIN Carrera c ON cs.carrera = c.codigo
                   WHERE cs.vacante = ?";
$stmt_carreras = mysqli_prepare($conexion, $query_carreras);
mysqli_stmt_bind_param($stmt_carreras, "i", $vacante_id);
mysqli_stmt_execute($stmt_carreras);
$resultado_carreras = mysqli_stmt_get_result($stmt_carreras);
$carreras = mysqli_fetch_all($resultado_carreras, MYSQLI_ASSOC);

// Obtener requerimientos
$query_requerimientos = "SELECT descripcion
                         FROM Requerimiento
                         WHERE vacante = ?";
$stmt_requerimientos = mysqli_prepare($conexion, $query_requerimientos);
mysqli_stmt_bind_param($stmt_requerimientos, "i", $vacante_id);
mysqli_stmt_execute($stmt_requerimientos);
$resultado_requerimientos = mysqli_stmt_get_result($stmt_requerimientos);
$requerimientos = mysqli_fetch_all($resultado_requerimientos, MYSQLI_ASSOC);

// Procesar la actualización de la vacante si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = mysqli_real_escape_string($conexion, $_POST['titulo']);
    $descripcion = mysqli_real_escape_string($conexion, $_POST['descripcion']);
    $salario = is_numeric($_POST['salario']) ? $_POST['salario'] : 0;
    $es_directo = isset($_POST['es_directo']) ? 1 : 0;
    $fechaInicio = mysqli_real_escape_string($conexion, $_POST['fechaInicio']);
    $fechaCierre = mysqli_real_escape_string($conexion, $_POST['fechaCierre']);
    $estado = isset($_POST['estado']) ? 1 : 0;
    $tipo_contrato = mysqli_real_escape_string($conexion, $_POST['tipo_contrato']);

    $query_update = "UPDATE Vacante SET 
                     titulo = ?, descripcion = ?, salario = ?, es_directo = ?, 
                     fechaInicio = ?, fechaCierre = ?, 
                     estado = ?, tipo_contrato = ?
                     WHERE numero = ?";
    $stmt_update = mysqli_prepare($conexion, $query_update);
    mysqli_stmt_bind_param($stmt_update, "ssdisssssi", $titulo, $descripcion, $salario, $es_directo, 
                            $fechaInicio, $fechaCierre, $estado, $tipo_contrato, $vacante_id);
    
    if (mysqli_stmt_execute($stmt_update)) {
        // Actualización exitosa, recargar los datos de la vacante
        header("Location: detalle_vacante.php?id=" . $vacante_id);
        exit();
    } else {
        $error_message = "Error updating vacancy: " . mysqli_error($conexion);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Details - TalentBridge</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="css/detalle_vacante.css">
</head>
<body>
    <div class="dashboard-container">
        <?php include 'incluides/sidebar.php'; ?>
        <main class="main-content">
            <div class="vacante-details">
                <div class="vacante-header">
                    <h1 class="vacante-title"><?php echo htmlspecialchars($vacante['titulo']); ?></h1>
                    <button class="btn-edit" onclick="toggleEditForm()">Edit Job</button>
                </div>
                <div class="vacante-info">
                    <div class="info-item">
                        <div class="info-label">Description:</div>
                        <div class="info-value"><?php echo nl2br(htmlspecialchars($vacante['descripcion'])); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Salary:</div>
                        <div class="info-value">
                            <?php echo $vacante['salario'] ? '$' . number_format($vacante['salario'], 2) : 'Not specified'; ?>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Hiring Type:</div>
                        <div class="info-value"><?php echo $vacante['es_directo'] ? 'Direct' : 'Indirect'; ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Start Date:</div>
                        <div class="info-value"><?php echo date('d/m/Y', strtotime($vacante['fechaInicio'])); ?></div>
                    </div>
                
                    <div class="info-item">
                        <div class="info-label">End Date:</div>
                        <div class="info-value"><?php echo date('d/m/Y', strtotime($vacante['fechaCierre'])); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Status:</div>
                        <div class="info-value"><?php echo $vacante['estado'] ? 'Active' : 'Inactive'; ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Contract Type:</div>
                        <div class="info-value"><?php echo htmlspecialchars($vacante['tipo_contrato_nombre']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Contract Description:</div>
                        <div class="info-value"><?php echo htmlspecialchars($vacante['tipo_contrato_descripcion']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Company:</div>
                        <div class="info-value"><?php echo htmlspecialchars($vacante['nombre_empresa']); ?></div>
                    </div>
                </div>

                <?php if (!empty($carreras)): ?>
                    <h2 class="section-title">Requested Careers</h2>
                    <ul>
                        <?php foreach ($carreras as $carrera): ?>
                            <li class="list-item"><?php echo htmlspecialchars($carrera['nombre']); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <?php if (!empty($requerimientos)): ?>
                    <h2 class="section-title">Additional Requirements</h2>
                    <ul>
                        <?php foreach ($requerimientos as $req): ?>
                            <li class="list-item"><?php echo htmlspecialchars($req['descripcion']); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <div id="editForm" class="edit-form">
                    <h2>Edit Job</h2>
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="titulo">Title:</label>
                            <input type="text" id="titulo" name="titulo" value="<?php echo htmlspecialchars($vacante['titulo']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="descripcion">Description:</label>
                            <textarea id="descripcion" name="descripcion" required><?php echo htmlspecialchars($vacante['descripcion']); ?></textarea>
                        </div>
                        <div class="form-group">
                            <label for="salario">Salary:</label>
                            <input type="number" id="salario" name="salario" value="<?php echo $vacante['salario']; ?>">
                        </div>
                        <div class="form-group">
                            <label for="es_directo">Hiring Type:</label>
                            <select id="es_directo" name="es_directo">
                                <option value="1" <?php echo $vacante['es_directo'] ? 'selected' : ''; ?>>Direct</option>
                                <option value="0" <?php echo !$vacante['es_directo'] ? 'selected' : ''; ?>>Indirect</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="fechaInicio">Start Date:</label>
                            <input type="date" id="fechaInicio" name="fechaInicio" value="<?php echo $vacante['fechaInicio']; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="fechaCierre">End Date:</label>
                            <input type="date" id="fechaCierre" name="fechaCierre" value="<?php echo $vacante['fechaCierre']; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="estado">Status:</label>
                            <select id="estado" name="estado">
                                <option value="1" <?php echo $vacante['estado'] ? 'selected' : ''; ?>>Active</option>
                                <option value="0" <?php echo !$vacante['estado'] ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="tipo_contrato">Contract Type:</label>
                            <input type="text" id="tipo_contrato" name="tipo_contrato" value="<?php echo htmlspecialchars($vacante['tipo_contrato']); ?>" required>
                        </div>
                        <div class="form-actions">
                            <button type="button" class="btn-cancel" onclick="toggleEditForm()">Cancel</button>
                            <button type="submit" class="btn-save">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script>
        function toggleEditForm() {
            var form = document.getElementById('editForm');
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        }
    </script>
</body>
</html>
