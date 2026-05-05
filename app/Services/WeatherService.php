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
                'message' => 'Missing API key. Set API_KEY in your .env file (or in Vercel Environment Variables).',
            ];
        }

        if (empty($this->baseUrl)) {
            return [
                'error' => true,
                'message' => 'Missing API base URL. Set API_BASE_URL in your .env file (or in Vercel Environment Variables).',
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

    public function getCurrentWeather($city)
    {
        $configError = $this->validateConfig();
        if ($configError !== null) {
            return $configError;
        }

        try {
            $response = $this->client->request('GET', rtrim($this->baseUrl, '/') . '/weather', [
                'query' => [
                    'q' => $city,
                    'appid' => $this->apiKey,
                    'units' => 'metric',
                ],
            ]);

            return json_decode($response->getBody(), true);
        } catch (GuzzleException $e) {
            return [
                'error' => true,
                'message' => 'Failed to fetch weather: ' . $e->getMessage(),
            ];
        }
    }

    public function formatWeatherData($data)
    {
        if (isset($data['error'])) {
            return $data;
        }

        return [
            'city' => $data['name'] ?? 'Unknown',
            'country' => $data['sys']['country'] ?? '',
            'temperature' => $data['main']['temp'] ?? 0,
        ];
    }
}

