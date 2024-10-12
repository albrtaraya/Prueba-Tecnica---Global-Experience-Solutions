<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Controllers\EventController;


if (!isset($_GET['url'])) {
    http_response_code(500);
    $result = [
        'msg'=>"Parámetro 'url' es requerido.",
    ];
}else{
    $url = $_GET['url'];
    
    $controller = new EventController();
    
    $result = $controller->analyzeEvent($url);
}

header('Content-Type: application/json');
echo json_encode($result, JSON_PRETTY_PRINT);
