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






