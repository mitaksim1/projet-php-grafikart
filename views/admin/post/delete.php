<?php

use App\Connection;
use App\Table\PostTable;

$pdo = Connection::getPDO();
$table = new PostTable($pdo);
// $table->delete($params['id']);
// On rajoute un paramètre à l'url pour afficher un message en cas de suppréssion d'un article réussi
header('Location: ' . $router->url('admin_posts') . '?delete=1');

?>

<h1>Suppression de <?= $params['id'] ?></h1>