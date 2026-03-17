<?php
session_start();

require __DIR__ . '/../config/seguridad.php';
require_once __DIR__ . '/../config/Conexion.php';

verificarRoles([3]);

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../index.php");
    exit();
}

/* ======================
   CONEXION BD
====================== */

$pdo = Conexion::conectar();

/* ======================
   MENU
====================== */

$tipoMenu = "vendedor";
include("../views/navbar.php");

/* ======================
   DATOS USUARIO
====================== */

$nombreUsuario = $_SESSION['nombre'] ?? 'Vendedor';
$rolUsuario    = 'Vendedor';
$avatar        = "../public/imagenes/avatar.png";

/* ======================
   RESUMEN DATOS
====================== */

$totalVentas = 0;
$totalGastos = 0;
$totalUsuarios = 0;

try {

    $stmtVentas = $pdo->query("SELECT COUNT(*) AS total FROM ventas");
    $totalVentas = $stmtVentas->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

    $stmtGastos = $pdo->query("SELECT COUNT(*) AS total FROM gastos");
    $totalGastos = $stmtGastos->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

    $stmtUsuarios = $pdo->query("SELECT COUNT(*) AS total FROM usuarios");
    $totalUsuarios = $stmtUsuarios->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

} catch (Exception $e) {

    $totalVentas = 0;
    $totalGastos = 0;
    $totalUsuarios = 0;

}
?>

<!DOCTYPE html>
<html lang="es">

<head>

<meta charset="UTF-8">
<title>Panel Vendedor</title>

<link rel="stylesheet" href="../public/estilos/cliente.css">
<link rel="stylesheet" href="../public/estilos/estilos.css">
<link rel="stylesheet" href="../public/estilos/encabezado.css">

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>

<body>

<div class="main">

                 <!-- ======================
   TOPBAR
====================== -->

<div class="topbar">

<div class="titulo">Panel del Vendedor</div>

<div class="usuario-box">
            <img src="<?php echo htmlspecialchars($avatar); ?>" alt="Avatar">
            <div class="usuario-info">
                <span class="nombre"><?php echo htmlspecialchars($nombreUsuario); ?></span>
                <span class="rol"><?php echo htmlspecialchars($rolUsuario); ?></span>
            </div>
        </div>

</div>


<!-- ======================
   CONTENIDO
====================== -->

<div class="content">

<!-- CARDS -->

<div class="cards">

<div class="card">
<h3>Total de Ventas</h3>
<p><?= (int)$totalVentas ?></p>
</div>

<div class="card">
<h3>Total de Gastos</h3>
<p><?= (int)$totalGastos ?></p>
</div>

<div class="card">
<h3>Total de Usuarios</h3>
<p><?= (int)$totalUsuarios ?></p>
</div>

</div>


<!-- ======================
   ACCESOS
====================== -->

<div class="seccion">

<h3>Accesos permitidos</h3>

<div class="accesos">

<div class="acceso-card">
<h4>Aplicación</h4>
<p>Ver catálogo de productos.</p>
<a href="../app/aplicacion.php" class="btn-ir">Entrar</a>
</div>

<div class="acceso-card">
<h4>Facturas</h4>
<p>Consultar facturas registradas.</p>
<a href="../data/facturas.php" class="btn-ir">Entrar</a>
</div>

<div class="acceso-card">
<h4>Gastos</h4>
<p>Registrar gastos.</p>
<a href="../privadas/gastos.php" class="btn-ir">Entrar</a>
</div>

<div class="acceso-card">
<h4>Ventas</h4>
<p>Consultar ventas.</p>
<a href="../data/ventas.php" class="btn-ir">Entrar</a>
</div>

</div>

</div>


<!-- ======================
   GRAFICA
====================== -->

<div class="seccion">

<h3>Resumen visual</h3>

<div class="chart-container">

<canvas id="graficaVendedor"></canvas>

</div>

</div>


</div>
</div>


<!-- ======================
   GRAFICA
====================== -->

<script>

const ctx = document.getElementById('graficaVendedor');

new Chart(ctx, {

type: 'bar',

data: {

labels: ['Ventas','Gastos','Usuarios'],

datasets: [{

label: 'Cantidad',

data: [
<?= (int)$totalVentas ?>,
<?= (int)$totalGastos ?>,
<?= (int)$totalUsuarios ?>
],

backgroundColor: [

'rgba(30,60,114,0.8)',
'rgba(42,82,152,0.8)',
'rgba(90,120,200,0.8)'

],

borderWidth: 1,
borderRadius: 8

}]

},

options: {

responsive:true,

plugins:{
legend:{display:false}
},

scales:{
y:{
beginAtZero:true
}
}

}

});

</script>


<!-- ======================
   SEGURIDAD BOTON ATRAS
====================== -->

<script>

history.pushState(null, null, location.href);

window.addEventListener("popstate", function () {

Swal.fire({
title: "¿Quieres salir del panel?",
text: "Se cerrará tu sesión por seguridad.",
icon: "warning",
showCancelButton: true,
confirmButtonText: "Sí, salir",
cancelButtonText: "Cancelar"

}).then((result) => {

if (result.isConfirmed) {

window.location.href = "../config/cerrar_sesion.php";

} else {

history.pushState(null, null, location.href);

}

});

});

</script>

</body>
</html>