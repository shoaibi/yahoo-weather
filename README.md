## Requirement
- A catalog of cities is provided with coordinates
- Get a new set of coordinates
- Use Yahoo weather API to get weather for provided list of cities
- Compute distance between user entered coordinates and cities
- Show a grid of cities, sorted by temperature variation

## How to run the application?
- Run composer update
- Run composer dump-autoload -o (it would save a little time during requests)
- Ensure "cache" exists in the under app directory and is writable by the user that would run the application
- Visit the index.php inside your browser

## Dependencies 
- "guzzlehttp/guzzle": "^6.1",
- "pimple/pimple": "~3.0",
- "phpfastcache/phpfastcache": "^3.0"
- https://gist.github.com/treffynnon/563670/6d3d934eb5ca9916e1fef8f8cc08f89ea90a025e
- http://www.geodatasource.com/developers/php


## Few Missing things:
- String validation
- Unit testing
- Using a cache system that supports namespaces
