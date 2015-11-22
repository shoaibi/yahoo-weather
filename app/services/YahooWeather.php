<?php
namespace App\Services;

class YahooWeather implements WeatherService {

    const API_ENDPOINT = 'http://query.yahooapis.com/v1/public/yql';

    protected $weatherData;

    public function getByLatitudeAndLongitude($latitude, $longitude, $unit = 'c') {
        $query              = $this->resolveWeatherQuery($latitude, $longitude, $unit);
        $namespacedQuery    = static::API_ENDPOINT . '?q=' . urlencode($query) . '&format=json';

        // could use ->json() but we want an object, not an array
        $result             = ServiceLocator::get('httpClient')->get($namespacedQuery);
        $json               = $result->getBody()->getContents();
        if (empty($json)) {
            throw new \Exception('Yahoo weather API did not respond with any content');
        }

        $this->weatherData  = json_decode($json);
        if (json_last_error() != JSON_ERROR_NONE)
        {
            throw new \Exception('Yahoo responded with an invalid json');
        }
    }

    protected function validateAPIResponse($response) {
        if ($response->getStatusCode() != 200) {
            throw new \Exception('Yahoo weather API failed with a non OK status code: ' . $response->getStatusCode());
        }
        if ($response-> getHeader('content-type') != 'application/json; charset=utf8') {
            throw new \Exception('Yahoo weather API did not reply with a proper json header: ' . $response-> getHeader('content-type'));
        }
    }

    protected function getWoidQuery($latitude, $longitude)
    {
        return 'select woeid from geo.placefinder where text="' . $latitude . ',' .
                    $longitude . '" and gflags="R"';
    }

    protected function resolveWeatherQuery($latitude, $longitude, $unit)
    {
        $woeidQuery         = $this->getWoidQuery($latitude, $longitude);
        $query              = 'select units, item from weather.forecast where woeid in ' .
                            '(' . $woeidQuery . ') and u="' . $unit . '"';
        return $query;
    }

    public function getDescription() {
        return $this->getItemData('description');
    }

    public function getHighTemperature() {
        return $this->getItemData('forecast')[0]->high;
    }

    public function getLowTemperature() {
        return $this->getItemData('forecast')[0]->low;
    }

    protected function getItemData($key) {
        $item = $this->weatherData->query->results->channel->item;
        return $item->{$key};
    }
}
