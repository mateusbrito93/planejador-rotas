<?php
// api.php - O Backend Seguro
header('Content-Type: application/json');

// --- SUA CHAVE FICA SEGURA AQUI (LADO DO SERVIDOR) ---
$TOMTOM_KEY = 'SUA API TOMTOM'; 

// Recebe qual ação o frontend quer (busca, rota ou chave publica)
$action = $_GET['action'] ?? '';

// Função auxiliar para fazer requisição cURL
function requestTomTom($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    // Retorna o status HTTP correto para o frontend
    http_response_code($httpCode);
    return $response;
}

switch ($action) {
    case 'get_map_key':
        // Retorna a chave APENAS para inicializar o mapa visual.
        // Lembre-se: Configure restrição de DOMÍNIO no painel da TomTom.
        echo json_encode(['key' => $TOMTOM_KEY]);
        break;

    case 'search':
        // Proxy para busca de endereços
        $query = urlencode($_GET['query'] ?? '');
        if (strlen($query) < 3) { echo json_encode([]); exit; }
        
        $url = "https://api.tomtom.com/search/2/search/{$query}.json?key={$TOMTOM_KEY}&limit=5&countrySet=BR&language=pt-BR";
        echo requestTomTom($url);
        break;

    case 'route':
        // Proxy para cálculo de rota
        $start = $_GET['start'] ?? ''; // formato lat,lon
        $end = $_GET['end'] ?? '';     // formato lat,lon
        
        if (!$start || !$end) {
            http_response_code(400);
            echo json_encode(['error' => 'Origem e destino necessários']);
            exit;
        }

        $locations = "{$start}:{$end}";
        $url = "https://api.tomtom.com/routing/1/calculateRoute/{$locations}/json?key={$TOMTOM_KEY}&traffic=true&maxAlternatives=3&departAt=now&routeRepresentation=polyline";
        echo requestTomTom($url);
        break;

    default:
        http_response_code(400);
        echo json_encode(['error' => 'Ação inválida']);
        break;
}
?>