<?php
session_start();

// Si el usuario ya está logueado, redirigir al dashboard correspondiente
if (isset($_SESSION['user_id'])) {
    switch ($_SESSION['user_role']) {
        case 'PRO':
            header("Location: user/prospect/prospecto_dashboard.php");
            break;
        case 'EMP':
            header("Location: user/company/empresa_dashboard.php");
            break;
        case 'ADM':
            header("Location: user/admin/admin_dashboard.php");
            break;
    }
    exit();
}

include_once($_SERVER['DOCUMENT_ROOT'] . '/Outsourcing/config.php');

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = mysqli_real_escape_string($conexion, $_POST['correo']);
    $contrasenia = mysqli_real_escape_string($conexion, $_POST['contrasenia']);
    
    $query = "SELECT numero, rol FROM Usuario WHERE correo = ? AND contrasenia = ?";
    $stmt = mysqli_prepare($conexion, $query);
    mysqli_stmt_bind_param($stmt, "ss", $correo, $contrasenia);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    
    if ($usuario = mysqli_fetch_assoc($resultado)) {
        $_SESSION['user_id'] = $usuario['numero'];
        $_SESSION['user_role'] = $usuario['rol'];
        
        // Establecer una cookie de sesión con tiempo de vida limitado (por ejemplo, 30 minutos)
        session_set_cookie_params(1800); // 30 minutos en segundos
        session_regenerate_id(true); // Regenerar el ID de sesión por seguridad
        
        switch ($usuario['rol']) {
            case 'PRO':
                header("Location: user/prospect/prospecto_dashboard.php");
                break;
            case 'EMP':
                header("Location: user/company/empresa_dashboard.php");
                break;
            case 'ADM':
                header("Location: user/admin/admin_dashboard.php");
                break;
        }
        exit();
    } else {
        $error = "Incorrect email or password";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - TalentBridge</title>
    <link rel="stylesheet" href="css/login.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="login-section">
            <button class="back-button" onclick="window.location.href='index.php'">← Back</button>
            <div class="login-form">
                <h2>Login</h2>
                <?php if ($error): ?>
                    <p class="error"><?php echo $error; ?></p>
                <?php endif; ?>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="form-group">
                        <label for="correo">Email Address:</label>
                        <input type="email" id="correo" name="correo" required>
                    </div>
                    <div class="form-group">
                        <label for="contrasenia">Password:</label>
                        <input type="password" id="contrasenia" name="contrasenia" required>
                    </div>
                    <div class="form-group">
                        <input type="submit" value="Login">
                    </div>
                </form>
                <div class="register-link">
                    Don't have an account? <a href="registro.php">Sign up now</a>
                </div>
            </div>
        </div>
        <div class="features-section">
            <div class="feature active">
                <h3>Connect with Talent</h3>
                <p>Find the best professionals for your company with our advanced recruitment platform.</p>
            </div>
            <div class="feature">
                <h3>Vacancy Management</h3>
                <p>Post and manage your job listings efficiently and easily.</p>
            </div>
            <div class="feature">
                <h3>Candidate Tracking</h3>
                <p>Maintain a detailed record of candidates and their progress in the selection process.</p>
            </div>
            <div class="feature">
                <h3>Analytics and Reports</h3>
                <p>Gain valuable insights into your recruitment processes with our analysis tools.</p>
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
