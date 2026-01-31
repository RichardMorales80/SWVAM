<?php

// ============================
// CLASE DE CONEXIÓN A LA BD
// ============================

class Conexion {
    
    // ============================
    // MÉTODO ESTÁTICO PARA CONECTAR
    // ============================

    // static permite llamarlo sin crear un objeto
    // Ejemplo: Conexion::conectar();
    public static function conectar() {

        try {

            // ============================
            // CREAR CONEXIÓN PDO
            // ============================

            // Se crea un nuevo objeto PDO con:
            // host  → servidor de la base de datos
            // dbname → nombre de la base de datos
            // charset → codificación UTF-8
            $base = new PDO(
                'mysql:host=sql201.byethost7.com;dbname=b7_40012077_Matthew;charset=utf8', 
                'b7_40012077',     // Usuario de la base de datos
                'Negrito123'      // Contraseña de la base de datos
            );

            // ============================
            // RETORNAR CONEXIÓN
            // ============================

            // Devuelve la conexión para usarla en consultas
            return $base;

        } catch (PDOException $e) {

            // ============================
            // MANEJO DE ERRORES
            // ============================

            // Si falla la conexión, detiene el sistema y muestra error
            die("Error de conexión: " . $e->getMessage());
        }
    }
}

?>
