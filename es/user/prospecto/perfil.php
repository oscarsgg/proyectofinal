<?php
session_start();
include_once('../../../../Outsourcing/config.php');


// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Obtener los datos del prospecto
$query = "SELECT p.*, u.correo FROM prospecto AS p 
          INNER JOIN usuario as u ON p.usuario = u.numero 
          WHERE u.numero = ?";
$stmt = $conexion->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$prospecto = $result->fetch_assoc();

if (!$prospecto) {
    die("No se encontró el perfil del prospecto.");
}

// Calcular la edad usando el procedimiento almacenado
$stmt = $conexion->prepare("CALL SP_calcularEdad(?, @edad);");
$stmt->bind_param("i", $prospecto['numero']);
$stmt->execute();
$stmt->close();

$result = $conexion->query("SELECT @edad AS edad;");
$edad = $result->fetch_assoc()['edad'];

// Obtener experiencia laboral
$query_exp = "SELECT e.*, r.descripcion AS responsabilidad, r.numero AS responsabilidad_id
              FROM experiencia as e 
              LEFT JOIN responsabilidades as r ON e.numero = r.experiencia 
              WHERE e.prospecto = ?
              ORDER BY e.fechaInicio DESC, r.numero ASC";
$stmt_exp = $conexion->prepare($query_exp);
$stmt_exp->bind_param("i", $prospecto['numero']);
$stmt_exp->execute();
$result_exp = $stmt_exp->get_result();

$experiencias = [];
while ($row = $result_exp->fetch_assoc()) {
    $exp_id = $row['numero'];
    if (!isset($experiencias[$exp_id])) {
        $experiencias[$exp_id] = $row;
        $experiencias[$exp_id]['responsabilidades'] = [];
    }
    if ($row['responsabilidad']) {
        $experiencias[$exp_id]['responsabilidades'][] = [
            'id' => $row['responsabilidad_id'],
            'descripcion' => $row['responsabilidad']
        ];
    }
}

// Obtener carreras estudiadas
$query_edu = "SELECT c.codigo, c.nombre, ce.anioConcluido 
              FROM carreras_estudiadas ce 
              INNER JOIN carrera c ON ce.carrera = c.codigo 
              WHERE ce.prospecto = ?";
$stmt_edu = $conexion->prepare($query_edu);
$stmt_edu->bind_param("i", $prospecto['numero']);
$stmt_edu->execute();
$result_edu = $stmt_edu->get_result();
$educacion = $result_edu->fetch_all(MYSQLI_ASSOC);

// Procesar la actualización del perfil
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_about_me':
                $telefono = $_POST['phone'];
                $fechaNacimiento = $_POST['birthdate'];
                $resumen = $_POST['summary'];

                // Validar número de teléfono
                if (!preg_match('/^\d{10}$/', $telefono)) {
                    $error = 'El número de teléfono debe tener 10 dígitos.';
                    break;
                }

                // Validar edad (mayor de 18 años)
                $fechaNacimiento = new DateTime($fechaNacimiento);
                $hoy = new DateTime();
                $edad = $hoy->diff($fechaNacimiento)->y;
                if ($edad < 18) {
                    $error = 'Debes ser mayor de 18 años.';
                    break;
                }

                // Actualizar en la base de datos
                $stmt = $conexion->prepare("UPDATE prospecto SET numTel = ?, fechaNacimiento = ?, resumen = ? WHERE numero = ?");

                // Convert the formatted date to a variable
                $formattedFechaNacimiento = $fechaNacimiento->format('Y-m-d');

                // Pass the variable to bind_param
                $stmt->bind_param("sssi", $telefono, $formattedFechaNacimiento, $resumen, $prospecto['numero']);


                if ($stmt->execute()) {
                    $success = 'Perfil actualizado correctamente.';
                    
                } else {
                    $error = 'Error al actualizar el perfil.';
                }
                break;

            case 'update_education':
                // Eliminar educación existente
                $stmt = $conexion->prepare("DELETE FROM carreras_estudiadas WHERE prospecto = ?");
                $stmt->bind_param("i", $prospecto['numero']);
                $stmt->execute();

                // Insertar nueva educación
                $stmt = $conexion->prepare("INSERT INTO carreras_estudiadas (prospecto, carrera, anioConcluido) VALUES (?, ?, ?)");
                foreach ($_POST['carrera'] as $index => $carrera) {
                    $anioConcluido = $_POST['anioConcluido'][$index];

                    // Validar año de conclusión
                    if ($anioConcluido > date('Y') || $anioConcluido < 1900) {
                        $error = 'Año de conclusión inválido.';
                        break 2; // Salir del switch y del foreach
                    }

                    $stmt->bind_param("isi", $prospecto['numero'], $carrera, $anioConcluido);
                    if (!$stmt->execute()) {
                        $error = 'Error al actualizar el historial académico.';
                        break 2; // Salir del switch y del foreach
                    }
                }
                if (!isset($error)) {
                    $success = 'Historial académico actualizado correctamente.';
                }
                break;

            case 'update_experience':
                // Iniciar transacción
                $conexion->begin_transaction();

                try {
                    // Obtener las experiencias existentes del prospecto
                    $stmt = $conexion->prepare("SELECT numero FROM experiencia WHERE prospecto = ?");
                    $stmt->bind_param("i", $prospecto['numero']);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $existing_experiences = $result->fetch_all(MYSQLI_ASSOC);
                    $existing_exp_ids = array_column($existing_experiences, 'numero');

                    // Procesar las experiencias enviadas en el formulario
                    $submitted_exp_ids = [];
                    if (isset($_POST['puesto']) && is_array($_POST['puesto'])) {
                        foreach ($_POST['puesto'] as $index => $puesto) {
                            $empresa = $_POST['empresa'][$index];
                            $fechaInicio = $_POST['fechaInicio'][$index];
                            $fechaFin = $_POST['fechaFin'][$index];
                            $exp_id = isset($_POST['exp_id'][$index]) ? intval($_POST['exp_id'][$index]) : null;

                            // Validar fechas
                            if ($fechaInicio > $fechaFin) {
                                throw new Exception('La fecha de inicio no puede ser posterior a la fecha de fin.');
                            }

                            if ($exp_id && in_array($exp_id, $existing_exp_ids)) {
                                // Actualizar experiencia existente
                                $stmt_update_exp = $conexion->prepare("UPDATE experiencia SET puesto = ?, nombreEmpresa = ?, fechaInicio = ?, fechaFin = ? WHERE numero = ? AND prospecto = ?");
                                $stmt_update_exp->bind_param("ssssii", $puesto, $empresa, $fechaInicio, $fechaFin, $exp_id, $prospecto['numero']);
                                if (!$stmt_update_exp->execute()) {
                                    throw new Exception('Error al actualizar experiencia.');
                                }
                            } else {
                                // Insertar nueva experiencia
                                $stmt_insert_exp = $conexion->prepare("INSERT INTO experiencia (prospecto, puesto, nombreEmpresa, fechaInicio, fechaFin) VALUES (?, ?, ?, ?, ?)");
                                $stmt_insert_exp->bind_param("issss", $prospecto['numero'], $puesto, $empresa, $fechaInicio, $fechaFin);
                                if (!$stmt_insert_exp->execute()) {
                                    throw new Exception('Error al insertar experiencia.');
                                }
                                $exp_id = $conexion->insert_id;
                            }

                            $submitted_exp_ids[] = $exp_id;

                            // Procesar responsabilidades
                            if (isset($_POST['responsabilidades'][$index]) && is_array($_POST['responsabilidades'][$index])) {
                                // Eliminar responsabilidades existentes para esta experiencia
                                $stmt_delete_resp = $conexion->prepare("DELETE FROM responsabilidades WHERE experiencia = ?");
                                $stmt_delete_resp->bind_param("i", $exp_id);
                                $stmt_delete_resp->execute();

                                // Insertar nuevas responsabilidades
                                $stmt_insert_resp = $conexion->prepare("INSERT INTO responsabilidades (experiencia, descripcion) VALUES (?, ?)");
                                foreach ($_POST['responsabilidades'][$index] as $resp) {
                                    $stmt_insert_resp->bind_param("is", $exp_id, $resp);
                                    if (!$stmt_insert_resp->execute()) {
                                        throw new Exception('Error al insertar responsabilidad.');
                                    }
                                }
                            }
                        }
                    }

                    // Eliminar experiencias que ya no existen en el formulario
                    $exp_to_delete = array_diff($existing_exp_ids, $submitted_exp_ids);
                    if (!empty($exp_to_delete)) {
                        $exp_ids_str = implode(',', $exp_to_delete);
                        $conexion->query("DELETE FROM responsabilidades WHERE experiencia IN ($exp_ids_str)");
                        $conexion->query("DELETE FROM experiencia WHERE numero IN ($exp_ids_str) AND prospecto = {$prospecto['numero']}");
                    }

                    $conexion->commit();
                    $success = 'Experiencia laboral actualizada correctamente.';
                } catch (Exception $e) {
                    $conexion->rollback();
                    $error = $e->getMessage();
                }
                break;
        }
    }
}

// Obtener todas las carreras para el buscador
$query_all_carreras = "SELECT codigo, nombre FROM carrera ORDER BY nombre";
$result_all_carreras = $conexion->query($query_all_carreras);
$todas_carreras = $result_all_carreras->fetch_all(MYSQLI_ASSOC);

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil del Prospecto</title>
    <link rel="stylesheet" href="css/prospectProfile.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        /* Estilos adicionales para los modales y el popup de éxito */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 600px;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover,
        .close:focus {
            color: #000;
            text-decoration: none;
            cursor: pointer;
        }

        .popup {
            display: none;
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 20px;
            border-radius: 5px;
            z-index: 1001;
            animation: fadeIn 0.5s, fadeOut 0.5s 2.5s;
        }

        .success-popup {
            background-color: #4CAF50;
            color: white;
        }

        .popup-icon {
            display: inline-block;
            margin-right: 10px;
        }

        .success-svg {
            fill: white;
            width: 24px;
            height: 24px;
        }

        .close-svg {
            fill: white;
            width: 16px;
            height: 16px;
            cursor: pointer;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes fadeOut {
            from {
                opacity: 1;
            }

            to {
                opacity: 0;
            }
        }
    </style>
</head>

<body>
    <?php include 'incluides/sidebar.php'; ?>
    <div class="containeres">
        <main>
            <div class="profile-header">
                <img src="img/default.jpg" alt="Foto de perfil" class="profile-image" id="profile-image">
                <div class="profile-info">
                    <h1><?php echo htmlspecialchars($prospecto['nombre'] . ' ' . $prospecto['primerApellido'] . ' ' . $prospecto['segundoApellido']); ?>
                    </h1>
                    <p><?php echo htmlspecialchars($prospecto['correo']); ?></p>
                    <p><?php echo $edad; ?> años</p>
                </div>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?>         
            </div>
            <?php endif; ?>

            <section class="tasks-section">
                <h2>Información</h2>
                <div class="tabs">
                    <div class="tab active" data-tab="habilidades">Acerca de mí</div>
                    <div class="tab" data-tab="carreras">Historial académico</div>
                    <div class="tab" data-tab="experiencia">Experiencia</div>
                </div>
                <div class="scrollable-content">
                    <div id="habilidades" class="content-section active">
                        <div class="profile-details">
                            <div class="detail-item">
                                <span class="detail-label">Teléfono:</span>
                                <span class="detail-value"><?php echo htmlspecialchars($prospecto['numTel']); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Fecha de Nacimiento:</span>
                                <span
                                    class="detail-value"><?php echo htmlspecialchars($prospecto['fechaNacimiento']); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Resumen:</span>
                                <p class="detail-value"><?php echo nl2br(htmlspecialchars($prospecto['resumen'])); ?>
                                </p>
                            </div>
                        </div>
                        <button id="editAboutMeBtn" class="edit-button">Editar información básica</button>

                        <!-- Formulario para editar Acerca de Mí -->
                        <div id="editAboutMeForm" class="edit-form" style="display: none;">
                            <form method="post" action="">
                                <input type="hidden" name="action" value="update_about_me">
                                <div class="form-group">
                                    <label for="edit-phone">Teléfono:</label>
                                    <input type="tel" id="edit-phone" name="phone"
                                        value="<?php echo htmlspecialchars($prospecto['numTel']); ?>" required
                                        pattern="\d{10}">
                                </div>
                                <div class="form-group">
                                    <label for="edit-birthdate">Fecha de Nacimiento:</label>
                                    <input type="date" id="edit-birthdate" name="birthdate"
                                        value="<?php echo htmlspecialchars($prospecto['fechaNacimiento']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="edit-summary">Resumen:</label>
                                    <textarea id="edit-summary" name="summary" rows="4"
                                        required><?php echo htmlspecialchars($prospecto['resumen']); ?></textarea>
                                </div>
                                <button type="submit" class="btn-submit">Guardar Cambios</button>
                                <button type="button" class="btn-cancel"
                                    onclick="toggleEditForm('editAboutMeForm')">Cancelar</button>
                            </form>
                        </div>
                    </div>

                    <div id="carreras" class="content-section">
                        <div class="teams-list" id="teams-list">
                            <?php if (!empty($educacion)): ?>
                                <?php foreach ($educacion as $edu): ?>
                                    <div class="team-item">
                                        <div class="team-icon"></div>
                                        <span
                                            class="editable"><?php echo htmlspecialchars($edu['nombre']) . ' (' . htmlspecialchars($edu['anioConcluido']) . ')'; ?></span>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p>No hay información académica disponible.</p>
                            <?php endif; ?>
                        </div>
                        <button id="editEducationBtn" class="edit-button">Editar Historial Académico</button>

                        <!-- Formulario para editar Historial Académico -->
                        <div id="editEducationForm" class="edit-form" style="display: none;">
                            <form method="post" action="">
                                <input type="hidden" name="action" value="update_education">
                                <div id="educationList">
                                    <?php if (!empty($educacion)): ?>
                                        <?php foreach ($educacion as $index => $edu): ?>
                                            <div class="education-item">
                                                <select name="carrera[]" required>
                                                    <?php foreach ($todas_carreras as $carrera): ?>
                                                        <option value="<?php echo htmlspecialchars($carrera['codigo']); ?>" <?php echo ($carrera['codigo'] == $edu['codigo']) ? 'selected' : ''; ?>>
                                                            <?php echo htmlspecialchars($carrera['nombre']); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <input type="number" name="anioConcluido[]"
                                                    value="<?php echo htmlspecialchars($edu['anioConcluido']); ?>" required
                                                    min="1900" max="<?php echo date('Y'); ?>">
                                                <button type="button" class="remove-education">Eliminar</button>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                                <button type="button" id="addEducation">Agregar Educación</button>
                                <button type="submit" class="btn-submit">Guardar Cambios</button>
                                <button type="button" class="btn-cancel"
                                    onclick="toggleEditForm('editEducationForm')">Cancelar</button>
                            </form>
                        </div>
                    </div>

                    <div id="experiencia" class="content-section">
                        <div class="experience-list">
                            <?php if (!empty($experiencias)): ?>
                                <?php foreach ($experiencias as $exp): ?>
                                    <div class="experience-item">
                                        <h3><?php echo htmlspecialchars($exp['puesto']); ?></h3>
                                        <p><?php echo htmlspecialchars($exp['nombreEmpresa']); ?></p>
                                        <p><?php echo htmlspecialchars($exp['fechaInicio']) . ' - ' . htmlspecialchars($exp['fechaFin']); ?>
                                        </p>
                                        <?php if (!empty($exp['responsabilidades'])): ?>
                                            <ul>
                                                <?php foreach ($exp['responsabilidades'] as $resp): ?>
                                                    <li><?php echo htmlspecialchars($resp['descripcion']); ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p>No hay experiencia laboral registrada.</p>
                            <?php endif; ?>
                        </div>
                        <button id="editExperienceBtn" class="edit-button">Editar Experiencia</button>


                        <!-- Formulario para editar Experiencia -->
                        <div id="editExperienceForm" class="edit-form" style="display: none;">
                            <form method="post" action="">
                                <input type="hidden" name="action" value="update_experience">
                                <div id="experienceList">
                                    <?php foreach ($experiencias as $index => $exp): ?>
                                        <div class="experience-item">
                                            <input type="hidden" name="exp_id[]"
                                                value="<?php echo htmlspecialchars($exp['numero']); ?>">
                                            <input type="text" name="puesto[]"
                                                value="<?php echo htmlspecialchars($exp['puesto']); ?>" required>
                                            <input type="text" name="empresa[]"
                                                value="<?php echo htmlspecialchars($exp['nombreEmpresa']); ?>" required>
                                            <input type="date" name="fechaInicio[]"
                                                value="<?php echo htmlspecialchars($exp['fechaInicio']); ?>" required>
                                            <input type="date" name="fechaFin[]"
                                                value="<?php echo htmlspecialchars($exp['fechaFin']); ?>" required>
                                            <div class="responsabilidades-list">
                                                <?php foreach ($exp['responsabilidades'] as $resp): ?>
                                                    <div class="responsabilidad-item">
                                                        <input type="text" name="responsabilidades[<?php echo $index; ?>][]"
                                                            value="<?php echo htmlspecialchars($resp['descripcion']); ?>"
                                                            required>
                                                        <button type="button" class="remove-responsabilidad">Eliminar</button>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                            <button type="button" class="add-responsabilidad">Agregar
                                                Responsabilidad</button>
                                            <button type="button" class="remove-experience">Eliminar Experiencia</button>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <button type="button" id="addExperience">Agregar Experiencia</button>
                                <button type="submit" class="btn-submit">Guardar Cambios</button>
                                <button type="button" class="btn-cancel"
                                    onclick="toggleEditForm('editExperienceForm')">Cancelar</button>
                            </form>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <script>
        $(document).ready(function () {
            const tabs = document.querySelectorAll('.tab');
            const contentSections = document.querySelectorAll('.content-section');

            // Tab switching functionality
            tabs.forEach(tab => {
                tab.addEventListener('click', () => {
                    tabs.forEach(t => t.classList.remove('active'));
                    tab.classList.add('active');
                    const tabId = tab.getAttribute('data-tab');
                    contentSections.forEach(section => {
                        section.classList.remove('active');
                        if (section.id === tabId) {
                            section.classList.add('active');
                        }
                    });
                });
            });

            // Toggle edit forms
            window.toggleEditForm = function (formId) {
                $(`#${formId}`).toggle();
            }

            $('#editAboutMeBtn').click(() => toggleEditForm('editAboutMeForm'));
            $('#editEducationBtn').click(() => toggleEditForm('editEducationForm'));
            $('#editExperienceBtn').click(() => toggleEditForm('editExperienceForm'));

            // Add new education
            $('#addEducation').click(function () {
                $('#educationList').append(`
                    <div class="education-item">
                        <select name="carrera[]" required>
                            <?php foreach ($todas_carreras as $carrera): ?>
                                            <option value="<?php echo htmlspecialchars($carrera['codigo']); ?>">
                                                <?php echo htmlspecialchars($carrera['nombre']); ?>
                                            </option>
                            <?php endforeach; ?>
                        </select>
                        <input type="number" name="anioConcluido[]" required min="1900" max="<?php echo date('Y'); ?>">
                        <button type="button" class="remove-education">Eliminar</button>
                    </div>
                `);
            });

            // Remove education
            $(document).on('click', '.remove-education', function () {
                $(this).closest('.education-item').remove();
            });

            // Add new experience
            $('#addExperience').click(function () {
                const index = $('#experienceList .experience-item').length;
                $('#experienceList').append(`
                    <div class="experience-item">
                        <input type="text" name="puesto[]" required placeholder="Puesto">
                        <input type="text" name="empresa[]" required placeholder="Empresa">
                        <input type="date" name="fechaInicio[]" required>
                        <input type="date" name="fechaFin[]" required>
                        <div class="responsabilidades-list">
                            <div class="responsabilidad-item">
                                <input type="text" name="responsabilidades[${index}][]" required placeholder="Responsabilidad">
                                <button type="button" class="remove-responsabilidad">Eliminar</button>
                            </div>
                        </div>
                        <button type="button" class="add-responsabilidad">Agregar Responsabilidad</button>
                        <button type="button" class="remove-experience">Eliminar Experiencia</button>
                    </div>
                `);
            });

            // Remove experience
            $(document).on('click', '.remove-experience', function () {
                $(this).closest('.experience-item').remove();
            });

            // Add new responsabilidad
            $(document).on('click', '.add-responsabilidad', function () {
                const experienceItem = $(this).closest('.experience-item');
                const index = $('#experienceList .experience-item').index(experienceItem);
                experienceItem.find('.responsabilidades-list').append(`
                    <div class="responsabilidad-item">
                        <input type="text" name="responsabilidades[${index}][]" required placeholder="Responsabilidad">
                        <button type="button" class="remove-responsabilidad">Eliminar</button>
                    </div>
                `);
            });

            // Remove responsabilidad
            $(document).on('click', '.remove-responsabilidad', function () {
                $(this).closest('.responsabilidad-item').remove();
            });

            // Buscador de carreras
            $('#carrera-search').on('input', function () {
                const searchTerm = $(this).val().toLowerCase();
                $('select[name="carrera[]"] option').each(function () {
                    const text = $(this).text().toLowerCase();
                    $(this).toggle(text.includes(searchTerm));
                });
            });
        });
    </script>
</body>

</html>