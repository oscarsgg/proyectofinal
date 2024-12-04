<?php
include_once('../../Outsourcing/config.php');

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tipo_registro = $_POST['tipo_registro'];

    // Validaciones comunes
    $correo = filter_var($_POST['correo'], FILTER_VALIDATE_EMAIL);
    if (!$correo) {
        $error = "El correo electrónico no es válido.";
    } else {
        // Verificar si el correo ya existe
        $stmt = $conexion->prepare("SELECT COUNT(*) FROM usuario WHERE correo = ?");
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        if ($count > 0) {
            $error = "Este correo electrónico ya está registrado.";
        }
    }

    $contrasenia = $_POST['contrasenia'];
    $confirmar_contrasenia = $_POST['confirmar_contrasenia'];
    if (strlen($contrasenia) < 5) {
        $error = "La contraseña debe tener al menos 5 caracteres.";
    } elseif ($contrasenia !== $confirmar_contrasenia) {
        $error = "Las contraseñas no coinciden.";
    }

    $numTel = preg_replace('/[^0-9]/', '', $_POST['numTel']);
    if (strlen($numTel) != 10) {
        $error = "El número de teléfono debe tener 10 dígitos.";
    }

    if (empty($error)) {
        if ($tipo_registro == 'prospecto') {
            // Validaciones específicas para prospecto
            $nombre = trim($_POST['nombre']);
            $primerApellido = trim($_POST['primerApellido']);
            $segundoApellido = trim($_POST['segundoApellido']);

            if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/', $nombre) || strlen($nombre) < 2) {
                $error = "El nombre debe contener solo letras, espacios y acentos, y tener al menos 2 caracteres.";
            }
            if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ]+$/', $primerApellido) || strlen($primerApellido) < 2) {
                $error = "El primer apellido debe contener solo letras y acentos, y tener al menos 2 caracteres.";
            }
            if (!empty($segundoApellido) && !preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ]+$/', $segundoApellido)) {
                $error = "El segundo apellido debe contener solo letras y acentos.";
            }

            $fechaNacimiento = $_POST['fechaNacimiento'];
            $resumen = mysqli_real_escape_string($conexion, $_POST['resumen']);

            // Verificar edad
            $edad = date_diff(date_create($fechaNacimiento), date_create('today'))->y;
            if ($edad < 18) {
                $error = "Debes ser mayor de 18 años para registrarte.";
            }

            if (empty($error)) {
                // Insertar en la tabla Usuario
                $stmt = $conexion->prepare("INSERT INTO usuario (correo, contrasenia, rol) VALUES (?, SHA1(?), 'PRO')");
                $stmt->bind_param("ss", $correo, $contrasenia);

                if ($stmt->execute()) {
                    $id_usuario = $stmt->insert_id;

                    // Insertar en la tabla Prospecto
                    $stmt = $conexion->prepare("INSERT INTO prospecto (nombre, primerApellido, segundoApellido, resumen, fechaNacimiento, numTel, usuario) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("ssssssi", $nombre, $primerApellido, $segundoApellido, $resumen, $fechaNacimiento, $numTel, $id_usuario);

                    if ($stmt->execute()) {
                        $success = "Registro de prospecto exitoso";
                    } else {
                        $error = "Error al registrar el prospecto: " . $stmt->error;
                    }
                } else {
                    $error = "Error al crear el usuario: " . $stmt->error;
                }
            }
        } elseif ($tipo_registro == 'empresa') {
            // Validaciones específicas para empresa
            $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
            $ciudad = mysqli_real_escape_string($conexion, $_POST['ciudad']);
            $calle = mysqli_real_escape_string($conexion, $_POST['calle']);
            $numeroCalle = intval($_POST['numeroCalle']);
            $colonia = mysqli_real_escape_string($conexion, $_POST['colonia']);
            $codigoPostal = preg_replace('/[^0-9]/', '', $_POST['codigoPostal']);
            $nombreCont = trim($_POST['nombreCont']);
            $primerApellidoCont = trim($_POST['primerApellidoCont']);
            $segundoApellidoCont = trim($_POST['segundoApellidoCont']);

            if (strlen($codigoPostal) != 5) {
                $error = "El código postal debe tener 5 dígitos.";
            }

            if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/', $nombreCont) || strlen($nombreCont) < 2) {
                $error = "El nombre del contacto debe contener solo letras, espacios y acentos, y tener al menos 2 caracteres.";
            }
            if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ]+$/', $primerApellidoCont) || strlen($primerApellidoCont) < 2) {
                $error = "El primer apellido del contacto debe contener solo letras y acentos, y tener al menos 2 caracteres.";
            }
            if (!empty($segundoApellidoCont) && !preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ]+$/', $segundoApellidoCont)) {
                $error = "El segundo apellido del contacto debe contener solo letras y acentos.";
            }

            if (empty($error)) {
                // Insertar en la tabla Usuario
                $stmt = $conexion->prepare("INSERT INTO usuario (correo, contrasenia, rol) VALUES (?, SHA1(?), 'EMP')");
                $stmt->bind_param("ss", $correo, $contrasenia);

                if ($stmt->execute()) {
                    $id_usuario = $stmt->insert_id;

                    // Insertar en la tabla Empresa
                    $stmt = $conexion->prepare("INSERT INTO empresa (nombre, ciudad, calle, numeroCalle, colonia, codigoPostal, nombreCont, primerApellidoCont, segundoApellidoCont, numTel, usuario) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("sssissssssi", $nombre, $ciudad, $calle, $numeroCalle, $colonia, $codigoPostal, $nombreCont, $primerApellidoCont, $segundoApellidoCont, $numTel, $id_usuario);

                    if ($stmt->execute()) {
                        $success = "Registro de empresa exitoso";
                    } else {
                        $error = "Error al registrar la empresa: " . $stmt->error;
                    }
                } else {
                    $error = "Error al crear el usuario: " . $stmt->error;
                }
            }
        }
    }
}

// New PHP function to check email availability
function checkEmailAvailability($email)
{
    global $conexion;
    $stmt = $conexion->prepare("SELECT COUNT(*) FROM usuario WHERE correo = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();
    return $count == 0;
}

// AJAX endpoint for email validation
if (isset($_GET['check_email'])) {
    $email = $_GET['check_email'];
    $isAvailable = checkEmailAvailability($email);
    $isValid = filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    echo json_encode(['available' => $isAvailable, 'valid' => $isValid]);
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - TalentBridge</title>
    <link rel="stylesheet" href="css/registro.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
</head>

<body>
    <div class="container">
        <button class="back-button" onclick="window.location.href='index.php'">← Volver</button>
        <div class="register-section">
            <div class="register-form">
                <h2>Registro en TalentBridge</h2>

                <?php if ($error): ?>
                    <p class="error"><?php echo $error; ?></p>
                <?php endif; ?>

                <?php if ($success): ?>
                    <p class="success"><?php echo $success; ?></p>
                <?php else: ?>
                    <form id="tipoRegistroForm">
                        <div class="form-group">
                            <label for="tipo_registro">Seleccione el tipo de registro:</label>
                            <select id="tipo_registro" name="tipo_registro">
                                <option value="">Seleccione una opción</option>
                                <option value="prospecto">Prospecto</option>
                                <option value="empresa">Empresa</option>
                            </select>
                        </div>
                    </form>

                    <form id="registroProspectoForm" style="display:none;" method="POST">
                        <input type="hidden" name="tipo_registro" value="prospecto">
                        <div class="form-group">
                            <label for="nombre">Nombre(s):</label>
                            <input type="text" id="nombre" name="nombre" required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ ]{2,}"
                                title="Ingrese al menos 2 letras, sin números">
                        </div>


                        <div class="form-group">
                            <label for="primerApellido">Primer Apellido:</label>
                            <input type="text" id="primerApellido" name="primerApellido" required
                                pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ]{2,}"
                                title="Ingrese al menos 2 letras, sin números ni espacios">
                        </div>

                        <div class="form-group">
                            <label for="segundoApellido">Segundo Apellido:</label>
                            <input type="text" id="segundoApellido" name="segundoApellido" pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ]*"
                                title="Solo se permiten letras, sin números ni espacios">
                        </div>

                        <div class="form-group">
                            <label for="fechaNacimiento">Fecha de Nacimiento:</label>
                            <input type="date" id="fechaNacimiento" name="fechaNacimiento" required
                                max="<?php echo date('Y-m-d', strtotime('-18 years')); ?>">
                            <small id="errorMensaje" style="color: red; display: none;">Debes ser mayor a 18 años.</small>
                        </div>

                        <div class="form-group">
                            <label for="resumen">Resumen Profesional:
                                <div class="tooltip">
                                    <div class="icon">i</div>
                                    <div class="tooltiptext">
                                        Añade una descripcion atractiva
                                        sobre ti para las empresas
                                    </div>
                                </div>
                            </label>

                            <textarea id="resumen" name="resumen" rows="4"></textarea>
                        </div>

                        <div class="form-group">
                            <label for="correo">Correo Electrónico:</label>
                            <input type="email" id="correo" name="correo" required
                                pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$">
                        </div>

                        <div class="form-group">
                            <label for="contrasenia">Contraseña:</label>
                            <input type="password" id="contrasenia" name="contrasenia" required minlength="5">
                        </div>

                        <div class="form-group">
                            <label for="confirmar_contrasenia">Confirmar Contraseña:</label>
                            <input type="password" id="confirmar_contrasenia" name="confirmar_contrasenia" required
                                minlength="5">
                        </div>

                        <div class="form-group">
                            <label for="numTel">Número de Teléfono:</label>
                            <input type="tel" id="numTel" name="numTel" required pattern="[0-9]{10}"
                                title="Ingrese un número de 10 dígitos sin espacios ni guiones">
                        </div>

                        <div class="form-group">
                            <input type="submit" value="Registrarse como Prospecto">
                        </div>
                    </form>

                    <form id="registroEmpresaForm" style="display:none;" method="POST">
                        <input type="hidden" name="tipo_registro" value="empresa">
                        <div class="form-group">
                            <label for="nombre">Nombre de la Empresa:</label>
                            <input type="text" id="nombre" name="nombre" required>
                        </div>

                        <div class="form-group">
                            <label for="ciudad">Ciudad:</label>
                            <input type="text" id="ciudad" name="ciudad" required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ ]{2,}"
                            title="Ingrese al menos 2 letras, sin números">
                        </div>

                        <div class="form-group">
                            <label for="calle">Calle:</label>
                            <input type="text" id="calle" name="calle" required>
                        </div>

                        <div class="form-group">
                            <label for="numeroCalle">Número de Calle:</label>
                            <input type="number" id="numeroCalle" name="numeroCalle" required>
                        </div>

                        <div class="form-group">
                            <label for="colonia">Colonia:</label>
                            <input type="text" id="colonia" name="colonia" required>
                        </div>

                        <div class="form-group">
                            <label for="codigoPostal">Código Postal:</label>
                            <input type="text" id="codigoPostal" name="codigoPostal" required pattern="[0-9]{5}"
                                title="El código postal debe tener 5 dígitos">
                        </div>

                        <div class="form-group">
                            <label for="nombreCont">Nombre del Contacto:</label>
                            <input type="text" id="nombreCont" name="nombreCont" required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ ]{2,}"
                            title="Ingrese al menos 2 letras, sin números">
                        </div>

                        <div class="form-group">
                            <label for="primerApellidoCont">Primer Apellido del Contacto:</label>
                            <input type="text" id="primerApellidoCont" name="primerApellidoCont" required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ]{2,}"
                            title="Ingrese al menos 2 letras, sin números ni espacios">
                        </div>

                        <div class="form-group">
                            <label for="segundoApellidoCont">Segundo Apellido del Contacto:</label>
                            <input type="text" id="segundoApellidoCont" name="segundoApellidoCont" pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ]{2,}"
                            title="Ingrese al menos 2 letras, sin números ni espacios">
                        </div>

                        <div class="form-group">
                            <label for="correo">Correo Electrónico:</label>
                            <input type="email" id="correo" name="correo" required
                                pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$">
                        </div>

                        <div class="form-group">
                            <label for="contrasenia">Contraseña:</label>
                            <input type="password" id="contrasenia" name="contrasenia" required minlength="5">
                        </div>

                        <div class="form-group">
                            <label for="confirmar_contrasenia">Confirmar Contraseña:</label>
                            <input type="password" id="confirmar_contrasenia" name="confirmar_contrasenia" required
                                minlength="5">
                        </div>

                        <div class="form-group">
                            <label for="numTel">Número de Teléfono:</label>
                            <input type="tel" id="numTel" name="numTel" required pattern="[0-9]{10}"
                                title="Ingrese un número de 10 dígitos sin espacios ni guiones">
                        </div>

                        <div class="form-group">
                            <input type="submit" value="Registrarse como Empresa">
                        </div>
                    </form>

                <?php endif; ?>
            </div>
        </div>
        <div class="features-section">
            <div class="feature-container">
                <div class="feature active">
                    <h3>Conecta con el Talento</h3>
                    <p>Encuentra los mejores profesionales para tu empresa con nuestra plataforma de reclutamiento
                        avanzada.</p>
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
                    <p>Obtén insights valiosos sobre tus procesos de reclutamiento con nuestras herramientas de
                        análisis.</p>
                </div>
            </div>
            <div class="feature-nav">
                <span class="feature-nav-dot active" data-index="0"></span>
                <span class="feature-nav-dot" data-index="1"></span>
                <span class="feature-nav-dot" data-index="2"></span>
                <span class="feature-nav-dot" data-index="3"></span>
            </div>
            <div class="progress-bar">
                <div class="progress" id="registrationProgress"></div>
            </div>
        </div>
    </div>

    <script>
        const features = document.querySelectorAll('.feature');
        const dots = document.querySelectorAll('.feature-nav-dot');
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
            dot.addEventListener('click', () => showFeature(index));
        });

        document.getElementById('tipo_registro').addEventListener('change', function () {
            var tipoRegistro = this.value;
            document.getElementById('registroProspectoForm').style.display = 'none';
            document.getElementById('registroEmpresaForm').style.display = 'none';
            if (tipoRegistro === 'prospecto') {
                document.getElementById('registroProspectoForm').style.display = 'block';
                updateProgress('registroProspectoForm');
            } else if (tipoRegistro === 'empresa') {
                document.getElementById('registroEmpresaForm').style.display = 'block';
                updateProgress('registroEmpresaForm');
            }
        });

        function updateProgress(formId) {
            const form = document.getElementById(formId);
            const inputs = form.querySelectorAll('input:not([type="submit"]), select, textarea');
            const progressBar = document.getElementById('registrationProgress');

            function calculateProgress() {
                let filledInputs = 0;
                inputs.forEach(inp => {
                    if (inp.value.trim() !== '') {
                        filledInputs++;
                    }
                });
                const progress = (filledInputs / inputs.length) * 100;
                progressBar.style.width = `${progress}%`;
            }

            inputs.forEach(input => {
                input.addEventListener('input', calculateProgress);
            });

            calculateProgress(); // Calcula el progreso inicial

            // New JavaScript for real-time email validation
            function validateEmail(email) {
                const re = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
                return re.test(String(email).toLowerCase());
            }

            function checkEmail(inputElement) {
                const email = inputElement.value;
                const statusElement = inputElement.nextElementSibling;

                if (!validateEmail(email)) {
                    statusElement.textContent = "El correo electrónico no es válido.";
                    statusElement.className = "email-status invalid";
                    return;
                }

                fetch(`registro.php?check_email=${encodeURIComponent(email)}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.valid && data.available) {
                            statusElement.textContent = "El correo electrónico es válido y está disponible.";
                            statusElement.className = "email-status valid";
                        } else if (data.valid && !data.available) {
                            statusElement.textContent = "El correo electrónico ya está registrado.";
                            statusElement.className = "email-status invalid";
                        } else {
                            statusElement.textContent = "El correo electrónico no es válido.";
                            statusElement.className = "email-status invalid";
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        statusElement.textContent = "Error al verificar el correo electrónico.";
                        statusElement.className = "email-status invalid";
                    });
            }

            // Add event listeners to email inputs
            document.querySelectorAll('input[type="email"]').forEach(input => {
                const statusElement = document.createElement('div');
                statusElement.className = 'email-status';
                input.parentNode.insertBefore(statusElement, input.nextSibling);

                input.addEventListener('input', () => checkEmail(input));
            });

            document.getElementById('fechaNacimiento').addEventListener('change', function () {
                const fechaNacimiento = new Date(this.value);
                const hoy = new Date();
                const edadMinima = 18;
                const fechaLimite = new Date(hoy.getFullYear() - edadMinima, hoy.getMonth(), hoy.getDate());

                const mensajeError = document.getElementById('errorMensaje');
                if (fechaNacimiento > fechaLimite) {
                    mensajeError.style.display = 'block';
                } else {
                    mensajeError.style.display = 'none';
                }
            });
        }
    </script>
</body>

</html>