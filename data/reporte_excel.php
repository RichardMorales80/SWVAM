<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

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

/* ======================================
   FILTROS
====================================== */

$fecha_inicio = $_GET['fecha_inicio'] ?? '';
$fecha_fin    = $_GET['fecha_fin'] ?? '';

$params = [];

$filtroVentas = "";

if($fecha_inicio && $fecha_fin){

    $filtroVentas = "
    WHERE v.fecha BETWEEN :inicio AND :fin
    ";

    $params[':inicio'] = $fecha_inicio;
    $params[':fin']    = $fecha_fin;
}


/* ======================================
   VENTAS
====================================== */

$stmtV = $pdo->prepare("

SELECT 
v.fecha,
dv.descripcion,
dv.precio,
dv.cantidad,
dv.total

FROM detalle_venta dv

JOIN ventas v
ON v.id_venta = dv.id_venta

$filtroVentas

");

$stmtV->execute($params);

$ventas = $stmtV->fetchAll(PDO::FETCH_ASSOC);


/* ======================================
   GASTOS
====================================== */

$stmtG = $pdo->prepare("

SELECT 
fecha,
concepto,
total

FROM gastos

" . ($fecha_inicio && $fecha_fin
? "WHERE fecha BETWEEN :inicio AND :fin"
: "")

);

$stmtG->execute($params);

$gastos = $stmtG->fetchAll(PDO::FETCH_ASSOC);


/* ======================================
   INVENTARIO
====================================== */

$stmtI = $pdo->prepare("

SELECT
nombre,
cantidad,
precio,
(cantidad * precio) AS total

FROM productos

ORDER BY total DESC

");

$stmtI->execute();

$inventario = $stmtI->fetchAll(PDO::FETCH_ASSOC);


/* ======================================
   TOTALES
====================================== */

$totalVentas = array_sum(array_column($ventas,'total'));

$totalGastos = array_sum(array_column($gastos,'total'));

$balance = $totalVentas - $totalGastos;

$totalActivos = array_sum(array_column($inventario,'total'));

$totalRegistrosVentas = count($ventas);

$ticketPromedio = $totalRegistrosVentas > 0
? $totalVentas / $totalRegistrosVentas
: 0;

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
   CREAR EXCEL
====================================== */

$spreadsheet = new Spreadsheet();


/* ======================================
   HOJA RESUMEN
====================================== */

$sheet = $spreadsheet->getActiveSheet();

$sheet->setTitle('Resumen');


/* ===== LOGO ===== */

$logo = new Drawing();

$logo->setPath(__DIR__ . '/../public/imagenes/logo.png');

$logo->setHeight(80);

$logo->setCoordinates('A1');

$logo->setWorksheet($sheet);


/* ===== TITULO ===== */

$sheet->mergeCells('A5:F5');

$sheet->setCellValue(
    'A5',
    'REPORTE FINANCIERO GENERAL'
);

$sheet->getStyle('A5')
->getFont()
->setSize(16)
->setBold(true);

$sheet->getStyle('A5')
->getAlignment()
->setHorizontal('center');


/* ===== RESUMEN ===== */

$sheet->setCellValue('A8','Ventas Totales');
$sheet->setCellValue('B8',$totalVentas);

$sheet->setCellValue('A9','Gastos Totales');
$sheet->setCellValue('B9',$totalGastos);

$sheet->setCellValue('A10','Balance Neto');
$sheet->setCellValue('B10',$balance);

$sheet->setCellValue('A11','Margen de Ganancia');
$sheet->setCellValue('B11',$porcentajeGanancia.'%');

$sheet->setCellValue('A12','% Gastos');
$sheet->setCellValue('B12',$porcentajeGastos.'%');

$sheet->setCellValue('A13','Ticket Promedio');
$sheet->setCellValue('B13',$ticketPromedio);

$sheet->setCellValue('A14','Estado Financiero');
$sheet->setCellValue('B14',$estadoFinanciero);

$sheet->setCellValue('A15','Total Activos');
$sheet->setCellValue('B15',$totalActivos);

$sheet->getStyle('B8:B15')
->getNumberFormat()
->setFormatCode('"$"#,##0.00');


/* ======================================
   DATOS GRAFICA GENERAL
====================================== */

$sheet->setCellValue('D8','Concepto');
$sheet->setCellValue('E8','Monto');

$sheet->setCellValue('D9','Ventas');
$sheet->setCellValue('D10','Gastos');
$sheet->setCellValue('D11','Balance');
$sheet->setCellValue('D12','Activos');

$sheet->setCellValue('E9',$totalVentas);
$sheet->setCellValue('E10',$totalGastos);
$sheet->setCellValue('E11',$balance);
$sheet->setCellValue('E12',$totalActivos);


/* ======================================
   GRAFICA GENERAL
====================================== */

$dataSeriesLabels = [
new DataSeriesValues(
'String',
'Resumen!$E$8',
null,
1
),
];

$xAxisTickValues = [
new DataSeriesValues(
'String',
'Resumen!$D$9:$D$12',
null,
4
),
];

$dataSeriesValues = [
new DataSeriesValues(
'Number',
'Resumen!$E$9:$E$12',
null,
4
),
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

$legend = new Legend(
Legend::POSITION_RIGHT,
null,
false
);

$title = new Title(
'Comparacion Financiera'
);

$chart = new Chart(
'grafica_general',
$title,
$legend,
$plotArea
);

$chart->setTopLeftPosition('G8');

$chart->setBottomRightPosition('N25');

$sheet->addChart($chart);


/* ======================================
   HOJA VENTAS
====================================== */

$ventasSheet = $spreadsheet->createSheet();

$ventasSheet->setTitle('Ventas');

$ventasSheet->fromArray(

[
'Fecha',
'Descripcion',
'Precio',
'Cantidad',
'Total'
],

NULL,

'A1'

);

$fila = 2;

foreach($ventas as $v){

    $ventasSheet->setCellValue(
        "A$fila",
        $v['fecha']
    );

    $ventasSheet->setCellValue(
        "B$fila",
        $v['descripcion']
    );

    $ventasSheet->setCellValue(
        "C$fila",
        $v['precio']
    );

    $ventasSheet->setCellValue(
        "D$fila",
        $v['cantidad']
    );

    $ventasSheet->setCellValue(
        "E$fila",
        $v['total']
    );

    $fila++;
}

$ventasSheet->setCellValue(
    "D$fila",
    "TOTAL:"
);

$ventasSheet->setCellValue(
    "E$fila",
    "=SUM(E2:E".($fila-1).")"
);

$ventasSheet->getStyle("C2:E$fila")
->getNumberFormat()
->setFormatCode('"$"#,##0.00');


/* ===== DATOS GRAFICA VENTAS ===== */

$ventasSheet->setCellValue('G1','Producto');
$ventasSheet->setCellValue('H1','Total');

$grafFila = 2;

foreach($ventas as $v){

    $ventasSheet->setCellValue(
        "G$grafFila",
        $v['descripcion']
    );

    $ventasSheet->setCellValue(
        "H$grafFila",
        $v['total']
    );

    $grafFila++;
}


/* ===== GRAFICA VENTAS ===== */

$labelsVentas = [
new DataSeriesValues(
'String',
'Ventas!$G$1',
null,
1
),
];

$xVentas = [
new DataSeriesValues(
'String',
'Ventas!$G$2:$G$'.$grafFila,
null,
$grafFila
),
];

$valuesVentas = [
new DataSeriesValues(
'Number',
'Ventas!$H$2:$H$'.$grafFila,
null,
$grafFila
),
];

$seriesVentas = new DataSeries(
DataSeries::TYPE_PIECHART,
null,
range(0, count($valuesVentas)-1),
$labelsVentas,
$xVentas,
$valuesVentas
);

$plotVentas = new PlotArea(null, [$seriesVentas]);

$titleVentas = new Title(
'Grafica de Ventas'
);

$chartVentas = new Chart(
'grafica_ventas',
$titleVentas,
null,
$plotVentas
);

$chartVentas->setTopLeftPosition('J2');
$chartVentas->setBottomRightPosition('Q20');

$ventasSheet->addChart($chartVentas);


/* ======================================
   HOJA GASTOS
====================================== */

$gastosSheet = $spreadsheet->createSheet();

$gastosSheet->setTitle('Gastos');

$gastosSheet->fromArray(

[
'Fecha',
'Concepto',
'Total'
],

NULL,

'A1'

);

$fila = 2;

foreach($gastos as $g){

    $gastosSheet->setCellValue(
        "A$fila",
        $g['fecha']
    );

    $gastosSheet->setCellValue(
        "B$fila",
        $g['concepto']
    );

    $gastosSheet->setCellValue(
        "C$fila",
        $g['total']
    );

    $fila++;
}

$gastosSheet->setCellValue(
    "B$fila",
    "TOTAL:"
);

$gastosSheet->setCellValue(
    "C$fila",
    "=SUM(C2:C".($fila-1).")"
);

$gastosSheet->getStyle("C2:C$fila")
->getNumberFormat()
->setFormatCode('"$"#,##0.00');


/* ===== DATOS GRAFICA GASTOS ===== */

$gastosSheet->setCellValue('E1','Concepto');
$gastosSheet->setCellValue('F1','Monto');

$grafFila = 2;

foreach($gastos as $g){

    $gastosSheet->setCellValue(
        "E$grafFila",
        $g['concepto']
    );

    $gastosSheet->setCellValue(
        "F$grafFila",
        $g['total']
    );

    $grafFila++;
}


/* ===== GRAFICA GASTOS ===== */

$labelsGastos = [
new DataSeriesValues(
'String',
'Gastos!$E$1',
null,
1
),
];

$xGastos = [
new DataSeriesValues(
'String',
'Gastos!$E$2:$E$'.$grafFila,
null,
$grafFila
),
];

$valuesGastos = [
new DataSeriesValues(
'Number',
'Gastos!$F$2:$F$'.$grafFila,
null,
$grafFila
),
];

$seriesGastos = new DataSeries(
DataSeries::TYPE_BARCHART,
DataSeries::GROUPING_CLUSTERED,
range(0, count($valuesGastos)-1),
$labelsGastos,
$xGastos,
$valuesGastos
);

$plotGastos = new PlotArea(null, [$seriesGastos]);

$titleGastos = new Title(
'Grafica de Gastos'
);

$chartGastos = new Chart(
'grafica_gastos',
$titleGastos,
null,
$plotGastos
);

$chartGastos->setTopLeftPosition('H2');
$chartGastos->setBottomRightPosition('P20');

$gastosSheet->addChart($chartGastos);


/* ======================================
   HOJA INVENTARIO
====================================== */

$inventarioSheet = $spreadsheet->createSheet();

$inventarioSheet->setTitle('Inventario');

$inventarioSheet->fromArray(

[
'Producto',
'Cantidad',
'Precio',
'Valor Total'
],

NULL,

'A1'

);

$fila = 2;

foreach($inventario as $i){

    $inventarioSheet->setCellValue(
        "A$fila",
        $i['nombre']
    );

    $inventarioSheet->setCellValue(
        "B$fila",
        $i['cantidad']
    );

    $inventarioSheet->setCellValue(
        "C$fila",
        $i['precio']
    );

    $inventarioSheet->setCellValue(
        "D$fila",
        $i['total']
    );

    $fila++;
}

$inventarioSheet->setCellValue(
    "C$fila",
    "TOTAL ACTIVOS:"
);

$inventarioSheet->setCellValue(
    "D$fila",
    "=SUM(D2:D".($fila-1).")"
);

$inventarioSheet->getStyle("C2:D$fila")
->getNumberFormat()
->setFormatCode('"$"#,##0.00');


/* ===== DATOS GRAFICA INVENTARIO ===== */

$inventarioSheet->setCellValue('F1','Producto');
$inventarioSheet->setCellValue('G1','Valor');

$grafFila = 2;

foreach($inventario as $i){

    $inventarioSheet->setCellValue(
        "F$grafFila",
        $i['nombre']
    );

    $inventarioSheet->setCellValue(
        "G$grafFila",
        $i['total']
    );

    $grafFila++;
}


/* ===== GRAFICA INVENTARIO ===== */

$labelsInv = [
new DataSeriesValues(
'String',
'Inventario!$F$1',
null,
1
),
];

$xInv = [
new DataSeriesValues(
'String',
'Inventario!$F$2:$F$'.$grafFila,
null,
$grafFila
),
];

$valuesInv = [
new DataSeriesValues(
'Number',
'Inventario!$G$2:$G$'.$grafFila,
null,
$grafFila
),
];

$seriesInv = new DataSeries(
DataSeries::TYPE_BARCHART,
DataSeries::GROUPING_CLUSTERED,
range(0, count($valuesInv)-1),
$labelsInv,
$xInv,
$valuesInv
);

$plotInv = new PlotArea(null, [$seriesInv]);

$titleInv = new Title(
'Grafica Inventario'
);

$chartInv = new Chart(
'grafica_inventario',
$titleInv,
null,
$plotInv
);

$chartInv->setTopLeftPosition('I2');
$chartInv->setBottomRightPosition('Q20');

$inventarioSheet->addChart($chartInv);


/* ======================================
   HOJA EXPLICACION
====================================== */

$expSheet = $spreadsheet->createSheet();

$expSheet->setTitle('Explicacion');

$expSheet->fromArray(

[
[
'Indicador',
'Como se calcula',
'Para que sirve'
],

[
'Ventas Totales',
'Suma total de ventas',
'Permite conocer ingresos'
],

[
'Gastos Totales',
'Suma total de gastos',
'Permite conocer egresos'
],

[
'Balance Neto',
'Ventas - Gastos',
'Muestra ganancia o perdida'
],

[
'Margen de Ganancia',
'(Balance / Ventas) * 100',
'Mide rentabilidad'
],

[
'% Gastos',
'(Gastos / Ventas) * 100',
'Mide impacto de gastos'
],

[
'Ticket Promedio',
'Ventas / Numero de ventas',
'Muestra promedio por venta'
],

[
'Estado Financiero',
'Interpretacion automatica',
'Muestra situacion financiera'
],

[
'Activos',
'Cantidad * Precio',
'Muestra valor del inventario'
]

],

NULL,

'A1'

);

$expSheet->getStyle('A1:C1')
->getFont()
->setBold(true);


/* ======================================
   AUTO AJUSTAR COLUMNAS
====================================== */

foreach($spreadsheet->getAllSheets() as $hoja){

    foreach(range('A','Q') as $columna){

        $hoja->getColumnDimension($columna)
        ->setAutoSize(true);
    }
}


/* ======================================
   DESCARGAR
====================================== */

header(
'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
);

header(
'Content-Disposition: attachment;filename="Reporte_Financiero.xlsx"'
);

header(
'Cache-Control: max-age=0'
);

$writer = new Xlsx($spreadsheet);

$writer->setIncludeCharts(true);

$writer->save('php://output');

exit;

?>