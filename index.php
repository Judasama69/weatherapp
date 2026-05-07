<?php

require_once 'vendor/autoload.php';
require_once __DIR__ . '/app/Config/Config.php';
require_once __DIR__ . '/app/Services/WeatherService.php';

use App\Config\Config;
use App\Services\WeatherService;

session_start();

$config = Config::getInstance();
$weatherService = new WeatherService(
    $config->get('api_key'),
    $config->get('api_base_url')
);

$weather = null;
$error = null;
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// Let the user choose a city; keep the input cleared on refresh (Post/Redirect/Get).
if ($requestMethod === 'POST' && isset($_POST['city'])) {
    $submittedCity = trim((string) $_POST['city']);
    if ($submittedCity === '') {
        $_SESSION['flash_error'] = 'Please enter a city name';
    } else {
        $_SESSION['selected_city'] = $submittedCity;
    }

    header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
    exit;
}

if (isset($_SESSION['flash_error'])) {
    $error = (string) $_SESSION['flash_error'];
    unset($_SESSION['flash_error']);
}

$city = (string) ($_SESSION['selected_city'] ?? $config->get('default_city'));
$rawData = $weatherService->getCurrentWeather($city);
if (isset($rawData['error'])) {
    $error = $rawData['message'];
} else {
    $weather = $weatherService->formatWeatherData($rawData);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weather App</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 16px;
        }

        .container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 500px;
            width: 100%;
        }

        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
            font-size: 28px;
        }

        .search-box {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
        }

        .search-box input {
            flex: 1;
            padding: 12px 15px;
            border: 2px solid #ddd;
            border-radius: 10px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        .search-box input:focus {
            outline: none;
            border-color: #667eea;
        }

        .search-box button {
            padding: 12px 30px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }

        .search-box button:hover {
            background: #5568d3;
        }

        .error {
            background: #fee;
            color: #c33;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid #c33;
        }

        .weather-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            text-align: center;
            margin-bottom: 20px;
        }

        .city-name {
            font-size: clamp(22px, 5vw, 32px);
            font-weight: bold;
            margin-bottom: 10px;
            line-height: 1.15;
        }

        .temperature {
            font-size: clamp(44px, 10vw, 64px);
            font-weight: bold;
            margin: 20px 0;
        }

        .description {
            font-size: 18px;
            text-transform: capitalize;
            opacity: 0.9;
        }

        @media (max-width: 480px) {
            body {
                padding: 12px;
            }

            .container {
                padding: 18px;
                border-radius: 16px;
            }

            h1 {
                font-size: 22px;
                margin-bottom: 16px;
            }

            .search-box {
                flex-direction: column;
                gap: 12px;
                margin-bottom: 16px;
            }

            .search-box button {
                width: 100%;
            }

            .weather-card {
                padding: 18px;
                border-radius: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>check the weather in your city</h1>

        <form method="POST" class="search-box">
            <input
                type="text"
                name="city"
                placeholder="Enter city name..."
                value=""
            >
            <button type="submit">Search</button>
        </form>

        <?php if ($error): ?>
            <div class="error">
                error <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($weather && !isset($weather['error'])): ?>
            <div class="weather-card">
                <div class="city-name">
                    <?php echo htmlspecialchars($weather['city']); ?>, <?php echo htmlspecialchars($weather['country']); ?>
                </div>
                <div class="temperature">
                    <?php echo round($weather['temperature']); ?>°C
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>

<footer>Made by kenjie</footer>
</html>
