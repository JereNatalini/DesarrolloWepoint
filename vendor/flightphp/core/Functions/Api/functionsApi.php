<?php
require 'Functions/Database/functionsDB.php';

function createProductArrayPO($name, $sku, $purchase_rate) {
    return [
        "name" => $name,
        "sku" => $sku,
        "item_type" => "inventory",
        "purchase_rate" => $purchase_rate
    ];
}

function createProductArraySO($name, $sku, $rate) {
    return [
        "name" => $name,
        "sku" => $sku,
        "item_type" => "inventory",
        "rate" => $rate
    ];
}

function UpdateItemPO($name,$purchase_rate,$sku){
    $statement = Flight::db()->prepare('UPDATE productos SET nombre = ?, purchase_rate = ? WHERE sku = ?');
    $statement->bindParam(1, $name, PDO::PARAM_STR);
    $statement->bindParam(2, $purchase_rate, PDO::PARAM_STR);
    $statement->bindParam(3, $sku, PDO::PARAM_STR);
    $statement->execute();
}

function UpdateItemSO($name,$rate,$sku){
    $statement = Flight::db()->prepare('UPDATE productos SET nombre = ?, rate = ? WHERE sku = ?');
    $statement->bindParam(1, $name, PDO::PARAM_STR);
    $statement->bindParam(2, $rate, PDO::PARAM_STR);
    $statement->bindParam(3, $sku, PDO::PARAM_STR);
    $statement->execute();
}

function getItem($sku) {
    //Verificar que sea de la misma empresa
    $statement = Flight::db()->prepare('SELECT * FROM productos WHERE sku = ?');
    $statement->bindParam(1, $sku, PDO::PARAM_STR);
    $statement->execute();
    return $statement->fetch(PDO::FETCH_ASSOC);
}


// Función para insertar un nuevo producto en la base de datos
function insertItem($item) {
    $statement = Flight::db()->prepare('INSERT INTO productos (sku, nombre, descripcion, unidad, item_id_zoho, purchase_rate) VALUES (?, ?, ?, ?, ?, ?)');
    $statement->execute([$item->getSku(), $item->getName(), $item->getDescription(), $item->getUnit(), $item->getIdItemZoho(), $item->getPurchaseRate()]);
}


function insertOrdenDeCompra ($id_usuario, $fecha, $json_po){ 
    $statement = Flight::db()->prepare('INSERT INTO ordenes_compra (id_usuario , fecha_orden, json_purchase_order) VALUES (? ,? , ? )');
    $statement->bindParam(1, $id_usuario, PDO::PARAM_STR);
    $statement->bindParam(2, $fecha, PDO::PARAM_STR);
    $statement->bindParam(3, $json_po, PDO::PARAM_STR);
    $statement->execute();
}

function clientExists($email, $password){
    //Macthear por email y password. Ver metodo de Pato para encriptar
    $statement = Flight::db()->prepare('SELECT U.empresa , U.vendor_id_zoho , U.customer_id_zoho, U.email , U.id_usuario FROM usuarios U WHERE email = ? AND password = ?');
    $statement->bindParam(1, $email, PDO::PARAM_STR);
    $statement->bindParam(2, $password, PDO::PARAM_STR);
    $statement->execute();
    return $statement->fetch(PDO::FETCH_ASSOC);
}

function verificarToken($token){
   
    $datosTokenDecodificado = decodificarToken($token);
   

    // Verificar si la decodificación fue exitosa
    if ($datosTokenDecodificado !== null) {

        $data = json_encode($datosTokenDecodificado, true);
        $data = json_decode($data, true);
        // Obtener el ID y el correo electrónico directamente desde el objeto
        $email = $data['data']['email'];
        $password = $data['data']['password'];
        
        
        $cliente = clientExists($email, $password);

         if ($cliente){
            return $cliente;
         }else{
            return false;
         }

    } else {
        echo "Error al decodificar el JSON\n";
        return  false;
    }
}

function insertOrdenDeVenta ($id_usuario, $fecha, $json_so){ //CAMBIAR ID_USUARIO POR SUBCONSULTA A LA TABLA USUARIOS
    $statement = Flight::db()->prepare('INSERT INTO ordenes_venta (id_usuario , fecha_orden, json_sales_order) VALUES (? , ? ,?)');
    $statement->bindParam(1, $id_usuario, PDO::PARAM_STR);
    $statement->bindParam(2, $fecha, PDO::PARAM_STR);
    $statement->bindParam(3, $json_so, PDO::PARAM_STR);
    $statement->execute();
}


?>