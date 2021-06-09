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
    <p class="text-muted"><?= $post->getCreatedAt()->format('d/m/Y') ?></p>
    ```

4. On change l'affichage de la date avec le mois écrit en lettres.

    ```
    <p class="text-muted"><?= $post->getCreatedAt()->format('d F Y') ?></p>
    ```

### Créer un lien qui mène vers la page de l'article

1. Dans **index.php** on commence par créer la nouvelle route.

    ```
    $router->get('/blog/[*:slug]-[i:id]', 'post/show', 'post');
    ```

2. On passe le chemin vers la route au lien que l'on avait crée auparavnt.

    ```
    <a href="<?php url('post', ['id' => $post->getId(), 'slug' => $post->getSlug() ]) ?>" class="btn btn-primary">Voir plus</a>
    ```

    - On a aps encore codé la méthode *url()* ni les méthodes *getId()* et *getSlug()*.

3. On va alors, les créer :

    ```
    public function getslug(): ?string
    {
        return $this->slug;
    }

    public function getID(): ?int
    {
        return $this->id;
    }
    ```

4. Il nous reste à gérer la méthode url(). On avait vu que pour gérer les routes on a **AltoRouter**.

    - Dans AltoRouter on a la méthode **generate()** qui nous permettra de gérer nos routes comme on a configurée, le soucis c'est que dans la méthode run() on a pas donné accès à cette méthode. alors si on veut l'utiliser dans nos fichier **Router.php**, il va falloir chnager le code de cette méthode en ajoutan la ligne suivante:

    ```
    $router = $this->router;
    ```

    - On peut maintenant accèder à la route comme suit:

    ```
    <a href="<?php $router->generate('post', ['id' => $post->getId(), 'slug' => $post->getslug() ]) ?>" class="btn btn-primary">Voir plus</a>
    ```

5. Quand j'ai testé, ça ne se passait rien. J'avais quelques erreurs au niveau du camelCase dans le nom de quelques méthodes, mais ce n'était pas ça.

    J'ai essayé de mettre une autre route pour voir si c'était quelque chose au niveau de la route que j'avais mis, parce que ce n'était pas ailleurs et je ne voyais rien d'autre et ça marchait si je mettais */blog/category* par exemple, alors en re analysant bien le code j'ai vu que j'avais écris *<?php* au lieu de *?p=*.

    Du coup, j'ai actualisé la page et je voyais bien dans l'url le bon chemin : 
    
    *http://localhost:8000/blog/ut-vitae-dolorum-exercitationem-culpa-dolor-eius-dolor-tempora-33*

    Bien sûr on a une erreur parce qu'on a pas encore créer la page de vue */post/show*.

6. Notre idée initiale c'était d'appeler une méthode **url()**.

    Alors, on va créer cette méthode dans la classe Router.

    ```
    public function url(string $name, array $params = [])
    {
        return $this->router->generate($name, $params);
    }
    ```

7. Maintenant qu'on a crée cette méthode dans le run() on ne vas plus envoyer le $routes, on va envoyer $this (l'objet courant).

    ```
    $router = $this;
    ```

8. On peut appeler la méthode comme on avait fait avant :

    ```
    <a href="<?= $router->url('post', ['id' => $post->getID(), 'slug' => $post->getSlug()]) ?>" class="btn btn-primary">Voir plus</a>
    ```

9. On actualise la page et ça continue à marcher.

L'avantage de cette approche, c'est qu'on ne fait plus appel à AltoRouter en dehor du fichier Routes.php. 

Si un jour, on décide de changer de router, on aura qu'à changer ce fichier.

10. Une dernière chose que l'on peut faire c'est de factoriser le code des cartes pour les articles dans un fichier à part, ainsi si un jour on a besoin on pourra les ré utiliser ailleurs.

    - On crée dans le même dossier **views/post** le fichier *card.php*.

    - On passe tout le code concernant les cartes dans ce fichier.

    ```
    <div class="card">
        <div class="card-body">
            <h5 class="card-title"><?= htmlentities($post->getName()) ?></h5>
            <p class="text-muted"><?= $post->getCreatedAt()->format('d F Y') ?></p>
            <p><?= $post->getExcerpt() ?></p>
            <p>
                <a href="<?= $router->url('post', ['id' => $post->getID(), 'slug' => $post->getSlug()]) ?>" class="btn btn-primary">Voir plus</a>
            </p>
        </div>
    </div>
    ```

    - On n'a qu'à appeler ce fichier dans **views/post/index.php**.

    ```
    <div class="row">
        <?php foreach ($posts as $post) : ?>
            <div class="col-md-3">
                <?php require 'card.php' ?>
            </div>
        <?php endforeach ?>
    </div>
    ```

11. Pour finir on ajoute juste une marge en bas sous les cartes.

    ```
    <div class="card mb-3">
    ```

















   
