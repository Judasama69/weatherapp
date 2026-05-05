<?php

namespace App\Config;

use Dotenv\Dotenv;

class Config
{
    private static $instance = null;
    private $env = [];

    private function __construct()
    {
        $this->loadEnv();
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function loadEnv()
    {
        $projectRoot = realpath(__DIR__ . '/../../') ?: (__DIR__ . '/../../');
        $dotenv = Dotenv::createImmutable($projectRoot);
        $dotenv->safeLoad();

        $this->env = [
            'api_key' => $_ENV['API_KEY'] ?? $_SERVER['API_KEY'] ?? '',
            'api_base_url' => $_ENV['API_BASE_URL'] ?? $_SERVER['API_BASE_URL'] ?? 'https://api.openweathermap.org/data/2.5',
            'debug' => filter_var($_ENV['APP_DEBUG'] ?? $_SERVER['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOL),
            'default_city' => $_ENV['DEFAULT_CITY'] ?? $_SERVER['DEFAULT_CITY'] ?? 'Bais',
        ];
    }

    public function get($key)
    {
        return $this->env[$key] ?? null;
    }

    public function all()
    {
        return $this->env;
    }
}

