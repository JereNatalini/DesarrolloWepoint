<!-- Funciones para pegarle a la api zoho, como la de post de productos y demas -->

<?php
    

    $token_zoho = fetchTokenFromDataBase(); 


        function postZohoProductos($jsonItem) {

            global $token_zoho;
            // URL de la API de Zoho y el ID de la organización
            $url = 'https://www.zohoapis.com/inventory/v1/items?organization_id=753793595';
        
            // Token de autorización

            $token = $token_zoho;

        
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
            global $token_zoho;

            $url = "https://www.zohoapis.com/inventory/v1/items/{$itemId}?organization_id=753793595";

            $token = $token_zoho;

        
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

        function postPoZoho($jsonData) {
            global $token_zoho;
            $url = 'https://www.zohoapis.com/inventory/v1/purchaseorders?organization_id=753793595';

            // Token de autorización
            $accessToken = $token_zoho;


            // Encabezados de la solicitud
            $headers = array(
                'Authorization: ' . $accessToken,
                'Content-Type: application/json'
            );

            // Configurar las opciones de cURL
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $jsonData);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

            // Ejecutar la solicitud cURL y obtener la respuesta
            $response = curl_exec($curl);

            // Verificar si hubo algún error
            if (curl_errno($curl)) {
                echo 'Error al hacer la solicitud cURL: ' . curl_error($curl);
            }

            // Cerrar la conexión cURL
            curl_close($curl);

            // Imprimir la respuesta
            return $response;
        }




        function fetchTokenFromDataBase() {
            // Define the endpoint URL
            $endpointUrl = "https://www.wepoint.ar/Testing/App/api/token";
        
            // Initialize cURL session
            $curl = curl_init();
        
            // Set cURL options
            curl_setopt($curl, CURLOPT_URL, $endpointUrl);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        
            // Execute the cURL request
            $response = curl_exec($curl);
        
            // Check for cURL errors
            if ($response === false) {
                echo "cURL Error: " . curl_error($curl);
                return;
            }
        
            // Close cURL session
            curl_close($curl);
        
            // Parse the JSON response
            $data = json_decode($response, true);
        
            // Check if JSON decoding was successful
            if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
                echo "Error parsing JSON response";
                return;
            }
        
            // Access the token from the response
            $token = ' Zoho-oauthtoken ' . $data[0]['Token'];
        
            // Return the token
            return $token;
        }
?>
