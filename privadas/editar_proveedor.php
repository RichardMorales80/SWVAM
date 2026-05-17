<?php
session_start();
require '../config/Conexion.php';
require __DIR__ . '/../config/seguridad.php';

verificarRoles([1]);

$titulo = "Gestión de Proveedores";
$tipoMenu = "admin";
include("../views/navbar.php");

/* ===== CONEXION ===== */
$db = Conexion::conectar();

/* ===== DATOS ===== */
$sql = "SELECT * FROM proveedores";
$stmt = $db->prepare($sql);
$stmt->execute();
$proveedores = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Modificar Proveedores</title>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="../public/estilos/ventas.css">
<link rel="stylesheet" href="../public/estilos/estilos.css">
<link rel="stylesheet" href="../public/estilos/registro.css">
<link rel="stylesheet" href="<?= BASE_URL ?>public/estilos/responsivo.css?v=99999">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>

<div class="topbar">
    <h4><?= $titulo ?></h4>
</div>

<div class="main-content">
    <div class="card p-3">

        <h5>Listado de Proveedores</h5>

        <!-- FILTROS -->
        <div class="filtros-usuarios">
            <input type="text" id="busqueda" placeholder="Buscar proveedor">

            <select id="filtroEstado">
                <option value="todos">Todos</option>
                <option value="activos">Activos</option>
                <option value="inactivos">Inactivos</option>
            </select>

            <button id="limpiar" class="btn-sistema btn-editar">Limpiar</button>
        </div>

        <table class="tabla-sistema" id="tablaProveedores">

    <thead>
        <tr>

            <th>ID</th>

            <th>Nombre</th>

            <th class="ocultar-mobile">Correo</th>

            <th class="ocultar-mobile">Teléfono</th>

            <th class="ocultar-mobile">Dirección</th>

            <th class="ocultar-mobile">Estado</th>

            <th>Acciones</th>

        </tr>
    </thead>

    <tbody>

        <?php foreach($proveedores as $p): ?>

        <tr data-busqueda="<?= strtolower($p['nombre'].' '.$p['correo'].' '.$p['telefono'].' '.$p['direccion']) ?>">

            <td>
                <?= $p['id_proveedor'] ?>
            </td>

            <td>
                <?= htmlspecialchars($p['nombre']) ?>
            </td>

            <td class="ocultar-mobile">
                <?= htmlspecialchars($p['correo']) ?>
            </td>

            <td class="ocultar-mobile">
                <?= $p['telefono'] ?>
            </td>

            <td class="ocultar-mobile">
                <?= htmlspecialchars($p['direccion']) ?>
            </td>

            <td class="estado ocultar-mobile">
                <?= $p['estado'] == 1 ? 'activo' : 'inactivo' ?>
            </td>

            <td>

                <div class="acciones-tabla">

                    <button
                        class="btn-sistema btn-editar editBtn"

                        data-id="<?= $p['id_proveedor'] ?>"

                        data-nombre="<?= htmlspecialchars($p['nombre']) ?>"

                        data-correo="<?= htmlspecialchars($p['correo']) ?>"

                        data-telefono="<?= $p['telefono'] ?>"

                        data-direccion="<?= htmlspecialchars($p['direccion']) ?>">
                        Modificar
                    </button>

                    <button
                        class="btn-sistema btn-eliminar toggleEstado"

                        data-id="<?= $p['id_proveedor'] ?>">
                        <?= $p['estado'] == 1 ? 'Desactivar' : 'Activar' ?>
                    </button>

                </div>

            </td>

        </tr>

        <?php endforeach; ?>

    </tbody>

</table>
            
        <nav>
          <ul id="paginacion" class="pagination justify-content-center"></ul>
       </nav>

    </div>
</div>

<!-- MODAL -->
<div class="modal fade" id="modalEditar">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5>Modificar Proveedor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form action="../privadas/update_proveedor.php" method="POST">
                <div class="modal-body">

                    <input type="hidden" name="id" id="edit_id">

                    <label>Nombre</label>
                    <input type="text" name="nombre" id="edit_nombre" class="form-control" required>

                    <label>Correo</label>
                    <input type="email" name="correo" id="edit_correo" class="form-control" required>

                    <label>Teléfono</label>
                    <input type="text" name="telefono" id="edit_telefono" class="form-control" required>

                    <label>Dirección</label>
                    <input type="text" name="direccion" id="edit_direccion" class="form-control" required>

                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">
                        Guardar cambios
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>

// ===== MODAL =====
$('.editBtn').click(function(){

    $('#edit_id').val($(this).data('id'));
    $('#edit_nombre').val($(this).data('nombre'));
    $('#edit_correo').val($(this).data('correo'));
    $('#edit_telefono').val($(this).data('telefono'));
    $('#edit_direccion').val($(this).data('direccion'));

    new bootstrap.Modal(document.getElementById('modalEditar')).show();
});


$('.toggleEstado').click(function(){

    let id = $(this).data('id');

    Swal.fire({
        title: '¿Cambiar estado?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí'
    }).then(result => {

        if(result.isConfirmed){

            fetch('../privadas/estado_proveedor.php',{
                method:'POST',
                body:new URLSearchParams({id:id})
            })
            .then(res=>res.text())
            .then(text=>{
                try{
                    return JSON.parse(text);
                }catch(e){
                    console.error("Respuesta inválida:", text);
                    throw new Error("No es JSON");
                }
            })
            .then(data => {

                //  VALIDAR RESPUESTA
                if(data.success){

                    Swal.fire({
                        icon:'success',
                        title:'Estado actualizado'
                    }).then(()=>{
                        location.reload();
                    });

                }else{

                    Swal.fire({
                        icon:'error',
                        title:'Error',
                        text: data.error || 'No se pudo actualizar'
                    });

                }

            })
            .catch(err=>{
                console.error(err);

                Swal.fire({
                    icon:'error',
                    title:'Error servidor'
                });
            });

        }

    });

});


let filasPorPagina = 5;
let paginaActual = 1;

function showPage(pagina = 1){

    paginaActual = pagina;

    const texto = $('#busqueda').val().toLowerCase();
    const filtro = $('#filtroEstado').val();

    let filas = $('#tablaProveedores tbody tr');

    // FILTRAR
    let visibles = filas.filter(function(){

        let fila = $(this).data('busqueda');
        let estado = $(this).find('.estado').text().trim().toLowerCase();

        let visible = fila.includes(texto);

        if(filtro === 'activos') visible = visible && estado === 'activo';
        if(filtro === 'inactivos') visible = visible && estado === 'inactivo';

        return visible;
    });

    //  OCULTAR TODAS
    filas.hide();

    //  PAGINAR
    let inicio = (pagina - 1) * filasPorPagina;
    let fin = inicio + filasPorPagina;

    visibles.slice(inicio, fin).show();

    //  BOTONES
    generarPaginacion(visibles.length);
}


function generarPaginacion(total){

    let totalPaginas = Math.ceil(total / filasPorPagina);
    let html = '';

    for(let i = 1; i <= totalPaginas; i++){

        html += `<button class="btn-pag ${i===paginaActual?'activo':''}" 
                    onclick="showPage(${i})">
                    ${i}
                </button>`;
    }

    $('#paginacion').html(html);
}


// ===== EVENTOS =====
$('#busqueda').on('keyup', () => showPage(1));
$('#filtroEstado').on('change', () => showPage(1));

$('#limpiar').click(function(){
    $('#busqueda').val('');
    $('#filtroEstado').val('todos');
    showPage(1);
});

// ===== INICIAL =====
showPage(1);

</script>

</body>
</html>