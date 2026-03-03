<?php
require_once __DIR__ . '/../config/Conexion.php';
require __DIR__ . '/../config/seguridad.php';
verificarRol(1);

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
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Reporte Financiero</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="../public/estilos/encabezado.css">

<style>
body{background:#f4f6f9; padding:20px;}
.card-hover:hover{transform:scale(1.03); transition:0.3s;}
#contenedorMasVendidos{display:none;}
</style>
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
<div class="container-fluid">

<h1 class="text-center mb-4">Dashboard Financiero</h1>

<!-- FILTROS -->
<form method="GET" class="row g-2 mb-4">
<div class="col-md-3">
<input type="date" name="fecha_inicio" class="form-control" value="<?= $fecha_inicio ?>">
</div>
<div class="col-md-3">
<input type="date" name="fecha_fin" class="form-control" value="<?= $fecha_fin ?>">
</div>
<div class="col-md-6 d-flex gap-2">
<button class="btn btn-primary">Filtrar</button>
<a href="reporte.php" class="btn btn-secondary">Limpiar</a>
</div>
<div class="mb-4 d-flex gap-2">

    <a href="reporte_pdf.php?fecha_inicio=<?= $fecha_inicio ?>&fecha_fin=<?= $fecha_fin ?>" 
       class="btn btn-danger">
       Descargar PDF
    </a>

    <a href="reporte_excel.php?fecha_inicio=<?= $fecha_inicio ?>&fecha_fin=<?= $fecha_fin ?>" 
       class="btn btn-success">
       Descargar Excel
    </a>

</div>
</form>

<!-- RESUMEN -->
<div class="row text-center mb-4">

<div class="col-md-4">
<div class="card alert-success fw-bold card-hover p-3">
<h5>Ventas</h5>
<p class="fs-4">$<?= number_format($totalVentas,2) ?></p>
</div>
</div>

<div class="col-md-4">
<div class="card alert-danger fw-bold card-hover p-3">
<h5>Gastos</h5>
<p class="fs-4">$<?= number_format($totalGastos,2) ?></p>
</div>
</div>

<div class="col-md-4">
<div class="card <?= $balanceTotal>=0?'alert-primary':'alert-warning' ?> fw-bold card-hover p-3">
<h5>Balance</h5>
<p class="fs-4">$<?= number_format($balanceTotal,2) ?></p>
</div>
</div>

</div>

<!-- GRAFICA GENERAL -->
<div class="card p-4 shadow mb-4">
<canvas id="graficaGeneral"></canvas>
</div>

<!-- BOTON MÁS VENDIDOS -->
<div class="mb-3">
<button class="btn btn-info" id="btnMasVendidos">
Mostrar Productos Más Vendidos
</button>
</div>

<!-- CONTENEDOR MÁS VENDIDOS -->
<div id="contenedorMasVendidos" class="card shadow p-3 mb-4">

<h4 class="mb-3 text-center fw-bold">
Productos Más Vendidos
</h4>

<table id="tablaMasVendidos" class="table table-striped table-bordered">
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

<div class="card p-4 shadow mt-4">
<canvas id="graficaMasVendidos"></canvas>
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
            backgroundColor:['#9adba9','#e19ca3','#80a7e5']
        }]
    },
    options:{
        responsive:true,
        plugins:{legend:{display:false}},
        scales:{
            y:{
                beginAtZero:true,
                ticks:{
                    callback:function(value){
                        return '$'+value.toLocaleString();
                    }
                }
            }
        }
    }
});

/* =========================
   MÁS VENDIDOS
========================= */

$('#btnMasVendidos').click(function(){

    $.ajax({
        url: 'mas_vendidos.php',
        type: 'GET',
        data: {
            fecha_inicio: '<?= $fecha_inicio ?>',
            fecha_fin: '<?= $fecha_fin ?>'
        },
        dataType: 'json',

        success: function(res){

            if(res.productos && res.productos.length > 0){

                let tbody = '';
                let labels = [];
                let cantidades = [];

                res.productos.forEach(p => {

                    tbody += `<tr>
                        <td>${p.nombre}</td>
                        <td>${p.cantidad}</td>
                        <td>$${parseFloat(p.precio).toFixed(2)}</td>
                        <td>$${parseFloat(p.total).toFixed(2)}</td>
                    </tr>`;

                    labels.push(p.nombre);
                    cantidades.push(p.cantidad);
                });

                $('#tablaMasVendidos tbody').html(tbody);
                $('#granTotalProductos')
                    .text('$' + parseFloat(res.granTotal).toFixed(2));

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
                                backgroundColor:'#72a8b0'
                            }]
                        },
                        options:{
                            responsive:true,
                            plugins:{legend:{display:false}},
                            scales:{y:{beginAtZero:true}}
                        }
                    }
                );

            } else {
                Swal.fire('Aviso','No hay productos vendidos','info');
            }
        },

        error: function(){
            Swal.fire('Error','No se pudieron cargar los productos','error');
        }

    });

});

});
</script>

</body>
</html>
