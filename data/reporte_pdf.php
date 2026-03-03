<?php
require_once __DIR__ . '/../config/Conexion.php';
require_once __DIR__ . '/../public/librerias/fpdf186/fpdf.php';

$pdo = Conexion::conectar();

$fecha_inicio = $_GET['fecha_inicio'] ?? '';
$fecha_fin    = $_GET['fecha_fin'] ?? '';

$condicion = "";
$params = [];

if($fecha_inicio && $fecha_fin){
    $condicion = " WHERE fecha BETWEEN :inicio AND :fin ";
    $params = [
        ':inicio' => $fecha_inicio,
        ':fin' => $fecha_fin
    ];
}

/* =========================
   TOTALES
========================= */
$sqlVentas = "SELECT COALESCE(SUM(total),0) FROM ventas $condicion";
$stmt = $pdo->prepare($sqlVentas);
$stmt->execute($params);
$totalVentas = (float)$stmt->fetchColumn();

$sqlGastos = "SELECT COALESCE(SUM(total),0) FROM gastos $condicion";
$stmt = $pdo->prepare($sqlGastos);
$stmt->execute($params);
$totalGastos = (float)$stmt->fetchColumn();

$balance = $totalVentas - $totalGastos;

/* =========================
   PRODUCTOS MÁS VENDIDOS
========================= */
$sqlProductos = "
    SELECT descripcion, SUM(cantidad) AS cantidad
    FROM ventas
    $condicion
    GROUP BY descripcion
    ORDER BY cantidad DESC
    LIMIT 5
";

$stmt = $pdo->prepare($sqlProductos);
$stmt->execute($params);
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* =========================
   FUNCIONES GRÁFICAS
========================= */

function graficaResumen($ventas,$gastos,$balance){
    $width = 500; $height = 320;
    $img = imagecreate($width,$height);

    $bg = imagecolorallocate($img,255,255,255);
    $azul = imagecolorallocate($img,52,152,219);
    $rojo = imagecolorallocate($img,231,76,60);
    $verde = imagecolorallocate($img,46,204,113);
    $negro = imagecolorallocate($img,0,0,0);

    imagestring($img,5,140,10,"Resumen Financiero",$negro);

    $max = max($ventas,$gastos,$balance,1);
    $barWidth = 80;
    $x = 80;

    $datos = [$ventas,$gastos,$balance];
    $colores = [$azul,$rojo,$verde];
    $labels = ['Ventas','Gastos','Balance'];

    foreach($datos as $i=>$valor){
        $barHeight = ($valor/$max)*180;
        imagefilledrectangle($img,$x,260-$barHeight,$x+$barWidth,260,$colores[$i]);
        imagestring($img,3,$x,270,$labels[$i],$negro);
        $x += 130;
    }

    imagepng($img,"grafica_resumen.png");
    imagedestroy($img);
}

function graficaProductos($productos){
    $width = 500; $height = 320;
    $img = imagecreate($width,$height);

    $bg = imagecolorallocate($img,255,255,255);
    $azul = imagecolorallocate($img,52,152,219);
    $negro = imagecolorallocate($img,0,0,0);

    imagestring($img,5,120,10,"Productos Mas Vendidos",$negro);

    if(empty($productos)){
        imagepng($img,"grafica_productos.png");
        imagedestroy($img);
        return;
    }

    $cantidades = array_map(fn($p)=>(float)$p['cantidad'],$productos);
    $max = max($cantidades);
    if($max<=0) $max=1;

    $barWidth = 50;
    $x = 60;

    foreach($productos as $p){
        $cantidad = (float)$p['cantidad'];
        $barHeight = ($cantidad/$max)*180;

        imagefilledrectangle($img,$x,260-$barHeight,$x+$barWidth,260,$azul);
        imagestring($img,2,$x,270,substr($p['descripcion'],0,8),$negro);

        $x += 80;
    }

    imagepng($img,"grafica_productos.png");
    imagedestroy($img);
}

function graficaPastel($ventas,$gastos){
    $width = 400; $height = 320;
    $img = imagecreate($width,$height);

    $bg = imagecolorallocate($img,255,255,255);
    $azul = imagecolorallocate($img,52,152,219);
    $rojo = imagecolorallocate($img,231,76,60);
    $negro = imagecolorallocate($img,0,0,0);

    imagestring($img,5,80,10,"Distribucion Financiera",$negro);

    $total = $ventas + $gastos;
    if($total==0) $total=1;

    $anguloVentas = ($ventas/$total)*360;

    imagefilledarc($img,200,170,200,200,0,$anguloVentas,$azul,IMG_ARC_PIE);
    imagefilledarc($img,200,170,200,200,$anguloVentas,360,$rojo,IMG_ARC_PIE);

    imagefilledrectangle($img,50,280,65,295,$azul);
    imagestring($img,3,70,280,"Ventas",$negro);

    imagefilledrectangle($img,150,280,165,295,$rojo);
    imagestring($img,3,170,280,"Gastos",$negro);

    imagepng($img,"grafica_pastel.png");
    imagedestroy($img);
}

/* GENERAR GRÁFICAS */
graficaResumen($totalVentas,$totalGastos,$balance);
graficaProductos($productos);
graficaPastel($totalVentas,$totalGastos);

/* =========================
   PDF
========================= */

class PDF extends FPDF {

    function Header(){

        $logoPath = __DIR__ . '/../public/imagenes/logo.png';

        if(file_exists($logoPath)){
            $this->Image($logoPath, 10, 8, 25);
        }

        $this->SetFont('Arial','B',16);
        $this->Cell(0,10,'REPORTE FINANCIERO',0,1,'C');

        $this->Line(10,28,200,28);
        $this->Ln(15);
    }
}

$pdf = new PDF();

/* Página 1 */
$pdf->AddPage();
$pdf->SetFont('Arial','B',12);
$pdf->Cell(0,8,"Periodo: $fecha_inicio al $fecha_fin",0,1);
$pdf->Ln(5);
$pdf->Image("grafica_resumen.png",10,60,180);

/* Página 2 */
$pdf->AddPage();
$pdf->Image("grafica_productos.png",10,50,180);

/* Página 3 */
$pdf->AddPage();
$pdf->Image("grafica_pastel.png",40,60,130);

$pdf->Output("D","reporte_financiero.pdf");

/* Eliminar imágenes temporales */
unlink("grafica_resumen.png");
unlink("grafica_productos.png");
unlink("grafica_pastel.png");

exit;