## Création de la structure

On veut pouvoir accèder à l'url localhost:8000/blog et localhost:8000/blog/category;

On va avoir besoin donc d'un système qui va gérer les routes, nous on va utiliser **Altorouter**.

1. Installation d'altorouter, on précise la version pour avoir la même que le cours.

    ```
    composer require altorouter/altorouter:1.2.0
    ```

2. On va créer le dossier **public** où on va mettre le fichier racine de notre projet : **index.php**.

    C'est dans ce fichier que l'on va appeler altorouter.

    ```
    <?php
    require '../vendor/autoload.php';
    ```

3. On va démarrer l'autorouter en l'intanciant :

    ```
    $router = new AltoRouter();
    ```

4. Une fois AltoRouter instancié, on pourra créer la première route :

    ```
    $router->map('GET', '/blog', function() {

    });
    ```

    - On appelle la méthode map d'AltoRouter qui prendra comme premier paramètre la méthode HTTP

    - En deuxième paramètre la route à laquelle on souhaite accèder

    - En troisième paramètre la méthode à appeler avec les instructions à suivre pour cette route

5. Dans la fonction qui sera appelé par la route on mettra la vue que l'on veut afficher.

    On va donc créer le dossier **views** qui va contenir les fichiers des vues, on commence par le fichier qui va contenir les articles de l'application.

    Pour que les vues soient bien organisées et on se retrouve facilement, on va créer un sous-dossier **post** qui contiendra à son tour le fichier **index.php**.

    Ce fichier va contenir le code html de la page des articles.

6. On fait de même pour le fichier des catégories : **views/category/show.php**.

7. Une fois que ces fichier sont crées, on pourra finir la configuration de la route */blog*.

    ```
    $router->map('GET', '/blog', function() {
        require dirname(__DIR__) . 'views/post/index.php';
    });
    ```

8. On crée une autre route pour la route */blog/category*.

    ```
    $router->map('GET', '/blog/category', function() {
        require dirname(__DIR__) . 'views/category/show.php';
    });
    ```

9. On voit que le code se répète au niveau du *dirname* on peut le factoriser en créant une constante que l'on ira appeller à la palce.

    ```
    define('VIEW_PATH', dirname(__DIR__) . '/views');
    ```

    - On aura qu'appeler maintenant la constante VIEW_PATH :

    ```
    $router->map('GET', '/blog', function() {
        require  VIEW_PATH . '/post/index.php';
    });
    ````

10. On vérifie si la route appelé correspond à une de ces deux routes :

    ```
    $match = $router-match();
    $match['target']();
    ```

    - Cette deuxième ligne correspond à : "On va chercher la valeur contenue dans la clé $_GET['target'] (correspond à la fonction callback contenue dans la route) et on l'appelle, c'est pour ça que l'on a mis les ().

11. Si on teste en lançant le serveur et en allant à la page */blog*, on voit bien le *h1* Mon Blog.

    ```
    php -S localhost:8000 -t public 
    ```

12. Si on tape *localhost:8000/blog/category*, on tombe bien sur la page.

## Structure HTM de base / lien Bootstrap

1. Pour améliorer un peu le design des pages, on va importer le lien [Bootstrap](https://getbootstrap.com/docs/5.0/getting-started/introduction/).

    - Dans **views** on va créer un nouveau dossier/fichier **layouts/header.php**. 
    
    Ce fichier va contenir le code html de base de notre site et c'est ici que l'on vient coller le lien à Bootstrap.

    ```
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">
    ```

2. On va créer une navbar dans notre page.

    ```
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <a href="#" class="navbar-brand">Mon Site</a>
    </nav>
    ```

3. On va créer une *div* qui sera le container de l'appication.

    ```
    <div class="container mt-4">
    ```

4. On crée le fichier **layouts/footer.php** qui va contenir la fermeture de la *div* container et la fermeture de la page HTML.

    ```
        </div>
    </body>
    </html>
    ```

5. Maintenant, on pourra appeler ces fichiers dans nos pages.

    ```
    <?php require VIEW_PATH . '/layouts/header.php'; ?>
    <?php require VIEW_PATH . '/layouts/footer.php'; ?>
    ```

6. Si on actualise la page, on va voit que le rendu c'est plus joli.

## Base de données mySQL

Comme interface de visualisation, on va utiliser [Adminer](https://www.adminer.org/#download) qui n'utilise qu'un seul fichier PHP pour gérer les requêtes.

1. On fait le download d'Adminer en cliquant sur la deuxième ligne où c'est marqué **english only**.

2. Une fois téléchargé, on va mettre ce fichier dans le dossier **public**, on le renomme juste comme **adminer.php**.

3. Maintenant, si on va à *localhost:8000/adminer.php* on voit l'interface d'Adminer.

4. On rentre les identifiants de notre base de données MySQL.

    A ce niveau j'ai eu un soucis en rapport avec *mysql*, *pdo_mysql* et un autre que je ne me souviens plus. Bizarre, vu qu'on avait activé les extensions lors de la présentation du projet.

    J'ai essayé plein de choses, ça ne marchait pas, jusqu'à ce que j'essaie la moitié de ce que dit ce mec sur [Youtube](https://www.youtube.com/watch?v=OnP8YPS6L-I).

    Je redémarre la VM et le message à changé pour *Access denied for the user 'root'@'localhost'*, j'essaie alors de comprendre pourquoi et de corriger cette erreur aussi.

    A la fin, ce qu'à marché, c'était d'effacer l'utilisateur root et le re installer comme conseillé dans cette réponse : https://askubuntu.com/questions/1029961/with-adminer-php-i-get-a-access-denied-for-user-rootlocalhost-error-ubuntu.

    Bien sûr, ça a marché à la moitié, au moment de recréer l'utilisateur root j'ai eu une erreur comme quoi mon mot de passe ne correspondait pas à la politique actuelle. J'ai du alors, faire d'autres recherches et j'ai trouvé cette réponse qui ma aidé à régler mon problème : https://stackoverflow.com/questions/43094726/your-password-does-not-satisfy-the-current-policy-requirements.

    J'ai changé le **validate_password.policy** à low, le **validate_password.number_count** à 0, le **validate_password.length** à 4 le reste j'ai laissé par défaut. 

    Je re teste et ça a marché, ouf!

5. Création de la base de données **tutoblog**.

6. Pour créer nos requêtes on va pas utiliser l'interface d'Adminer, on va les créer directment dans notre projet, ça nous permettra d'avoir plus de contrôle, on pourra les modifier au fur et à mesure.

    - A la racine du projet, on va créer un fichier **bdd.sql**.

    - On a installé l'extension SQL Server(mssql).

7. Dans le fichier sql, on va commencer à créer nos premières tables.

    ```
    CREATE TABLE post (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        name VARCHAR(255) NOT NULL,
        slug VARCHAR(255) NOT NULL,
        content TEXT(650000) NOT NULL,
        created_at DATETIME NOT NULL, 
        PRIMARY KEY (id)
    )
    ```

    On copie ce code, on va dans Adminer, on clique sur **SQL Command** à gauche de l'interface et on colle la requête dans l'endroit prévu.

    On l'exécute et on voit que la table **post** a bien été crée.

8. On passe à la création de la table **category** en suivant les mêmes pas.

    ```
    CREATE TABLE category (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        name VARCHAR(255) NOT NULL,
        slug VARCHAR(255) NOT NULL,
        PRIMARY KEY (id)
    )
    ```

9. Maintenant, il va falloir lier la table post avec la table des catégories vu que chaque article pourra appartenir à plusieurs catégories et chaque catégories pourra avoir plusieurs articles la realtion sera du type **NN**. Il va falloir alors créer une table intermédiaire.

    ```
    CREATE TABLE post_category (
        post_id INT UNSIGNED NOT NULL,
        category_id INT UNSIGNED NOT NULL,
        PRIMARY KEY (post_id, category_id),
        CONSTRAINT fk_post
            FOREIGN KEY (post_id)
            REFERENCES post (id)
            ON DELETE CASCADE
            ON UPDATE RESTRICT,
        CONSTRAINT fk_category
            FOREIGN KEY (category_id)
            REFERENCES category (id)
            ON DELETE CASCADE
            ON UPDATE RESTRICT
    )
    ```

10. On veut aussi créer un login pour que les utilisateurs puissent se connecter à la partie administration.

    ```
    CREATE TABLE user (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        username VARCHAR(255) NOT NULL,
        password VARCHAR(255) NOT NULL,
        PRIMARY KEY (id)
    )
    ```

11. Une fois toutes les tables crées, on peut commencer à les remplir, on commence par la table **category**. Il suffit de cliquer sur *New Item* et on insère les données que l'on veut. 

    cf. bdd pour voir les données saisies.

12. On crée des fausses données pour la table **post** aussi.

13. On crée des fausses données pour la table **post_category**.

14. Maintenant on pourra faire un teste pour voir si ça marche.

    En faisant la requête suivante, on retrouve bien tous les post liés à l'article dont l'id est 1.

    ```
    SELECT *
    FROM post_category pc
    WHERE pc.post_id = 1
    ```

15. Si on ajoute une autre commande sql, on pourra récupérer aussi la catégorie correspondant à l'article :

    ``` 
    SELECT *
    FROM post_category pc
    LEFT JOIN category c ON pc.category_id = c.id
    WHERE pc.post_id = 1
    ```












    




