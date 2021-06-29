<?php

use App\Connection;
use App\Table\PostTable;

$pdo = Connection::getPDO();
$postTable = new PostTable($pdo);
$post = $postTable->find($params['id']);
// Pour afficher un message si modification réussie
$success = false;

if (!empty($_POST)) {
    $post->setName($_POST['name']);
    $postTable->update($post);
    // Si pas d'erreur lors de la requête
    $success = true;
}
?>

<!-- Message si modification réussie -->
<?php if ($success): ?>
    <div class="alert alert-success">
        L'article a bien été modifiée
    </div>
<?php endif ?>

<h1>Editer l'article <?= e($post->getName()) ?></h1>

<form action="" method="POST">
    <div class="form-group">
        <label for="name">Titre</label>
        <input type="text" class="form-control" name="name" value="<?= e($post->getName()) ?>">
    </div>
    <button class="btn btn-primary">Modifier</button>
</form>
