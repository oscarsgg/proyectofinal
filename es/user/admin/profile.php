<?php
// index.php
session_start();
include 'config.php';
// Asumimos que el ID del administrador está almacenado en la sesión
$admin_id = $_SESSION['admin_id'] ?? 1; // Usamos 1 como ejemplo, deberías usar la sesión real

$sql = "SELECT numero, correo, estado, rol, foto FROM usuario WHERE numero = ?";
if($stmt = mysqli_prepare($conexion, $sql)){
    mysqli_stmt_bind_param($stmt, "s", $admin_id);
    if(mysqli_stmt_execute($stmt)){
        mysqli_stmt_store_result($stmt);
        if(mysqli_stmt_num_rows($stmt) == 1){
            mysqli_stmt_bind_result($stmt, $numero, $correo, $estado, $rol, $foto);
            mysqli_stmt_fetch($stmt);
        } else {
            echo "No se encontró el perfil del administrador.";
            exit();
        }
    } else {
        echo "Oops! Algo salió mal. Por favor, inténtalo de nuevo más tarde.";
        exit();
    }
} else {
    echo "Oops! Algo salió mal. Por favor, inténtalo de nuevo más tarde.";
    exit();
}
mysqli_stmt_close($stmt);
mysqli_close($conexion);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de Administrador</title>
    <link rel="stylesheet" href="css/perfiAdmin.css">
</head>
<body>
    <?php include 'incluides/sidebar.php'; ?>
    <div class="container">
        <h1>Perfil de Administrador</h1>
        <div class="profile-image">
            <img src="<?php echo htmlspecialchars($foto ? $foto : 'img/user.jpg'); ?>" alt="Foto de perfil">
        </div>
        <form action="update_profile.php" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="foto">Cambiar Foto de Perfil:</label>
                <input type="file" id="foto" name="foto" accept="image/*">
            </div>
            <div class="form-group">
                <label for="numero">Número de Usuario:</label>
                <input type="text" id="numero" name="numero" value="<?php echo htmlspecialchars($numero); ?>" readonly>
            </div>
            <div class="form-group">
                <label for="correo">Correo Electrónico:</label>
                <input type="email" id="correo" name="correo" value="<?php echo htmlspecialchars($correo); ?>" readonly>
            </div>
            <div class="form-group">
                <label for="estado">Estado:</label>
                <input type="text" id="estado" name="estado" value="<?php echo htmlspecialchars($estado); ?>" readonly>
            </div>
            <div class="form-group">
                <label for="rol">Rol:</label>
                <input type="text" id="rol" name="rol" value="<?php echo htmlspecialchars($rol); ?>" readonly>
            </div>
            <div class="form-group">
                <input type="submit" value="Actualizar Perfil">
            </div>
        </form>
    </div>
</body>
</html>