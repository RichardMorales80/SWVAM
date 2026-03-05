<?php
session_start();
require '../config/Conexion.php';
require __DIR__ . '/../config/seguridad.php';

verificarRoles([1,3]);

if(!isset($_SESSION['id_usuario'])){
    header("Location: ../index.php");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];
$id_rol = $_SESSION['id_rol'];
$db = Conexion::conectar();

/* CREAR DIRECCIONES VACÍAS SI NO EXISTEN */
$sqlCheck = "SELECT id_usuario FROM usuarios WHERE id_direccion IS NULL";
$stmtCheck = $db->prepare($sqlCheck);
$stmtCheck->execute();
$usuariosSinDireccion = $stmtCheck->fetchAll(PDO::FETCH_ASSOC);

foreach($usuariosSinDireccion as $u) {
    $sqlInsert = "INSERT INTO direcciones (calle, numero_exterior, numero_interior, colonia, ciudad, estado, codigo_postal, created_at) 
                  VALUES ('','','','','','','','".date('Y-m-d H:i:s')."')";
    $stmtInsert = $db->prepare($sqlInsert);
    $stmtInsert->execute();
    $newIdDireccion = $db->lastInsertId();

    $sqlUpdate = "UPDATE usuarios SET id_direccion = :id_direccion WHERE id_usuario = :id_usuario";
    $stmtUpdate = $db->prepare($sqlUpdate);
    $stmtUpdate->execute([
        ':id_direccion' => $newIdDireccion,
        ':id_usuario' => $u['id_usuario']
    ]);
}

/* OBTENER USUARIOS CON DIRECCIÓN */
$sql = "
SELECT 
    u.id_usuario,
    u.primer_nombre,
    u.primer_apellido,
    u.segundo_apellido,
    u.correo,
    u.telefono,
    u.id_rol,
    u.id_direccion,
    COALESCE(d.calle,'') as calle,
    COALESCE(d.numero_exterior,'') as numero_exterior,
    COALESCE(d.numero_interior,'') as numero_interior,
    COALESCE(d.colonia,'') as colonia,
    COALESCE(d.ciudad,'') as ciudad,
    COALESCE(d.estado,'') as estado,
    COALESCE(d.codigo_postal,'') as codigo_postal
FROM usuarios u
LEFT JOIN direcciones d ON u.id_direccion = d.id_direccion
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
<link rel="stylesheet" href="../public/estilos/ventas.css">
<link rel="stylesheet" href="../public/estilos/encabezado.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
</head>
<body>

<!-- NAV -->
<nav class="main_nav">
    <div class="menu_toggle" id="menuToggle">☰</div>
    <ul class="menu" id="menu">
        <li class="logo-item"><a href="#"><img src="../public/imagenes/logo.png" class="logo" alt="logo"></a></li>
        <li><a href="../views/administrador.php" class="main_menu_link">Atrás</a></li>
    </ul>
</nav>

<!-- CONTENEDOR PRINCIPAL -->

    <h2 class="text-center mb-4">Listado de Usuarios</h2>

    <div class="card p-3">
        <table class="table-pro" id="usuariosTable">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Primer Nombre</th>
                    <th>Primer Apellido</th>
                    <th>Segundo Apellido</th>
                    <th>Correo</th>
                    <th>Teléfono</th>
                    <th>Calle</th>
                    <th>Número Ext.</th>
                    <th>Número Int.</th>
                    <th>Colonia</th>
                    <th>Ciudad</th>
                    <th>Estado</th>
                    <th>C.P.</th>
                    <th>Rol</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($usuarios as $u): ?>
                <tr>
                    <td><?= $u['id_usuario'] ?></td>
                    <td><?= $u['primer_nombre'] ?></td>
                    <td><?= $u['primer_apellido'] ?></td>
                    <td><?= $u['segundo_apellido'] ?></td>
                    <td><?= $u['correo'] ?></td>
                    <td><?= $u['telefono'] ?></td>
                    <td><?= $u['calle'] ?></td>
                    <td><?= $u['numero_exterior'] ?></td>
                    <td><?= $u['numero_interior'] ?></td>
                    <td><?= $u['colonia'] ?></td>
                    <td><?= $u['ciudad'] ?></td>
                    <td><?= $u['estado'] ?></td>
                    <td><?= $u['codigo_postal'] ?></td>
                    <td><?= $u['id_rol']==1?'Administrador':'Usuario' ?></td>
                    <td>
                        <button class="btn btn-primary btn-sm editBtn"
                            data-id="<?= $u['id_usuario'] ?>"
                            data-primer_nombre="<?= $u['primer_nombre'] ?>"
                            data-primer_apellido="<?= $u['primer_apellido'] ?>"
                            data-segundo_apellido="<?= $u['segundo_apellido'] ?>"
                            data-correo="<?= $u['correo'] ?>"
                            data-telefono="<?= $u['telefono'] ?>"
                            data-rol="<?= $u['id_rol'] ?>"
                            data-id_direccion="<?= $u['id_direccion'] ?>"
                            data-calle="<?= $u['calle'] ?>"
                            data-numero_exterior="<?= $u['numero_exterior'] ?>"
                            data-numero_interior="<?= $u['numero_interior'] ?>"
                            data-colonia="<?= $u['colonia'] ?>"
                            data-ciudad="<?= $u['ciudad'] ?>"
                            data-estado="<?= $u['estado'] ?>"
                            data-codigo_postal="<?= $u['codigo_postal'] ?>"
                        >Modificar</button>
                        <button class="btn btn-danger btn-sm deleteBtn" data-id="<?= $u['id_usuario'] ?>">Eliminar</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- PAGINACIÓN -->
        <div id="paginacion" class="text-center mt-3"></div>
    </div>


<!-- MODAL EDITAR -->
<div class="modal fade" id="modalEditar" tabindex="-1">
<div class="modal-dialog modal-lg">
<div class="modal-content">
<form action="../config/modifus.php" method="POST" class="registro">
<div class="modal-header">
<h5 class="modal-title">Modificar Usuario</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body">

<input type="hidden" name="id_usuario" id="edit_id">
<input type="hidden" name="id_direccion" id="edit_id_direccion">

<div class="form-grid-3">
    <div class="col">
        <label>Primer Nombre</label>
        <input type="text" name="primer_nombre" id="edit_primer_nombre" required>
    </div>
    <div class="col">
        <label>Primer Apellido</label>
        <input type="text" name="primer_apellido" id="edit_primer_apellido" required>
    </div>
    <div class="col">
        <label>Segundo Apellido</label>
        <input type="text" name="segundo_apellido" id="edit_segundo_apellido">
    </div>
</div>

<div class="form-grid-3">
    <div class="col">
        <label>Correo</label>
        <input type="email" name="correo" id="edit_correo" required>
    </div>
    <div class="col">
        <label>Teléfono</label>
        <input type="text" name="telefono" id="edit_telefono">
    </div>
    <div class="col">
        <label>Rol</label>
        <select name="id_rol" id="edit_rol">
            <option value="1">Administrador</option>
            <option value="2">Usuario</option>
        </select>
    </div>
</div>

<h5 class="mt-3">Dirección</h5>
<div class="form-grid-3">
    <div class="col">
        <label>Calle</label>
        <input type="text" name="calle" id="edit_calle">
    </div>
    <div class="col">
        <label>Número Ext.</label>
        <input type="text" name="numero_exterior" id="edit_num_ext">
    </div>
    <div class="col">
        <label>Número Int.</label>
        <input type="text" name="numero_interior" id="edit_num_int">
    </div>
</div>

<div class="form-grid-3">
    <div class="col">
        <label>Colonia</label>
        <input type="text" name="colonia" id="edit_colonia">
    </div>
    <div class="col">
        <label>Ciudad</label>
        <input type="text" name="ciudad" id="edit_ciudad">
    </div>
    <div class="col">
        <label>Estado</label>
        <input type="text" name="estado" id="edit_estado">
    </div>
</div>

<div class="form-grid-3">
    <div class="col">
        <label>Código Postal</label>
        <input type="text" name="codigo_postal" id="edit_cp">
    </div>
    <div class="col">
        <label>Nueva contraseña (opcional)</label>
        <input type="password" name="password">
    </div>
</div>

</div>
<div class="modal-footer">
<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
<button type="submit" class="btn boton">Guardar cambios</button>
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
// EDITAR
$('.editBtn').click(function(){
    $('#edit_id').val($(this).data('id'));
    $('#edit_primer_nombre').val($(this).data('primer_nombre'));
    $('#edit_primer_apellido').val($(this).data('primer_apellido'));
    $('#edit_segundo_apellido').val($(this).data('segundo_apellido'));
    $('#edit_correo').val($(this).data('correo'));
    $('#edit_telefono').val($(this).data('telefono'));
    $('#edit_rol').val($(this).data('rol'));
    $('#edit_id_direccion').val($(this).data('id_direccion'));
    $('#edit_calle').val($(this).data('calle'));
    $('#edit_num_ext').val($(this).data('numero_exterior'));
    $('#edit_num_int').val($(this).data('numero_interior'));
    $('#edit_colonia').val($(this).data('colonia'));
    $('#edit_ciudad').val($(this).data('ciudad'));
    $('#edit_estado').val($(this).data('estado'));
    $('#edit_cp').val($(this).data('codigo_postal'));
    new bootstrap.Modal(document.getElementById('modalEditar')).show();
});

// ELIMINAR
$('.deleteBtn').click(function(){
    $('#delete_id').val($(this).data('id'));
    new bootstrap.Modal(document.getElementById('modalEliminar')).show();
});

// PAGINACIÓN
$(document).ready(function(){
    const rowsPerPage = 5;
    const rows = $('#usuariosTable tbody tr');
    const totalPages = Math.ceil(rows.length / rowsPerPage);
    const paginacion = $('#paginacion');

    function showPage(page){
        rows.hide();
        rows.slice((page-1)*rowsPerPage, page*rowsPerPage).show();
        paginacion.find('button').removeClass('activo');
        paginacion.find('button').eq(page-1).addClass('activo');
    }

    // Generar botones
    paginacion.empty();
    for(let i=1;i<=totalPages;i++){
        paginacion.append(`<button>${i}</button>`);
    }

    paginacion.on('click','button',function(){
        const page = $(this).text();
        showPage(page);
    });

    if(totalPages>0) showPage(1);
});
</script>

</body>
</html>