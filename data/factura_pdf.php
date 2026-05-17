<?php

ob_start();

ini_set('display_errors', 0);
error_reporting(0);

require_once __DIR__ . '/../config/Conexion.php';
require_once __DIR__ . '/../public/librerias/fpdf186/fpdf.php';

$pdo = Conexion::conectar();

/* =========================
   VALIDAR ID
========================= */

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if($id <= 0){
    die('Factura no valida');
}

/* =========================
   FACTURA
========================= */

$sql = "SELECT 
f.id_factura,
f.fecha,
f.id_venta,
IFNULL(f.rfc,'') AS rfc,
IFNULL(f.razon_social,'') AS razon_social,
CONCAT(
IFNULL(u.primer_nombre,''),' ',
IFNULL(u.primer_apellido,'')
) AS cliente
FROM facturas f
LEFT JOIN ventas v 
ON v.id_venta = f.id_venta
LEFT JOIN usuarios u 
ON u.id_usuario = v.id_usuario
WHERE f.id_factura = ?";

$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);

$factura = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$factura){
    die('Factura no encontrada');
}

/* =========================
   DETALLE
========================= */

$sqlDetalle = "SELECT 
IFNULL(descripcion,'') AS descripcion,
IFNULL(cantidad,0) AS cantidad,
IFNULL(precio,0) AS precio,
IFNULL(total,0) AS total
FROM detalle_venta
WHERE id_venta = ?";

$stmt = $pdo->prepare($sqlDetalle);
$stmt->execute([$factura['id_venta']]);

$detalle = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* =========================
   CALCULOS
========================= */

$subtotal = 0;

foreach($detalle as $d){

    $subtotal += floatval($d['total']);
}

$iva = $subtotal * 0.16;
$total = $subtotal + $iva;

/* =========================
   PDF
========================= */

$pdf = new FPDF();
$pdf->AddPage();

$pdf->SetMargins(15,15,15);

/* =========================
   HEADER COLOR GRIS CLARO
========================= */

$pdf->SetFillColor(245,245,245);
$pdf->Rect(0,0,220,40,'F');
/* =========================
   LOGO
========================= */

$logo = __DIR__ . '/../public/imagenes/logo.png';

if(file_exists($logo)){

    $pdf->Image($logo,15,8,25);
}

/* =========================
   TITULO
========================= */

$pdf->SetTextColor(0,0,0);

$pdf->SetFont('Arial','B',20);

$pdf->SetXY(120,10);

$pdf->Cell(75,8,'FACTURA',0,1,'R');

$pdf->SetFont('Arial','',10);

$pdf->SetX(120);

$pdf->Cell(
75,
5,
'Folio: FAC-'.str_pad($factura['id_factura'],5,'0',STR_PAD_LEFT),
0,
1,
'R'
);

$pdf->SetX(120);

$pdf->Cell(
75,
5,
'Fecha: '.$factura['fecha'],
0,
1,
'R'
);

$pdf->SetX(120);

$pdf->Cell(
75,
5,
'Estado: PAGADA',
0,
1,
'R'
);

$pdf->Ln(10);

/* =========================
   EMPRESA
========================= */

$pdf->SetTextColor(0,0,0);

$pdf->SetFont('Arial','B',14);

$pdf->Cell(0,7,'MATTHEW NDT',0,1);

$pdf->SetFont('Arial','',10);

$pdf->Cell(0,5,'Technology & Solutions',0,1);
$pdf->Cell(0,5,'RFC: XAXX010101000',0,1);
$pdf->Cell(0,5,'Coacalco, Estado de Mexico',0,1);
$pdf->Cell(0,5,'Telefono: 55 1234 5678',0,1);
$pdf->Cell(0,5,'Correo: contacto@matthewndt.com',0,1);

$pdf->Ln(8);

/* =========================
   CLIENTE
========================= */

$pdf->SetFillColor(240,240,240);

$pdf->SetFont('Arial','B',11);

$pdf->Cell(
0,
8,
'DATOS DEL CLIENTE',
0,
1,
'L',
true
);

$pdf->SetFont('Arial','',10);

$pdf->Cell(40,7,'Cliente:',0,0);
$pdf->Cell(100,7,$factura['cliente'],0,1);

$pdf->Cell(40,7,'RFC:',0,0);
$pdf->Cell(100,7,$factura['rfc'],0,1);

$pdf->Cell(40,7,'Razon Social:',0,0);
$pdf->Cell(100,7,$factura['razon_social'],0,1);

$pdf->Cell(40,7,'Metodo Pago:',0,0);
$pdf->Cell(100,7,'Tarjeta',0,1);

$pdf->Cell(40,7,'Uso CFDI:',0,0);
$pdf->Cell(100,7,'G03 - Gastos en general',0,1);

$pdf->Ln(10);

/* =========================
   TABLA
========================= */

$pdf->SetFillColor(33,37,41);

$pdf->SetTextColor(255,255,255);

$pdf->SetFont('Arial','B',11);

$pdf->Cell(75,10,'Descripcion',1,0,'C',true);
$pdf->Cell(25,10,'Cantidad',1,0,'C',true);
$pdf->Cell(40,10,'Precio',1,0,'C',true);
$pdf->Cell(45,10,'Importe',1,1,'C',true);

$pdf->SetTextColor(0,0,0);

$pdf->SetFont('Arial','',10);

foreach($detalle as $d){

    $pdf->Cell(
    75,
    10,
    $d['descripcion'],
    1
    );

    $pdf->Cell(
    25,
    10,
    $d['cantidad'],
    1,
    0,
    'C'
    );

    $pdf->Cell(
    40,
    10,
    '$'.number_format($d['precio'],2),
    1,
    0,
    'R'
    );

    $pdf->Cell(
    45,
    10,
    '$'.number_format($d['total'],2),
    1,
    1,
    'R'
    );
}

/* =========================
   TOTALES
========================= */

$pdf->Ln(8);

$pdf->SetX(110);

$pdf->SetFont('Arial','B',11);

$pdf->Cell(45,8,'Subtotal',1,0,'R');

$pdf->Cell(
35,
8,
'$'.number_format($subtotal,2),
1,
1,
'R'
);

$pdf->SetX(110);

$pdf->Cell(45,8,'IVA (16%)',1,0,'R');

$pdf->Cell(
35,
8,
'$'.number_format($iva,2),
1,
1,
'R'
);

$pdf->SetX(110);

$pdf->SetFillColor(33,37,41);

$pdf->SetTextColor(255,255,255);

$pdf->Cell(
45,
10,
'TOTAL',
1,
0,
'R',
true
);

$pdf->Cell(
35,
10,
'$'.number_format($total,2),
1,
1,
'R',
true
);

$pdf->SetTextColor(0,0,0);

/* =========================
   QR
========================= */

$qrLocal = __DIR__ . '/../public/imagenes/qr_demo.jpg';

if(file_exists($qrLocal)){

    $pdf->Image(
    $qrLocal,
    15,
    $pdf->GetY()+10,
    25,
    25
    );
}

/* =========================
   UUID
========================= */

$pdf->SetY($pdf->GetY()+10);

$pdf->SetX(50);

$pdf->SetFont('Arial','',8);

$texto =
'UUID: '.strtoupper(uniqid())."\n".
'Este documento es una representacion impresa de un CFDI.'."\n".
'Emitido por MATTHEW NDT Technology & Solutions.';

$pdf->MultiCell(
140,
5,
$texto,
0,
'L'
);

/* =========================
   PIE
========================= */

$pdf->Ln(10);

$pdf->SetFont('Arial','I',8);

$pdf->Cell(
0,
5,
'Gracias por su compra',
0,
1,
'C'
);

/* =========================
   LIMPIAR BUFFER
========================= */

while (ob_get_level()) {
    ob_end_clean();
}

/* =========================
   OUTPUT
========================= */

$pdf->Output(
'I',
'Factura_'.$factura['id_factura'].'.pdf'
);

exit;

?>