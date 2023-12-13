<?php
$servidor="mysql:dbname=Tienda;host=127.0.0.1";
$usuario="root";
$password="";

try {
    $pdo=new PDO($servidor, $usuario, $password, array
    (PDO::MYSQL_ATTR_INIT_COMMAND=> "SET NAMES utf8"));
    //echo  " Conexion a BD exitosa";
} catch (PDOException $e) {
    //echo "Conexion: fallida".$e->getMessage();
    
}


?>