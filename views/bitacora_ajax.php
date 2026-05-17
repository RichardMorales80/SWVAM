<?php
require_once __DIR__ . '/../config/Conexion.php';
require_once __DIR__ . '/../config/bitacora.php';

$pdo = Conexion::conectar();

$bitacora = obtenerBitacora($pdo, 10);

foreach($bitacora as $b){
    echo "<tr>
        <td>{$b['id_bitacora']}</td>
        <td>{$b['nombre_usuario']}</td>
        <td>{$b['accion']}</td>
        <td>".date("d/m/Y H:i:s", strtotime($b['fecha']))."</td>
    </tr>";
}