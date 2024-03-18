<?php
//Probando servidor
    require 'flight/Flight.php';
    require 'Item/item.php';
    require 'PurchaseOrder/purchase_order.php';
    require 'PurchaseOrder/po_builder.php';
    require 'Zoho/zoho_api.php';
    require 'Token/Token.php';
    require 'SaleOrder/sale_order.php';
    require 'funciones.php';

    global $token;
    $token = '';
    
    function returnTokenZoho(){
        global $token;
        return $token;
    }

    Flight::register('db', 'PDO', array('mysql:host=localhost;dbname=wepoint_api', 'wepoint', 'W1DjSYZJ0BLP'));
    
    Flight::route('POST /po', function() {
        $request = Flight::request();
        $po_data = json_decode($request->getBody(), true);
        
        $headers = getallheaders();

        if (!isset($headers['Authorization'])) {
            http_response_code(401);
            echo json_encode(array("mensaje" => "Token no proporcionado"));
            exit;
        }

        $authorizationHeader = $headers['Authorization'];
        $tokenCliente = str_replace("Bearer ", "", $authorizationHeader);
        

        $datosCliente = verificarToken($tokenCliente);


        if ($datosCliente){

            if (!isset($po_data['line_items'])) {
                echo json_encode(['error' => 'No se encontraron items en el pedido']);
                return;
            }
            $id_usuario = $datosCliente['id_usuario'];
            $vendor_id = $datosCliente['vendor_id_zoho'];
            
            $purchase_order_builder = new PoBuilder();
            
            // Agregar campos al PurchaseOrderBuilder
            $purchase_order_builder->set('purchaseorder_number', $po_data['purchaseorder_number']);
            $purchase_order_builder->set('date', $po_data['date']);
            $purchase_order_builder->set('vendor_id', $vendor_id);
            $purchase_order_builder->set('is_drop_shipment', $po_data['is_drop_shipment']);
            $purchase_order_builder->set('contact_persons', $po_data['contact_persons']);
            $purchase_order_builder->set('notes', $po_data['notes']);
            $purchase_order_builder->set('reference_number', $po_data['reference_number']);
            
            
            $processedSkus = [];

            foreach ($po_data['line_items'] as $item_data) {
                $sku = $item_data['sku'];

                // Chequea si el SKU ya fue leido 
                if (isset($processedSkus[$sku])) {
                    
                    Flight::halt(403, json_encode(['ERROR' => 'Se cargo el mismo Producto mas de 1 vez ' . $sku]));
                }

                // Mark SKU as processed
                $processedSkus[$sku] = true;
                
                //Verificar si el item existe en la base de datos
                $existing_item_data = getItem($item_data['sku']);
    
                if ($existing_item_data) {
                    // Crear un nuevo objeto Item y luego usar los métodos setters
                    $existing_item = new Item($existing_item_data['nombre']);
                    $existing_item
                        ->setSku($existing_item_data['sku'])
                        ->setDescription($existing_item_data['descripcion'])
                        ->setUnit($existing_item_data['unidad'])
                        ->setIdItemZoho($existing_item_data['item_id_zoho'])
                        ->setQuantity($item_data['quantity'])
                        ->setPurchaseRate($item_data['purchase_rate']);
                        
                    //Comparar el precio que entra con el que esta en la DB y actualizarlo si es distinto
                    //Aca trae el item y accede a la columna purchase_rate... 
                    if ($existing_item_data['purchase_rate'] !== $item_data['purchase_rate'] || $existing_item_data['nombre'] !== $item_data['name']) {
                        

                        $name = $item_data['name'];
                        $sku = $item_data['sku'];
                        $purchase_rate = $item_data['purchase_rate'];

                        updateItemDB($name, $purchase_rate, $sku);
                        $jsonItem = createProductArray($name, $sku, $purchase_rate);
                        $response = updateItemZoho($existing_item_data['item_id_zoho'], json_encode($jsonItem));
                        
                    }
                    


                    
                    // Agregarlo al PurchaseOrderBuilder
                    $purchase_order_builder->addItem($existing_item);
                }
                else
                {
                    $name = $item_data['name'];
                    $sku = $item_data['sku'];
                    $purchase_rate = $item_data['purchase_rate'];

                    //Si no existe crear el item para insertarlo a zoho
                    $array_post_item_zoho = CreateProductArray($name, $sku, $purchase_rate);

                    //Post al zoho con los parametros de arriba
                    //Response del zoho, de ahi sacamos el item id
                    $response = postZohoProductos(json_encode($array_post_item_zoho));
                    $item_id_zoho = json_decode($response, true);

                    
                
                    if ($item_id_zoho && isset($item_id_zoho['item']['item_id'])) {
                        // Acceder al atributo 'item_id'
                        $itemId = $item_id_zoho['item']['item_id'];
                    
                    }
                    else{
                        Flight::halt(403, 'Error al tomar los datos del producto (No se pudo cargan en el sistema)');
                        
                    }
    
                    $item_posteado = new Item($item_data['name']);
                    $item_posteado
                        ->setSku($item_data['sku'])
                        ->setName($item_data['name'])
                        ->setDescription($item_data['description'])
                        ->setUnit($item_data['unit'])
                        ->setIdItemZoho($itemId)
                        ->setQuantity($item_data['quantity'])
                        ->setPurchaseRate($item_data['purchase_rate']);
                    
                    
                    // Construir el Item, guardarlo en la DB y agregarlo al PurchaseOrderBuilder
                    insertItem($item_posteado);
                    $purchase_order_builder->addItem($item_posteado);
                }
    
            }
            
            // Construir la purchaseOrder
            
            $purchase_order = $purchase_order_builder->buildPO();
            $JsonPurchaseorder = $purchase_order->toJson();
    
            insertOrdenDeCompra($purchase_order->getPurchaseorderNumber(),$id_usuario,$purchase_order->getDate()  ,$JsonPurchaseorder);
    
    
            Flight::json(['status' => 'success']);

        }else{

            Flight::halt(403, 'No tienes autorizacion o el usuario no existe, verificar los datos');

        }


       
    });
    
    function updateItemDB($name,$purchase_rate,$sku){
        $statement = Flight::db()->prepare('UPDATE Productos SET nombre = ?, purchase_rate = ? WHERE sku = ?');
        $statement->bindParam(1, $name, PDO::PARAM_STR);
        $statement->bindParam(2, $purchase_rate, PDO::PARAM_STR);
        $statement->bindParam(3, $sku, PDO::PARAM_STR);
        $statement->execute();
    }
   
    function getItem($sku) {
        //Verificar que sea de la misma empresa
        $statement = Flight::db()->prepare('SELECT * FROM Productos WHERE sku = ?');
        $statement->bindParam(1, $sku, PDO::PARAM_STR);
        $statement->execute();
        return $statement->fetch(PDO::FETCH_ASSOC);
    }
    

    // Función para insertar un nuevo producto en la base de datos
    function insertItem($item) {
        $statement = Flight::db()->prepare('INSERT INTO Productos (sku, nombre, descripcion, unidad, item_id_zoho, purchase_rate) VALUES (?, ?, ?, ?, ?, ?)');
        $statement->execute([$item->getSku(), $item->getName(), $item->getDescription(), $item->getUnit(), $item->getIdItemZoho(), $item->getPurchaseRate()]);
    }
    

    function insertOrdenDeCompra ($order_id ,$id_usuario, $fecha, $json_po){ //CAMBIAR ID_USUARIO POR SUBCONSULTA A LA TABLA USUARIOS........ NO PASAR ID_ORDEN PQ ES IDENDITY
        $statement = Flight::db()->prepare('INSERT INTO Ordenes_compra (id_orden ,id_usuario , fecha_orden, json_purchase_order) VALUES (? ,? , ? ,?)');
        $statement->bindParam(1, $order_id, PDO::PARAM_STR);
        $statement->bindParam(2, $id_usuario, PDO::PARAM_STR);
        $statement->bindParam(3, $fecha, PDO::PARAM_STR);
        $statement->bindParam(4, $json_po, PDO::PARAM_STR);
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
        $statement = Flight::db()->prepare('INSERT INTO Ordenes_venta (id_usuario , fecha_orden, json_sales_order) VALUES (? , ? ,?)');
        $statement->bindParam(1, $id_usuario, PDO::PARAM_STR);
        $statement->bindParam(2, $fecha, PDO::PARAM_STR);
        $statement->bindParam(3, $json_so, PDO::PARAM_STR);
        $statement->execute();
    }


    Flight::route('POST /so', function(){
        $body_sale_order = Flight::request(); //validar atributos del body
        $sale_order_data = json_decode($body_sale_order->getBody(), true);

        $headers = getallheaders();

        if (!isset($headers['Authorization'])) {
            http_response_code(401);
            echo json_encode(array("mensaje" => "Token no proporcionado"));
            exit;
        }

        $authorizationHeader = $headers['Authorization'];
        $tokenCliente = str_replace("Bearer ", "", $authorizationHeader);
        


        $datosCliente = verificarToken($tokenCliente);

        if ($datosCliente){
            if (!isset($sale_order_data['line_items'])) {
                Flight::halt(403, json_encode(['error' => 'No se encontraron items en la Factura']));
                return;
            }
    
            $lineItemsSO = array();
            $customer_id = $datosCliente['customer_id_zoho'];
            $id_usuario = $datosCliente['id_usuario'];

            $sale_order = new SalesOrder();
            //seteamos los atributos que no vienen default en el ctor
            $sale_order->setCustomerId($customer_id);
            $sale_order->setContactPersons($sale_order_data['contact_persons']);
            $sale_order->setReferenceNumber($sale_order_data['nro_orden_venta']); 
            //El reference number va ser el numero de factura del cliente
    
    
            
            $processedSkus = [];

            foreach ($sale_order_data['line_items'] as $item_data) {
                $sku = $item_data['sku'];

                // Chequea si el SKU ya fue leido 
                if (isset($processedSkus[$sku])) {
                    
                    Flight::halt(403, json_encode([' ERROR ' => ' Se cargo el mismo Producto mas de 1 vez = ' . $sku]));
                }

                // Mark SKU as processed
                $processedSkus[$sku] = true;
                
                //Verificar si el item existe en la base de datos
                $existing_item = getItem($item_data['sku']);
    
                if(!$existing_item) {
                    //primero deberia evaluar la posibilidad de que este cargado en el zoho y no en la db
                     Flight::halt(403, json_encode(['error' => 'El producto ' . $item_data['name'] . ' nunca se recibio en una Orden de Compra']));
                }
                else{
    
                    //Instanciar Item
                    $itemSaleOrder = new Item($existing_item['nombre']);
                    $itemSaleOrder->setIdItemZoho($existing_item['item_id_zoho']);
                    $itemSaleOrder->setDescription($existing_item['descripcion']);
                    $itemSaleOrder->setUnit($existing_item['unidad']);
                    $itemSaleOrder->setQuantity($item_data['quantity']);
    
                    $lineItemsSO[] = $itemSaleOrder;
                }
                
            }
            $sale_order->setLineItems($lineItemsSO);
            $sale_order_json = $sale_order->toJson();
    
            insertOrdenDeVenta($id_usuario, $sale_order_data['date'], $sale_order_json);

            Flight::json(['status' => 'success']);

        }else{

            Flight::halt(403, 'No tienes autorizacion o el usuario no existe, verificar los datos');

        }




       
    });

    Flight::route('POST /OAuth', function() {
        $request = Flight::request();
        $post_data = $request->data;
        
        $email = $post_data['email'];
        $password = $post_data['password'];

        $cliente = clientExists($email , $password);
        
        If ($cliente){
            $token = generarTokenCliente($email , $password);
         }else{
            Flight::halt(403, 'El usuario no existe');
         }
        Flight::json(['status' => 'success','token' => $token]);
    });


    Flight::route('GET /datosPo', function() {
        $db = Flight::db();
        $request = Flight::request();
        $headers = getallheaders();

        Flight::halt(200, print_r($headers['Authorization'], true));
        
        if (!isset($headers['Authorization'])) {
            http_response_code(401);
            echo json_encode(array("mensaje" => "Token no proporcionado"));
            exit;
        }

        $authorizationHeader = $headers['Authorization'];
        $token = str_replace("Bearer ", "", $authorizationHeader);

        if (verificarToken($token)){
            //Ejecutar una consula SQL
            $statement = $db->query('SELECT OC.id_Orden, OC.id_usuario, OC.fecha_Orden, OC.json_Purchase_Order, U.empresa , U.email FROM Ordenes_compra OC JOIN usuarios U ON OC.id_usuario = U.id_usuario');
            //Verificar si es cliente basicamente

            //Obtener los resultados de la tabla
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);

            //devolver los resultados como Json
            Flight::json($result);

        }else{

            Flight::halt(403, 'No tienes autorizacion o el usuario no existe, verificar los datos');

        }

        // El formato para pasarle el token es 'Bearer 'Token''

    });

    
    Flight::route('GET /datosSo', function() {
        $db = Flight::db();
        $request = Flight::request();
        $headers = getallheaders();



        if (!isset($headers['Authorization'])) {
            http_response_code(401);
            echo json_encode(array("mensaje" => "Token no proporcionado"));
            exit;
        }

        $authorizationHeader = $headers['Authorization'];
        $token = str_replace("Bearer ", "", $authorizationHeader);

        if (verificarToken($token)){

            //Ejecutar una consula SQL
            $statement = $db->query('SELECT OV.id_orden, OV.id_usuario, OV.fecha_orden, OV.json_sales_order, U.empresa , U.email FROM ordenes_venta OV JOIN usuarios U ON OV.id_usuario = U.id_usuario');

            //Obtener los resultados de la tabla
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);

            //devolver los resultados como Json
            Flight::json($result);

        }else{

            Flight::halt(403, 'No tienes autorizacion o el usuario no existe, verificar los datos');

        }



    });

    Flight::start();


?>

