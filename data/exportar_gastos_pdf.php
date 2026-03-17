<?php
session_start();

require_once '../config/Conexion.php';
require_once __DIR__ . '/../public/librerias/fpdf186/fpdf.php';
require_once __DIR__ . '/../config/seguridad.php';

verificarRol(1);

if(!isset($_SESSION['id_usuario'])){
    header("Location: ../index.php");
    exit();
}

$buscar = trim($_GET['buscar'] ?? '');
$desde  = trim($_GET['desde'] ?? '');
$hasta  = trim($_GET['hasta'] ?? '');

try {
    $pdo = Conexion::conectar();

    $query = "SELECT 
                g.id_gasto, 
                CONCAT(u.primer_nombre,' ',u.primer_apellido) AS usuario,
                g.concepto, 
                g.fecha, 
                g.total
              FROM gastos g
              INNER JOIN usuarios u ON g.id_usuario = u.id_usuario
              WHERE 1=1";

    $params = [];

    if($buscar !== ''){
        $query .= " AND g.concepto LIKE :buscar";
        $params[':buscar'] = "%$buscar%";
    }

    if($desde !== ''){
        $query .= " AND DATE(g.fecha) >= :desde";
        $params[':desde'] = $desde;
    }

    if($hasta !== ''){
        $query .= " AND DATE(g.fecha) <= :hasta";
        $params[':hasta'] = $hasta;
    }

    $query .= " ORDER BY g.fecha DESC";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $totalGastos = 0;
    foreach($data as $row){
        $totalGastos += (float)$row['total'];
    }

    $pdf = new FPDF('L','mm','A4');
    $pdf->AddPage();
    $pdf->SetFont('Arial','B',14);
    $pdf->Cell(0,10,'Reporte de Gastos',0,1,'C');
    $pdf->Ln(3);

    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(15,8,'ID',1,0,'C');
    $pdf->Cell(55,8,'Usuario',1,0,'C');
    $pdf->Cell(95,8,'Concepto',1,0,'C');
    $pdf->Cell(50,8,'Fecha',1,0,'C');
    $pdf->Cell(30,8,'Total',1,1,'C');

    $pdf->SetFont('Arial','',9);

    foreach($data as $row){
        $pdf->Cell(15,8,$row['id_gasto'],1,0,'C');
        $pdf->Cell(55,8,utf8_decode($row['usuario']),1,0,'L');
        $pdf->Cell(95,8,utf8_decode($row['concepto']),1,0,'L');
        $pdf->Cell(50,8,$row['fecha'],1,0,'C');
        $pdf->Cell(30,8,'$'.number_format($row['total'],2),1,1,'R');
    }

    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(215,8,'TOTAL',1,0,'R');
    $pdf->Cell(30,8,'$'.number_format($totalGastos,2),1,1,'R');

    $pdf->Output('D', 'Gastos.pdf');

} catch(Exception $e){
    http_response_code(500);
    echo "Error al generar PDF: " . $e->getMessage();
}
?>