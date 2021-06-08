<?php
// Import d'Altorouter

use App\Router;

require '../vendor/autoload.php';

$router = new Router(dirname(__DIR__) . '/views');
$router->get('/blog', 'post/index', 'blog');
$router->get('/blog/category', 'category/show', 'category');
$router->run();


    