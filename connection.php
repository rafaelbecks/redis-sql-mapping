<?php 

//Conexión a servidor de Redis

$redis = new Redis();
$connection = $redis->connect("127.0.0.1", "6379");

//Conexión a servidor de MySQL

$usuario = "root";
$password = "11235813";

$sqlConn = new PDO('mysql:host=localhost;dbname=redis-sql-mapping', $usuario, $password);
?>