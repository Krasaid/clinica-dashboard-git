<?php
// db_connection.php
 = "localhost";
 = "root"; // TU USUARIO DE MYSQL
 = "";     // TU CONTRASE�A DE MYSQL (vac�o en XAMPP/WAMP por defecto)
 = "clinica_db";

// Crear conexi�n
 = new mysqli(, , , );

// Verificar conexi�n
if (->connect_error) {
    die("Conexi�n fallida: " . ->connect_error);
}
?>
