<?php
session_start();
require_once '../config/Conexion.php';
require __DIR__ . '/../config/seguridad.php';

verificarRoles([1,3]);

if(!isset($_SESSION['id_usuario'], $_SESSION['id_rol'])){
    echo json_encode([
        "tabla" => "<tr><td colspan='6'>Sesión no válida</td></tr>",
        "paginacion" => "",
        "total" => 0
    ]);
    exit;
}

$pdo = Conexion::conectar();

$id_usuario = $_SESSION['id_usuario'];
$id_rol     = $_SESSION['id_rol'];

$pagina = isset($_POST['pagina']) ? (int)$_POST['pagina'] : 1;
$pagina = ($pagina > 0) ? $pagina : 1;

$limite = 5;
$inicio = ($pagina - 1) * $limite;

$buscar = trim($_POST['buscar'] ?? '');
$desde  = trim($_POST['desde'] ?? '');
$hasta  = trim($_POST['hasta'] ?? '');

$where = " WHERE 1=1 ";
$params = [];

/* CONTROL POR ROL */
if($id_rol == 3){
    $where .= " AND g.id_usuario = :id_usuario ";
    $params[':id_usuario'] = $id_usuario;
}

/* FILTRO BUSCADOR */
if($buscar !== ''){
    $where .= " AND g.concepto LIKE :buscar ";
    $params[':buscar'] = "%{$buscar}%";
}

/* FILTRO FECHAS */
if($desde !== ''){
    $where .= " AND DATE(g.fecha) >= :desde ";
    $params[':desde'] = $desde;
}

if($hasta !== ''){
    $where .= " AND DATE(g.fecha) <= :hasta ";
    $params[':hasta'] = $hasta;
}

/* TOTAL REGISTROS */
$sqlTotal = "SELECT COUNT(*) 
             FROM gastos g
             INNER JOIN usuarios u ON g.id_usuario = u.id_usuario
             $where";

$stmtTotal = $pdo->prepare($sqlTotal);
$stmtTotal->execute($params);
$totalRegistros = (int)$stmtTotal->fetchColumn();
$totalPaginas = ($totalRegistros > 0) ? ceil($totalRegistros / $limite) : 1;

/* CONSULTA PRINCIPAL */
$sql = "SELECT g.*, CONCAT(u.primer_nombre, ' ', u.primer_apellido) AS nombre_completo
        FROM gastos g
        INNER JOIN usuarios u ON g.id_usuario = u.id_usuario
        $where
        ORDER BY g.id_gasto DESC
        LIMIT $inicio, $limite";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* TOTAL SUMA */
$sqlSuma = "SELECT COALESCE(SUM(g.total),0)
            FROM gastos g
            INNER JOIN usuarios u ON g.id_usuario = u.id_usuario
            $where";

$stmtSuma = $pdo->prepare($sqlSuma);
$stmtSuma->execute($params);
$totalGastos = $stmtSuma->fetchColumn();

/* TABLA */
$tabla = "";
$colspan = ($id_rol == 1) ? 6 : 5;

if(empty($datos)){
    $tabla .= "<tr>
        <td colspan='{$colspan}' class='text-center'>No hay registros</td>
    </tr>";
} else {
    foreach($datos as $d){
        $tabla .= "<tr>";
        $tabla .= "<td>" . (int)$d['id_gasto'] . "</td>";
        $tabla .= "<td>" . htmlspecialchars($d['nombre_completo']) . "</td>";
        $tabla .= "<td>" . htmlspecialchars($d['concepto']) . "</td>";
        $tabla .= "<td>" . htmlspecialchars($d['fecha']) . "</td>";
        $tabla .= "<td>$" . number_format((float)$d['total'], 2) . "</td>";

        if($id_rol == 1){
            $tabla .= "<td>
                <button onclick='eliminarGasto(" . (int)$d['id_gasto'] . ")' class='btn btn-danger btn-sm'>
                    Eliminar
                </button>
            </td>";
        }

        $tabla .= "</tr>";
    }
}

/* PAGINACION */
$paginacion = "";

if($totalPaginas > 1){
    if($pagina > 1){
        $anterior = $pagina - 1;
        $paginacion .= "<button onclick='cargarGastos($anterior)'>«</button>";
    }

    for($i = 1; $i <= $totalPaginas; $i++){
        $clase = ($i == $pagina) ? "class='activo'" : "";
        $paginacion .= "<button onclick='cargarGastos($i)' $clase>$i</button>";
    }

    if($pagina < $totalPaginas){
        $siguiente = $pagina + 1;
        $paginacion .= "<button onclick='cargarGastos($siguiente)'>»</button>";
    }
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    "tabla" => $tabla,
    "paginacion" => $paginacion,
    "total" => (float)$totalGastos
]);