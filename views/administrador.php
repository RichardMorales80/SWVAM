<?php
session_start();

require __DIR__ . '/../config/seguridad.php';
require_once __DIR__ . '/../config/Conexion.php';
require_once __DIR__ . '/../config/bitacora.php';

verificarRol(1);

if(!isset($_SESSION['id_usuario'])){
    header("Location: ../index.php");
    exit();
}

$tipoMenu = "admin";
include("../views/navbar.php");

$pdo = Conexion::conectar();

/* =========================
   PAGINACION BITACORA
========================= */

$porPagina = 5;
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;

if($pagina < 1){
    $pagina = 1;
}

$offset = ($pagina - 1) * $porPagina;

$totalRegistros = $pdo->query("SELECT COUNT(*) FROM bitacora")->fetchColumn();
$totalPaginas = ceil($totalRegistros / $porPagina);

$sqlBitacora = "SELECT 
                    b.id_bitacora,
                    b.id_usuario,
                    b.accion,
                    b.fecha,
                    CONCAT(
                        COALESCE(u.primer_nombre,''),' ',
                        COALESCE(u.primer_apellido,'')
                    ) AS nombre_usuario
                FROM bitacora b
                LEFT JOIN usuarios u ON u.id_usuario = b.id_usuario
                ORDER BY b.fecha DESC, b.id_bitacora DESC
                LIMIT $porPagina OFFSET $offset";

$stmtBitacora = $pdo->prepare($sqlBitacora);
$stmtBitacora->execute();
$bitacora = $stmtBitacora->fetchAll(PDO::FETCH_ASSOC);

/* =========================
   CONTADORES
========================= */

$loginsHoy = $pdo->query("
    SELECT COUNT(*) 
    FROM bitacora 
    WHERE accion LIKE '%Inició sesión%' 
    AND DATE(fecha) = CURDATE()
")->fetchColumn();

$totalAccesos = $pdo->query("
    SELECT COUNT(*) 
    FROM bitacora 
    WHERE accion LIKE '%Inició sesión%'
")->fetchColumn();

/* =========================
   DATOS FINANZAS
========================= */

$totalVentas = $pdo->query("SELECT COALESCE(SUM(total),0) FROM ventas")->fetchColumn();
$totalGastos = $pdo->query("SELECT COALESCE(SUM(total),0) FROM gastos")->fetchColumn();
$balance = $totalVentas - $totalGastos;

$nombre = htmlspecialchars($_SESSION['nombre'] ?? 'Administrador');

/* =========================
   PRODUCTOS MAS VENDIDOS
========================= */

$sqlProductos = "SELECT 
                    descripcion AS nombre, 
                    SUM(cantidad) AS cantidad
                 FROM ventas
                 GROUP BY descripcion
                 ORDER BY cantidad DESC";

$stmt = $pdo->prepare($sqlProductos);
$stmt->execute();
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$labelsProductos = [];
$datosProductos = [];

foreach($productos as $p){
    $labelsProductos[] = $p['nombre'];
    $datosProductos[] = (float)$p['cantidad'];
}

if(count($labelsProductos) === 0){
    $labelsProductos = ['Sin ventas'];
    $datosProductos = [0];
}

/* =========================
   GASTOS POR USUARIO
========================= */

$sqlGastosUsuario = "SELECT 
                        CONCAT(u.primer_nombre,' ',u.primer_apellido) AS nombre,
                        COALESCE(SUM(g.total),0) AS total
                     FROM usuarios u
                     LEFT JOIN gastos g ON g.id_usuario = u.id_usuario
                     WHERE u.id_rol IN (1,3)
                     GROUP BY u.id_usuario
                     ORDER BY total DESC";

$stmt = $pdo->prepare($sqlGastosUsuario);
$stmt->execute();
$gastos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$labelsGastos = [];
$datosGastos = [];

foreach($gastos as $g){
    $labelsGastos[] = $g['nombre'];
    $datosGastos[] = (float)$g['total'];
}

if(count($labelsGastos) == 0){
    $labelsGastos = ['Sin datos'];
    $datosGastos = [0];
}

/* =========================
   VENTAS POR CLIENTE
========================= */

$sqlVentasCliente = "SELECT 
                        CONCAT(u.primer_nombre,' ',u.primer_apellido) AS cliente,
                        COALESCE(SUM(v.total),0) AS total
                     FROM usuarios u
                     LEFT JOIN ventas v ON v.id_usuario = u.id_usuario
                     WHERE u.id_rol = 2
                     GROUP BY u.id_usuario
                     ORDER BY total DESC";

$stmt = $pdo->prepare($sqlVentasCliente);
$stmt->execute();
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$labelsClientes = [];
$datosClientes = [];

foreach($clientes as $c){
    $labelsClientes[] = $c['cliente'];
    $datosClientes[] = (float)$c['total'];
}

if(count($labelsClientes) == 0){
    $labelsClientes = ['Sin clientes'];
    $datosClientes = [0];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Panel Administrador</title>

<link rel="stylesheet" href="../public/estilos/estilos.css">
<link rel="stylesheet" href="../public/estilos/encabezado.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>

<div class="topbar">
    <div class="topbar-left">
        <h2>Panel de Administración</h2>
    </div>

    <div class="topbar-user">
        <span class="usuario-nombre"><?= $nombre ?></span>
        <img src="../public/imagenes/avatar.png" class="avatar" alt="Avatar">
    </div>
</div>

<div class="main-content">
    <div class="catalogo-container">

        <h3 style="margin-bottom:20px;">Resumen del sistema</h3>

        <!-- ================= CARDS ================= -->

        <div class="row" style="gap:20px;margin-bottom:40px;">

            <div class="col-md-3">
                <div class="card text-center">
                    <h5>Ventas Totales</h5>
                    <p class="estado-activo">$<?= number_format($totalVentas,2) ?></p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card text-center">
                    <h5>Gastos Totales</h5>
                    <p class="estado-inactivo">$<?= number_format($totalGastos,2) ?></p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card text-center">
                    <h5>Balance</h5>
                    <p>$<?= number_format($balance,2) ?></p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card text-center">
                    <h5>Logins Hoy</h5>
                    <p><?= $loginsHoy ?></p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card text-center">
                    <h5>Total Accesos</h5>
                    <p><?= $totalAccesos ?></p>
                </div>
            </div>

        </div>

        <!-- ================= GRAFICAS ================= -->

        <div class="row graficas-row" style="gap:20px;flex-wrap:wrap;">

            <div class="col-md-6">
                <div class="card card-table">
                    <h5 style="text-align:center;">Finanzas</h5>
                    <canvas id="graficaFinanzas"></canvas>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card card-table">
                    <h5 style="text-align:center;">Productos más vendidos</h5>
                    <canvas id="graficaProductos"></canvas>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card card-table">
                    <h5 style="text-align:center;">Gastos por usuario</h5>
                    <canvas id="graficaGastos"></canvas>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card card-table">
                    <h5 style="text-align:center;">Ventas por cliente</h5>
                    <canvas id="graficaClientes"></canvas>
                </div>
            </div>

        </div>

        <!-- ================= BITACORA ================= -->

        <div class="card card-table" style="margin-top:40px;">

    <h5 style="text-align:center;">Actividad reciente</h5>

    <table class="tabla-sistema">
        <thead>
            <tr>
                <th>ID</th>
                <th>Usuario</th>
                <th>Acción</th>
                <th>Fecha</th>
            </tr>
        </thead>

        <tbody>
            <?php if(!empty($bitacora)): ?>
                <?php foreach($bitacora as $b): ?>

                    <?php
                    /* =========================
                       FORMATEAR ACCION
                    ========================= */

                    $accion = $b['accion'];

                    $partes = explode('|', $accion);

                    $accionTexto = trim($partes[0]);
                    $tiempoTexto = isset($partes[1]) ? trim($partes[1]) : '';

                    /* =========================
                       COLOR SEGUN ACCION
                    ========================= */

                    $color = '#333';

                    if(str_contains($accionTexto, 'Inició')){
                        $color = 'green';
                    }elseif(str_contains($accionTexto, 'inactividad')){
                        $color = 'orange';
                    }elseif(str_contains($accionTexto, 'Cerró')){
                        $color = 'red';
                    }
                    ?>

                    <tr>

                        <td><?= $b['id_bitacora'] ?></td>

                        <td>
                            <?php
                            $nombreBit = trim($b['nombre_usuario']);
                            echo $nombreBit !== '' ? htmlspecialchars($nombreBit) : 'Usuario '.$b['id_usuario'];
                            ?>
                        </td>

                        <td style="color:<?= $color ?>;">
                            <strong><?= htmlspecialchars($accionTexto) ?></strong>

                            <?php if($tiempoTexto): ?>
                                <br>
                                <small style="color:#777;">
                                    <?= htmlspecialchars($tiempoTexto) ?>
                                </small>
                            <?php endif; ?>
                        </td>

                        <td>
                            <?= date("d/m/Y H:i:s", strtotime($b['fecha'])) ?>
                        </td>

                    </tr>

                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" style="text-align:center;">
                        No hay actividad registrada
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div style="text-align:center; margin-top:15px;">

        <?php if($pagina > 1): ?>
            <a href="?pagina=<?= $pagina-1 ?>" class="btn-sistema">Anterior</a>
        <?php endif; ?>

        <span style="margin:0 10px;">
            Página <?= $pagina ?> de <?= max($totalPaginas, 1) ?>
        </span>

        <?php if($pagina < $totalPaginas): ?>
            <a href="?pagina=<?= $pagina+1 ?>" class="btn-sistema">Siguiente</a>
        <?php endif; ?>

    </div>

</div>

    </div>
</div>

<script>
new Chart(document.getElementById('graficaFinanzas'), {
    type:'bar',
    data:{
        labels:['Ventas','Gastos','Balance'],
        datasets:[{
            data:[
                <?= $totalVentas ?>,
                <?= $totalGastos ?>,
                <?= $balance ?>
            ],
            backgroundColor:['#97e19a','#ecedd7','#979cbd']
        }]
    },
    options:{
        responsive:true,
        plugins:{
            legend:{display:false}
        }
    }
});

new Chart(document.getElementById('graficaProductos'), {
    type:'bar',
    data:{
        labels:<?= json_encode($labelsProductos) ?>,
        datasets:[{
            data:<?= json_encode($datosProductos) ?>,
            backgroundColor:'#7fa3e6'
        }]
    },
    options:{
        responsive:true,
        plugins:{
            legend:{display:false}
        }
    }
});

new Chart(document.getElementById('graficaGastos'), {
    type:'bar',
    data:{
        labels:<?= json_encode($labelsGastos) ?>,
        datasets:[{
            data:<?= json_encode($datosGastos) ?>,
            backgroundColor:'#979cbd'
        }]
    },
    options:{
        responsive:true,
        plugins:{
            legend:{display:false}
        }
    }
});

new Chart(document.getElementById('graficaClientes'), {
    type:'bar',
    data:{
        labels:<?= json_encode($labelsClientes) ?>,
        datasets:[{
            data:<?= json_encode($datosClientes) ?>,
            backgroundColor:'#97c9bd'
        }]
    },
    options:{
        responsive:true,
        plugins:{
            legend:{display:false}
        }
    }
});
</script>

</body>
</html>