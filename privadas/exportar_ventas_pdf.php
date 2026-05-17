<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

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

/* FILTRO POR USUARIO */
if ($id_rol != 1) {
    $where[] = "v.id_usuario = :id_usuario";
    $params[':id_usuario'] = $id_usuario;
}

/* BUSQUEDA */
if (!empty($buscar)) {
    $where[] = "dv.descripcion LIKE :buscar";
    $params[':buscar'] = "%$buscar%";
}

/* FILTRO POR FECHA */
if (!empty($inicio) && !empty($fin)) {
    $where[] = "v.fecha BETWEEN :inicio AND :fin";
    $params[':inicio'] = $inicio . " 00:00:00";
    $params[':fin']    = $fin . " 23:59:59";
}

$whereSQL = "";
if (!empty($where)) {
    $whereSQL = "WHERE " . implode(" AND ", $where);
}

/* CONSULTA */
$sql = "
SELECT 
    v.id_venta,
    v.fecha,
    dv.descripcion,
    dv.cantidad,
    dv.precio,
    dv.total
FROM ventas v
INNER JOIN detalle_venta dv ON dv.id_venta = v.id_venta
$whereSQL
ORDER BY v.fecha DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* TOTAL */
$granTotal = 0;
foreach($ventas as $v){
    $granTotal += $v['total'];
}

/* PDF */
$pdf = new FPDF();
$pdf->AddPage();

/* ===== LOGO ===== */

$pdf->Image(__DIR__ . '/../public/imagenes/logo.png', 10, 8, 30);

/* ===== TITULO ===== */
$pdf->SetFont('Arial','B',16);
$pdf->Cell(0,10,'Ventas Generales',0,1,'C');

/* ESPACIO */
$pdf->Ln(10);

/* ENCABEZADOS */
$pdf->SetFont('Arial','B',10);
$pdf->Cell(15,8,'ID',1);
$pdf->Cell(70,8,'Descripcion',1);
$pdf->Cell(20,8,'Precio',1);
$pdf->Cell(20,8,'Cantidad',1);
$pdf->Cell(20,8,'Total',1);
$pdf->Cell(45,8,'Fecha',1);
$pdf->Ln();

/* DATOS */
$pdf->SetFont('Arial','',10);

foreach($ventas as $v){

    $pdf->Cell(15,8,$v['id_venta'],1);
    $pdf->Cell(70,8,mb_convert_encoding($v['descripcion'], 'ISO-8859-1', 'UTF-8'),1);
    $pdf->Cell(20,8,$v['precio'],1);
    $pdf->Cell(20,8,$v['cantidad'],1);
    $pdf->Cell(20,8,$v['total'],1);
    $pdf->Cell(45,8,$v['fecha'],1);
    $pdf->Ln();
}

/* TOTAL FINAL */
$pdf->SetFont('Arial','B',10);
$pdf->Cell(145,8,'Gran Total:',1);
$pdf->Cell(45,8,number_format($granTotal,2),1);

$pdf->Output("D","Ventas.pdf");
exit;