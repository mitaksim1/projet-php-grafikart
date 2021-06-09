<!-- Ce fichier contiendra les articles que l'on souhaite afficher -->

<?php
// Pour afficher le titre de la page

use App\Helpers\Text;
use App\Model\Post;

$title = 'Mon Blog';
$pdo = new PDO('mysql:dbname=tutoblog;host=127.0.0.1', 'root', 'Root*', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);
// On récupére les articles les plus récents
$query = $pdo->query('SELECT * FROM post ORDER BY created_at DESC LIMIT 12');
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