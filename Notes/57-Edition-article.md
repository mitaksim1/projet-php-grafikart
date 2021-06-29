## Chapitre 57 - Edition d'un article

Dans ce chapitre on va éditer des articles grâce aux formulaires.

Ce chapitre est très important parce que ça nous permettra de voir le HTML pour la création des formulaires, la validation des données au moment de vérifier si tous les champs ont été remplis et la persistance des données à envoyer à la bdd.

Dans ce chapitre, on va commencer juste par pouvoir modifier le champs Titre.

1. On commence par récupérer l'article qu'on souhaite éditer dans le fichier *views/admin/post/edit.php*;

    ```
    <?php

    use App\Connection;
    use App\Table\PostTable;

    $pdo = Connection::getPDO();
    $postTable = new PostTable($pdo);
    $post = $postTable->find($params['id']);

    ?>

    <h1>Editer l'article <?= $params['id'] ?></h1>
    ```

    - On a bien sûr, besoin de nous connecter à notre bdd.

    - On a besoin de la méthode **find** de la classe Table qui est héritée par la classe **PostTable**.

    - On récupère l'article dans la variable **$post** en passant comme paramètre à la méthode **find** la variable récupéré dans **$params['id']**.

2. On peut récupérer le nom de l'article :

    ```
    <h1>Editer l'article <?= e($post->getName()) ?></h1>
    ```
3. Si on teste en cliquant sur le titre on est bien dirigé ver s la page *http://localhost:8000/admin/post/19*.

4. On peut créer alors, notre formulaire.

    ```
    <form action="" method="POST">
        <div class="form-group">
            <label for="name">Titre</label>
            <input type="text" class="form-control" name="name" value="<?= e($post->getName()) ?>">
        </div>
        <button class="btn btn-primary">Modifier</button>
    </form>
    ```

5. Si on teste on a une erreur, parce que la page été définie à être redirigé vers une route url en GET.

    - On va donc, dupliquer cette route en la passant en méthode post et en changeant son nom pour qu'on ne se retrouve pas avec deux noms pareils ce que va nous donner une erreur.

    ```
    ->post('/admin/post/[i:id]', 'admin/post/edit', 'admin_post_edit')
    ```

6. Pour tester si le formulaire marche on fait juste un **dd** :

    ```
    if (!empty($_POST)) {
        dd('Traiter les données');
    }
    ```

7. On va modifier maintenaNt le fichier **Router.php** en dupliquant la méthode **post()** que l'on va renommer à **match()**.

    ```public function post(string $url, string $view, ?string $name = null): self
    {
        $this->router->map('POST|GET', $url, $view, $name);
        // Méthode fluent permet de retourner la classe elle même et ainsi enchaîber les méthodes
        return $this;
    }
    ```

    Cette méthode va gérer les routes en POST et en GET.

8. Maintenant dans les routes, on a pas besoin de dupliquer les deux routes, on peut juste appeler la méthode match pour cette même route.

    ```
    ->match('/admin/post/[i:id]', 'admin/post/edit', 'admin_post')
    ```

### Persister les données dans la bdd

On va pas créer la logique dans la vue, c'est pas une bonne méthode, on va la créer dans le fichier **PostTable**.

1. On crée la structure de la méthode **update()**.

    ```public function update(): void 
    {
        
    }
    ```

2. Dans la condition que l'oin avait crée dans **edit.php** on va faire quelque chose comme ça:

    ```
    if (!empty($_POST)) {
        $post->name = $_POST['name'];
        $pots->content = $_POST['content'];
        $postTable->update($post);
    }
    ```

3. On a pas modifier à la propriété *name* parce qu'elle est privée, alors on va créer un *setter*.

    ```
    public function setName(string $name): ?self
    {
        $this->name = $name;
        return $this;
    }
    ```

4. On fait la même chose pour la propriété *content*.

    ```
    public function setContent(string $content): ?self
    {
        $this->content = $content;

        return $this;
    }
    ```

5. Pour l'instant on va modifier que le titre de l'articlea, alors :

    ```
    if (!empty($_POST)) {
        $post->setName($_POST['name']);
        $postTable->update($post);
    }
    ```

6. On peut finir de coder la méthode update().

    ```
    public function update(Post $post): void 
    {
        $query = $this->pdo->prepare("UPDATE {$this->table} SET name = :name WHERE id = :id");
       
        $queryExecuted = $query->execute([
            'id' => $post->getId(),
            'name' => $post->getName()
        ]);
       
        if ($queryExecuted === false) {
            throw new \Exception("Impossible de supprimer l'enregistrement $id dans la table {$this->table}");
        }
    }
    ```

7. Dans **edit.php**, on va initialiser la variable $success pour afficher un message si tout c'est bien passé.

    ```
    $success = false;

    if (!empty($_POST)) {

        $post->setName($_POST['name']);
        $postTable->update($post);
        $success = true;
    }
    ```

    Dans le code html :

    ```
    <?php if ($success): ?>
        <div class="alert alert-success">
            L'article a bien été modifiée
        </div>
    <?php endif ?>
    ```




