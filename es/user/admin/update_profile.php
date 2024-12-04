<?php
// update_profile.php
session_start();
include 'config.php';

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $admin_id = $_SESSION['admin_id'] ?? 1; // Usamos 1 como ejemplo, deberías usar la sesión real
    $correo = trim($_POST["correo"]);
    $estado = trim($_POST["estado"]);

    // Validar correo
    if(!filter_var($correo, FILTER_VALIDATE_EMAIL)){
        die("Por favor, introduce un correo electrónico válido.");
    }

    // Preparar la consulta de actualización
    $sql = "UPDATE usuario SET correo = ?, estado = ?";
    $params = [$correo, $estado];
    
    // Manejar la subida de la foto si se proporcionó una nueva
    if(isset($_FILES['foto']) && $_FILES['foto']['error'] == 0){
        $allowed = array("jpg" => "image/jpg", "jpeg" => "image/jpeg", "gif" => "image/gif", "png" => "image/png");
        $filename = $_FILES["foto"]["name"];
        $filetype = $_FILES["foto"]["type"];
        $filesize = $_FILES["foto"]["size"];

        // Verificar la extensión del archivo
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if(!array_key_exists($ext, $allowed)) die("Error: Por favor selecciona un formato de archivo válido.");

        // Verificar el tamaño del archivo - 5MB máximo
        $maxsize = 5 * 1024 * 1024;
        if($filesize > $maxsize) die("Error: El tamaño del archivo es mayor que el límite permitido (5MB)");

        // Verificar el tipo MIME del archivo
        if(in_array($filetype, $allowed)){
            // Crear un nombre de archivo único
            $new_filename = uniqid() . "." . $ext;
            $upload_dir = "img/";
            $upload_path = $upload_dir . $new_filename;

            // Verificar si el directorio existe, si no, crearlo
            if (!file_exists($upload_dir)) {
                if (!mkdir($upload_dir, 0755, true)) {
                    die('Error: No se pudo crear el directorio de subida.');
                }
            }

            // Asegurar que el directorio tenga permisos de escritura
            if (!is_writable($upload_dir)) {
                die('Error: El directorio de subida no tiene permisos de escritura.');
            }

            if(move_uploaded_file($_FILES["foto"]["tmp_name"], $upload_path)){
                $sql .= ", foto = ?";
                $params[] = $upload_path;
            } else {
                $error = error_get_last();
                echo "Error: Hubo un problema al subir tu archivo. Por favor, inténtalo de nuevo.<br>";
                echo "Detalles del error: " . $error['message'];
                exit();
            }
        } else {
            echo "Error: Hay un problema con el tipo de archivo. Por favor, inténtalo de nuevo."; 
            exit();
        }
    }

    $sql .= " WHERE numero = ?";
    $params[] = $admin_id;
    
    if($stmt = mysqli_prepare($conexion, $sql)){
        mysqli_stmt_bind_param($stmt, str_repeat('s', count($params)), ...$params);
        
        if(mysqli_stmt_execute($stmt)){
            $_SESSION['success_message'] = "Perfil actualizado con éxito.";
            header("location: profile.php");
            exit();
        } else {
            echo "Error: No se pudo actualizar el perfil. " . mysqli_error($conexion);
        }
    } else {
        echo "Error: No se pudo preparar la consulta. " . mysqli_error($conexion);
    }
    
    mysqli_stmt_close($stmt);
    mysqli_close($conexion);
} else {
    // Si no es una solicitud POST, redirigir a la página de perfil
    header("location: profile.php");
    exit();
}
?>