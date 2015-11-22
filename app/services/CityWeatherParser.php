<?php
namespace App\Services;

// TODO: @Shoaibi: Break into CityParser and CityWeatherParser.
class CityWeatherParser {

    public function resolveWeatherDataForCities() {
        if ($citiesWeather = $this->getCitiesWeatherFromCache()) {
            return $citiesWeather;
        }

        $cities         = $this->resolveCities();
        $citiesWeather  = $this->resolveWeatherForCities($cities['data']);
        return $citiesWeather;
    }

    protected function resolveCities() {
        $cities         = $this->getCitiesFromCache();
        $response       = ServiceLocator::get('httpClient')->get($this->resolveConfigValue('cityDataSource'));
        list($etag, $lastModified, $contentLength) = $this->resolveCacheHeadersFromResponse($response);
        $updateCache    = $this->needsCacheUpdate($cities, $etag, $lastModified, $contentLength);
        if ($updateCache)
        {
            $cities         = compact('contentLength', 'etag', 'lastModified');
            $sourceData     = $response->getBody()->getContents();
            $this->updateCitiesCache($sourceData, $cities);
        }
        return $cities;
    }

    protected function resolveCacheHeadersFromResponse($response) {
        return [$response->getHeader('ETag'), $response->getHeader('Last-Modified'), $response->getHeader('Content-Length')];
    }

    protected function needsCacheUpdate($cities, $etag, $lastModified, $contentLength) {
        return (!$cities || ($etag != $cities['etag'] || $lastModified != $cities['lastModified'] || $contentLength != $cities['contentLength']));
    }

    protected function updateCitiesCache($sourceData, array & $cities) {
        $explodedData   = explode("\n", $sourceData);
        // discard the header, we won't accommodate column order change as that effectively changes api
        array_shift($explodedData);
        $data           = [];
        $keys           = ['country', 'zip', 'city', 'longitude', 'latitude'];
        foreach ($explodedData as $dataRow) {
            $data[]     = array_combine(array_values($keys), str_getcsv($dataRow, ' ', '""'));
        }
        $cities['data'] = $data;
        $this->setCitiesCache($cities);
    }

    protected function resolveWeatherForCities($cities) {
        $citiesWeather          = [];
        $ws                     = ServiceLocator::get('weatherService');
        foreach ($cities as $city) {
            $weatherData            = $this->resolveWeatherForCity($city, $ws);
            $temperatureDifference  = $weatherData['high'] - $weatherData['low'];
            $key                    = $temperatureDifference . "_" . (rand(100, 999)/1000);
            $citiesWeather[$key]    = $weatherData;

        }
        ksort($citiesWeather);
        $this->setCitiesWeatherCache($citiesWeather);
        return $citiesWeather;
    }

    protected function resolveWeatherForCity(array $city, WeatherService $ws)
    {
        $ws->getByLatitudeAndLongitude($city['latitude'], $city['longitude'], $this->resolveConfigValue('temperatureUnit'));
        $weatherData        = [
            'desc'          => $ws->getDescription(),
            'high'          => $ws->getHighTemperature(),
            'low'           => $ws->getLowTemperature(),
        ];
        $weatherData            = array_merge($city, $weatherData);
        return $weatherData;
    }


    protected function resolveCitiesKey() {
        return $this->resolveConfigValue('citiesCacheKey');
    }

    protected function resolveCitiesWeatherKey() {
        return $this->resolveConfigValue('citiesWeatherCacheKey');
    }

    protected function resolveConfigValue($configKey) {
        return ServiceLocator::get('config')->get('application.CityWeatherParser.'. $configKey);
    }

    protected function getCitiesFromCache() {
        return ServiceLocator::get('cache')->get($this->resolveCitiesKey());
    }

    protected function setCitiesCache($value) {
        return ServiceLocator::get('cache')->set($this->resolveCitiesKey(), $value);
    }

    protected function getCitiesWeatherFromCache() {
        return ServiceLocator::get('cache')->get($this->resolveCitiesWeatherKey());
    }

    protected function setCitiesWeatherCache($value) {
        return ServiceLocator::get('cache')->set($this->resolveCitiesWeatherKey(), $value);
    }
}