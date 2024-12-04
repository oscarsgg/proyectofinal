<?php
session_start();
include_once('../../../../Outsourcing/config.php');

// Verificar si el usuario está autenticado como empresa
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'EMP') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Obtener el número de la empresa asociada al usuario
$query_empresa = "SELECT numero FROM empresa WHERE usuario = ?";
$stmt = $conexion->prepare($query_empresa);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result_empresa = $stmt->get_result();

if ($result_empresa->num_rows == 0) {
    die("Error: No se encontró una empresa asociada a este usuario.");
}

$empresa = $result_empresa->fetch_assoc();
$empresa_id = $empresa['numero'];

// Obtener información de la membresía actual
$query = "SELECT m.*, ps.duracion, ps.precio, ps.precioMensual 
          FROM membresia as m 
          INNER JOIN plan_suscripcion as ps ON m.plan_suscripcion = ps.codigo
          WHERE m.empresa = ? 
          ORDER BY m.fechaVencimiento DESC 
          LIMIT 1";
$stmt = $conexion->prepare($query);
$stmt->bind_param("i", $empresa_id);
$stmt->execute();
$result = $stmt->get_result();
$membresia = $result->fetch_assoc();

// Verificar si se encontró una membresía antes de acceder a sus datos
if ($membresia) {
    // Verificar si la membresía ha expirado
    $membresia_expirada = strtotime($membresia['fechaVencimiento']) < time();
} else {
    // Manejo de error si no hay membresía
    $membresia_expirada = true; // O define otro valor predeterminado según la lógica de tu aplicación
    $membresia = []; // Opcional: para evitar más errores si intentas usar `$membresia` más tarde
}

// Obtener planes de suscripción
$query = "SELECT * FROM plan_suscripcion ORDER BY precio ASC";
$result = $conexion->query($query);
$planes = $result->fetch_all(MYSQLI_ASSOC);

// Procesar el pago y la renovación de la membresía
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['numero_tarjeta'])) {
    $numero_tarjeta = $conexion->real_escape_string($_POST['numero_tarjeta']);
    $fecha_vencimiento = $conexion->real_escape_string($_POST['fecha_vencimiento']);
    $cvv = $conexion->real_escape_string($_POST['cvv']);
    $plan_seleccionado = $conexion->real_escape_string($_POST['plan_id']);

    // Validar los datos de la tarjeta
    if (strlen($numero_tarjeta) !== 16 || !ctype_digit($numero_tarjeta)) {
        $error = "El número de tarjeta debe tener 16 dígitos.";
    } elseif (strlen($cvv) !== 3 || !ctype_digit($cvv)) {
        $error = "El CVV debe tener 3 dígitos.";
    } else {
        // Obtener la duración del plan seleccionado
        $query_plan = "SELECT duracion, nombrePlan FROM plan_suscripcion WHERE codigo = ?";
        $stmt = $conexion->prepare($query_plan);
        $stmt->bind_param("s", $plan_seleccionado);
        $stmt->execute();
        $result_plan = $stmt->get_result();
        $plan = $result_plan->fetch_assoc();
        $duracion_meses = $plan['duracion'];

        // Calcular la nueva fecha de vencimiento
        $fecha_inicio = date('Y-m-d');
        $fecha_vencimiento = date('Y-m-d', strtotime("+$duracion_meses months"));

        // Determinar el estado de la membresía
        $query_count = "SELECT COUNT(*) as total FROM membresia WHERE empresa = ?";
        $stmt = $conexion->prepare($query_count);
        $stmt->bind_param("i", $empresa_id);
        $stmt->execute();
        $result_count = $stmt->get_result();
        $count = $result_count->fetch_assoc();
        $estado_membresia = ($count['total'] == 0) ? 'NV' : 'REN';

        // Insertar la nueva membresía
        $query_insert = "INSERT INTO membresia (fechaInicio, fechaVencimiento, empresa, plan_suscripcion, estado_membresia) 
                         VALUES (?, ?, ?, ?, ?)";
        $stmt = $conexion->prepare($query_insert);
        $stmt->bind_param("ssiss", $fecha_inicio, $fecha_vencimiento, $empresa_id, $plan_seleccionado, $estado_membresia);

        if ($stmt->execute()) {
            $success = "¡Pago procesado con éxito! Su membresía ha sido actualizada.";
            // Actualizar la información de la membresía actual
            $membresia['fechaVencimiento'] = $fecha_vencimiento;
            $membresia['plan_suscripcion'] = $plan_seleccionado;
            $membresia_expirada = false;
        } else {
            $error = "Error al procesar el pago: " . $conexion->error;
        }
    }
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Membresía - Sistema de Outsourcing</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/membresias.css">
</head>

<body>
    <div class="container">
        <?php include 'incluides/sidebar.php'; ?>

        <div class="header-container">
            <h1 class="main-heading">MEMBRESÍA</h1>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert error-alert"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if (isset($success)): ?>
            <div class="alert success-alert"><?php echo $success; ?></div>
        <?php endif; ?>

        <?php if ($membresia_expirada): ?>
            <div class="alert expired-alert">
                <p>Su membresía ha expirado. Por favor, seleccione un nuevo plan para continuar usando el sistema.</p>
            </div>
        <?php else: ?>
            <div class="membership-info">
                <p>Su membresía actual vence el: <span
                        class="membership-date"><?php echo date('d/m/Y', strtotime($membresia['fechaVencimiento'])); ?></span>
                </p>
            </div>
        <?php endif; ?>

        <h2 class="page-subtitle">Planes disponibles</h2>

        <div class="plan-container">
            <?php foreach ($planes as $plan): ?>
                <div class="cards__card card">
                    <p class="card__heading"><?php echo htmlspecialchars($plan['nombrePlan']); ?></p>
                    <p class="card__price">$<?php echo number_format($plan['precioMensual'], 2); ?>/mes</p>
                    <ul class="card_bullets flow" role="list">
                        <li>Duración: <?php echo $plan['duracion']; ?> meses</li>
                        <?php if ($plan['duracion'] > 1): ?>
                            <li>Precio total: $<?php echo number_format($plan['precio'], 2); ?></li>
                        <?php endif; ?>
                    </ul>

                    <?php if ($membresia && isset($membresia['plan_suscripcion']) && $membresia['plan_suscripcion'] === $plan['codigo'] && !$membresia_expirada): ?>
                        <button class="card__cta cta selected" disabled>Plan Actual</button>
                    <?php elseif ($membresia_expirada): ?>
                        <button class="card__cta cta select-plan"
                            data-plan="<?php echo $plan['codigo']; ?>">Seleccionar</button>
                    <?php endif; ?>

                </div>
            <?php endforeach; ?>
        </div>

        <div id="paymentModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h2>Confirmar pago</h2>
                <section class="add-card page">
                    <form class="form" id="paymentForm" method="POST">
                        <input type="hidden" id="plan_id" name="plan_id" value="">
                        <label for="name" class="label">
                            <span class="title">Nombre del propietario de la tarjeta</span>
                            <input class="input-field" type="text" name="input-name" title="Input title"
                                placeholder="Ingrese el nombre completo" required />
                        </label>
                        <label for="numero_tarjeta" class="label">
                            <span class="title">Número de la tarjeta</span>
                            <input id="numero_tarjeta" class="input-field" type="text" name="numero_tarjeta"
                                title="Input title" placeholder="0000 0000 0000 0000" required maxlength="16" />
                        </label>
                        <div class="split">
                            <label for="fecha_vencimiento" class="label">
                                <span class="title">Fecha de expiración</span>
                                <input id="fecha_vencimiento" class="input-field" type="month" name="fecha_vencimiento"
                                    title="Expiry Date" placeholder="01/23" required />
                            </label>
                            <label for="cvv" class="label">
                                <span class="title">CVV</span>
                                <input id="cvv" class="input-field" type="text" name="cvv" title="CVV" placeholder="CVV"
                                    required maxlength="3" />
                            </label>
                        </div>
                        <button type="submit" class="checkout-btn">Confirmar pago</button>
                    </form>
                </section>
            </div>
        </div>

        <div class="benefits-section">
            <h2 class="benefits-title">¿Por qué elegir nuestra Membresía Premium?</h2>
            <p class="benefits-subtitle">Obtén el máximo valor con todas las herramientas y ventajas que necesitas para
                crecer</p>
            <div class="benefit-cards">
                <div class="benefit-card">
                    <i class="benefit-icon fas fa-briefcase"></i>
                    <h3>Publicaciones Ilimitadas</h3>
                    <p>Publica vacantes sin límites y encuentra los mejores candidatos para tu empresa.</p>
                </div>
                <div class="benefit-card">
                    <i class="benefit-icon fas fa-chart-line"></i>
                    <h3>Reportes y Análisis</h3>
                    <p>Accede a estadísticas detalladas de tus vacantes para tomar decisiones informadas.</p>
                </div>
                <div class="benefit-card">
                    <i class="benefit-icon fas fa-headset"></i>
                    <h3>Soporte Prioritario</h3>
                    <p>Obtén ayuda inmediata con nuestro soporte exclusivo para miembros premium.</p>
                </div>
                <div class="benefit-card">
                    <i class="benefit-icon fas fa-user-check"></i>
                    <h3>Perfil de Empresa Destacado</h3>
                    <p>Mejora la visibilidad de tu empresa y atrae el talento que necesitas.</p>
                </div>
                <div class="benefit-card">
                    <i class="benefit-icon fas fa-dollar-sign"></i>
                    <h3>Descuentos en Renovación</h3>
                    <p>Recibe descuentos exclusivos para renovar y mantener los beneficios activos.</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Elementos de la interfaz
        const modal = document.getElementById("paymentModal");
        const closeButton = document.getElementsByClassName("close")[0];
        const planIdInput = document.getElementById("plan_id");

        // Asignar el plan seleccionado al campo oculto y abrir el modal
        const selectPlanButtons = document.querySelectorAll(".select-plan");
        selectPlanButtons.forEach(button => {
            button.addEventListener("click", function () {
                const planId = this.getAttribute("data-plan");
                planIdInput.value = planId;
                modal.style.display = "block";
            });
        });

        // Cerrar el modal al hacer clic en la "x"
        closeButton.onclick = function () {
            modal.style.display = "none";
        };

        // Cerrar el modal si se hace clic fuera de él
        window.onclick = function (event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        };

        // Validación del formulario
        document.getElementById("paymentForm").addEventListener("submit", function (e) {
            const numeroTarjeta = document.getElementById("numero_tarjeta").value;
            const fechaVencimiento = document.getElementById("fecha_vencimiento").value;
            const cvv = document.getElementById("cvv").value;

            // Validar el número de tarjeta
            if (numeroTarjeta.length !== 16 || !/^\d+$/.test(numeroTarjeta)) {
                alert("El número de tarjeta debe tener 16 dígitos.");
                e.preventDefault();
                return;
            }

            // Validar la fecha de vencimiento
            const hoy = new Date();
            const [mes, año] = fechaVencimiento.split('/');

            // Verificar que mes y año sean números válidos
            if (!mes || !año || isNaN(mes) || isNaN(año) || mes < 1 || mes > 12) {
                alert("La fecha de vencimiento no es válida.");
                e.preventDefault();
                return;
            }

            // Crear una fecha para el último día del mes de la fecha de vencimiento
            const ultimoDiaMes = new Date(`20${año}`, mes, 0); // Año asumido como 20XX
            if (ultimoDiaMes < hoy) {
                alert("La fecha de vencimiento de la tarjeta no es válida.");
                e.preventDefault();
                return;
            }

            // Validar el CVV
            if (cvv.length !== 3 || !/^\d+$/.test(cvv)) {
                alert("El CVV debe tener 3 dígitos.");
                e.preventDefault();
                return;
            }
        });
    </script>
</body>

</html>