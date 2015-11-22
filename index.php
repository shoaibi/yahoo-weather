<?php
require_once('bootstrap.php');

// TODO: Use a View component
echo file_get_contents(HTML_RESOURCES_DIR . DIRECTORY_SEPARATOR . 'header.html');

try {
    if (!isset($_GET['latitude'], $_GET['longitude'])) {
        echo file_get_contents(HTML_RESOURCES_DIR . DIRECTORY_SEPARATOR . 'form.html');
    } else {
        $latitude           = floatval($_GET['latitude']);
        $longitude          = floatval($_GET['longitude']);
        $wrc = new \App\Services\WeatherResultsCompiler();
        echo $wrc->render($latitude, $longitude);
    }
} catch (Exception $e) {
    echo '<div class="alert alert-danger" role="alert">' . $e->getMessage() . '</div>';
}

echo file_get_contents(HTML_RESOURCES_DIR . DIRECTORY_SEPARATOR . 'footer.html');
