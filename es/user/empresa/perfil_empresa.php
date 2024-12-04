<?php
session_start();
include_once('../../../../Outsourcing/config.php');

// Verificar si el usuario está logueado y es una empresa
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'EMP') {
    header("Location: login.php");
    exit();
}

// Obtener información de la empresa y el usuario
$query = "SELECT e.*, u.correo 
          FROM empresa e 
          INNER JOIN usuario u ON e.usuario = u.numero 
          WHERE e.usuario = ?";
$stmt = mysqli_prepare($conexion, $query);
mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
$empresa = mysqli_fetch_assoc($resultado);

$mensaje = '';

// Procesar el formulario cuando se envía
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recoger y sanitizar los datos del formulario
    $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $ciudad = mysqli_real_escape_string($conexion, $_POST['ciudad']);
    $calle = mysqli_real_escape_string($conexion, $_POST['calle']);
    $numeroCalle = intval($_POST['numeroCalle']);
    $colonia = mysqli_real_escape_string($conexion, $_POST['colonia']);
    $codigoPostal = intval($_POST['codigoPostal']);
    $nombreCont = mysqli_real_escape_string($conexion, $_POST['nombreCont']);
    $primerApellidoCont = mysqli_real_escape_string($conexion, $_POST['primerApellidoCont']);
    $segundoApellidoCont = mysqli_real_escape_string($conexion, $_POST['segundoApellidoCont']);
    $correo = mysqli_real_escape_string($conexion, $_POST['correo']);
    $contrasenia = $_POST['contrasenia'] ? password_hash($_POST['contrasenia'], PASSWORD_DEFAULT) : $empresa['contrasenia'];

    // Actualizar la información de la empresa
    $query_update_empresa = "UPDATE empresa SET 
                             nombre = ?, ciudad = ?, calle = ?, numeroCalle = ?, 
                             colonia = ?, codigoPostal = ?, nombreCont = ?, 
                             primerApellidoCont = ?, segundoApellidoCont = ? 
                             WHERE usuario = ?";
    $stmt_update_empresa = mysqli_prepare($conexion, $query_update_empresa);
    mysqli_stmt_bind_param($stmt_update_empresa, "sssisssssi", 
                           $nombre, $ciudad, $calle, $numeroCalle, 
                           $colonia, $codigoPostal, $nombreCont, 
                           $primerApellidoCont, $segundoApellidoCont, $_SESSION['user_id']);
    
    // Actualizar la información del usuario
    $query_update_usuario = "UPDATE usuario SET correo = ?, contrasenia = ? WHERE numero = ?";
    $stmt_update_usuario = mysqli_prepare($conexion, $query_update_usuario);
    mysqli_stmt_bind_param($stmt_update_usuario, "ssi", $correo, $contrasenia, $_SESSION['user_id']);

    if (mysqli_stmt_execute($stmt_update_empresa) && mysqli_stmt_execute($stmt_update_usuario)) {
        $mensaje = "Perfil actualizado con éxito.";
        // Actualizar los datos en la variable $empresa
        $empresa = array_merge($empresa, $_POST);
        $empresa['correo'] = $correo; // Actualizar el correo en la variable $empresa
    } else {
        $mensaje = "Error al actualizar el perfil.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de Empresa - TalentBridge</title>
    <link rel="stylesheet" href="css/perfil.css">
    <script src="https://kit.fontawesome.com/your-fontawesome-kit.js" crossorigin="anonymous"></script>
</head>
<body>
    <?php include 'incluides/sidebar.php'; ?>
    <div class="dashboard-container">
        <main class="main-content">
            <header class="main-header">
                <h1>Perfil de la Empresa</h1>
            </header>
            <?php if ($mensaje): ?>
                <div class="mensaje <?php echo strpos($mensaje, 'éxito') !== false ? 'exito' : 'error'; ?>">
                    <?php echo $mensaje; ?>
                </div>
            <?php endif; ?>
            <section class="profile-section">
                <div class="profile-header">
                    <div class="profile-avatar">
                        <img src="img/company.jpeg" alt="" width="135px">
                    </div>
                    <h2><?php echo htmlspecialchars($empresa['nombre']); ?></h2>
                    <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($empresa['ciudad']); ?></p>
                </div>
                <div class="profile-body">
                    <div class="profile-info">
                        <h3>Información de la Empresa</h3>
                        <p><strong>Dirección:</strong> <?php echo htmlspecialchars($empresa['calle'] . ' ' . $empresa['numeroCalle'] . ', ' . $empresa['colonia']); ?></p>
                        <p><strong>Código Postal:</strong> <?php echo htmlspecialchars($empresa['codigoPostal']); ?></p>
                        <p><strong>Correo:</strong> <?php echo htmlspecialchars($empresa['correo']); ?></p>
                    </div>
                    <div class="profile-contact">
                        <h3>Información de Contacto</h3>
                        <p><strong>Nombre:</strong> <?php echo htmlspecialchars($empresa['nombreCont'] . ' ' . $empresa['primerApellidoCont'] . ' ' . $empresa['segundoApellidoCont']); ?></p>
                    </div>
                </div>
                <div class="profile-actions">
                    <button id="editProfileBtn" class="btn-edit">Editar Perfil</button>
                </div>
            </section>

            <!-- Formulario oculto para editar el perfil -->
            <div id="editProfileForm" class="edit-profile-form" style="display: none;">
                <h3>Editar Perfil</h3>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="form-group">
                        <label for="nombre">Nombre de la Empresa:</label>
                        <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($empresa['nombre']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="ciudad">Ciudad:</label>
                        <input type="text" id="ciudad" name="ciudad" value="<?php echo htmlspecialchars($empresa['ciudad']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="calle">Calle:</label>
                        <input type="text" id="calle" name="calle" value="<?php echo htmlspecialchars($empresa['calle']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="numeroCalle">Número de Calle:</label>
                        <input type="number" id="numeroCalle" name="numeroCalle" value="<?php echo htmlspecialchars($empresa['numeroCalle']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="colonia">Colonia:</label>
                        <input type="text" id="colonia" name="colonia" value="<?php echo htmlspecialchars($empresa['colonia']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="codigoPostal">Código Postal:</label>
                        <input type="number" id="codigoPostal" name="codigoPostal" value="<?php echo htmlspecialchars($empresa['codigoPostal']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="nombreCont">Nombre del Contacto:</label>
                        <input type="text" id="nombreCont" name="nombreCont" value="<?php echo htmlspecialchars($empresa['nombreCont']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="primerApellidoCont">Primer Apellido del Contacto:</label>
                        <input type="text" id="primerApellidoCont" name="primerApellidoCont" value="<?php echo htmlspecialchars($empresa['primerApellidoCont']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="segundoApellidoCont">Segundo Apellido del Contacto:</label>
                        <input type="text" id="segundoApellidoCont" name="segundoApellidoCont" value="<?php echo htmlspecialchars($empresa['segundoApellidoCont']); ?>">
                    </div>
                    <div class="form-group">
                        <label for="correo">Correo Electrónico:</label>
                        <input type="email" id="correo" name="correo" value="<?php echo htmlspecialchars($empresa['correo']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="contrasenia">Nueva Contraseña (dejar en blanco para no cambiar):</label>
                        <input type="password" id="contrasenia" name="contrasenia">
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn-submit">Actualizar Perfil</button>
                        <button type="button" id="cancelEditBtn" class="btn-cancel">Cancelar</button>
                    </div>
                </form>
            </div>
        </main>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mainContent = document.querySelector('.main-content');
            const editProfileBtn = document.getElementById('editProfileBtn');
            const editProfileForm = document.getElementById('editProfileForm');
            const cancelEditBtn = document.getElementById('cancelEditBtn');

            editProfileBtn.addEventListener('click', function() {
                editProfileForm.style.display = 'block';
            });

            cancelEditBtn.addEventListener('click', function() {
                editProfileForm.style.display = 'none';
            });
        });
    </script>
</body>
</html>