<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/Conexion.php';
require_once __DIR__ . '/../config/seguridad.php';

verificarRol(1);

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Title;

$pdo = Conexion::conectar();

/* =========================
   FILTRO POR FECHA
========================= */

$fecha_inicio = $_GET['fecha_inicio'] ?? '';
$fecha_fin    = $_GET['fecha_fin'] ?? '';

$condicion = "";
$params = [];

if($fecha_inicio && $fecha_fin){
    $condicion = " WHERE fecha BETWEEN :inicio AND :fin ";
    $params[':inicio'] = $fecha_inicio;
    $params[':fin'] = $fecha_fin;
}

/* =========================
   OBTENER DATOS
========================= */

$stmtV = $pdo->prepare("SELECT fecha, descripcion, precio, cantidad, total FROM ventas $condicion");
$stmtV->execute($params);
$ventas = $stmtV->fetchAll(PDO::FETCH_ASSOC);

$stmtG = $pdo->prepare("SELECT fecha, concepto, total FROM gastos $condicion");
$stmtG->execute($params);
$gastos = $stmtG->fetchAll(PDO::FETCH_ASSOC);

/* =========================
   TOTALES
========================= */

$totalVentas = array_sum(array_column($ventas,'total'));
$totalGastos = array_sum(array_column($gastos,'total'));
$balance = $totalVentas - $totalGastos;

/* =========================
   CREAR EXCEL
========================= */

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Resumen');

/* =========================
   LOGO
========================= */

$logo = new Drawing();
$logo->setPath(__DIR__ . '/../public/imagenes/logo.png');
$logo->setHeight(80);
$logo->setCoordinates('A1');
$logo->setWorksheet($sheet);

/* =========================
   TITULO
========================= */

$sheet->mergeCells('A5:D5');
$sheet->setCellValue('A5','REPORTE FINANCIERO GENERAL');
$sheet->getStyle('A5')->getFont()->setSize(16)->setBold(true);
$sheet->getStyle('A5')->getAlignment()->setHorizontal('center');

/* Línea divisora */
$sheet->mergeCells('A6:D6');
$sheet->getStyle('A6:D6')->getBorders()->getBottom()->setBorderStyle('thin');

/* =========================
   RESUMEN
========================= */

$sheet->setCellValue('A8','Total Ventas');
$sheet->setCellValue('B8',$totalVentas);

$sheet->setCellValue('A9','Total Gastos');
$sheet->setCellValue('B9',$totalGastos);

$sheet->setCellValue('A10','Balance');
$sheet->setCellValue('B10',$balance);

$sheet->getStyle('B8:B10')
      ->getNumberFormat()
      ->setFormatCode('"$"#,##0.00');

/* =========================
   DATOS PARA GRAFICA
========================= */

$sheet->setCellValue('A12','Concepto');
$sheet->setCellValue('A13','Ventas');
$sheet->setCellValue('A14','Gastos');

$sheet->setCellValue('B13',$totalVentas);
$sheet->setCellValue('B14',$totalGastos);

$dataSeriesLabels = [
    new DataSeriesValues('String', 'Resumen!$A$12', null, 1),
];

$xAxisTickValues = [
    new DataSeriesValues('String', 'Resumen!$A$13:$A$14', null, 2),
];

$dataSeriesValues = [
    new DataSeriesValues('Number', 'Resumen!$B$13:$B$14', null, 2),
];

$series = new DataSeries(
    DataSeries::TYPE_BARCHART,
    DataSeries::GROUPING_CLUSTERED,
    range(0, count($dataSeriesValues)-1),
    $dataSeriesLabels,
    $xAxisTickValues,
    $dataSeriesValues
);

$plotArea = new PlotArea(null, [$series]);
$legend = new Legend(Legend::POSITION_RIGHT, null, false);
$title = new Title('Comparación Ventas vs Gastos');

$chart = new Chart(
    'grafica',
    $title,
    $legend,
    $plotArea
);

$chart->setTopLeftPosition('D12');
$chart->setBottomRightPosition('L25');
$sheet->addChart($chart);

/* =========================
   HOJA VENTAS
========================= */

$ventasSheet = $spreadsheet->createSheet();
$ventasSheet->setTitle('Ventas');

$ventasSheet->fromArray(
    ['Fecha','Descripción','Precio','Cantidad','Total'],
    NULL,
    'A1'
);

$fila = 2;
foreach($ventas as $v){
    $ventasSheet->setCellValue("A$fila",$v['fecha']);
    $ventasSheet->setCellValue("B$fila",$v['descripcion']);
    $ventasSheet->setCellValue("C$fila",$v['precio']);
    $ventasSheet->setCellValue("D$fila",$v['cantidad']);
    $ventasSheet->setCellValue("E$fila",$v['total']);
    $fila++;
}

$ventasSheet->setCellValue("D$fila","TOTAL:");
$ventasSheet->setCellValue("E$fila","=SUM(E2:E".($fila-1).")");
$ventasSheet->getStyle("E2:E$fila")
            ->getNumberFormat()
            ->setFormatCode('"$"#,##0.00');

/* =========================
   HOJA GASTOS
========================= */

$gastosSheet = $spreadsheet->createSheet();
$gastosSheet->setTitle('Gastos');

$gastosSheet->fromArray(
    ['Fecha','Concepto','Total'],
    NULL,
    'A1'
);

$fila = 2;
foreach($gastos as $g){
    $gastosSheet->setCellValue("A$fila",$g['fecha']);
    $gastosSheet->setCellValue("B$fila",$g['concepto']);
    $gastosSheet->setCellValue("C$fila",$g['total']);
    $fila++;
}

$gastosSheet->setCellValue("B$fila","TOTAL:");
$gastosSheet->setCellValue("C$fila","=SUM(C2:C".($fila-1).")");
$gastosSheet->getStyle("C2:C$fila")
            ->getNumberFormat()
            ->setFormatCode('"$"#,##0.00');

/* =========================
   HOJA BALANCE
========================= */

$balanceSheet = $spreadsheet->createSheet();
$balanceSheet->setTitle('Balance');

$balanceSheet->setCellValue('A1','Balance General');
$balanceSheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

$balanceSheet->setCellValue('A3','Ventas');
$balanceSheet->setCellValue('B3',$totalVentas);

$balanceSheet->setCellValue('A4','Gastos');
$balanceSheet->setCellValue('B4',$totalGastos);

$balanceSheet->setCellValue('A5','Balance');
$balanceSheet->setCellValue('B5',$balance);

$balanceSheet->getStyle('B3:B5')
             ->getNumberFormat()
             ->setFormatCode('"$"#,##0.00');

/* =========================
   DESCARGAR
========================= */

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Reporte_Financiero.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->setIncludeCharts(true);
$writer->save('php://output');
exit;