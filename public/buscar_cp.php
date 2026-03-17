<?php
require_once '../config/Conexion.php';

header('Content-Type: application/json; charset=utf-8');

try {
    if (!isset($_POST['codigo_postal'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Código postal no recibido.'
        ]);
        exit;
    }

    $cp = trim($_POST['codigo_postal']);

    if (!preg_match('/^\d{5}$/', $cp)) {
        echo json_encode([
            'success' => false,
            'message' => 'El código postal debe tener 5 dígitos.'
        ]);
        exit;
    }

    $db = Conexion::conectar();

    $sql = "SELECT d_asenta, D_mnpio, d_estado
            FROM cat_cp
            WHERE d_codigo = :cp
            ORDER BY d_asenta ASC";

    $stmt = $db->prepare($sql);
    $stmt->bindParam(':cp', $cp, PDO::PARAM_STR);
    $stmt->execute();

    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$resultados || count($resultados) === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'No existe información para ese código postal.'
        ]);
        exit;
    }

    $colonias = [];
    foreach ($resultados as $fila) {
        $colonias[] = $fila['d_asenta'];
    }

    echo json_encode([
        'success' => true,
        'estado' => $resultados[0]['d_estado'],
        'ciudad' => $resultados[0]['D_mnpio'],
        'colonias' => $colonias
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al consultar el código postal.'
    ]);
}
?>