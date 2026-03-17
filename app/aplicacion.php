<?php

require_once '../global/configuracion.php';
require_once '../config/Conexion.php';
require __DIR__ . '/../config/seguridad.php';

verificarRoles([1,2,3]);

$pdo = Conexion::conectar();
include '../app/caqrrito.php';
include '../templetes/cabecera.php';

$sql = "
SELECT 
    id_producto,
    nombre,
    descripcion,
    precio,
    cantidad AS stock,
    imagen
FROM productos
WHERE estado = 1
ORDER BY id_producto DESC
";

$consulta = $pdo->prepare($sql);
$consulta->execute();
$productos = $consulta->fetchAll(PDO::FETCH_ASSOC);

$totalCarrito = 0;

if (isset($_SESSION['id_usuario'])) {
    $stmt = $pdo->prepare("SELECT SUM(cantidad) FROM carrito WHERE id_usuario = ?");
    $stmt->execute([$_SESSION['id_usuario']]);
    $totalCarrito = $stmt->fetchColumn() ?? 0;
}
?>



<!-- ================= TOPBAR ================= -->

<div class="topbar">

    <div class="topbar-left">
        <h4>Catálogo de productos</h4>
    </div>

    <div class="topbar-user">
        <span class="usuario-nombre">
            <?= htmlspecialchars($_SESSION['nombre'] ?? 'Usuario') ?>
        </span>

        <img src="../public/imagenes/avatar.png" class="avatar" alt="Avatar">
    </div>

</div>

<!-- ================= CONTENIDO ================= -->

<div class="main-content">

    <div class="container">

        <div class="catalogo-grid">

            <?php foreach ($productos as $p): ?>

                <?php
                $id = (int)$p['id_producto'];
                $nombre = $p['nombre'];
                $precio = (float)$p['precio'];
                $stock = (int)$p['stock'];
                $desc = $p['descripcion'];
                $img = $p['imagen'];

                $rutaFisica = __DIR__ . '/../uploads/' . $img;

                $imgPublica = (!empty($img) && file_exists($rutaFisica))
                    ? '../uploads/' . $img
                    : '../uploads/default.png';
                ?>

                <div class="producto-item">

                    <img src="<?= htmlspecialchars($imgPublica) ?>" class="producto-img" alt="Producto">

                    <h4 class="producto-titulo">
                        <?= htmlspecialchars($nombre) ?>
                    </h4>

                    <p class="producto-precio">
                        $<?= number_format($precio, 2) ?>
                    </p>

                    <p class="producto-desc">
                        <?= htmlspecialchars($desc) ?>
                    </p>

                    <p class="producto-stock">
                        Stock:
                        <span style="color:<?= $stock > 0 ? 'green' : 'red' ?>">
                            <?= $stock ?>
                        </span>
                    </p>

                    <form method="post">
                        <input type="hidden" name="id" value="<?= openssl_encrypt($id, COD, KEY) ?>">
                        <input type="hidden" name="nombre" value="<?= openssl_encrypt($nombre, COD, KEY) ?>">
                        <input type="hidden" name="precio" value="<?= openssl_encrypt($precio, COD, KEY) ?>">
                        <input type="hidden" name="stock" value="<?= $stock ?>">

                        <input
                            type="number"
                            name="cantidad"
                            value="1"
                            min="1"
                            max="<?= $stock ?>"
                            class="cantidad"
                        >

                        <?php if ($stock > 0): ?>
                            <button type="submit" name="btnaccion" value="Agregar" class="btn-agregar">
                                Agregar al carrito
                            </button>
                        <?php else: ?>
                            <button class="btn-sinstock" disabled>
                                Sin stock
                            </button>
                        <?php endif; ?>
                    </form>

                </div>

            <?php endforeach; ?>

        </div>

        <!-- ================= PAGINACION ================= -->

        <div class="paginacion-container">
            <button class="pag-btn" id="prev">◀</button>
            <div id="paginacion"></div>
            <button class="pag-btn" id="next">▶</button>
        </div>

    </div>

</div>

<!-- ================= JS ================= -->

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
const productos = document.querySelectorAll(".producto-item");
const porPagina = 6;
let pagina = 1;

function mostrarPagina(p) {
    pagina = p;

    productos.forEach(prod => prod.style.display = "none");

    const inicio = (p - 1) * porPagina;
    const fin = inicio + porPagina;

    for (let i = inicio; i < fin && i < productos.length; i++) {
        productos[i].style.display = "block";
    }

    document.querySelectorAll("#paginacion button").forEach(btn => {
        btn.classList.remove("activo");
    });

    if (document.querySelectorAll("#paginacion button")[p - 1]) {
        document.querySelectorAll("#paginacion button")[p - 1].classList.add("activo");
    }
}

function crearPaginacion() {
    const totalPaginas = Math.ceil(productos.length / porPagina);
    const cont = document.getElementById("paginacion");
    cont.innerHTML = "";

    for (let i = 1; i <= totalPaginas; i++) {
        const btn = document.createElement("button");
        btn.innerText = i;
        btn.onclick = () => mostrarPagina(i);
        cont.appendChild(btn);
    }
}

document.getElementById("prev").onclick = () => {
    if (pagina > 1) {
        mostrarPagina(pagina - 1);
    }
};

document.getElementById("next").onclick = () => {
    if (pagina < Math.ceil(productos.length / porPagina)) {
        mostrarPagina(pagina + 1);
    }
};

crearPaginacion();
mostrarPagina(1);
</script>

<!-- ================= MENSAJE PRODUCTO AGREGADO ================= -->

<?php if (isset($_SESSION['mensaje_carrito'])): ?>
<script>
Swal.fire({
    icon: 'success',
    title: 'Producto agregado',
    text: '<?= htmlspecialchars($_SESSION['mensaje_carrito'], ENT_QUOTES, 'UTF-8') ?>',
    timer: 1500,
    showConfirmButton: false
});
</script>
<?php unset($_SESSION['mensaje_carrito']); endif; ?>
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