## Chapitre 55 : Création de la classe Table

Dans ce chapitre on va essayer d'éviter la répétition des requêtes SQL.

L'idée serait de pouvoir appeler une nouvelle classe qui aurait des méthodes pour afficher ce dont on a besoin.

    Par exemple pour récupérer la pagination :

    ```
    $table = new PostTable();
    $table->findPaginated();
    ```

    Pour récupérer les catégories :

    ```
    $table = new CategoryTable();
    $table->findCategory();
    ```

1. On commence par créer la nouvelle classe **PostTable** dans un dossier que l'on va nommer **Table**.

2. On crée le *__construct* de cette table :

    ```
    private $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }
    ```

3. On crée la méthode **findPaginated()** où on vient coller tout le code concernant la pagination, la récupération des posts et l'hydratation des catégories que l'on avait codé dans **views/post/index.php**.

    ```
    public function findpaginated()
        {
            $paginatedQuery = new PaginatedQuery(
                // Premier requête liste les articles
                "SELECT * FROM post ORDER BY created_at DESC",
                // Deuxième requête récupères le nombre d'articles total
                "SELECT COUNT(id) FROM post",
                $this->pdo
            );

            // On récupère les articles
            $posts = $paginatedQuery->getItems(Post::class);

            // On hydrate les catégories
            // On récupère l'id de chaque article
            $postsById = [];
            foreach ($posts as $post) {
                // On passe l'id du post comme index du tableau $postsById
                // et la valeur de cet index sera le post lui même
                $postsById[$post->getId()] = $post;
            }
            // dd(array_keys($postsById));

            $categories = $pdo
                ->query('SELECT c.*, pc.post_id
                    FROM post_category pc
                    JOIN category c ON c.id = pc.category_id
                    WHERE pc.post_id IN (' . implode(',', array_keys($postsById)) . ')'
                )->fetchAll(PDO::FETCH_CLASS, Category::class);
            // dump($categories);

            // On parcourt les catégories
            foreach ($categories as $category) {
                // On trouve l'article $posts correspondant à la ligne
                // On ajoute la catégorie à l'article
                $postsById[$category->getPostId()]->addCategory($category);
            }
        }
    ```

4. Comme dans cette méthode on a plusieurs choses à retourner, on pourra tout mettre dans un tableau comme suit :

    ```
    return [$posts, $paginatedQuery];
    ```

5. On peut maintenant, instancier cette classe à la place de l'ancien code et faire appel à la méthode *findPaginated*.

    ```
    $table = new PostTable($pdo);
    $var = $table->findPaginated();
    $posts = $var[0];
    $pagination = $var[1];
    ```

6. On change le nom de la variable à appeler dans le code HTML.

    ```
    <div class="d-flex justify-content-between my-4">
        <?= $pagination->previousLink($link) ?>
        <?= $pagination->nextLink($link) ?>
    </div>
    ```

7. On teste et tout continue à marcher.

8. L'écriture : *$posts = $var[0]; $pagination = $var[1];* n'est pas très jolie, avec les nouvelles versions de PHP on a cette façon d'écrire qui est plus propre et prend moins de ligne.

    ```
    [$posts, $pagination] = $table->findPaginated();
    ```

    - $posts correspondra au premier élément du tableau et $pagination au deuxième

9. 







