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
      <th scope="col">Titre</th>
      <th scope="col">Actions</th>
    </tr>
  </thead>
  <tbody>
    <tr>
    <?php foreach ($posts as $post) : ?>
      <td scope="row">
        <a href="<?= $router->url('admin_post', ['id' => $post->getId()]) ?>">
        <?= e($post->getName()) ?>
        </a>
    </td>
      <td scope="row">
        <a href="<?= $router->url('admin_post', ['id' => $post->getId()]) ?>" class="btn btn-primary">
        Editer
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
