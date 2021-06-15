## Chapitre 53 : Réorganisation de la pagination

On va réorganiser le code qui s'occupe de la pagination de notre site, là il se répète deux fois dans deux fichiers différents et pour ne pas avoir besoin de changer ce même code dans plusieurs endroits différents on va créer une méthode qui va le faire à notre place.

Quand on crée une méthode dans une classe on a besoin des paramètres, pour savoir qu'est-ce qu'on mettra dedans il faut voir ce que différe dans chaque code. Dans notre cas, on voit que ce que change c'est : la requête SQL ($sqlListing), le nombre des pages selon les articles à afficher ($sqlCount), la façon de récupérer les données le nombre des articles par page ) chez nous ça ne change pas, mais on peut le mettre si au cas où on veuille le changer dans le futur), etc...

On a besoin aussi de vérifier les paramètres externes, ça veut dire les paramètres dont dépendent les quatres paramètres cités plus haut : $currentpage, $pdo (pour les requêtes SQL futures qui viendront d'autres bdd).

Maintenant, on pourra réfléchir aux méthodes dont on aura besoin. Pour les articles on pourrait avoir une méthode => getItems(): array, une autre méthode pour gérer les pages précédentes ou suivantes => previousPageLink(): ?string et nextpageLink(): ?string

On peut créer la signature pour les paramètres aussi : $pdo : PDO = Connection::getPDO(), $sqlListing: string, $classMapping: string, $sqlCount: string, $perPage: int = 12

Il nous suffit maintenant de créer nos classes.

1. On commence par la classe paginatedQuery :

    ```
    <?php
    namespace App;

    class PaginatedQuery {

        private $query;

        private $queryCount;

        private $classMapping;

        private $pdo;

        private $perPage;

        public function __construct(string $query, string $queryCount, string $classMapping, ?\PDO $pdo, int $perPage = 12)
        {
            $this->query = $query;
            $this->queryCount = $queryCount;
            $this->classMapping = $classMapping;
            // Si PDO n'est pas définit on prend la connexion globale de l'appli
            $this->pdo = $pdo ?: Connection::getPDO();
            $this->perPage = $perPage;
        }
    }
    ```

2. On peut maintenant, appeler cette classe, en lui passant les paramètres demandés.

    ```
    $paginatedQuery = new PaginatedQuery(
    "SELECT p.* 
        FROM post p 
        JOIN post_category pc ON pc.post_id = p.id
        WHERE pc.category_id = {$category->getId()}
        ORDER BY created_at DESC", 
    "SELECT COUNT(category_id) FROM post_category WHERE category_id = . {$category->getId()}",
    Post::class
    );
    ```

    - On a enlèvée la dernière partie de la requête, perce qu'apparement il va gérer ça automatiquement après.

3. On va créer notre première méthode **getItems()**.

    ```
    public function getItems(): array
    {
        // On a besoin de la page courante
        $currentPage = URL::getPositiveInt('page', 1);

        // On a besoin de compter les résultats
        // Récupère le nombre des articles pour la catégorie donnée
        $count = (int)$pdo
        ->query('SELECT COUNT(category_id) FROM post_category WHERE category_id = ' . $category->getId())
        ->fetch(PDO::FETCH_NUM)[0];

        if ($currentPage > $pages) {
            throw new Exception('Cette page n\'existe pas');
        }

        // On calcule le offset par page
        $offset = $perPage * ($currentPage -1);

        // On récupére les articles les plus récents
        $query = $pdo->query("
            SELECT p.* 
            FROM post p 
            JOIN post_category pc ON pc.post_id = p.id
            WHERE pc.category_id = {$category->getId()}
            ORDER BY created_at 
            DESC LIMIT $perPage 
            OFFSET $offset
        ");

        $posts = $query->fetchAll(PDO::FETCH_CLASS, Post::class);
    }
    ```

    Il faut faire quelques changements dans ce code :

    ```
    public function getItems(): array
    {
        // 1. On a besoin de la page courante
        $currentPage = URL::getPositiveInt('page', 1);

        // 2. On a besoin de compter les résultats
        // Récupère le nombre des articles pour la catégorie donnée
        $count = (int)$this->pdo
            //->query('SELECT COUNT(category_id) FROM post_category WHERE category_id = ' . $category->getId())
            ->query($this->queryCount)
            ->fetch(PDO::FETCH_NUM)[0];

        // 3. Calcule le nombre d'articles qu'on mettra par page
        $pages = ceil($count / $this->perPage);
        // dd($pages);  
        
        // 4. Envoi une exception si la page n'existe pas
        if ($currentPage > $pages) {
            throw new Exception('Cette page n\'existe pas');
        }
        // 5. On calcule le offset par page
        $offset = $this->perPage * ($currentPage -1);

        // On récupére les articles les plus récents
        $query = $this->pdo->query($this->query .
            " LIMIT {$this->perPage} 
            OFFSET $offset
        ")
        ->fetchAll(PDO::FETCH_CLASS, $this->classMapping);
    }   
    ```

4. On efface l'ancien code dans **views/category/show.php** et on appelle la méthode getItems() à la place.

    ```
    $posts = $paginatedQuery->getItems();
    dd($posts);
    ```

    On a fait un dump pour debuger et on reçoit bien les nombre d'articles convenu par page.

5. On va générer les liens vers les pages précédentes/suivantes.





