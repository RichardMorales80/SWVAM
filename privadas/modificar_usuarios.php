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
    WHERE u.estado = 1
    ";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modificar Usuarios</title>
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">

<link rel="stylesheet" href="../public/estilos/ventas.css">
<link rel="stylesheet" href="../public/estilos/estilos.css?v=99999">
<link rel="stylesheet" href="../public/estilos/registro.css">
<link rel="stylesheet" href="../public/estilos/responsivo.css?v=99999">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    </head>
    <body>
    
    <?php
    $rutaBase = "../";
    if($id_rol == 1 || $id_rol == 3){
        //include("../views/modal_registro_usuario.php");
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
    <br><br>
    <div class="main-content">
        <div class="card p-3">
    
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:10px;">
                
    
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
                                    <option value="2">Usuario</option>
                    <option value="3">Empleado</option>
                </select>
    
                <input type="text" id="filtroCiudad" placeholder="Filtrar por ciudad">
    
                <button type="button" id="limpiarFiltros" class="btn-sistema btn-editar">Limpiar</button>
            </div>
    
    <div class="table-responsive">

<table class="tabla-sistema" id="usuariosTable">

    <thead>

        <tr>

            <th>ID</th>

            <th class="ocultar-mobile">Nombre</th>

            <th class="ocultar-mobile">Apellido</th>

            <th>Correo</th>

            <th class="ocultar-mobile">Teléfono</th>

            <th class="ocultar-mobile">Ciudad</th>

            <th class="ocultar-mobile">Rol</th>

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

            <!-- ID -->
            <td>
                <?= $u['id_usuario'] ?>
            </td>

            <!-- NOMBRE -->
            <td class="ocultar-mobile">
                <?= htmlspecialchars($u['primer_nombre']) ?>
            </td>

            <!-- APELLIDO -->
            <td class="ocultar-mobile">
                <?= htmlspecialchars($u['primer_apellido']) ?>
            </td>

            <!-- CORREO -->
            <td>
                <?= htmlspecialchars($u['correo']) ?>
            </td>

            <!-- TELEFONO -->
            <td class="ocultar-mobile">
                <?= htmlspecialchars($u['telefono']) ?>
            </td>

            <!-- CIUDAD -->
            <td class="ocultar-mobile">
                <?= htmlspecialchars($u['ciudad']) ?>
            </td>

            <!-- ROL -->
            <td class="ocultar-mobile">

                <?= $u['id_rol'] == 1 
                    ? 'Administrador' 
                    : ($u['id_rol'] == 2 
                    ? 'Usuario' 
                    : ($u['id_rol'] == 3 
                    ? 'Empleado' 
                    : 'Sin rol')) ?>

            </td>

            <?php if($id_rol == 1): ?>

            <!-- ACCIONES -->
            <td>

                <div class="acciones-tabla">

                    <button 

                        class="btn-sistema btn-editar editBtn"

                        data-id="<?= $u['id_usuario']; ?>"

                        data-primer_nombre="<?= $u['primer_nombre']; ?>"

                        data-primer_apellido="<?= $u['primer_apellido']; ?>"

                        data-segundo_apellido="<?= $u['segundo_apellido']; ?>"

                        data-correo="<?= $u['correo']; ?>"

                        data-telefono="<?= $u['telefono']; ?>"

                        data-rol="<?= $u['id_rol']; ?>"

                        data-id_direccion="<?= $u['id_direccion']; ?>"

                        data-calle="<?= $u['calle']; ?>"

                        data-numero_exterior="<?= $u['numero_exterior']; ?>"

                        data-numero_interior="<?= $u['numero_interior']; ?>"

                        data-colonia="<?= $u['colonia']; ?>"

                        data-ciudad="<?= $u['ciudad']; ?>"

                        data-estado="<?= $u['estado']; ?>"

                        data-codigo_postal="<?= $u['codigo_postal']; ?>"

                    >

                        Modificar

                    </button>

                    <button 

                        class="btn-sistema btn-eliminar deleteBtn"

                        data-id="<?= $u['id_usuario']; ?>"

                    >

                        Desactivar

                    </button>

                    <button 

                        class="btn-sistema btn-eliminar autorizarBtn"

                        data-id="<?= intval($u['id_usuario']) ?>"

                    >

                        Autorizar gasto extra

                    </button>

                </div>

            </td>

            <?php endif; ?>

        </tr>

        <?php endforeach; ?>

    </tbody>

</table>

</div>
    
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
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
    
                    <div class="modal-body">
                        <input type="hidden" name="id_usuario" id="edit_id">
                        <input type="hidden" name="id_direccion" id="edit_id_direccion">
    
                        <div class="form-grid-3">
                            <div class="col">
                                <label>Primer Nombre</label>
                                <input type="text" name="primer_nombre" id="edit_primer_nombre" class="form-control" required>
                            </div>
                            <div class="col">
                                <label>Primer Apellido</label>
                                <input type="text" name="primer_apellido" id="edit_primer_apellido" class="form-control" required>
                            </div>
                            <div class="col">
                                <label>Segundo Apellido</label>
                                <input type="text" name="segundo_apellido" id="edit_segundo_apellido" class="form-control">
                            </div>
                        </div>
    
                        <div class="form-grid-3">
                            <div class="col">
                                <label>Correo</label>
                                <input type="email" name="correo" id="edit_correo" class="form-control" required>
                            </div>
                            <div class="col">
                                <label>Teléfono</label>
                                <input type="text" name="telefono" id="edit_telefono" class="form-control">
                            </div>
                            <div class="col">
                                <label>Rol</label>
                                <select name="id_rol" id="edit_rol" class="form-control">
                                    <option value="2">Cliente</option>
                                    <option value="3">Vendedor</option>
                                </select>
                            </div>
                        </div>
    
                        <h5 class="mt-3">Dirección</h5>
    
                        <div class="form-grid-3">
                            <div class="col">
                                <label>Calle</label>
                                <input type="text" name="calle" id="edit_calle" class="form-control">
                            </div>
                            <div class="col">
                                <label>Número Ext.</label>
                                <input type="text" name="numero_exterior" id="edit_num_ext" class="form-control">
                            </div>
                            <div class="col">
                                <label>Número Int.</label>
                                <input type="text" name="numero_interior" id="edit_num_int" class="form-control">
                            </div>
                        </div>
    
                        <div class="form-grid-3">
                            <div class="col">
                                <label>Colonia</label>
                                <select name="colonia" id="edit_colonia" class="form-control">
                                    <option value="">Selecciona colonia</option>
                                </select>
                            </div>
                            <div class="col">
                                <label>Ciudad</label>
                                <input type="text" name="ciudad" id="edit_ciudad" class="form-control">
                            </div>
                            <div class="col">
                                <label>Estado</label>
                                <input type="text" name="estado" id="edit_estado" class="form-control">
                            </div>
                        </div>
    
                        <div class="form-grid-3">
                            <div class="col">
                                <label>Código Postal</label>
                                <input type="text" name="codigo_postal" id="edit_cp" class="form-control">
                            </div>
    
                            <div class="form-grid-3">
                                <label>Nueva contraseña (opcional)</label>
                                <input type="password" name="password" id="edit_password" class="form-control" autocomplete="new-password">
                            </div>
    
                            <div class="form-grid-3">
                                <label>Confirmar contraseña (opcional)</label>
                                <input type="password" name="confirmar_password" id="edit_confirmar_password" class="form-control" autocomplete="new-password">
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
                        <h5 class="modal-title">Desactivar Usuario</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id_usuario" id="delete_id">
                        <p>¿Seguro que deseas <b>desactivar</b> este usuario?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn-sistema btn-eliminar">Desactivar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    
    
    <script>
    
    $(document).ready(function(){
    
    /* ================= PAGINACIÓN ================= */
    
    let currentPage = 1;
    
    function renderPagination(totalPages){
        const paginacion = $('#paginacion');
        paginacion.html('');
    
        if(totalPages <= 0) return;
    
        let html = '<nav><ul class="pagination justify-content-center">';
    
        if(currentPage > 1){
            html += `<li class="page-item">
                        <a class="page-link" href="#" data-page="${currentPage-1}">«</a>
                     </li>`;
        }
    
        for(let i = 1; i <= totalPages; i++){
            html += `<li class="page-item ${i === currentPage ? 'active' : ''}">
                        <a class="page-link" href="#" data-page="${i}">${i}</a>
                     </li>`;
        }
    
        if(currentPage < totalPages){
            html += `<li class="page-item">
                        <a class="page-link" href="#" data-page="${currentPage+1}">»</a>
                     </li>`;
        }
    
        html += '</ul></nav>';
        paginacion.html(html);
    }
    
    function showPage(page){
    
        const rowsPerPage = 5;
        const allRows = $('#usuariosTable tbody tr');
    
        let visibles = [];
    
        allRows.each(function(){
    
            const texto = $('#busquedaUsuario').val().toLowerCase().trim();
            const rol = $('#filtroRol').val();
            const ciudad = $('#filtroCiudad').val().toLowerCase().trim();
    
            const busqueda = ($(this).attr('data-busqueda') || '').toLowerCase();
            const filaRol = ($(this).attr('data-rol') || '').toString();
            const filaCiudad = ($(this).attr('data-ciudad') || '').toLowerCase();
    
            if(
                (texto === '' || busqueda.includes(texto)) &&
                (rol === '' || filaRol === rol) &&
                (ciudad === '' || filaCiudad.includes(ciudad))
            ){
                visibles.push($(this));
            }
        });
    
        allRows.hide();
    
        const totalPages = Math.ceil(visibles.length / rowsPerPage);
    
        if(totalPages === 0){
            $('#paginacion').html('<span>No se encontraron usuarios.</span>');
            return;
        }
    
        currentPage = Math.max(1, Math.min(page, totalPages));
    
        visibles.slice((currentPage - 1) * rowsPerPage, currentPage * rowsPerPage)
                .forEach(row => row.show());
    
        renderPagination(totalPages);
    }
    
    $('#paginacion').on('click', 'a.page-link', function(e){
        e.preventDefault();
        showPage(parseInt($(this).data('page')));
    });
    
    $('#busquedaUsuario, #filtroCiudad').on('keyup', () => showPage(1));
    $('#filtroRol').on('change', () => showPage(1));
    
    $('#limpiarFiltros').on('click', function(){
        $('#busquedaUsuario, #filtroCiudad').val('');
        $('#filtroRol').val('');
        showPage(1);
    });
    
    showPage(1);
    
    
    /* ================= EDITAR ================= */
    
    $(document).on('click', '.editBtn', function(){
    
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
    
        let cp = $(this).data('codigo_postal');
        $('#edit_cp').val(cp);
    
        let coloniaActual = $(this).data('colonia');
        $('#edit_colonia').data('selected', coloniaActual);
    
        cargarCP(cp);
    
        new bootstrap.Modal(document.getElementById('modalEditar')).show();
    });
    
    
    /* ================= ELIMINAR ================= */
    
    $(document).on('click', '.deleteBtn', function(){
    
        let id = $(this).data('id');
        $('#delete_id').val(id);
    
        new bootstrap.Modal(document.getElementById('modalEliminar')).show();
    });
    
    
    /* ================= CP ================= */
    
    function cargarCP(cp){
    
        if(!cp || cp.length !== 5) return;
    
        fetch('../public/buscar_cp.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'codigo_postal=' + cp
        })
        .then(res => res.json())
        .then(data => {
    
            let select = $('#edit_colonia').empty();
    
            if(data.success){
    
                $('#edit_estado').val(data.estado);
                $('#edit_ciudad').val(data.ciudad);
    
                select.append('<option value="">Selecciona colonia</option>');
    
                data.colonias.forEach(c =>
                    select.append(`<option value="${c}">${c}</option>`)
                );
    
                let sel = select.data('selected');
                if(sel) select.val(sel);
    
            } else {
                select.append('<option>No encontrado</option>');
            }
        })
        .catch(error => console.error("Error CP:", error));
    }
    
    
    /* ================= EVENTO CP ================= */
    
    $(document).on('keyup', '#edit_cp', function(){
    
        let cp = $(this).val().trim();
    
        if(cp.length === 5){
            cargarCP(cp);
        }
    
    });
    
    
    /* ================= PASSWORD ================= */
    
    $('#formEditarUsuario').on('submit', function(e){
    
        const pass = $('#edit_password').val().trim();
        const conf = $('#edit_confirmar_password').val().trim();
    
        if(pass || conf){
    
            if(!pass || !conf){
                e.preventDefault();
                return Swal.fire('Error','Completa la contraseña','warning');
            }
    
            if(pass !== conf){
                e.preventDefault();
                return Swal.fire('Error','No coinciden','error');
            }
        }
    });
    
    
    /* ================= REGISTRO ================= */

$(document).on('click', '#btnAbrirRegistro', function(){

    $('#modalRegistro').addClass('mostrar');

    $('body').css('overflow','hidden');

});

$(document).on('click', '#cerrarRegistro', function(){

    $('#modalRegistro').removeClass('mostrar');

    $('body').css('overflow','auto');

});

$(document).on('click', '#modalRegistro', function(e){

    if(e.target === this){

        $('#modalRegistro').removeClass('mostrar');

        $('body').css('overflow','auto');

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
    
    <?php
    $rutaBase = "../";
    include("../views/modal_registro_usuario.php");
    ?>
    
    <script src="../public/validar_cuenta.js"></script>
    <script>
    
    document.querySelectorAll('.autorizarBtn').forEach(button => {
    
        button.addEventListener('click', function(){
    
            let idUsuario = this.dataset.id;
            console.log(idUsuario);
    
            Swal.fire({
                title: '¿Autorizar gasto extra?',
                text: 'El vendedor podrá exceder el límite una vez.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, autorizar',
                cancelButtonText: 'Cancelar'
            })
    
            .then((result) => {
    
                if(result.isConfirmed){
    
                    fetch('autorizar_gasto.php', {
    
                        method: 'POST',
    
                        headers: {
                            'Content-Type':'application/x-www-form-urlencoded'
                        },
    
                        body: 'id=' + encodeURIComponent(idUsuario)
    
                    })
    
                    .then(response => response.text())
    
                    .then(data => {
    
                        console.log(data);
    
                        if(data.trim() === 'ok'){
    
                            Swal.fire({
                                icon: 'success',
                                title: 'Autorizado',
                                text: 'El vendedor puede registrar un gasto extra'
                            });
    
                        } else {
    
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: data
                            });
                        }
    
                    })
    
                    .catch(error => {
    
                        console.error(error);
    
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'No se pudo conectar con el servidor'
                        });
    
                    });
    
                }
    
            });
    
        });
    
    });
    
    </script>
    <script>

$(document).ready(function(){

    // ABRIR MODAL
    $('#btnAbrirRegistro').click(function(){

        $('#modalRegistro').addClass('mostrar');

        $('body').css('overflow','hidden');

    });

    // CERRAR MODAL
    $('#cerrarRegistro').click(function(){

        $('#modalRegistro').removeClass('mostrar');

        $('body').css('overflow','auto');

    });

    // CERRAR AL DAR CLICK FUERA
    $(window).click(function(e){

        if($(e.target).is('#modalRegistro')){

            $('#modalRegistro').removeClass('mostrar');

            $('body').css('overflow','auto');

        }

    });

});

</script>
    </body>
    </html>