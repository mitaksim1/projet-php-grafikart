<?php

use PDO;
use Exception;
use App\Connection;
use App\Model\{Post, Category};
use App\Table\CategoryTable;
use App\Table\PostTable;

$id = (int)$params['id'];
$slug = $params['slug'];

// Requête pour récupérer un article selon so ID
$pdo = Connection::getPDO();

// Récupères un article selon son id
$post = (new PostTable($pdo))->find($id);

// Requête qui va afficher les catégories pour un post donné
(new CategoryTable($pdo))->hydratePosts([$post]);

if ($post->getSlug() !== $slug) {
    // 'post' : nom donné lors de la création de la route
    $url = $router->url('post', ['slug' => $post->getSlug(), 'id' => $id]);
    // Code de redirection
    http_response_code(301);
    // Si pas la bonne url on redirige vers la bonne
    header('Location: ' . $url);
}

$title = $post->getName();
?>


<h1><?= e($title) ?></h1>
<p class="text-muted"><?= $post->getCreatedAt()->format('d F Y') ?></p>
<?php foreach ($post->getCategories() as $key => $category): 
    if ($key > 0): 
        echo ', ';
    endif;
    $category_url = $router->url('category', ['id' => $category->getId(), 'slug' => $category->getSlug()]);
    ?><a href="<?= $category_url ?>"><?= e($category->getName()) ?></a>
<?php endforeach ?>
<p><?= $post->getFormattedContent() ?></p>

   
