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

5. On va créer la méthode qui va gérer les liens vers les pages précédentes/suivantes.

    ```
    public function previousLink(): ?string 
    {

    }
    ```

6. A l'intérieur de cette méthode on va avoir besoin de la page courante, on a pas accès à la variable $currentpage de getItems(), soit on crée une propriété qui va stocker cette information pour nous soit on va créer une autre méthode. Nous on va choisir la deuxième option.

    ```
    public function getCurrentPage(): int
    {
        // 1. On a besoin de la page courante
        $currentPage = URL::getPositiveInt('page', 1);
    }
    ```

7. Maintenant dans **getItems()** on n'a qu'à faire appel à cette méthode :

    ```
    $currentPage = $this->getCurrentPage();
    ```

8. On fait la même chose pour la méthode **previousLink()**.

    ```
    public function previousLink(string $link): ?string 
    {
        $currentPage = $this->getCurrentPage();
        if ($currentPage <= 1) return null;
        if ($currentPage > 2) $link .= "?page=" . ($currentPage - 1);
        return <<<HTML
            <a href="{$link}" class="btn btn-primary">&laquo; Page précédente</a>
HTML;
    }
    ```

9. Quand j'ai voulu tester pour voir si ça marche, j'ai du resémarrer l'ordi à cause d'un bug de la machine virtuelle, alor j'a perdu la page où j'étais et c'est là que je me suis rendu compre qu'il y avait une erreur au moment d'accèder à un article, le message disait que la variable $tite était undefined. 

    Le soucis c'était au niveau du fichier **views/post/show.php**, en fait, on n'avait pas déclarée la variable $title ligne 57 : 

    On avait codé ça :

    ```
    <h1><?= e($post->getName()) ?></h1>
    ```

    En fait, il fallait que l'on déclare la variable comme suit, pour après l'appeler.

    ```
    $title = $post->getName();
    ```

    ```
    <h1><?= e($title) ?></h1>
    ```

10. Là, je teste et ça a remarché, ouf! Par contre, comme on a effacé le code pour la page suivante, j'ai du taper *?page=2* sur l'adresse url pour voir si le lien s'affichait bien.

11. On va faire la même chose pour la page suivante en créant la méthode **nextLink()**.

    ```
    public function nextLink(string $link): ?string
    {
        $currentPage = $this->getCurrentPage();
        // On a besoin de savoir le nombre de pages qui existente
        $pages = $this->getPages();
        // Si la page où on est est supérieur au nombre de pages total, on n'a besoin de rien faire
        if ($currentPage >= $pages) return null;
        $link .= "?page=" . ($currentPage + 1);
        return <<<HTML
            <a href="{$link}" class="btn btn-primary ml-auto">Page suivante &raquo;</a>
HTML; 
    }
    ```

12. Comme on avait besoin de savoir combien de pages on a pour savoir s'il faut mettre le lien où pas, on a enlève le code de getItems() pour le mettre dans une autre méthode **getPages()** :

    ```
    private function getPages(): int
    { 
        if ($this->count === null) {
            // Récupère le nombre des articles pour la catégorie donnée
            $this->count = (int)$this->pdo
            //->query('SELECT COUNT(category_id) FROM post_category WHERE category_id = ' . $category->getId())
            ->query($this->queryCount)
            ->fetch(PDO::FETCH_NUM)[0];
        }
        
        // Calcule le nombre de pages que l'on aura
        $pages = ceil($this->count / $this->perPage);
        // dd($pages);  

        return $pages;
    }
    ```

    - Comme cette méthode va être appelé dans deux méthodes différentes (getItems et nextLink) et pour que la connexion à PDO ne se fasse qu'une seule fois on va créer une condition pour qu'elle ne soit faite qu'une seule fois.






