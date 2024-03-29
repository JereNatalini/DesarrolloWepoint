<?php
        function postZohoProductos($jsonItem) {
            // URL de la API de Zoho y el ID de la organización
            $url = 'https://www.zohoapis.com/inventory/v1/items?organization_id=753793595';
        
            // Token de autorización
            $token = 'Zoho-oauthtoken 1000.d0464f4f96caf4b2b88d004bc3726a88.fcc8b854053a0a73c13a9705a9bb4871';
        
            // Cabeceras de la solicitud
            $headers = [
                'Authorization: ' . $token,
                'Content-Type:application/json',
            ];
        
            // Configuración de cURL
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $jsonItem);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        
            // Ejecutar la solicitud cURL
            $response = curl_exec($curl);
        
            // Manejar errores
            if (curl_errno($curl)) {
                echo 'Error en la solicitud cURL: ' . curl_error($curl);
            }
        
            // Cerrar la sesión cURL
            curl_close($curl);
        
            // Decodificar la respuesta JSON si es necesario
            
        
            return $response;
        }
        
        function updateItemZoho($itemId, $jsonData) {
            $url = "https://www.zohoapis.com/inventory/v1/items/{$itemId}?organization_id=753793595";

            $token = 'Zoho-oauthtoken 1000.d0464f4f96caf4b2b88d004bc3726a88.fcc8b854053a0a73c13a9705a9bb4871';
        
            $headers = [
                'Authorization: ' . $token,
                'Content-Type:application/json',
            ];
        
            $ch = curl_init($url);
            
            // Configurar opciones de cURL
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
            // Ejecutar la solicitud cURL
            $response = curl_exec($ch);
        
            // Manejar errores si es necesario
            if (curl_errno($ch)) {
                echo 'Error en la solicitud cURL: ' . curl_error($ch);
            }
        
            // Cerrar la sesión cURL
            curl_close($ch);
        
            // Imprimir la respuesta
            return $response;
        }

?>
