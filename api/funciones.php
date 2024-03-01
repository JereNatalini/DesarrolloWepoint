<?php


function createProductArray($name, $sku, $purchase_rate) {
    return [
        "name" => $name,
        "sku" => $sku,
        "item_type" => "inventory",
        "purchase_rate" => $purchase_rate
    ];
}

/*
function createProductArray($name, $sku) {
    $json_post_item = [
        "name" => $name,
        "sku" => $sku,
    
    ];

    // Elimina si los campos estan vacios

    return json_encode($json_post_item);
}
*/

?>