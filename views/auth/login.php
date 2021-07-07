<?php

use App\Connection;
use App\HTML\Form;
use App\Model\User;
use App\Table\Exceptions\NotFoundException;
use App\Table\UserTable;

$user = new User();
$errors = [];

if (!empty($_POST)) {
    // On sauvegarder la valeur saisie dans l'input username
    $user->setUsername($_POST['username']);
    // On défini ce message comme le message par défaut
    $errors['password'] = 'Identifiant ou mot de passe incorrect';

    if (!empty($_POST['username']) || !empty($_POST['password'])) {
        // Connexion à la bdd et aux méthodes de UserTable
        $pdo = Connection::getPDO();
        $table = new UserTable($pdo);
        // Maintenant on peut vérifier si l'user existe dans la bdd
        // On capture l'erreur au cas ou
        try {
            $userLogin = $table->findByUsername($_POST['username']);
            // Récupération du mot de passe de la bdd
            $userLogin->getPassword();
            // Récupération du password saisi par l'user
            $_POST['password'];
            // Vérification si les mots de passe correspondent
            if (password_verify($_POST['password'], $userLogin->getPassword()) === true) {
                // Start la session
                session_start();
                // Sauvegarde l'id de l'utilisateur dans la clé 'auth'
                $_SESSION['auth'] = $userLogin->getId();
                header('Location: ' . $router->url('admin_posts'));
                exit();
            };
        } catch (NotFoundException $e) {

        }     
    }     
}

$form = new Form($user, $errors);
?>

<h1>Se connecter</h1>

<?php if (isset($_GET['forbidden'])): ?>
<div class="alert alert-danger">
    Vous ne pouvez pas accèder à cette page
</div>
<?php endif ?>

<form action="<?= $router->url('login') ?>" method="POST">
    <?= $form->input('username', 'Nom d\'utilisateur'); ?>
    <?= $form->input('password', 'Mot de passe'); ?>
    <button type="submit" class="btn btn-primary">Se connecter</button>
</form>