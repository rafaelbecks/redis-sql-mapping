<?php

require 'vendor/autoload.php';
require "connection.php";
require "redis-mapping.php";

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

$app->get("/logs",function() use ($sqlConn){

    $sql = "SELECT * FROM log_file";

    $logs = array();

     foreach($sqlConn->query($sql) as $fila) {
        $logs[] = $fila;
    }

    echo json_encode($logs);
});

$app->post("/logs",function() use($app,$sqlConn,$redis,$mappingArray){

    try {
        $body = json_decode($app->request->getBody());

        $sql = "INSERT INTO log_file (username, ip_adress, accion, fecha_accion, url, body_request, body_response, http, http_status) VALUES (
                '".$body->username."',
                '".$body->ip_adress."',
                '".$body->accion."',
                '".$body->fecha_accion."',
                '".$body->url."',
                '".$body->body_request."',
                '".$body->body_response."',
                '".$body->http."',
                '".$body->http_status."'
            )";

        if($sqlConn->exec($sql))
        {
            $redis->sAdd($body->username, $body->username);
            $redis->sAdd($body->ip_adress, $body->username);
            $redis->sAdd($mappingArray[$body->accion], $body->username);
            $redis->sAdd($body->fecha_accion, $body->username);
            $redis->sAdd($body->url, $body->username);
            $redis->sAdd($body->body_request, $body->username);
            $redis->sAdd($body->body_response, $body->username);
            $redis->sAdd($mappingArray[$body->http], $body->username);
            $redis->sAdd($mappingArray[$body->http_status], $body->username);
        }
       
    } catch (Exception $e) {
        echo $e->getMessage();        
    }

    echo json_encode(array("mensaje" => "Se ha registrado el log"));

});

$app->get("/logs/inter/:criteria",function($criteria) use($app,$redis){

    //criteria : post-200-127.0.0.1

    try {
        $arraySets = explode("-",$criteria);

        $result = call_user_func_array(array($redis, "sInter"), $arraySets);           
    
        $respuesta = (count($result)>0) ? $result : "No existen elementos comunes entre los sets";

        echo json_encode(array("mensaje" => $respuesta));


    } catch (Exception $e) {
        echo $e->getMessage();        
    }

    
    


});


$app->run();

?>
