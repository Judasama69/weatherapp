<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class WeatherService
{
    private $client;
    private $apiKey;
    private $baseUrl;

    public function __construct($apiKey, $baseUrl)
    {
        $this->apiKey = $apiKey;
        $this->baseUrl = $baseUrl;
        $this->client = new Client();
    }

    private function validateConfig()
    {
        if (empty($this->apiKey)) {
            return [
                'error' => true,
                'message' => 'Missing API key. Set API_KEY in your .env file (or system environment variables).',
            ];
        }

        if (empty($this->baseUrl)) {
            return [
                'error' => true,
                'message' => 'Missing API base URL. Set API_BASE_URL in your .env file (or system environment variables).',
            ];
        }

        if (!filter_var($this->baseUrl, FILTER_VALIDATE_URL)) {
            return [
                'error' => true,
                'message' => 'Invalid API base URL: ' . $this->baseUrl,
            ];
        }

        return null;
    }

    /**
     * Get current weather for a city
     */
    public function getCurrentWeather($city)
    {
        $configError = $this->validateConfig();
        if ($configError !== null) {
            return $configError;
        }

        try {
            $response = $this->client->request('GET', $this->baseUrl . '/weather', [
                'query' => [
                    'q' => $city,
                    'appid' => $this->apiKey,
                    'units' => 'metric'  // Use Celsius
                ]
            ]);

            return json_decode($response->getBody(), true);
        } catch (GuzzleException $e) {
            return [
                'error' => true,
                'message' => 'Failed to fetch weather: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get weather forecast for 5 days
     */
    public function getForecast($city)
    {
        $configError = $this->validateConfig();
        if ($configError !== null) {
            return $configError;
        }

        try {
            $response = $this->client->request('GET', $this->baseUrl . '/forecast', [
                'query' => [
                    'q' => $city,
                    'appid' => $this->apiKey,
                    'units' => 'metric'
                ]
            ]);

            return json_decode($response->getBody(), true);
        } catch (GuzzleException $e) {
            return [
                'error' => true,
                'message' => 'Failed to fetch forecast: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get weather by coordinates
     */
    public function getWeatherByCoordinates($lat, $lon)
    {
        $configError = $this->validateConfig();
        if ($configError !== null) {
            return $configError;
        }

        try {
            $response = $this->client->request('GET', $this->baseUrl . '/weather', [
                'query' => [
                    'lat' => $lat,
                    'lon' => $lon,
                    'appid' => $this->apiKey,
                    'units' => 'metric'
                ]
            ]);

            return json_decode($response->getBody(), true);
        } catch (GuzzleException $e) {
            return [
                'error' => true,
                'message' => 'Failed to fetch weather: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Format weather data for display
     */
    public function formatWeatherData($data)
    {
        if (isset($data['error'])) {
            return $data;
        }

        return [
            'city' => $data['name'] ?? 'Unknown',
            'country' => $data['sys']['country'] ?? '',
            'temperature' => $data['main']['temp'] ?? 0,
            'feels_like' => $data['main']['feels_like'] ?? 0,
            'humidity' => $data['main']['humidity'] ?? 0,
            'pressure' => $data['main']['pressure'] ?? 0,
            'description' => $data['weather'][0]['description'] ?? '',
            'icon' => $data['weather'][0]['icon'] ?? '',
            'wind_speed' => $data['wind']['speed'] ?? 0,
            'clouds' => $data['clouds']['all'] ?? 0
        ];
    }
}
?>
