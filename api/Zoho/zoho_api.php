<?php
        function postZohoProductos($jsonItem) {
            // URL de la API de Zoho y el ID de la organización
            $url = 'https://www.zohoapis.com/inventory/v1/items?organization_id=753793595';
        
            // Token de autorización
            $token = 'Zoho-oauthtoken 1000.980193589c6f94ea71cb7172184a8e01.3bf9e2286d3a03399c712e9d89449805';
        
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
        
        


?>