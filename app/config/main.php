<?php

return [
    'application' => [
        'CityWeatherParser' => [
            // format: country zip city longitude latitude
            'cityDataSource' => 'https://url.of.a.catalog/file.dat',
            'citiesCacheKey' => 'cities',
            'citiesWeatherCacheKey' => 'cities_weather',
            'temperatureUnit' => 'c',

        ],
        'WeatherResultsCompiler' => [
            'distanceUnit' => 'M',
            'citiesDistanceWeatherCacheKey' => 'cities_distance_weather',
        ],
    ],
    'components' => [
        'cache' => [
            'storage' => 'files', // read more about available options at: https://github.com/khoaofgod/phpfastcache/blob/final/phpfastcache/3.0.0/phpfastcache.php#L51
            'path' => BASE_DIR . DIRECTORY_SEPARATOR . 'cache',
        ],
    ],
];
