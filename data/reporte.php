<?php
session_start();
require_once '../config/Conexion.php';

$pdo = Conexion::conectar();

if(!isset($_SESSION['id_usuario'])){
    header("Location: ../views/login.php");
    exit();
}

$fecha_inicio = $_GET['fecha_inicio'] ?? '';
$fecha_fin    = $_GET['fecha_fin'] ?? '';

$condicion = "";
$params = [];

if($fecha_inicio && $fecha_fin){
    $condicion = " WHERE fecha BETWEEN :inicio AND :fin ";
    $params[':inicio'] = $fecha_inicio;
    $params[':fin'] = $fecha_fin;
}

/* =====================
   TOTALES GENERALES
===================== */

$sqlVentas = "SELECT COALESCE(SUM(total),0) FROM ventas $condicion";
$stmtV = $pdo->prepare($sqlVentas);
$stmtV->execute($params);
$totalVentas = $stmtV->fetchColumn();

$sqlGastos = "SELECT COALESCE(SUM(total),0) FROM gastos $condicion";
$stmtG = $pdo->prepare($sqlGastos);
$stmtG->execute($params);
$totalGastos = $stmtG->fetchColumn();

$balanceTotal = $totalVentas - $totalGastos;

/* =====================
   TABLA POR USUARIO
===================== */

$sqlTabla = "
SELECT 
    u.primer_nombre,
    u.primer_apellido,

    COALESCE(SUM(v.total),0) AS ventas,
    COALESCE(SUM(g.total),0) AS gastos,

    (COALESCE(SUM(v.total),0) - COALESCE(SUM(g.total),0)) AS balance

FROM usuarios u

LEFT JOIN ventas v 
    ON u.id_usuario = v.id_usuario
    ".($fecha_inicio && $fecha_fin ? " AND v.fecha BETWEEN :inicio AND :fin ":"")."

LEFT JOIN gastos g 
    ON u.id_usuario = g.id_usuario
    ".($fecha_inicio && $fecha_fin ? " AND g.fecha BETWEEN :inicio AND :fin ":"")."

GROUP BY u.id_usuario
ORDER BY ventas DESC
";

$stmtT = $pdo->prepare($sqlTabla);
$stmtT->execute($params);
$tabla = $stmtT->fetchAll(PDO::FETCH_ASSOC);

/* =====================
   SIN DATOS
===================== */

$sinDatos = false;

if($fecha_inicio && $fecha_fin){
    if($totalVentas == 0 && $totalGastos == 0){
        $sinDatos = true;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Reporte Financiero</title>

    <link rel="stylesheet" href="../public/estilos/estilos.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
body{
    background:#f4f6f9;
    padding:40px;
}
</style>
</head>

<body>
<header class="header">
    <div class="container">
        
        <div class="logo">
            <h1>Reporte Financiero</h1>
        </div>
        <nav class="menu">
            <a href="../public/index.html">Inicio</a>
            <a href="../config/cerrar_sesion.php">Salir</a>
             <a href="../views/administrador.php">Atras</a>
        </nav>
    </div>
</header>
    



<br><br><br><br><br
    


    


<h2 class="text-center fw-bold mb-4">Reporte de Ventas y Gastos</h2>

<form method="GET" class="row mb-3">

<div class="col-md-4">
<input type="date" name="fecha_inicio" class="form-control" value="<?= $fecha_inicio ?>">
</div>

<div class="col-md-4">
<input type="date" name="fecha_fin" class="form-control" value="<?= $fecha_fin ?>">
</div>

<div class="col-md-4 d-flex gap-2">
<button class="btn btn-primary">Filtrar</button>
<a href="reporte.php" class="btn btn-secondary">Limpiar</a>
</div>

</form>

<a href="exportar_pdf.php?fecha_inicio=<?= $fecha_inicio ?>&fecha_fin=<?= $fecha_fin ?>" 
class="btn btn-danger mb-3">
Exportar a PDF
</a>

<?php if($sinDatos): ?>
<div class="alert alert-warning text-center fw-bold">
No hay información en este rango de fechas
</div>
<?php endif; ?>

<div class="row text-center mb-4">

<div class="col-md-4">
<div class="alert alert-success fw-bold">
Ventas <br>
$<?= number_format($totalVentas,2) ?>
</div>
</div>

<div class="col-md-4">
<div class="alert alert-danger fw-bold">
Gastos <br>
$<?= number_format($totalGastos,2) ?>
</div>
</div>

<div class="col-md-4">
<div class="alert <?= $balanceTotal >= 0 ? 'alert-primary':'alert-warning' ?> fw-bold">
Balance <br>
$<?= number_format($balanceTotal,2) ?>
</div>
</div>

</div>

<div class="card p-4 shadow mb-4">
<canvas id="grafica"></canvas>
</div>

<div class="card shadow p-3">

<h4 class="mb-3 text-center fw-bold">Detalle por Usuario</h4>

<table class="table table-bordered table-striped">

<thead class="table-dark">
<tr>
<th>Usuario</th>
<th>Ventas</th>
<th>Gastos</th>
<th>Balance</th>
</tr>
</thead>

<tbody>

<?php if(count($tabla)==0): ?>

<tr>
<td colspan="4" class="text-center">No hay registros</td>
</tr>

<?php endif; ?>

<?php foreach($tabla as $fila): ?>

<tr>
<td><?= $fila['primer_nombre'].' '.$fila['primer_apellido'] ?></td>
<td>$<?= number_format($fila['ventas'],2) ?></td>
<td>$<?= number_format($fila['gastos'],2) ?></td>

<td class="<?= $fila['balance']>=0?'text-success':'text-danger' ?>">
$<?= number_format($fila['balance'],2) ?>
</td>
</tr>

<?php endforeach; ?>

</tbody>
</table>

</div>

<script>

new Chart(document.getElementById('grafica'),{
    type:'bar',
    data:{
        labels:['Ventas','Gastos','Balance'],
        datasets:[{
            label:'Monto en $',
            data:[
                <?= $totalVentas ?>,
                <?= $totalGastos ?>,
                <?= $balanceTotal ?>
            ],
            backgroundColor:['#198754','#dc3545','#0d6efd']
        }]
    },
    options:{
        responsive:true,
        scales:{
            y:{
                beginAtZero:true,
                ticks:{
                    callback:(v)=>'$'+v.toLocaleString()
                }
            }
        }
    }
});

</script>

</body>
</html>
