<!-- Ce fichier contiendra les articles que l'on souhaite afficher -->

<?php
// Pour afficher le titre de la page

use App\Helpers\Text;
use App\Model\Post;

$title = 'Mon Blog';

$pdo = new PDO('mysql:dbname=tutoblog;host=127.0.0.1', 'root', 'Root*', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);
// Initialise la page courante en prennant la valeur de la clé 'page'
$currentPage = (int)$_GET['page'] ?? 1;

// Envoi une erreur si la page envoyé n'est pas valide
if ($currentPage <= 0) {
    throw new Exception('Numéro de page invalide');
}

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