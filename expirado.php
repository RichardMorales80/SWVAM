
<?php
session_start();
session_unset();
session_destroy();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Sesión expirada</title>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
</head>
<body>
    <script>
        swal("Sesión expirada", "Por seguridad, se cerró tu sesión por inactividad", "warning")
        .then(() => {
            window.location.href = "loguin.php";
        });
    </script>
</body>
</html>
