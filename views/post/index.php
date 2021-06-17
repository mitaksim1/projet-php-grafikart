<!-- Ce fichier contiendra les articles que l'on souhaite afficher -->

<?php
// Pour afficher le titre de la page

use App\Connection;
use App\Helpers\Text;
use App\Model\Category;
use App\Model\Post;
use App\PaginatedQuery;
use App\URL;

$title = 'Mon Blog';

$pdo = Connection::getPDO();

$paginatedQuery = new PaginatedQuery(
    "SELECT * FROM post ORDER BY created_at DESC",
    "SELECT COUNT(id) FROM post"
);

$posts = $paginatedQuery->getItems(Post::class);

// On récupère l'id de chaque article
$ids = [];
foreach ($posts as $post) {
    $ids[] = $post->getId();
}

$categories = $pdo
    ->query('SELECT c.*, pc.post_id
        FROM post_category pc
        JOIN category c ON c.id = pc.category_id
        WHERE pc.post_id IN (' . implode(',', $ids) . ')'
    )->fetchAll(PDO::FETCH_CLASS, Category::class);
dd($categories);

// On parcourt les catégories
    // On trouve l'article $posts correspondant à la ligne
        // On ajoute la catégorie à l'article

$link = $router->url('home');
?>

<h1>Mon Blog</h1>

<?php //dump($posts);exit; 
?>

<div class="row">
    <?php foreach ($posts as $post) : ?>
        <div class="col-md-3">
            <?php require 'card.php' ?>
        </div>
    <?php endforeach ?>
</div>

<div class="d-flex justify-content-between my-4">
    <?= $paginatedQuery->previousLink($link) ?>
    <?= $paginatedQuery->nextLink($link) ?>
</div>