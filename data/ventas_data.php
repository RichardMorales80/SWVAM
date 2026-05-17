<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config/Conexion.php';
require __DIR__ . '/../config/seguridad.php';

try {

    verificarRoles([1,3]);

    $pdo = Conexion::conectar();

    $pagina = $_POST['pagina'] ?? 1;

    $limite = 5;
    $offset = ($pagina - 1) * $limite;

    /* ================= FILTROS ================= */

    $buscar = $_POST['buscar'] ?? '';
    $inicio = $_POST['inicio'] ?? '';
    $fin    = $_POST['fin'] ?? '';

    $where = [];
    $params = [];

    /* BUSCAR */
    if (!empty($buscar)) {
        $where[] = "d.descripcion LIKE :buscar";
        $params[':buscar'] = "%$buscar%";
    }

    /* FECHAS */
    if (!empty($inicio) && !empty($fin)) {
        $where[] = "v.fecha BETWEEN :inicio AND :fin";
        $params[':inicio'] = $inicio . " 00:00:00";
        $params[':fin']    = $fin . " 23:59:59";
    }

    /* ARMAR WHERE */
    $whereSQL = "";
    if (!empty($where)) {
        $whereSQL = "WHERE " . implode(" AND ", $where);
    }

    /* ================= CONSULTA ================= */

    $sql = "
    SELECT 
        v.id_venta,
        v.estado_pago,
        d.descripcion,
        d.precio,
        d.cantidad,
        d.total,
        v.fecha
    FROM ventas v
    INNER JOIN detalle_venta d ON v.id_venta = d.id_venta
    $whereSQL
    ORDER BY v.fecha DESC
    LIMIT $limite OFFSET $offset
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    $ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    /* ================= TOTAL ================= */

    $sqlTotal = "
    SELECT SUM(d.total) as total
    FROM detalle_venta d
    INNER JOIN ventas v ON v.id_venta = d.id_venta
    $whereSQL
    ";

    $stmtTotal = $pdo->prepare($sqlTotal);
    $stmtTotal->execute($params);

    $total = $stmtTotal->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

    /* ================= TABLA ================= */

    $tabla = "";

    if ($ventas) {

        foreach ($ventas as $v) {

            $estado = $v['estado_pago'] ?? 'pendiente';

            $badge = ($estado == 'pagado')
                ? "<span style='color:green;font-weight:bold;'>Pagado</span>"
                : "<span style='color:orange;font-weight:bold;'>Pendiente</span>";

            $boton = "";

            if ($estado == 'pendiente') {
                $boton = "
                <form method='POST' action='../privadas/confirmar_pago.php'>
                    <input type='hidden' name='id_venta' value='{$v['id_venta']}'>
                    <button class='btn btn-success btn-sm'>Confirmar</button>
                </form>
                ";
            } else {
                $boton = "<span style='color:green;'>✔</span>";
            }

            $tabla .= "
            <tr>
                <td>{$v['id_venta']}</td>
                <td>{$v['descripcion']}</td>
                <td>{$v['descripcion']}</td>
                <td>$".number_format($v['precio'],2)."</td>
                <td>{$v['cantidad']}</td>
                <td><strong>$".number_format($v['total'],2)."</strong></td>
                <td>".date("d/m/Y H:i", strtotime($v['fecha']))."</td>
                <td>$badge</td>
                <td>$boton</td>
            </tr>
            ";
        }

    } else {

        $tabla = "<tr><td colspan='9'>No hay ventas</td></tr>";

    }

    /* ================= PAGINACION ================= */

    $paginacion = "";

    for ($i=1; $i<=5; $i++) {
        $paginacion .= "<button onclick='cargarVentas($i)'>$i</button>";
    }

    /* ================= RESPUESTA ================= */

    echo json_encode([
        "tabla" => $tabla,
        "paginacion" => $paginacion,
        "gran_total" => $total
    ]);

} catch (Throwable $e) {

    echo json_encode([
        "tabla" => "<tr><td colspan='9'>Error</td></tr>",
        "paginacion" => "",
        "gran_total" => 0,
        "error" => $e->getMessage()
    ]);

}