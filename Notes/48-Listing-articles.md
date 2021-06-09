## Chapitre 48 - Listing d'articles

Notre objectif pour ce chapitre est de lister les articles dans notre page d'accueil en créant un système de pagination les listant 12 par 12.

1. Notre page d'accueil ce situe dans **views/post/index.php**.

    Pour récupérer les 12 derniers articles on va devoir faire une requête SQL, donc on doit instancier PDO.

    On peut aller récupérer le code dans le fichier **fill.php**.

    ```
    $pdo = new PDO('mysql:dbname=tutoblog;host=127.0.0.1', 'root', 'Root*', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    ```

2. On fait notre requête :

    ```
    $pdo->query('SELECT * FROM post ORDER BY created_at DESC LIMIT 12');
    ```

    - Ensuite on stocke le résultat de la requête dans  un variable :

    ```
    $query = $pdo->query('SELECT * FROM post ORDER BY created_at DESC LIMIT 12');
    ```

3. Comme ça on pourra récupérer ce résultat en forme de tableau faisant un **fetchAll**.

    ```
    $posts = $query->fetchAll(PDO::FETCH_OBJ);
    ```

    - On précise qu'on veut récupérer ce tableau en forme d'objets.

4. On crée la structure HTML où on va afficher la liste des articles :

    ```
    <div class="row">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Titre</h5>
                </div>
            </div>
        </div>
    </div>
    ```

    - **col-md-3** : permet d'avoir trois colonnes
    - **card** : permet d'afficher les articles dans une carte

5. Comme on veut afficher 12 articles par page, il va falloir créer une boucle, pour que chaque article soit dans une carte.

    ```
    <div class="row">
    <?php foreach ($posts as $post): ?>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Titre</h5>
            </div>
        </div>
    </div>
    <?php endforeach ?>
    ```

6. On aura maitenant qu'à appeler le titre comme suit :

    ```
    <h5 class="card-title"><?= $post->name ?></h5>
    ```

7. On teste et les articles s'affichent bien sur chaque carte.





   
