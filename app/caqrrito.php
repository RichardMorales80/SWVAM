<?php
// ============================
// INICIAR SESIÓN (solo si no está iniciada)
// ============================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../global/configuracion.php';
require_once '../config/Conexion.php';

$pdo = Conexion::conectar();
$mensaje = "";

// ============================
// VALIDAR USUARIO
// ============================
$id_usuario = $_SESSION['id_usuario'] ?? null;
if (!$id_usuario) {
    if (isset($_POST['ajax'])) {
        echo json_encode(['error' => "Debes iniciar sesión"]);
        exit;
    }
    $mensaje = "Debes iniciar sesión";
    return;
}

// ============================
// ACCIONES DEL CARRITO
// ============================
if (isset($_POST['btnaccion'])) {

    switch ($_POST['btnaccion']) {

        // =========================
        // AGREGAR PRODUCTO
        // =========================
        case 'Agregar':

            $ID = openssl_decrypt($_POST['id'], COD, KEY);
            $DESCRIPCION = openssl_decrypt($_POST['nombre'], COD, KEY); // 'nombre' lo guardamos como descripción
            $PRECIO = openssl_decrypt($_POST['precio'], COD, KEY);
            $CANTIDAD = intval($_POST['cantidad'] ?? 1);

            // Validación básica
            if (!is_numeric($ID) || !is_numeric($PRECIO)) break;

            // Revisar si ya existe el producto en el carrito
            $stmt = $pdo->prepare("SELECT cantidad FROM carrito WHERE id_usuario=? AND id_producto=?");
            $stmt->execute([$id_usuario, $ID]);
            $existe = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existe) {
                // Actualizar cantidad
                $nuevaCantidad = $existe['cantidad'] + $CANTIDAD;
                $update = $pdo->prepare(
                    "UPDATE carrito SET cantidad=? WHERE id_usuario=? AND id_producto=?"
                );
                $update->execute([$nuevaCantidad, $id_usuario, $ID]);
                $mensaje = "Cantidad actualizada";

            } else {
                // Insertar nuevo producto
                $insert = $pdo->prepare(
                    "INSERT INTO carrito (id_usuario, id_producto, descripcion, precio, cantidad)
                     VALUES (?,?,?,?,?)"
                );
                $insert->execute([$id_usuario, $ID, $DESCRIPCION, $PRECIO, $CANTIDAD]);
                $mensaje = "Producto agregado";
            }

            break;

        // =========================
        // ELIMINAR PRODUCTO
        // =========================
        case 'Eliminar':

            $ID = openssl_decrypt($_POST['id'], COD, KEY);
            $delete = $pdo->prepare("DELETE FROM carrito WHERE id_usuario=? AND id_producto=?");
            $delete->execute([$id_usuario, $ID]);
            $mensaje = "Producto eliminado";

            break;
    }

    // =========================
    // RESPUESTA AJAX (cantidad total)
    // =========================
    if (isset($_POST['ajax'])) {
        $totalCarrito = 0;
        $stmt = $pdo->prepare("SELECT cantidad FROM carrito WHERE id_usuario=?");
        $stmt->execute([$id_usuario]);
        $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($productos as $p) {
            $totalCarrito += $p['cantidad'];
        }

        echo json_encode([
            'mensaje' => $mensaje,
            'total' => $totalCarrito
        ]);
        exit;
    }
}
?>

