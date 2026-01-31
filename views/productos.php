<?php
session_start();

ini_set('display_errors',1);
error_reporting(E_ALL);

if (!isset($_SESSION['nombre'])) {
    header("Location: ../public/login.php");
    exit;
}

require '../config/Conexion.php';
include '../config/inactividad.php';

$db = Conexion::conectar();

/* ================= BUSCADOR ================= */

$buscar = $_GET['buscar'] ?? '';

if($buscar != ''){

    $sql = "SELECT id_producto, nombre, descripcion, precio, cantidad, imagen, estado
            FROM productos
            WHERE nombre LIKE :busca
               OR descripcion LIKE :busca
            ORDER BY id_producto DESC";

    $stmt = $db->prepare($sql);
    $stmt->bindValue(':busca','%'.$buscar.'%');

}else{

    $sql = "SELECT id_producto, nombre, descripcion, precio, cantidad, imagen, estado
            FROM productos
            ORDER BY id_producto DESC";

    $stmt = $db->prepare($sql);
}

$stmt->execute();
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Editar productos</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../public/estilos/principal.css">

<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.4/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>

<ul class="menu">
<li><a href="../views/administrador.php">Atrás</a></li>
<li><a href="../config/cerrar_sesion.php">Salir</a></li>
</ul>
<br><br><br><br><br>

<div class="container mt-5">

<h2>Productos</h2>

<!-- ================= BUSCADOR ================= -->

<form method="GET" class="row mb-3">

<div class="col-md-4">
<input type="text" 
name="buscar" 
class="form-control" 
placeholder="Buscar por nombre o descripción"
value="<?= htmlspecialchars($buscar) ?>">
</div>

<div class="col-md-2">
<button class="btn btn-primary">Buscar</button>
</div>

<div class="col-md-2">
<a href="../views/productos.php" class="btn btn-secondary">Limpiar</a>
</div>

</form>

<!-- ================= TABLA ================= -->

<table class="table table-bordered table-hover align-middle">

<thead class="table-dark">
<tr>
<th>Imagen</th>
<th>ID</th>
<th>Nombre</th>
<th>Precio</th>
<th>Descripción</th>
<th>Cantidad</th>
<th>Estado</th>
<th>Acciones</th>
</tr>
</thead>

<tbody>

<?php foreach($productos as $p): ?>

<?php
$rutaFisica = __DIR__.'/../uploads/'.$p['imagen'];
$rutaPublica = '../uploads/'.$p['imagen'];

$img = (!empty($p['imagen']) && file_exists($rutaFisica)) 
        ? $rutaPublica 
        : '../uploads/default.png';
?>

<tr
data-id="<?= $p['id_producto'] ?>"
data-nombre="<?= htmlspecialchars($p['nombre'],ENT_QUOTES) ?>"
data-precio="<?= $p['precio'] ?>"
data-descripcion="<?= htmlspecialchars($p['descripcion'],ENT_QUOTES) ?>"
data-cantidad="<?= $p['cantidad'] ?>"
data-estado="<?= $p['estado'] ?>"
>

<td><img src="<?= $img ?>" width="70"></td>
<td><?= $p['id_producto'] ?></td>
<td><?= htmlspecialchars($p['nombre']) ?></td>
<td>$<?= number_format($p['precio'],2) ?></td>
<td><?= htmlspecialchars($p['descripcion']) ?></td>
<td><?= $p['cantidad'] ?></td>

<td>
<?php if($p['estado']==1): ?>
<span class="badge bg-success">Activo</span>
<?php else: ?>
<span class="badge bg-danger">Inactivo</span>
<?php endif; ?>
</td>

<td>
<button class="btn btn-primary btn-sm editBtn">Modificar</button>

<button class="btn btn-warning btn-sm toggleBtn"
data-id="<?= $p['id_producto'] ?>"
data-estado="<?= $p['estado'] ?>">
<?= $p['estado']==1 ? 'Inactivar' : 'Activar' ?>
</button>
</td>

</tr>

<?php endforeach; ?>

<?php if(count($productos)==0): ?>
<tr>
<td colspan="8" class="text-center text-muted">
No se encontraron productos
</td>
</tr>
<?php endif; ?>

</tbody>
</table>
</div>

<!-- ================= MODAL ================= -->

<div class="modal fade" id="modalModificar">
<div class="modal-dialog">
<div class="modal-content">

<div class="modal-header">
<h5>Modificar producto</h5>
<button class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">

<form id="formModificar" enctype="multipart/form-data">

<input type="hidden" name="id" id="prod_id">

<label>Nombre</label>
<input type="text" name="nombre" id="prod_nombre" class="form-control mb-2">

<label>Precio</label>
<input type="number" step="0.01" name="precio" id="prod_precio" class="form-control mb-2">

<label>Descripción</label>
<textarea name="descripcion" id="prod_descripcion" class="form-control mb-2"></textarea>

<label>Cantidad</label>
<input type="number" name="cantidad" id="prod_cantidad" class="form-control mb-2">

<label>Estado</label>
<select name="estado" id="prod_estado" class="form-control mb-2">
<option value="1">Activo</option>
<option value="0">Inactivo</option>
</select>

<label>Imagen</label>
<input type="file" name="imagen" class="form-control mb-3">

<button class="btn btn-success w-100">Guardar cambios</button>

</form>

</div>
</div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/js/bootstrap.bundle.min.js"></script>

<script>

/* ===== Abrir modal ===== */
$('.editBtn').click(function(){

 let tr = $(this).closest('tr');

 $('#prod_id').val(tr.data('id'));
 $('#prod_nombre').val(tr.data('nombre'));
 $('#prod_precio').val(tr.data('precio'));
 $('#prod_descripcion').val(tr.data('descripcion'));
 $('#prod_cantidad').val(tr.data('cantidad'));
 $('#prod_estado').val(tr.data('estado'));

 new bootstrap.Modal(document.getElementById('modalModificar')).show();
});

/* ===== Guardar cambios ===== */
$('#formModificar').submit(function(e){

 e.preventDefault();

 let datos = new FormData(this);

 $.ajax({
   url:'../privadas/product_update.php',
   type:'POST',
   data:datos,
   contentType:false,
   processData:false,

   success:function(){
     Swal.fire('OK','Actualizado','success')
     .then(()=>location.reload());
   }
 });
});

/* ===== Activar / Inactivar ===== */
$('.toggleBtn').click(function(){

 let id = $(this).data('id');
 let estado = $(this).data('estado');

 let nuevo = estado==1 ? 0 : 1;

 Swal.fire({
   title:'¿Cambiar estado?',
   icon:'warning',
   showCancelButton:true,
   confirmButtonText:'Sí'
 }).then(res=>{

   if(res.isConfirmed){

     $.post('../privadas/estado.php',
       {id:id, estado:nuevo},
       function(){
         location.reload();
       }
     );
   }
 });

});

</script>

</body>
</html>
