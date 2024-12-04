<?php
require_once("incluides/admin.php");

$db = connect();
$admin = new Admin();
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confPassword = $_POST['confPassword'];

    $admin->set_email($email);
    $admin->set_password($password);
    $admin->set_confPassword($confPassword);
    $mensaje = $admin->insertAdmin($db);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="stylesheet" href="css/registrar.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- Enlaza jQuery -->
    <title>Document</title>
</head>

<body>
    <?php include 'incluides/sidebar.php'; ?>
    <main class="main-container">
        <div class="container2">
            <form action="registrar_admin.php" method="post" class="email-form">
                <h1 class="h1-form">Registrar administrador</h1>
                <br>
                <label class="msj-error"  font-weight: bold;"> <?php  if ($_SERVER["REQUEST_METHOD"] == "POST") { echo $mensaje;     } ?></label>
                <br>
                <label class="label-form">Correo electronico</label><br>
                <input type="text" name="email" id="email" class="input-form" placeholder="usuario@gmail.com"><br>

                <label class="label-form">Contraseña</label>
                <div class="password-container">
                    <input type="password" name="password" id="password" placeholder="contraseña" class="input-form">
                    <button type="button" class="toggle-password" onclick="togglePassword('password')">Mostrar</button>
                </div>

                <label class="label-form">Confirmar contraseña</label>
                <div class="password-container">
                    <input type="password" name="confPassword" id="confPassword" placeholder="confirmar contraseña"
                        class="input-form">
                    <button type="button" class="toggle-password"
                        onclick="togglePassword('confPassword')">Mostrar</button>
                </div>
                <button type="submit">Registrate</button>
            </form>
            <footer>
                <p></p>
            </footer>
        </div>
    </main>

    <script>
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            if (input.type === "password") {
                input.type = "text";
                input.nextElementSibling.innerText = "Ocultar"; // Cambia el texto del botón
            } else {
                input.type = "password";
                input.nextElementSibling.innerText = "Mostrar"; // Cambia el texto del botón
            }
        }
    </script>
</body>

</html>