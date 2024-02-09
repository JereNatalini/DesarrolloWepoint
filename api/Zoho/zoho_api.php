<?php

    
        function postZohoProductos($JsonProducto){
            $organization_id = '753793595';
            $url = "https://www.zohoapis.com/inventory/v1/items?organization_id={$organization_id}";
            $ch = curl_init($url);

            $headers = array(
                "Authorization: Zoho-oauthtoken 1000.83f01a66569d84ab67f7a900e397c2bc.db899f8a410585f548182cab62b3f3a2" /*.returnTokenZoho()*/,
                'Content-Type: application/json'
            );

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $JsonProducto);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            $response = curl_exec($ch);

            if (curl_errno($ch)) {
                echo 'Error en la consulta cURL: ' . curl_error($ch);
            }

            curl_close($ch);

            $response_object = json_decode($response, true);

            return $response_object;
        }


?>