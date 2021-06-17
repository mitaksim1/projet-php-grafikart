<!-- Ce fichier contiendra les articles que l'on souhaite afficher -->

<?php
// Pour afficher le titre de la page

use PDO;
use App\URL;
use App\Connection;
use App\Model\Post;
use App\Helpers\Text;
use App\Model\Category;
use App\PaginatedQuery;
use App\Table\PostTable;

$title = 'Mon Blog';

$pdo = Connection::getPDO();

$table = new PostTable($pdo);
$var = $table->findPaginated();
$posts = $var[0];
$pagination = $var[1];

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
    <?= $pagination->previousLink($link) ?>
    <?= $pagination->nextLink($link) ?>
</div>