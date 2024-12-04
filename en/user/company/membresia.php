<?php
// membresia.php

session_start();
include_once($_SERVER['DOCUMENT_ROOT'] . '/Outsourcing/config.php');

// Verificar si el usuario está autenticado como empresa
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

// Obtener información de la membresía actual
$query = "SELECT * FROM Membresia WHERE empresa = $empresa_id ORDER BY fechaVencimiento DESC LIMIT 1";
$result = mysqli_query($conexion, $query);
$membresia = mysqli_fetch_assoc($result);

// Verificar si la membresía ha expirado
$membresia_expirada = strtotime($membresia['fechaVencimiento']) < time();

// Obtener planes de suscripción
$query = "SELECT * FROM Plan_suscripcion ORDER BY precio ASC";
$result = mysqli_query($conexion, $query);
$planes = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Procesar el pago si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['plan_id'])) {
    $plan_id = mysqli_real_escape_string($conexion, $_POST['plan_id']);
    $numero_tarjeta = mysqli_real_escape_string($conexion, $_POST['numero_tarjeta']);
    $fecha_vencimiento = mysqli_real_escape_string($conexion, $_POST['fecha_vencimiento']);
    $cvv = mysqli_real_escape_string($conexion, $_POST['cvv']);

    // Validar los datos de la tarjeta
    if (strlen($numero_tarjeta) !== 16 || !ctype_digit($numero_tarjeta)) {
        $error = "The card number must be 16 digits long.";
    } elseif (strtotime($fecha_vencimiento) <= time()) {
        $error = "The card expiration date is not valid.";
    } elseif (strlen($cvv) !== 3 || !ctype_digit($cvv)) {
        $error = "The CVV must be 3 digits.";
    } else {
        // Procesar el pago (aquí deberías integrar con un sistema de pago real)
        // Por ahora, asumiremos que el pago fue exitoso

        // Actualizar la membresía
        $query = "SELECT duracion FROM Plan_suscripcion WHERE codigo = '$plan_id'";
        $result = mysqli_query($conexion, $query);
        $duracion = mysqli_fetch_assoc($result)['duracion'];

        $nueva_fecha_vencimiento = date('Y-m-d', strtotime("+$duracion months"));
        
        $query = "UPDATE Membresia SET fechaVencimiento = '$nueva_fecha_vencimiento', estatus = 1, plan_suscripcion = '$plan_id' WHERE empresa = $empresa_id";
        mysqli_query($conexion, $query);

        // Registrar la renovación
        $query = "INSERT INTO Renovacion (fechaRenovacion, membresia) VALUES (CURDATE(), {$membresia['numero']})";
        mysqli_query($conexion, $query);

        $success = "¡Pago procesado con éxito! Su membresía ha sido actualizada.";
        
        // Actualizar la información de la membresía
        $query = "SELECT * FROM Membresia WHERE empresa = $empresa_id ORDER BY fechaVencimiento DESC LIMIT 1";
        $result = mysqli_query($conexion, $query);
        $membresia = mysqli_fetch_assoc($result);
        $membresia_expirada = false;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Membership</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/membresia.css">
</head>
<body>
    <div class="container">
        <?php include 'incluides/sidebar.php'; ?>
        <h1>Membership</h1>
        
        <?php if (isset($error)): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>

        <?php if (isset($success)): ?>
            <p class="success"><?php echo $success; ?></p>
        <?php endif; ?>

        <?php if ($membresia_expirada): ?>
            <div class="error">
                <p>Your membership has expired. Please renew your plan to continue using the system.</p>
            </div>
        <?php else: ?>
            <p>Your current membership expires on: <?php echo date('d/m/Y', strtotime($membresia['fechaVencimiento'])); ?></p>
        <?php endif; ?>

        <h2>Available Plans</h2>
        <div class="plan-container">
            <?php foreach ($planes as $plan): ?>
                <div class="plan-card">
                    <div class="plan-title"><?php echo ucfirst(strtolower(substr($plan['codigo'], 0, 3))); ?></div>
                    <div class="plan-price">$<?php echo number_format($plan['precio'], 2); ?> MXN</div>
                    <div class="plan-details">
                        <p>Duration: <?php echo $plan['duracion']; ?> months</p>
                        <p>Monthly Price: $<?php echo number_format($plan['precioMensual'], 2); ?> MXN</p>
                    </div>
                    <a href="#" class="btn select-plan" data-plan="<?php echo $plan['codigo']; ?>">Select</a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div id="paymentModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Confirm Payment</h2>
            <form id="paymentForm" method="POST">
                <input type="hidden" id="plan_id" name="plan_id" value="">
                <div>
                    <label for="numero_tarjeta">Card Number:</label>
                    <input type="text" id="numero_tarjeta" name="numero_tarjeta" required maxlength="16">
                </div>
                <div>
                    <label for="fecha_vencimiento">Expiration Date:</label>
                    <input type="month" id="fecha_vencimiento" name="fecha_vencimiento" required>
                </div>
                <div>
                    <label for="cvv">CVV:</label>
                    <input type="text" id="cvv" name="cvv" required maxlength="3">
                </div>
                <button type="submit" class="btn">Confirm Payment</button>
            </form>
        </div>
    </div>

    <script>
        const modal = document.getElementById("paymentModal");
        const selectPlanButtons = document.querySelectorAll(".select-plan");
        const closeButton = document.getElementsByClassName("close")[0];
        const planIdInput = document.getElementById("plan_id");

        selectPlanButtons.forEach(button => {
            button.addEventListener("click", function(e) {
                e.preventDefault();
                const planId = this.getAttribute("data-plan");
                planIdInput.value = planId;
                modal.style.display = "block";
            });
        });

        closeButton.onclick = function() {
            modal.style.display = "none";
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }

        // Form Validation
        document.getElementById("paymentForm").addEventListener("submit", function(e) {
            const numeroTarjeta = document.getElementById("numero_tarjeta").value;
            const fechaVencimiento = document.getElementById("fecha_vencimiento").value;
            const cvv = document.getElementById("cvv").value;

            if (numeroTarjeta.length !== 16 || !/^\d+$/.test(numeroTarjeta)) {
                alert("The card number must have 16 digits.");
                e.preventDefault();
                return;
            }

            const hoy = new Date();
            const fechaVencimientoDate = new Date(fechaVencimiento);
            if (fechaVencimientoDate <= hoy) {
                alert("The expiration date of the card is not valid.");
                e.preventDefault();
                return;
            }

            if (cvv.length !== 3 || !/^\d+$/.test(cvv)) {
                alert("The CVV must have 3 digits.");
                e.preventDefault();
                return;
            }

            if (!confirm("Are you sure you want to proceed with the payment?")) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>