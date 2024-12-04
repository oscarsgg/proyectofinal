<?php
// Iniciar la sesión
session_start();

// Destruir todas las variables de sesión
$_SESSION = array();

// Destruir la cookie de sesión si está habilitada
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destruir la sesión
session_destroy();

// Verificar si la sesión fue destruida
if (session_status() == PHP_SESSION_NONE) {
    // Redirigir a la página de inicio de sesión
    header("Location: index.php");
    exit();
} else {
    // En caso de error al destruir la sesión
    echo "Error: Unable to logout properly.";
}
?>
