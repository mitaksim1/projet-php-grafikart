<?php
// Import d'Altorouter
require '../vendor/autoload.php';

use App\Router;

// Cette constante va gérer le temps que la page a mis pour se charger (performance)
// microtime(true) : va nous donner la valeur en format float
define('DEBUG_TIME', microtime(true));
// sleep(2);

$whoops = new \Whoops\Run;
$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
$whoops->register();

$router = new Router(dirname(__DIR__) . '/views');
$router->get('/', 'post/index', 'home');
$router->get('/blog/category', 'category/show', 'category');
$router->run();


    