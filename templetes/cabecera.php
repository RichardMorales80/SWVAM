<?php
if (!defined('BASE_URL')) {
    define("BASE_URL", "/");
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// SOLO VARIABLES, NO VALIDACIONES
$idRol = $_SESSION['id_rol'] ?? 2;

switch ($idRol) {
    case 1:
        $paginaPrincipal = '../views/administrador.php';
        break;
    case 2:
        $paginaPrincipal = '../public/cliente.php';
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

<link rel="stylesheet" href="../public/estilos/aplicacion.css">
<link rel="stylesheet" href="../public/estilos/estilos.css">

<!-- Bootstrap -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.4.1/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

</head>

<body class="bg-light">

<!-- ================= SIDEBAR ================= -->
<aside class="sidebar-app">

<div class="logo-container">
    <a href="<?= $paginaPrincipal ?>">
        <img src="../public/imagenes/logo.png" width="90" alt="Logo">
    </a>
</div>

<nav>

<!-- BOTÓN ATRÁS -->
<a href="<?= $paginaPrincipal ?>">
    ⬅ Atrás
</a>

<!-- CARRITO -->
<!-- <a href="<?= BASE_URL ?>app/mostrarcarro.php">
   <!--  <i class="bi bi-cart3"></i> Carrito
   <!--  (<span id="contadorCarrito">0</span>)
<!-- </a>

<!-- SALIR -->
<a href="#" id="btnSalir">
    <i class="bi bi-box-arrow-right"></i> Salir
</a>

</nav>

</aside>

<div class="main-content">

<!-- ================= CONTADOR CARRITO ================= -->
<script>
function actualizarContador() {
    $.ajax({
        url: '../app/obtenerCarrito.php',
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

$(document).ready(function() {
    actualizarContador();
});
</script>


<!-- ================= SWEETALERT ================= -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).on('click', '#btnSalir', function(e){
    e.preventDefault();

    Swal.fire({
        title: '¿Cerrar sesión?',
        text: 'Serás redirigido al inicio',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, salir',
        cancelButtonText: 'Cancelar'
    }).then((result) => {

        if (result.isConfirmed) {

            Swal.fire({
                title: 'Cerrando sesión...',
                timer: 1200,
                showConfirmButton: false,
                willClose: () => {
                    window.location.href = '../index.php';
                }
            });

        }

    });
});
</script>
