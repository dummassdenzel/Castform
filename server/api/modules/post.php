<?php

require_once 'global.php';

class Post extends GlobalMethods
{
    private $pdo;
    private $openWeatherKey;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->openWeatherKey = $_ENV['OPENWEATHER_KEY'];
    }

    public function searchByCity($data)
    {
        if (!isset($data->city) || empty($data->city)) {
            return $this->sendPayload(null, "failed", "Please enter a City.", 400);
        }

        $city = $data->city;
        $api_key = $this->openWeatherKey;
        $api = "https://api.openweathermap.org/data/2.5/weather?q=$city&appid=$api_key";

        $result = file_get_contents($api);

        if ($result === false) {
            return $this->sendPayload(null, "failed", "Unable to fetch weather data.", 500);
        }

        $resultArray = json_decode($result, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->sendPayload(null, "failed", "Invalid JSON response.", 500);
        }

        return $this->sendPayload($resultArray, "success", "Request Success!", 200);
    }

}
