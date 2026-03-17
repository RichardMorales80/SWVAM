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
<link rel="stylesheet" href="../public/estilos/estilos.css">
<!-- Bootstrap CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.4.1/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<!-- jQuery y Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>






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

<a href="<?= $paginaPrincipal ?>">
<i class="bi bi-house-door"></i> Inicio
</a>

<a href="<?= $paginaPrincipal ?>">
<a href="../views/administrador.php">
            ⬅ Atrás
        </a>
</a>


<a href="../app/mostrarcarro.php">
<i class="bi bi-cart3"></i> Carrito
(<span id="contadorCarrito">0</span>)
</a>

<a href="../config/cerrar_sesion.php" class="salir">
<i class="bi bi-box-arrow-right"></i> Salir
</a>

</nav>

</aside>
<div class="main-content">

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
