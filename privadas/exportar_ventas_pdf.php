<?php
require_once __DIR__ . '/../config/Conexion.php';
require __DIR__ . '/../config/seguridad.php';
require_once __DIR__ . '/../public/librerias/fpdf186/fpdf.php';
verificarRoles([1,3]);

$pdo = Conexion::conectar();
$id_rol = $_SESSION['id_rol'];
$id_usuario = $_SESSION['id_usuario'];

$buscar = $_GET['buscar'] ?? '';
$inicio = $_GET['inicio'] ?? '';
$fin    = $_GET['fin'] ?? '';

$where = [];
$params = [];

if ($id_rol != 1) {
    $where[] = "id_usuario = :id_usuario";
    $params[':id_usuario'] = $id_usuario;
}

if (!empty($buscar)) {
    $where[] = "(descripcion LIKE :buscar OR id_producto LIKE :buscar)";
    $params[':buscar'] = "%$buscar%";
}

if (!empty($inicio) && !empty($fin)) {
    $where[] = "fecha BETWEEN :inicio AND :fin";
    $params[':inicio'] = $inicio . " 00:00:00";
    $params[':fin']    = $fin . " 23:59:59";
}

$whereSQL = "";
if(!empty($where)) $whereSQL = "WHERE " . implode(" AND ", $where);

$sql = "SELECT * FROM ventas $whereSQL ORDER BY fecha DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* CALCULAR GRAN TOTAL */
$granTotal = 0;
foreach($ventas as $v) $granTotal += $v['total'];

/* GENERAR PDF */
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',14);
$pdf->Cell(0,10,'Ventas',0,1,'C');

$pdf->SetFont('Arial','B',10);
$pdf->Cell(10,8,'ID',1);
$pdf->Cell(20,8,'Producto',1);
$pdf->Cell(60,8,'Descripcion',1);
$pdf->Cell(20,8,'Precio',1);
$pdf->Cell(20,8,'Cantidad',1);
$pdf->Cell(20,8,'Total',1);
$pdf->Cell(35,8,'Fecha',1);
$pdf->Ln();

$pdf->SetFont('Arial','',10);
foreach($ventas as $v){
    $pdf->Cell(10,8,$v['id_venta'],1);
    $pdf->Cell(20,8,$v['id_producto'],1);
    $pdf->Cell(60,8,utf8_decode($v['descripcion']),1);
    $pdf->Cell(20,8,$v['precio'],1);
    $pdf->Cell(20,8,$v['cantidad'],1);
    $pdf->Cell(20,8,$v['total'],1);
    $pdf->Cell(35,8,$v['fecha'],1);
    $pdf->Ln();
}

// FILA DE GRAN TOTAL
$pdf->SetFont('Arial','B',10);
$pdf->Cell(150,8,'Gran Total:',1);
$pdf->Cell(35,8,number_format($granTotal,2),1);
$pdf->Ln();

$pdf->Output("D","Ventas.pdf");
exit;