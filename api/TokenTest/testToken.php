<?php
require 'vendor/autoload.php';
use Firebaze\JWT\JWT;
use Firebase\JWT\Key;


/*
Test para generar el token de autenticacion 
*/

//Cambiar en base alo que hagamo nosotros 

//Esto es para ver como funciona 



function generarTokenCliente($id, $email){

    $time = time();

    $token = array(
    
        "iat" => $time, //Tiempo en que inicia el token
        "exp" => $time + (60*60*24),  // Tiempo en que expirará el token (1 día)
    

        $ata = [

            "id" => $id,
            "email" => $email 
        ]

      
    );

    $jwt = JWT::encode($token , "dfhsdfg34dfchs4xgsrsdry46"); 
       

   echo '<pre>'; print _r($token); echo '</pre>';

    return $jwt;
    

}

?>