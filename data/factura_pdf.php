<?php
require_once __DIR__ . '/../config/Conexion.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/SWVAM/public/librerias/fpdf186/fpdf.php';

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
f.id_venta,
f.rfc,
f.razon_social,
v.total,
CONCAT(u.primer_nombre,' ',u.primer_apellido) AS cliente
FROM facturas f
LEFT JOIN ventas v ON v.id_venta = f.id_venta
LEFT JOIN usuarios u ON u.id_usuario = v.id_usuario
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
FROM ventas
WHERE id_venta = ?";

$stmt = $pdo->prepare($sqlDetalle);
$stmt->execute([$factura['id_venta']]);

$detalle = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* =========================
   CALCULOS
========================= */

$subtotal = 0;

foreach($detalle as $d){
    $subtotal += $d['total'];
}

$iva = $subtotal * 0.16;
$total = $subtotal + $iva;

/* =========================
   PDF
========================= */

$pdf = new FPDF();
$pdf->AddPage();

/* ===== LOGO ===== */
$pdf->Image($_SERVER['DOCUMENT_ROOT'].'/SWVAM/public/imagenes/logo.png',10,10,30);

/* ===== EMPRESA ===== */
$pdf->SetFont('Arial','B',14);
$pdf->Cell(0,8,'MATTHEW NDT',0,1,'R');

$pdf->SetFont('Arial','',10);
$pdf->Cell(0,5,'Technology & Solutions',0,1,'R');
$pdf->Cell(0,5,'RFC: XAXX010101000',0,1,'R');
$pdf->Cell(0,5,'Estado de Mexico, Mexico',0,1,'R');

$pdf->Ln(15);

/* ===== DATOS FACTURA ===== */

$pdf->SetFont('Arial','B',12);
$pdf->Cell(100,8,'Factura #: '.$factura['id_factura'],0,0);
$pdf->Cell(0,8,'Fecha: '.$factura['fecha'],0,1);

$pdf->Cell(100,8,'Cliente: '.$factura['cliente'],0,1);
$pdf->Cell(100,8,'RFC: '.$factura['rfc'],0,1);
$pdf->Cell(100,8,'Razon Social: '.$factura['razon_social'],0,1);

$pdf->Ln(5);

/* ===== TABLA HEADER ===== */

$pdf->SetFillColor(40,40,40);
$pdf->SetTextColor(255,255,255);
$pdf->SetFont('Arial','B',11);

$pdf->Cell(80,8,'Producto',1,0,'C',true);
$pdf->Cell(25,8,'Cant.',1,0,'C',true);
$pdf->Cell(40,8,'Precio',1,0,'C',true);
$pdf->Cell(45,8,'Importe',1,1,'C',true);

/* ===== TABLA BODY ===== */

$pdf->SetTextColor(0,0,0);
$pdf->SetFont('Arial','',10);

foreach($detalle as $d){

    $pdf->Cell(80,8,$d['descripcion'],1);
    $pdf->Cell(25,8,$d['cantidad'],1,0,'C');
    $pdf->Cell(40,8,'$'.number_format($d['precio'],2),1,0,'R');
    $pdf->Cell(45,8,'$'.number_format($d['total'],2),1,1,'R');
}

/* ===== TOTALES ===== */

$pdf->Ln(5);

$pdf->SetFont('Arial','B',11);

$pdf->Cell(145,8,'Subtotal',1,0,'R');
$pdf->Cell(45,8,'$'.number_format($subtotal,2),1,1,'R');

$pdf->Cell(145,8,'IVA (16%)',1,0,'R');
$pdf->Cell(45,8,'$'.number_format($iva,2),1,1,'R');

$pdf->Cell(145,10,'TOTAL',1,0,'R');
$pdf->Cell(45,10,'$'.number_format($total,2),1,1,'R');


/* ===== QR SEGURO FINAL ===== */

$qrTexto = "Factura ".$factura['id_factura']." Total $".$total;

$qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=".urlencode($qrTexto);

$rutaQR = __DIR__ . "/../facturas_generadas/qr/";
$archivoQR = $rutaQR . "QR_".$factura['id_factura'].".png";

/* CREAR CARPETA */
if(!file_exists($rutaQR)){
    mkdir($rutaQR,0777,true);
}

/* GENERAR SOLO SI NO EXISTE */
if(!file_exists($archivoQR)){

    $qrImagen = @file_get_contents($qrUrl);

    /* VALIDAR QUE SEA IMAGEN REAL */
    if($qrImagen !== false && strlen($qrImagen) > 100){

        file_put_contents($archivoQR, $qrImagen);

    }else{

        /* FALLBACK: NO USAR QR */
        $archivoQR = null;
    }
}

/* VALIDAR ARCHIVO FINAL */
if($archivoQR && file_exists($archivoQR) && filesize($archivoQR) > 100){

    $pdf->Ln(5);
    $pdf->Image($archivoQR,10,$pdf->GetY(),30);


}
/* ===== FOOTER ===== */

$pdf->Ln(25);
$pdf->SetFont('Arial','I',9);
$pdf->Cell(0,5,'Este documento es una representacion impresa de una factura.',0,1,'C');
$pdf->Cell(0,5,'Gracias por su preferencia.',0,1,'C');

/* =========================
   GUARDAR PDF EN SERVIDOR
========================= */

$ruta = __DIR__ . "/../facturas_generadas/";

if(!file_exists($ruta)){
    mkdir($ruta,0777,true);
}

$nombreArchivo = "FACTURA_".$factura['id_factura'].".pdf";

$pdf->Output("F", $ruta.$nombreArchivo);

/* =========================
   MOSTRAR PDF
========================= */

$pdf->Output("I",$nombreArchivo);