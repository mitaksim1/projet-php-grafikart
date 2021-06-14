<?php

use App\Connection;
use App\Model\Category;

$id = (int)$params['id'];
$slug = $params['slug'];

// Requête pour récupérer un article selon so ID
$pdo = Connection::getPDO();
// Comme on va recevoir des paramètres envoyés par l'utilisateur on fait une rquête préparé
$query = $pdo->prepare('SELECT * FROM category WHERE id = :id');
// On précise que l'id correspondra à l'id envoyé par l'utilisateur
$query->execute(['id' => $id]);
$query->setFetchMode(PDO::FETCH_CLASS, Category::class);
/**
 * On peut typer cette variable comme suit :
 * @var Category|false
 */
$category = $query->fetch();
// dd($category);

if ($category === false) {
    throw new Exception('Aucune catégorie ne correspond à cet ID');
}

if ($category->getSlug() !== $slug) {
    // 'category' : nom donné lors de la création de la route
    $url = $router->url('category', ['slug' => $category->getSlug(), 'id' => $id]);
    // Code de redirection
    http_response_code(301);
    // Si pas la bonne url on redirige vers la bonne
    header('Location: ' . $url);
}
// dd($category);

// Donne un titre à l'onglet de la page catégorie
$title = "Catégorie {$category->getName()}";
?>

<h1><?= e($title) ?></h1>


