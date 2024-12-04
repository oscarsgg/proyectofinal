<?php
session_start();
include_once('../../../../Outsourcing/config.php');

// Verificar si el usuario está autenticado y es un administrador
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'ADM') {
    header("Location: login.php");
    exit();
}

// Función para obtener todas las vacantes del sistema
function getVacantes($conexion) {
    $query = "SELECT v.numero, v.titulo, v.estado, v.fechaInicio, v.fechaCierre, e.nombre as nombre_empresa
              FROM vacante as v
              INNER JOIN empresa as e ON v.empresa = e.numero
              ORDER BY v.fechaInicio DESC";
    
    $result = $conexion->query($query);
    
    $vacantes = [];
    $currentDate = date('Y-m-d');
    while ($row = $result->fetch_assoc()) {
        if ($row['estado'] == 1) {
            if ($currentDate >= $row['fechaInicio'] && $currentDate <= $row['fechaCierre']) {
                $row['estado_texto'] = 'Activa';
            } else if ($currentDate > $row['fechaCierre']) {
                $row['estado_texto'] = 'Terminada';
            } else {
                $row['estado_texto'] = 'Programada';
            }
        } else {
            $row['estado_texto'] = 'Cancelada';
        }
        $vacantes[] = $row;
    }
    
    return $vacantes;
}

// Obtener todas las vacantes
$vacantes = getVacantes($conexion);

// Procesar acciones POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'agregar':
                $titulo = $_POST['titulo'];
                $estado = $_POST['estado'];
                $empresa = $_POST['empresa'];
                $fechaInicio = $_POST['fechaInicio'];
                $fechaCierre = $_POST['fechaCierre'];
                
                $stmt = $conexion->prepare("INSERT INTO vacante (titulo, estado, empresa, fechaInicio, fechaCierre) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("siiss", $titulo, $estado, $empresa, $fechaInicio, $fechaCierre);
                $stmt->execute();
                break;
            
            case 'editar':
                $id = $_POST['id'];
                $titulo = $_POST['titulo'];
                $estado = $_POST['estado'];
                $fechaInicio = $_POST['fechaInicio'];
                $fechaCierre = $_POST['fechaCierre'];
                
                $stmt = $conexion->prepare("UPDATE vacante SET titulo = ?, estado = ?, fechaInicio = ?, fechaCierre = ? WHERE numero = ?");
                $stmt->bind_param("sissi", $titulo, $estado, $fechaInicio, $fechaCierre, $id);
                $stmt->execute();
                break;
            
            case 'eliminar':
                $id = $_POST['id'];
                
                $stmt = $conexion->prepare("DELETE FROM vacante WHERE numero = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                break;
        }
        
        // Recargar la página para mostrar los cambios
        header("Location: gestionar_vacantes.php");
        exit();
    }
}

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Vacantes</title>
    <link rel="stylesheet" href="css/vacantes.css">
</head>

<body>
    <?php include 'incluides/sidebar.php'; ?>
    <!-- Contenido principal -->
    <div class="main-content">
        <h1>Gestión de Vacantes</h1>
        <div id="addVacancyModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h2 id="modalTitle">Agregar Nueva Vacante</h2>

                <form id="vacancyForm" method="post">
                    <input type="hidden" name="action" id="formAction" value="agregar">
                    <input type="hidden" name="id" id="vacancyId" value="">
                    <div class="form-group">
                        <label for="vacancyTitle">Título de la Vacante:</label>
                        <input type="text" id="vacancyTitle" name="titulo" class="input-form" required>
                    </div>
                    <div class="form-group">
                        <label for="vacancyStatus">Estado:</label>
                        <select id="vacancyStatus" name="estado" class="input-form">
                            <option value="1">Activa</option>
                            <option value="0">Cancelada</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="vacancyCompany">Empresa:</label>
                        <select id="vacancyCompany" name="empresa" class="input-form" required>
                            <?php
                            $empresas = $conexion->query("SELECT numero, nombre FROM Empresa");
                            while ($empresa = $empresas->fetch_assoc()) {
                                echo "<option value='" . $empresa['numero'] . "'>" . htmlspecialchars($empresa['nombre']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="vacancyStartDate">Fecha de Inicio:</label>
                        <input type="date" id="vacancyStartDate" name="fechaInicio" class="input-form" required>
                    </div>
                    <div class="form-group">
                        <label for="vacancyEndDate">Fecha de Cierre:</label>
                        <input type="date" id="vacancyEndDate" name="fechaCierre" class="input-form" required>
                    </div>
                    <button type="submit" class="btn-add" id="saveButton">Guardar Vacante</button>
                </form>
            </div>
        </div>
        <table>
            <thead>
                <tr>
                    <th>TITULO</th>
                    <th>EMPRESA</th>
                    <th>ESTADO</th>
                    <th>ACCIONES</th>
                </tr>
            </thead>
            
            <tbody id="vacantesTableBody">
                <?php foreach ($vacantes as $vacante): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($vacante['titulo']); ?></td>
                        <td><?php echo htmlspecialchars($vacante['nombre_empresa']); ?></td>
                        <td>
                            <span class="status-badge <?php echo $vacante['estado_texto']; ?>">
                                <?php echo ucfirst(htmlspecialchars($vacante['estado_texto'])); ?>
                            </span>
                        </td>
                        <td>
                            <div class="actions">
                                <button onclick="abrirModalEditar(<?php echo $vacante['numero']; ?>, '<?php echo addslashes($vacante['titulo']); ?>', '<?php echo $vacante['estado']; ?>', '<?php echo $vacante['fechaInicio']; ?>', '<?php echo $vacante['fechaCierre']; ?>')">
                                    <img src="https://img.icons8.com/ios/452/edit--v1.png" alt="Edit">
                                </button>
                                <button onclick="confirmarEliminar(<?php echo $vacante['numero']; ?>)">
                                    <img src="https://img.icons8.com/ios/452/delete--v1.png" alt="Delete">
                                </button>
                                <button class="redirect-button" onclick="redirigirPagina(<?php echo $vacante['numero']; ?>)">
                                    <img src="https://img.icons8.com/ios/452/external-link.png" alt="Redirect">
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="pagination" id="paginationControls">
            <button class="pagination-btn" id="prevPageBtn"><< Prev</button>
            <!-- Aquí se agregarán los botones de página dinámicamente -->
            <button class="pagination-btn" id="nextPageBtn">Next >></button>
        </div>
    </div>

    <script>
        // Variables de control para el estado de edición y paginación
        let isEditing = false;
        let currentPage = 1;
        const itemsPerPage = 10;

        document.addEventListener("DOMContentLoaded", () => {
            const modal = document.getElementById("addVacancyModal");
            const btn = document.getElementById("addVacancyBtn");
            const span = document.getElementsByClassName("close")[0];
            const form = document.getElementById("vacancyForm");

            btn.onclick = function() {
                isEditing = false;
                form.reset();
                document.getElementById("modalTitle").innerText = "Agregar Nueva Vacante";
                document.getElementById("saveButton").innerText = "Guardar Vacante";
                document.getElementById("formAction").value = "agregar";
                document.getElementById("vacancyId").value = "";
                modal.style.display = "flex";
            };

            span.onclick = function() {
                modal.style.display = "none";
            };

            window.onclick = function(event) {
                if (event.target == modal) {
                    modal.style.display = "none";
                }
            };

            actualizarPaginacion();
        });

        function abrirModalEditar(id, titulo, estado, fechaInicio, fechaCierre) {
            isEditing = true;
            document.getElementById("vacancyId").value = id;
            document.getElementById("vacancyTitle").value = titulo;
            document.getElementById("vacancyStatus").value = estado;
            document.getElementById("vacancyStartDate").value = fechaInicio;
            document.getElementById("vacancyEndDate").value = fechaCierre;
            document.getElementById("modalTitle").innerText = "Editar Vacante";
            document.getElementById("saveButton").innerText = "Actualizar Vacante";
            document.getElementById("formAction").value = "editar";
            document.getElementById("addVacancyModal").style.display = "flex";
        }

        function confirmarEliminar(id) {
            if (confirm("¿Estás seguro de que quieres eliminar esta vacante?")) {
                const form = document.createElement('form');
                form.method = 'post';
                form.innerHTML = `
                    <input type="hidden" name="action" value="eliminar">
                    <input type="hidden" name="id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function redirigirPagina(id) {
            window.location.href = `detalle_vacante.php?id=${id}`;
        }

        function actualizarPaginacion() {
            const rows = document.querySelectorAll("#vacantesTableBody tr");
            const totalPages = Math.ceil(rows.length / itemsPerPage);
            
            document.getElementById("paginationControls").innerHTML = `
                <button class="pagination-btn" id="prevPageBtn" ${currentPage === 1 ? 'disabled' : ''}>< Prev</button>
                ${Array.from({length: totalPages}, (_, i) => 
                    `<button class="pagination-btn ${currentPage === i + 1 ? 'active' : ''}" onclick="cambiarPagina(${i + 1})">${i + 1}</button>`
                ).join('')}
                <button class="pagination-btn" id="nextPageBtn" ${currentPage === totalPages ? 'disabled' : ''}>Next ></button>
            `;

            mostrarPagina(currentPage);

            document.getElementById("prevPageBtn").onclick = () => cambiarPagina(currentPage - 1);
            document.getElementById("nextPageBtn").onclick = () => cambiarPagina(currentPage + 1);
        }

        function cambiarPagina(page) {
            currentPage = page;
            mostrarPagina(currentPage);
            actualizarPaginacion();
        }

        function mostrarPagina(page) {
            const rows = document.querySelectorAll("#vacantesTableBody tr");
            const start = (page - 1) * itemsPerPage;
            const end = start + itemsPerPage;

            rows.forEach((row, index) => {
                row.style.display = (index >= start && index < end) ? '' : 'none';
            });
        }
    </script>
</body>

</html>