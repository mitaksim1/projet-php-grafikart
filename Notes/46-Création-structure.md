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









    




