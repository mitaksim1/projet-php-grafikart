<?php
// Import d'Altorouter
require '../vendor/autoload.php';

// Pour démarrer AltoRouter, on l'instancie
$router = new AltoRouter();

// Une fois AltoRouter instancié, on pourra créer les routes
$router->map('GET', '/blog', function() {
    require dirname(__DIR__) . 'views/post/index.php';
});

$router->map('GET', '/blog/category', function() {
    require dirname(__DIR__) . 'views/category/show.php';
});
