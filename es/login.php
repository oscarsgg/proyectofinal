<?php
session_start();

// Si el usuario ya está logueado, redirigir al dashboard correspondiente
if (isset($_SESSION['user_id'])) {
    switch ($_SESSION['user_role']) {
        case 'PRO':
            header("Location: user/prospecto/");
            break;
        case 'EMP':
            header("Location: user/empresa/");
            break;
        case 'ADM':
            header("Location: user/admin/");
            break;
    }
    exit();
}

include_once('../../Outsourcing/config.php');

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = mysqli_real_escape_string($conexion, $_POST['correo']);
    $contrasenia = mysqli_real_escape_string($conexion, $_POST['contrasenia']);

    // Encriptar la contraseña ingresada por el usuario
    $contrasenia_encriptada = sha1($contrasenia);

    $query = "SELECT numero, rol, estado FROM usuario WHERE correo = ? AND contrasenia = ?";
    $stmt = mysqli_prepare($conexion, $query);
    mysqli_stmt_bind_param($stmt, "ss", $correo, $contrasenia_encriptada);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);

    if ($usuario = mysqli_fetch_assoc($resultado)) {
        if ($usuario['estado'] == 0) {
            $error = "La cuenta ha sido deshabilitada. Contactar al soporte del sistema.";
        } else {
            $_SESSION['user_id'] = $usuario['numero'];
            $_SESSION['user_role'] = $usuario['rol'];

            // Establecer una cookie de sesión con tiempo de vida limitado (por ejemplo, 30 minutos)
            session_set_cookie_params(1800); // 30 minutos en segundos
            session_regenerate_id(true); // Regenerar el ID de sesión por seguridad

            switch ($usuario['rol']) {
                case 'PRO':
                    header("Location: user/prospecto/");
                    break;
                case 'EMP':
                    header("Location: user/empresa/");
                    break;
                case 'ADM':
                    header("Location: user/admin/");
                    break;
            }
            exit();
        }
    } else {
        $error = "Correo o contraseña incorrectos";
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - TalentBridge</title>
    <link rel="stylesheet" href="css/login.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
</head>

<body>
    <div class="container">
        <div class="login-section">
            <button class="back-button" onclick="window.location.href='index.php'">← Volver</button>
            <div class="login-form">
                <h2>Iniciar Sesión</h2>
                <?php if ($error): ?>
                    <p class="error"><?php echo $error; ?></p>
                <?php endif; ?>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="form-group">
                        <label for="correo">Correo Electrónico:</label>
                        <input type="email" id="correo" name="correo" required>
                    </div>
                    <div class="form-group">
                        <label for="contrasenia">Contraseña:</label>
                        <input type="password" id="contrasenia" name="contrasenia" required>
                    </div>
                    <div class="form-group">
                        <input type="submit" value="Iniciar Sesión">
                    </div>
                </form>
                <div class="register-link">
                    ¿No tienes una cuenta? <a href="registro.php">Regístrate ahora</a>
                </div>
            </div>
        </div>
        <div class="features-section">
            <div class="feature active">
                <h3>Conecta con el Talento</h3>
                <p>Encuentra los mejores profesionales para tu empresa con nuestra plataforma de reclutamiento avanzada.</p>
            </div>
            <div class="feature">
                <h3>Gestión de Vacantes</h3>
                <p>Publica y administra tus ofertas de trabajo de manera eficiente y sencilla.</p>
            </div>
            <div class="feature">
                <h3>Seguimiento de Candidatos</h3>
                <p>Mantén un registro detallado de los candidatos y su progreso en el proceso de selección.</p>
            </div>
            <div class="feature">
                <h3>Análisis y Reportes</h3>
                <p>Obtén insights valiosos sobre tus procesos de reclutamiento con nuestras herramientas de análisis.</p>
            </div>
            <div class="dots">
                <span class="dot active"></span>
                <span class="dot"></span>
                <span class="dot"></span>
                <span class="dot"></span>
            </div>
        </div>
    </div>

    <script>
        const features = document.querySelectorAll('.feature');
        const dots = document.querySelectorAll('.dot');
        let currentFeature = 0;

        function showFeature(index) {
            features[currentFeature].classList.remove('active');
            dots[currentFeature].classList.remove('active');
            features[index].classList.add('active');
            dots[index].classList.add('active');
            currentFeature = index;
        }

        function nextFeature() {
            let next = (currentFeature + 1) % features.length;
            showFeature(next);
        }

        setInterval(nextFeature, 5000);

        dots.forEach((dot, index) => {
            dot.addEventListener('click', () => {
                showFeature(index);
            });
        });
    </script>
</body>

</html>