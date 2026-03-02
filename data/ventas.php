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
<link rel="stylesheet" href="../public/estilos/principal.css">
<link rel="stylesheet" href="../public/estilos/encabezado.css">
<link rel="stylesheet" href="../public/estilos/ventas.css">
<title><?= $titulo ?></title>
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
            <a href="../views/administrador.php" class="main_menu_link">Atrâs</a>
        </li>
    </ul>
</nav>

<br>

<h2><?= $titulo ?></h2>
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

        <div class="campo boton">
            <button type="submit">Filtrar</button>
        </div>
        <div class="campo boton">
    <button type="button" class="btn-limpiar" onclick="limpiarFiltros()">
        Limpiar
    </button>
</div>

    </form>
</div>

<div id="tablaVentas"></div>
<div id="paginacion"></div>

<script>
let paginaActual = 1;

function cargarVentas(pagina = 1) {

    paginaActual = pagina;

    let formData = new FormData(document.getElementById("filtroForm"));
    formData.append("pagina", pagina);

    fetch("ventas_data.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        document.getElementById("tablaVentas").innerHTML = data.tabla;
        document.getElementById("paginacion").innerHTML = data.paginacion;
    });
}

document.getElementById("filtroForm")
.addEventListener("submit", function(e){
    e.preventDefault();
    cargarVentas(1);
});

document.addEventListener("keyup", function(e){
    if(e.target.name === "buscar"){
        cargarVentas(1);
    }
});
function limpiarFiltros() {
    document.getElementById("filtroForm").reset();
    cargarVentas(1);
}

cargarVentas();
</script>


</body>
</html>