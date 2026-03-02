<?php
require_once '../config/Conexion.php';
require __DIR__ . '/../config/seguridad.php';

verificarRoles([1,3]);

$pdo = Conexion::conectar();

$id_usuario = $_SESSION['id_usuario'];
$id_rol     = $_SESSION['id_rol'];

$pagina = $_POST['pagina'] ?? 1;
$limite = 5;
$inicio = ($pagina - 1) * $limite;

$buscar = trim($_POST['buscar'] ?? '');
$desde  = $_POST['desde'] ?? '';
$hasta  = $_POST['hasta'] ?? '';

$where = " WHERE 1=1 ";
$params = [];

/* ==============================
   CONTROL POR ROL
============================== */
if($id_rol == 3){
    $where .= " AND g.id_usuario = :id_usuario ";
    $params[':id_usuario'] = $id_usuario;
}

/* ==============================
   FILTRO BUSCADOR
============================== */
if($buscar != ''){
    $where .= " AND g.concepto LIKE :buscar ";
    $params[':buscar'] = "%$buscar%";
}

/* ==============================
   FILTRO FECHAS
============================== */
if($desde != ''){
    $where .= " AND DATE(g.fecha) >= :desde ";
    $params[':desde'] = $desde;
}
if($hasta != ''){
    $where .= " AND DATE(g.fecha) <= :hasta ";
    $params[':hasta'] = $hasta;
}

/* ==============================
   TOTAL REGISTROS
============================== */
$sqlTotal = "SELECT COUNT(*) 
             FROM gastos g
             INNER JOIN usuarios u ON g.id_usuario = u.id_usuario
             $where";

$stmtTotal = $pdo->prepare($sqlTotal);
$stmtTotal->execute($params);
$totalRegistros = $stmtTotal->fetchColumn();
$totalPaginas = ceil($totalRegistros / $limite);

/* ==============================
   CONSULTA PRINCIPAL CON NOMBRE COMPLETO
============================== */
$sql = "SELECT g.*, CONCAT(u.primer_nombre, ' ', u.primer_apellido) AS nombre_completo
        FROM gastos g
        INNER JOIN usuarios u ON g.id_usuario = u.id_usuario
        $where
        ORDER BY g.id_gasto DESC
        LIMIT $inicio, $limite";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ==============================
   CONSTRUCCION DE LA TABLA HTML
============================== */
$tabla = "";

if(count($datos) == 0){
    $tabla .= "<tr>
        <td colspan='6' class='text-center'>No hay registros</td>
    </tr>";
}

foreach($datos as $d){
    $tabla .= "<tr>
        <td>{$d['id_gasto']}</td>
        <td>{$d['nombre_completo']}</td>
        <td>".htmlspecialchars($d['concepto'])."</td>
        <td>{$d['fecha']}</td>
        <td>$".number_format($d['total'],2)."</td>";

    if($id_rol == 1){
        $tabla .= "<td>
            <button onclick='eliminarGasto({$d['id_gasto']})'
            class='btn btn-danger btn-sm'>
            Eliminar
            </button>
        </td>";
    }

    $tabla .= "</tr>";
}

/* ==============================
   PAGINACION
============================== */
$paginacion = "";

if($pagina > 1){
    $anterior = $pagina - 1;
    $paginacion .= "<button onclick='cargarGastos($anterior)'>«</button>";
}

for($i=1; $i<=$totalPaginas; $i++){
    $clase = ($i == $pagina) ? "class='activo'" : "";
    $paginacion .= "<button onclick='cargarGastos($i)' $clase>$i</button>";
}

if($pagina < $totalPaginas){
    $siguiente = $pagina + 1;
    $paginacion .= "<button onclick='cargarGastos($siguiente)'>»</button>";
}

/* ==============================
   DEVOLVER DATOS JSON
============================== */
echo json_encode([
    "tabla"=>$tabla,
    "paginacion"=>$paginacion
]);