<?php
require_once '../config/Conexion.php';
require __DIR__ . '/../config/seguridad.php';

verificarRoles([1,3]);

if(!isset($_SESSION['id_usuario'])){
    header("Location: ../index.php");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];
$id_rol     = $_SESSION['id_rol'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Gestión de Gastos</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<link rel="stylesheet" href="../public/estilos/principal.css">
<link rel="stylesheet" href="../public/estilos/ventas.css">
<link rel="stylesheet" href="/SWVAM/public/estilos/encabezado.css">

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

<h3 class="mb-4">Gestión de Gastos</h3>

<!-- ================= FORM REGISTRO ================= -->
<form method="post" action="/SWVAM/privadas/guardar_gasto.php" class="row g-3 mb-4">

    <div class="col-md-5">
        <input type="text"
               name="concepto"
               class="form-control"
               placeholder="Concepto del gasto"
               required>
    </div>

    <div class="col-md-3">
        <input type="number"
               step="0.01"
               name="total"
               class="form-control"
               placeholder="Total"
               required>
    </div>

    <div class="col-md-2 d-grid">
        <button type="submit"
                class="btn btn-primary">
            Guardar
        </button>
    </div>

</form>

<!-- ================= FILTROS ================= -->
<form id="filtroForm" class="filtro-form">

        <div class="campo">
            <label>Buscar</label>
            <input type="text" name="buscar" placeholder="Concepto">
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
<!-- ================= TABLA ================= -->
<div class="card p-3">
    <table class="table-pro">
        <thead class="table-dark">
        <tr>
            <th>ID</th>
            <th>Usuario</th>
            <th>Concepto</th>
            <th>Fecha</th>
            <th>Total</th>
            <?php if($id_rol == 1): ?>
            <th>Acciones</th>
            <?php endif; ?>
        </tr>
        </thead>
        <tbody id="tablaGastos">
        <!-- AJAX -->
        </tbody>
    </table>

    <div id="paginacion" class="text-center mt-3"></div>
</div>

</div>

<!-- ================= SCRIPT AJAX ================= -->
<script>
function cargarGastos(pagina = 1){

    let formData = new FormData(document.getElementById("filtroForm"));
    formData.append("pagina", pagina);

    fetch("/SWVAM/privadas/gastos_data.php", {
        method:"POST",
        body:formData
    })
    .then(res=>res.json())
    .then(data=>{
        document.getElementById("tablaGastos").innerHTML = data.tabla;
        document.getElementById("paginacion").innerHTML = data.paginacion;
    });
}

// Filtrar al enviar formulario
document.getElementById("filtroForm")
.addEventListener("submit", function(e){
    e.preventDefault();
    cargarGastos(1);
});

// Filtrar al escribir en buscar
document.addEventListener("keyup", function(e){
    if(e.target.name === "buscar"){
        cargarGastos(1);
    }
});

// Función eliminar gasto
function eliminarGasto(id){
    Swal.fire({
        title:"¿Eliminar gasto?",
        icon:"warning",
        showCancelButton:true,
        confirmButtonText:"Sí, eliminar"
    }).then(result=>{
        if(result.isConfirmed){
            fetch("/SWVAM/privadas/eliminar_gasto.php",{
                method:"POST",
                headers:{'Content-Type':'application/x-www-form-urlencoded'},
                body:"id="+id
            }).then(()=>{
                cargarGastos();
            });
        }
    });
}

// Función limpiar filtros
function limpiarFiltros(){
    document.querySelector('input[name="buscar"]').value = '';
    document.querySelector('input[name="desde"]').value = '';
    document.querySelector('input[name="hasta"]').value = '';
    cargarGastos(1);
}

// Cargar gastos al cargar página
cargarGastos();
</script>

</body>
</html>