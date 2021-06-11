<!-- Ce fichier contiendra les articles que l'on souhaite afficher -->

<?php
// Pour afficher le titre de la page

use App\Connection;
use App\Helpers\Text;
use App\Model\Post;

$title = 'Mon Blog';

$pdo = Connection::getPDO();

$page = $_GET['page'] ?? 1;

// Si la valeur saisi dans $page n'est pas un entier
if (!filter_var($page, FILTER_VALIDATE_INT)) {
    throw new Exception('Numéro de page invalide');
}

$currentPage = (int)$page;

// Calcule le nombre d'articles total dans la bdd
$count = (int)$pdo->query('SELECT COUNT(id) FROM post')->fetch(PDO::FETCH_NUM)[0];

// Calcule le nombre d'articles qu'on mettra par page
$perPage = 12;
$pages = ceil($count / $perPage);
// dd($pages);

if ($currentPage > $pages) {
    throw new Exception('Cette page n\'existe pas');
}

// On calcule le offset par page
$offset = $perPage * ($currentPage -1);

// On récupére les articles les plus récents
$query = $pdo->query("SELECT * FROM post ORDER BY created_at DESC LIMIT $perPage OFFSET $offset");

$posts = $query->fetchAll(PDO::FETCH_CLASS, Post::class);
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
    <?php if ($currentPage > 1): ?>
        <?php
        $link = $router->url('home');
        if ($currentPage > 2) $link .= '?page=' . ($currentPage - 1);
        ?>
        <a href="<?= $link ?>" class="btn btn-primary">&laquo; Page précédente</a>
    <?php endif ?>
    <?php if ($currentPage < $pages): ?>
        <a href="<?= $router->url('home') ?>?page=<?= $currentPage + 1 ?>" class="btn btn-primary ml-auto"> Page suivante &raquo;</a>
    <?php endif ?>
</div>