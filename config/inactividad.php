<?php
// Iniciar sesión solo si no está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Tiempo máximo de inactividad (ejemplo: 5 minutos)
$tiempoInactividad = 300; // 300 segundos = 5 minutos

// Verificar si la sesión ya tiene una marca de tiempo
if (isset($_SESSION['last_activity'])) {
    if (time() - $_SESSION['last_activity'] > $tiempoInactividad) {
        // La sesión expiró
        session_unset();
        session_destroy();

        // Mostrar SweetAlert y redirigir a login
        echo '<!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <title>Sesión expirada</title>
            <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
        </head>
        <body>
            <script>
                swal("Tu sesión ha expirado", "Por favor inicia sesión nuevamente", "warning")
                .then(() => {
                    window.location.href = "../public/login.php";
                });
            </script>
        </body>
        </html>';
        exit();
    }
}

// Actualizar el tiempo de última actividad
$_SESSION['last_activity'] = time();

// Opcional: verifica si el usuario está logueado
if (!isset($_SESSION['usuario'])) {
    // No hay sesión activa → mandar al login
    echo '<!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Acceso denegado</title>
        <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    </head>
    <body>
        <script>
            swal("Acceso denegado", "Debes iniciar sesión", "error")
            .then(() => {
                window.location.href = "../public/login.php";
            });
        </script>
    </body>
    </html>';
    exit();
}
?>
