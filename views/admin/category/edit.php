<?php

use App\Auth;
use App\Connection;
use App\Table\CategoryTable;
use App\HTML\Form;
use App\ObjectHelper;
use App\Validators\CategoryValidator;

Auth::check();

$pdo = Connection::getPDO();
$table = new CategoryTable($pdo);
$item = $table->find($params['id']);
// Pour afficher un message si modification réussie
$success = false;
$errors = [];
$fields = ['name', 'slug'];

if (!empty($_POST)) {

    // Validation des articles
    $validator = new CategoryValidator($_POST, $table, $item->getId());
    ObjectHelper::hydrate($item, $_POST, $fields);

    if ($validator->validate()) {
        // Maintenant, il faut qu'on passe un tableau comme argument à la méthode update avec les champs que l'on veu changer
        // En deuxième paramètre, on prend l'id de l'article à modifier
        $table->update([
            'name' => $item->getName(),
            'slug' => $item->getSlug()
        ], $item->getId());
        // Si pas d'erreur lors de la requête
        $success = true;
    } else {
        $errors = $validator->errors();
    }
}
$form = new Form($item, $errors);
?>

<!-- Message si modification réussie -->
<?php if ($success): ?>
    <div class="alert alert-success">
        La catégorie a bien été modifiée
    </div>
<?php endif ?>

<!-- Message si article a bien été crée -->
<?php if (isset($_GET['created'])): ?>
    <div class="alert alert-success">
        La catégorie a bien été crée
    </div>
<?php endif ?>

<!-- Message si erreur lors de la modification -->
<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        La catégorie n'a pas pu être modifié, merci de corriger vos erreurs
    </div>
<?php endif ?>

<h1>Editer la catégorie <?= e($item->getName()) ?></h1>

<?php require('_form.php'); ?>