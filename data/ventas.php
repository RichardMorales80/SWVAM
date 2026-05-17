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
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $titulo ?></title>
<link rel="stylesheet" href="/public/estilos/estilos.css">
<link rel="stylesheet" href="/public/estilos/principal.css">
<link rel="stylesheet" href="/public/estilos/ventas.css">
<link rel="stylesheet" href="<?= BASE_URL ?>public/estilos/responsivo.css?v=99999">
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

<div class="main-content">

<br><br><br><br>

<!-- ================= FILTROS ================= -->

<div class="filtro-container">

<form id="filtroForm" class="filtro-form">

<div class="campo">
<label>Buscar</label>
<input type="text" name="buscar" placeholder="Producto">
</div>

<div class="campo">
<label>Desde</label>
<input type="date" name="inicio">
</div>

<div class="campo">
<label>Hasta</label>
<input type="date" name="fin">
</div>

<div class="campo boton">
<button class="btn-sistema btn-editar" type="submit">Filtrar</button>
</div>

<div class="campo boton">
<button type="button" class="btn-sistema btn-eliminar"  onclick="limpiarFiltros()">Limpiar</button>
</div>

<div class="campo boton">
<button type="button"  class="btn-sistema btn-guardar" onclick="exportExcel()">Excel</button>
</div>

<div class="campo boton">
<button type="button" class="btn-sistema btn-editar" onclick="exportPDF()">PDF</button>
</div>

</form>

</div>

<!-- ================= TOTAL ================= -->

<div class="card total-gastos">
<label>Gran Total:</label>
<span id="granTotalVentas">$0.00</span>
</div>

<div class="card">

<div style="overflow-x:auto;">

<table class="table-pro">
<thead>
<tr>
    <th>ID</th>
    <th>Producto</th>
    <th>Descripción</th>
    <th>Precio</th>
    <th>Cantidad</th>
    <th>Total</th>
    <th>Fecha</th>
    <th>Estado</th>
    <th>Acción</th>
</tr>
</thead>

<tbody id="tablaVentas"></tbody>

</table>

</div>

<div id="paginacion"></div>

</div>
<!-- ================= SCRIPT ================= -->

<script>

function cargarVentas(pagina = 1){

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

        document.getElementById("granTotalVentas").textContent =
        "$" + parseFloat(data.gran_total).toLocaleString('es-MX',{minimumFractionDigits:2});

    });

}


// ===== VALIDACIÓN DE FECHAS =====
document.addEventListener("DOMContentLoaded", () => {

    let hoy = new Date().toISOString().split("T")[0];

    let inicio = document.querySelector('input[name="inicio"]');
    let fin = document.querySelector('input[name="fin"]');

    // Limitar fechas futuras
    if(inicio) inicio.setAttribute("max", hoy);
    if(fin) fin.setAttribute("max", hoy);

    // Evitar fechas manuales futuras
    function validarFechas(){

        if(inicio.value > hoy){
            inicio.value = hoy;
        }

        if(fin.value > hoy){
            fin.value = hoy;
        }

        // Evitar que fin sea menor que inicio
        if(fin.value && inicio.value && fin.value < inicio.value){
            fin.value = inicio.value;
        }
    }

    inicio?.addEventListener("change", validarFechas);
    fin?.addEventListener("change", validarFechas);

});


// ===== FILTRO =====
document.getElementById("filtroForm").addEventListener("submit",function(e){
    e.preventDefault();
    cargarVentas();
});


// ===== LIMPIAR =====
function limpiarFiltros(){
    document.getElementById("filtroForm").reset();
    cargarVentas();
}


// ===== EXPORTAR =====
function exportExcel(){
    window.location.href="../privadas/exportar_ventas_excel.php";
}

function exportPDF(){
    window.location.href="../privadas/exportar_ventas_pdf.php";
}


// ===== INICIAL =====
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