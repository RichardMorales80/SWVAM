<?php
require __DIR__ . '/../config/seguridad.php';
require_once __DIR__ . '/../config/Conexion.php';

verificarRol(1);

$tipoMenu = "admin";
include("../views/navbar.php");

$pdo = Conexion::conectar();
require_once __DIR__ . '/../config/bitacora.php';
registrarVisitaPagina($pdo);
/* ================= BUSCADOR ================= */
$buscar = $_GET['buscar'] ?? '';

if ($buscar != '') {
    $sql = "SELECT id_producto, nombre, descripcion, precio, cantidad, imagen, estado
            FROM productos
            WHERE nombre LIKE :busca OR descripcion LIKE :busca
            ORDER BY id_producto DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':busca', '%' . $buscar . '%', PDO::PARAM_STR);
} else {
    $sql = "SELECT id_producto, nombre, descripcion, precio, cantidad, imagen, estado
            FROM productos
            ORDER BY id_producto DESC";
    $stmt = $pdo->prepare($sql);
}

$stmt->execute();
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ================= DATOS USUARIO ================= */
$nombre = htmlspecialchars($_SESSION['nombre'] ?? 'Administrador');
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Productos</title>

    <link rel="stylesheet" href="../public/estilos/ventas.css">
    <link rel="stylesheet" href="../public/estilos/estilos.css">
    <link rel="stylesheet" href="../public/estilos/registro.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.4/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<div class="topbar">
    <div class="topbar-left">
        <h2>Gestión de Productos</h2>
    </div>

    <div class="topbar-user">
        <span class="usuario-nombre"><?= $nombre ?></span>
        <img src="../public/imagenes/avatar.png" class="avatar" alt="Avatar">
    </div>
</div>

<div class="main-content">
    <div class="catalogo-container">

        <h3 style="margin-bottom:20px;">Productos registrados</h3>

        <!-- ================= BUSCADOR ================= -->
        <div class="card p-3" style="margin-bottom:20px;">
            <form method="GET" style="display:flex; justify-content:space-between; align-items:center; gap:10px; flex-wrap:wrap;">
                <input
                    type="text"
                    name="buscar"
                    placeholder="Buscar por nombre o descripción"
                    value="<?= htmlspecialchars($buscar) ?>"
                    style="max-width:350px;"
                >

                <div style="display:flex; gap:10px; flex-wrap:wrap;">
                    <button type="submit" class="btn-sistema btn-guardar">Buscar</button>
                    <a href="../views/productos.php" class="btn-sistema btn-editar">Limpiar</a>
                </div>
            </form>
        </div>

        <!-- ================= TABLA ================= -->
        <div class="card p-3">
            <table class="tabla-sistema" id="productosTable">
                <thead>
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
                    <?php if (!empty($productos)): ?>
                        <?php foreach ($productos as $p): ?>
                            <?php
                                $rutaFisica = __DIR__ . '/../uploads/' . $p['imagen'];
                                $rutaPublica = '../uploads/' . $p['imagen'];

                                $img = (!empty($p['imagen']) && file_exists($rutaFisica))
                                    ? $rutaPublica
                                    : '../public/imagenes/default.png';
                            ?>
                            <tr
                                data-id="<?= $p['id_producto'] ?>"
                                data-nombre="<?= htmlspecialchars($p['nombre'], ENT_QUOTES, 'UTF-8') ?>"
                                data-precio="<?= $p['precio'] ?>"
                                data-descripcion="<?= htmlspecialchars($p['descripcion'], ENT_QUOTES, 'UTF-8') ?>"
                                data-cantidad="<?= $p['cantidad'] ?>"
                                data-estado="<?= $p['estado'] ?>"
                            >
                                <td><img src="<?= $img ?>" alt="Producto" width="70"></td>
                                <td><?= $p['id_producto'] ?></td>
                                <td><?= htmlspecialchars($p['nombre']) ?></td>
                                <td>$<?= number_format($p['precio'], 2) ?></td>
                                <td><?= htmlspecialchars($p['descripcion']) ?></td>
                                <td><?= $p['cantidad'] ?></td>
                                <td>
                                    <?php if ($p['estado'] == 1): ?>
                                        <span class="estado-activo">Activo</span>
                                    <?php else: ?>
                                        <span class="estado-inactivo">Inactivo</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button type="button" class="btn-sistema btn-editar editBtn">
                                        Modificar
                                    </button>

                                    <button
                                        type="button"
                                        class="btn-sistema btn-eliminar toggleBtn"
                                        data-id="<?= $p['id_producto'] ?>"
                                        data-estado="<?= $p['estado'] ?>"
                                    >
                                        <?= $p['estado'] == 1 ? 'Inactivar' : 'Activar' ?>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align:center;">No se encontraron productos</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>

<!-- ================= MODAL MODIFICAR ================= -->
<div class="modal fade" id="modalModificar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Modificar producto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <form id="formModificar" enctype="multipart/form-data">
                    <input type="hidden" name="id" id="prod_id">

                    <label class="form-label">Nombre</label>
                    <input type="text" name="nombre" id="prod_nombre" class="form-control mb-2" required>

                    <label class="form-label">Precio</label>
                    <input type="number" step="0.01" name="precio" id="prod_precio" class="form-control mb-2" required>

                    <label class="form-label">Descripción</label>
                    <textarea name="descripcion" id="prod_descripcion" class="form-control mb-2" required></textarea>

                    <label class="form-label">Cantidad</label>
                    <input type="number" name="cantidad" id="prod_cantidad" class="form-control mb-2" required>

                    <label class="form-label">Estado</label>
                    <select name="estado" id="prod_estado" class="form-control mb-2" required>
                        <option value="1">Activo</option>
                        <option value="0">Inactivo</option>
                    </select>

                    <label class="form-label">Imagen</label>
                    <input type="file" name="imagen" class="form-control mb-3">

                    <button type="submit" class="btn-sistema btn-guardar" style="width:100%;">Guardar cambios</button>
                </form>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
const modalModificar = new bootstrap.Modal(document.getElementById('modalModificar'));

$('.editBtn').on('click', function () {
    let tr = $(this).closest('tr');

    $('#prod_id').val(tr.data('id'));
    $('#prod_nombre').val(tr.data('nombre'));
    $('#prod_precio').val(tr.data('precio'));
    $('#prod_descripcion').val(tr.data('descripcion'));
    $('#prod_cantidad').val(tr.data('cantidad'));
    $('#prod_estado').val(tr.data('estado'));

    modalModificar.show();
});

$('#formModificar').on('submit', function (e) {
    e.preventDefault();

    let datos = new FormData(this);

    $.ajax({
        url: '../privadas/product_update.php',
        type: 'POST',
        data: datos,
        contentType: false,
        processData: false,
        success: function () {
            Swal.fire({
                icon: 'success',
                title: 'Producto actualizado',
                text: 'Los cambios se guardaron correctamente'
            }).then(() => {
                location.reload();
            });
        },
        error: function () {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudo actualizar el producto'
            });
        }
    });
});

$('.toggleBtn').on('click', function () {
    let id = $(this).data('id');
    let estado = $(this).data('estado');
    let nuevo = estado == 1 ? 0 : 1;

    Swal.fire({
        title: '¿Cambiar estado?',
        text: 'Se actualizará el estado del producto.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, continuar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('../privadas/estado.php', { id: id, estado: nuevo }, function () {
                location.reload();
            }).fail(function () {
                Swal.fire('Error', 'No se pudo cambiar el estado', 'error');
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