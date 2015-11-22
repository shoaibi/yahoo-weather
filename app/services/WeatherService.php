<?php
namespace App\Services;

interface WeatherService {
    public function getByLatitudeAndLongitude($latitude, $longitude);
    public function getDescription();
    public function getHighTemperature();
    public function getLowTemperature();
}