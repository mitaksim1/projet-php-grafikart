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


