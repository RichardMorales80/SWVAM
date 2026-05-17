<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require __DIR__ . '/../config/seguridad.php';
require_once __DIR__ . '/../config/Conexion.php';
verificarRoles([1,3]);

if(!isset($_SESSION['id_usuario'])){
    header("Location: ../index.php");
    exit();
}
$pdo = Conexion::conectar();

$id_usuario = $_SESSION['id_usuario'];
$id_rol     = $_SESSION['id_rol'];

$tipoMenu = ($id_rol == 1) ? "admin" : "vendedor";
include("../views/navbar.php");
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Gestión de Gastos</title>
<link rel="icon" href="../public/imagenes/logo.png">
<link rel="stylesheet" href="../public/estilos/estilos.css">
<link rel="stylesheet" href="../public/estilos/principal.css">
<link rel="stylesheet" href="../public/estilos/ventas.css">
<link rel="stylesheet" href="../public/estilos/encabezado.css">
<link rel="stylesheet" href="../public/estilos/responsivo.css?v=99999">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>

<body>

<?php if(isset($_GET['ok']) && $_GET['ok'] == 1): ?>
<script>
Swal.fire({
    icon: 'success',
    title: 'Gasto guardado',
    text: 'El gasto se registró correctamente'
});
</script>
<?php endif; ?>

<?php if(isset($_GET['error']) && $_GET['error'] == 3): ?>
<script>
Swal.fire({
    icon: 'error',
    title: 'Límite de gastos excedido',
    html: `
        <b>No se puede registrar el gasto</b><br><br>
        Se ha superado el límite permitido.<br><br>
        Exceso: <b>$<?= number_format($_GET['exceso'] ?? 0,2) ?></b>
    `,
    confirmButtonText: 'Entendido'
}).then(() => {

    window.history.replaceState(
        {},
        document.title,
        window.location.pathname
    );

});
</script>
<?php endif; ?>

<?php if(isset($_GET['error']) && $_GET['error'] == 2): ?>
<script>
Swal.fire({
    icon: 'error',
    title: 'Error',
    text: 'No se pudo guardar el gasto'
});
</script>
<?php endif; ?>

<div class="topbar">
    <div class="topbar-left">
        <h4>Gestión de Gastos</h4>
    </div>

    <div class="topbar-user">
        <span class="usuario-nombre">
            <?= $_SESSION['nombre'] ?? 'Usuario' ?>
        </span>

        <img src="../public/imagenes/avatar.png" class="avatar" alt="Avatar">
    </div>
</div>

<div class="main-content">

    <div class="catalogo-container">

    
        <div class="total-gastos">
            <label>Total Gastos:</label>
            <span id="totalGastos">$0.00</span>
        </div>

        <div class="card card-form">
            <form method="post" action="/privadas/guardar_gasto.php">
                <div class="form-group">
                    <label>Concepto</label>
                    <input type="text" name="concepto" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>Total</label>
                    <input type="number" step="0.01" name="total" class="form-control" required>
                </div>

                <button type="submit" class="btn-sistema btn-guardar">
                    Guardar Gasto
                </button>
            </form>
            
            <form id="filtroForm" class="filtro-form">

                <div class="form-group">
                    <label>Buscar</label>
                    <input type="text" name="buscar" class="form-control" placeholder="Concepto">
                </div>

                <div class="form-group">
                    <label>Desde</label>
                    <input type="date" name="desde" class="form-control">
                </div>

                <div class="form-group">
                    <label>Hasta</label>
                    <input type="date" name="hasta" class="form-control">
                </div>

                <button type="submit" class="btn-sistema btn-editar">
                    Filtrar
                </button>

                <button type="button" class="btn-sistema btn-eliminar" onclick="limpiarFiltros()">
                    Limpiar
                </button>

                <?php if($id_rol == 1): ?>
                    <button type="button" class="btn-sistema btn-guardar"onclick="exportExcel()">
                        Exportar Excel
                    </button>

                    <button type="button" class="btn-sistema btn-editar" onclick="exportPDF()">
                        Exportar PDF
                    </button>
                <?php endif; ?>

            </form>
        
        
</div>
        

        

        <div class="card card-table">
            <table class="tabla-sistema">
                <thead>
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
                </tbody>
            </table>

            <div id="paginacion" class="paginacion-container"></div>
        </div>

    </div>

</div>

<script>
const idRol = <?= (int)$id_rol ?>;

function cargarGastos(pagina = 1){
    let formData = new FormData(document.getElementById("filtroForm"));
    formData.append("pagina", pagina);

    fetch("/privadas/gastos_data.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        document.getElementById("tablaGastos").innerHTML = data.tabla;
        document.getElementById("paginacion").innerHTML = data.paginacion;
        document.getElementById("totalGastos").innerText =
            '$' + parseFloat(data.total).toFixed(2);
    })
    .catch(error => {
        console.error("Error al cargar gastos:", error);
        Swal.fire({
            icon: "error",
            title: "Error",
            text: "No se pudieron cargar los gastos."
        });
    });
}

/* FILTRAR */
document.getElementById("filtroForm").addEventListener("submit", function(e){
    e.preventDefault();

    let buscar = document.querySelector('input[name="buscar"]').value.trim();
    let desde  = document.querySelector('input[name="desde"]').value.trim();
    let hasta  = document.querySelector('input[name="hasta"]').value.trim();

    if(buscar === '' && desde === '' && hasta === ''){
        Swal.fire({
            icon: 'warning',
            title: 'Filtro requerido',
            text: 'Debes agregar una fecha o un concepto de gasto para filtrar.'
        });
        return;
    }

    cargarGastos(1);
});

/* FILTRAR MIENTRAS ESCRIBE */
document.querySelector('input[name="buscar"]').addEventListener("keyup", function(){
    cargarGastos(1);
});

/* LIMPIAR FILTROS */
function limpiarFiltros(){
    document.getElementById("filtroForm").reset();
    cargarGastos(1);
}

/* ELIMINAR */
function eliminarGasto(id){

    if(idRol !== 1){
        Swal.fire({
            icon: "warning",
            title: "Acceso denegado",
            text: "No tienes permiso para eliminar gastos."
        });
        return;
    }

    Swal.fire({
        title: "¿Eliminar gasto?",
        text: "Esta acción no se puede deshacer",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Sí, eliminar",
        cancelButtonText: "Cancelar"
    }).then((result) => {

        if(result.isConfirmed){

            fetch("eliminar_gasto.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: "id=" + encodeURIComponent(id)
            })

            .then(response => response.text())

            .then(data => {

                console.log(data);

                if(data.trim() === "ok"){

                    Swal.fire({
                        icon: "success",
                        title: "Eliminado",
                        text: "El gasto fue eliminado correctamente"
                    });

                    cargarGastos(1);

                } else {

                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: data
                    });
                }
            })

            .catch(error => {

                console.error(error);

                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "No se pudo conectar con el servidor"
                });
            });
        }
    });
}

/* EXPORTAR */
function exportExcel(){
    if(idRol !== 1){
        Swal.fire({
            icon: "warning",
            title: "Acceso denegado",
            text: "No tienes permiso para exportar gastos."
        });
        return;
    }

    let buscar = document.querySelector('input[name="buscar"]').value;
    let desde = document.querySelector('input[name="desde"]').value;
    let hasta = document.querySelector('input[name="hasta"]').value;

    window.location.href =
    `/data/exportar_gastos_excel.php?buscar=${encodeURIComponent(buscar)}&desde=${encodeURIComponent(desde)}&hasta=${encodeURIComponent(hasta)}`;
}

function exportPDF(){
    if(idRol !== 1){
        Swal.fire({
            icon: "warning",
            title: "Acceso denegado",
            text: "No tienes permiso para exportar gastos."
        });
        return;
    }

    let buscar = document.querySelector('input[name="buscar"]').value;
    let desde = document.querySelector('input[name="desde"]').value;
    let hasta = document.querySelector('input[name="hasta"]').value;

    window.location.href =
    `/data/exportar_gastos_pdf.php?buscar=${encodeURIComponent(buscar)}&desde=${encodeURIComponent(desde)}&hasta=${encodeURIComponent(hasta)}`;
}

/* CARGAR AUTOMATICAMENTE AL ENTRAR */
cargarGastos(1);
</script>

<script>



/* VISTA INICIAL */
//mostrarMensajeInicial();
</script>

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
        } else {
            history.pushState(null, null, location.href);
        }
    });
});

history.pushState(null, null, location.href);
</script>
<script>
document.addEventListener("DOMContentLoaded", () => {

    let hoy = new Date().toISOString().split("T")[0];
    let hasta = document.querySelector('input[name="hasta"]');

    if(hasta){
        // Limitar calendario
        hasta.setAttribute("max", hoy);

        // Evitar que escriban fecha futura manualmente
        hasta.addEventListener("change", function(){
            if(this.value > hoy){
                this.value = hoy;
            }
        });
    }

});
</script>

</body>
</html>