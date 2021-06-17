## Chapitre 54 : Affichage des catégories sur le listing

Dans ce chapitre on veut afficher la liste des catégories dans le listing des articles

1. Dans le fichier **views/post/show.php** on vient récupérer la requête qui récupères les catégories pour un article donné et on va le coller dans la vue **card.php**.

    Pour l'instant on ne fait qu'un *dump($categories)* et on retrouve bien sur chaque card le tableau avec les catégories de chaque article. Ca c'set possible, parce que dans *show.php* on fait une boucle pour afficher les articles sur le tableau $posts.

    ```
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
    // dump($categories);
    ```

2. Le soucis de cette approche c'est qu'à chaque fois que l'on charge une carte, on fait une requête SQL, alors ce n'est pas très performant.

    On va éviter si possible de faire une requete SQL dans une boucle.

    Le mec nous a montré d'autres façon de faire, regarder la vidéo vers 5 min pour plus de détails, je vais me concentrer sur la solution finale.

3. La première chose que l'on devra faire c'est récupérer l'id des articles.

    Dans **views/post/index.php**: 

    ```
    $ids = [];
    foreach ($posts as $post) {
        $ids[] = $post->getId();
    }
    ```

4. Maintenant qu'on a cet id on peut générer la deuxième requête.

    ```
    $pdo->query('SELECT c.*, pc.post_id
    FROM post_category pc
    JOIN category c ON c.id = pc.category_id
    WHERE pc.post_id IN (' . implode(',', $ids) . ')
    ');
    ```

    - On prend toutes les informations de la table *category*, on prend la clé étrangère *post_id* de la table post_category

    - Pour que ce soit possible de prendre les données de *category* on doit faire une jointure sur cette table que l'on renomme c, selon l'id passé qui devra correspondre à l'id de la clé étrangère category_id

    - On ne va prendre les données pour les id's de l'article qui correspondra aux tableaux d'ids passées.

5. Cete requête va nous retourner un PDO statement où on pourra appeler la méthode fetchAll.

    ```
    $pdo
        ->query('SELECT c.*, pc.post_id
            FROM post_category pc
            JOIN category c ON c.id = pc.category_id
            WHERE pc.post_id IN (' . implode(',', $ids) . ')'
        )->fetchAll(PDO::FETCH_CLASS, Category::class);
    ```

6. On sauvegarde le résultat de la requête dans la variable $categories et on fait un dd($categories);

    On reçoit bien une liste avec toutes le catégories (40 au total) et pour chaque catégorie l'id du post auquelle elle correspond.

7. On va ajouter la propriété **post_id** à la classe Category.

    ```
    private $post_id;

    public function getPostId(): ?int
    {
        return $this->post_id;
    }
    ```

8. On peut maintenant faire la liaison entre le tableau des catégories et le tableau des articles.

    Le pseudo-code de ce qu'on devra faire :

    ```
    // On parcourt les catégories
    // On trouve l'article $posts correspondant à la ligne
        // On ajoute la catégorie à l'article
    ```

9. Pour simplifier la tâche on va changer un peu le code. au lieu de récupérer juste l'id du post, on va récupérer l'id et le post au même temps :

    ```
    $postsById = [];
    foreach ($posts as $post) {
        // On passe l'id du post comme index du tableau $postsById
        // et la valeur de cet index sera le post lui même
        $postsById[$post->getId()] = $post;
    }
    dd($postsById);
    ```
10. Il va falloir changer le code dans l'implode qui faisait appel à la variable $ids.

    Pour récupérer l'id du post comme avant on peut utiliser la fonction **array_keys** qui nous permet de récupérer la clé d'un tableau.

    ```
    dd(array_keys($postsById));
    ```

    ```
    $categories = $pdo
        ->query('SELECT c.*, pc.post_id
            FROM post_category pc
            JOIN category c ON c.id = pc.category_id
            WHERE pc.post_id IN (' . implode(',', array_keys($postsById)) . ')'
        )->fetchAll(PDO::FETCH_CLASS, Category::class);
    dd($categories);
    ```

11. On suit les étapes du pseudo-code :

    ```
    foreach ($categories as $category) {
        // On trouve l'article $posts correspondant à la ligne
        $postsById[$category->getPostId()]->addCategory($category);
    }
    ``` 

    - On commence par la boucle sur le tableau des catégories.

    - Ensuite on récupère le post_id qui correspond à cette catégorie.

    - Dans la classe Post on va créer la méthode **addCategory** : 
    
    ```
    public function addCategory(Category $category): void
    {
        $this->categories[] = $category;
        // Pour cette catégorie on va appeler la méthode setPost() et sauvegarder l'article associé
        $category->setPost($this);
    }
    ```

    - Dans la classe Category on va créer la propriété Post et ensuite la méthode **setPost**.

    ```
    public function setPost(Post $post) 
    {
        return $this->post = $post;
    }
    ```

    - Après toutes ces étapes on pourra ajouter la catégorie au post grâce à la méthode addCategory().

12. On fait un dump($posts) et on voit que la clé categories a bien un array avec toutes les catégories pour un post donné.

13. On va ajouter un getter pou récupèrer les catégories et ainsi pouvoir les ajouter dans les cartes dans le code html.

    ```
    /**
     * @return Category[]
     */
    public function getCategories(): array
    {
        return $this->categories;
    }
    ```

    - Le docBlock permet à l'éditeur d'auto compléter les choses, ici on précise que la valeur de retour de cette méthode est le tableau de Category

14. Dans le fichier **views/post/show.php** on avait fait une boucle sur le tableau des catégories, il nous suffit de copier ce code et le coller dans **card.php**.

    ```
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
    ```
15. On teste et tous les articles ont bien les catégories juste après la date.
