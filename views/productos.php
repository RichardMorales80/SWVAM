<?php
require __DIR__ . '/../config/seguridad.php';
require_once __DIR__ . '/../config/Conexion.php';
 
verificarRol(1);

$tipoMenu = "admin";
include("../views/navbar.php");
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
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Productos</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet" href="/public/estilos/productos.css">
<link rel="stylesheet" href="/public/estilos/estilos.css">
<link rel="stylesheet" href="../public/estilos/responsivo.css?v=99999">
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.4/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>

<!-- TOPBAR -->
<div class="topbar">
    <div class="topbar-left">
        <h4>Gestion de productos</h4>
    </div>

    <div class="topbar-user">
        <span class="usuario-nombre">
            <?= $_SESSION['nombre_usuario'] ?? 'Usuario' ?>
        </span>
        <img src="../public/imagenes/avatar.png" class="avatar" alt="Avatar">
    </div>
</div>

<div class="container mt-5">



<!-- ================= FILTROS ================= -->

<div class="filtro-container">

<form method="GET" id="filtroForm" class="filtro-form">

<div class="campo">
<label>Buscar</label>
<input type="text" name="buscar" placeholder="Producto"
value="<?= htmlspecialchars($buscar) ?>">
</div>

<div class="campo boton">
<button class="btn-sistema btn-editar" type="submit">Filtrar</button>
</div>

<div class="campo boton">
<button type="button" class="btn-sistema btn-eliminar" onclick="limpiarFiltros()">Limpiar</button>
</div>

</form>

</div>

<!-- TABLA -->
<div class="table-responsive">

<table id="productosTable" class="table table-bordered table-hover align-middle tabla-sistema">

<thead class="table-dark">

<tr>

<th>Imagen</th>

<th>ID</th>

<th>Nombre</th>

<th class="ocultar-mobile">Precio</th>

<th class="ocultar-mobile">Descripción</th>

<th class="ocultar-mobile">Cantidad</th>

<th class="ocultar-mobile">Estado</th>

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

<!-- IMAGEN -->
<td>

<img 

src="<?= $img ?>" 

width="70"

class="img-producto"

>

</td>

<!-- ID -->
<td>

<?= $p['id_producto'] ?>

</td>

<!-- NOMBRE -->
<td>

<?= htmlspecialchars($p['nombre']) ?>

</td>

<!-- PRECIO -->
<td class="ocultar-mobile">

$<?= number_format($p['precio'],2) ?>

</td>

<!-- DESCRIPCION -->
<td class="ocultar-mobile">

<?= htmlspecialchars($p['descripcion']) ?>

</td>

<!-- CANTIDAD -->
<td class="ocultar-mobile">

<?= $p['cantidad'] ?>

</td>

<!-- ESTADO -->
<td class="ocultar-mobile">

<?php if($p['estado']==1): ?>

<span class="badge bg-success">
Activo
</span>

<?php else: ?>

<span class="badge bg-danger">
Inactivo
</span>

<?php endif; ?>

</td>

<!-- ACCIONES -->
<td>

<div class="acciones-tabla">

<button class="btn btn-primary btn-sm editBtn">

Modificar

</button>

</div>

</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>

<!-- PAGINACIÓN -->
<div id="paginacion" class="mt-3"></div>

<!-- MODAL  -->
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

/* ===== EDITAR ===== */
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


/* ===== GUARDAR ===== */
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

     //  CERRAR MODAL 
     let modalEl = document.getElementById('modalModificar');
     let modal = bootstrap.Modal.getInstance(modalEl);

     if(modal){
        modal.hide();
     }

     //  LIMPIAR BACKDROP BUG
     setTimeout(() => {
        document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
        document.body.classList.remove('modal-open');
        document.body.style = '';
     }, 200);

     //  ALERT CORRECTO
     setTimeout(() => {
        Swal.fire({
          icon: 'success',
          title: 'Producto actualizado',
          showConfirmButton: false,
          timer: 1500
        }).then(()=>{
          location.reload();
        });
     }, 300);

   },

   error:function(){
     Swal.fire('Error','No se pudo actualizar','error');
   }

 });
});


/* ===== ACTIVAR / INACTIVAR ===== */
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

     $.post('../privadas/estado.php',{id:id, estado:nuevo},function(){

        Swal.fire({
          icon:'success',
          title:'Estado actualizado',
          timer:1200,
          showConfirmButton:false
        }).then(()=>{
            location.reload();
        });

     });

   }

 });

});


/* ================= PAGINACIÓN ================= */

let currentPage = 1;

function renderPagination(totalPages){

 if(totalPages <= 1){
   $('#paginacion').html('');
   return;
 }

 let html = '<nav><ul class="pagination justify-content-center">';

 if(currentPage > 1){
   html += `<li class="page-item"><a class="page-link" href="#" data-page="${currentPage-1}">«</a></li>`;
 }

 for(let i=1;i<=totalPages;i++){
   html += `<li class="page-item ${i===currentPage?'active':''}">
            <a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
 }

 if(currentPage < totalPages){
   html += `<li class="page-item"><a class="page-link" href="#" data-page="${currentPage+1}">»</a></li>`;
 }

 html += '</ul></nav>';

 $('#paginacion').html(html);
}

function showPage(page){

 let rows = $('#productosTable tbody tr');

 let hayFiltro = new URLSearchParams(window.location.search).get('buscar');

 if(hayFiltro){
     rows.show();
     $('#paginacion').html('');
     return;
 }

 let rowsPerPage = 5;

 rows.hide();

 let totalPages = Math.ceil(rows.length / rowsPerPage);

 currentPage = Math.max(1, Math.min(page, totalPages));

 let start = (currentPage - 1) * rowsPerPage;
 let end = start + rowsPerPage;

 rows.slice(start, end).show();

 renderPagination(totalPages);
}


/* CLICK PAGINACIÓN */
$(document).on('click','#paginacion a',function(e){
 e.preventDefault();
 showPage($(this).data('page'));
});


/* INICIO */
$(document).ready(function(){

 let hayFiltro = new URLSearchParams(window.location.search).get('buscar');

 if(!hayFiltro){
     showPage(1);
 }else{
     $('#paginacion').html('');
 }

});


/* LIMPIAR FILTROS */
function limpiarFiltros(){
    window.location.href = 'productos.php';
}

</script>

</body>
</html>
