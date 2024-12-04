<?php
session_start();
include_once($_SERVER['DOCUMENT_ROOT'] . '/Outsourcing/config.php');

// Verify if the user is authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Process search
$search_keyword = isset($_GET['search_keyword']) ? mysqli_real_escape_string($conexion, $_GET['search_keyword']) : '';
$search_city = isset($_GET['search_city']) ? mysqli_real_escape_string($conexion, $_GET['search_city']) : '';

$query_vacantes = "
    SELECT v.numero, v.titulo, v.descripcion, v.salario, v.fechaInicio, v.fechaCierre,
           e.nombre AS empresa_nombre, e.ciudad, e.colonia, tc.nombre AS tipo_contrato
    FROM Vacante v
    JOIN Empresa e ON v.empresa = e.numero
    JOIN Tipo_Contrato tc ON v.tipo_contrato = tc.codigo
    WHERE v.fechaCierre >= CURDATE() AND v.fechaInicio <= CURDATE()
";

if (!empty($search_keyword)) {
    $query_vacantes .= " AND (v.titulo LIKE '%$search_keyword%' OR v.descripcion LIKE '%$search_keyword%')";
}

if (!empty($search_city)) {
    $query_vacantes .= " AND e.ciudad LIKE '%$search_city%'";
}

$query_vacantes .= " ORDER BY v.fechaInicio DESC";

$result_vacantes = mysqli_query($conexion, $query_vacantes);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Vacancies - Outsourcing System</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            margin-left: 100px;
            padding: 0;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        h1 {
            color: #000;
            text-align: center;
            margin-bottom: 30px;
        }
        .search-form {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .search-form form {
            display: flex;
            gap: 10px;
        }
        .search-form input[type="text"] {
            flex-grow: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .search-form button {
            padding: 10px 20px;
            background-color: #000;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .search-form button:hover {
            background-color: #333;
        }
        .vacantes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        .vacante-card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 20px;
            transition: transform 0.3s ease;
        }
        .vacante-card:hover {
            transform: translateY(-5px);
        }
        .vacante-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .vacante-company {
            font-weight: bold;
            color: #666;
        }
        .vacante-details {
            margin-top: 10px;
            font-size: 14px;
        }
        .vacante-link {
            display: inline-block;
            margin-top: 10px;
            padding: 5px 10px;
            background-color: #000;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }
        .vacante-link:hover {
            background-color: #333;
        }
        .no-results {
            text-align: center;
            font-size: 18px;
            margin-top: 50px;
        }
    </style>
</head>
<body>
    <?php include 'incluides/sidebar.php'; ?>
    <div class="container">
        <h1>Search Job Vacancies</h1>
        
        <div class="search-form">
            <form action="" method="GET">
                <input type="text" name="search_keyword" placeholder="Search by title or description" value="<?php echo htmlspecialchars($search_keyword); ?>">
                <input type="text" name="search_city" placeholder="City" value="<?php echo htmlspecialchars($search_city); ?>">
                <button type="submit">Search</button>
            </form>
        </div>

        <div class="vacantes-grid">
            <?php
            if (mysqli_num_rows($result_vacantes) > 0) {
                while ($vacante = mysqli_fetch_assoc($result_vacantes)) {
                    ?>
                    <div class="vacante-card">
                        <div class="vacante-title"><?php echo htmlspecialchars($vacante['titulo']); ?></div>
                        <div class="vacante-company"><?php echo htmlspecialchars($vacante['empresa_nombre']); ?></div>
                        <div class="vacante-details">
                            <p><strong>Location:</strong> <?php echo htmlspecialchars($vacante['ciudad']) . ', ' . htmlspecialchars($vacante['colonia']); ?></p>
                            <p><strong>Salary:</strong> <?php echo $vacante['salario'] ? '$' . number_format($vacante['salario'], 2) : 'Not specified'; ?>
                            <p><strong>Contract Type:</strong> <?php echo htmlspecialchars($vacante['tipo_contrato']); ?></p>
                            <p><strong>Start Date:</strong> <?php echo date('d/m/Y', strtotime($vacante['fechaInicio'])); ?></p>
                            <p><strong>Closing Date:</strong> <?php echo date('d/m/Y', strtotime($vacante['fechaCierre'])); ?></p>
                        </div>
                        <a href="detalles_vacante.php?id=<?php echo $vacante['numero']; ?>" class="vacante-link">View Details</a>
                    </div>
                    <?php
                }
            } else {
                echo "<p class='no-results'>No job vacancies found that match your search.</p>";
            }
            ?>
        </div>
    </div>
</body>
</html>
