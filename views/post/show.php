<?php

use PDO;
use Exception;
use App\Connection;
use App\Model\{Post, Category};

$id = (int)$params['id'];
$slug = $params['slug'];

// Requête pour récupérer un article selon so ID
$pdo = Connection::getPDO();
// Comme on va recevoir des paramètres envoyés par l'utilisateur on fait une rquête préparé
$query = $pdo->prepare('SELECT * FROM post WHERE id = :id');
// On précise que l'id correspondra à l'id envoyé par l'utilisateur
$query->execute(['id' => $id]);
$query->setFetchMode(PDO::FETCH_CLASS, Post::class);
/**
 * On peut typer cette variable comme suit :
 * @var Post|false
 */
$post = $query->fetch();
// dd($post);

if ($post === false) {
    throw new Exception('Aucun article ne correspond à cet ID');
}

if ($post->getSlug() !== $slug) {
    // 'post' : nom donné lors de la création de la route
    $url = $router->url('post', ['slug' => $post->getSlug(), 'id' => $id]);
    // Code de redirection
    http_response_code(301);
    // Si pas la bonne url on redirige vers la bonne
    header('Location: ' . $url);
}

// Requête pour récupérer les catégories d'un article
$query = $pdo->prepare('
SELECT c.id, c.slug, c.name 
FROM post_category pc 
JOIN category c ON pc.category_id = c.id
WHERE pc.post_id = :id');
// L'id a exécuter sera l'id du post choisi
$query->execute(['id' => $post->getId()]);
$query->setFetchMode(PDO::FETCH_CLASS, Category::class);
// Pour aider le navigateur
/**
 * @var Category[]
 */
$categories = $query->fetchAll();
// dd($categories);
?>


<h1><?= e($post->getName()) ?></h1>
<p class="text-muted"><?= $post->getCreatedAt()->format('d F Y') ?></p>
<?php foreach ($categories as $key => $category): 
    if ($key > 0): 
        echo ', ';
    endif
    ?><a href="#"><?= e($category->getName()) ?></a>
<?php endforeach ?>
<p><?= $post->getFormattedContent() ?></p>

   
