<?php
session_start();
include_once('../../../../Outsourcing/config.php');

// Verificar si el usuario está logueado y es una empresa
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'EMP') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Obtener el número de la empresa asociada al usuario
$query_empresa = "SELECT numero FROM empresa WHERE usuario = $user_id";
$result_empresa = mysqli_query($conexion, $query_empresa);

if (mysqli_num_rows($result_empresa) == 0) {
    die("Error: No se encontró una empresa asociada a este usuario.");
}

$empresa = mysqli_fetch_assoc($result_empresa);
$empresa_id = $empresa['numero'];


// Obtener las vacantes de la empresa
$sql_vacantes = "SELECT numero, titulo FROM vacante WHERE empresa = $empresa_id";
$result_vacantes = $conexion->query($sql_vacantes);

// Función para obtener el nombre del estatus
function obtener_nombre_estatus($codigo)
{
    global $conexion;
    $sql = "SELECT nombre FROM estatus_solicitud WHERE codigo = '$codigo'";
    $result = $conexion->query($sql);
    if ($result->num_rows > 0) {
        return $result->fetch_assoc()['nombre'];
    }
    return $codigo;
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Revisar Solicitudes</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/solicitudes.css">
</head>

<body>
    <?php include 'incluides/sidebar.php'; ?>
    <div class="container">
        <h1>Revisar Solicitudes</h1>

        <div class="filters">
            <select id="vacante-select">
                <option value="">Seleccione una vacante</option>
                <?php while ($row = $result_vacantes->fetch_assoc()): ?>
                    <option value="<?php echo $row['numero']; ?>"><?php echo $row['titulo']; ?></option>
                <?php endwhile; ?>
            </select>

            <select id="estatus-select">
                <option value="">Todos los estatus</option>
                <option value="PEND">Pendiente</option>
                <option value="APRO">Aprobada</option>
                <option value="RECH">Rechazada</option>
                <option value="PFRM">Por firmar</option>
                <option value="CERR">Cerrada</option>
            </select>
        </div>

        <div id="solicitudes-list">
            <div class="welcome-message">
                <p>Bienvenido al sistema de revisión de solicitudes.</p>
                <p>Por favor, seleccione una vacante y un estatus para comenzar.</p>
            </div>
        </div>
    </div>

    <div id="perfil-modal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Perfil del Prospecto</h2>
            <div id="perfil-content"></div>
        </div>
    </div>

    <div id="confirm-modal" class="modal">
        <div class="modal-content">
            <h2>Confirmar cambios</h2>
            <p>¿Está seguro que desea cambiar el estatus de esta solicitud?</p>
            <button id="confirm-yes" class="btn">Sí, cambiar</button>
            <button id="confirm-no" class="btn btn-secondary">Cancelar</button>
        </div>
    </div>

    <div id="contrato-modal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Generar Contrato</h2>
            <form id="contrato-form">
                <input type="hidden" id="contrato-prospecto" name="prospecto">
                <input type="hidden" id="contrato-vacante" name="vacante">
                <div class="form-group">
                    <label for="fecha-inicio">Fecha de inicio:</label>
                    <input type="date" id="fecha-inicio" name="fecha_inicio" required>
                </div>
                <div class="form-group">
                    <label for="fecha-cierre">Fecha de cierre:</label>
                    <input type="date" id="fecha-cierre" name="fecha_cierre" required>
                </div>
                <div class="form-group">
                    <label for="salario">Salario:</label>
                    <input type="number" id="salario" name="salario" step="0.01" required>
                </div>
                <div class="form-group">
                    <label for="horas-diarias">Horas diarias:</label>
                    <input type="number" id="horas-diarias" name="horas_diarias" required>
                </div>
                <div class="form-group">
                    <label for="horario">Horario:</label>
                    <input type="text" id="horario" name="horario" required>
                </div>
                <div class="form-group">
                    <label for="tipo-contrato">Tipo de contrato:</label>
                    <select id="tipo-contrato" name="tipo_contrato" required>
                        <?php
                        $sql_tipos_contrato = "SELECT codigo, nombre FROM tipo_contrato";
                        $result_tipos_contrato = $conexion->query($sql_tipos_contrato);
                        if ($result_tipos_contrato->num_rows > 0) {
                            while ($row = $result_tipos_contrato->fetch_assoc()) {
                                echo "<option value='" . $row['codigo'] . "'>" . $row['nombre'] . "</option>";
                            }
                        } else {
                            echo "<option value=''>No hay tipos de contrato disponibles</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="firma-canvas">Firma de la empresa:</label>
                    <canvas id="firma-canvas" width="400" height="200"></canvas>
                </div>
                <div class="form-actions">
                    <button type="button" id="limpiar-firma" class="btn btn-secondary">Limpiar firma</button>
                    <button type="submit" class="btn btn-primary">Generar Contrato</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Función para cargar solicitudes con filtros opcionales
        function cargarSolicitudes() {
            var vacante_id = $('#vacante-select').val();
            var estatus = $('#estatus-select').val();
            $.ajax({
                url: 'obtener_solicitudes.php',
                method: 'GET',
                data: { vacante_id: vacante_id, estatus: estatus },
                success: function (response) {
                    $('#solicitudes-list').html(response);
                }
            });
        }

        // Cargar las primeras 10 solicitudes al cargar la página
        cargarSolicitudes();

        // Recargar solicitudes cuando se cambian los filtros
        $('#vacante-select, #estatus-select').change(cargarSolicitudes);

        $(document).ready(function () {
            function cargarSolicitudes() {
                var vacante_id = $('#vacante-select').val();
                var estatus = $('#estatus-select').val();
                if (vacante_id) {
                    $.ajax({
                        url: 'obtener_solicitudes.php',
                        method: 'GET',
                        data: { vacante_id: vacante_id, estatus: estatus },
                        success: function (response) {
                            $('#solicitudes-list').html(response);
                        }
                    });
                } else {
                    $('#solicitudes-list').html('<div class="welcome-message"><p>Por favor, seleccione una vacante para ver las solicitudes.</p></div>');
                }
            }

            $('#vacante-select, #estatus-select').change(cargarSolicitudes);

            $(document).on('click', '.ver-perfil', function () {
                var prospecto_id = $(this).data('prospecto');
                $.ajax({
                    url: 'obtener_perfil.php',
                    method: 'GET',
                    data: { prospecto_id: prospecto_id },
                    success: function (response) {
                        $('#perfil-content').html(response);
                        $('#perfil-modal').css('display', 'block');
                    }
                });
            });

            $('.close').click(function () {
                $('#perfil-modal').css('display', 'none');
            });

            $(window).click(function (event) {
                if (event.target == document.getElementById('perfil-modal')) {
                    $('#perfil-modal').css('display', 'none');
                }
            });

            var cambioEstatusData = {};

            $(document).on('click', '.cambiar-estatus', function () {
                cambioEstatusData.prospecto = $(this).data('prospecto');
                cambioEstatusData.vacante = $(this).data('vacante');
                cambioEstatusData.nuevo_estatus = $(this).data('estatus');
                $('#confirm-modal').css('display', 'block');
            });

            $('#confirm-yes').click(function () {
                $.ajax({
                    url: 'cambiar_estatus.php',
                    method: 'POST',
                    data: cambioEstatusData,
                    success: function (response) {
                        $('#confirm-modal').css('display', 'none');
                        cargarSolicitudes();
                        // Redirige a la misma página para actualizar la membresía actual
                        header("Location: " . $_SERVER['PHP_SELF']);
                        exit();

                    }
                });
            });

            $('#confirm-no').click(function () {
                $('#confirm-modal').css('display', 'none');
            });

            //Codigo para generar la firma y el contrato
            var canvas = document.getElementById('firma-canvas');
            var ctx = canvas.getContext('2d');
            var drawing = false;
            var lastX, lastY;

            canvas.addEventListener('mousedown', startDrawing);
            canvas.addEventListener('mousemove', draw);
            canvas.addEventListener('mouseup', stopDrawing);
            canvas.addEventListener('mouseout', stopDrawing);

            function startDrawing(e) {
                drawing = true;
                [lastX, lastY] = [e.offsetX, e.offsetY];
            }


            function draw(e) {
                if (!drawing) return;
                ctx.beginPath();
                ctx.moveTo(lastX, lastY);
                ctx.lineTo(e.offsetX, e.offsetY);
                ctx.stroke();
                [lastX, lastY] = [e.offsetX, e.offsetY];
            }

            function stopDrawing() {
                drawing = false;
            }

            $('#limpiar-firma').click(function () {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
            });

            $(document).on('click', '.generar-contrato', function () {
                var prospecto = $(this).data('prospecto');
                var vacante = $(this).data('vacante');
                $('#contrato-prospecto').val(prospecto);
                $('#contrato-vacante').val(vacante);
                $('#contrato-modal').css('display', 'block');
            });

            $('#contrato-form').submit(function (e) {
                e.preventDefault();
                var formData = new FormData(this);

                // Convertir la firma a imagen y añadirla al FormData
                canvas.toBlob(function (blob) {
                    formData.append('firma', blob, 'firma.png');

                    $.ajax({
                        url: 'generar_contrato.php',
                        method: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        dataType: 'json',
                        success: function (response) {
                            console.log('Respuesta del servidor:', response);
                            if (response.success) {
                                alert(response.message);
                                $('#contrato-modal').css('display', 'none');
                                cargarSolicitudes();
                            } else {
                                alert('Error: ' + response.message);
                            }
                        },
                        error: function (xhr, status, error) {
                            console.error('Error en la solicitud AJAX:', error);
                            console.error('Respuesta del servidor:', xhr.responseText);
                            alert('Error en la solicitud AJAX. Por favor, revise la consola para más detalles.');
                        }
                    });
                });
            });

            // Cerrar el modal cuando se hace clic en la 'x'
            $('.close').click(function () {
                $('#contrato-modal').css('display', 'none');
            });

            // Cerrar el modal cuando se hace clic fuera de él
            $(window).click(function (event) {
                if (event.target == document.getElementById('contrato-modal')) {
                    $('#contrato-modal').css('display', 'none');
                }
            });
        });
    </script>
</body>

</html>