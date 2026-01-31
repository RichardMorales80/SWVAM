<?php
session_start();
require '../config/Conexion.php';

try {
    $pdo = Conexion::conectar();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die("Error de conexión: " . $e->getMessage());
}

if ($_POST['btnaccion'] != 'proceder' || empty($_SESSION['CARRITO']) || empty($_POST['email'])) {
    die("Acción inválida o carrito vacío o correo no enviado");
}

$email = $_POST['email'];
$nombreCliente = "Cliente Web";

// Buscar o crear usuario
$stmt = $pdo->prepare("SELECT id_usuario FROM usuarios WHERE email=?");
$stmt->execute([$email]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if ($usuario) {
    $id_usuario = $usuario['id_usuario'];
} else {
    $stmt = $pdo->prepare("INSERT INTO usuarios(nombre,email) VALUES(?,?)");
    $stmt->execute([$nombreCliente,$email]);
    $id_usuario = $pdo->lastInsertId();
}

$fecha = date('Y-m-d H:i:s');

try {
    $pdo->beginTransaction();
    $stmt = $pdo->prepare("INSERT INTO ventas(id_usuario,id_producto,descripcion,precio,cantidad,total,fecha) VALUES(?,?,?,?,?,?,?)");

    foreach ($_SESSION['CARRITO'] as $producto) {
        $id_producto = intval($producto['ID']);
        $descripcion = trim($producto['NOMBRE']);
        $precio = floatval($producto['PRECIO']);
        $cantidad = intval($producto['CANTIDAD']);
        $total = $precio * $cantidad;

        // Validar datos
        if ($id_producto <= 0 || $precio <= 0 || $cantidad <= 0 || empty($descripcion)) {
            throw new Exception("Datos inválidos para el producto: ".print_r($producto,true));
        }

        $stmt->execute([$id_usuario,$id_producto,$descripcion,$precio,$cantidad,$total,$fecha]);
    }

    $pdo->commit();
    unset($_SESSION['CARRITO']);

    echo "<script>alert('Compra registrada con éxito');window.location.href='../index.php';</script>";
    exit();

} catch (Exception $e) {
    $pdo->rollBack();
    die("Error al registrar la compra: ".$e->getMessage());
}

