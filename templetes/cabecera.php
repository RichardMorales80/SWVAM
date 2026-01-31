<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ============================
   VALIDAR SESIÓN
============================ */
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../public/login.php");
    exit;
}

/* ============================
   VALIDAR ROL
============================ */
$idRol = $_SESSION['id_rol'] ?? 2; // por defecto cliente
switch ($idRol) {
    case 1:
        $paginaPrincipal = '../views/administrador.php';
        break;
    case 2:
        $paginaPrincipal = '../views/clientes.php';
        break;
    default:
        $paginaPrincipal = '../views/vendedor.php';
        break;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Matthew NDT</title>

<!-- Bootstrap CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.4.1/dist/css/bootstrap.min.css">

<!-- jQuery y Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.4.1/dist/js/bootstrap.min.js"></script>

<style>
body {
    padding-top:120px; /* Espacio para navbar fija */
}
</style>

</head>
<body class="bg-light">

<!-- ============================
     NAVBAR
============================ -->
<nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">

    <a class="navbar-brand" href="<?= $paginaPrincipal ?>">
        <img src="../public/imagenes/logo1.png" width="90" height="90" alt="Logo">
    </a>

    <button class="navbar-toggler" data-toggle="collapse" data-target="#my-nav">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div id="my-nav" class="collapse navbar-collapse">
        <ul class="nav nav-pills nav-fill">

            <li class="nav-item">
                <a class="nav-link" href="<?= $paginaPrincipal ?>">Inicio</a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="<?= $paginaPrincipal ?>">Atrás</a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="../app/mostrarcarro.php">
                    Carrito (<span id="contadorCarrito">0</span>)
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-danger" href="../config/cerrar_sesion.php">
                    Salir
                </a>
            </li>

        </ul>
    </div>
</nav>

<div class="container">

<!-- ============================
     SCRIPT PARA ACTUALIZAR CONTADOR DE CARRITO
============================ -->
<script>
function actualizarContador() {
    $.ajax({
        url: '../app/obtenerCarrito.php', // Archivo que devuelve el total
        method: 'GET',
        dataType: 'json',
        success: function(respuesta) {
            $('#contadorCarrito').text(respuesta.total || 0);
        },
        error: function() {
            $('#contadorCarrito').text(0);
        }
    });
}

// Actualiza el contador al cargar la página
$(document).ready(function() {
    actualizarContador();
});
</script>
