<?php

use App\Auth;
use App\Connection;
use App\Table\CategoryTable;

Auth::check();

$pdo = Connection::getPDO();
$table = new CategoryTable($pdo);
// dd($params['id']);
$table->delete($params['id']);
// On rajoute un paramètre à l'url pour afficher un message en cas de suppréssion d'un article réussi
header('Location: ' . $router->url('admin_categories') . '?delete=1');

?>

<h1>Suppression de <?= $params['id'] ?></h1>