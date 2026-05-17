<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

require_once '../config/Conexion.php';
require __DIR__ . '/../config/seguridad.php';

/* SOLO ADMIN */
verificarRoles([1]);

if(!isset($_SESSION['id_usuario'])){
    http_response_code(401);
    exit('No autorizado');
}

$pdo = Conexion::conectar();

require_once __DIR__ . '/../config/bitacora.php';

if($_SERVER['REQUEST_METHOD'] === 'POST'){

    $id = intval($_POST['id'] ?? 0);

    /* VALIDAR ID */
    if($id <= 0){
        http_response_code(400);
        exit('ID inválido');
    }

    /* BUSCAR GASTO */
    $sqlBuscar = "SELECT concepto, total
                  FROM gastos
                  WHERE id_gasto = :id";

    $stmtBuscar = $pdo->prepare($sqlBuscar);

    $stmtBuscar->execute([
        ':id' => $id
    ]);

    $gasto = $stmtBuscar->fetch(PDO::FETCH_ASSOC);

    if(!$gasto){
        http_response_code(404);
        exit('Gasto no encontrado');
    }

    /* ELIMINAR */
    $sql = "DELETE FROM gastos
            WHERE id_gasto = :id";

    $stmt = $pdo->prepare($sql);

    $eliminado = $stmt->execute([
        ':id' => $id
    ]);

    if($eliminado){

        registrarBitacora(
            $pdo,
            $_SESSION['id_usuario'],
            "Eliminó gasto: {$gasto['concepto']} ({$gasto['total']})"
        );

        echo "ok";

    } else {

        http_response_code(500);
        echo "Error al eliminar";
    }

    exit();
}

http_response_code(405);
echo "Método no permitido";