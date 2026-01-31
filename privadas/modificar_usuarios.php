<?php
session_start();
require '../config/Conexion.php';

/* 🔐 PROTECCIÓN: SOLO ADMIN */
if (!isset($_SESSION['id_usuario']) || $_SESSION['id_rol'] != 1) {
    header("Location: ../public/login.php");
    exit;
}

$db = Conexion::conectar();

/* OBTENER USUARIOS */
$sql = "
    SELECT 
        id_usuario,
        primer_nombre,
        primer_apellido,
        correo,
        telefono,
        lugar_residencia,
        id_rol
    FROM usuarios
";
$stmt = $db->prepare($sql);
$stmt->execute();
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Modificar Usuarios</title>

<link rel="stylesheet" href="../public/estilos/principal.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
</head>

<body>

<!-- NAV -->
<nav class="main_nav">
<ul class="menu">
    <li class="logo-item">
        <img src="../public/imagenes/logo1.png" class="logo">
    </li>
    <li><a href="../views/administrador.php" class="main_menu_link">Atrás</a></li>
    <li><a href="../config/cerrar_sesion.php" class="main_menu_link">Salir</a></li>
</ul>
</nav>
<br><br><br><br><br><br><br><br>

<div class="container mt-5">

<h2 class="text-center mb-4">Listado de Usuarios</h2>

<table class="table table-bordered table-striped">
<thead class="table-dark">
<tr>
    <th>ID</th>
    <th>Nombre</th>
    <th>Apellido</th>
    <th>Correo</th>
    <th>Teléfono</th>
    <th>Dirección</th>
    <th>Rol</th>
    <th>Acciones</th>
</tr>
</thead>
<tbody>

<?php foreach ($usuarios as $u): ?>
<tr>
    <td><?= $u['id_usuario'] ?></td>
    <td><?= $u['primer_nombre'] ?></td>
    <td><?= $u['primer_apellido'] ?></td>
    <td><?= $u['correo'] ?></td>
    <td><?= $u['telefono'] ?></td>
    <td><?= $u['lugar_residencia'] ?></td>
    <td><?= $u['id_rol'] == 1 ? 'Administrador' : 'Usuario' ?></td>
    <td>
        <button class="btn btn-primary btn-sm editBtn"
            data-id="<?= $u['id_usuario'] ?>"
            data-nombre="<?= $u['primer_nombre'] ?>"
            data-apellido="<?= $u['primer_apellido'] ?>"
            data-correo="<?= $u['correo'] ?>"
            data-telefono="<?= $u['telefono'] ?>"
            data-direccion="<?= $u['lugar_residencia'] ?>"
            data-rol="<?= $u['id_rol'] ?>"
        >
            Modificar
        </button>

        <button class="btn btn-danger btn-sm deleteBtn"
            data-id="<?= $u['id_usuario'] ?>">
            Eliminar
        </button>
    </td>
</tr>
<?php endforeach; ?>

</tbody>
</table>
</div>

<!-- MODAL MODIFICAR -->
<div class="modal fade" id="modalEditar" tabindex="-1">
<div class="modal-dialog">
<div class="modal-content">

<form action="../config/modifus.php" method="POST">
<div class="modal-header">
<h5 class="modal-title">Modificar Usuario</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">

<input type="hidden" name="id_usuario" id="edit_id">

<div class="row">
<div class="col-md-6">
<label>Nombre</label>
<input type="text" name="primer_nombre" id="edit_nombre" class="form-control" required>
</div>

<div class="col-md-6">
<label>Apellido</label>
<input type="text" name="primer_apellido" id="edit_apellido" class="form-control" required>
</div>
</div>

<label class="mt-2">Correo</label>
<input type="email" name="correo" id="edit_correo" class="form-control" required>

<div class="row mt-2">
<div class="col-md-6">
<label>Teléfono</label>
<input type="text" name="telefono" id="edit_telefono" class="form-control">
</div>

<div class="col-md-6">
<label>Rol</label>
<select name="id_rol" id="edit_rol" class="form-control">
<option value="1">Administrador</option>
<option value="2">Usuario</option>
</select>
</div>
</div>

<label class="mt-2">Dirección</label>
<input type="text" name="lugar_residencia" id="edit_direccion" class="form-control">

<label class="mt-2">Nueva contraseña (opcional)</label>
<input type="password" name="password" class="form-control">

</div>

<div class="modal-footer">
<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
<button type="submit" class="btn btn-success">Guardar cambios</button>
</div>

</form>
</div>
</div>
</div>

<!-- MODAL ELIMINAR -->
<div class="modal fade" id="modalEliminar" tabindex="-1">
<div class="modal-dialog">
<div class="modal-content">

<form action="../config/elimius.php" method="POST">
<div class="modal-header">
<h5 class="modal-title">Eliminar Usuario</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">
<input type="hidden" name="id_usuario" id="delete_id">
<p>¿Seguro que deseas eliminar este usuario?</p>
</div>

<div class="modal-footer">
<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
<button type="submit" class="btn btn-danger">Eliminar</button>
</div>

</form>
</div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
$('.editBtn').click(function(){
    $('#edit_id').val($(this).data('id'));
    $('#edit_nombre').val($(this).data('nombre'));
    $('#edit_apellido').val($(this).data('apellido'));
    $('#edit_correo').val($(this).data('correo'));
    $('#edit_telefono').val($(this).data('telefono'));
    $('#edit_direccion').val($(this).data('direccion'));
    $('#edit_rol').val($(this).data('rol'));

    new bootstrap.Modal(document.getElementById('modalEditar')).show();
});

$('.deleteBtn').click(function(){
    $('#delete_id').val($(this).data('id'));
    new bootstrap.Modal(document.getElementById('modalEliminar')).show();
});
</script>

</body>
</html>
