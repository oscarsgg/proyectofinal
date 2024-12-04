<?php
session_start();
include_once($_SERVER['DOCUMENT_ROOT'] . '/Outsourcing/config.php');

// Verificar si el usuario está logueado y es una empresa
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'EMP') {
    header("Location: login.php");
    exit();
}

// Obtener el número de la empresa
$query_empresa = "SELECT numero FROM Empresa WHERE usuario = ?";
$stmt_empresa = mysqli_prepare($conexion, $query_empresa);
mysqli_stmt_bind_param($stmt_empresa, "i", $_SESSION['user_id']);
mysqli_stmt_execute($stmt_empresa);
$resultado_empresa = mysqli_stmt_get_result($stmt_empresa);
$empresa = mysqli_fetch_assoc($resultado_empresa);
$numero_empresa = $empresa['numero'];

// Obtener tipos de contrato
$query_tipos_contrato = "SELECT codigo, nombre FROM Tipo_Contrato";
$resultado_tipos_contrato = mysqli_query($conexion, $query_tipos_contrato);
$tipos_contrato = mysqli_fetch_all($resultado_tipos_contrato, MYSQLI_ASSOC);


// Obtener carreras
$query_carreras = "SELECT codigo, nombre FROM Carrera";
$resultado_carreras = mysqli_query($conexion, $query_carreras);
$carreras = mysqli_fetch_all($resultado_carreras, MYSQLI_ASSOC);

$mensaje = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recoger y sanitizar los datos del formulario
    $titulo = mysqli_real_escape_string($conexion, $_POST['titulo']);
    $descripcion = mysqli_real_escape_string($conexion, $_POST['descripcion']);
    $salario = isset($_POST['salario']) ? intval($_POST['salario']) : null;
    $es_directo = $_POST['es_directo'] == '1' ? 1 : 0;
    $fechaInicio = mysqli_real_escape_string($conexion, $_POST['fechaInicio']);
    $fechaCierre = mysqli_real_escape_string($conexion, $_POST['fechaCierre']);
    $tipo_contrato = mysqli_real_escape_string($conexion, $_POST['tipo_contrato']);
    $carreras_solicitadas = isset($_POST['carreras']) ? $_POST['carreras'] : [];
    $requerimientos = isset($_POST['requerimientos']) ? $_POST['requerimientos'] : [];

    // Validar fechas
    $fecha_actual = new DateTime();
    $fecha_inicio = new DateTime($fechaInicio);
    $fecha_cierre = new DateTime($fechaCierre);

    if ($fecha_inicio < $fecha_actual) {
        $mensaje = "Error: Start date must be later than current date.";
    } elseif ($fecha_cierre <= $fecha_inicio) {
        $mensaje = "Error: The closing date must be after the start date.";
    } else {
        // Calcular días restantes
        $diferencia = $fecha_actual->diff($fecha_cierre);
        $diasRestantes = $diferencia->days;

        // Iniciar transacción
        mysqli_begin_transaction($conexion);

        try {
            // Insertar la vacante
            $query_insertar = "INSERT INTO Vacante (titulo, descripcion, salario, es_directo, fechaInicio, fechaCierre, diasRestantes, tipo_contrato, empresa) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt_insertar = mysqli_prepare($conexion, $query_insertar);
            mysqli_stmt_bind_param($stmt_insertar, "sssissisi", $titulo, $descripcion, $salario, $es_directo, $fechaInicio, $fechaCierre, $diasRestantes, $tipo_contrato, $numero_empresa);
            mysqli_stmt_execute($stmt_insertar);
            
            $vacante_id = mysqli_insert_id($conexion);


            // Insertar carreras solicitadas
            if (!empty($carreras_solicitadas)) {
                $query_carrera = "INSERT INTO Carreras_solicitadas (vacante, carrera) VALUES (?, ?)";
                $stmt_carrera = mysqli_prepare($conexion, $query_carrera);
                foreach ($carreras_solicitadas as $carrera) {
                    mysqli_stmt_bind_param($stmt_carrera, "is", $vacante_id, $carrera);
                    mysqli_stmt_execute($stmt_carrera);
                }
            }

            // Insertar requerimientos
            if (!empty($requerimientos)) {
                $query_req = "INSERT INTO Requerimiento (descripcion, vacante) VALUES (?, ?)";
                $stmt_req = mysqli_prepare($conexion, $query_req);
                foreach ($requerimientos as $req) {
                    if (!empty($req)) {
                        mysqli_stmt_bind_param($stmt_req, "si", $req, $vacante_id);
                        mysqli_stmt_execute($stmt_req);
                    }
                }
            }

            // Confirmar transacción
            mysqli_commit($conexion);
            $mensaje = "Vacancy published successfully.";
        } catch (Exception $e) {
            // Revertir transacción en caso de error
            mysqli_rollback($conexion);
            $mensaje = "Error posting vacancy: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Job - TalentBridge</title>
    <link rel="stylesheet" href="css/publicar_vacante.css">
    <script src="https://kit.fontawesome.com/your-fontawesome-kit.js" crossorigin="anonymous"></script>
</head>
<body>
    <div class="dashboard-container">
        <?php include 'incluides/sidebar.php'; ?>
        <main class="main-content">
            <header class="main-header">
                <button id="toggleSidebar" class="toggle-sidebar-btn">
                    <i class="fas fa-bars"></i>
                </button>
                <h1>Post New Job</h1>
            </header>
            <?php if ($mensaje): ?>
                <div class="mensaje <?php echo strpos($mensaje, 'success') !== false ? 'exito' : 'error'; ?>">
                    <?php echo $mensaje; ?>
                </div>
            <?php endif; ?>
            <section class="vacante-form">
                <form action="" method="post">
                    <div class="form-group">
                        <label for="titulo">Job Title:</label>
                        <input type="text" id="titulo" name="titulo" required maxlength="30">
                    </div>
                    <div class="form-group">
                        <label for="descripcion">Description:</label>
                        <textarea id="descripcion" name="descripcion" required maxlength="250"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="salario">Salary (optional):</label>
                        <input type="number" id="salario" name="salario">
                    </div>
                    <div class="form-group">
                        <label>Is it direct hiring?</label>
                        <div class="radio-group">
                            <input type="radio" id="es_directo_si" name="es_directo" value="1" required>
                            <label for="es_directo_si">Yes</label>
                            <input type="radio" id="es_directo_no" name="es_directo" value="0" required>
                            <label for="es_directo_no">No</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="fechaInicio">Start Date:</label>
                        <input type="date" id="fechaInicio" name="fechaInicio" required>
                    </div>
                    <div class="form-group">
                        <label for="fechaCierre">End Date:</label>
                        <input type="date" id="fechaCierre" name="fechaCierre" required>
                    </div>
                    <div class="form-group">
                        <label for="tipo_contrato">Contract Type:</label>
                        <select id="tipo_contrato" name="tipo_contrato" required>
                            <?php foreach ($tipos_contrato as $tipo): ?>
                                <option value="<?php echo htmlspecialchars($tipo['codigo']); ?>">
                                    <?php echo htmlspecialchars($tipo['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Requested Careers:</label>
                        <div class="checkbox-container" id="carreras-container">
                            <input type="text" id="carrera-search" placeholder="Search career...">
                            <div class="checkbox-scroll">
                                <?php foreach ($carreras as $carrera): ?>
                                    <div class="checkbox-item">
                                        <input type="checkbox" id="carrera_<?php echo $carrera['codigo']; ?>" name="carreras[]" value="<?php echo $carrera['codigo']; ?>">
                                        <label for="carrera_<?php echo $carrera['codigo']; ?>">
                                            <?php echo htmlspecialchars($carrera['nombre']); ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Requirements:</label>
                        <div id="requerimientos-container">
                            <div class="requerimiento-item">
                                <input type="text" name="requerimientos[]" placeholder="Enter a requirement">
                                <button type="button" class="btn-add-requerimiento">+</button>
                            </div>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn-submit">Post Job</button>
                    </div>
                </form>
            </section>
        </main>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggleBtn = document.getElementById('toggleSidebar');
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.querySelector('.main-content');

            toggleBtn.addEventListener('click', function() {
                sidebar.style.display = sidebar.style.display === 'none' ? 'block' : 'none';
                mainContent.style.marginLeft = sidebar.style.display === 'none' ? '0' : '250px';
            });

            // Functionality to dynamically add requirements
            const requerimientosContainer = document.getElementById('requerimientos-container');
            const addRequerimientoBtn = requerimientosContainer.querySelector('.btn-add-requerimiento');

            addRequerimientoBtn.addEventListener('click', function() {
                const newRequerimiento = document.createElement('div');
                newRequerimiento.className = 'requerimiento-item';
                newRequerimiento.innerHTML = `
                    <input type="text" name="requerimientos[]" placeholder="Enter a requirement">
                    <button type="button" class="btn-remove-requerimiento">-</button>
                `;
                requerimientosContainer.insertBefore(newRequerimiento, this.parentNode);
            });

            requerimientosContainer.addEventListener('click', function(e) {
                if (e.target.classList.contains('btn-remove-requerimiento')) {
                    e.target.parentNode.remove();
                }
            });

            // Search functionality for careers
            const carreraSearch = document.getElementById('carrera-search');
            const carreraItems = document.querySelectorAll('#carreras-container .checkbox-item');

            carreraSearch.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                carreraItems.forEach(item => {
                    const text = item.textContent.toLowerCase();
                    item.style.display = text.includes(searchTerm) ? '' : 'none';
                });
            });

            // Date validation
            const fechaInicio = document.getElementById('fechaInicio');
            const fechaCierre = document.getElementById('fechaCierre');

            fechaInicio.addEventListener('change', validateDates);
            fechaCierre.addEventListener('change', validateDates);

            function validateDates() {
                const start = new Date(fechaInicio.value);
                const end = new Date(fechaCierre.value);
                const today = new Date();
                today.setHours(0, 0, 0, 0);

                if (start < today) {
                    alert('The start date must be later than today.');
                    fechaInicio.value = '';
                }

                if (end <= start) {
                    alert('The end date must be later than the start date.');
                    fechaCierre.value = '';
                }
            }
        });
    </script>
</body>
</html>
