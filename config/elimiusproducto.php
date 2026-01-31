<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['codigoBarras'])) {
    include 'config/Conexion.php';

    try {
        $codigo = $_POST['codigoBarras'];
        $base = Conexion::conectar();

        $query = "UPDATE tablaproductos SET estado = 1 WHERE codigoBarras = :codigo";
        $stmt = $base->prepare($query);
        $stmt->bindParam(':codigo', $codigo, PDO::PARAM_STR);
        $stmt->execute();

        header("Location: resp.php?msg=activado");
        exit;

    } catch (PDOException $e) {
        echo "Error al activar producto: " . $e->getMessage();
    }
} else {
    echo "No se recibió el código.";
}

