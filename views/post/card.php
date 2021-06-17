<div class="card mb-3">
    <div class="card-body">
        <h5 class="card-title"><?= htmlentities($post->getName()) ?></h5>
        <p class="text-muted"><?= $post->getCreatedAt()->format('d F Y') ?> ::
<?php foreach ($post->getCategories() as $key => $category): 
    if ($key > 0): 
        echo ', ';
    endif;
    $category_url = $router->url('category', ['id' => $category->getId(), 'slug' => $category->getSlug()]);
    ?><a href="<?= $category_url ?>"><?= e($category->getName()) ?></a>
<?php endforeach ?>
        </p>
        <p><?= $post->getExcerpt() ?></p>
        <p>
            <a href="<?= $router->url('post', ['id' => $post->getID(), 'slug' => $post->getSlug()]) ?>" class="btn btn-primary">Voir plus</a>
        </p>
    </div>
</div>