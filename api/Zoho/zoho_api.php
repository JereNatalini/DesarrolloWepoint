<?php
    class ZohoApi {
        function postZoho($PO_post_data){
            $organization_id = '753793595';
            $url = "https://www.zohoapis.com/inventory/v1/items?organization_id={$organization_id}";
        
            $headers = array(
                "Authorization: Zoho-oauthtoken ". devolverTokenZoho(),
                'Content-Type: application/json'
            );
        
            $data = $PO_post_data; //Purchase Order, esto seria como el body parameter
        
            $ch = curl_init($url);
        
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
            $response = curl_exec($ch);
        
            if (curl_errno($ch)) {
                echo 'Error en la consulta cURL: ' . curl_error($ch);
            }
        
            curl_close($ch);
        
            return $response;
        }
    }

?>