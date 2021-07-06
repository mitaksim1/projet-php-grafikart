<?php

use App\Auth;
use App\HTML\Form;
use App\Connection;
use App\Model\Post;
use App\ObjectHelper;
use App\Table\PostTable;
use App\Validators\PostValidator;

Auth::check();

// Pour afficher un message si modification réussie
$errors = [];

// On va encore créer l'article, alors pour l'instant on ntancie juste la classe avec l'objet vide
$post = new Post();
// On précise que lors de la création d'un article la dadate sera la date du jour
$post->setCreatedAt(date('Y-m-d H:i:s'));

if (!empty($_POST)) {
    $pdo = Connection::getPDO();
    $postTable = new PostTable($pdo);

    // Validation des articles
    $validator = new PostValidator($_POST, $postTable, $post->getId());
    ObjectHelper::hydrate($post, $_POST, ['name', 'content', 'slug', 'created_at']);

    if ($validator->validate()) {
        $postTable->createPost($post);
        // Une fois l'article crée, on envoi vers la page
        header('Location: ' . $router->url('admin_post', ['id' => $post->getId()]) . '?created=1');
        exit();
    } else {
        $errors = $validator->errors();
    }
}
$form = new Form($post, $errors);
?>

<!-- Message si erreur lors de la création de l'article -->
<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        L'article n'a pas pu être enregistré, merci de corriger vos erreurs
    </div>
<?php endif ?>

<h1>Créer un article</h1>

<?php require('_form.php'); ?>

