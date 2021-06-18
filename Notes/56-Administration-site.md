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







    
