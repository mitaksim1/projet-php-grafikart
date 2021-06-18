## Chapitre 56 : Administration du site

L'idée c'est que quand on tape */admin* on soit redirigé vers la page d'administration, cela permettra à une personne de gérer toutes les pages du site.

Cette page sera une page toute simple qui va lister les articles sous forme d'un tableau.

Sur une colonne on aura le titre de l'article et quand on cliquera dessus ça va nous diriger vers le formulaire d'addition.

Le mec nous a laissé cette partie comme challenge, j'ai réussi à aller jusqu'à la page form, mais je n'ai pas crée le formulaire.

Je continue avec la correction, avant de continuer il nous a montré comment faire l'indentation des fichiers dans les dossiers pour qu'il soit plus visible et on se retrouve mieux.

- Aller dans File->Preferences->Settings->Appearance->Workbench-> "chercher le titre Tree Indent, qui par défaut est à 8, nous on l'a mis à 15.

### Création nouvelle route pour la page admin et affichage des titres des articles dans un tableau

1. On commence par créer la nouvelle route dans **public/index.php**.

    ```
    ->get('/admin', 'admin/post/index', 'admin_posts')
    ```

2. On crée la vue qui correspond au nom qui l'on avait donnée : **views/admin/post/index.php**.

3. Les premières choses à faire dans ce fichier:

    - c'est de donner un titre à la page 
    
    - ensuite faire la connexion avec PDO

    - appeler la méthode findPaginated(), en récupérant les deux variables passées dans le tableau

    ```
    $title = 'Administration';

    $pdo = Connection::getPDO();

    $table = new PostTable($pdo);
    [$posts, $pagination] = $table->findPaginated();
    ```

4. On peut maintenant créer notre tableau, où on va faire une boucle pour afficher les titres des articles sur chaque ligne;

    ```
    <table class="table">
      <thead>
        <tr>
          <th scope="col">Titre</th>
          <th scope="col">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr>
        <?php foreach ($posts as $post) : ?>
          <th scope="row"><?= e($post->getName()) ?></th>
        </tr>
        <?php endforeach ?>
        </tbody>
    </table>
    ```

5. On a besoin du système de pagination, on va copier le code que l'on avait dans **views/post/index.php**.

    ```
    <div class="d-flex justify-content-between my-4">
        <?= $pagination->previousLink($link) ?>
        <?= $pagination->nextLink($link) ?>
    </div>
    ```

6. Il faut récupérer la variable $link aussi où on va passer le nom de la route pour admin.

    ```
    $link = $router->url('admin_posts');
    ```

### Création d'une nouvelle route pour créer et modifier un article

1. On commence par créer la route que va nous permettre de modifier un article selon son id.

    ```
    ->get('/admin/post/[i:id]', 'admin/post/edit', 'admin_post')
    ```

2. On crée aussi la route qui nous permettra de créer un nouvel article.

    ```
    ->get('/admin/post/new', 'admin/post/new', 'admin_post_new')
    ```

3. On va entourer les titres dans une balise "a" pour créer le lien qui va nous permettre d'éditer l'article.

    ```
    <?php foreach ($posts as $post) : ?>
      <td scope="row"><a href="<?= $router->url('admin_post', ['id' => $post->getId()]) ?>"><?= e($post->getName()) ?></a></td>
    </tr>
    <?php endforeach ?>
    ```

4. La route *admin_post* va nous mèner à la vue **admin/post/edit**, on va alors la créer.

    ```
    <h1>Editer l'article <?= $params['id'] ?></h1>
    ```

5. On profite pour créer aussi la vue **admin/post/new**.

    ```
    <h1>Créer un nouvel article</h1>
    ```

6. On va créer un bouton qui nous permettra d'éditer l'article, ce sera le même code de plus haut.

    ```
    <td scope="row">
        <a href="<?= $router->url('admin_post', ['id' => $post->getId()]) ?>" class="btn btn-primary">
        Editer
        </a>
    </td>
    ```

    - Pour indiquer que c'est un bouton on ajoute la classe *btn btn-primary*.

7. Il va nous falloir un bouton *Suppriler* aussi, pour l'instant on récupère le même code qui on viendra à changer après.

    ```
    <a href="<?= $router->url('admin_post', ['id' => $post->getId()]) ?>" class="btn btn-danger">
        Supprimer
    </a>
    ```

8. Si on veut envoyer un message de confirmation au cas où l'utilisateur clique sur le bouton *Supprimer** par erreur, on peut ajouter un peu de Javascript comme suit :

    ```
    <a href="<?= $router->url('admin_post', ['id' => $post->getId()]) ?>" class="btn btn-danger"
    onclick="return confirm('Voulez vous vraiment effectuer cette action ?')")>
        Supprimer
    </a>
    ```

    On teste et il y a bien l'alert qui apparaît nous demandant la confirmation. Si clique sur *Annuler* on reste sur la page et si on clique sur *Ok* on est redirigé vers la page qui nous permettra de supprimer l'article.

### Suppression d'un article

1. Bien sûr, qu'il va nous falloir une url différente. On va créer une url qui ressemble à celle pour modifier un article, sauf qu'on mettra un delete à la fin.

    ```
    ->get('/admin/post/[i:id]/delete', 'admin/post/delete', 'admin_post_delete')
    ```

2. On va créer la vue qui correspond.

    ```
    <h1>Suppression de <?= $params['id'] ?></h1>
    ```

3. On n'oublie pas de changer l'url dans le lien crée vers ce nouveau fichier.

    ```
    <a href="<?= $router->url('admin_post_delete', ['id' => $post->getId()]) ?>" class="btn btn-danger"
    onclick="return confirm('Voulez vous vraiment effectuer cette action ?')")>
        Supprimer
    </a>
    ```

4. On teste et on est bien redirigé vers la page créée.

5. On va maintenant créer le code pour la suprression d'un article.

    - On commence par instancier PDO.

    - On instancie la classe PostTable, où on va créer la méthode delete(), on passera comme paramètre à cette méthode l'id de l'article à supprimer.

    - Une fois que la suppression est finalisée on redirige l'utilisateur vers la page de listing des articles.

    ```
    <?php

    use App\Connection;
    use App\Table\PostTable;

    $pdo = Connection::getPDO();
    $table = new PostTable($pdo);
    $table->delete($params['id']);
    header('Location: ' .$router->url('admin_posts'));
    ?>

    <h1>Suppression de <?= $params['id'] ?></h1>
    ```

6. On va créer la méthode **delete()** dans la classe PostTable.

    ```
    public function delete(int $id)
    {
        $query = $this->pdo->prepare("DELETE FROM {$this->table} WHERE id = ?");
        $query->execute([$id]);
    }
    ```

7. On profite pour remplacer l'appel à la table *post* (écrit en dur comme ça) par *$this->table* dans les autres méthodes qussi, ainsi si un jour on décide de changer le nom de cette table il nous suffira de la changer dans la variable.

8. Cette requête nous retourne true ou false, on pourrait la typer comme étant un boolean, mais c'est mieux de gérer une exception en cas d'erreur.

    ```
    public function delete(int $id)
    {
        $query = $this->pdo->prepare("DELETE FROM {$this->table} WHERE id = ?");
        
        $queryExecuted = $query->execute([$id]);
        
        if ($queryExecuted === false) {
            throw new \Exception("Impossible de supprimer l'enregistrement $id dans la table {$this->table}");
        }
    }
    ```

    On aura pas besoin d'un return, parce que si c'est bien passé, ça va nous retourner true. si c'est mal apssé ça va tomber dans l'exception.

9. Avant de tester si ça marche et pour mieux visualiser les articles, on va ajouter leursb
    - On commence par ajouter une nouvelle colonne dans les en-têtes du tableau : 

    ```
     <tr>
        <th>#id</th>
        <th scope="col">Titre</th>
        <th scope="col">Actions</th>
    </tr>
    ```

    - On ajoute une nouvelle ligne dans le tableau avec l'appel aux id's de articles :

    ```
    <td>#<?= $post->getId() ?></td>
    ```

10. On teste en essayant de supprimer le premier article et ça marche, l'article est bien supprimé et on est bien redirigé vers le listing des articles.










    
