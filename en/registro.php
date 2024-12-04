<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/Outsourcing/config.php');

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tipo_registro = $_POST['tipo_registro'];
    
    // Common validations
    $correo = filter_var($_POST['correo'], FILTER_VALIDATE_EMAIL);
    if (!$correo || !preg_match('/\.[a-z]{2,}$/i', $_POST['correo'])) {
        $error = "The email address is not valid.";
    }

    $contrasenia = $_POST['contrasenia'];
    $confirmar_contrasenia = $_POST['confirmar_contrasenia'];
    if (strlen($contrasenia) < 5) {
        $error = "The password must be at least 5 characters long.";
    } elseif ($contrasenia !== $confirmar_contrasenia) {
        $error = "The passwords do not match.";
    }

    $numTel = preg_replace('/[^0-9]/', '', $_POST['numTel']);
    if (strlen($numTel) != 10) {
        $error = "The phone number must have 10 digits.";
    }

    if (empty($error)) {
        if ($tipo_registro == 'prospecto') {
            // Specific validations for prospect
            $nombre = preg_replace('/[^a-zA-Z]/', '', $_POST['nombre']);
            $primerApellido = preg_replace('/[^a-zA-Z]/', '', $_POST['primerApellido']);
            $segundoApellido = preg_replace('/[^a-zA-Z]/', '', $_POST['segundoApellido']);
            
            if (strlen($nombre) < 2 || strlen($primerApellido) < 2) {
                $error = "The name must be at least 2 characters long, and the first surname must be at least 2 letters long.";
            }

            $fechaNacimiento = $_POST['fechaNacimiento'];
            $resumen = mysqli_real_escape_string($conexion, $_POST['resumen']);

            // Verify age
            $edad = date_diff(date_create($fechaNacimiento), date_create('today'))->y;
            if ($edad < 18) {
                $error = "You must be over 18 years old to register.";
            }

            if (empty($error)) {
                // Insert into User table
                $query_usuario = "INSERT INTO Usuario (correo, contrasenia, rol) VALUES (?, ?, 'PRO')";
                $stmt_usuario = mysqli_prepare($conexion, $query_usuario);
                mysqli_stmt_bind_param($stmt_usuario, "ss", $correo, $contrasenia);
                
                if (mysqli_stmt_execute($stmt_usuario)) {
                    $id_usuario = mysqli_insert_id($conexion);
                    
                    // Insert into Prospect table
                    $query_prospecto = "INSERT INTO Prospecto (nombre, primerApellido, segundoApellido, resumen, fechaNacimiento, numTel, usuario) VALUES (?, ?, ?, ?, ?, ?, ?)";
                    $stmt_prospecto = mysqli_prepare($conexion, $query_prospecto);
                    mysqli_stmt_bind_param($stmt_prospecto, "ssssssi", $nombre, $primerApellido, $segundoApellido, $resumen, $fechaNacimiento, $numTel, $id_usuario);
                    
                    if (mysqli_stmt_execute($stmt_prospecto)) {
                        $success = "Prospect registration successful.";
                    } else {
                        $error = "Error registering the prospect.";
                    }
                } else {
                    $error = "Error creating the user.";
                }
            }
        } elseif ($tipo_registro == 'empresa') {
            // Specific validations for company
            $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
            $ciudad = mysqli_real_escape_string($conexion, $_POST['ciudad']);
            $calle = mysqli_real_escape_string($conexion, $_POST['calle']);
            $numeroCalle = intval($_POST['numeroCalle']);
            $colonia = mysqli_real_escape_string($conexion, $_POST['colonia']);
            $codigoPostal = preg_replace('/[^0-9]/', '', $_POST['codigoPostal']);
            $nombreCont = trim($_POST['nombreCont']);
            $primerApellidoCont = preg_replace('/[^a-zA-Z]/', '', $_POST['primerApellidoCont']);
            $segundoApellidoCont = preg_replace('/[^a-zA-Z]/', '', $_POST['segundoApellidoCont']);

            if (strlen($codigoPostal) != 5) {
                $error = "The postal code must have 5 digits.";
            }

            if (strlen($nombreCont) < 2 || strlen($primerApellidoCont) < 2) {
                $error = "The contact's name must be at least 2 characters long, and the first surname must be at least 2 letters long.";
            }

            if (empty($error)) {
                // Insert into User table
                $query_usuario = "INSERT INTO Usuario (correo, contrasenia, rol) VALUES (?, ?, 'EMP')";
                $stmt_usuario = mysqli_prepare($conexion, $query_usuario);
                mysqli_stmt_bind_param($stmt_usuario, "ss", $correo, $contrasenia);
                
                if (mysqli_stmt_execute($stmt_usuario)) {
                    $id_usuario = mysqli_insert_id($conexion);
                    
                    // Insert into Company table
                    $query_empresa = "INSERT INTO Empresa (nombre, ciudad, calle, numeroCalle, colonia, codigoPostal, nombreCont, primerApellidoCont, segundoApellidoCont, numTel, usuario) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmt_empresa = mysqli_prepare($conexion, $query_empresa);
                    mysqli_stmt_bind_param($stmt_empresa, "sssissssssi", $nombre, $ciudad, $calle, $numeroCalle, $colonia, $codigoPostal, $nombreCont, $primerApellidoCont, $segundoApellidoCont, $numTel, $id_usuario);
                    
                    if (mysqli_stmt_execute($stmt_empresa)) {
                        $success = "Company registration successful.";
                    } else {
                        $error = "Error registering the company.";
                    }
                } else {
                    $error = "Error creating the user.";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration - TalentBridge</title>
    <link rel="stylesheet" href="css/registro.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <button class="back-button" onclick="window.location.href='index.php'">← Back</button>
        <div class="register-section">
            <div class="register-form">
                <h2>Registration at TalentBridge</h2>
                
                <?php if ($error): ?>
                    <p class="error"><?php echo $error; ?></p>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <p class="success"><?php echo $success; ?></p>
                <?php else: ?>
                
                <form id="tipoRegistroForm">
                    <div class="form-group">
                        <label for="tipo_registro">Select the type of registration:</label>
                        <select id="tipo_registro" name="tipo_registro">
                            <option value="">Select an option</option>
                            <option value="prospecto">Prospect</option>
                            <option value="empresa">Company</option>
                        </select>
                    </div>
                </form>

                <form id="registroProspectoForm" style="display:none;" method="POST">
                    <input type="hidden" name="tipo_registro" value="prospecto">
                    <div class="form-group">
                        <label for="nombre">Name:</label>
                        <input type="text" id="nombre" name="nombre" required minlength="2">
                    </div>
                    
                    <div class="form-group">
                        <label for="primerApellido">First Surname:</label>
                        <input type="text" id="primerApellido" name="primerApellido" required pattern="[A-Za-z]{2,}" title="Enter at least 2 letters, no spaces">
                    </div>
                    
                    <div class="form-group">
                        <label for="segundoApellido">Second Surname:</label>
                        <input type="text" id="segundoApellido" name="segundoApellido" pattern="[A-Za-z]*" title="Only letters allowed, no spaces">
                    </div>
                    
                    <div class="form-group">
                        <label for="fechaNacimiento">Date of Birth:</label>
                        <input type="date" id="fechaNacimiento" name="fechaNacimiento" required max="<?php echo date('Y-m-d', strtotime('-18 years')); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="resumen">Professional Summary:</label>
                        <textarea id="resumen" name="resumen" rows="4"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="correo">Email Address:</label>
                        <input type="email" id="correo" name="correo" required pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$">
                    </div>
                    
                    <div class="form-group">
                        <label for="contrasenia">Password:</label>
                        <input type="password" id="contrasenia" name="contrasenia" required minlength="5">
                    </div>
                    
                    <div class="form-group">
                        <label for="confirmar_contrasenia">Confirm Password:</label>
                        <input type="password" id="confirmar_contrasenia" name="confirmar_contrasenia" required minlength="5">
                    </div>
                    
                    <div class="form-group">
                        <label for="numTel">Phone Number:</label>
                        <input type="tel" id="numTel" name="numTel" required pattern="[0-9]{10}" title="Enter a 10-digit number without spaces or dashes">
                    </div>
                    
                    <div class="form-group">
                        <input type="submit" value="Register as Prospect">
                    </div>
                </form>

                <form id="registroEmpresaForm" style="display:none;" method="POST">
                    <input type="hidden" name="tipo_registro" value="empresa">
                    <div class="form-group">
                        <label for="nombre">Company Name:</label>
                        <input type="text" id="nombre" name="nombre" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="ciudad">City:</label>
                        <input type="text" id="ciudad" name="ciudad" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="calle">Street:</label>
                        <input type="text" id="calle" name="calle" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="numeroCalle">Street Number:</label>
                        <input type="number" id="numeroCalle" name="numeroCalle" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="colonia">Neighborhood:</label>
                        <input type="text" id="colonia" name="colonia" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="codigoPostal">Postal Code:</label>
                        <input type="text" id="codigoPostal" name="codigoPostal" required pattern="[0-9]{5}" title="The postal code must have 5 digits">
                    </div>
                    
                    <div class="form-group">
                        <label for="nombreCont">Contact Name:</label>
                        <input type="text" id="nombreCont" name="nombreCont" required minlength="2">
                    </div>
                    
                    <div class="form-group">
                        <label for="primerApellidoCont">Contact First Surname:</label>
                        <input type="text" id="primerApellidoCont" name="primerApellidoCont" required pattern="[A-Za-z]{2,}" title="Enter at least 2 letters, no spaces">
                    </div>
                    
                    <div class="form-group">
                        <label for="segundoApellidoCont">Contact Second Surname:</label>
                        <input type="text" id="segundoApellidoCont" name="segundoApellidoCont" pattern="[A-Za-z]*" title="Only letters allowed, no spaces">
                    </div>
                    
                    <div class="form-group">
                        <label for="correo">Email Address:</label>
                        <input type="email" id="correo" name="correo" required pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$">
                    </div>
                    
                    <div class="form-group">
                        <label for="contrasenia">Password:</label>
                        <input type="password" id="contrasenia" name="contrasenia" required minlength="5">
                    </div>
                    
                    <div class="form-group">
                        <label for="confirmar_contrasenia">Confirm Password:</label>
                        <input type="password" id="confirmar_contrasenia" name="confirmar_contrasenia" required minlength="5">
                    </div>
                    
                    <div class="form-group">
                        <label for="numTel">Phone Number:</label>
                        <input type="tel" id="numTel" name="numTel" required pattern="[0-9]{10}" title="Enter a 10-digit number without spaces or dashes">
                    </div>
                    
                    <div class="form-group">
                        <input type="submit" value="Register as Company">
                    </div>
                </form>
                
                <?php endif; ?>
            </div>
        </div>
        <div class="features-section">
            <div class="feature-container">
                <div class="feature active">
                    <h3>Connect with Talent</h3>
                    <p>Find the best professionals for your company with our advanced recruitment platform.</p>
                </div>
                <div class="feature">
                    <h3>Job Management</h3>
                    <p>Post and manage your job offers efficiently and easily.</p>
                </div>
                <div class="feature">
                    <h3>Candidate Tracking</h3>
                    <p>Keep a detailed record of candidates and their progress in the selection process.</p>
                </div>
                <div class="feature">
                    <h3>Analysis and Reports</h3>
                    <p>Gain valuable insights into your recruitment processes with our analysis tools.</p>
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

        document.getElementById('tipo_registro').addEventListener('change', function() {
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
        }
    </script>
</body>
</html>