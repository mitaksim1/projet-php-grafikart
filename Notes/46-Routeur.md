## Le routeur

Chaque chapitre sera suivi d'un autre qui va organiser un peu mieux le code, alors ce chapitre sera consacrée à ça.

1. Pour mieux organiser nos systèmes de routes, on pourrait faire quelque chose comme ça :

    ```
    $router = new Router(dirname(__DIR__) . '/views');
    $router-get('/blog', 'post/index', 'blog');
    $router-get('/blog/category', 'category/show', 'category');
    $router-run();
    ```

    - On créerait une classe Router qui ici serait instanciée, il prendra comme paramètre le chemin vers le dossier views.

    - On appelerait la méthode get() de Router et cette méthode prendrait 3 paramètres : le chemin de la route, le fichier à appeler dès l'appel à cette route et en troisième paramètre on donnerait un nom à cette route.

    - Une fois toutes les routes listées on appelerait la méthode **run()** qui s'occuperait de lancer le routeur.

2. On passe à la création de la classe Router.

    - On crée un fichier **src** où on va créer le fichier **Router.php** :

    ```
    <?php
    namespace App;

    use AltoRouter;

    class Router {

        /**
         * Pour que les autres méthodes puissent accèder à cette variable, on doit la créer comme une propriété de la classe
         * @var string
         */
        private $viewPath; 

        /**
         * On aura besoin d'AltoRouter 
         * @var AltoRouter
         */
        private $router;

        // Cette méthode prendra comme paramètre le chemin vers le dossier views
        public function __construct(string $viewPath)
        {
            // La propriété $viewPath, va stocker la valeur reçue dans le paramètre $viewPath
            $this->viewPath = $viewPath;
            // Pour pouvoir utiliser AltoRouter dans toutes les méthodes on doit l'intancier dès l'intance de la classe
            $this->router = new AltoRouter();
        }

        // Méthode qui va gérer les routes de l'application
        public function get(string $url, string $view, ?string $name = null): self
        {
            $this->router->map('GET', $url, $view, $name);
            // Méthode fluent permet de retourner la classe elle même et ainsi enchaîber les méthodes
            return $this;
        }

        public function run(): self
        {
            // match : vérifie si il y a une correspondance entre l'url et une des routes enregistrées
            $match = $this->router->match();
            // Ca envoi un tableau associatif contenant les correspondances
            $view = $match['target'];
            require $this->viewPath . $view . '.php';

            return $this;
        }
    }
    ```

3. Finalisé cette étape on essaie de voir la page en lançant le serveur et on reçoit un message comme quoi la classe App/Router n'est pas trouvée.

    C'est normal, il faut "expliquer" à Composer comment charger les classes.

    Dans le fichier **composer.json**, on va ajouter une nouvelle clé que l'on va nommer "autoload" en lui précisant que c'est un autoloader du type **psr-4** :

    ```
    "autoload": {
        "psr-4": {
            "App\\": "src/"
        }
    },
    ```

    Cette ligne dit à Composer qu'il va trouver le namespace **App** dans le dossier *src*.

4. Pour que cet ajout marche il va falloir recharger l'autoloader de composer :

    ```
    composer dump-autoload
    ```

5. Dans **index.php**, on va enlever l'instanciation à AltoRouter que l'on avait crée auparavant et on va faire un use de **App/Router** (si pas déjà fait).

6. On re teste et on a l'erreur suivante : *127.0.0.1:43896 [500]: GET /blog - require(): Failed opening required '/home/mataks/Documents/Projet-PHP-Grafikart/viewspost/index.php' (include_path='.:/usr/share/php') in /home/mataks/Documents/Projet-PHP-Grafikart/src/Router.php on line 43*.

    Si on vérifie à la ligne 43 du fichier Router.php on voit que l'on avait pas mis un "/" pour séparer l'url du chemin.

    ```
    require $this->viewPath . DIRECTORY_SEPARATOR . $view . '.php';
    ```

    - On met le DIRECTORY_SEPARATOR comme ça on ne sera pas embêté par le système opérationnel où onn sera.

7. On actualise la page, on a plus cette erreur mais une autre lié à la constante VIEW_PATH : *127.0.0.1:43934 [500]: GET /blog - require(): Failed opening required 'VIEW_PATH/layouts/header.php' (include_path='.:/usr/share/php') in /home/mataks/Documents/Projet-PHP-Grafikart/views/post/index.php on line 2*.

    Effectivemment on a changé notre code et on l'utilise plus.

    Il va falloir changer le require pour le header et pour le footer. Pour ne pas se répéter on va utiliser la bufferisation avec **ob_start**.

    ```
    public function run(): self
    {
        // match : vérifie si il y a une correspondance entre l'url et une des routes enregistrées
        $match = $this->router->match();
        // Ca envoi un tableau associatif contenant les correspondances
        $view = $match['target'];
        // ob_start va sauvegarder le require
        ob_start();
        require $this->viewPath . DIRECTORY_SEPARATOR . $view . '.php';
        // ob_get_clean va récupérer la ligne ou les lignes contenues entre ob_start et lui, dans ce cas il va sauvegarder dans $content le require ci-dessus
        $content = ob_get_clean();
        // Une fois le require récupéré on va appeler la vue (à créer) default.php
        require $this->viewPath . DIRECTORY_SEPARATOR . 'layouts/default.php';

        return $this;
    }
    ```

8. On passe alors, à la création de la vue **default.php**.

    - On récupère le code du header.php et du footer.php et le contenu sera le $content.

    ```
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Document</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-    +0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">
    </head>
    <body>
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
            <a href="#" class="navbar-brand">Mon Site</a>
        </nav>

        <div class="container mt-4">
            <?= $content ?>
        </div>
    </body>
    </html>
    ```

9. On peut effacer les requires dans **post/index.php** et dans **category/show.php**, on ne laisse que le *h1*.

10. On re teste et on récupère bien nos deux pages.

### Changer le titre pour chaque page

1. Dans le fichier *post/index.php* on va initialiser la variable $title = 'Mon Blog';

2. Ainsi on aura qu'à l'appeler dans le code HTML dans **default.php**.

    ```
    <title><?= $title ?></title>
    ```

3. Pour qu'il n'y ait pas d'erreur dans la page */blog/category*, on va créer une condition :

    ```
    <title><?= $title ?? 'Mon Site' ?></title>
    ```

### Performance de l'application

On va ajouter des nouvelles classes au fur et à mesure de l'avancement du projet, ce serait intéressant alors, de voir combien de temps la page met à se charger.

1. Dans **index.php** on va créer une constante :

    ```
    define('DEBUG_TIME', microtime(true));
    ```

    - **microtime** : nous donne le temps actuelle (datetime), mais avec les mille secondes.

2. Dans **default.php** on va créer un footer :

    ```
    <footer class="bg-light py-4 footer">
        <div class="container">
            Page générée en <?= 1000 * (microtime(true) - DEBUG_TIME) ?>ms
        </div>
    </footer>
    ```
3. On teste et on voit bien sur notre page le temps qui a mis pour que la page se charge.

    On a rajouté la méthode **sleep(2)** tout de suite après juste pour montrer qu'on peut le retarder de 2s si on veut.

4. On a arrondi la valeur.

    ```
    Page générée en <?= round(1000 * (microtime(true) - DEBUG_TIME)) ?>ms
    ```

5. On ajoute un peu de style à la page pour mieux organiser les éléments.

    ```
    <html lang="fr" class="h-100">

    <body class="d-flex flex-column h-100">

    <footer class="bg-light py-4 footer mt-auto">
    ```

6. Comme cette constante serve juste pour lé débug en mode dev, on pourra créer cette condition pour qu'elle s'affiche :

    ```
     <?php if (defined('DEBUG_TIME')): ?>
        Page générée en <?= round(1000 * (microtime(true) - DEBUG_TIME)) ?>ms
    <?php endif ?>
    ```

    De cette façon, si on a pas besoin de débugger le code, on pourra commenter la définition de la constante et on n'aura pas des erreurs dans notre code.

7. en parlant de débugage, on va installer **var-dumper** de Symfony.

    ```
    composer require symfony/var-dumper
    ```

8. On va aussi installer [**whoops**](https://github.com/filp/whoops), une librairie qui affiche les erreurs d'une façon plus jolie.

    ```
    composer require filp/whoops:2.3.1
    ```

9. Pour que whoops marche, on doit écrire ces trois lignes dans le fichier racine du projet.

    ```
    $whoops = new \Whoops\Run;
    $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
    $whoops->register();
    ```

    Maintenant, chaque fois qu'une Exception sera capturé dans notre système elle sera traité et affiché avec whoops.






