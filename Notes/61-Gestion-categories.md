## ahpitre 61 - Gestion des catégories

A la fin de la vidéo précédente on avait comme challenge créer la même chose que l'on avait fait pour les articles, mais pour les catégories et ajouter au header un lien pour les article et un autre pour les catégories.

On va commencer par créer un noveau template pour l'admin de la partie articles et le lien sur le header.

1. Dans le dossier **views/admin/** on va créer un nouveau dossier **layouts** où on va créer le fichier default.php qui va contenir la même chose que le fichier default.php que l'on avait crée avant plus le lien qui va nous méner à la page des articles.

2. Dans la méthode *run()* dans **Router.php**, on va ajouter la variable $layout qui va contenir la condition pour savoir quel des deux layouts on doit afficher :

    ```
    $isAdmin = strpos($view, 'admin/') !== false;
    $layout = $isAdmin ? 'admin/layouts/default' : 'layout/default';
    ```

    On n'oublie pas de changer l'appel à $layout dans le *require* :

    ```
    require $this->viewPath . DIRECTORY_SEPARATOR . $layout . '.php';
    ```

3. On teste et ça marche.

### La partie Catégories

1. Dans **views/admin**, on va dupliquer le dossier **post**, parce qu'on veut les mêmes fonctionnalités.

    On renomme le dossier **category**.

2. Dans **src/Model** on avait déjà crée la classe **Category.php**.

    On va créer les setters qu'il manque.

3. Ensuite, on va créer un validator, parce qu'on aura besoin de valider les catégories.

    On fait simplement un C/C de PostValidator, on change juste le nom de la variable de $postId par $id pour que ce soit plus cohérent.

    ```
    <?php
    namespace App\Validators;

    use App\Table\CategoryTable;

    class CategoryValidator extends AbstractValidator {

        public function __construct(array $data, CategoryTable $table, ?int $id = null)
        {
            parent::__construct($data);
            // Valide l'existence du titre
            $this->validator->rule('required', ['name', 'slug']);
            // valide la longueur du titre
            $this->validator->rule('lengthBetween', ['name', 'slug'], 10, 200);
            // Valide le champs slug
            $this->validator->rule('slug', 'slug');
            // On crée notre propre validator
            $this->validator->rule(function ($field, $value) use ($table, $id) {
                return !$table->exists($field, $value, $id);
            }, ['slug', 'name'], 'Cette valeur est déjà utilisé'); 
        }
    }
    ```

### Routing

1. On commence par commenter nos routes pour mieux les organiser.

    Ensuite on C/C toute la partie des routes admin/post pour les routes des categories, on n'aura qu'à changer les chemins de **post** à **category**.

2. On va ajouter le lien dans la page.

    ```
    <li class="nav-item">
        <a href="<?= $router->url('admin_categories') ?>" class="nav-link">Categories</a>
    </li>
    ```

### Les différentes vues

1. On vérifie chaque fichier et on change les données de post à category où il faut.

    - **_form.php** : on a gardé le même code, on a juste enlevé les inputs pour created_at et pour le contenu.

    - **edit.php** : ici on a fait plus de changement, au lieu de renommer les variables avec le mot "category" on a donné un nom plus générique au cas on aurait   besoin de changer d'autres données dans le futur : $postTable -> $table et $post -> $item.

    On a ajouté aussi l'appel à la méthode Auth à ce fichier, mais aussi au fichers *edit* et *new* de **post**.

    - **index.php** : dans index.php de post on faisait appel à findPaginated(), cette méthode n'est pas disponible pour category, alors on va créer une autre méthode  dans la classe parent **Table.php** où on va récupérer toutes les informations de la table category.

    ```
    public function all()
    {
        $sql = "SELECT * FROM {$this->table}";
        return $this->pdo->query($sql, PDO::FETCH_CLASS, $this->class)->fetchAll();
    }
    ```

2. On peut maintenant changer l'appel à **findPaginated()** dans index.php à l'appel à la méthode ***all()**.

    ```
    $pdo = Connection::getPDO();
    $table = new CategoryTable($pdo);
    $items = $table->all();
    ```

3. On ajoute un titre pour le slug et l'url dans la partie HTML.

4. On change la route pour pouvoir créer une nouvelle catégorie :

    ```
    <a href="<?= $router->url('admin_category_new') ?>" class="btn btn-primary">Créer</a>
    ```

5. On change la boucle de $posts à $items et l'appel à chaque élément $item.

    On change aussi la route de *post* à *category*.

6. On enlève la partie qui concerne la pagination, parce que pour les catégories on en a pas besoin.

7. On change aussi toutes les données dans le fichier *category/new.php*.

### CategoryTable

On fait la même chose que l'on avait fait pour la classe PostTable, on va créer les méthodes **create**, **delete** et **update**.

1. On C/C ce qu'on avait fait dans PostTable pour nous inspirer.

2. Lors des changements on se rend compte que le code reste quand même identique, il y a juste une partie qui change selon si on utilise la table PostTable ou CategoryTable. On va donc, passer les méthodes delete et create à la classe Table parent.

    - **delete()** : on peut laisser cette méthode tel quelle est

    - **create()** : c'est presuqe la même chose, sauf que dans ce cas on a deux parties dynamiques, la partie de la requête qui correspondra aux champs et la partie qui va être exécutées qui correspondra aux variables.

    ```
    // $data correpondra aux champs dont on aura besoin pour créer une catégorie
    public function create(array $data): int
    {
        // Récupération des champs
        $sqlFields = [];
        foreach ($data as $key => $value) {
            // Correspond à : name = :name
            $sqlFields[] = "$key = :$key";
        }
        $query = $this->pdo->prepare("INSERT INTO {$this->table} SET " . implode(', ', $sqlFields));
        $queryExecuted = $query->execute($data);
        // Condition si requête bien exécutée
        if ($queryExecuted === false) {
            throw new \Exception("Impossible de créer l'enregistrement dans la table {$this->table}");
        }
        // Retourne l'id du dernier élément crée
        return (int)$this->pdo->lastInsertId();
    }
    ```

    - **update()** : ici c'est la même chose que pour le create, sauf qu'il faut ajouter le champs id pour savoir quel article on veut modifier et on ne retourne rien.

    ```
    public function update(array $data, int $id)
    {
        // Récupération des champs
        $sqlFields = [];
        foreach ($data as $key => $value) {
            // Correspond à : name = :name
            $sqlFields[] = "$key = :$key";
        }
        $query = $this->pdo->prepare("INSERT INTO {$this->table} SET " . implode(', ', $sqlFields) . " WHERE id = :id");
        // array_merge, on va rajouter la clé id au tableau $data
        $queryExecuted = $query->execute(array_merge($data, ['id' => $id]));
        // Condition si requête bien exécutée
        if ($queryExecuted === false) {
            throw new \Exception("Impossible de modifier l'enregistrement dans la table {$this->table}");
        }
    }
    ```

3. On va revenir dans nos fichier *new* et *edit* pour voir essayer d'utiliser es méthodes et voir si ça marche.

    - On fait les modifs nécésssaires dans les deux fichiers. Ici, exemple juste sur *edit.php*

    ```
    $fields = ['name', 'slug'];

    // Maintenant, il faut qu'on passe un tableau comme argument à la méthode update avec les champs que l'on veu changer
    // En deuxième paramètre, on prend l'id de l'article à modifier
    $table->update([
        'name' => $item->getName(),
        'slug' => $item->getSlug()
    ], $item->getId());
    ```

4. Si on teste, on a une erreur : *Declaration of App\Table\PostTable::create(App\Model\Post $post): void must be compatible with App\Table\Table::create(array $data): int*, parce que la méthode update de PostTable ne correspond pas à la méthode de son parent, on a besoin des informations précises, alors on va renommer cette méthode à **updatePost**.

    ```
    public function updatePost(Post $post): void 
    {
        $this->update([
            'id' => $post->getId(),
            'name' => $post->getName(),
            'slug' => $post->getSlug(),
            'content' => $post->getContent(),
            'created' => $post->getCreatedAt()->format('Y-m-d H:i:s')
        ], $post->getId());
    }
    ```

    Même si on a changé le nom de la méthode, on peut faire appel à la méthode de son parent comme ci-dessus.

5. On fait la même chose pour la méthode create.

    ```
    public function create(Post $post): void
    {
        $id = $this->createPost([
            'name' => $post->getName(),
            'slug' => $post->getSlug(),
            'content' => $post->getContent(),
            'created' => $post->getCreatedAt()->format('Y-m-d H:i:s')
        ]);
        $post->setId($id);
    }
    ```

6. Pour la méthode delete(), on peut juste la supprimer, parce que c'est la même.

7. Comme on a changé le nom des méthodes, dans les fichiers *edit* et *new* de post, il faut que change l'appel à ces méthodes là.

8. On re actualise la page, on fait tous les tests et ça marche.

9. On va dans la partie catégorie, on se retrouve bien avec la liste des 5 catégories, mais quand on essaie de modifier une catégorie on a l'erreur : *Undefined variable: category*. 

    On avait décidé de changer le nom de la variable par un nom plus générique, alors il faut changer l'appel à l'ancien nom partout où on en a besoin. Dans ce cas, on avait oublié de changer le nom dans le fichier *_form.pho*.

10. On teste toutes les fonctionnalitées et ça marche.

11. Juste pour savoir comment faire si on veut changer une requête d'une méthode parent, on va faire un exemple avec le changement d'ordre de récupérations des catégories de ASC à DESC.

    - On copie la méthode all() dans la classe enfant CategoryTable, et on change la requête comme ça nous convient.

    ```
    public function all()
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY id DESC";
        return $this->pdo->query($sql, PDO::FETCH_CLASS, $this->class)->fetchAll();
    }
    ```
12. Pour ne pas se répéter, on peut mettre le retour de la requête dans une méthode à part.

    Dans Table.php :

    ```
    public function queryAndFetchAll(string $sql): array
    {
        return $this->pdo->query($sql, PDO::FETCH_CLASS, $this->class)->fetchAll();
    }
    ```

    Dans CategoryTable :

    ```
    public function all()
    {
        return $this->queryAndFetchAll("SELECT * FROM {$this->table} ORDER BY id DESC");  
    }
    ```

13. On réactualise la page et la liste est bien decresente.

L'avantage de cette approche, c'est qui si dans le futur, on a besoin d'ajouter la gestion d'un nouveau contenu, il nous suffira d'ajouter une nouvelle table, une nouvelle vue et un nouveau fichier de validation (si besoin de valider des champs) sans avoir à changer le code des autres ficheirs.












