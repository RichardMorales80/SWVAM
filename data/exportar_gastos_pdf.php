<?php
require_once '../config/Conexion.php';
require_once __DIR__ . '/../public/librerias/fpdf186/fpdf.php'; // FPDF correcto
require_once __DIR__ . '/../config/seguridad.php';
verificarRoles([1,3]);

$buscar = $_GET['buscar'] ?? '';
$desde  = $_GET['desde'] ?? '';
$hasta  = $_GET['hasta'] ?? '';

try {
    $pdo = Conexion::conectar();

    $query = "SELECT g.id_gasto, CONCAT(u.primer_nombre,' ',u.primer_apellido) AS usuario,
                     g.concepto, g.fecha, g.total
              FROM gastos g
              INNER JOIN usuarios u ON g.id_usuario = u.id_usuario
              WHERE 1=1";

    $params = [];
    if($buscar){
        $query .= " AND g.concepto LIKE :buscar";
        $params[':buscar'] = "%$buscar%";
    }
    if($desde){
        $query .= " AND g.fecha >= :desde";
        $params[':desde'] = $desde;
    }
    if($hasta){
        $query .= " AND g.fecha <= :hasta";
        $params[':hasta'] = $hasta;
    }

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calcular total de gastos
    $totalGastos = 0;
    foreach($data as $row){
        $totalGastos += $row['total'];
    }

    // Crear PDF con FPDF
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial','B',14);
    $pdf->Cell(0,10,'Gastos',0,1,'C');
    $pdf->Ln(5);

    // Encabezado de tabla
    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(10,8,'ID',1);
    $pdf->Cell(40,8,'Usuario',1);
    $pdf->Cell(60,8,'Concepto',1);
    $pdf->Cell(30,8,'Fecha',1);
    $pdf->Cell(20,8,'Total',1);
    $pdf->Ln();

    // Contenido de tabla
    $pdf->SetFont('Arial','',10);
    foreach($data as $row){
        $pdf->Cell(10,8,$row['id_gasto'],1);
        $pdf->Cell(40,8,$row['usuario'],1);
        $pdf->Cell(60,8,$row['concepto'],1);
        $pdf->Cell(30,8,$row['fecha'],1);
        $pdf->Cell(20,8,'$'.number_format($row['total'],2),1);
        $pdf->Ln();
    }

    // Fila de totales
    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(10+40+60+30,8,'TOTAL',1,0,'R');
    $pdf->Cell(20,8,'$'.number_format($totalGastos,2),1,1,'L');

    // Generar PDF
    $pdf->Output('D','Gastos.pdf');

} catch(Exception $e){
    http_response_code(500);
    echo "Error al generar PDF: ".$e->getMessage();
}