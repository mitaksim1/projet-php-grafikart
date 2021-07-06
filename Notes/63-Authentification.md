## Chapitre 63 - Authentification

Dans le dossier commands, on avait déjà crée un fichier qui s'occupait de mettre un utilisateur dès que l'on se connectait à la bdd avec le login *admin* et le mot de passe *admin* aussi.

Ce qu'on veut faire c'est empêcher des gens d'avoir accès à ces pages là et les rediriger vers une page de connexion.

1. Pour créer cette page de connexion, il va nous falloir une nouvelle route.

    ```
    ->match('/login', 'auth/login', 'login')
    ```

2. Crétion de la vue dans un nouveau dossier **auth/login.php**.

3. Si on veut se baser sur le formulaire que l'on avait déjà crée : *$form = new Form($post, $errors);* on peut, sauf que l'on a pas encore la classe qui permet de répresenter l'utilisateur, alors on va la créer.

    Dans Model/User.php :

    ```
    <?php
    namespace App\Model;

    class User {

        /**
         * @var string
         */
        private $username;

        /**
         * @var string
         */
        private $password;
    }
    ```

4. Pour pouvoir accéder à ces propriétés on aura besoin de ses getters and setters.

    Pour ne pas avoir a tout saisir à la main, on a installé l'extension **PHP Getters and Setters**.

    ```
    /**
     * Get the value of username
     *
     * @return  string
     */ 
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set the value of username
     *
     * @param  string  $username
     *
     * @return  self
     */ 
    public function setUsername(string $username)
    {
        $this->username = $username;

        return $this;
    }
    ```

5. On a juste rajouté dans le getter de username que le retour peut être une string ou null :

    ```
    public function getUsername(): ?string
    ```

6. Comme on a typé les méthodes, plus besoin de laisser les commentaires fournis avec l'extension

7. Maintenant que l'on a crée la classe User et ses getters et setters, on peut l'intancier pour s'en servir.

    ```
    <?php

    use App\HTML\Form;
    use App\Model\User;

    $user = new User();
    $form = new Form($user, []);
    ?>
    ```

8. On teste pour voir si la route marche.

### Création du formulaire

1. On fait appel à la méthode **input** déjà crée.

    ```
    <form action="" method="POST">
        <?= $form->input('username', 'Nom d\'utilisateur'); ?>
        <?= $form->input('password', 'Mot de passe'); ?>
    </form>
    ```

2. On a bien notre formulaire surla page, le soucis c'est que le mot d passe apparaît quand on le saisi.

    On va rajouter une condition à la méthode input pour gérer le type de l'input pour chaque libellé.

    ```
    $type = $key === "password" ? "password" : "text";

    <input type="{$type}" id="field{$key}" class="{$this->getInputClass($key)}" name="{$key}" value="{$value}" required>
    ```

3. On ajouteun bouton à notre formulaire.

    ```
    <button type="submit" class="btn btn-primary">Se connecter</button>
    ```

4. Maintenant, il faut traiter les données.

    On commence, bien sûr par vérifier que les données dans la variable $_POST n'est pas vide, ça veut dire s'il y a bien un utilisateur qui essaie de se connecter.

    Si c'est le cas on a bien des données à traiter.

    ```
    if (!empty($_POST)) {
    if (empty($_POST['username']) || empty($_POST['password'])) {
            $errors['password'] = 'Identifiant ou mot de passe incorrect';
        } 
    }
    ```

    - On a initialisé la variable $errors pour pouvoir l'utiliser dans le if.

    ```
    $errors = [];
    ```

5. Maintenant, que l'on a cette variable, on pourra la passer comme deuxième paramètre de l'instance de Form.

    ```
    $form = new Form($user, $errors);
    ```

6. Si on teste, on va avoir une erreur, parce que $form s'attend à avoir un tableau des erreurs et ici on n'en a qu'un.

    On peut changer alors, la méthode **getErrorFeedback** en créant une condition qui va vérifier ce détail.

    ```
    private function getErrorFeedback(string $key): string
    {
        if (isset($this->errors[$key])) {
            if (is_array($this->errors[$key])) {
                $error = implode('<br>', $this->errors[$key]);
            } else {
                $error = $this->errors[$key];
            }
            return '<div class="invalid-feedback">' . $error . '</div>';
        }
        return '';
    } 
    ```

7. On re teste, en n'oubliant pas d'effacer le *required* dans l'inspecteur et ça marche.
