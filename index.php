<?php

require 'vendor/autoload.php';
require "connection.php";

$app = new \Slim\Slim;

$app->response->headers->set('Content-Type', 'application/json');


$app->get('/', function () {
    echo "Redis Client API";
});

$app->get('/verificacion-conexion', function () use($connection) {
    try {
        if($connection)
            $response = array("mensaje" => "La conexión a redis ha sido configurada exitosamente");
        else
            $response = array("mensaje" => "La conexión a redis no ha podido establecerse");

        echo json_encode($response);

    } catch (Exception $e) {
        $response = array("mensaje" => "Ha ocurrido un error al intentar conectarse a Redis: ".$e->getMessage());

        echo json_encode($response);
    }
});


$app->run();

?>
