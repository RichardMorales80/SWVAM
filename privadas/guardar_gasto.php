<?php
session_start();

require_once '../config/Conexion.php';
require __DIR__ . '/../config/seguridad.php';

verificarRoles([1,3]);

if(!isset($_SESSION['id_usuario'])){
    header("Location: ../index.php");
    exit();
}

$pdo = Conexion::conectar();
require_once __DIR__ . '/../config/bitacora.php';
registrarVisitaPagina($pdo);
$id_usuario = $_SESSION['id_usuario'];

if($_SERVER['REQUEST_METHOD'] === 'POST'){

    $concepto = trim($_POST['concepto'] ?? '');
    $total    = floatval($_POST['total'] ?? 0);

    if($concepto === '' || $total <= 0){
        header("Location: ../privadas/gastos.php?error=1");
        exit();
    }

    $sql = "INSERT INTO gastos (id_usuario, concepto, fecha, total)
            VALUES (:id_usuario, :concepto, NOW(), :total)";

    $stmt = $pdo->prepare($sql);

    $guardado = $stmt->execute([
        ':id_usuario' => $id_usuario,
        ':concepto'   => $concepto,
        ':total'      => $total
    ]);

    if($guardado){
        header("Location: ../privadas/gastos.php?ok=1");
        exit();
    } else {
        header("Location: ../privadas/gastos.php?error=2");
        exit();
    }
}

header("Location: ../privadas/gastos.php");
exit();