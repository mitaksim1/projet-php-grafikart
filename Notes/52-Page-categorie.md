## Chapitre 52 : Page catégorie

Dans ce chapitre on va lister dans la page d'une catégorie les articles qui en font partie.

La première chose à faire c'est de vérifier si on a bien une catégorie que correspond à l'url.

Notre page catégorie a été codé dans le fichier **views/category/show.php**, pour l'instant on n'avait laissé qu'un titre *Ma catégorie*.

1. Dans ce fichier, on vient coller la même requête que l'on avait fait pour afficher les articles.


    ```
    $id = (int)$params['id'];
    $slug = $params['slug'];

    $pdo = Connection::getPDO();
    
    $query = $pdo->prepare('SELECT * FROM post WHERE id = :id');
    
    $query->execute(['id' => $id]);
    $query->setFetchMode(PDO::FETCH_CLASS, Post::class);

    /**
     * On peut typer cette variable comme suit :
     * @var Post|false
     */
    $post = $query->fetch();

    if ($post === false) {
        throw new Exception('Aucun article ne correspond à cet ID');
    }

    if ($post->getSlug() !== $slug) {
       
        $url = $router->url('post', ['slug' => $post->getSlug(), 'id' => $id]);
        
        http_response_code(301);
        
        header('Location: ' . $url);
    }
    ```
    - On change la requête de post à category

    - On n'oublie pas d'importer les namespaces.

2. On teste et on reçoit biens les données pour la catégorie choisi.

3. On affiche le titre de la catégorie :

    ```
    <h1>Catégorie <?= e($category->getName()) ?></h1>
    ```

4. Comme on voit que l'onglet de la page ne contient pas de titre on le déclare :

    ```
    $title = "Catégorie {$category->getName()}";
    ```
5. Maintenant, on pourra appeler $title dans le *h1*.

    ```
    <h1><?= e($title) ?></h1>
    ```

6. On va échapper le titre des onglets tous le temps, alors dans **views/layouts/default.php** :

    ```
    <title><?= e($title) ?? 'Mon Site' ?></title>
    ```
### Affichage des articles pour une catégorie donnée

1. On récupère le code que l'on avait utilisé pour savoir combien d'articles il fallait mettre par page.

    ```
    $currentPage = URL::getPositiveInt('page', 1);

    // Calcule le nombre d'articles total dans la bdd
    $count = (int)$pdo->query('SELECT COUNT(id) FROM post')->fetch(PDO::FETCH_NUM)[0];

    // Calcule le nombre d'articles qu'on mettra par page
    $perPage = 12;
    $pages = ceil($count / $perPage);
    // dd($pages);

    if ($currentPage > $pages) {
        throw new Exception('Cette page n\'existe pas');
    }

    // On calcule le offset par page
    $offset = $perPage * ($currentPage -1);

    // On récupére les articles les plus récents
    $query = $pdo->query("SELECT * FROM post ORDER BY created_at DESC LIMIT $perPage OFFSET $offset");

    $posts = $query->fetchAll(PDO::FETCH_CLASS, Post::class);
    ```

1. Pour pouvoir afficher les articles selon sa ctégorie, il va falloir faire une jointure entre les deux tables.

    - Alors, on changer la requête :

    ```
    $count = (int)$pdo
    ->query('SELECT COUNT(id) FROM post_category WHERE category_id = ' . $category->getId())
    ->fetch(PDO::FETCH_NUM)[0];
    ```

    - On change cette partie aussi :

    ```
    $query = $pdo->query("
        SELECT p.* 
        FROM post p 
        JOIN post_category pc ON pc.post_id = p.id
        ORDER BY created_at 
        DESC LIMIT $perPage 
        OFFSET $offset
        WHERE pc.category_id = {$category->getId()}
    ");
    ```

2. On récupère le même code que l'on avait utilisé pour afficher les articles dans **views/post/index.php** :

    ```
    <div class="row">
        <?php foreach ($posts as $post) : ?>
            <div class="col-md-3">
                <?php require 'card.php' ?>
            </div>
        <?php endforeach ?>
    </div>

    <div class="d-flex justify-content-between my-4">
        <?php if ($currentPage > 1): ?>
            <?php
            $link = $router->url('home');
            if ($currentPage > 2) $link .= '?page=' . ($currentPage - 1);
            ?>
            <a href="<?= $link ?>" class="btn btn-primary">&laquo; Page précédente</a>
        <?php endif ?>
        <?php if ($currentPage < $pages): ?>
            <a href="<?= $router->url('home') ?>?page=<?= $currentPage + 1 ?>" class="btn btn-primary ml-auto"> Page suivante &raquo;</a>
        <?php endif ?>
    </div>
    ```

    - On change les données nécéssaires.

3. On crée la variable $link qui va contenir l'url à être envoyé quand clique sur le lien.

    ```
    $link = $router->url('category', ['id' => $category->getId(), 'slug' => $category->getSlug()]);
    ```

4. Si on teste on a l'erreur suivante : *Column not found: 1054 Unknown column 'id' in 'field list'*.

    Effectivement dans la table **post_category** on a pas de champs id, alors on remplace la requête par :

    ```
    ->query('SELECT COUNT(category_id) FROM post_category WHERE category_id = ' . $category->getId())
    ```

5. On re teste et on a une autre erreur : *Syntax error or access violation: 1064 You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near 'WHERE pc.category_id = 3' at line 7*.

    Ne pas oublier que les commandes SQL ont des priorités, il faut qu'on mette le WHERE avant le ORDER BY.

    ```
    $query = $pdo->query("
        SELECT p.* 
        FROM post p 
        JOIN post_category pc ON pc.post_id = p.id
        WHERE pc.category_id = {$category->getId()}
        ORDER BY created_at 
        DESC LIMIT $perPage 
        OFFSET $offset
    ");
    ```

6. On re teste et maintenant c'est la table Post qu'il ne trouve pas, il faut apporter aussi son namespace : 

    ```
    use App\Model\{Category, Post};
    ```

7. On re teste et maintenant il y aune erreur au niveau du require du fichier **card.php**.

    ```
    <div class="row">
        <?php foreach ($posts as $post) : ?>
            <div class="col-md-3">
                <?php require dirname(__DIR__) . '/post/card.php'?>
            </div>
        <?php endforeach ?>
    </div>
    ```

    **dirname(__DIR__)** : /home/mataks/Documents/Projet-PHP-Grafikart/views


    






