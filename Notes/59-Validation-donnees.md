## Chapitre 59 - Validation des données

On va essayer de factoriser notre code pour la partie validation aussi.

1. Dans **src**, on va créer un nouveau dossier **validators** où on va créer un fichier qui va s'occuper de valider les articles **PostValidator.php**.

    ```
    <?php
    namespace App\Validators;

    class PostValidator {

    }
    ```

2. On va réfléchir maintenant aux données que l'on veut recevoir dans le constructeur.

    On aura besoin du tableau $_POST, parce que c'est là que l'on va recevoir toutes nos informations, pour l'intsant c'est Validator() qui le récupère pour après faire les validations, alors on va mettre tout le code que l'on avait crée dans le constructeur.

    ```
    public function __construct(array $data)
    {
        $this->data = $data;
        
        $validator = new Validator($data);
       
        $validator->rule('required', ['name', 'slug']);
       
        $validator->rule('lengthBetween', ['name', 'slug'], 10, 200);
    }
    ```

3. On crée la méthode validate() qui va faire la même chose que la méthode validate de Valitron.

    - On aura besoin de récupérer $validator, alors on va le créer comme une propriété comme ça on en aura accès partout.

    ```
    private $validator;
    ```

4. Dans la méthode __construct, on précise que cette propriété va stocker la valeur de l'objet $validator.

    ```
    $this->validator = $validator;
    ```

5. Ensuite, on pourra la récupérer dans la méthode validate().

    Comme cette méthode nous retourne true ou false, on peut directement retourner lé résultat.

    ```
    public function validate(): bool
    {
        return $this->validator->validate();
    }
    ```

### On gére la validation du slug

1. On commence par ajouter une règle comme on avait fait pour les autres.

    ```
    $validator->rule('slug', 'slug');
    ```

2. On teste, en changeant le slug d'un article et ça marche.

    Maintenant, on veut empêcher la création d'un même slug dans un autre article.

    Dans Valitron, on n'a pas de méthode toute prête pour ça, mais on a une fonction qui nous permet de créer nos propres règles.

    ```
    $validator->rule(function ($field, $value) {
        return false;
    }, 'slug', 'Ce slug est déjà utilisé'); 
    ```

3. La partie visuelle est faite, il nous faut créer la logique de la condition.

    On veut qu'elle nous retourne *true* si le slug n'existe pas déjà dans la bdd ou *false* si elle existe.

    Dans **PostValidator** on a pas accès à la connexion avec la base de données, on va alors créer notre méthode dans la classe **Table**.

    On pourra injecter cette méthode dans le constructeur de **PostValidator** après.

    ```
    public function exists(string $field, $value): bool
    {
        $query= $this->pdo->prepare("SELECT COUNT(id) FROM {$this->table} WHERE $field = ?");
        $query->execute([$value]);
        $result = (int)$query->fetch(PDO::FETCH_NUM)[0] > 0;
    }
    ```

4. On pourra accèder à cette méthode depuis PostValidator :

    ```
    public function __construct(array $data, PostTable $table)
    ```

5. On précise que l'on utilisé cette classe avec un *use* comme suit :

    - Ce callback va nous retourner *true* si c'est c'est faux que cette donné existe

    ```
    $validator->rule(function ($field, $value) use ($table) {
        return !$table->exists($field, $value);
    }, 'slug', 'Ce slug est déjà utilisé'); 
    ```

6. Comme on a rajouté l'instance de PostTable dans le constructeur, il faut aussi que je le passe en argument lors de l'instanciaton de PostValidator dans edit.php.

    ```
    $validator = new PostValidator($_POST, $postTable);
    ```

7. On teste et ça marche!

###

Quand os essaie de soumettre le formulaire, il ne nous laisse pas faire en nous disant que ce slug existe déjà.

C'est normal, vu qu'au moment où il fait la requête dans la bdd il tombe sur son id aussi.

Pour règler ça :

1. On doit changer la signature de la méthode **exists()** et ses instructions :

    - On va rajouter le paramètre $exceptId qui est initialisé à null qui va vérifier l'id de l'article.

    - Onlance la requête sans la préparer où on vérifie d'abord juste le slug ($field).

    - Ensuite, on vérifie si on a bien l'id de l'article, si oui on concatène une autre conditionà la requêter et on ajoute cette id au tableau qui va être exécuté.

    - A ce moment là, on prepare la requête.

    ```
    public function exists(string $field, $value, ?int $exceptId) = null: bool
    {
        $sql = "SELECT COUNT(id) FROM {$this->table} WHERE $field = ?";
        $params = [$value];
        if ($exceptId !== null) {
            $sql .= " AND id != ?";
            $params[] = $exceptId;
        }
        $query = $this->pdo->prepare($sql);
        $query->execute($params);
        $result = (int)$query->fetch(PDO::FETCH_NUM)[0] > 0;

        return $result;
    }
    ```

2. Dans le constuct de PostValidator, on doit injecter ce nouveau paramètre :

    ```
    public function __construct(array $data, PostTable $table, ?int $postId = null)
    {
        ....

        $validator->rule(function ($field, $value) use ($table, $postId) {
            return !$table->exists($field, $value, $postId);
        }, 'slug', 'Ce slug est déjà utilisé'); 
        $this->validator = $validator;
    }
    ```

3. On fait la même chose dans **edit.php** lors de l'instanciation de la classe.

    ```
    $validator = new PostValidator($_POST, $postTable, $post->getId());
    ```

4. Pour ajouter cette vérification au titre aussi, il suffit de rajouter l'index au tableau passé et changer le message:

    ```
    $validator->rule(function ($field, $value) use ($table, $postId) {

        return !$table->exists($field, $value, $postId);

    }, ['slug', 'name'], 'Cette valeur est déjà utilisé'); 
    ```

### On prévoit des situations pour mieux factoriser le code

On peut se dire que demain, on va avoir besoin des méthodes **validate()** et **errors()** pour valider les données d'une table Catégories.

On va donc, les mettre dans une classe parent, comme ça elles pourront être héritées par les classes qui en auront besoin.

1. On crée la classe **AbstractValidator** où on va C/C tout le code de PostValidator, on va effacer les données dont on a pas besoin peit à peti.

    A la fin on se retrouve avec ce code :

    ```
    <?php
    namespace App\Validators;

    use App\Validator;

    abstract class PostValidator {

        private $data;
        private $validator;

        public function __construct(array $data)
        {
            $this->data = $data;

            $validator = new Validator($data);
            $this->validator = $validator;
        }
    }
    ```

2. Maintenant, dans PostValidator, on a qu'a extends la classe parent et enlèver les données dont on a plus besoin.

3. On n'oublie pas de passer les propriétés du parent en type protégée.


