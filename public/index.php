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

// Gére la redirection de la page si ?page=1
if (isset($_GET['page']) && $_GET['page'] === '1') {
    // réécrire l'url sans le paramètre ?page
    $uri = explode('?', $_SERVER['REQUEST_URI'])[0];
    $get = $_GET;
    // unset détruit la valeur passée en paramètre
    unset($get['page']);
    $query = http_build_query($get);
    if (!empty($query)) {
        $uri = $uri . '?' .$query;
    }
    http_response_code(301);
    header('Location: ' . $uri);
    exit();
}

$router = new Router(dirname(__DIR__) . '/views');
$router->get('/', 'post/index', 'home')
        ->get('/blog/category/[*:slug]-[i:id]', 'category/show', 'category')
        ->get('/blog/[*:slug]-[i:id]', 'post/show', 'post')
        // ADMIN
        // Gestion des articles
        ->get('/admin', 'admin/post/index', 'admin_posts')
        ->match('/admin/post/[i:id]', 'admin/post/edit', 'admin_post')
        ->post('/admin/post/[i:id]/delete', 'admin/post/delete', 'admin_post_delete')
        ->match('/admin/post/new', 'admin/post/new', 'admin_post_new')
        ->get('/admin/form', 'admin/form', 'form')
        // Gestion des catégories
        ->get('/admin/categories', 'admin/category/index', 'admin_categories')
        ->match('/admin/category/[i:id]', 'admin/category/edit', 'admin_category')
        ->post('/admin/category/[i:id]/delete', 'admin/category/delete', 'admin_category_delete')
        ->match('/admin/category/new', 'admin/category/new', 'admin_category_new')
        ->get('/admin/form', 'admin/form', 'form')

        ->run();


    