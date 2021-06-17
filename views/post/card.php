<?php
// Récupère toutes les catégories associées à l'article
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
dump($categories);
?>

<div class="card mb-3">
    <div class="card-body">
        <h5 class="card-title"><?= htmlentities($post->getName()) ?></h5>
        <p class="text-muted"><?= $post->getCreatedAt()->format('d F Y') ?></p>
        <p><?= $post->getExcerpt() ?></p>
        <p>
            <a href="<?= $router->url('post', ['id' => $post->getID(), 'slug' => $post->getSlug()]) ?>" class="btn btn-primary">Voir plus</a>
        </p>
    </div>
</div>