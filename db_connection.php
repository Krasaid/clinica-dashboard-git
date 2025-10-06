<?php
// db_connection.php
 = "localhost";
 = "root"; // TU USUARIO DE MYSQL
 = "";     // TU CONTRASEÑA DE MYSQL (vacío en XAMPP/WAMP por defecto)
 = "clinica_db";

// Crear conexión
 = new mysqli(, , , );

// Verificar conexión
if (->connect_error) {
    die("Conexión fallida: " . ->connect_error);
}
?>
