<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include '../config/Conexion.php';
    $base = Conexion::conectar();

    // Validar que el campo 'estado' esté definido y sea válido
    if (!isset($_POST['estado'])) {
        die("Error: El campo 'estado' es obligatorio.");
    }

    $estado = $_POST['estado'];
    if ($estado !== '0' && $estado !== '1') {
        die("Error: El campo 'estado' es inválido.");
    }

    // Validar también otros campos si quieres (opcional)
    $idproducto = $_POST['idproducto'] ?? null;
    $nombre = $_POST['nombre'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';
    $precio = $_POST['precio'] ?? '';

    if (!$idproducto) {
        die("Error: No se recibió el código del producto.");
    }

    // Preparar y ejecutar consulta
    $query = "UPDATE tablaproductos 
              SET nombre = :nombre, descripcion = :descripcion, precioVenta = :precio, estado = :estado
              WHERE codigoBarras = :idproducto";

    $stmt = $base->prepare($query);
    $resultado = $stmt->execute([
        ':nombre' => $nombre,
        ':descripcion' => $descripcion,
        ':precio' => $precio,
        ':estado' => $estado,
        ':idproducto' => $idproducto
    ]);

    if ($resultado) {
        header("Location:../views/productos.php?msg=modificado");
        exit();
    } else {
        die("Error: No se pudo actualizar el producto.");
    }
}
?>

