<?php
require_once '../config/Conexion.php';
require_once '../libs/tcpdf/tcpdf.php';

$pdo = Conexion::conectar();

$fecha_inicio = $_GET['fecha_inicio'] ?? '';
$fecha_fin = $_GET['fecha_fin'] ?? '';

$whereVentas = "";
$whereGastos = "";
$params=[];

if($fecha_inicio && $fecha_fin){
    $whereVentas=" AND v.fecha BETWEEN :inicio AND :fin ";
    $whereGastos=" AND g.fecha BETWEEN :inicio AND :fin ";
    $params[':inicio']=$fecha_inicio;
    $params[':fin']=$fecha_fin;
}

$sql="
SELECT 
u.primer_nombre,
u.primer_apellido,
COALESCE(SUM(v.total),0) ventas,
COALESCE(SUM(g.total),0) gastos,
(COALESCE(SUM(v.total),0)-COALESCE(SUM(g.total),0)) balance
FROM usuarios u
LEFT JOIN ventas v ON u.id_usuario=v.id_usuario $whereVentas
LEFT JOIN gastos g ON u.id_usuario=g.id_usuario $whereGastos
GROUP BY u.id_usuario
";

$stmt=$pdo->prepare($sql);
$stmt->execute($params);
$data=$stmt->fetchAll(PDO::FETCH_ASSOC);

$pdf=new TCPDF();
$pdf->AddPage();

$html="<h2>Reporte Financiero</h2>";

if(count($data)==0){

$html.="<p>No hay datos en este rango</p>";

}else{

$html.="<table border='1' cellpadding='5'>
<tr>
<th>Usuario</th>
<th>Ventas</th>
<th>Gastos</th>
<th>Balance</th>
</tr>";

foreach($data as $r){

$html.="<tr>
<td>{$r['primer_nombre']} {$r['primer_apellido']}</td>
<td>$".number_format($r['ventas'],2)."</td>
<td>$".number_format($r['gastos'],2)."</td>
<td>$".number_format($r['balance'],2)."</td>
</tr>";

}

$html.="</table>";
}

$pdf->writeHTML($html);
$pdf->Output("reporte.pdf","I");
