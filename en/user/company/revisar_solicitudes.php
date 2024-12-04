<?php
session_start();
include_once($_SERVER['DOCUMENT_ROOT'] . '/Outsourcing/config.php');

// Verificar si el usuario está logueado y es una empresa
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'EMP') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Obtener el número de la empresa asociada al usuario
$query_empresa = "SELECT numero FROM Empresa WHERE usuario = $user_id";
$result_empresa = mysqli_query($conexion, $query_empresa);

if (mysqli_num_rows($result_empresa) == 0) {
    die("Error: A company associated with this user was not found.");
}

$empresa = mysqli_fetch_assoc($result_empresa);
$empresa_id = $empresa['numero'];

// Obtener el número de la empresa asociada al usuario
$query_empresa = "SELECT numero FROM Empresa WHERE usuario = $empresa_id";
$result_empresa = mysqli_query($conexion, $query_empresa);

if (mysqli_num_rows($result_empresa) == 0) {
    die("Error: A company associated with this user was not found.");
}

$empresa = mysqli_fetch_assoc($result_empresa);
$empresa_id = $empresa['numero'];

// Obtener el número de la empresa asociada al usuario
$query_empresa = "SELECT numero FROM Empresa WHERE usuario = $user_id";
$result_empresa = mysqli_query($conexion, $query_empresa);

if (mysqli_num_rows($result_empresa) == 0) {
    die("Error: A company associated with this user was not found.");
}

$empresa = mysqli_fetch_assoc($result_empresa);
$empresa_id = $empresa['numero'];

// Obtener las vacantes de la empresa
$sql_vacantes = "SELECT numero, titulo FROM Vacante WHERE empresa = $empresa_id";
$result_vacantes = $conexion->query($sql_vacantes);

// Función para obtener el nombre del estatus
function obtener_nombre_estatus($codigo) {
    global $conexion;
    $sql = "SELECT nombre FROM Estatus_Solicitud WHERE codigo = '$codigo'";
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
    <title>Review Applications</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/review.css">
</head>
<body>
    <?php include 'incluides/sidebar.php'; ?>
    <div class="container">
        <h1>Review Applications</h1>
        
        <div class="filters">
            <select id="vacante-select">
                <option value="">Select a vacancy</option>
                <?php while($row = $result_vacantes->fetch_assoc()): ?>
                    <option value="<?php echo $row['numero']; ?>"><?php echo $row['titulo']; ?></option>
                <?php endwhile; ?>
            </select>
            
            <select id="estatus-select">
                <option value="">All statuses</option>
                <option value="PEND">Pending</option>
                <option value="APRO">Approved</option>
                <option value="RECH">Rejected</option>
                <option value="PFRM">To be signed</option>
                <option value="CERR">Closed</option>
            </select>
        </div>
        
        <div id="solicitudes-list">
            <div class="welcome-message">
                <p>Welcome to the application review system.</p>
                <p>Please select a vacancy and a status to get started.</p>
            </div>
        </div>
    </div>

    <div id="perfil-modal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Prospect Profile</h2>
            <div id="perfil-content"></div>
        </div>
    </div>

    <div id="confirm-modal" class="modal">
        <div class="modal-content">
            <h2>Confirm changes</h2>
            <p>Are you sure you want to change the status of this application?</p>
            <button id="confirm-yes" class="btn">Yes, change</button>
            <button id="confirm-no" class="btn btn-secondary">Cancel</button>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            function cargarSolicitudes() {
                var vacante_id = $('#vacante-select').val();
                var estatus = $('#estatus-select').val();
                if (vacante_id) {
                    $.ajax({
                        url: 'obtener_solicitudes.php',
                        method: 'GET',
                        data: { vacante_id: vacante_id, estatus: estatus },
                        success: function(response) {
                            $('#solicitudes-list').html(response);
                        }
                    });
                } else {
                    $('#solicitudes-list').html('<div class="welcome-message"><p>Please select a vacancy to view the applications.</p></div>');
                }
            }

            $('#vacante-select, #estatus-select').change(cargarSolicitudes);

            $(document).on('click', '.ver-perfil', function() {
                var prospecto_id = $(this).data('prospecto');
                $.ajax({
                    url: 'obtener_perfil.php',
                    method: 'GET',
                    data: { prospecto_id: prospecto_id },
                    success: function(response) {
                        $('#perfil-content').html(response);
                        $('#perfil-modal').css('display', 'block');
                    }
                });
            });

            $('.close').click(function() {
                $('#perfil-modal').css('display', 'none');
            });

            $(window).click(function(event) {
                if (event.target == document.getElementById('perfil-modal')) {
                    $('#perfil-modal').css('display', 'none');
                }
            });

            var cambioEstatusData = {};

            $(document).on('click', '.cambiar-estatus', function() {
                cambioEstatusData.prospecto = $(this).data('prospecto');
                cambioEstatusData.vacante = $(this).data('vacante');
                cambioEstatusData.nuevo_estatus = $(this).data('estatus');
                $('#confirm-modal').css('display', 'block');
            });

            $('#confirm-yes').click(function() {
                $.ajax({
                    url: 'cambiar_estatus.php',
                    method: 'POST',
                    data: cambioEstatusData,
                    success: function(response) {
                        $('#confirm-modal').css('display', 'none');
                        cargarSolicitudes();
                    }
                });
            });

            $('#confirm-no').click(function() {
                $('#confirm-modal').css('display', 'none');
            });
        });
    </script>
</body>
</html>