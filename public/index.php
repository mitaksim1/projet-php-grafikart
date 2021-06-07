<?php
// Import d'Altorouter
require '../vendor/autoload.php';

// Pour démarrer AltoRouter, on l'instancie
$router = new AltoRouter();

// Création d'une constante pour éviter la répétition
define('VIEW_PATH', dirname(__DIR__) . '/views');

// Une fois AltoRouter instancié, on pourra créer les routes
$router->map('GET', '/blog', function() {
    require  VIEW_PATH . '/post/index.php';
});

$router->map('GET', '/blog/category', function() {
    require VIEW_PATH . '/category/show.php';
});

// Une fois que les routes sont créés on peut vérifier si la route demandée correspond à une de ces deux routes.
$match = $router->match();
// On appelle la fonction contenue dans la valeur de la clé 'target'
$match['target']();
    