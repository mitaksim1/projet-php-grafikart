## Chapitre 47 : Remplir la base de données

Dans ce chapitre on va voir comment remplir la base de données avec des fausses données pour qu'on puisse faire des testes plus poussés.

On va créer un script qui va s'occuper de remplir automatiquement notre base de données.

1. Pour ne pas mélanger ce script avec les autres fichiers, on va le mettre dans un dossier que l'on va bommer **commands**.

    ```
    <?php
    $pdo = new PDO('mysql:dbname=tutoblog;host=127.0.0.1', 'root', 'Root*', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // Pour que ça marche, il faut pas qu'il tienne compte des clé étrangères
    $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');

    // On commence par effacer les données que l'on avait saisies manuellement
    $pdo->exec('TRUNCATE TABLE post_category');
    $pdo->exec('TRUNCATE TABLE post');
    $pdo->exec('TRUNCATE TABLE category');
    $pdo->exec('TRUNCATE TABLE user');

    // Une fois que les tables sont vidées on peut réabiliter les clés étrangères
    $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');

    // Maintenant que la bdd est vide, on pourra la remplir avec des faux articles
    for ($i = 0; $i < 50; $i++) {
        $pdo->exec("INSERT INTO post SET name='Article #$i', slug='article-$i', created_at='2020-06-08 14:00:00', content='lorem ipsum'");
    }
    ```

2. On teste le fichier en tapant sur le terminal :

    ```
    php commands/fill.php
    ```

3. Si on va dans */adminer.php* sur la table **post** on va les 50 faux articles que l'on a crée.

4. A ce moment le mec nous conseille d'utiliser Faker, mais comme cette librairie est obsolète il va falloir que je trouve une autre solution.

    A o'Clock j'ai vu **NelmioAlice** qui est basé sur Faker, je vais essayer de l'utiliser à la place;

    - Je commence par installer nelmio/alice :

    ```
    composer require --dev nelmio/alice
    ```

    J'ai pas réussi à utiliser Nelmio/Alice, je l'ai supprimé et installé **fakerphp/faker** qui me semble marcher un peu comme Faker. Je teste!

    ```
    composer require --dev fakerphp/faker
    ```

5. Je suis le pas à pas du mec jusqu'au test pour voir si ça marche et ça a marché!!!

    Les étapes à suivre après l'installation de faker :

    - Faire un require d'autoload :

    ```
    require dirname(__DIR__) . '/vendor/autoload.php';
    ```

    - Appeler Faker comme suit :

    ```
    $faker = Faker\Factory::create('fr_FR');
    ``` 

    - Changer les données que l'on avait crée en dur par :

    ```
    for ($i = 0; $i < 50; $i++) {
        $pdo->exec("INSERT INTO post SET name='{$faker->sentence()}', slug='{$faker->slug}', created_at='{$faker->date} {$faker->time}', content='{$faker->paragraphs(rand  (3,15), true)}'");
    }
    ```

    - On teste en lançant la commande :

    ```
    php commands/fill.php
    ```

    - Dans Adminer, je clique sur **Select data** et ça a marché, on a bien les fausses données dans notre bdd.

6. On va faire la même chose pour les catégories.

    ```
    for ($i = 0; $i < 5; $i++) {
        $pdo->exec("INSERT INTO category SET name='{$faker->sentence(3)}', slug='{$faker->slug}'");
    }
    ```

7. Maintenant il faut faire le lien entre la table post et la table category.

    - On commence par stocker les résultats trouvées dans la table post et category dans un tableau :

    ```
    $posts = [];
    $categories = [];
    ```

    - Avec la méthode **lastInsertId()**, on peut récupérer le dernier id inséré dans la bdd, ainsi on récupère tous les éléments au fur et à mesure dans chaque tableau :

    ```
    for ($i = 0; $i < 50; $i++) {
        $pdo->exec("INSERT INTO post SET name='{$faker->sentence()}', slug='{$faker->slug}', created_at='{$faker->date} {$faker->time}', content='{$faker->paragraphs(rand  (3,15), true)}'");
        // Quand on fait un INSERT on peut récupérer le dernier id inseré
        $posts[] = $pdo->lastInsertId();

    }
    ```
    On fait la même chose pour category.

8. Maintenant, qu'on a ces tableaux là, on pourra boucler dessus et faire l'association entre les deux tables :

    - On commence par boucler le tableau $posts, pour chaque post on veut qu'une catégorie soit attribuée, alors on utilise la méthode randomElements() de faker qui va faire ça pour nous;

    Cette méthode prend comme paramètre le tableau où il faudra trouver l'élément aléatoire et il va gérer un entier entre 0 et le nombre des catégories qu'il y en a dans le tableau.

    - Après pour chaque catégorie aléatoire, il va faire un insert dans la table post_category

    ```
    foreach($posts as $post) {  
        $randomCategories = $faker->randomElements($categories, rand(0, count($categories)));
        foreach ($randomCategories as $category) {
            $pdo->exec("INSERT INTO post_category SET post_id=$post, category_id=$category");  
        }
    }
    ```

9. On teste en lançant la commande *php commands/fill.phph*, j'ai bien les données comme le mec sauf que les id's des catégories dans post_category ne vont que jusqu'à 3 au lieu de 5 (?) et ils ne sont pas très aléatoire puisque ils de suivent les un après les autres. 

10. On va créer aussi UN UTILISATEUR POUR TESTER.

    ```
    $password = password_hash('admin', PASSWORD_BCRYPT);
    $pdo->exec("INSERT INTO user SET username='admin', password='$password'");
    ```

    On relance la commande, on vérifie dans notre bdd et il y a bien l'user admin qui a été crée.

11. Pour simplifier les choses et se préparer pour le prochain chapitre on va définir la page */blog* comme étant la page d'accueil de notre site.

    Dans **public/index.php** :

    ```
    $router->get('/', 'post/index', 'home');
    ```


