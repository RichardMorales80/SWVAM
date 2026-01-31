<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config/Conexion.php';

$pdo = Conexion::conectar();

$mensaje = "";

/* ==========================
   VALIDAR SESIÓN
========================== */

if(!isset($_SESSION['id_usuario'])){
    header("Location: ../views/login.php");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];

/* ==========================
   GUARDAR GASTO
========================== */

if(isset($_POST['btnaccion']) && $_POST['btnaccion'] === 'guardar'){

    $concepto = trim($_POST['concepto']);
    $total    = floatval($_POST['total']);
    $fecha    = date('Y-m-d');

    if($concepto == "" || $total <= 0){

        $_SESSION['msg'] = 'error';

    } else {

        $sql = "INSERT INTO gastos (id_usuario, concepto, fecha, total)
                VALUES (:id_usuario, :concepto, :fecha, :total)";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ':id_usuario' => $id_usuario,
            ':concepto'   => $concepto,
            ':fecha'      => $fecha,
            ':total'      => $total
        ]);

        $_SESSION['msg'] = 'ok';
    }

    header("Location: gastos.php");
    exit();
}

/* ==========================
   MENSAJES
========================== */

if(isset($_SESSION['msg'])){

    if($_SESSION['msg'] == 'ok'){
        $mensaje = "Swal.fire({
            icon:'success',
            title:'Gasto guardado correctamente',
            timer:1800,
            showConfirmButton:false
        })";
    }

    if($_SESSION['msg'] == 'error'){
        $mensaje = "Swal.fire({
            icon:'error',
            title:'Completa todos los campos',
            timer:1800,
            showConfirmButton:false
        })";
    }

    unset($_SESSION['msg']);
}

/* ==========================
   CONSULTAR GASTOS USUARIO
========================== */

$sql = "SELECT * FROM gastos 
        WHERE id_usuario = :id_usuario
        ORDER BY id_gasto DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([':id_usuario' => $id_usuario]);
$gastos = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ==========================
   TOTAL GASTOS USUARIO
========================== */

$sqlTotal = "SELECT SUM(total) AS total_gastos 
             FROM gastos 
             WHERE id_usuario = :id_usuario";

$stmtTotal = $pdo->prepare($sqlTotal);
$stmtTotal->execute([':id_usuario' => $id_usuario]);
$resTotal = $stmtTotal->fetch(PDO::FETCH_ASSOC);

$granTotal = $resTotal['total_gastos'] ?? 0;
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Gastos</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<link rel="stylesheet" href="../public/estilos/principal.css">
</head>

<body>

<!-- ================= NAV ================= -->

<nav class="main_nav">
<ul class="menu">
    <li class="logo-item">
        <img src="../public/imagenes/logo1.png" class="logo">
    </li>
    <li><a href="../views/administrador.php" class="main_menu_link">Atrás</a></li>
    <li><a href="../config/cerrar_sesion.php" class="main_menu_link">Salir</a></li>
</ul>
</nav>

<br><br><br><br><br><br><br>

<h3 class="text-center mb-4">Registro de Gastos</h3>

<!-- ================= FORM ================= -->

<form method="post" class="row g-3 mb-4 container">

    <div class="col-md-6">
        <input type="text"
               name="concepto"
               class="form-control"
               placeholder="Concepto del gasto"
               required>
    </div>

    <div class="col-md-4">
        <input type="number"
               step="0.01"
               name="total"
               class="form-control"
               placeholder="Total"
               required>
    </div>

    <div class="col-md-2 d-grid">
        <button type="submit"
                name="btnaccion"
                value="guardar"
                class="btn btn-primary">
            Guardar
        </button>
    </div>

</form>

<!-- ================= TABLA ================= -->

<div class="container">

<table class="table table-bordered table-striped">

<thead class="table-dark">
<tr>
    <th>ID</th>
    <th>Concepto</th>
    <th>Fecha</th>
    <th>Total</th>
</tr>
</thead>

<tbody>

<?php if(count($gastos) == 0): ?>

<tr>
    <td colspan="4" class="text-center">No hay gastos registrados</td>
</tr>

<?php endif; ?>

<?php foreach($gastos as $g): ?>

<tr>
    <td><?= $g['id_gasto'] ?></td>
    <td><?= htmlspecialchars($g['concepto']) ?></td>
    <td><?= $g['fecha'] ?></td>
    <td>$<?= number_format($g['total'],2) ?></td>
</tr>

<?php endforeach; ?>

</tbody>

</table>

<!-- ================= TOTAL ================= -->

<div class="alert alert-info text-end fw-bold fs-5">
    Total de gastos: $<?= number_format($granTotal,2) ?>
</div>

</div>

<!-- ================= SWEET ================= -->

<?php if(!empty($mensaje)): ?>
<script>
<?= $mensaje ?>
</script>
<?php endif; ?>

</body>
</html>

<?php include '../templetes/pie.php'; ?>

