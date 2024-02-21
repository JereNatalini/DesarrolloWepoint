<?php
    require 'flight/Flight.php';
    require 'Item/item_builder.php';
    require 'Item/item.php';
    require 'PurchaseOrder/purchase_order.php';
    require 'PurchaseOrder/po_builder.php';
    require 'Zoho/zoho_api.php';
    require 'TokenTest/testToken.php';
    require 'funciones.php';

    global $token;
    $token = '';
    
    function returnTokenZoho(){
        global $token;
        return $token;
    }

    Flight::register('db', 'PDO', array('mysql:host=localhost;dbname=wepoint_api', 'root', ''));
    
    Flight::route('POST /po', function() {
        $request = Flight::request();
        $po_data = json_decode($request->getBody(), true);

        $tokenCliente = $po_data['token'];

        if (verificarToken($tokenCliente)){

            if (!isset($po_data['line_items'])) {
                echo json_encode(['error' => 'No se encontraron items en el pedido']);
                return;
            }
            
            $purchase_order_builder = new PoBuilder();
        
            // Agregar campos al PurchaseOrderBuilder
            $purchase_order_builder->set('purchaseorder_number', $po_data['purchaseorder_number']);
            $purchase_order_builder->set('date', $po_data['date']);
            $purchase_order_builder->set('vendor_id', $po_data['vendor_id']);
            $purchase_order_builder->set('is_drop_shipment', $po_data['is_drop_shipment']);
            $purchase_order_builder->set('contact_persons', $po_data['contact_persons']);
            $purchase_order_builder->set('notes', $po_data['notes']);
            $purchase_order_builder->set('reference_number', $po_data['reference_number']);
            
            foreach ($po_data['line_items'] as $item_data) {
                
                //Verificar si el item existe en la base de datos
                $existing_item_data = getItem($item_data['sku']);
    
                if ($existing_item_data) {
                    // Crear un nuevo objeto Item y luego usar los métodos setters
                    $existing_item = new Item($existing_item_data['item_name'], $existing_item_data['sku']);
                    $existing_item
                        ->setDescription($existing_item_data['item_desc'])
                        ->setUnit($existing_item_data['unit'])
                        ->setIdItemZoho($existing_item_data['zoho_item_id'])
                        ->setQuantity($item_data['quantity']);
                    
                    // Agregarlo al PurchaseOrderBuilder
                    $purchase_order_builder->addItem($existing_item);
                }
                else
                {
                    $name = $item_data['name'];
                    $sku = $item_data['sku'];
                    //Si no existe crear el item para insertarlo a zoho
                    $array_post_item_zoho = CreateProductArray($item_data['name'], $item_data['sku']);
                    
    
                    //Testeo dpara ver el json generado 
                    //insertTest("Test", $array_post_item_zoho);
    
                 
                
    
                    $array_post_item_zoho = CreateProductArray($name, $sku);
                    //Post al zoho con los parametros de arriba
                   
                    //Response del zoho, de ahi sacamos el item id
                    $response = postZohoProductos(json_encode($array_post_item_zoho));
                    $item_id_zoho = json_decode($response, true);
                   
                    $itemId = $item_id_zoho['item']['item_id'];
                   
                    if ($item_id_zoho && isset($item_id_zoho['item']['item_id'])) {
                        // Acceder al atributo 'item_id'
                        $itemId = $item_id_zoho['item']['item_id'];
                    
                    }
                    else{
                        $itemId = "No posteo nada";
                        
                    }
    
                    $item_posteado = new Item($item_data['name'], $item_data['sku']);
                    $item_posteado
                        ->setName($item_data['name'])
                        ->setDescription($item_data['description'])
                        ->setUnit($item_data['unit'])
                        ->setIdItemZoho($itemId)
                        ->setQuantity($item_data['quantity']);
                    
                    
                    // Construir el Item, guardarlo en la DB y agregarlo al PurchaseOrderBuilder
                    insertItem($item_posteado);
                    $purchase_order_builder->addItem($item_posteado);
                }
    
            }
            
        
            // Construir la purchaseOrder
            
            $purchase_order = $purchase_order_builder->buildPO();
            $JsonPurchaseorder = $purchase_order->toJson();
    
            insertOrdenDeCompra($purchase_order->getVendorId(), $JsonPurchaseorder);
    
    
            Flight::json(['status' => 'success']);

        }else{

            Flight::halt(403, 'No tienes autorizacion o el usuario no existe, verificar los datos');

        }


       
    });

   
    function getItem($sku) {
        //Verificar que sea de la misma empresa
        $statement = Flight::db()->prepare('SELECT * FROM Items WHERE sku = ?');
        $statement->bindParam(1, $sku, PDO::PARAM_STR);
        $statement->execute();
        return $statement->fetch(PDO::FETCH_ASSOC);
    }
    
    Function getPurchaseOrder($Client_id) {
        //Verificar que sea de la misma empresa
        $statement = Flight::db()->prepare('SELECT * FROM Purchase_orders WHERE client_id = ?');
        $statement->bindParam(1, $Client_id, PDO::PARAM_STR);
        $statement->execute();
        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    // Función para insertar un nuevo producto en la base de datos
    function insertItem($item_builder) {
        $statement = Flight::db()->prepare('INSERT INTO Items (sku, item_name, item_desc, unit, zoho_item_id) VALUES (?, ?, ?, ?, ?)');
        $statement->execute([$item_builder->getSku(), $item_builder->getName(), $item_builder->getDescription(), $item_builder->getUnit(), $item_builder->getIdItemZoho()]);
    }
    

    function insertOrdenDeCompra ($vendor_id, $json_po){
        $statement = Flight::db()->prepare('INSERT INTO Purchase_orders (client_id, purchase_order) VALUES (?, ?)');
        $statement->bindParam(1, $vendor_id, PDO::PARAM_STR);
        $statement->bindParam(2, $json_po, PDO::PARAM_STR);
        $statement->execute();
    }

    function clienExists($id_cliente, $email){
        $statement = Flight::db()->prepare('SELECT * FROM clients WHERE client_id = ? AND email = ?');
        $statement->bindParam(1, $id_cliente, PDO::PARAM_STR);
        $statement->bindParam(2, $email, PDO::PARAM_STR);
        $statement->execute();
        $rows = $statement->fetchColumn();
        return $rows;
    }

    function verificarToken($token){
       
        $datosTokenDecodificado = decodificarToken($token);
       

        // Verificar si la decodificación fue exitosa
        if ($datosTokenDecodificado !== null) {

            $data = json_encode($datosTokenDecodificado, true);
            $data = json_decode($data, true);
            // Obtener el ID y el correo electrónico directamente desde el objeto
            $id = $data['data']['id'];
            $email = $data['data']['email'];
            
             if (clienExists($id, $email)){
                return true;
             }else{
                return false;
             }

        } else {
            echo "Error al decodificar el JSON\n";
            return  false;
        }
    }


    Flight::route('POST /so', function(){
        $body_sale_order = Flight::request(); //validar atributos del body
        $sale_order_data = json_decode($$body_sale_order->getBody(), true);

        if (!isset($sale_order_data['line_items'])) {
            echo json_encode(['error' => 'No se encontraron items en la Factura']);
            return;
        }

        $sale_order = new SoBuilder();
        $sale_order->set('customer_id', $sale_order_data['customer_id']);
        $sale_order->set('date', $sale_order_data['date']);
        $sale_order->set('line_items', $sale_order_data['line_items']);

        foreach ($sale_order_data['line_items'] as $item_data) {
            
            //Verificar si el item existe en la base de datos
            $existing_item = getItem($item_data['sku']);

            if(!$existing_item) {
                //primero deberia evaluar la posibilidad de que este cargado en el zoho y no en la db
                error_log('El producto ' . $item_data['name'] . 'nunca se recibio en una Orden de Compra');
            }
            else{

                //BUILDEAR ITEM
                $item_builder = new ItemBuilder();
                $item_builder->set('name', $existing_item['name']);
                $item_builder->set('sku', $existing_item['sku']);
                $item_builder->set('description', $existing_item['description']);
                $item_builder->set('unit', $existing_item['unit']);
                $item_builder->set('quantity', $sale_order_data['quantity']);
                $item_builder->set('item_id_zoho', $existing_item['item_id_zoho']);



                $sale_order->addItem($item_builder->buildItem());
            }
            
        }

    });

    //ADAPTAR METODO A DB
    /*
    function insertSaleOrder($saleOrder){
        $statement = Flight::db()->prepare('INSERT INTO Items (sku, item_name, item_desc, unit) VALUES (?, ?, ?, ?)');
        $statement->bindParam(1, $item_data['sku'], PDO::PARAM_STR);
        $statement->bindParam(2, $item_data['name'], PDO::PARAM_STR);
        $statement->bindParam(3, $item_data['description'], PDO::PARAM_STR);
        $statement->bindParam(4, $item_data['unit'], PDO::PARAM_STR);
        $statement->execute();
    }
    */

    Flight::route('POST /token', function() {
        $request = Flight::request();
        $post_data = $request->data;
        $id = $post_data['id'];
        $email = $post_data['email'];
        $token = generarTokenCliente($id, $email);
        $tokenDecode = decodificarToken($token);
        Flight::json(['status' => 'success','token' => $token ,'token_Email' => $tokenDecode ]);
    });

    


    Flight::route('GET /datosPo', function() {
        $db = Flight::db();

        //Ejecutar una consula SQL
        $statement = $db->query('SELECT * FROM ordenes_compra');

        //Obtener los resultados de la tabla
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);

        //devolver los resultados como Json
        Flight::json($result);

    });

    
    Flight::route('GET /datosSo', function() {
        $db = Flight::db();

        //Ejecutar una consula SQL
        $statement = $db->query('SELECT * FROM ordenes_venta');

        //Obtener los resultados de la tabla
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);

        //devolver los resultados como Json
        Flight::json($result);

    });

    Flight::start();


?>

