<?php
require 'vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/*
Test para generar el token de autenticación 
*/

function generarTokenCliente($id, $email){
    $time = time();
    $token = array(
        "iat" => $time, // Tiempo en que inicia el token
        "exp" => $time + (60*60*24),  // Tiempo en que expirará el token (1 día)
        "data" => [
            "id" => $id,
            "email" => $email 
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