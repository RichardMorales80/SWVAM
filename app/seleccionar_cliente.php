<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
session_start();

require_once __DIR__ . '/../global/configuracion.php';
require_once __DIR__ . '/../config/Conexion.php';
require_once __DIR__ . '/../config/seguridad.php';

verificarRoles([1,2]);

$pdo = Conexion::conectar();

/* =========================
   SELECCIONAR CLIENTE
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id_cliente = $_POST['id_cliente'] ?? null;
    $nombre_cliente = $_POST['nombre_cliente'] ?? '';

    if ($id_cliente) {

        $_SESSION['id_cliente'] = (int)$id_cliente;
        $_SESSION['nombre_cliente'] = $nombre_cliente;

       header("Location: " . BASE_URL . "public/clientes.php");
        exit;
    }
}

/* =========================
   OBTENER CLIENTES
========================= */

//  SOLO CLIENTES
$sql = "SELECT * FROM usuarios WHERE id_rol = 2";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/../templetes/cabecera.php';
?>

<!-- ================= TOPBAR ================= -->
<div class="topbar">

    <div class="topbar-left">
        <h4>Seleccionar Cliente</h4>
    </div>

    <div class="topbar-user">
        <span class="usuario-nombre">
            <?= htmlspecialchars($_SESSION['nombre'] ?? 'Usuario') ?>
        </span>

        <img src="../public/imagenes/avatar.png" class="avatar" alt="Avatar">
    </div>

</div>

<div class="container mt-4">

<h3 style="text-align:center;">Lista de Clientes</h3>

<table class="table table-bordered table-striped">

<thead>
<tr>
    <th>Nombre</th>
    <th>Correo</th>
    <th>Acción</th>
</tr>
</thead>

<tbody>

<?php foreach($clientes as $cliente): ?>

<?php
// USAR CAMPO REAL (ajústalo si tu BD usa otro nombre)
$nombre = $cliente['usuario'] ?? 'Cliente';
$correo = $cliente['correo'] ?? $cliente['email'] ?? 'Sin correo';
$id = $cliente['id_usuario'] ?? $cliente['id'];
?>

<tr>
<td><?= htmlspecialchars($nombre) ?></td>
<td><?= htmlspecialchars($correo) ?></td>

<td>
<form method="post">

<input type="hidden" name="id_cliente" value="<?= $id ?>">
<input type="hidden" name="nombre_cliente" value="<?= htmlspecialchars($nombre) ?>">

<button class="btn btn-success">
Seleccionar
</button>

</form>
</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>