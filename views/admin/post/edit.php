<?php

use App\Connection;
use App\Table\PostTable;
use App\HTML\Form;
use App\ObjectHelper;
use App\Validators\PostValidator;

$pdo = Connection::getPDO();
$postTable = new PostTable($pdo);
$post = $postTable->find($params['id']);
// Pour afficher un message si modification réussie
$success = false;
$errors = [];

if (!empty($_POST)) {

    // Validation des articles
    $validator = new PostValidator($_POST, $postTable, $post->getId());
    ObjectHelper::hydrate($post, $_POST, ['name', 'content', 'slug', 'created_at']);

    if ($validator->validate()) {
        $postTable->update($post);
        // Si pas d'erreur lors de la requête
        $success = true;
    } else {
        $errors = $validator->errors();
    }
}
$form = new Form($post, $errors);
?>

<!-- Message si modification réussie -->
<?php if ($success): ?>
    <div class="alert alert-success">
        L'article a bien été modifiée
    </div>
<?php endif ?>

<!-- Message si article a bien été crée -->
<?php if (isset($_GET['created'])): ?>
    <div class="alert alert-success">
        L'article a bien été crée
    </div>
<?php endif ?>

<!-- Message si erreur lors de la modification -->
<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        L'article n'a pas pu être modifié, merci de corriger vos erreurs
    </div>
<?php endif ?>

<h1>Editer l'article <?= e($post->getName()) ?></h1>

<?php require('_form.php'); ?>