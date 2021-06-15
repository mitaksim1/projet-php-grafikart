<?php

use App\Connection;
use App\Model\{Category, Post};
use App\PaginatedQuery;

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

$paginatedQuery = new PaginatedQuery(
    "SELECT p.* 
        FROM post p 
        JOIN post_category pc ON pc.post_id = p.id
        WHERE pc.category_id = {$category->getId()}
        ORDER BY created_at DESC", 
    "SELECT COUNT(category_id) FROM post_category WHERE category_id = {$category->getId()}",
    Post::class
);
/** @var Post[] */
$posts = $paginatedQuery->getItems();
dd($posts);

// On sauvegarde la route à envoyer par le lien
$link = $router->url('category', ['id' => $category->getId(), 'slug' => $category->getSlug()]);

// Donne un titre à l'onglet de la page catégorie
$title = "Catégorie {$category->getName()}";

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
    <?php if ($currentPage > 1): ?>
        <?php
        // Pour ne pas écraser la valeur de $link, on va créer une variable intermédiaire pour la condition
        $link_2 = $link;
        if ($currentPage > 2) $link_2 = $link . '?page=' . ($currentPage - 1);
        ?>
        <a href="<?= $link_2 ?>" class="btn btn-primary">&laquo; Page précédente</a>
    <?php endif ?>
    <?php if ($currentPage < $pages): ?>
        <a href="<?= $link ?>?page=<?= $currentPage + 1 ?>" class="btn btn-primary ml-auto"> Page suivante &raquo;</a>
    <?php endif ?>
</div>


