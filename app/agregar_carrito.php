

<?php
session_start();
include 'conex.php';

$id_usuario = $_SESSION['id_usuario']; // asegúrate de guardarlo al login

if(isset($_GET['id'])){

  $id_producto = $_GET['id'];

  // VERIFICAR SI YA ESTÁ EN CARRITO
  $consulta = $conexion->query("SELECT * FROM carrito 
                                WHERE id_usuario='$id_usuario' 
                                AND id_producto='$id_producto'");

  if($consulta->num_rows > 0){

      // SUMAR CANTIDAD
      $conexion->query("UPDATE carrito 
                        SET cantidad = cantidad + 1 
                        WHERE id_usuario='$id_usuario' 
                        AND id_producto='$id_producto'");

  }else{

      // OBTENER PRODUCTO
      $res = $conexion->query("SELECT * FROM productos 
                               WHERE idproducto='$id_producto'");
      $fila = mysqli_fetch_assoc($res);

      $nombre = $fila['nombre'];
      $precio = $fila['precio'];
      $imagen = $fila['imagen'];

      // INSERTAR EN CARRITO
      $conexion->query("INSERT INTO carrito 
        (id_usuario,id_producto,nombre,precio,imagen,cantidad)
        VALUES('$id_usuario','$id_producto','$nombre','$precio','$imagen',1)");
  }
}

?>


