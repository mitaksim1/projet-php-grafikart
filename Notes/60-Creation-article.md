## Chapitre 60 - Création d'un article

Dans ce chapitre on va corriger les challenges laissés à la fin du chapitre précédent.

### Correction challenge 1

1. On commence para créer la classe **ObjectHelper.php** directement à la racine du sossier **src**.

    ```
    <?php
    namespace App;

    class ObjectHelper {

        
    }
    ````

2. On crée la méthode **hydrate** avec les paramètres demandées dans l'ennoncé.

    On C/C le code que l'on veut remplacer :

    ```
    <?php
    namespace App;

    class ObjectHelper {

        public function hydratr($object, array $data, array $fields) 
        {
            $post
            ->setName($_POST['name'])
            ->setContent($_POST['content'])
            ->setSlug($_POST['slug'])
            ->setCreatedAt($_POST['created_at']);
        }
    }
    ```

3. On peut se servir de la même logique que l'on avait utilisé dans le Form.php pour renommer les setters en camelCase.

    ```
    $method = 'set' . str_replace(' ', '', ucwords(str_replace('_', ' ', $field)));
    ```

    - **'set'** : Dans le Form on appelé les getters et ici on appelle les setters, alors ne pas oublier de changer cette partie.

4. On veut pouvoir remplacer les champs passées en paramètre, alors on va boucler sur les champs et pour chaque champs on va les renommer en suivant la méthode que l'on avait crée. 

    A la fin on prend l'objet (l'article), on lui passe la méthode pour changer les champs et comme clé on lui passé le champs lui même.

    ```
    public static function hydrate($object, array $data, array $fields): void
    {
        foreach ($fields as $field) {
            $method = 'set' . str_replace(' ', '', ucwords(str_replace('_', ' ', $field)));
            $object->$method($data[$field]);
        }
    }
    ```

### Correction challenge 2

1. La vue new.php avait déjà été crée dans le dossier *admn/post*.

2. On C/C tout le code de edit.php où on va faire les modifs nécéssaires.

    ```
    <?php

    use App\Connection;
    use App\Table\PostTable;
    use App\Validator;
    use App\HTML\Form;
    use App\ObjectHelper;
    use App\Validators\PostValidator;

    // Pour afficher un message si modification réussie
    $success = false;
    $errors = [];

    // On va encore créer l'article, alors pour l'instant on ntancie juste la classe avec l'objet vide
    $post = new Post();

    if (!empty($_POST)) {
        $pdo = Connection::getPDO();
        $postTable = new PostTable($pdo);
        // On change la langue
        Validator::lang('fr');

        // Validation des articles
        $validator = new PostValidator($_POST, $postTable, $post->getId());
        ObjectHelper::hydrate($post, $_POST, ['name', 'content', 'slug', 'created_at']);

        if ($validator->validate()) {
            $postTable->update($post);
            // Si pas d'erreur lors de la requête
            $success = true;
        } else {
            $errors = $validator->errors();
        }
    }

    $form = new Form($post, $errors);

    ?>

    <!-- Message si création de l'aricle réussie -->

    <?php if ($success): ?>
        <div class="alert alert-success">
            L'article a bien été enregistré
        </div>
    <?php endif ?>

    <!-- Message si erreur lors de la création de l'article -->
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            L'article n'a pas pu être enregistré, merci de corriger vos erreurs
        </div>
    <?php endif ?>

    <h1>Créer un article</h1>

    <?php require('_form.php'); ?>
    ```

3. La partie du formulaire va se répéter, alors, on va créer un fichier à part pour lui : **post/_form.php**

4. On change l'appel à ce formulaire dans le fichier *edit.php* aussi.

5. On change l'appel à la route */admin/post/new* à match() parce qu'on veut pouvoir y accéder en get ou un post.

    ```
    ->match('/admin/post/new', 'admin/post/new', 'admin_post_new')
    ```

6. On teste et on a une erreur, parce que dans la méthode **getValue** on avait précisé qu'on attendait une string comme retour et la on retourne null (lors de la création d'un article l'input est vide), alors il faut rajouter le "?" à la méthode.

7. On va setter la date dès l'intanciation d'un objet Post.

    ```
    $post->setCreatedAt(date('Y-m-d H:i:s'));
    ```

8. On fait un teste en mettant les données, on met comme slug un slug qui existe déjà pour tester si le système des erreurs s'affiche et ça marche bien.

    Il faut juste qu'on corrige la méthode à appeler de update à create (qui l'on va encore créer).

    ```
    if ($validator->validate()) {
        $postTable->create($post);
        // Si pas d'erreur lors de la requête
        $success = true;
    } else {
        $errors = $validator->errors();
    }
    ```

9. On passe à la création de la méthode create(), on peut se baser sur la méthode update pour commencer.

    ```
    public function create(Post $post): void
    {
        $query = $this->pdp->prepare("INSERT INTO {$this->table} VALUES name = :name, slug = :slug, created_at = :created, content = :content");
        $queryExecuted = $query->execute([
            'name' => $post->getName(),
            'slug' => $post->getSlug(),
            'content' => $post->getContent(),
            'created' => $post->getCreatedAt()->format('Y-m-d H:i:s')
        ]);
        // Condition si requête bien exécutée
        if ($queryExecuted === false) {
            throw new \Exception("Impossible de créer l'enregistrement dans la table {$this->table}");
        }
        $post->setId($this->pdo->lastInsertId());
    }
    ```

    - Une fois l'article crée, on pourra dire à PDO que l'id de cette article sera le dernier inséré.

10. On n'avait pas crée le setter pour l'id, on va donc le créer.

    ```
    public function setId(int $id): self
    {
        $this->id = $id;
        
        return $this;
    }
    ```

11. Dans **new.php**, une fois l'article crée, on va rediriger l'utilisateur vers la page de l'article.

    ```
    if ($validator->validate()) {
        $postTable->create($post);

        // Une fois l'article crée, on envoi vers la page
        header('Location: ' . $router->url('admin_post', ['id' => $post->getId()]) . '?created=1');
        exit();

    } else {

        $errors = $validator->errors();
    }
    ```

12. On teste et ça marche on est bien redirigé vers : *http://localhost:8000/admin/post/51?created=1*.

    Le seul soucis c'est que sur le bouton est toujours marqué "Modifier", alors on va créer une condition dans le formulaire.

    ```
    <button class="btn btn-primary">
    <?php if ($post->getId() !== null): ?>
    Modifier
    <?php else: ?>
    Créer
    <?php endif ?>
    </button>
    ```

### Création du bouton "Créer" dans la page admin

1. Au lieu de mettre le titre "Actions", on va mettre un bouton à la place :

    ```
     <tr>
        <th>#id</th>
        <th scope="col">Titre</th>
        <th>
            <a href="<?= $router->url('admin_post_new') ?>" class="btn btn-primary">Créer</a>
        </th>
    </tr>
    ```

2. Dans *edit.php*, on va créer une condition pour que le message s'affiche si l'article a bien été crée.

    ```
    <?php if (isset($_GET['created'])): ?>
        <div class="alert alert-success">
            L'article a bien été crée
        </div>
    <?php endif ?>
    ```

3. On teste et ça marche!








