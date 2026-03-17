<?php
require_once __DIR__ . '/../config/Conexion.php';
require __DIR__ . '/../config/seguridad.php';
verificarRol(1);
$tipoMenu = "admin";
include("../views/navbar.php");

$pdo = Conexion::conectar();
$fecha_inicio = $_GET['fecha_inicio'] ?? '';
$fecha_fin    = $_GET['fecha_fin'] ?? '';

$condicion = "";
$params = [];

if($fecha_inicio && $fecha_fin){
    $condicion = " WHERE fecha BETWEEN :inicio AND :fin ";
    $params[':inicio'] = $fecha_inicio;
    $params[':fin'] = $fecha_fin;
}

/* =========================
   TOTALES GENERALES
========================= */

$sqlVentas = "SELECT COALESCE(SUM(total),0) FROM ventas $condicion";
$stmtV = $pdo->prepare($sqlVentas);
$stmtV->execute($params);
$totalVentas = $stmtV->fetchColumn();

$sqlGastos = "SELECT COALESCE(SUM(total),0) FROM gastos $condicion";
$stmtG = $pdo->prepare($sqlGastos);
$stmtG->execute($params);
$totalGastos = $stmtG->fetchColumn();

$balanceTotal = $totalVentas - $totalGastos;
$titulo = "Reporte Financiero";
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Reporte Financiero</title>

<link rel="stylesheet" href="../public/estilos/estilos.css">
<link rel="stylesheet" href="../public/estilos/encabezado.css">

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

<style>
#contenedorMasVendidos{
display:none;
}
</style>

</head>

<body>


<div class="topbar">

<div class="topbar-left">
<h4><?= $titulo ?></h4>
</div>

<div class="topbar-user">

<span class="usuario-nombre">
<?= $_SESSION['nombre'] ?? 'Usuario' ?>
</span>

<img src="../public/imagenes/avatar.png" class="avatar">

</div>

</div>


<!-- ================= CONTENIDO ================= -->

<div class="main-content">

<div class="catalogo-container">

<h2 class="mb-4">Dashboard Financiero</h2>

<!-- ================= FILTROS ================= -->

<div class="card card-form">

<form method="GET">

<div class="form-group">
<label>Desde</label>
<input type="date" name="fecha_inicio" class="form-control" value="<?= $fecha_inicio ?>">
</div>

<div class="form-group">
<label>Hasta</label>
<input type="date" name="fecha_fin" class="form-control" value="<?= $fecha_fin ?>">
</div>

<div class="acciones">

<button class="btn-sistema btn-editar">
Filtrar
</button>

<a href="reporte.php" class="btn-sistema btn-eliminar">
Limpiar
</a>

<a href="reporte_pdf.php?fecha_inicio=<?= $fecha_inicio ?>&fecha_fin=<?= $fecha_fin ?>" 
class="btn-sistema btn-editar">
PDF
</a>

<a href="reporte_excel.php?fecha_inicio=<?= $fecha_inicio ?>&fecha_fin=<?= $fecha_fin ?>" 
class="btn-sistema btn-guardar">
Excel
</a>

</div>

</form>

</div>

<!-- ================= RESUMEN ================= -->

<div class="row">

<div class="col-md-4">

<div class="card text-center">

<h5>Ventas</h5>

<p class="estado-activo fs-4">
$<?= number_format($totalVentas,2) ?>
</p>

</div>

</div>


<div class="col-md-4">

<div class="card text-center">

<h5>Gastos</h5>

<p class="estado-inactivo fs-4">
$<?= number_format($totalGastos,2) ?>
</p>

</div>

</div>


<div class="col-md-4">

<div class="card text-center">

<h5>Balance</h5>

<p class="fw-bold fs-4">
$<?= number_format($balanceTotal,2) ?>
</p>

</div>

</div>

</div>

<!-- ================= GRAFICA GENERAL ================= -->

<div class="card card-table">

<canvas id="graficaGeneral"></canvas>

</div>


<!-- ================= BOTON ================= -->

<div style="margin-top:20px;">

<button class="btn-sistema btn-editar" id="btnMasVendidos">
Mostrar Productos Más Vendidos
</button>

</div>


<!-- ================= PRODUCTOS MÁS VENDIDOS ================= -->

<div id="contenedorMasVendidos" class="card card-table">

<h3>Productos Más Vendidos</h3>

<table id="tablaMasVendidos" class="tabla-sistema">

<thead>

<tr>
<th>Producto</th>
<th>Cantidad</th>
<th>Precio Unitario</th>
<th>Total</th>
</tr>

</thead>

<tbody></tbody>

<tfoot>

<tr>
<th colspan="3">Gran Total</th>
<th id="granTotalProductos">$0.00</th>
</tr>

</tfoot>

</table>


<div class="card card-table">

<canvas id="graficaMasVendidos"></canvas>

</div>

</div>

</div>

</div>


<script>

$(document).ready(function(){

/* =========================
   GRAFICA GENERAL
========================= */

new Chart(document.getElementById('graficaGeneral'),{

type:'bar',

data:{
labels:['Ventas','Gastos','Balance'],

datasets:[{
label:'Monto $',

data:[
<?= $totalVentas ?>,
<?= $totalGastos ?>,
<?= $balanceTotal ?>
],

backgroundColor:[
'#aedab0',
'#786f6f',
'#4b506b'
]

}]
},

options:{
responsive:true,
plugins:{legend:{display:false}},
scales:{
y:{
beginAtZero:true
}
}
}

});


/* =========================
   PRODUCTOS MÁS VENDIDOS
========================= */

$('#btnMasVendidos').click(function(){

$.ajax({

url:'mas_vendidos.php',

type:'GET',

data:{
fecha_inicio:'<?= $fecha_inicio ?>',
fecha_fin:'<?= $fecha_fin ?>'
},

dataType:'json',

success:function(res){

if(res.productos.length > 0){

let tbody='';
let labels=[];
let cantidades=[];

res.productos.forEach(p=>{

tbody+=`<tr>
<td>${p.nombre}</td>
<td>${p.cantidad}</td>
<td>$${parseFloat(p.precio).toFixed(2)}</td>
<td>$${parseFloat(p.total).toFixed(2)}</td>
</tr>`;

labels.push(p.nombre);
cantidades.push(p.cantidad);

});

$('#tablaMasVendidos tbody').html(tbody);

$('#granTotalProductos').text(
'$'+parseFloat(res.granTotal).toFixed(2)
);

$('#contenedorMasVendidos').slideDown();

if(window.graficaMV) window.graficaMV.destroy();

window.graficaMV = new Chart(

document.getElementById('graficaMasVendidos'),

{

type:'bar',

data:{
labels:labels,

datasets:[{
label:'Cantidad Vendida',
data:cantidades,
backgroundColor:'#9ac9ef'
}]

},

options:{
responsive:true,
plugins:{legend:{display:false}},
scales:{y:{beginAtZero:true}}
}

});

}else{

Swal.fire(
'Aviso',
'No hay productos vendidos',
'info'
);

}

},

error:function(){

Swal.fire(
'Error',
'No se pudieron cargar los productos',
'error'
);

}

});

});

});

</script>
<!-- ================= SEGURIDAD BOTON ATRAS ================= -->

<script>

let salirConfirmado = false;

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

salirConfirmado = true;
window.location.href = "../config/cerrar_sesion.php";

}else{

history.pushState(null, null, location.href);

}

});

});

history.pushState(null, null, location.href);

</script>

</body>
</html>