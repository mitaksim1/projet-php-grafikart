## Chapitre 58 - Gestion des formulaires

Dans ce chapitre on va essayer de gérer la création de nos formulaires.

On a vu que pour créer juste un champs on a beaucoup de lignes html, à la longue ça peut être un peu pénible.

On va donc, créer une classe qui va nous permettre de gagner du temps.

Le mec nous passé juste la signature de qu'il avait pensé faire.

- On instancierait une classe **Form** qui serait crée dans le namespace **App\HTML\Form**.

- Dans la tag *form*, on n'aurait qu'à appeler les méthodes input() et textarea() qui prendrait comme paramètres le *name* qui correspondrait à la valeur du champs name et en deuxième paramètre le *label* qui correspondrait au Titre.

A NOUS DE JOUER !

1. Je commence par créer la classe Form.

    ```
    <?php
    namespace App\HTML;

    class Form {

        private $data;
        private $errors;

        public function __construct($data, array $errors)
        {
            $this->data = $data;
            $this->errors = $errors;
        }

        public function input(string $name, string $label): string
        {
            return '';
        }

        public function textarea(string $name, string $label): string
        {
            return '';
        }
    }
    ```

2. On peut effacer l'ancien code que l'on avait crée dans le form, on ne laisse juste la strucuture que fait appel à ces méthodes.

    On va copier le code (en le commentant) dans notre classe juste pour avoir une trace de ce que l'on veut faire.

    ```
    <div class="form-group">
        <label for="name">Titre</label>
        <input type="text" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" name="name" value="<?= e($post->getName()) ?>" required>
        <?php if (isset($errors['name'])): ?>
        <div class="invalid-feedback">
            <?= implode('<br>', $errors['name']) ?>
        </div>
        <?php endif ?>
    </div>
    ```

3. On retourne le code HTML avec la méthode **Heredoc** :

    ```
    public function input(string $key, string $label): string
    {
        return <<<HTML
            <div class="form-group">
                <label for="field{$key}">{$label}</label>
                <input type="text" id="field{$key}"class="form-control" name="{key}" value="" required>
            </div>
HTML;
    }
    ```

    Pour l'instant , on a effacé les conditions pour afficher les messages.

4. On veut pouvoir récupérer les valeurs de l'input.

    Comme on la clé va changer, soit ce sera *name* soit ce sera *slug*, on ne pourra pas le faire de la façon classique utilisant le getter de $post (->getName()), on va faire comme suit :

    ```
    $method = 'get' . ucfirst($key);
    // dd($method);
    $value = $this->data->$method();
    ```

5. On teste.

6. On va gérer le cas où la donnée envoyé au construct soit un tableau.

    Dans ce cas, ce sera intéressant de créer une méthode privé que va récupérer les clés de tableau, si il n'y a rien retourner null.

    Si c'est pas un tableau utiliser la logique que l'on avait crée dans la méthode input().

    ```
    private function getValue(string $key)
    {
        // Si donnée passée en paramètre est un tableau
        if (is_array($this->data)) {
            // retourner la clé de ce tableau
            return $this->data[$key] ?? null;
        }
        $method = 'get' . ucfirst($key);
        // dd($method);
        $value = $this->data->$method();
    }
    ```

7. Maintenant, dans la méthode input(), on a qu'à appeler cette méthode.

    ```
    $value = $this->getValue($key);
    ```

### On gère les erreurs

1. On avait ajouté à la classe 'form-control' de l'input la classe Boostrap *is-invalid*, pour pouvoir l'ajouter danjs notre nouvel code, on va initialiser une variable qui contiendar la valeur initiale et on va créer la condition pour vérifier si il y a des erreurs, si c'est le cas on ajoutera la classe is-invalid aussi.

    ```
    $inputClass = 'form-control';
    if (isset($this->errors[$key])) {
        $inputClass .= ' is-invalid';
    }
    ```

2. On remplace la variable laissé en dur par cette variable.

    ```
    <input type="text" id="field{$key}" class="{$inputClass}" name="{$key}" value="{$value}" required>
    ```

3. On va maintenant re créer la *div* qui va contenir la classe Bootstrap *invalid-feedback*, c'est elle qui va nous retourner le message d'erreur.

    ```
    $invalidFeedback = '';
    if (isset($this->errors[$key])) {
        $inputClass .= ' is-invalid';
        $invalidFeedback = '<div class="invalid-feedback">' . implode('<br>', $this->errors[$key]) . '</div>';
    }
    return <<<HTML
        <div class="form-group">
            <label for="field{$key}">{$label}</label>
            <input type="text" id="field{$key}" class="{$inputClass}" name="{$key}" value="{$value}" required>
            {$invalidFeedback}
        </div>
    ```

4. On teste et ça marche.

### Le textarea pour le contenu

1. On C/C le code de la méthode **input** en changeant les données nécéssaires : 

    ```
    public function textarea(string $key, string $label): string
    {
        $value = $this->getValue($key);
        $inputClass = 'form-control';
        $invalidFeedback = '';
        if (isset($this->errors[$key])) {
            $inputClass .= ' is-invalid';
            $invalidFeedback = '<div class="invalid-feedback">' . implode('<br>', $this->errors[$key]) . '</div>';
        }
        return <<<HTML
            <div class="form-group">
                <label for="field{$key}">{$label}</label>
                <textarea type="text" id="field{$key}" class="{$inputClass}" name="{$key}" required>{$value}"</textarea>
                {$invalidFeedback}
            </div>
HTML;
    }
    ```

2. Si on teste comme ça, on a un message d'erreur : *Call to undefined method App\Model\Post::getContent()* , parce que dans notre classe **Post** on n'avait pas crée un gettre pour $content. On va donc, le créer.

    Dans **Model/Post.php** :

    ```
    public function getContent(): ?string
    {
        return $this->content;
    }
    ```

3. On re teste et maintenant on a bien le textarea avec le contenu à l'intérieur.

### Factorisation du code

Dans les deux méthodes on répète le même code, alors on va le factoriser dans deux autres méthodes, une qui va gérer l'ajout de la classe 'is-invalid' et l'autre qui va gérer l'ajout de la div avec le texte de l'erreur.

1. On commence par la méthode **getInputClass** :

    ```
    private function getInputClass(string $key)
    {
        $inputClass = 'form-control';
        if (isset($this->errors[$key])) {
            $inputClass .= ' is-invalid';
        }
        return $inputClass;
    }
    ```
2. Ensuite **getErrorFeedback**.

    ```
    private function getErrorFeedback(string $key)
    {
        if (isset($this->errors[$key])) {
            return '<div class="invalid-feedback">' . implode('<br>', $this->errors[$key]) . '</div>';
        }
        return '';
    } 
    ```

3. Dans les méthodes **input** et **tetxarea** on a qu'à les appeler comme suit :

    ```
    <input type="text" id="field{$key}" class="{$this->getInputClass($key)}" name="{$key}" value="{$value}" required>
    {$this->getErrorFeedback($key)}
    ```

    ```
    <textarea type="text" id="field{$key}" class="{$this->getInputClass($key)}" name="{$key}" required>{$value}"</textarea>
    {$this->getErrorFeedback($key)}
    ```

4. On teste pour vérifier que rien n'est cassé et ça marche.

### Création d'un champs pour rentrer une date

1. On commence par appeler la méthode **input** dans notre formulaire.

    ```
    <?= $form->input('created_at', 'Date de création'); ?>
    ```

2. Si on teste on a une erreur : *Call to undefined method App\Model\Post::getCreated_at()*, on voit que le mot created est un majuscule et le mot A de at en minuscule, il va falloir changer notre variable $method que l'on avait crée dans la méthode *getValue()* **HTML/Form.php**.

    ```
    $method = 'get' . str_replace(' ', '', ucwords(str_replace('_', ' ', $key)));
    ```

    - Si **$key** a un underscore ("_") la méthode *str_replace* va le remplacer par un espace vide (" "). 
    
    On va se retrouver donc avec deux mots.

    - **ucwords** : va mettre une majuscule à la première lettre de ces deux mots.

    - Ensuite on appelle encore **str_replace** pour enlèver l'espace que l'on avait crée.

    Dans notre cas, on va se retrouver donc, avec le mot *created_at* qui deviendra *CreatedAt*.

3. On teste et on a un autre message d'erreur : *Object of class DateTime could not be converted to string*, cela est du au fait que quand on fait un getValue() il va renvoyer la valeur DateTime de getCreatedAt à la méthode input qui à son tour essaie de l'injecter sur forme de string.

    Pour corriger ça :

    - La première chose que l'on va faire c'est préciser le type de retour de la méthode *getValue* à string.

    ```
    private function getValue(string $key): string
    ```

    - On va stocker la valeur reçu dans une variable, pour pouvoir faire une condition si la valeur retourné et du type DateTime.

    ```
    $value = $this->data->$method();
        if ($value instanceof DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }
        return $value;
    ```

 




