<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once '../config/Conexion.php';

// Validar que exista la sesión
$id_usuario = $_SESSION['id_usuario'] ?? 0;

if ($id_usuario == 0) {
    echo json_encode(['total' => 0]);
    exit;
}

try {
    $pdo = Conexion::conectar();

    // Sumar todas las cantidades de productos del usuario, si no hay, devolver 0
    $sql = "SELECT COALESCE(SUM(cantidad),0) AS total FROM carrito WHERE id_usuario = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_usuario]);
    $total = $stmt->fetchColumn();

    // Devolver JSON
    echo json_encode(['total' => (int)$total]);

} catch (Exception $e) {
    // En caso de error en la BD
    echo json_encode(['total' => 0, 'error' => $e->getMessage()]);
}
