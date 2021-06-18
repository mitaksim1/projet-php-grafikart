<?php

use App\Connection;
use App\Table\PostTable;

$pdo = Connection::getPDO();
$table = new PostTable($pdo);
$table->delete($params['id']);
header('Location: ' . $router->url('admin_posts'));

?>

<h1>Suppression de <?= $params['id'] ?></h1>