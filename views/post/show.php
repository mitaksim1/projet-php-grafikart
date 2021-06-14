<?php

use App\Connection;
use App\Model\Post;

$id = (int)$params['id'];
$slug = $params['slug'];

$pdo = Connection::getPDO();
// Comme on va recevoir des paramètres envoyés par l'utilisateur on fait une rquête préparé
$query = $pdo->prepare('SELECT * FROM post WHERE id = :id');
// On précise que l'id correspondra à l'id envoyé par l'utilisateur
$query->execute(['id' => $id]);
$query->setFetchMode(PDO::FETCH_CLASS, Post::class);
$post = $query->fetch();
// dd($post);

if ($post === false) {
    throw new Exception('Aucun article ne correspond à cet ID');
}

