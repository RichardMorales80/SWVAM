<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../config/Conexion.php';

$pdo = Conexion::conectar();

$id = $_GET['id'] ?? 0;

if(!$id){
    die("Factura no válida");
}

/* =========================
   DATOS FACTURA
========================= */

$sql = "SELECT 
f.id_factura,
f.fecha,
v.id_venta,
CONCAT(u.primer_nombre,' ',u.primer_apellido) AS cliente
FROM facturas f
JOIN ventas v ON v.id_venta = f.id_venta
JOIN usuarios u ON u.id_usuario = v.id_usuario
WHERE f.id_factura = ?";

$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);

$factura = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$factura){
    die("Factura no encontrada");
}

/* =========================
   DETALLE
========================= */

$sqlDetalle = "SELECT descripcion, cantidad, precio, total
FROM detalle_venta
WHERE id_venta = ?";

$stmt = $pdo->prepare($sqlDetalle);
$stmt->execute([$factura['id_venta']]);

$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* =========================
   CALCULOS
========================= */

$subtotal = 0;

foreach($productos as $p){
    $subtotal += $p['total'];
}

$iva = $subtotal * 0.16;
$total = $subtotal + $iva;
?>

<!DOCTYPE html>
<html lang="es">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Factura</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
    background:#f5f5f5;
    font-family:Arial, Helvetica, sans-serif;
}

.factura{
    background:white;
    max-width:1000px;
    margin:auto;
    padding:40px;
    border-radius:10px;
    box-shadow:0 0 10px rgba(0,0,0,0.1);
}

.logo{
    width:120px;
}

.titulo{
    font-size:32px;
    font-weight:bold;
    color:#333;
}

.datos-empresa{
    font-size:14px;
    color:#555;
}

.table th{
    background:#212529;
    color:white;
}

.total-box{
    width:350px;
    margin-left:auto;
}

.footer{
    margin-top:40px;
    font-size:12px;
    color:#777;
    text-align:center;
}

.qr{
    width:100px;
}

.estado{
    font-size:18px;
    font-weight:bold;
    color:green;
}

</style>

</head>

<body>

<div class="factura">

    <!-- ENCABEZADO -->
    <div class="row">

        <div class="col-md-6">

            <img src="../assets/logo.png" class="logo">

            <div class="datos-empresa mt-2">

                <strong>SWVAM Solutions</strong><br>
                RFC: XAXX010101000<br>
                Coacalco, Estado de México<br>
                Tel: 55 1234 5678<br>
                Email: contacto@swvam.com

            </div>

        </div>

        <div class="col-md-6 text-end">

            <div class="titulo">FACTURA</div>

            <p>
                <strong>Folio:</strong> FAC-<?= str_pad($factura['id_factura'],5,"0",STR_PAD_LEFT) ?><br>

                <strong>Fecha:</strong> <?= $factura['fecha'] ?><br>

                <strong>Estado:</strong> 
                <span class="estado">PAGADA</span>
            </p>

        </div>

    </div>

    <hr>

    <!-- CLIENTE -->
    <div class="row mb-4">

        <div class="col-md-6">

            <h5>Cliente</h5>

            <p>
                <?= $factura['cliente'] ?><br>
                Público en general<br>
                Método de pago: Tarjeta<br>
                Uso CFDI: G03
            </p>

        </div>

        <div class="col-md-6 text-end">

            <h5>Factura electrónica</h5>

            <p>
                UUID: <?= strtoupper(uniqid()) ?><br>
                Moneda: MXN<br>
                Tipo: Ingreso
            </p>

        </div>

    </div>

    <!-- TABLA -->
    <table class="table table-bordered">

        <thead>
            <tr>
                <th>Descripción</th>
                <th width="120">Cantidad</th>
                <th width="150">Precio Unitario</th>
                <th width="150">Importe</th>
            </tr>
        </thead>

        <tbody>

            <?php foreach($productos as $p): ?>

            <tr>

                <td><?= $p['descripcion'] ?></td>

                <td><?= $p['cantidad'] ?></td>

                <td>$<?= number_format($p['precio'],2) ?></td>

                <td>$<?= number_format($p['total'],2) ?></td>

            </tr>

            <?php endforeach; ?>

        </tbody>

    </table>

    <!-- TOTALES -->
    <div class="total-box">

        <table class="table">

            <tr>
                <th>Subtotal:</th>
                <td class="text-end">
                    $<?= number_format($subtotal,2) ?>
                </td>
            </tr>

            <tr>
                <th>IVA (16%):</th>
                <td class="text-end">
                    $<?= number_format($iva,2) ?>
                </td>
            </tr>

            <tr class="table-dark">

                <th>Total:</th>

                <td class="text-end">
                    $<?= number_format($total,2) ?>
                </td>

            </tr>

        </table>

    </div>

    <!-- QR Y SELLO -->
    <div class="row mt-5">

        <div class="col-md-6">

            <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=Factura<?= $factura['id_factura'] ?>" class="qr">

        </div>

        <div class="col-md-6 text-end">

            <small>
                Este documento es una representación impresa de un CFDI.<br>
                Emitido por SWVAM Solutions.
            </small>

        </div>

    </div>

    <!-- BOTONES -->
    <div class="mt-4">

        <a href="facturas.php" class="btn btn-secondary">
            ⬅ Volver
        </a>

        <a href="factura_pdf.php?id=<?= $factura['id_factura'] ?>" class="btn btn-danger">
            📄 Descargar PDF
        </a>

    </div>

</div>

</body>
</html>