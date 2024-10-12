<?php

namespace App\Controllers;

use App\Utils\Validator;
use Exception;

class EventController {

    public function analyzeEvent($url){
        http_response_code(200);
        if (!Validator::isValidUrl($url)) {
            http_response_code(500);
            return [
                'error'=>'La URL proporcionada no es válida.',
            ];
        }

        $preTikets = null;
        $platform = $this->determinePlatform($url);
        $tikets = [];
                
        switch ($platform) {
            case 'VividSeats':
                $preTikets = $this->getPreTiketsVividSeats($url);
                $tikets = $this->createArrayTiketsVividSeats($preTikets);
                break;
            case 'SeatGeek':
                return [
                    'msg'=>'Proximamente...',
                ];
            default:
                http_response_code(500);
                return [
                    'error'=>'Plataforma no soportada.',
                ];
                break;
        }

        
        if ($preTikets === null) {
            http_response_code(500);
            return [
                'error'=>'No se pudieron obtener los datos.',
            ];
        } 

        
        if ($preTikets === 0) {
            http_response_code(500);
            return [
                'error'=>'No se encontró un productionId en la URL.',
            ];
        } 

        
        return [
            'platform'=> $platform,
            'tikets' => $tikets
        ];

    }// analyzeEvent()

    private function createArrayTiketsVividSeats($tickets){
        $groupsMap = [];
        foreach ($tickets['groups'] as $group) {
            $groupsMap[$group['i']] = [
                'name' => $group['n'],
                'price_low' => floatval($group['l']),
                'price_high' => floatval($group['h'])
            ];
        }
        
        // Inicializar el arreglo de categorías
        $categories = [];
        $categoryId = 1;
        
        // Iterar sobre los tickets para acumular la cantidad de tickets por categoría
        foreach ($tickets['tickets'] as $ticket) {
            
            $categoryName = $ticket['s'];
            $ticketQuantity = intval($ticket['q']);
            $ticketPrice = floatval($ticket['p']);
            array_push($categories, [
                'id' => $categoryId++,
                'category' => $categoryName,
                'tickets_available' => $ticketQuantity,
                'price' => $ticketPrice
            ]);
        }
        
        // Reindexar el arreglo para eliminar las claves de grupo
        $finalCategories = array_values($categories);
        
        return $finalCategories;
    }//createArrayTiketsVividSeats()

    private function getPreTiketsVividSeats($url){
        $pattern = '/production\/(\d+)/';
        $productionId = 0;

        if (preg_match($pattern, $url, $matches)) {
            $productionId = $matches[1];
        }

        if($productionId === 0){
            return 0;
        }

        $vididUrl = 'http://www.vividseats.com/hermes/api/v1/listings?productionId='.$productionId.'&includeIpAddress=true&currency=USD';
        
        $headers = array(
            'Cookie: _pxhd=fVgPbGAIK64MB-vZr/eu/QpImk9ZvE-z7Ad8oDiktD3GTUlwsKTmhFFpDwpbtvdeJLvDaQhzpV2gzFfOtXo1-A==:OabARc-hEHMMEm8zLN/3DsWfV2UYZgyG4w70o9kJI-26Boc6QXRD64x7SYPemk9dgy2S8gJ55sKF4PX1dyrCOn5W5kzgBCK5Koa62nX8jz0=; Expires=Sun, 12 Oct 2025 05:03:51 GMT; Path=/',
            'Accept: application/json',
            'Referer: https://www.vividseats.com/',
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/129.0.0.0 Safari/537.36',        
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $vididUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        
        $response = curl_exec($ch);
        
        if (curl_errno($ch)) {
            curl_close($ch);
            return null; 
        } 
        
        $preTikets = json_decode($response, true);

        curl_close($ch);

        return $preTikets;
    }// getPreTiketsVividSeats()

    private function determinePlatform(string $url){
        $parsedUrl = parse_url($url);
        if (!isset($parsedUrl['host'])) {
            throw new Exception('No se pudo determinar la plataforma a partir de la URL.');
        }

        $host = $parsedUrl['host'];

        if (strpos($host, 'vividseats.com') !== false) {
            return 'VividSeats';
        } elseif (strpos($host, 'seatgeek.com') !== false) {
            return 'SeatGeek';
        } else {
            return 'Unknown';
        }
    }// determinePlatform()

}// EventController