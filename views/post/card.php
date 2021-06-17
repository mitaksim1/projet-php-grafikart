<?php
// On crée un tableau contenant la syntaxe html d'un lien
$categories = [];
// On boucle sur les catégories associés à l'article
foreach ($post->getCategories() as $category) {
    $url = $router->url('category', ['id' => $category->getId(), 'slug' => $category->getSlug()]);
    $categories[] = <<<HTML
    <a href="<?= {$url}">{$category->getName()}</a>
HTML;
}
// dd(implode(', ', $categories));

?>
<div class="card mb-3">
    <div class="card-body">
        <h5 class="card-title"><?= htmlentities($post->getName()) ?></h5>
        <p class="text-muted"><?= $post->getCreatedAt()->format('d F Y') ?> 
        <?php if (!empty($post->getCategories())): ?>
        ::
        <?= implode(', ', $categories) ?>
        <?php endif ?>

        </p>
        <p><?= $post->getExcerpt() ?></p>
        <p>
            <a href="<?= $router->url('post', ['id' => $post->getID(), 'slug' => $post->getSlug()]) ?>" class="btn btn-primary">Voir plus</a>
        </p>
    </div>
</div>