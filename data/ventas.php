<?php
session_start();

require __DIR__ . '/../config/seguridad.php';
require_once __DIR__ . '/../config/Conexion.php';

verificarRoles([1,3]);

$tipoMenu = "admin";
include("../views/navbar.php");

$id_rol = $_SESSION['id_rol'];
$titulo = ($id_rol == 1) ? "Ventas Generales" : "Mis Compras";
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title><?= $titulo ?></title>

<link rel="stylesheet" href="../public/estilos/estilos.css">
<link rel="stylesheet" href="../public/estilos/principal.css">
<link rel="stylesheet" href="../public/estilos/ventas.css">
<link rel="stylesheet" href="../public/estilos/encabezado.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>

<!-- ================= TOPBAR ================= -->

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

<h2><?= $titulo ?></h2>

<!-- ================= FILTROS ================= -->

<div class="card">

<form id="filtroForm">

<div class="form-group">
<label>Buscar</label>
<input type="text" name="buscar" class="form-control" placeholder="Descripción o producto">
</div>

<div class="form-group">
<label>Desde</label>
<input type="date" name="inicio" class="form-control">
</div>

<div class="form-group">
<label>Hasta</label>
<input type="date" name="fin" class="form-control">
</div>

<button type="submit" class="btn-sistema btn-editar">
Filtrar
</button>

<button type="button" class="btn-sistema" onclick="limpiarFiltros()">
Limpiar
</button>

<button type="button" class="btn-sistema btn-guardar" onclick="exportExcel()">
Exportar Excel
</button>

<button type="button" class="btn-sistema btn-eliminar" onclick="exportPDF()">
Exportar PDF
</button>

</form>

</div>

<!-- ================= TOTAL ================= -->

<div class="card">

<div class="total-gastos">

<label>Gran Total:</label>
<span id="granTotalVentas">$0.00</span>

</div>

</div>

</div>

</div>
<!-- ================= TABLA ================= -->

<div class="card card-table">

<table class="tabla-sistema">

<thead>

<tr>
<th>ID</th>
<th>Usuario</th>
<th>Producto</th>
<th>Fecha</th>
<th>Total</th>
</tr>

</thead>

<tbody id="tablaVentas">
</tbody>

</table>

<div id="paginacion" class="paginacion-container"></div>

</div>





<!-- ================= SCRIPT ================= -->

<script>

let paginaActual = 1;

function cargarVentas(pagina = 1){

paginaActual = pagina;

let formData = new FormData(document.getElementById("filtroForm"));
formData.append("pagina", pagina);

fetch("../data/ventas_data.php",{
method:"POST",
body:formData
})

.then(res=>res.json())

.then(data=>{

document.getElementById("tablaVentas").innerHTML = data.tabla;
document.getElementById("paginacion").innerHTML = data.paginacion;

const totalSpan = document.getElementById("granTotalVentas");

if(totalSpan && data.gran_total !== undefined){

totalSpan.textContent = "$" + parseFloat(data.gran_total)
.toLocaleString('es-MX',{minimumFractionDigits:2});

}

});

}

/* FILTRAR */

document.getElementById("filtroForm")
.addEventListener("submit",function(e){

e.preventDefault();
cargarVentas(1);

});

/* FILTRO EN TIEMPO REAL */

document.addEventListener("keyup",function(e){

if(e.target.name==="buscar"){
cargarVentas(1);
}

});

/* LIMPIAR */

function limpiarFiltros(){

document.getElementById("filtroForm").reset();
cargarVentas(1);

}

/* EXPORTAR */

function exportExcel(){

let buscar=document.querySelector('input[name="buscar"]').value;
let inicio=document.querySelector('input[name="inicio"]').value;
let fin=document.querySelector('input[name="fin"]').value;

window.location.href=`../privadas/exportar_ventas_excel.php?buscar=${buscar}&inicio=${inicio}&fin=${fin}`;

}

function exportPDF(){

let buscar=document.querySelector('input[name="buscar"]').value;
let inicio=document.querySelector('input[name="inicio"]').value;
let fin=document.querySelector('input[name="fin"]').value;

window.location.href=`../privadas/exportar_ventas_pdf.php?buscar=${buscar}&inicio=${inicio}&fin=${fin}`;

}

cargarVentas();

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