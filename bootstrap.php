<?php
require_once('vendor/autoload.php');

defined('BASE_DIR') || define('BASE_DIR', __DIR__);
defined('APP_DIR') || define('APP_DIR', BASE_DIR . DIRECTORY_SEPARATOR . 'app');
defined('RESOURCES_DIR') || define('RESOURCES_DIR', BASE_DIR . DIRECTORY_SEPARATOR . 'resources');
defined('HTML_RESOURCES_DIR') || define('HTML_RESOURCES_DIR', RESOURCES_DIR . DIRECTORY_SEPARATOR . 'html');
defined('SERVICES_DIR') || define('SERVICES_DIR', APP_DIR . DIRECTORY_SEPARATOR . 'services');
defined('CONFIG_DIR') || define('CONFIG_DIR', APP_DIR . DIRECTORY_SEPARATOR . 'config');

use Pimple\Container;
$container = new Container();

$container['config'] = function ($c) {
    $configValues= require_once(CONFIG_DIR . DIRECTORY_SEPARATOR . 'main.php');
    return new App\Services\DotNotation($configValues);
};

$container['cache'] = function ($c) {
    // TODO: Swap out and Use a cache which supports cache namespaces
    $cacheConfig = $c['config']->get('components.cache');
    phpFastCache::setup("storage", $cacheConfig['storage']);
    phpFastCache::setup("path", $cacheConfig['path']);
    return phpFastCache();
};

$container['weatherService'] = function ($c) {
    return new App\Services\YahooWeather();
};

$container['cityWeatherParser'] = function ($c) {
    return new App\Services\CityWeatherParser();
};

$container['httpClient'] = function ($c) {
    return new GuzzleHttp\Client();
};

\App\Services\ServiceLocator::setContainer($container);
