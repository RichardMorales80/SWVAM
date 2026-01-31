<?php
session_start();
session_unset();
session_destroy();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Sesión cerrada</title>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
</head>
<body>
    <script>
        swal("Sesión cerrada", "Has salido del sistema correctamente", "success")
        .then(() => {
            window.location.href = "../public/login.php";
        });
    </script>
</body>
</html>
