<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../global/configuracion.php';
require_once '../config/Conexion.php';

$pdo = Conexion::conectar();

/* =========================
   DEFINIR USUARIO CARRITO
========================= */

if (($_SESSION['id_rol'] ?? 0) == 2) {
    // CLIENTE
    $usuario_carrito = $_SESSION['id_usuario'];
} else {
    // ADMIN / VENDEDOR
    $usuario_carrito = $_SESSION['id_cliente'] ?? null;
}

/* =========================
   ACCIONES DEL CARRITO
========================= */

if (isset($_POST['btnaccion'])) {

    switch ($_POST['btnaccion']) {

        case 'Agregar':

            
            if (($_SESSION['id_rol'] ?? 0) != 2 && empty($_SESSION['id_cliente'])) {

                $_SESSION['mensaje'] = "Primero selecciona un cliente";
                header("Location: ../app/seleccionar_cliente.php");
                exit;
            }

            $ID = openssl_decrypt($_POST['id'], COD, KEY);
            $DESCRIPCION = openssl_decrypt($_POST['nombre'], COD, KEY);
            $PRECIO = openssl_decrypt($_POST['precio'], COD, KEY);
            $CANTIDAD = intval($_POST['cantidad'] ?? 1);

            if (!is_numeric($ID) || !is_numeric($PRECIO)) {
                break;
            }

            // STOCK
            $stmtStock = $pdo->prepare("SELECT cantidad FROM productos WHERE id_producto=?");
            $stmtStock->execute([$ID]);
            $stock = $stmtStock->fetchColumn() ?? 0;

            // EXISTE EN CARRITO
            $stmt = $pdo->prepare("SELECT cantidad FROM carrito WHERE id_usuario=? AND id_producto=?");
            $stmt->execute([$usuario_carrito, $ID]);
            $existe = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existe) {

                $nuevaCantidad = $existe['cantidad'] + $CANTIDAD;

                if ($nuevaCantidad > $stock) {
                    $_SESSION['error_stock'] = "No hay stock disponible";
                    break;
                }

                $update = $pdo->prepare(
                    "UPDATE carrito SET cantidad=? WHERE id_usuario=? AND id_producto=?"
                );
                $update->execute([$nuevaCantidad, $usuario_carrito, $ID]);

                $_SESSION['mensaje'] = "Cantidad actualizada";

            } else {

                if ($CANTIDAD > $stock) {
                    $_SESSION['error_stock'] = "No hay stock disponible";
                    break;
                }

                $insert = $pdo->prepare(
                    "INSERT INTO carrito (id_usuario, id_producto, descripcion, precio, cantidad)
                     VALUES (?,?,?,?,?)"
                );

                $insert->execute([
                    $usuario_carrito,
                    $ID,
                    $DESCRIPCION,
                    $PRECIO,
                    $CANTIDAD
                ]);

                $_SESSION['mensaje'] = "Producto agregado correctamente";
            }

            break;

        case 'Eliminar':

            $ID = openssl_decrypt($_POST['id'], COD, KEY);

            if ($ID) {
                $delete = $pdo->prepare(
                    "DELETE FROM carrito WHERE id_usuario=? AND id_producto=?"
                );
                $delete->execute([$usuario_carrito, $ID]);

                $_SESSION['mensaje'] = "Producto eliminado correctamente";
            }

            break;
    }

    /* =========================
       RESPUESTA AJAX
    ========================= */

    if (isset($_POST['ajax'])) {

        $totalCarrito = 0;

        $stmt = $pdo->prepare("SELECT cantidad FROM carrito WHERE id_usuario=?");
        $stmt->execute([$usuario_carrito]);
        $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($productos as $p) {
            $totalCarrito += $p['cantidad'];
        }

        echo json_encode([
            'success' => true,
            'mensaje' => $_SESSION['mensaje'] ?? '',
            'total' => $totalCarrito
        ]);

        exit;
    }

    /* =========================
       REDIRECCIÓN FINAL
    ========================= */

    if (($_SESSION['id_rol'] ?? 0) == 2) {
        header("Location: ../app/cliente_app.php");
    } else {
        header("Location: ../app/aplicacion.php");
    }
    exit;
}
?>