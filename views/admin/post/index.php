<?php
use App\Connection;
use App\Table\PostTable;

$title = 'Administration';

$pdo = Connection::getPDO();

$table = new PostTable($pdo);
[$posts, $pagination] = $table->findPaginated();

$link = $router->url('admin_posts');
?>
<h1>Admin</h1>

<table class="table">
  <thead>
    <tr>
        <th>#id</th>
        <th scope="col">Titre</th>
        <th scope="col">Actions</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($posts as $post) : ?>
    <tr>
        <td>#<?= $post->getId() ?></td>
        <td scope="row">
            <a href="<?= $router->url('admin_post', ['id' => $post->getId()]) ?>">
            <?= e($post->getName()) ?>
            </a>
    </td>
      <td scope="row">
        <a href="<?= $router->url('admin_post', ['id' => $post->getId()]) ?>" class="btn btn-primary">
        Editer
        </a>
        <a href="<?= $router->url('admin_post_delete', ['id' => $post->getId()]) ?>" class="btn btn-danger"
        onclick="return confirm('Voulez vous vraiment effectuer cette action ?')")>
        Supprimer
        </a>
    </td>
    </tr>
    <?php endforeach ?>
    </tbody>
</table>

<div class="d-flex justify-content-between my-4">
    <?= $pagination->previousLink($link) ?>
    <?= $pagination->nextLink($link) ?>
</div>
