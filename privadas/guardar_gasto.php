<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

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

$id_usuario = $_SESSION['id_usuario'];

if($_SERVER['REQUEST_METHOD'] === 'POST'){

    $concepto = trim($_POST['concepto'] ?? '');
    $total    = floatval($_POST['total'] ?? 0);

    /* =========================
       VALIDACION BASICA
    ========================= */

    if($concepto === '' || $total <= 0){

        header("Location: ../privadas/gastos.php?error=1");
        exit();
    }

    /* =========================
       OBTENER ROL Y PERMISO
    ========================= */

    $sqlUsuario = "SELECT id_rol, permiso_gasto_extra
                   FROM usuarios
                   WHERE id_usuario = :id_usuario";

    $stmtUsuario = $pdo->prepare($sqlUsuario);

    $stmtUsuario->execute([
        ':id_usuario' => $id_usuario
    ]);

    $usuarioData = $stmtUsuario->fetch(PDO::FETCH_ASSOC);

    $id_rol_usuario = (int)($usuarioData['id_rol'] ?? 0);

    $permiso_extra = (int)($usuarioData['permiso_gasto_extra'] ?? 0);

    /* =========================
       LIMITES POR ROL
    ========================= */

    if($id_rol_usuario == 1){

        // ADMINISTRADOR
        $limite_gastos = 10000;

    } else {

        // VENDEDORES Y DEMAS
        $limite_gastos = 3000;
    }

    /* =========================
       TOTAL DEL DIA
    ========================= */

    $sqlTotal = "SELECT COALESCE(SUM(total),0)
                 FROM gastos
                 WHERE id_usuario = :id_usuario
                 AND DATE(fecha) = CURDATE()";

    $stmtTotal = $pdo->prepare($sqlTotal);

    $stmtTotal->execute([
        ':id_usuario' => $id_usuario
    ]);

    $total_actual = (float)$stmtTotal->fetchColumn();

    /* =========================
       LIMITE FINAL
    ========================= */

    $limite_final = $limite_gastos;

    // SI TIENE PERMISO EXTRA
    if($permiso_extra == 1){

        $limite_final += 3000;
    }

    /* =========================
       VALIDAR LIMITE DIARIO
    ========================= */

    if(($total_actual + $total) > $limite_final){

        $exceso = ($total_actual + $total) - $limite_final;

        header("Location: ../privadas/gastos.php?error=3&exceso=" . urlencode($exceso));
        exit();
    }

    /* =========================
       INSERTAR GASTO
    ========================= */

    $sql = "INSERT INTO gastos
            (id_usuario, concepto, fecha, total)
            VALUES
            (:id_usuario, :concepto, NOW(), :total)";

    $stmt = $pdo->prepare($sql);

    $guardado = $stmt->execute([
        ':id_usuario' => $id_usuario,
        ':concepto'   => $concepto,
        ':total'      => $total
    ]);

    if($guardado){

        /* =========================
           QUITAR PERMISO EXTRA
        ========================= */

        if($permiso_extra == 1){

            $sqlReset = "UPDATE usuarios
                         SET permiso_gasto_extra = 0
                         WHERE id_usuario = :id_usuario";

            $stmtReset = $pdo->prepare($sqlReset);

            $stmtReset->execute([
                ':id_usuario' => $id_usuario
            ]);
        }

        /* =========================
           BITACORA
        ========================= */

        registrarBitacora(
            $pdo,
            $id_usuario,
            "Registro de gasto: $concepto ($total)"
        );

        header("Location: ../privadas/gastos.php?ok=1");
        exit();

    } else {

        header("Location: ../privadas/gastos.php?error=2");
        exit();
    }
}

header("Location: ../privadas/gastos.php");
exit();