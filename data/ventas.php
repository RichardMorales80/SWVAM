<?php
require __DIR__ . '/../config/seguridad.php';
verificarRoles([1,3]);

$id_rol = $_SESSION['id_rol'];
$titulo = ($id_rol == 1) ? "Ventas Generales" : "Mis Compras";
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title><?= $titulo ?></title>

<link rel="stylesheet" href="../public/estilos/principal.css">
<link rel="stylesheet" href="../public/estilos/encabezado.css">
<link rel="stylesheet" href="../public/estilos/ventas.css">

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>

<!-- NAV -->
<nav class="main_nav">
    <div class="menu_toggle" id="menuToggle">☰</div>
    <ul class="menu" id="menu">
        <li class="logo-item">
            <a href="#"><img src="../public/imagenes/logo.png" class="logo" alt="logo"></a>
        </li>
        <li>
            <a href="../views/administrador.php" class="main_menu_link">Atrás</a>
        </li>
    </ul>
</nav>

<div class="dashboard-container">

<h2><?= $titulo ?></h2>

<!-- ================= FILTROS ================= -->
<div class="filtro-container">
<form id="filtroForm" class="filtro-form">

    <div class="campo">
        <label>Buscar</label>
        <input type="text" name="buscar" placeholder="Descripción o producto">
    </div>

    <div class="campo">
        <label>Desde</label>
        <input type="date" name="inicio">
    </div>

    <div class="campo">
        <label>Hasta</label>
        <input type="date" name="fin">
    </div>

    <!-- Botones Filtrar + Limpiar -->
    <div class="campo boton d-flex gap-2 align-items-end">
        <button type="submit" class="btn-filtrar">Filtrar</button>
        <button type="button" class="btn-limpiar" onclick="limpiarFiltros()">Limpiar</button>
    </div>

    <!-- Botones Exportar Excel y PDF -->
    <div class="campo boton d-flex gap-2 align-items-end mt-2">
        <button type="button" class="btn btn-success" onclick="exportExcel()">Exportar Excel</button>
        <button type="button" class="btn btn-danger" onclick="exportPDF()">Exportar PDF</button>
    </div>

</form>
</div>

<!-- ================= TABLA ================= -->
<div id="tablaVentas"></div>

<!-- ================= TOTAL ================= -->
<div class="total-gastos">
    <label>Gran Total:</label>
    <span id="granTotalVentas">$0.00</span>
</div>

<!-- ================= PAGINACION ================= -->
<div id="paginacion"></div>

</div> <!-- fin dashboard -->

<!-- ================= SCRIPT AJAX Y EXPORT ================= -->
<script>
let paginaActual = 1;

// Cargar ventas con filtros y paginación
function cargarVentas(pagina = 1) {
    paginaActual = pagina;

    let formData = new FormData(document.getElementById("filtroForm"));
    formData.append("pagina", pagina);

    fetch("../data/ventas_data.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        document.getElementById("tablaVentas").innerHTML = data.tabla;
        document.getElementById("paginacion").innerHTML = data.paginacion;

        // Actualizar gran total
        const totalSpan = document.getElementById("granTotalVentas");
        if(totalSpan && data.gran_total !== undefined){
            totalSpan.textContent = "$" + parseFloat(data.gran_total).toLocaleString('es-MX', {minimumFractionDigits:2});
        }
    });
}

// Filtrar
document.getElementById("filtroForm").addEventListener("submit", function(e){
    e.preventDefault();
    cargarVentas(1);
});

// Filtrar mientras escribe
document.addEventListener("keyup", function(e){
    if(e.target.name === "buscar"){
        cargarVentas(1);
    }
});

// Limpiar filtros
function limpiarFiltros() {
    document.getElementById("filtroForm").reset();
    cargarVentas(1);
}

// ================= EXPORTAR EXCEL =================
function exportExcel(){
    let buscar = document.querySelector('input[name="buscar"]').value;
    let inicio = document.querySelector('input[name="inicio"]').value;
    let fin    = document.querySelector('input[name="fin"]').value;

    let url = `../privadas/exportar_ventas_excel.php?buscar=${buscar}&inicio=${inicio}&fin=${fin}`;
    window.location.href = url;
}

// ================= EXPORTAR PDF =================
function exportPDF(){
    let buscar = document.querySelector('input[name="buscar"]').value;
    let inicio = document.querySelector('input[name="inicio"]').value;
    let fin    = document.querySelector('input[name="fin"]').value;

    let url = `../privadas/exportar_ventas_pdf.php?buscar=${buscar}&inicio=${inicio}&fin=${fin}`;
    window.location.href = url;
}

// Cargar al inicio
cargarVentas();
</script>

</body>
</html>