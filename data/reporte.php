<?php
require_once __DIR__ . '/../config/Conexion.php';
require __DIR__ . '/../config/seguridad.php';

verificarRol(1);

$tipoMenu = "admin";

$pdo = Conexion::conectar();

$fecha_inicio = $_GET['fecha_inicio'] ?? '';
$fecha_fin    = $_GET['fecha_fin'] ?? '';

$condicion = "";
$params = [];

if($fecha_inicio && $fecha_fin){

    $condicion = " WHERE fecha BETWEEN :inicio AND :fin ";

    $params[':inicio'] = $fecha_inicio;
    $params[':fin']    = $fecha_fin;
}

/* ======================================
   TOTALES
====================================== */

$sqlVentas = "
SELECT COALESCE(SUM(total),0)
FROM ventas
$condicion
";

$stmtV = $pdo->prepare($sqlVentas);
$stmtV->execute($params);

$totalVentas = (float)$stmtV->fetchColumn();


$sqlGastos = "
SELECT COALESCE(SUM(total),0)
FROM gastos
$condicion
";

$stmtG = $pdo->prepare($sqlGastos);
$stmtG->execute($params);

$totalGastos = (float)$stmtG->fetchColumn();


/* ======================================
   BALANCE
====================================== */

$balanceTotal = $totalVentas - $totalGastos;


/* ======================================
   TOTAL DE VENTAS
====================================== */

$sqlCantidadVentas = "
SELECT COUNT(*)
FROM ventas
$condicion
";

$stmtCantidad = $pdo->prepare($sqlCantidadVentas);
$stmtCantidad->execute($params);

$totalRegistrosVentas = (int)$stmtCantidad->fetchColumn();


/* ======================================
   TICKET PROMEDIO
====================================== */

$ticketPromedio = $totalRegistrosVentas > 0
? $totalVentas / $totalRegistrosVentas
: 0;


/* ======================================
   PORCENTAJES
====================================== */

$porcentajeGastos = $totalVentas > 0
? ($totalGastos / $totalVentas) * 100
: 0;


$porcentajeGanancia = $totalVentas > 0
? ($balanceTotal / $totalVentas) * 100
: 0;


/* ======================================
   ESTADO FINANCIERO
====================================== */

if($balanceTotal > 10000){

    $estadoFinanciero = "Excelente";
    $colorEstado = "#2E7D32";

}
elseif($balanceTotal > 0){

    $estadoFinanciero = "Estable";
    $colorEstado = "#1976D2";

}
else{

    $estadoFinanciero = "Pérdidas";
    $colorEstado = "#C62828";
}


/* ======================================
   ANALISIS AUTOMATICO
====================================== */

if($balanceTotal > 0){

    $mensajeAnalisis = "
    El negocio presenta ganancias positivas
    durante el periodo seleccionado,
    mostrando estabilidad financiera.
    ";

}else{

    $mensajeAnalisis = "
    El negocio presenta pérdidas durante
    el periodo seleccionado, por lo que
    se recomienda revisar gastos y ventas.
    ";
}


/* ======================================
   ACTIVOS
====================================== */

$sqlActivos = "
SELECT COALESCE(SUM(cantidad * precio),0)
FROM productos
";

$stmtActivos = $pdo->prepare($sqlActivos);
$stmtActivos->execute();

$totalActivos = (float)$stmtActivos->fetchColumn();


/* ======================================
   TITULO
====================================== */

$titulo = "Panel Financiero";

?>

<!DOCTYPE html>
<html lang="es">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Panel Financiero</title>

<link rel="stylesheet" href="../public/estilos/estilos1.css">
<link rel="stylesheet" href="../public/estilos/estilos.css">
<link rel="stylesheet" href="../public/estilos/encabezado.css">

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>

/* ======================================
   GRAFICAS
====================================== */

.contenedor-grafica{
    width:100%;
    max-width:650px;
    margin:20px auto;
}

#graficaGeneral{
    width:100% !important;
    height:350px !important;
}

#graficaMasVendidos{
    width:100% !important;
    height:350px !important;
}

#contenedorMasVendidos{
    display:none;
}

/* ======================================
   CARDS
====================================== */

.card-mini{

    box-shadow:0 4px 12px rgba(0,0,0,.08);

    border-radius:12px;

    transition:.3s;

    padding:20px;

    background:#fff;
}

.card-mini:hover{
    transform:translateY(-3px);
}

.card-form{
    box-shadow:0 4px 12px rgba(0,0,0,.08);
}

.resumen-cards{

    display:grid;

    grid-template-columns:
    repeat(auto-fit,minmax(220px,1fr));

    gap:15px;

    margin-top:20px;
}

.card-mini h5{

    margin-bottom:10px;

    color:#555;
}

.card-mini p{

    font-size:20px;

    font-weight:bold;

    margin:0;
}

.flujo-positivo{
    color:#2E7D32;
}

.flujo-negativo{
    color:#C62828;
}

</style>

</head>

<body>

<?php include("../views/navbar.php"); ?>

<div class="topbar">

<div class="topbar-left">
<h4><?= $titulo ?></h4>
</div>

<div class="topbar-user">

<span>
<?= $_SESSION['nombre'] ?? 'Usuario' ?>
</span>

<img
src="../public/imagenes/avatar.png"
class="avatar">

</div>

</div>

<div class="main-content">
<div class="catalogo-container">
<div class="dashboard-admin">

<h2 class="mb-4">
Panel de Control Financiero
</h2>

<p style="margin-bottom:20px;color:#666;">

Visualización general del comportamiento
financiero del negocio mediante indicadores
de ventas, gastos, flujo de efectivo y activos.

</p>

<!-- ======================================
     FILTROS
====================================== -->

<div class="card card-form">

<form method="GET">

<div class="form-inline">

<div class="form-group">

<label>Desde</label>

<input
type="date"
name="fecha_inicio"
max="<?= date('Y-m-d') ?>"
value="<?= $fecha_inicio ?>">

</div>

<div class="form-group">

<label>Hasta</label>

<input
type="date"
name="fecha_fin"
max="<?= date('Y-m-d') ?>"
value="<?= $fecha_fin ?>">

</div>

<div class="acciones">

<button class="btn-sistema btn-editar">
Filtrar
</button>

<a
href="reporte.php"
class="btn-sistema btn-eliminar">

Limpiar

</a>

<a
href="reporte_pdf.php?fecha_inicio=<?= $fecha_inicio ?>&fecha_fin=<?= $fecha_fin ?>"
target="_blank"
class="btn-sistema btn-editar">

PDF

</a>

<a
href="reporte_excel.php?fecha_inicio=<?= $fecha_inicio ?>&fecha_fin=<?= $fecha_fin ?>"
class="btn-sistema btn-guardar">

Excel

</a>

</div>

</div>

</form>

</div>

<!-- ======================================
     CARDS
====================================== -->

<div class="resumen-cards">

<!-- ACTIVOS -->

<div class="card-mini">

<h5>Activos</h5>

<p style="color:#1565C0;">

$<?= number_format($totalActivos,2) ?>

</p>

</div>

<!-- VENTAS -->

<div class="card-mini">

<h5>Ventas Totales</h5>

<p class="flujo-positivo">

$<?= number_format($totalVentas,2) ?>

</p>

</div>

<!-- GASTOS -->

<div class="card-mini">

<h5>Gastos Totales</h5>

<p class="flujo-negativo">

$<?= number_format($totalGastos,2) ?>

</p>

</div>

<!-- BALANCE -->

<div class="card-mini">

<h5>Balance Neto</h5>

<p style="
color:<?= $balanceTotal >= 0 ? '#2E7D32' : '#C62828' ?>;
">

$<?= number_format($balanceTotal,2) ?>

</p>

</div>

<!-- MARGEN -->

<div class="card-mini">

<h5>Margen de Ganancia</h5>

<p>

<?= number_format($porcentajeGanancia,1) ?>%

</p>

</div>

<!-- GASTOS % -->

<div class="card-mini">

<h5>% Gastos</h5>

<p>

<?= number_format($porcentajeGastos,1) ?>%

</p>

</div>

<!-- TICKET -->

<div class="card-mini">

<h5>Ticket Promedio</h5>

<p>

$<?= number_format($ticketPromedio,2) ?>

</p>

</div>

<!-- ESTADO -->

<div class="card-mini">

<h5>Estado Financiero</h5>

<p style="color:<?= $colorEstado ?>;">

<?= $estadoFinanciero ?>

</p>

</div>

<!-- TOTAL VENTAS -->

<div class="card-mini">

<h5>Total Ventas</h5>

<p>

<?= $totalRegistrosVentas ?>

</p>

</div>

</div>

<!-- ======================================
     ANALISIS
====================================== -->

<div class="card card-form" style="margin-top:20px;">

<h3>Análisis Financiero</h3>

<p style="margin-top:10px;">

<?= $mensajeAnalisis ?>

</p>

<p style="margin-top:15px;color:#666;">

Última actualización:
<?= date('d/m/Y H:i') ?>

</p>

</div>

<!-- ======================================
     FLUJO FINANCIERO
====================================== -->

<div class="card card-form" style="margin-top:20px;">

<h3>Flujo Financiero</h3>

<table class="tabla-sistema">

<thead>

<tr>
<th>Movimiento</th>
<th>Monto</th>
<th>Tipo</th>
</tr>

</thead>

<tbody>

<tr>

<td>Ingresos por ventas</td>

<td class="flujo-positivo">

$<?= number_format($totalVentas,2) ?>

</td>

<td>Entrada</td>

</tr>

<tr>

<td>Gastos operativos</td>

<td class="flujo-negativo">

$<?= number_format($totalGastos,2) ?>

</td>

<td>Salida</td>

</tr>

<tr>

<td><strong>Flujo Neto</strong></td>

<td style="
font-weight:bold;
color:<?= $balanceTotal >= 0 ? '#2E7D32' : '#C62828' ?>;
">

<strong>

$<?= number_format($balanceTotal,2) ?>

</strong>

</td>

<td>

<?= $balanceTotal >= 0 ? 'Positivo' : 'Negativo' ?>

</td>

</tr>

</tbody>

</table>

</div>

<!-- ======================================
     BOTON
====================================== -->

<div style="margin-top:20px;">

<button
class="btn-sistema btn-editar"
id="btnMasVendidos">

Mostrar Productos Más Vendidos

</button>

</div>

</div>
</div>

<!-- ======================================
     GRAFICA GENERAL
====================================== -->

<div class="contenedor-grafica">

<canvas id="graficaGeneral"></canvas>

</div>

<!-- ======================================
     PRODUCTOS MAS VENDIDOS
====================================== -->

<div
id="contenedorMasVendidos"
class="card card-table">

<h3>Productos Más Vendidos</h3>

<table
id="tablaMasVendidos"
class="tabla-sistema">

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

<th colspan="3">
Gran Total
</th>

<th id="granTotalProductos">
$0.00
</th>

</tr>

</tfoot>

</table>

<div class="contenedor-grafica">

<canvas id="graficaMasVendidos"></canvas>

</div>

</div>

</div>

<script>

$(document).ready(function(){

/* ======================================
   GRAFICA GENERAL
====================================== */

let ventas  = parseFloat('<?= $totalVentas ?>') || 0;

let gastos  = parseFloat('<?= $totalGastos ?>') || 0;

let balance = parseFloat('<?= $balanceTotal ?>') || 0;

let activos = parseFloat('<?= $totalActivos ?>') || 0;


new Chart(
document.getElementById('graficaGeneral'),

{

type:'doughnut',

data:{

labels:[
'Ventas',
'Gastos',
'Balance',
'Activos'
],

datasets:[{

data:[
ventas,
gastos,
balance,
activos
],

backgroundColor:[
'#4CAF50',
'#F44336',
'#3F51B5',
'#1565C0'
]

}]

},

options:{
responsive:true,
maintainAspectRatio:false
}

});


/* ======================================
   PRODUCTOS MAS VENDIDOS
====================================== */

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

if(res.productos && res.productos.length > 0){

let labels = [];
let datos  = [];
let tbody  = '';

res.productos.forEach(p=>{

let total = parseFloat(p.total) || 0;

tbody += `
<tr>
<td>${p.nombre}</td>
<td>${p.cantidad}</td>
<td>$${parseFloat(p.precio).toFixed(2)}</td>
<td>$${total.toFixed(2)}</td>
</tr>
`;

labels.push(p.nombre);
datos.push(total);

});

$('#tablaMasVendidos tbody').html(tbody);

$('#granTotalProductos').text(
'$'+parseFloat(res.granTotal || 0).toFixed(2)
);

$('#contenedorMasVendidos').slideDown();

if(window.graficaMV){

window.graficaMV.destroy();

}

window.graficaMV = new Chart(

document.getElementById('graficaMasVendidos'),

{

type:'pie',

data:{

labels:labels,

datasets:[{

data:datos,

backgroundColor:[
'#4CAF50',
'#2196F3',
'#FFC107',
'#E91E63',
'#9C27B0',
'#FF5722',
'#00BCD4',
'#8BC34A'
]

}]

},

options:{
responsive:true,
maintainAspectRatio:false
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

</body>
</html>