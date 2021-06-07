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


