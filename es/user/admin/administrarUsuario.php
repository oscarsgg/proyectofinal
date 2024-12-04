<?php
// Conexión a la base de datos
include 'config.php';
// Función para obtener los datos de los prospectos
function getProspectos($conexion) {
    $sql = "SELECT numero, nombre, primerApellido, segundoApellido, aniosExperiencia, numTel, resumen FROM prospecto";
    $result = $conexion->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Función para obtener los datos de las empresas
function getEmpresas($conexion) {
    $sql = "SELECT numero, nombre, calle, numeroCalle, colonia, codigoPostal, nombreCont FROM empresa";
    $result = $conexion->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Función para obtener los datos de los administradores
function getAdmins($conexion) {
    $sql = "SELECT numero, correo, estado, rol FROM usuario WHERE rol = 'ADM'";
    $result = $conexion->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Obtener los datos
$prospectos = getProspectos($conexion);
$empresas = getEmpresas($conexion);
$admins = getAdmins($conexion);

// Cerrar la conexión
$conexion->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración</title>
    <link rel="stylesheet" href="css/administrarUsuario.css">
</head>
<body>
    <?php include 'incluides/sidebar.php'; ?>   
    <div class="container">
        <h1>Panel de Administración</h1>
        <div class="search-container">
            <input type="text" id="searchInput" placeholder="Buscar...">
        </div>
        <div class="tabs">
            <button class="tab-button active" data-tab="prospectos">Prospectos</button>
            <button class="tab-button" data-tab="empresas">Empresas</button>
            <button class="tab-button" data-tab="admins">Administradores</button>
        </div>

        <div class="tab-content active" id="prospectos">
            <h2>Prospectos</h2>
            <table>
                <thead>
                    <tr>
                        <th>Nombre Completo</th>
                        <th>Años de Experiencia</th>
                        <th>Teléfono</th>
                        <th>Resumen</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($prospectos as $prospecto): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($prospecto['nombre'] . ' ' . $prospecto['primerApellido'] . ' ' . $prospecto['segundoApellido']); ?></td>
                        <td><?php echo $prospecto['aniosExperiencia'] !== null ? htmlspecialchars($prospecto['aniosExperiencia']) : 'N/A'; ?></td>
                        <td><?php echo htmlspecialchars($prospecto['numTel']); ?></td>
                        <td class="resumen-cell">
                            <div class="resumen-content"><?php echo htmlspecialchars($prospecto['resumen']); ?></div>
                        </td>
                        <td>
                            <button class="view-btn" data-type="prospecto" data-id="<?php echo $prospecto['numero']; ?>">Ver</button>
                            <button class="edit-btn" data-type="prospecto" data-id="<?php echo $prospecto['numero']; ?>">Editar</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="tab-content" id="empresas">
            <h2>Empresas</h2>
            <table>
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Dirección</th>
                        <th>Código Postal</th>
                        <th>Contacto</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($empresas as $empresa): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($empresa['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($empresa['calle'] . ' ' . $empresa['numeroCalle'] . ', ' . $empresa['colonia']); ?></td>
                        <td><?php echo htmlspecialchars($empresa['codigoPostal']); ?></td>
                        <td><?php echo htmlspecialchars($empresa['nombreCont']); ?></td>
                        <td>
                            <button class="view-btn" data-type="empresa" data-id="<?php echo $empresa['numero']; ?>">Ver</button>
                            <button class="edit-btn" data-type="empresa" data-id="<?php echo $empresa['numero']; ?>">Editar</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="tab-content" id="admins">
            <h2>Administradores</h2>
            <table>
                <thead>
                    <tr>
                        <th>Correo</th>
                        <th>Estado</th>
                        <th>Rol</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($admins as $admin): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($admin['correo']); ?></td>
                        <td><?php echo htmlspecialchars($admin['estado']); ?></td>
                        <td><?php echo htmlspecialchars($admin['rol']); ?></td>
                        <td>
                            <button class="view-btn" data-type="admin" data-id="<?php echo $admin['numero']; ?>">Ver</button>
                            <button class="edit-btn" data-type="admin" data-id="<?php echo $admin['numero']; ?>">Editar</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Editar <span id="editType"></span></h2>
            <form id="editForm">
                <input type="hidden" id="editId" name="id">
                <input type="hidden" id="editUserType" name="userType">
                <div id="formFields" class="form-grid"></div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Guardar cambios</button>
                    <button type="button" class="btn btn-secondary" id="cancelEdit">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <div id="viewModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Ver <span id="viewType"></span></h2>
            <div id="viewFields"></div>
        </div>
    </div>

    <script src="administrarUsuario.js"></script>
</body>
</html>