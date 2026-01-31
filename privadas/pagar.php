<?php
session_start();
include '../config/Conexion.php';

if(!isset($_SESSION['id_usuario'])){
    header("Location: ../public/login.php");
    exit();
}

if(isset($_POST['btnaccion']) && $_POST['btnaccion'] === 'proceder'){

    $base = Conexion::conectar();

    try {

        $base->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $base->beginTransaction();

        $id_usuario = $_SESSION['id_usuario'];

        // ============================
        // OBTENER CARRITO DESDE BD
        // ============================

        $sqlCarrito = "SELECT * FROM carrito WHERE id_usuario=?";
        $stmtCarrito = $base->prepare($sqlCarrito);
        $stmtCarrito->execute([$id_usuario]);
        $carrito = $stmtCarrito->fetchAll(PDO::FETCH_ASSOC);

        if(count($carrito) == 0){
            throw new Exception("El carrito está vacío");
        }

        // ============================
        // PROCESAR COMPRA
        // ============================

        foreach($carrito as $producto){

            $id_producto = $producto['id_producto'];
            $descripcion = $producto['descripcion'];
            $precio = $producto['precio'];
            $cantidad = $producto['cantidad'];
            $total = $precio * $cantidad;
            $fecha = date('Y-m-d H:i:s');

            // -------- DESCONTAR STOCK --------

            $sqlStock = "
                UPDATE productos 
                SET cantidad = cantidad - :cantidad 
                WHERE id_producto = :id_producto
                AND cantidad >= :cantidad
                AND estado = 1
            ";

            $stmtStock = $base->prepare($sqlStock);

            $stmtStock->execute([
                ':cantidad' => $cantidad,
                ':id_producto' => $id_producto
            ]);

            if($stmtStock->rowCount() == 0){
                throw new Exception("Sin stock para producto ID $id_producto");
            }

            // -------- INSERTAR VENTA --------

            $sqlVenta = "
                INSERT INTO ventas
                (id_usuario, id_producto, descripcion, precio, cantidad, total, fecha)
                VALUES
                (:id_usuario, :id_producto, :descripcion, :precio, :cantidad, :total, :fecha)
            ";

            $stmtVenta = $base->prepare($sqlVenta);

            $stmtVenta->execute([
                ':id_usuario' => $id_usuario,
                ':id_producto' => $id_producto,
                ':descripcion' => $descripcion,
                ':precio' => $precio,
                ':cantidad' => $cantidad,
                ':total' => $total,
                ':fecha' => $fecha
            ]);
        }

        // ============================
        // VACIAR CARRITO EN BD
        // ============================

        $sqlVaciar = "DELETE FROM carrito WHERE id_usuario=?";
        $stmtVaciar = $base->prepare($sqlVaciar);
        $stmtVaciar->execute([$id_usuario]);

        $base->commit();

        $mensaje = 'Swal.fire({
            icon:"success",
            title:"Compra realizada correctamente",
            showConfirmButton:false,
            timer:2000
        }).then(()=>{window.location.href="../app/index.php";});';

    } catch(Exception $e){

        $base->rollBack();

        $mensaje = 'Swal.fire({
            icon:"error",
            title:"Error en la compra",
            text:"'.$e->getMessage().'",
            showConfirmButton:true
        }).then(()=>{window.location.href="../app/mostrarcarro.php";});';
    }

} else {

    $mensaje = 'Swal.fire({
        icon:"warning",
        title:"Acción inválida",
        showConfirmButton:false,
        timer:2000
    }).then(()=>{window.location.href="../app/mostrarcarro.php";});';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Procesando compra</title>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>

<script>
<?= $mensaje ?>
</script>

</body>
</html>
