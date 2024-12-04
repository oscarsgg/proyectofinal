<?php

include_once('../../Outsourcing/config.php');

// Pagination
$results_per_page = 100;
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$start_from = ($page - 1) * $results_per_page;

// Get total number of vacancies
$sql_count = "SELECT COUNT(*) AS total FROM vacante";
$result_count = $conexion->query($sql_count);
$row_count = $result_count->fetch_assoc();
$total_pages = ceil($row_count['total'] / $results_per_page);

// Get vacancies with pagination
$sql = "SELECT v.numero, v.titulo, v.descripcion, v.salario, v.es_directo, v.cantPostulantes, v.fechaInicio, v.fechaCierre, e.nombre as empresa_nombre, e.ciudad
        FROM vacante AS v
        INNER JOIN empresa AS e ON v.empresa = e.numero
        ORDER BY v.fechaInicio DESC
        LIMIT $start_from, $results_per_page";

$result = $conexion->query($sql);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/vacantes.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <title>Vacantes</title>
</head>

<body>
    <header class="header">
        <div class="main-nav-bar">
            <div class="marginbar">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-brand-cucumber" width="68"
                    height="68" viewBox="0 0 24 24" stroke-width="1.5" stroke="#ffffff" fill="none"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <path
                        d="M20 10.99c-.01 5.52 -4.48 10 -10 10.01v-2.26l-.01 -.01c-4.28 -1.11 -6.86 -5.47 -5.76 -9.75a8 8 0 0 1 9.74 -5.76c3.53 .91 6.03 4.13 6.03 7.78v-.01z" />
                    <path d="M10.5 8l-.5 -1" />
                    <path d="M13.5 14l.5 1" />
                    <path d="M9 12.5l-1 .5" />
                    <path d="M11 14l-.5 1" />
                    <path d="M13 8l.5 -1" />
                    <path d="M16 12.5l-1 -.5" />
                    <path d="M9 10l-1 -.5" />
                </svg>
            </div>
            <nav class="main-nav">
                <a href="index.php">Inicio</a>
                <a href="#caracteristicas">Vacantes</a>
                <a href="#testimonios">Empresas</a>
                <a href="#contacto">Certificaciones</a>
                <a href="#resenas">Reseñas</a>
            </nav>
        </div>
    </header>
    <section>
        <div class="job-filter-container">
            <div class="job-filter-header">
                <h2>Trabajos listados</h2>
            </div>
            <input type="text" class="search-bar" placeholder="Buscar trabajo" id="searchBar">
        </div>
    </section>
    <section class="marginxd">
        <div id="jobListings">
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo '<div class="job-listing">';
                    echo '<div class="job-title">' . htmlspecialchars($row['titulo']) . '</div>';
                    echo '<div class="job-details">';
                    echo '<div class="job-company"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" width="24" height="24" stroke-width="2"><path d="M8 9l5 5v7h-5v-4m0 4h-5v-7l5 -5m1 1v-6a1 1 0 0 1 1 -1h10a1 1 0 0 1 1 1v17h-8"></path><path d="M13 7l0 .01"></path><path d="M17 7l0 .01"></path><path d="M17 11l0 .01"></path><path d="M17 15l0 .01"></path></svg>' . htmlspecialchars($row['empresa_nombre']) . '</div>';
                    echo '<div class="job-location"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" width="24" height="24" stroke-width="2"><path d="M9 11a3 3 0 1 0 6 0a3 3 0 0 0 -6 0"></path><path d="M17.657 16.657l-4.243 4.243a2 2 0 0 1 -2.827 0l-4.244 -4.243a8 8 0 1 1 11.314 0z"></path></svg>' . htmlspecialchars($row['ciudad']) . '</div>';
                    echo '<div class="job-type"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7m0 2a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v9a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2z" /><path d="M8 7v-2a2 2 0 0 1 2 -2h4a2 2 0 0 1 2 2v2" /><path d="M12 12l0 .01" /><path d="M3 13a20 20 0 0 0 18 0" /></svg>' . ($row['es_directo'] ? 'Directo' : 'Indirecto') . '</div>';
                    echo '</div>';
                    echo '<div class="job-content">' . htmlspecialchars(substr($row['descripcion'], 0, 200)) . '...</div>';
                    echo '<button class="learn-more-btn" data-id="' . $row['numero'] . '">Conocer más</button>';
                    echo '</div>';
                }
            } else {
                echo "No se encontraron vacantes.";
            }
            ?>
        </div>
        
    </section>

    <div class="modal">
        <div class="card">
            <img src="https://uiverse.io/build/_assets/astronaut-WTFWARES.png" alt="" class="image" />
            <!-- <button class="modal-close is-large" aria-label="close"></button> -->
            <div class="heading">Qué esperas para unirte?</div>
            <div>
                <a href="login.php">
                    <button class="animated-button">
                        <span>Iniciar sesion</span>
                        <span></span>
                    </button>
                </a>
                <a href="registro.php">
                    <button class="animated-button">
                        <span>Registrarse</span>
                        <span></span>
                    </button>
                </a>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function () {
            // Función para abrir el modal
            function openModal() {
                $('.modal').addClass('is-active');
            }

            // Función para cerrar el modal
            function closeModal() {
                $('.modal').removeClass('is-active');
            }

            // Abrir modal al hacer clic en "Conocer más"
            $('.learn-more-btn').click(function (e) {
                e.preventDefault();
                openModal();
            });

            // Cerrar modal al hacer clic en el fondo
            $('.modal-background').click(closeModal);

            // Cerrar modal al hacer clic en el botón de cerrar
            $('.modal-close').click(closeModal);

            // Redirigir a la página de inicio de sesión
            $('#loginBtn').click(function () {
                window.location.href = 'login.php';
            });

            // Redirigir a la página de registro
            $('#registerBtn').click(function () {
                window.location.href = 'registro.php';
            });

            // Funcionalidad de búsqueda
            $('#searchBar').on('input', function () {
                var searchTerm = $(this).val().toLowerCase();
                $('.job-listing').each(function () {
                    var jobTitle = $(this).find('.job-title').text().toLowerCase();
                    var jobCompany = $(this).find('.job-company').text().toLowerCase();
                    if (jobTitle.includes(searchTerm) || jobCompany.includes(searchTerm)) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            });
            // Toggle filter sidebar on mobile
            $('#filterToggle').click(function () {
                $('.job-filter-container').toggleClass('is-active');
            });

            // Close modal when clicking on the close button or outside the modal
            $('.modal-close, .modal-background').click(function () {
                closeModal();
            });

            // Close modal function
            function closeModal() {
                $('.modal').removeClass('is-active');
            }

            // Cerrar modal al hacer clic fuera de él
            window.onclick = function (event) {
                if (event.target == modal) {
                    modal.style.display = 'none';
                }
            }
        });
    </script>
</body>

</html>