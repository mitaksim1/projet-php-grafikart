<?php

use App\Connection;
use App\Table\PostTable;
use App\Validator;

$pdo = Connection::getPDO();
$postTable = new PostTable($pdo);
$post = $postTable->find($params['id']);
// Pour afficher un message si modification réussie
$success = false;
$errors = [];

if (!empty($_POST)) {
    // On change la langue
    Validator::lang('fr');

    $validator = new Validator($_POST);

    // Valide l'existence du titre
    $validator->rule('required', 'name');
    // valide la longueur du titre
    $validator->rule('lengthBetween', 'name', 10, 200);
    $post->setName($_POST['name']);

    if ($validator->validate()) {
        $postTable->update($post);
        // Si pas d'erreur lors de la requête
        $success = true;
    } else {
        $errors = $validator->errors();
    }
}
?>

<!-- Message si modification réussie -->
<?php if ($success): ?>
    <div class="alert alert-success">
        L'article a bien été modifiée
    </div>
<?php endif ?>

<!-- Message si erreur lors de la modification -->
<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        L'article n'a pas pu être modifié, merci de corriger vos erreurs
    </div>
<?php endif ?>

<h1>Editer l'article <?= e($post->getName()) ?></h1>

<form action="" method="POST">
    <div class="form-group">
        <label for="name">Titre</label>
        <input type="text" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" name="name" value="<?= e($post->getName()) ?>" required>
        <?php if (isset($errors['name'])): ?>
        <div class="invalid-feedback">
            <?= implode('<br>', $errors['name']) ?>
        </div>
        <?php endif ?>
    </div>
    <button class="btn btn-primary">Modifier</button>
</form>
