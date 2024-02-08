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
    
    Flight::route('POST desarrollowepoint/api/po', function() {
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
                print_r('El producto ya existe en la base de datos: ' . $item_data['name']);
                
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
                $item_builder->set('item_id_zoho', $response['item_id']);
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
    
    Flight::start();

?>

