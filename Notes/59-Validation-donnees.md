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

6. 