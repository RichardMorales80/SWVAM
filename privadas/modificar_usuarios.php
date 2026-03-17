<?php
session_start();
require '../config/Conexion.php';
require __DIR__ . '/../config/seguridad.php';

verificarRoles([1,3]);

if(!isset($_SESSION['id_usuario'])){
    header("Location: ../index.php");
    exit();
}

$titulo = "Gestión de Usuarios";
$tipoMenu = "admin";
include("../views/navbar.php");

$id_usuario = $_SESSION['id_usuario'];
$id_rol = $_SESSION['id_rol'];
$db = Conexion::conectar();

/* CREAR DIRECCIONES VACÍAS SI NO EXISTEN */
$sqlCheck = "SELECT id_usuario FROM usuarios WHERE id_direccion IS NULL";
$stmtCheck = $db->prepare($sqlCheck);
$stmtCheck->execute();
$usuariosSinDireccion = $stmtCheck->fetchAll(PDO::FETCH_ASSOC);

foreach($usuariosSinDireccion as $u) {
    $sqlInsert = "INSERT INTO direcciones (
                    calle, numero_exterior, numero_interior, colonia, ciudad, estado, codigo_postal, created_at
                  ) VALUES (
                    '', '', '', '', '', '', '', '".date('Y-m-d H:i:s')."'
                  )";
    $stmtInsert = $db->prepare($sqlInsert);
    $stmtInsert->execute();
    $newIdDireccion = $db->lastInsertId();

    $sqlUpdate = "UPDATE usuarios 
                  SET id_direccion = :id_direccion 
                  WHERE id_usuario = :id_usuario";
    $stmtUpdate = $db->prepare($sqlUpdate);
    $stmtUpdate->execute([
        ':id_direccion' => $newIdDireccion,
        ':id_usuario'   => $u['id_usuario']
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

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="../public/estilos/ventas.css">
<link rel="stylesheet" href="../public/estilos/estilos.css">
<link rel="stylesheet" href="../public/estilos/registro.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<?php
$rutaBase = "../";
if($id_rol == 1 || $id_rol == 3){
    include("../views/modal_registro_usuario.php");
}
?>

<div class="topbar">
    <div class="topbar-left">
        <h4><?= $titulo ?></h4>
    </div>

    <div class="topbar-user">
        <span class="usuario-nombre">
            <?= $_SESSION['nombre'] ?? 'Usuario' ?>
        </span>
        <img src="../public/imagenes/avatar.png" class="avatar" alt="Avatar">
    </div>
</div>

<div class="main-content">
    <div class="card p-3">

        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:10px;">
            <h5 style="margin:0;">Listado de Usuarios</h5>

            <?php if($id_rol == 1 || $id_rol == 3): ?>
            <button type="button" class="btn-sistema btn-guardar" id="btnAbrirRegistro">
                Agregar usuario
            </button>
            <?php endif; ?>
        </div>

        <div class="filtros-usuarios">
            <input type="text" id="busquedaUsuario" placeholder="Buscar por nombre, apellido, correo o teléfono">

            <select id="filtroRol">
                <option value="">Todos los roles</option>
                <option value="1">Administrador</option>
                <option value="2">Usuario</option>
                <option value="3">Empleado</option>
            </select>

            <input type="text" id="filtroCiudad" placeholder="Filtrar por ciudad">

            <button type="button" id="limpiarFiltros" class="btn-sistema btn-editar">Limpiar</button>
        </div>

        <table class="tabla-sistema" id="usuariosTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Apellido</th>
                    <th>Correo</th>
                    <th>Teléfono</th>
                    <th>Ciudad</th>
                    <th>Rol</th>
                    <?php if($id_rol == 1): ?>
                    <th>Acciones</th>
                    <?php endif; ?>
                </tr>
            </thead>

            <tbody>
                <?php foreach($usuarios as $u): ?>
                <tr 
                    data-rol="<?= $u['id_rol'] ?>"
                    data-ciudad="<?= strtolower(trim($u['ciudad'])) ?>"
                    data-busqueda="<?= strtolower(trim($u['primer_nombre'].' '.$u['primer_apellido'].' '.$u['segundo_apellido'].' '.$u['correo'].' '.$u['telefono'].' '.$u['ciudad'])) ?>"
                >
                    <td><?= $u['id_usuario'] ?></td>
                    <td><?= htmlspecialchars($u['primer_nombre']) ?></td>
                    <td><?= htmlspecialchars($u['primer_apellido']) ?></td>
                    <td><?= htmlspecialchars($u['correo']) ?></td>
                    <td><?= htmlspecialchars($u['telefono']) ?></td>
                    <td><?= htmlspecialchars($u['ciudad']) ?></td>
                    <td>
                        <?= $u['id_rol'] == 1 ? 'Administrador' : ($u['id_rol'] == 2 ? 'Usuario' : ($u['id_rol'] == 3 ? 'Empleado' : 'Sin rol')) ?>
                    </td>

                    <?php if($id_rol == 1): ?>
                    <td>
                        <button class="btn-sistema btn-editar editBtn"
                            data-id="<?= $u['id_usuario'] ?>"
                            data-primer_nombre="<?= htmlspecialchars($u['primer_nombre']) ?>"
                            data-primer_apellido="<?= htmlspecialchars($u['primer_apellido']) ?>"
                            data-segundo_apellido="<?= htmlspecialchars($u['segundo_apellido']) ?>"
                            data-correo="<?= htmlspecialchars($u['correo']) ?>"
                            data-telefono="<?= htmlspecialchars($u['telefono']) ?>"
                            data-rol="<?= $u['id_rol'] ?>"
                            data-id_direccion="<?= $u['id_direccion'] ?>"
                            data-calle="<?= htmlspecialchars($u['calle']) ?>"
                            data-numero_exterior="<?= htmlspecialchars($u['numero_exterior']) ?>"
                            data-numero_interior="<?= htmlspecialchars($u['numero_interior']) ?>"
                            data-colonia="<?= htmlspecialchars($u['colonia']) ?>"
                            data-ciudad="<?= htmlspecialchars($u['ciudad']) ?>"
                            data-estado="<?= htmlspecialchars($u['estado']) ?>"
                            data-codigo_postal="<?= htmlspecialchars($u['codigo_postal']) ?>">
                            Modificar
                        </button>

                        <button class="btn-sistema btn-eliminar deleteBtn"
                            data-id="<?= $u['id_usuario'] ?>">
                            Eliminar
                        </button>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div id="paginacion" class="paginacion"></div>
    </div>
</div>

<?php if($id_rol == 1): ?>
<!-- MODAL EDITAR -->
<div class="modal fade" id="modalEditar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="../config/modifus.php" method="POST" class="registro" id="formEditarUsuario">
                <div class="modal-header">
                    <h5 class="modal-title">Modificar Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
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
                                <option value="2">Cliente</option>
                                <option value="3">Vendedor</option>
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
                            <input type="password" name="password" id="edit_password" autocomplete="new-password">
                        </div>

                        <div class="col">
                            <label>Confirmar contraseña (opcional)</label>
                            <input type="password" name="confirmar_password" id="edit_confirmar_password" autocomplete="new-password">
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn-sistema btn-guardar">Guardar cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL ELIMINAR -->
<div class="modal fade" id="modalEliminar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="../config/elimius.php" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Eliminar Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
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
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<?php if($id_rol == 1): ?>
<script>
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
    $('#edit_password').val('');
    $('#edit_confirmar_password').val('');
    new bootstrap.Modal(document.getElementById('modalEditar')).show();
});

$('.deleteBtn').click(function(){
    $('#delete_id').val($(this).data('id'));
    new bootstrap.Modal(document.getElementById('modalEliminar')).show();
});

$('#formEditarUsuario').on('submit', function(e){
    const pass = $('#edit_password').val().trim();
    const confirmar = $('#edit_confirmar_password').val().trim();

    if(pass !== '' || confirmar !== ''){
        if(pass === '' || confirmar === ''){
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Contraseña incompleta',
                text: 'Si vas a cambiar la contraseña, debes escribirla y confirmarla.'
            });
            return;
        }

        if(pass !== confirmar){
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Contraseñas diferentes',
                text: 'La nueva contraseña y su confirmación no coinciden.'
            });
            return;
        }
    }
});
</script>
<?php endif; ?>

<script>
$(document).ready(function(){
    let currentPage = 1;

    function getFilteredRows() {
        const texto = $('#busquedaUsuario').val().toLowerCase().trim();
        const rol = $('#filtroRol').val();
        const ciudad = $('#filtroCiudad').val().toLowerCase().trim();

        let filasFiltradas = $('#usuariosTable tbody tr').filter(function(){
            const busqueda = ($(this).attr('data-busqueda') || '').toLowerCase();
            const filaRol = ($(this).attr('data-rol') || '').toString();
            const filaCiudad = ($(this).attr('data-ciudad') || '').toLowerCase();

            const coincideTexto = texto === '' || busqueda.includes(texto);
            const coincideRol = rol === '' || filaRol === rol;
            const coincideCiudad = ciudad === '' || filaCiudad.includes(ciudad);

            return coincideTexto && coincideRol && coincideCiudad;
        }).get();

        return $(filasFiltradas);
    }

    function renderPagination(totalPages){
        const paginacion = $('#paginacion');
        paginacion.html('');

        if(totalPages <= 0){
            return;
        }

        paginacion.append('<button id="prev">◀</button>');

        for(let i = 1; i <= totalPages; i++){
            paginacion.append(`<button data-page="${i}">${i}</button>`);
        }

        paginacion.append('<button id="next">▶</button>');
        paginacion.find(`[data-page="${currentPage}"]`).addClass('activo');
    }

    function showPage(page){
        const rowsPerPage = 5;
        const allRows = $('#usuariosTable tbody tr');
        const filteredRows = getFilteredRows();

        allRows.hide();

        const totalPages = Math.ceil(filteredRows.length / rowsPerPage);

        if(totalPages === 0){
            $('#paginacion').html('<span>No se encontraron usuarios.</span>');
            return;
        }

        if(page > totalPages){
            page = totalPages;
        }

        if(page < 1){
            page = 1;
        }

        currentPage = page;

        const start = (currentPage - 1) * rowsPerPage;
        const end = start + rowsPerPage;

        filteredRows.slice(start, end).show();
        renderPagination(totalPages);
    }

    $('#paginacion').on('click', 'button[data-page]', function(){
        const page = parseInt($(this).attr('data-page'));
        showPage(page);
    });

    $('#paginacion').on('click', '#prev', function(){
        showPage(currentPage - 1);
    });

    $('#paginacion').on('click', '#next', function(){
        showPage(currentPage + 1);
    });

    $('#busquedaUsuario, #filtroCiudad').on('keyup', function(){
        currentPage = 1;
        showPage(1);
    });

    $('#filtroRol').on('change', function(){
        currentPage = 1;
        showPage(1);
    });

    $('#limpiarFiltros').on('click', function(){
        $('#busquedaUsuario').val('');
        $('#filtroRol').val('');
        $('#filtroCiudad').val('');
        currentPage = 1;
        showPage(1);
    });

    showPage(1);
});
</script>

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
        } else {
            history.pushState(null, null, location.href);
        }
    });
});

history.pushState(null, null, location.href);
</script>

<script src="../public/validar_cuenta.js"></script>

</body>
</html>