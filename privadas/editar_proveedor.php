<?php
session_start();
require '../config/Conexion.php';

/* ===== PROTECCIÓN ===== */
if (!isset($_SESSION['id_usuario']) || $_SESSION['id_rol'] != 1) {
    header("Location: ../public/index.php");
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

<link rel="stylesheet" href="../public/estilos/estilos.css">
<link rel="stylesheet" href="../public/estilos/encabezado.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.4/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>

<?php
$tipoMenu = "simple";
include("../views/navbar.php");
?>

<div class="main-content">

<h1>Proveedores</h1>

<div class="card card-table">

<table class="tabla-sistema">

<thead>
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
<span style="color:green;font-weight:bold;">Activo</span>
<?php else: ?>
<span style="color:red;font-weight:bold;">Inactivo</span>
<?php endif; ?>
</td>

<td>

<button class="btn-sistema btn-editar editBtn">
Modificar
</button>

<?php if($p['estado']==1): ?>

<button class="btn-sistema btn-eliminar estadoBtn" data-estado="0">
Inactivar
</button>

<?php else: ?>

<button class="btn-sistema btn-guardar estadoBtn" data-estado="1">
Activar
</button>

<?php endif; ?>

</td>

</tr>

<?php endforeach; ?>

</tbody>
</table>

</div>

</div>

<!-- ================= MODAL ================= -->

<div class="modal" id="modalProveedor">

<div class="modal-contenido">

<span class="cerrar">&times;</span>

<h2>Modificar proveedor</h2>

<form id="formProveedor">

<input type="hidden" name="id" id="prov_id">

<label>Nombre</label>
<input type="text" name="nombre" id="prov_nombre" class="form-control" required>

<label>Correo</label>
<input type="email" name="correo" id="prov_correo" class="form-control" required>

<label>Teléfono</label>
<input type="text" name="telefono" id="prov_telefono"
class="form-control"
oninput="this.value=this.value.replace(/[^0-9]/g,'')"
required>

<label>Dirección</label>
<input type="text" name="direccion" id="prov_direccion" class="form-control" required>

<button class="btn-sistema btn-guardar">
Guardar cambios
</button>

</form>

</div>


<script>

/* ===== MODAL ===== */

const modal = document.getElementById("modalProveedor");
const cerrar = document.querySelector(".cerrar");

cerrar.onclick = () => modal.style.display="none";

window.onclick = (e)=>{
if(e.target==modal){
modal.style.display="none";
}
}

/* ===== ABRIR MODAL ===== */

$('.editBtn').click(function(){

 let tr = $(this).closest('tr');

 $('#prov_id').val(tr.data('id'));
 $('#prov_nombre').val(tr.data('nombre'));
 $('#prov_correo').val(tr.data('correo'));
 $('#prov_telefono').val(tr.data('telefono'));
 $('#prov_direccion').val(tr.data('direccion'));

 modal.style.display="block";

});

/* ===== GUARDAR ===== */

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
<!-- ================= SEGURIDAD BOTON ATRAS ================= -->

<script>

let salirConfirmado = false;

window.addEventListener("popstate", function () {

Swal.fire({
title: "¿Quieres salir del panel?",
text: "Se cerrará tu sesión por seguridad.",
icon: "warning",
showCancelButton: true,
confirmButtonText: "Sí, salir",
cancelButtonText: "Cancelar"
}).then((result) => {

if (result.isConfirmed) {

salirConfirmado = true;
window.location.href = "../config/cerrar_sesion.php";

}else{

history.pushState(null, null, location.href);

}

});

});

history.pushState(null, null, location.href);

</script>

</body>
</html>