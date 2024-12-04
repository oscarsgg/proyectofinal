<?php
// gestionar_vacantes.php

session_start();
include_once($_SERVER['DOCUMENT_ROOT'] . '/Outsourcing/config.php');
require_once 'check_membership.php';

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
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

// Obtener el término de búsqueda si existe
$search = isset($_GET['search']) ? mysqli_real_escape_string($conexion, $_GET['search']) : '';

// Consulta para obtener las vacantes de la empresa
$query = "SELECT numero, titulo, fechaCierre FROM Vacante WHERE empresa = $empresa_id";
if (!empty($search)) {
    $query .= " AND titulo LIKE '%$search%'";
}
$query .= " ORDER BY fechaCierre DESC";

$result = mysqli_query($conexion, $query);

?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vacancy management</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&family=Poppins:wght@500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/gestionarVac.css">
</head>
<body>
    <?php include 'incluides/sidebar.php'; ?>
    <div class="container">
        <h1>Manage Job Vacancies</h1>
        
        <div class="search-container">
            <form action="" method="GET" style="display: flex; width: 100%;">
                <input type="text" name="search" placeholder="Search vacancies..." 
                    value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit">Search</button>
            </form>
        </div>

        <div class="vacantes-list">
            <?php
            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $is_active = strtotime($row['fechaCierre']) >= strtotime(date('Y-m-d'));
                    ?>
                    <div class="vacante-item">
                        <div class="vacante-details">
                            <div class="vacante-title"><?php echo htmlspecialchars($row['titulo']); ?></div>
                            <span class="vacante-status <?php echo $is_active ? 'status-active' : 'status-inactive'; ?>">
                                <?php echo $is_active ? 'Active' : 'Inactive'; ?>
                            </span>
                        </div>
                        <a href="detalle_vacante.php?id=<?php echo $row['numero']; ?>" class="vacante-link">View Details</a>
                    </div>
                    <?php
                }
            } else {
                echo "<p>No job vacancies found.</p>";
            }
            ?>
        </div>
    </div>
</body>
</html>
