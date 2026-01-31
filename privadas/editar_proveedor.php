<?php
session_start();
require '../config/Conexion.php';

/* ===== PROTECCIÓN ===== */
if (!isset($_SESSION['id_usuario']) || $_SESSION['id_rol'] != 1) {
    header("Location: ../public/login.php");
    exit;
}

$db = Conexion::conectar();

/* ===== CONSULTA ===== */
$stmt = $db->query("SELECT * FROM proveedores ORDER BY estado DESC, id_proveedor DESC");
$proveedores = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Editar Proveedores</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../public/estilos/principal.css">

<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.4/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>

<!-- ===== MENU ===== -->
<ul class="menu">
    <li><a href="../views/administrador.php">Atrás</a></li>
    <li><a href="../config/cerrar_sesion.php">Salir</a></li>
</ul>

<br><br><br>

<div class="container mt-5">

<h2 class="mb-3">Proveedores</h2>

<table class="table table-bordered table-hover align-middle">

<thead class="table-dark">
<tr>
    <th>ID</th>
    <th>Nombre</th>
    <th>Correo</th>
    <th>Teléfono</th>
    <th>Dirección</th>
    <th>Estado</th>
    <th>Acciones</th>
</tr>
</thead>

<tbody>

<?php foreach($proveedores as $p): ?>

<tr
 data-id="<?= $p['id_proveedor'] ?>"
 data-nombre="<?= htmlspecialchars($p['nombre'],ENT_QUOTES) ?>"
 data-correo="<?= htmlspecialchars($p['correo'],ENT_QUOTES) ?>"
 data-telefono="<?= $p['telefono'] ?>"
 data-direccion="<?= htmlspecialchars($p['direccion'],ENT_QUOTES) ?>"
>

<td><?= $p['id_proveedor'] ?></td>
<td><?= htmlspecialchars($p['nombre']) ?></td>
<td><?= htmlspecialchars($p['correo']) ?></td>
<td><?= $p['telefono'] ?></td>
<td><?= htmlspecialchars($p['direccion']) ?></td>

<td>
<?php if($p['estado']==1): ?>
<span class="badge bg-success">Activo</span>
<?php else: ?>
<span class="badge bg-danger">Inactivo</span>
<?php endif; ?>
</td>

<td>

<button type="button" class="btn btn-primary btn-sm editBtn">
Modificar
</button>

<?php if($p['estado']==1): ?>

<button type="button" class="btn btn-danger btn-sm estadoBtn" data-estado="0">
Inactivar
</button>

<?php else: ?>

<button type="button" class="btn btn-success btn-sm estadoBtn" data-estado="1">
Activar
</button>

<?php endif; ?>

</td>

</tr>

<?php endforeach; ?>

</tbody>
</table>

</div>

<!-- ================= MODAL ================= -->

<div class="modal fade" id="modalProveedor">
<div class="modal-dialog">
<div class="modal-content">

<div class="modal-header">
<h5 class="modal-title">Modificar proveedor</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">

<form id="formProveedor">

<input type="hidden" name="id" id="prov_id">

<label>Nombre</label>
<input type="text" name="nombre" id="prov_nombre" class="form-control mb-2" required>

<label>Correo</label>
<input type="email" name="correo" id="prov_correo" class="form-control mb-2" required>

<label>Teléfono</label>
<input type="text" name="telefono" id="prov_telefono"
class="form-control mb-2"
oninput="this.value=this.value.replace(/[^0-9]/g,'')"
required>

<label>Dirección</label>
<input type="text" name="direccion" id="prov_direccion" class="form-control mb-3" required>

<button class="btn btn-success w-100">
Guardar cambios
</button>

</form>

</div>
</div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/js/bootstrap.bundle.min.js"></script>

<script>

/* ===== ABRIR MODAL ===== */
$('.editBtn').click(function(){

 let tr = $(this).closest('tr');

 $('#prov_id').val(tr.data('id'));
 $('#prov_nombre').val(tr.data('nombre'));
 $('#prov_correo').val(tr.data('correo'));
 $('#prov_telefono').val(tr.data('telefono'));
 $('#prov_direccion').val(tr.data('direccion'));

 new bootstrap.Modal(document.getElementById('modalProveedor')).show();
});

/* ===== GUARDAR CAMBIOS ===== */
$('#formProveedor').submit(function(e){

 e.preventDefault();

 $.ajax({
   url:'../privadas/update_proveedor.php',
   type:'POST',
   data: $(this).serialize(),

   success:function(){
     Swal.fire('Éxito','Proveedor actualizado','success')
     .then(()=>location.reload());
   },

   error:function(){
     Swal.fire('Error','No se pudo actualizar','error');
   }
 });

});

/* ===== ACTIVAR / INACTIVAR ===== */
$('.estadoBtn').click(function(){

 let tr = $(this).closest('tr');
 let id = tr.data('id');
 let estado = $(this).data('estado');

 Swal.fire({
   title:'¿Seguro?',
   text: estado==0 ? 'Se inactivará el proveedor' : 'Se activará el proveedor',
   icon:'warning',
   showCancelButton:true,
   confirmButtonText:'Sí'
 }).then((r)=>{

   if(r.isConfirmed){

     $.post('../privadas/proveedor_estado.php',{
       id:id,
       estado:estado
     },function(){

        Swal.fire('Listo','Estado actualizado','success')
        .then(()=>location.reload());

     });

   }

 });

});

</script>

</body>
</html>
