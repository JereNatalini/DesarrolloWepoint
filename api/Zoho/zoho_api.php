<?php
        function postZohoProductos($jsonItem) {
            // URL de la API de Zoho y el ID de la organización
            $url = 'https://www.zohoapis.com/inventory/v1/items?organization_id=753793595';
        
            // Token de autorización
            $token = 'Zoho-oauthtoken 1000.8ca0a79eecd858f9310080635cc82971.8d51aeede6b671b31f560ff7a285d004';
        
            // Cabeceras de la solicitud
            $headers = [
                'Authorization: ' . $token,
                'Content-Type: application/json',
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