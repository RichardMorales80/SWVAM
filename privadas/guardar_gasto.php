<?php

require_once '../config/Conexion.php';
require __DIR__ . '/../config/seguridad.php';

verificarRoles([1,3]);

if(!isset($_SESSION['id_usuario'])){
    header("Location: ../views/login.php");
    exit();
}

$pdo = Conexion::conectar();

$id_usuario = $_SESSION['id_usuario'];

if($_SERVER['REQUEST_METHOD'] === 'POST'){

    $concepto = trim($_POST['concepto'] ?? '');
    $total    = floatval($_POST['total'] ?? 0);

    if($concepto == '' || $total <= 0){
        header("Location: gastos.php");
        exit();
    }

    $sql = "INSERT INTO gastos (id_usuario, concepto, fecha, total)
            VALUES (:id_usuario, :concepto, NOW(), :total)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':id_usuario' => $id_usuario,
        ':concepto'   => $concepto,
        ':total'      => $total
    ]);

    header("Location: gastos.php");
    exit();
}