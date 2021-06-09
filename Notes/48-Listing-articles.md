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

8. Pour éviter que l'utilisateur n'insère du html dans le titre on va échapper les données.

    ```
    <h5 class="card-title"><?= htmlentities($post->name) ?></h5>
    ```

9. On récupère aussi le contenu de l'article.

    ```
    <p><?= htmlentities($post->content) ?></p>
    ```

10. Pour respecter les sauts de lligne on rajoute un **nl2br** au contenu.

    Attention à l'ordre, il faut d'abord échapper la varibale pour après appeler le nl2br.

    ```
    <p><?= nl2br(htmlentities($post->content)) ?></p>
    ```

11. On va ajouter un autre paragraphe où on va mettre notre bouton en forme de lien;

    ```
    <p>
        <a href="" class="btn btn-primary">Voir plus</a>
    </p>
    ```

### Afficher un extrait de l'article

1. On ne veut pas afficher tout l'article, mais juste un extrait.

    Une bonne solution c'est de créer une classe qui va s'occuper de ça :

    - Dans **src** on créee un nouveau dossier **helpers/Text.php**.

    - Dans la classe Text :

    ```
    <?php
    namespace App\Helpers;

    class Text {

        public static function excerpt(string $content, int $limit = 60)
        {
            if (mb_strlen($content) < $limit) {
                // si inférieur on retourne le contenu
                return $content;
            }
            // Si supérieur on appelle la méthode substr, on passe le contenu à couper et on donne les mésures que l'on souhaite
            // Les trois points c'est juste pour signaler à l'utilisateur qu'il y a une suite
            return substr($content, 0, $limit) . '...';
        }
    }
    ```

2. On a qu'à appeler la méthode excerpt dans notre code html :

    ```
    <p><?= nl2br(htmlentities(Text::excerpt($post->content))) ?></p>
    ```

3. On teste!

4. Quand on vérifie on voit qu'ils on coupé une partie du mot, pour éviter ça on peut utilise la fonction **mb_strpos()**.

    Cette fonction va prendre comme paramètre la string où on veut chercher ce que sera mis en deuxième paramètre et le troisième ce sera à partir d'où faire la recherche.

    ```
    $lastSpace = mb_strpos($content, ' ', $limit);
    ```

5. Maintenant, dans la fonction *substr* que l'on avait crée, au lieu de couper à $limit, on va couper à $lastSpace.

    ```
    return mb_substr($content, 0, $lastSpace) . '...';
    ```

    - Comme on veut gérer les strings unicodes, on le précise pour la fonction *substr* aussi.

6. On teste et c'est beaucoup mieux avec les mots entiers.

### Création de la classe Post

Pour éviter d'écrire un code trop verbeux comme dans cette ligne : *<p><?= nl2br(htmlentities(Text::excerpt($post->content))) ?></p>* 
    
1. On va créer une nouvelle classe Post qui va nous permettre de répresenter un article qui vient de notre bdd.

    ```
    <?php
    namespace App\Model;

    class Post {

        private $id;

        private $name;

        private $content;

        private $created_at;

        private $categories = [];
    }
    ```

2. Comme on est en train de créer la classe Post, maintenant au niveau de la requête, on ne veut plus faire un **FETCH_OBJ**, mais un **FETCH_CLASS**.

    ```
    $posts = $query->fetchAll(PDO::FETCH_CLASS, Post::class);
    ```
3. Pour confirmer si on reçoit toujours les données on fait un dump juste dans notre code html;

    ```
    <?php dump($posts);exit; ?>
    ```

    - On les reçoit bien, mais come on a définit les propriétés comme étant privées on ne pourra plus les accèder avec la syntaxe *$posts->name*.

    On pourrait passer les propriétés en public, mais on a une autre façon de le faire.

4. On peut créer une méthode **getter** qui va s'occuper d'accèder à la propriété *name*.

    ```
    public function getName(): ?string
    {
        return $this->name;
    }
    ```

5. On crée la méthode **getExcerpt()** qui va traiter la propriété $content directement pour quelle ne retourne qu'un extrait de son contenu.

    ```
    public function getExcerpt(): ?string
    {
        if ($this->content === null) {
            return null;
        }
        return Text::excerpt($this->content, 60);
    }
    ```

6. On pourra rajouter d'autres logiques à ce code, comme le *htmlentities* et le *nl2br* dans ce code aussi.

    ```
    return nl2br(htmlentities(Text::excerpt($this->content, 60)));
    ```

7. Maintenant, on peut changer le code html par :

    ```
    <h5 class="card-title"><?= htmlentities($post->getName()) ?></h5>
    <p><?= $post->getExcerpt() ?></p>
    ```

8. On actualise la page et ça marche normalement.

### Récupération de la date

1. Si on veut ajouter la date à notre article, on pourrait faire quelque chose comme ça :

    ```
    <p class="text-muted"><?= $post->created_at ?></p>
    ```

    - Le problème c'est qu'on allait recevoir une date en forme de chaîne de caractère, il va falloir la convertir en DateTime.

2. On va donc, créer une focntion qui fera ça pour nous, on n'aura qu'à l'appeler après.

    ```
    public function getCreatedAt(): DateTime
    {
        return new DateTime($this->created_at);
    }
    ```

3. On appele la méthode créée dans le code html.

    ```
    <p class="text-muted"></p>
    ```

4. 











   
