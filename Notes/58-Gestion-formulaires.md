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