<?php

function registrarBitacora($pdo, $id_usuario, $accion){

    $sql = "INSERT INTO bitacora (id_usuario, accion, fecha)
            VALUES (?, ?, NOW())";

    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$id_usuario, $accion]);
}

function obtenerBitacora($pdo, $limite = 5){

    $sql = "SELECT 
                b.id_bitacora,
                b.accion,
                b.fecha,
                CONCAT(
                    COALESCE(u.primer_nombre,''),' ',
                    COALESCE(u.primer_apellido,'')
                ) AS nombre_usuario
            FROM bitacora b
            LEFT JOIN usuarios u ON u.id_usuario = b.id_usuario
            ORDER BY b.fecha DESC
            LIMIT $limite";

    return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}