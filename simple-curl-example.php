<?php

/**
 * Alternative: Weather API using cURL (No Guzzle)
 * 
 * This file shows how to integrate APIs using only built-in PHP cURL
 * No external dependencies needed!
 */

class SimpleWeatherAPI
{
    private $apiKey;
    private $baseUrl;

    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
        $this->baseUrl = 'https://api.openweathermap.org/data/2.5';
    }

    /**
     * Make cURL request to API
     */
    private function makeRequest($endpoint, $params)
    {
        // Build query string
        $queryString = http_build_query(array_merge($params, [
            'appid' => $this->apiKey,
            'units' => 'metric'
        ]));

        $url = $this->baseUrl . $endpoint . '?' . $queryString;

        // Initialize cURL
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json'
            ]
        ]);

        // Execute request
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        curl_close($ch);

        // Handle errors
        if ($error) {
            return [
                'success' => false,
                'error' => 'cURL Error: ' . $error
            ];
        }

        if ($httpCode !== 200) {
            return [
                'success' => false,
                'error' => 'API Error: HTTP ' . $httpCode
            ];
        }

        // Decode response
        $data = json_decode($response, true);
        
        if ($data === null) {
            return [
                'success' => false,
                'error' => 'Invalid JSON response'
            ];
        }

        return [
            'success' => true,
            'data' => $data
        ];
    }

    /**
     * Get current weather by city
     */
    public function getWeather($city)
    {
        return $this->makeRequest('/weather', ['q' => $city]);
    }

    /**
     * Get weather by coordinates
     */
    public function getWeatherByCoords($lat, $lon)
    {
        return $this->makeRequest('/weather', [
            'lat' => $lat,
            'lon' => $lon
        ]);
    }

    /**
     * Get 5-day forecast
     */
    public function getForecast($city)
    {
        return $this->makeRequest('/forecast', ['q' => $city]);
    }

    /**
     * Format weather for display
     */
    public function format($rawData)
    {
        if (!isset($rawData['main'])) {
            return null;
        }

        return [
            'city' => $rawData['name'],
            'country' => $rawData['sys']['country'],
            'temp' => round($rawData['main']['temp']),
            'feels_like' => round($rawData['main']['feels_like']),
            'description' => $rawData['weather'][0]['description'],
            'humidity' => $rawData['main']['humidity'],
            'wind_speed' => round($rawData['wind']['speed'], 1),
            'pressure' => $rawData['main']['pressure']
        ];
    }
}

// ============================================
// USAGE EXAMPLE
// ============================================

// Initialize (get key from environment or config)
$apiKey = getenv('OPENWEATHER_API_KEY') ?: 'your-api-key-here';
$weather = new SimpleWeatherAPI($apiKey);

// Get weather
$result = $weather->getWeather('Manila');

if ($result['success']) {
    $formatted = $weather->format($result['data']);
    echo "City: " . $formatted['city'] . "\n";
    echo "Temperature: " . $formatted['temp'] . "°C\n";
    echo "Description: " . $formatted['description'] . "\n";
} else {
    echo "Error: " . $result['error'] . "\n";
}

?>