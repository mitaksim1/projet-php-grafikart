<!-- Ce fichier contiendra les articles que l'on souhaite afficher -->

<?php
// Pour afficher le titre de la page

use App\Helpers\Text;

$title = 'Mon Blog';
$pdo = new PDO('mysql:dbname=tutoblog;host=127.0.0.1', 'root', 'Root*', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);
// On récupére les articles les plus récents
$query = $pdo->query('SELECT * FROM post ORDER BY created_at DESC LIMIT 12');
$posts = $query->fetchAll(PDO::FETCH_OBJ);
?>

<h1>Mon Blog</h1>

<div class="row">
    <?php foreach ($posts as $post): ?>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title"><?= htmlentities($post->name) ?></h5>
                <p><?= nl2br(htmlentities(Text::excerpt($post->content))) ?></p>
                <p>
                    <a href="" class="btn btn-primary">Voir plus</a>
                </p>
            </div>
        </div>
    </div>
    <?php endforeach ?>
</div>


