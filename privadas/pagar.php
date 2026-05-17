<?php
session_start();

require_once __DIR__ . '/../config/Conexion.php';
require_once __DIR__ . '/../config/seguridad.php';

verificarRoles([1,2,3]);

if(!isset($_SESSION['id_usuario'])){
    header("Location: " . BASE_URL . "index.php");
    exit();
}

if(isset($_POST['btnaccion']) && $_POST['btnaccion'] === 'proceder'){

    $base = Conexion::conectar();

    try {

        $base->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $base->beginTransaction();

       $id_usuario = $_SESSION['id_cliente'] ?? $_SESSION['id_usuario'];

        // ============================
        // OBTENER CARRITO
        // ============================
        $stmtCarrito = $base->prepare("SELECT * FROM carrito WHERE id_usuario=?");
        $stmtCarrito->execute([$id_usuario]);
        $carrito = $stmtCarrito->fetchAll(PDO::FETCH_ASSOC);

        if(count($carrito) == 0){
            throw new Exception("El carrito está vacío");
        }

        // ============================
        // CALCULAR TOTAL
        // ============================
        $total_general = 0;
        foreach($carrito as $p){
            $total_general += $p['precio'] * $p['cantidad'];
        }

        $fecha = date('Y-m-d H:i:s');

        // ============================
        // INSERTAR VENTA (CABECERA)
        // ============================
        $stmtVenta = $base->prepare("
           INSERT INTO ventas (id_usuario, total, fecha, estado_pago)
           VALUES (?, ?, ?, 'pendiente')
        ");

        $stmtVenta->execute([$id_usuario, $total_general, $fecha]);

        $id_venta = $base->lastInsertId();

        // ============================
        // INSERTAR DETALLE + STOCK
        // ============================
        foreach($carrito as $producto){

            $descripcion = $producto['descripcion'];
            $precio = $producto['precio'];
            $cantidad = $producto['cantidad'];
            $total = $precio * $cantidad;

            // -------- STOCK --------
            $stmtStock = $base->prepare("
                UPDATE productos 
                SET cantidad = cantidad - ? 
                WHERE id_producto = ? 
                AND cantidad >= ?
            ");

            $stmtStock->execute([
                $cantidad,
                $producto['id_producto'],
                $cantidad
            ]);

            if($stmtStock->rowCount() == 0){
                throw new Exception("Sin stock suficiente");
            }

            // -------- DETALLE --------
            $stmtDetalle = $base->prepare("
                INSERT INTO detalle_venta
                (id_venta, descripcion, cantidad, precio, total)
                VALUES (?, ?, ?, ?, ?)
            ");

            $stmtDetalle->execute([
                $id_venta,
                $descripcion,
                $cantidad,
                $precio,
                $total
            ]);
        }

        
        // ============================
        // VACIAR CARRITO
        // ============================
        $stmtVaciar = $base->prepare("DELETE FROM carrito WHERE id_usuario=?");
        $stmtVaciar->execute([$id_usuario]);

        $base->commit();
                // ============================
               // ENVIAR CORREO
               // ============================

                 $correo = $_SESSION['correo'] ?? '';

                   if($correo){

                     $asunto = "Pedido confirmado";

                        $mensajeCorreo = "
                         Hola ".$_SESSION['nombre']."<br><br>

                      Tu pedido ha sido registrado correctamente.<br><br>

                          <b>Total:</b> $".$total_general."<br>
                            <b>Fecha:</b> ".$fecha."<br><br>

                             En breve podrás realizar el pago desde tu panel.<br><br>

                            Gracias por tu compra.
                                                  ";

                                     $headers = "MIME-Version: 1.0" . "\r\n";
                                     $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                                     $headers .= "From: sistema@tuempresa.com";

                                       mail($correo, $asunto, $mensajeCorreo, $headers);
                                            }
        $mensaje = 'Swal.fire({
            icon:"success",
            title:"Compra realizada correctamente",
            timer:2000,
            showConfirmButton:false
        }).then(()=>{window.location.href="' . BASE_URL . 'data/facturas.php";});';

    } catch(Exception $e){

        $base->rollBack();

        $mensaje = 'Swal.fire({
            icon:"error",
            title:"Error en la compra",
            text:"'.$e->getMessage().'"
        }).then(()=>{window.location.href="' . BASE_URL . 'app/mostrarcarro.php";});';
    }

} else {

    $mensaje = 'Swal.fire({
        icon:"warning",
        title:"Acción inválida",
        timer:2000,
        showConfirmButton:false
    }).then(()=>{window.location.href="' . BASE_URL . 'app/mostrarcarro.php";});';
}
?>

<!DOCTYPE html>
<html>
<head>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<script>
<?= $mensaje ?>
</script>
</body>
</html>