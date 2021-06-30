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

### Validation des données

On a pas encore géré la validation des données, pour l'instant si l'utilisateur clique sur le bouton "Modifier" il n'y aura pas des erreurs.

On pourrait mettre juste la propriété **required** dans l'input du formulaire, mais ça c'est juste une sécurité du côté frontend. Une personne mal intentionné pourrait inspecter le code, l'effacer manuellement et ainsi envoyer le formulaire vide ou avec des données indésirables.

1. La façon la plus simple de gérer ça côté backend, c'est de créer une variable **$errors = []** qui contiendrait tous les erreurs de validation.

2. Ensuite dans la condition que l'on avait crée, on créerai une autre que vavvérifier si le champs est vide avant d'accepter la modif.

    ```
    if (!empty($_POST)) {
        if (empty($_POST['name'])) {
            $errors['name'][] = 'Le champs titre ne peut pas être vide';
        }
        if (mb_strlen($_POST['name']) <= 10) {
            $errors['name'][] = 'Le champs titre doit contenir plus de 10 caractères';
        }
        dd($errors);
        ...
    }
    ```

    **[]** : on peut rajouter d'autres données dans la même clé dans un tableau en le mettant à côté comme ça.

3. On teste avec le **dd** en soumettant le formulaire vide et on retrouve bien dans le dump un tableau avec les deux erreurs.

4. Maintenant, il faut que les modifications ne soient faites que si il n'y a pas des erreurs dans le tableau $errors.

    ```
    if (empty($errors)) {

        $postTable->update($post);
       
        $success = true;
    }
    ```

5. Dans le code html on va aussi mettre un autre message.

    ```
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            L'article n'a pas pu être modifié, merci de corriger vos erreurs
        </div>
    <?php endif ?>
    ```

6. Le message n'est pas clair pour l'utilisateur, parce qu'il peut ne pas savoir où il s'est trompé, alors avec Boostratp on peut utiliser la classe **invalid-feedback** avec le message explicitant l'erreur. Cette classe ne marche si on ajoute la classe **is-valid** dans l'input concerné par le feedback.

    ```
    <input type="text" class="form-control is-invalid" name="name" value="<?= e($post->getName()) ?>">
    <div class="invalid-feedback">
        Lorem ipsum dolor, sit amet consectetur adipisicing elit. Fugit, aperiam.
    </div>
    ```

8. Pour que le message n'apparaîsse que si il y a une erreur on va créer une condition dans l'input pour gérér la classe **is-valid**.

    ```
    <input type="text" class="form-control <?= isset($errors['name']) ? "is-invalid" : ""?>"
    ```

9. On affiche le vrai message :

    ```
    <?php if (isset($errors['name'])): ?>
        <div class="invalid-feedback">
            <?= implode('<br>', $errors['name']) ?>
        </div>
    <?php endif ?>
    ```

10. On teste, en enlèvant le *required* dans l'inspecteur et maintenant on ne peut plus envoyer le formulaire vide, les messages que l'on a configuré apparaîssent bien.

### Automatiser la validation

Pour l'instant on n'a validé que les modifications du titre, mais on a aussi le contenu à vérifier et fururement on aura peut être d'autres données.

Pour éviter de faire des conditiosn à l'infini on va essayer de trouver une librairie que fasse la validation d'une façon plus simple pour nous.

On va donc sur Packagist et on essaie de trouver quelque chose qui correpsonde à nos besoins.

1. On va installer la librairie **vlucas/valitron**, pour avoir la même version que le mec, on précise la version.

    ```
    composer require vlucas/valitron
    ```

2. On se refère à la doc pour savoir comment ça marche : https://github.com/vlucas/valitron.

    Dans la condition que l'on avait crée on commence par instancier valitron, on lui passera comme paramètre le tableau contenu dans $_POST :

    ```
    $validator = new Validator($_POST);
    ```

3. Ensuite on appelle la méthode **rule()** qui va vérifier ce qu'on veut valider, elle prendra comme paramètre la règle à appliquer et le champs du tableau qu'on souhaite vérifier.

    ```
    if (!empty($_POST)) {
        $validator = new Validator($_POST);
        $validator->rule('required', 'name');
        $post->setName($_POST['name']);

        if ($validator->validate()) {
            $postTable->update($post);
            // Si pas d'erreur lors de la requête
            $success = true;
        } else {
            dd($validator->errors());
        }
    }
    ```

4. On teste, en enlèvant le *required* dans l'inspecteur manuellement et on voit que **errors** nous envoi un tableau d'erreurs.

    ```
    ^ array:1 [▼
      "name" => array:1 [▼
        0 => "Name is required"
      ]
    ]
    ```

5. Le problème c'est que le message est en anglais. On regarde dans la doc et heuresement on peut la changer.

    ```
    Validator::lang('fr);
    ```

6. Le résultat est meilleur, mais il laisse le mot *Name* en anglais. 

    Nous on veut le changer, il ne faut pas hésiter à regarder directement le code source pour voir comment la personne la codé.

    - On a cherché d'abord par le mot **errors**, ensuite on a vu qu'il y avait une méthode **checkAndSetLabel**, où il fait appele à une propriété *labels*, on revient donc dans la doc principale et on cherche par le mot *labels* comme ça on est tombé directement sur la partie où il nous explique comment faire.

    - On copie la partie qui nous intéresse et on remplace les clés et les valeurs par les données qui nous intéressent :

    ```
    $validator->labels(array(
        'name' => 'Titre',
        'contenu' => 'Contenu'
    ));
    ```

6. On peut remplacer le **dd** par :

    ```
    $errors = $validator->errors();
    ```

    On teste et maintenant on a bien le message sous l'input.

### Validation de la longueur du titre

1. Dans la doc il y a toute la liste des règles disponibles, pour notre cas on va utiliser **lengthBetween**.

    ```
    $validator->rule('lengthBetween', 'name', 10, 200);
    ```

2. On n'aime pas trop le message qui est affiché, alors en regardant le code source on voit que la méthode qui le gére c'est protégée, alors on peut l'écraser.

    On va donc, créer une classe pour pouvoir modifier les données qui nous ne correspondent pas.

    - Dans **src** on crée la classe **Validator.php** :

    ```
    <?php
    namespace App;

    use Valitron\Validator as ValitronValidator;

    class Validator extends ValitronValidator {

    }
    ```

3. Ensuite on vient coller le code que l'on a récupéré dans le [code source](https://github.com/vlucas/valitron/blob/f7e662e3c0c1c465d548542672e08862fbb601d9/src/Valitron/Validator.php#L1464).

    ```
    protected function checkAndSetLabel($field, $message, $params)
    {
        if (isset($this->_labels[$field])) {
            $message = str_replace('{field}', $this->_labels[$field], $message);

            if (is_array($params)) {
                $i = 1;
                foreach ($params as $k => $v) {
                    $tag = '{field' . $i . '}';
                    $label = isset($params[$k]) && (is_numeric($params[$k]) || is_string($params[$k])) && isset($this->_labels[$params[$k]]) ? $this->_labels[$params[$k]] : $tag;
                    $message = str_replace($tag, $label, $message);
                    $i++;
                }
            }
        } else {
            $message = $this->prepend_labels
                ? str_replace('{field}', ucwords(str_replace('_', ' ', $field)), $message)
                : str_replace('{field} ', '', $message);
        }

        return $message;
    }
    ```

7. Maintenant, que l'on extend de cette méthode on fera appel à notre classe, il faut changer le namespace du *use* dans **edit.php**.

    ```
    use App\Validator;
    ```

    On a plus besoin de préciser les noms pour les labels non plus, on peut donc effacer cette partie.

    ```
    if (!empty($_POST)) {
        
        Validator::lang('fr');
        $validator = new Validator($_POST);
        $validator->rule('required', 'name');   
        $validator->rule('lengthBetween', 'name', 10, 200);

        $post->setName($_POST['name']);

        if ($validator->validate()) {

            $postTable->update($post);
            $success = true;
        } else {

            $errors = $validator->errors();
        }
    }
    ```

8. Dans **Validator.php** on a qu'à retourner les données que l'on souhaite changer, on peut effacer le reste du code.

    ```
    <?php
    namespace App;

    use Valitron\Validator as ValitronValidator;

    class Validator extends ValitronValidator {

        protected function checkAndSetLabel($field, $message, $params)
        {   
            return str_replace('{field} ', '', $message);   
        }
    }
    ```

    
















