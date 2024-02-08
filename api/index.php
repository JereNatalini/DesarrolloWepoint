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

    Flight::register('db', 'PDO', array('mysql:host=localhost;dbname=zohoapi', 'root', ''));
    
    Flight::route(' /po', function() {
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

            if(!$existing_item) {
                //buildear para post en zoho
            /* 
                $item_builder = new ItemBuilder();
        
                $item_builder->set('name', $item_data['name']);
                $item_builder->set('sku', $item_data['sku']);
                $item_builder->set('description', $item_data['description']);
                $item_builder->set('unit', $item_data['unit']);

                item_builder encodeado
                response = funcion para postear en el zoho($item_builder encodeado)
                $item_builder->set('item_id_zoho', respones['item_id'])
                
            
            
            */
                insertItem($item_data);
            }
            else{
                error_log('El producto ya existe en la base de datos: ' . $item_data['name']);
            }
        
            $item_builder = new ItemBuilder();
        
            $item_builder->set('name', $item_data['name']);
            $item_builder->set('sku', $item_data['sku']);
            $item_builder->set('description', $item_data['description']);
            $item_builder->set('unit', $item_data['unit']);
            // Construir el Item y agregarlo al PurchaseOrderBuilder
            $purchase_order_builder->addItem($item_builder->buildItem());
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
    function insertItem($item_data) {
        $statement = Flight::db()->prepare('INSERT INTO Items (sku, item_name, item_desc, unit) VALUES (?, ?, ?, ?)');
        $statement->bindParam(1, $item_data['sku'], PDO::PARAM_STR);
        $statement->bindParam(2, $item_data['name'], PDO::PARAM_STR);
        $statement->bindParam(3, $item_data['description'], PDO::PARAM_STR);
        $statement->bindParam(4, $item_data['unit'], PDO::PARAM_STR);
        $statement->execute();
    }
    
    Flight::start();

?>

