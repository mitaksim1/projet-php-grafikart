<?php

use App\Connection;
use App\Table\CategoryTable;
use App\HTML\Form;
use App\Model\Category;
use App\ObjectHelper;
use App\Validators\CategoryValidator;

// Pour afficher un message si modification réussie
$errors = [];

// On va encore créer l'article, alors pour l'instant on ntancie juste la classe avec l'objet vide
$item = new Category();

if (!empty($_POST)) {
    $pdo = Connection::getPDO();
    $table = new CategoryTable($pdo);

    // Validation des articles
    $validator = new CategoryValidator($_POST, $table);
    ObjectHelper::hydrate($item, $_POST, ['name', 'slug']);

    if ($validator->validate()) {
        $table->create([
            'name' => $item->getName(),
            'slug' => $item->getSlug()
        ]);
        // Une fois l'article crée, on envoi vers la page
        header('Location: ' . $router->url('admin_categories') . '?created=1');
        exit();
    } else {
        $errors = $validator->errors();
    }
}
$form = new Form($item, $errors);
?>

<!-- Message si erreur lors de la création de l'article -->
<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        La catégorie n'a pas pu être enregistré, merci de corriger vos erreurs
    </div>
<?php endif ?>

<h1>Créer un catégorie</h1>

<?php require('_form.php'); ?>

