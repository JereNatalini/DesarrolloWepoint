<?php
    require 'flight/Flight.php';
    require 'Item/item_builder.php';
    require 'Item/item.php';
    require 'PurchaseOrder/purchase_order.php';
    require 'PurchaseOrder/po_builder.php';
    
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
            $existing_item = getItem($item_data['sku']);

            if($existing_item){
                echo json_encode('El producto ya existe en la base de datos: ' . $item_data['name']);
                
                $item_builder = new ItemBuilder();
                $item_builder->set('name', $existing_item['name']);
                $item_builder->set('sku', $existing_item['sku']);
                $item_builder->set('description', $existing_item['description']);
                $item_builder->set('unit', $existing_item['unit']);
                $item_builder->set('quantity', $item_data['quantity']);

            }
            else
            {
                $item_builder = new ItemBuilder();

                $item_builder->set('name', $item_data['name']);
                $item_builder->set('sku', $item_data['sku']);
                $item_builder->set('description', $item_data['description']);
                $item_builder->set('unit', $item_data['unit']);

                //Post al zoho con los parametros de arriba
                
                //Responde del zoho, de ahi sacamos el item id
                $jsonItem = $item_builder->toJson();
                $item_id_zoho = postZohoProductos($jsonItem);
                $item_builder->set('item_id_zoho', $item_id_zoho);
                //Seteamos el item id y la cantidad con el builder
                $item_builder->set('quantity', $item_data['quantity']);
                

                insertItem($item_builder->buildItem());
            }

            // Construir el Item y agregarlo al PurchaseOrderBuilder
            $purchase_order_builder->addItem($item_builder->buildItem());

            //Guardar en la DB

        }
        
    
        // Construir la purchaseOrder
        
        $purchase_order = $purchase_order_builder->buildPO();
        $JsonPurchaseorder = $purchase_order->toJson();

        insertOrdenDeCompra($purchase_order['vendor_id'], $JsonPurchaseorder);

//----------------------------------------------------------------------------------------------------------------------------------------------------------------
        

        //Faltaria crear un boton que llame a la funcion de Post zoho y creee una orden de compra 

//cambio 

        //test pa cambiar todo 

        //----------------------------------------------------------------------------------------------------------------------------------------------------------------


        Flight::json(['status' => 'success']);
    });

    function getItem($sku) {
        //Verificar que sea de la misma empresa
        $statement = Flight::db()->prepare('SELECT * FROM Items WHERE sku = ?');
        $statement->bindParam(1, $sku, PDO::PARAM_STR);
        $statement->execute();
        return $statement->fetch(PDO::FETCH_ASSOC);
    }
    
    // Función para insertar un nuevo producto en la base de datos
    function insertItem($item_builder) {
        $statement = Flight::db()->prepare('INSERT INTO Items (sku, item_name, item_desc, unit, item_id_zoho) VALUES (?, ?, ?, ?,?)');
        $statement->bindParam(1, $item_builder['sku'], PDO::PARAM_STR);
        $statement->bindParam(2, $item_builder['name'], PDO::PARAM_STR);
        $statement->bindParam(3, $item_builder['description'], PDO::PARAM_STR);
        $statement->bindParam(4, $item_builder['unit'], PDO::PARAM_STR);
        $statement->bindParam(5, $item_builder['item_id_zoho'], PDO::PARAM_BIGINT);
        $statement->execute();
    }

    function insertOrdenDeCompra ($id_Cliente, $purchase_order){
        $statement = Flight::db()->prepare('INSERT INTO Orden_de_compra (Id_cliente, orden_de_compra) VALUES (?, ?)');
        $statement->bindParam(1, $id_Cliente, PDO::PARAM_STR);
        $statement->bindParam(2, $purchase_order, PDO::PARAM_STR);
        $statement->execute();
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
        Flight::start();
    });

    //ADAPTAR METODO A DB
    function insertSaleOrder($saleOrder){
        $statement = Flight::db()->prepare('INSERT INTO Items (sku, item_name, item_desc, unit) VALUES (?, ?, ?, ?)');
        $statement->bindParam(1, $item_data['sku'], PDO::PARAM_STR);
        $statement->bindParam(2, $item_data['name'], PDO::PARAM_STR);
        $statement->bindParam(3, $item_data['description'], PDO::PARAM_STR);
        $statement->bindParam(4, $item_data['unit'], PDO::PARAM_STR);
        $statement->execute();
    }
    
    Flight::start();

?>

