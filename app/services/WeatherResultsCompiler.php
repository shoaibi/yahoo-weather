<?php

namespace App\Services;

class WeatherResultsCompiler {
    public function render($latitude, $longitude) {
        $distanceUnit       = ServiceLocator::get('config')->get('application.WeatherResultsCompiler.distanceUnit');
        $temperatureUnit    = ServiceLocator::get('config')->get('application.CityWeatherParser.temperatureUnit');
        $results            = $this->compile($latitude, $longitude, $distanceUnit);
        if (empty($results)) {
            throw new \Exception("Unable to compile data against: ($latitude, $longitude)");
        }
        $content    = $this->compileCityTableContent($results);
        $content    = $this->compileTemplateContent($content, $latitude, $longitude, $distanceUnit, $temperatureUnit);
        return $content;
    }

    protected function compileCityTableContent(array $cities) {
        $content            = '';
        foreach ($cities as $city) {
            $content .= "<tr>";
            $content .= "<td>${city['city']}</td>";
            $content .= "<td>${city['low']}</td>";
            $content .= "<td>${city['high']}</td>";
            $content .= "<td>${city['distance']}</td>";
            $content .= "<td>${city['desc']}</td>";
            $content .= "</tr>";
        }
        return $content;
    }

    protected function compileTemplateContent($content, $latitude, $longitude, $distanceUnit, $temperatureUnit) {
        $templateContent = file_get_contents(HTML_RESOURCES_DIR . DIRECTORY_SEPARATOR . 'table.html');
        $templatePlaceHolders = [
            '__DUNIT__' => $distanceUnit,
            '__TUNIT__' => $temperatureUnit,
            '__CONTENT__' => $content,
            '__LAT__' => $latitude,
            '__LONG__' => $longitude,
            '__URL__' => strtok($_SERVER["REQUEST_URI"],'?'),
        ];
        $content        = strtr($templateContent, $templatePlaceHolders);
        return $content;
    }

    protected function compile($latitude, $longitude, $distanceUnit) {
        $cacheKey = min($latitude, $longitude) . '_' . max($latitude, $longitude);
        $cacheKey = $this->resolveCitiesDistanceWeatherKey() . '_' . $cacheKey;
        if ($compiledData = ServiceLocator::get('cache')->get($cacheKey)) {
            return $compiledData;
        }
        $cityWeather    = ServiceLocator::get('cityWeatherParser')->resolveWeatherDataForCities();
        $compiledData   = $this->resolveCompiledData($cityWeather, $latitude, $longitude, $distanceUnit);
        ServiceLocator::get('cache')->set($cacheKey, $compiledData);
        return $compiledData;
    }

    protected function resolveCompiledData(array $cityWeather, $latitude, $longitude, $distanceUnit) {
        $compiledData = [];
        foreach ($cityWeather as $city) {
            $city['distance'] = $this->resolveDistance($city['latitude'], $city['longitude'], $latitude, $longitude. $distanceUnit);
            $compiledData[] = $city;
        }
        return $compiledData;
    }


    protected function resolveDistance($cityLatitude, $cityLongitude, $userLatitude, $userLongitude, $unit = 'M') {
        // Taken from: http://www.geodatasource.com/developers/php
        // i was going to use distance formula but then I remembered that earth isn't flat.
        // and while researching found the above mentioned. My sin and cos concepts need a refresher so I am not
        // fully sure of what is happening in the 2nd line of code below but I have an idea.
        $theta = $cityLongitude - $userLongitude;
        $distance = sin(deg2rad($cityLatitude)) * sin(deg2rad($userLatitude)) +  cos(deg2rad($cityLatitude)) * cos(deg2rad($userLatitude)) * cos(deg2rad($theta));
        $distance = acos($distance);
        $distance = rad2deg($distance);
        $distanceInMiles = $distance * 60 * 1.1515;

        if (!in_array($unit, ['M', 'NM', 'K'])) {
            throw new \Exception("Invalid distance unit ($unit) specified.");
        }

        if ($unit == "K") {
            return ($distanceInMiles* 1.609344);
        } else if ($unit == "N") {
            return ($distanceInMiles * 0.8684);
        } else {
            return $distanceInMiles;
        }
    }

    protected function resolveCitiesDistanceWeatherKey() {
        return ServiceLocator::get('config')->get('application.WeatherResultsCompiler.'. 'citiesDistanceWeatherCacheKey');
    }
}