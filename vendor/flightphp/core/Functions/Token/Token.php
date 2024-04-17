<?php
require 'vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;


/* Aca puede que este el error, por ahi no entra al JWT o al Key 




/*
Test para generar el token de autenticación 
*/

function generarTokenCliente($email, $password){
    $time = time();
    $token = array(
        "iat" => $time, // Tiempo en que inicia el token
        "exp" => $time + (60*60*6),  // Tiempo en que expirará el token (1 día)
        "data" => [
            "email" => $email,
            "password" => $password 
        ]
    );
    // La clave secreta es dfhsdfg34dfchs4xgsrsdry46
    $jwt = JWT::encode($token, "dfhsdfg34dfchs4xgsrsdry46", 'HS256'); 
    echo '<pre>'; print_r($token); echo '</pre>';
    return $jwt;
}
function decodificarToken($jwt) {
    try {
        $decoded = JWT::decode($jwt, new key("dfhsdfg34dfchs4xgsrsdry46", 'HS256'));
        $jsonString = json_encode($decoded);
        return (array) $decoded;
    } catch (Exception $e) {
        return null; // Retorna null si hay un error al decodificar el token
    }
}

?>