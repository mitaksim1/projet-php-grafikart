<?php

use App\Connection;
use App\Model\{Category, Post};
use App\Table\CategoryTable;
use App\Table\PostTable;

$id = (int)$params['id'];
$slug = $params['slug'];

// Requête pour récupérer un article selon so ID
$pdo = Connection::getPDO();
$categoryTable = new CategoryTable($pdo);
$category = $categoryTable->find($id);

if ($category->getSlug() !== $slug) {
    // 'category' : nom donné lors de la création de la route
    $url = $router->url('category', ['slug' => $category->getSlug(), 'id' => $id]);
    // Code de redirection
    http_response_code(301);
    // Si pas la bonne url on redirige vers la bonne
    header('Location: ' . $url);
}
// dd($category);

[$posts, $paginatedQuery] = (new PostTable($pdo))->findPaginatedForCategory($category->getId());

// On sauvegarde la route à envoyer par le lien
$link = $router->url('category', ['id' => $category->getId(), 'slug' => $category->getSlug()]);
// Donne un titre à l'onglet de la page catégorie
$title = 'Catégorie ' . $category->getName();
?>

<h1><?= e($title) ?></h1>

<div class="row">
    <?php foreach ($posts as $post) : ?>
        <div class="col-md-3">
            <?php require dirname(__DIR__) . '/post/card.php'?>
        </div>
    <?php endforeach ?>
</div>

<div class="d-flex justify-content-between my-4">
    <?= $paginatedQuery->previousLink($link) ?>
    <?= $paginatedQuery->nextLink($link) ?>
</div>


