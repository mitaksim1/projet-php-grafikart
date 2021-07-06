<?php
use App\Auth;
use App\Connection;
use App\Table\CategoryTable;

Auth::check();

$title = 'Gestion des catégories';

$pdo = Connection::getPDO();
$table = new CategoryTable($pdo);
$items = $table->all();

$link = $router->url('admin_posts');
?>
<h1>Admin</h1>

<!-- Message à envoyer en cas d'article bien supprimé -->
<?php if (isset($_GET['delete'])): ?>
    <div class="alert alert-success">
        L'article a bien été supprimé
    </div>
<?php endif ?>

<table class="table">
  <thead>
    <tr>
        <th>#id</th>
        <th>Slug</th>
        <th>URL</th>
        <th scope="col">Titre</th>
        <th>
            <a href="<?= $router->url('admin_category_new') ?>" class="btn btn-primary">Créer</a>
        </th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($items as $item) : ?>
    <tr>
        <td>#<?= $item->getId() ?></td>
        <td scope="row">
            <a href="<?= $router->url('admin_category', ['id' => $item->getId()]) ?>">
            <?= e($item->getName()) ?>
            </a>
        </td>
        <td><?= $item->getSlug() ?></td>
        <td scope="row">
        <a href="<?= $router->url('admin_category', ['id' => $item->getId()]) ?>" class="btn btn-primary">
        Editer
        </a>
        <form action="<?= $router->url('admin_category_delete', ['id' => $item->getId()]) ?>" method="POST" 
            onsubmit="return confirm('Voulez vous vraiment effectuer cette action ?')") style="display:inline">
            <button type="submit" class="btn btn-danger">Supprimer</button>
        </form>
    </td>
    </tr>
    <?php endforeach ?>
    </tbody>
</table>

