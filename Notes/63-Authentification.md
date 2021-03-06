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

8. En cas d'erreur on garde quand même la valeur de l'user pour qu'il n'ait pas à le retaper.

    ```
    $user->setUsername($_POST['username']);
    ```

### Vérifier si l'utilisateur existe en base de données

1. On peut créer la logique directemant dans le fichier *login.php*, mais nous on va rester dans la logique de créer une nouvelle table.

    On va C/C **PostTable** , on le renomme **UserTable** et on fera les modifications nécéssaires au fur et à mesure.

    ```
    <?php
    namespace App\Table;

    use App\Model\User;

    final class UserTable extends Table {

        protected $table = "user";
        protected $class = User::class;

    }
    ```

2. On C/C les instructions crées dans la méthode *find()* de **Table.php**, parce que la logique est la même, on change juste le *WHERE*, parce qu'ici on ne veut pas récupérer l'id mais le username de l'utilisateur.

    ```
    public function findByUserName(string $username) {
        
        $query = $this->pdo->prepare('SELECT * FROM ' . $this->table . ' WHERE username = :username');
        
        $query->execute(['username' => $username]);

        $query->setFetchMode(PDO::FETCH_CLASS, $this->class);

        $result = $query->fetch();

        if ($result === false) {
            throw new NotFoundException($this->table, $username);
        }

        return $result;
    }
    ```

3. Pour pouvoir accéder à la bdd, il faut avoir une connexion avec PDO.

    ```
    $pdo = Connection::getPDO();
    ```

4. On accède à la méthode *findByUsername()* en instanciant la classe **UserTable**.

    ```
    $table = new UserTable($pdo);
    ```

5. On peut maintenant, vérifier si l'user qui eesaie de se connecter correspond à un user dans la bdd.

    ```
    $userLogin = $table->findByUsername($_POST['username']);
    ```
6. Si on teste, on a une erreur, parce qu'on avait précisé lors de la construction de *NotFoundException* que le deuxième paramètre devrait être du type entier et là on retourne une string ($username), on enlève juste le typage pour ne plus avoir l'erreur et maintenant on a bien le message, sauf qu'il dit *id* au lieu de *username*.

    On va capturer l'erreur pour passer un message du type *NotFoundException* avec le même message que l'on avait crée avant.

    ```
    try {
        $userLogin = $table->findByUsername($_POST['username']);
    } catch (NotFoundException $e) {
        $errors['password'] = 'Identifiant ou mot de passe incorrect';
    }  
    ```

7. On re actualise la page et on reçoit bien le message d'erreur.

    Si on essaie de se logger comme admin, on a pas d'erreur.

8. Il faut maintenant, récupérer et vérifier si le mot de passe correspond aussi à un mot de passe existant dans la bdd.

    ```
    // Récupération du mot de passe de la bdd
    $userLogin->getPassword();

    // Récupération du password saisi par l'user
    $_POST['password'];
    ```

9. Dans **commands/fill.php** on avait hashé le mot de passe, alors il faut utiliser la fonction **password_verify** pour voir s'ils correspondent.

    ```
    password_verify($_POST['password'], $userLogin->getPassword());
    ```

10. Cette fonction va nous retourner true si les mots de passe correpondent ou false si non.

    On fait un dd pour vérifier si ça marche.

11. On traite le cas où la vérification nous retourne *false*, dans ce cas on affichera le même message d'erreur.

    Comme on se répéte on va définir que ce message sera le message à afficher par défaut et on traitera les autres possibles messages au fur et à mesure.

    - On le défini au début de la condition.

    - On change la logique initiale en vérifiant si les variables $_POST['username'] et $_POST['password'] ne sont pas vides.

    Si c'est le cas, on commence le traitement des données.

    - Maitentant, au moment de vérifier si les mots de passe correspondent on va vérifier si la réponse retourné est *true*.

    Si true, on continue le code, sinon il va tomber dans le *catch* et le message s'affichera.

    ```
    if (!empty($_POST)) {

    // On sauvegarder la valeur saisie dans l'input username
    $user->setUsername($_POST['username']);

    // On défini ce message comme le message par défaut
    $errors['password'] = 'Identifiant ou mot de passe incorrect';

    if (!empty($_POST['username']) || !empty($_POST['password'])) {

        // Connexion à la bdd et aux méthodes de UserTable
        $pdo = Connection::getPDO();
        $table = new UserTable($pdo);

        // Maintenant on peut vérifier si l'user existe dans la bdd
        // On capture l'erreur au cas ou
        try {
            $userLogin = $table->findByUsername($_POST['username']);

            // Récupération du mot de passe de la bdd
            $userLogin->getPassword();

            // Récupération du password saisi par l'user
            $_POST['password'];

            // Vérification si les mots de passe correspondent
            if (password_verify($_POST['password'], $userLogin->getPassword()) === true) {

            };
        } catch (NotFoundException $e) {

        }     
    }     
    ```

12. Si l'utiliateur est bien connecté, on va le rediriger vers la page d'administration (admin_posts).

    ```
    if (password_verify($_POST['password'], $userLogin->getPassword()) === true) {
        header('Location: ' . $router->url('admin_posts'));
        exit();
    }
    ```

    **Rappel** : On met le **exit()** parce que si tout marche, on ne veut pas que ça tombe dans le *catch* on veut que le oce arrête de s'exécuter.

13. On teste et on est bien redirigé vers la page des articles.

### Système de session

On a géré juste la connexion, il faut maitenant créer un système de session pour que l'utilisateur soit reconnu dans toutes les pages.

Dans la classe **Auth** que l'on avait crée, il y restait les instructions à coder, c'est là qu'on va gérer les sessions.

1. Si la clé $_SESSION['auth'] existe il y a bien un utilisateur connecté, on lui laissera accèder à toutes les pages, sinon il faut lui envoyer une exception.

    Pour séparer cette exception des autres, on va créer un autre dossier **Security** où on va créer un fichier pour cette exception **ForbiddenException**.

    ```
    <?php
    namespace App\Security;

    use Exception;

    class ForbiddenException extends Exception {

    }
    ```

2. Dans la classe Auth.

    ```
    <?php
    namespace App;

    use App\Security\ForbiddenException;
    use Exception;

    class Auth {
        /**
         * Vérifie si l'utilisateur est bien connecté
         */
        public static function check() {
            if (!isset($_SESSION['auth'])) {
                throw new ForbiddenException();
            }
        }
    }
    ```

    - On teste et on tombe bien sur le ForbiddenException.

3. C'est au moment où on a vérifié que l'utilisateur est bien en bdd, qu'on peut initialiser sa session.

    On sauvegarde son id dans la clé 'auth'.

    ```
    if (password_verify($_POST['password'], $userLogin->getPassword()) === true) {
        
        session_start();
       
        $_SESSION['auth'] = $userLogin->getId();

        header('Location: ' . $router->url('admin_posts'));
        exit();
    };
    ```

4. On avait pas prévu de sauvegarder l'id de l'utilisateur, on va donc rajouter cette propriété dans le model User.

5. Si on teste on tombe toujours sur le Forbidden, cela est dû au fait que dès fois la session ne se démarre pas, alors il faut vérifier si on a bien une session d'activée. On va utiliser la fonction **session_status**.

    ```
    public static function check() {
        // Vérifie si la session est activée
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['auth'])) {
            throw new ForbiddenException();
        }
    }
    ```

6. On re teste et ça marche, maintenant une personne déconnectée n'aura pas accès à cette page.

7. Pour tester vraiment si ça marche, dans l'inspecteur, on va dans l'onglet Applications, on supprime la ligne qui contient la session, on reactualise la page et on retombe sur le Forbidden.

### Redirection vers une page d'erreur

1. Pour gérer une page d'erreur, on va changer la partie du code dans **Router.php** qui gérait la direction des pages.

    - On va mettre le code dans un try/catch.

    ```
    try {
        ob_start();
        require $this->viewPath . DIRECTORY_SEPARATOR . $view . '.php';
        // ob_get_clean va récupérer la ligne ou les lignes contenues entre ob_start et lui, dans ce cas il va sauvegarder dans $content le require ci-dessus
        $content = ob_get_clean();
        // Une fois le require récupéré on va appeler la vue (à créer) default.php
        require $this->viewPath . DIRECTORY_SEPARATOR . $layout . '.php';
    } catch (ForbiddenException $e) {
        header('Location: ' .$this->url('login') . '?forbidden=1');
        exit();
    }
    ```

2. On re actualise la page, on est bien redirigé vers la page de login et l'url est bien : *http://localhost:8000/login?forbidden=1*.

3. Dans **login.php**, on va mettre un message d'erreur.

    ```
    <?php if (isset($_GET['forbidden'])): ?>
    <div class="alert alert-danger">
        Vous ne pouvez pas accèder à cette page
    </div>
    <?php endif ?>
    ```

### Déconnexion

1. Pour pouvoir se déconnecter, on va créer une nouvelle route.

    ```
    ->post('/logout', 'auth/logout', 'logout')
    ```

2. Le lien pour cette route on va le mettre dans le **layouts/default.php** admin que l'on avait crée tout au début.

    ```
    <li class="nav-item">
        <form action="<?= $router->url('/logout') ?>" method="post">
            <button type="submit" class="nav-link" style="background:transparent; border:none;">Se déconnecter</button>
        </form>
    </li>
    ```

3. On va créer la page à laquelle on sera redirigée au clique du bouton: **auth/logout.php**.

    ```
    <?php
    session_start();
    session_destroy();
    header('Location: ' .$router->url('login'));
    exit();
    ```











