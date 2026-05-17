<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/Conexion.php';
require_once __DIR__ . '/../public/librerias/fpdf186/fpdf.php';

$pdo = Conexion::conectar();

$fecha_inicio = $_GET['fecha_inicio'] ?? '';
$fecha_fin    = $_GET['fecha_fin'] ?? '';

$condicion = "";
$params = [];

if($fecha_inicio && $fecha_fin){

    $condicion = " WHERE v.fecha BETWEEN :inicio AND :fin ";

    $params = [
        ':inicio' => $fecha_inicio,
        ':fin' => $fecha_fin
    ];
}

/* ======================================
   FUNCION PARA TEXTOS UTF8
====================================== */

function texto($txt){
    return mb_convert_encoding($txt, 'ISO-8859-1', 'UTF-8');
}

/* ======================================
   VENTAS
====================================== */

$sqlVentas = "
SELECT COALESCE(SUM(total),0)
FROM ventas
" . ($fecha_inicio && $fecha_fin
? "WHERE fecha BETWEEN :inicio AND :fin"
: "");

$stmt = $pdo->prepare($sqlVentas);
$stmt->execute($params);

$totalVentas = (float)$stmt->fetchColumn();


/* ======================================
   GASTOS
====================================== */

$sqlGastos = "
SELECT COALESCE(SUM(total),0)
FROM gastos
" . ($fecha_inicio && $fecha_fin
? "WHERE fecha BETWEEN :inicio AND :fin"
: "");

$stmt = $pdo->prepare($sqlGastos);
$stmt->execute($params);

$totalGastos = (float)$stmt->fetchColumn();


/* ======================================
   BALANCE
====================================== */

$balance = $totalVentas - $totalGastos;


/* ======================================
   TOTAL DE VENTAS
====================================== */

$sqlCantidadVentas = "
SELECT COUNT(*)
FROM ventas
" . ($fecha_inicio && $fecha_fin
? "WHERE fecha BETWEEN :inicio AND :fin"
: "");

$stmt = $pdo->prepare($sqlCantidadVentas);
$stmt->execute($params);

$totalRegistrosVentas = (int)$stmt->fetchColumn();


/* ======================================
   TICKET PROMEDIO
====================================== */

$ticketPromedio = $totalRegistrosVentas > 0
? $totalVentas / $totalRegistrosVentas
: 0;


/* ======================================
   PORCENTAJES
====================================== */

$porcentajeGastos = $totalVentas > 0
? ($totalGastos / $totalVentas) * 100
: 0;

$porcentajeGanancia = $totalVentas > 0
? ($balance / $totalVentas) * 100
: 0;


/* ======================================
   ESTADO FINANCIERO
====================================== */

if($balance > 10000){

    $estadoFinanciero = "EXCELENTE";

}
elseif($balance > 0){

    $estadoFinanciero = "ESTABLE";

}
else{

    $estadoFinanciero = "PERDIDAS";
}


/* ======================================
   CONCLUSION AUTOMATICA
====================================== */

if($balance > 0){

    $conclusion = "
    Durante el periodo analizado el negocio
    presenta una rentabilidad positiva,
    mostrando un adecuado control financiero
    y estabilidad operativa.
    ";

}else{

    $conclusion = "
    Durante el periodo analizado el negocio
    presenta perdidas financieras, por lo que
    se recomienda revisar gastos operativos
    y estrategias de ventas.
    ";
}


/* ======================================
   ACTIVOS
====================================== */

$sqlActivos = "

SELECT
nombre,
cantidad,
precio,
(cantidad * precio) AS total

FROM productos

ORDER BY total DESC

";

$stmtActivos = $pdo->prepare($sqlActivos);
$stmtActivos->execute();

$activos = $stmtActivos->fetchAll(PDO::FETCH_ASSOC);


/* ======================================
   TOTAL ACTIVOS
====================================== */

$sqlTotalActivos = "

SELECT COALESCE(SUM(cantidad * precio),0)
FROM productos

";

$stmtTotalActivos = $pdo->prepare($sqlTotalActivos);
$stmtTotalActivos->execute();

$totalActivos = (float)$stmtTotalActivos->fetchColumn();


/* ======================================
   PRODUCTOS MAS VENDIDOS
====================================== */

$sqlProductos = "

SELECT 
descripcion,
SUM(cantidad) AS cantidad

FROM detalle_venta dv

JOIN ventas v 
ON v.id_venta = dv.id_venta

$condicion

GROUP BY descripcion

ORDER BY cantidad DESC

LIMIT 5

";

$stmt = $pdo->prepare($sqlProductos);
$stmt->execute($params);

$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);


/* ======================================
   CLASE PDF
====================================== */

class PDF extends FPDF {

    function Header(){

        $logoPath = __DIR__ . '/../public/imagenes/logo.png';

        if(file_exists($logoPath)){

            $this->Image($logoPath, 10, 8, 25);

        }

        $this->SetFont('Arial','B',16);

        $this->Cell(
            0,
            10,
            texto('REPORTE FINANCIERO'),
            0,
            1,
            'C'
        );

        $this->SetFont('Arial','',10);

        $this->Cell(
            0,
            5,
            texto('Generado: '.date('d/m/Y H:i')),
            0,
            1,
            'C'
        );

        $this->Line(10,30,200,30);

        $this->Ln(15);
    }

}


/* ======================================
   GENERAR PDF
====================================== */

$pdf = new PDF();

$pdf->AddPage();


/* ======================================
   PERIODO
====================================== */

$pdf->SetFont('Arial','',10);

if($fecha_inicio && $fecha_fin){

    $pdf->Cell(
        0,
        8,
        texto("Periodo: $fecha_inicio al $fecha_fin"),
        0,
        1
    );

    $pdf->Ln(3);
}


/* ======================================
   RESUMEN FINANCIERO
====================================== */

$pdf->SetFont('Arial','B',13);

$pdf->Cell(
    0,
    10,
    texto('Resumen Financiero'),
    0,
    1
);

$pdf->SetFont('Arial','',11);

$pdf->Cell(
    0,
    8,
    texto('Ventas Totales: $'.number_format($totalVentas,2)),
    0,
    1
);

$pdf->Cell(
    0,
    8,
    texto('Gastos Totales: $'.number_format($totalGastos,2)),
    0,
    1
);

$pdf->Cell(
    0,
    8,
    texto('Balance Neto: $'.number_format($balance,2)),
    0,
    1
);

$pdf->Cell(
    0,
    8,
    texto('Margen de Ganancia: '.number_format($porcentajeGanancia,1).'%'),
    0,
    1
);

$pdf->Cell(
    0,
    8,
    texto('% Gastos: '.number_format($porcentajeGastos,1).'%'),
    0,
    1
);

$pdf->Cell(
    0,
    8,
    texto('Ticket Promedio: $'.number_format($ticketPromedio,2)),
    0,
    1
);

$pdf->Cell(
    0,
    8,
    texto('Estado Financiero: '.$estadoFinanciero),
    0,
    1
);

$pdf->Cell(
    0,
    8,
    texto('Total de Ventas Registradas: '.$totalRegistrosVentas),
    0,
    1
);

$pdf->Cell(
    0,
    8,
    texto('Valor Total de Activos: $'.number_format($totalActivos,2)),
    0,
    1
);

$pdf->Ln(10);


/* ======================================
   ACTIVOS DE INVENTARIO
====================================== */

$pdf->SetFont('Arial','B',13);

$pdf->Cell(
    0,
    10,
    texto('Activos de Inventario'),
    0,
    1
);

$pdf->SetFont('Arial','B',10);

$pdf->Cell(70,8,texto('Producto'),1);
$pdf->Cell(30,8,texto('Cantidad'),1);
$pdf->Cell(40,8,texto('Precio'),1);
$pdf->Cell(40,8,texto('Valor Total'),1);

$pdf->Ln();

$pdf->SetFont('Arial','',10);

foreach($activos as $a){

    $pdf->Cell(
        70,
        8,
        texto($a['nombre']),
        1
    );

    $pdf->Cell(
        30,
        8,
        $a['cantidad'],
        1
    );

    $pdf->Cell(
        40,
        8,
        '$'.number_format($a['precio'],2),
        1
    );

    $pdf->Cell(
        40,
        8,
        '$'.number_format($a['total'],2),
        1
    );

    $pdf->Ln();
}

$pdf->SetFont('Arial','B',11);

$pdf->Cell(
    140,
    10,
    texto('Total Activos'),
    1
);

$pdf->Cell(
    40,
    10,
    '$'.number_format($totalActivos,2),
    1
);

$pdf->Ln(15);


/* ======================================
   PRODUCTOS MAS VENDIDOS
====================================== */

$pdf->SetFont('Arial','B',13);

$pdf->Cell(
    0,
    10,
    texto('Productos Más Vendidos'),
    0,
    1
);

$pdf->SetFont('Arial','B',10);

$pdf->Cell(130,8,texto('Producto'),1);
$pdf->Cell(40,8,texto('Cantidad'),1);

$pdf->Ln();

$pdf->SetFont('Arial','',10);

foreach($productos as $p){

    $pdf->Cell(
        130,
        8,
        texto($p['descripcion']),
        1
    );

    $pdf->Cell(
        40,
        8,
        $p['cantidad'],
        1
    );

    $pdf->Ln();
}

$pdf->Ln(10);


/* ======================================
   CONCLUSION
====================================== */

$pdf->SetFont('Arial','B',13);

$pdf->Cell(
    0,
    10,
    texto('Conclusión del Reporte'),
    0,
    1
);

$pdf->SetFont('Arial','',11);

$pdf->MultiCell(
    0,
    8,
    texto($conclusion)
);

$pdf->Ln(15);


/* ======================================
   FIRMA
====================================== */

$pdf->Cell(0,10,'',0,1);

$pdf->Cell(
    0,
    8,
    texto('__________________________________'),
    0,
    1,
    'C'
);

$pdf->Cell(
    0,
    8,
    texto('Responsable del Sistema'),
    0,
    1,
    'C'
);


/* ======================================
   SALIDA
====================================== */

$pdf->Output(
    "I",
    "reporte_financiero.pdf"
);

exit;

?>