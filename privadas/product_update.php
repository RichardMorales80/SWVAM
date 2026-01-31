<?php
require '../config/Conexion.php';

$base = Conexion::conectar();

if($_SERVER['REQUEST_METHOD']=='POST'){

    $id = $_POST['id'];
    $nombre = trim($_POST['nombre']);
    $precio = $_POST['precio'];
    $descripcion = trim($_POST['descripcion']);
    $cantidad = $_POST['cantidad'];

    // 👇 si no llega estado, queda activo
    $estado = isset($_POST['estado']) ? $_POST['estado'] : 1;

    /* ==== IMAGEN ACTUAL ==== */
    $stmt = $base->prepare("SELECT imagen FROM productos WHERE id_producto=?");
    $stmt->execute([$id]);
    $imgActual = $stmt->fetchColumn();

    $imgFinal = $imgActual;

    /* ==== CARPETA ==== */
    $dir = realpath(__DIR__.'/../uploads') . '/';

    if(!is_dir($dir)){
        mkdir($dir,0755,true);
    }

    /* ==== SUBIR NUEVA ==== */
    if(!empty($_FILES['imagen']['name'])){

        $ext = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);

        $nuevo = time().'_'.uniqid().'.'.$ext;

        if(move_uploaded_file($_FILES['imagen']['tmp_name'], $dir.$nuevo)){

            if($imgActual && file_exists($dir.$imgActual)){
                unlink($dir.$imgActual);
            }

            $imgFinal = $nuevo;
        }
    }

    /* ==== UPDATE ==== */
    $sql = "UPDATE productos SET
            nombre = ?,
            precio = ?,
            descripcion = ?,
            cantidad = ?,
            imagen = ?,
            estado = ?
            WHERE id_producto = ?";

    $stmtUp = $base->prepare($sql);

    $stmtUp->execute([
        $nombre,
        $precio,
        $descripcion,
        $cantidad,
        $imgFinal,
        $estado,
        $id
    ]);

    echo "success";
}
