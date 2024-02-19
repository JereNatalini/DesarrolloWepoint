<?php
require 'vendor/autoload.php';
use Firebase\JWT\JWT;


/*
Test para generar el token de autenticación 
*/

// Cambiar en base a lo que hagamos nosotros 

// Esto es para ver cómo funciona 

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

    $jwt = JWT::encode($token, "dfhsdfg34dfchs4xgsrsdry46", 'HS256'); 

    echo '<pre>'; print_r($token); echo '</pre>';

    return $jwt;
}