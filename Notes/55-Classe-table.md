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

### Classe PostTable

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

9. On peut allèger encore plus le code en enlèvant les imports qu'on a plus besoin.

### Classe CategoryTable

1. On crée la classe CategoryTable qui aura le même construct de PostTable.

    Pour éviter de répéter le code, on va créer une classe parent qui va être implementé par ces deux classes.

    ```
    <?php
    namespace App\Table;

    class Table {

        protected $pdo;

        public function __construct(\PDO $pdo)
        {
            $this->pdo = $pdo;
        }
    }
    ```

2. On aura qu'à faire un extends de cette classe dans les classe PostTable et CategoryTable.

    ```
    class PostTable extends Table {
        ...
    }
    ```

3. On récupère lde code du fichier **views/category/show.php** :

    ```
    <?php
    namespace App\Table;

    use App\Model\Category;
    use PDO;

    class CategoryTable extends Table {

       public function find(int $id)
       {
            // Comme on va recevoir des paramètres envoyés par l'utilisateur on fait une rquête préparé
            $query = $this->pdo->prepare('SELECT * FROM category WHERE id = :id');
            // On précise que l'id correspondra à l'id envoyé par l'utilisateur
            $query->execute(['id' => $id]);
            $query->setFetchMode(PDO::FETCH_CLASS, Category::class);
            /**
             * On peut typer cette variable comme suit :
             * @var Category|false
             */
            $category = $query->fetch();
            // dd($category);
        }
    }
    ```

4. On peut préciser le typage directement dans la signature de la fonction, on efface donc le docBlock au milieu du code et on retourne le fetch().

    ```
    class CategoryTable extends Table {

       public function find(int $id): ?Category
       {
            $query = $this->pdo->prepare('SELECT * FROM category WHERE id = :id');
            
            $query->setFetchMode(PDO::FETCH_CLASS, Category::class);

            return $query->fetch();
        }
    }
    ```

5. On peut maitenant instancier la classe dans *show.php* et appeler la méthode find().

    ```
    $categoryTable = new CategoryTable($pdo);
    $category = $categoryTable->find($id);
    ```

    Si on teste on est censé avoir le même résultat, moi comme j'avais l'erreur depuis le chapitre précédent ça ne marche pas à tous les coups.

6. Quand on tape un id qui n'existe pas il nous envoie une erreur nous disant qu'on attendait comme valeur de retour une instance de Category, mais un bool était retourné.

    Pour rappel quand fetch ne trouve pas un résultat il retourne false, alors il faut créer une condition pour qu'il nous renvoi null.

    On change un peu la méthode find() :

    ```
    public function find(int $id): Category
    {
        // Comme on va recevoir des paramètres envoyés par l'utilisateur on fait une rquête préparé
        $query = $this->pdo->prepare('SELECT * FROM category WHERE id = :id');

        // On précise que l'id correspondra à l'id envoyé par l'utilisateur
        $query->execute(['id' => $id]);

        $query->setFetchMode(PDO::FETCH_CLASS, Category::class);

        $result = $query->fetch();

        if ($result === false) {
            ... à coder
        }
        return $result;
    }
    ```

7. Comme on aura plusieurs messages d'exceptions on va créer une autre classe qui va les gérer : **src/Table/Exceptions/NotfoundException.php**.

    ```
    <?php
    namespace App\Table\Exception;

    class NotFoundException extends \Exception {

        public function __construct(string $table, int $id)
        {
            $this->message = "Aucun enregistrement ne correspond à l'id #$id dans la table '$table'";
        }
    }
    ```
8. On a qu'à appeler cette exception dans la condition :

    ```
    if ($result === false) {
        throw new NotFoundException('category', $id);
    }
    ```

9. Dans show.php on peut effacer la suite du code qui vérifiait si la catégorie demandée n'existait pas.

    Code éffacé : 

    ```
    if ($category === false) {
        throw new Exception('Aucune catégorie ne correspond à cet ID');
    }
    ```

    On teste et on a bien le message crée dans la classe qui apparaît.

### Affichage des catégories 

En peut plus bas dans le code on avait la requête qui s'occupait de récupérer les catégories pour un article donnée. La requête ressemble à *findPaginated()*, on mettre $categories comme paramètre de cette méthode en l'initialisant comme null, mais ça deviendrait compliqué à gérer, alors on va créer une méthode spécifique pour cette requête.

1. On crée la méthode **findPaginatedForCategory** et on copie le code qui était dans *views/category/show.php* :
*
    ```
    public function findPaginatedForCategory(int $categoryId) 
    {
        $paginatedQuery = new PaginatedQuery(
            "SELECT p.* 
                FROM post p 
                JOIN post_category pc ON pc.post_id = p.id
                WHERE pc.category_id = {$category->getId()}
                ORDER BY created_at DESC", 
            "SELECT COUNT(category_id) FROM post_category WHERE category_id = {$category->getId()}"
        );
        /** @var Post[] */
        $posts = $paginatedQuery->getItems(Post::class);
        // dd($posts);
        
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
           
        // dump($posts);
    }
    ```

2. On remplace l'appel à l'id de la catégorie *($category->getId())* par la variable passée en paramètre ($categoryId).

    ```
    $paginatedQuery = new PaginatedQuery(
        "SELECT p.* 
            FROM post p 
            JOIN post_category pc ON pc.post_id = p.id
            WHERE pc.category_id = {$categoryId}
            ORDER BY created_at DESC", 
        "SELECT COUNT(category_id) FROM post_category WHEREcategory_id = {$categoryId}"
    );
    ```

3. On va créer une nouvelle méthode pour la partie qui se répéte dans les deux codes à partir de $postsById. Comme cette requête concerne les catégories on va la mettre dans la classe **CategoryTable**.

    ```
    /**
     * @param App\Model\Post[] $posts
     */
    public function hydratePosts(array $posts): void
    {
         // On récupère l'id de chaque article
         $postsById = [];
         foreach ($posts as $post) {
             // On passe l'id du post comme index du tableau $postsById
             // et la valeur de cet index sera le post lui même
             $postsById[$post->getId()] = $post;
         }
         // dd(array_keys($postsById));
 
         $categories = $this->pdo
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

4. Il nous suffit maintnant de faire appel à cette méthode dans les méthodes qui en ont besoin : findpaginated et findPaginatedForCategory.

    On ajoute ce code à la palce de l'ancien :

    ```
    // On appelle la méthode hydratePosts()
    (new CategoryTable($this->pdo))->hydratePosts($posts);
    ```
    
5. La méthode est complète, on peut maintenant l'appeler dans show.php.

    ```
    [$posts, $paginatedQuery] = (new PostTable($pdo))->findPaginatedForCategory($category->getId());
    ```

6. On teste et ça marche!

### Factorisation du code qui affiche un article

1. Dans **views/post/show.php** on a une requête qui ne récupère qu'un post, c'est le même cas qu'on vait traité pour les catégories. 

    On va donc, récupèrer le code find() et l'adapter.

    ```
    public function find(int $id): Post
    {
        $query = $this->pdo->prepare('SELECT * FROM post WHERE id = :id');
        
        $query->execute(['id' => $id]);

        $query->setFetchMode(PDO::FETCH_CLASS, Post::class);

        $result = $query->fetch();

        if ($result === false) {
            throw new NotFoundException('post', $id);
        }
        return $result;
    }
    ```

2. Dans *show.php* on a qu'à appeler cette méthode :

    ```
    $post = (new PostTable($pdo))->find($id);
    ```

3. Plus bas dans le fichier on affiche les catégories pour ce post, alors on n'a qu'à appeler la méthode *hydratePosts*.

    ```
    (new CategoryTable($pdo))->hydratePosts([$post]);
    ```

4. Maintenant que l'on a changé le code il faut changer l'appel dans le code html.

    ```
    <?php foreach ($post->getCategories() as $key => $category): 
    ```

5. On teste et ça marche toujours.
 



    









