## Chapitre 49 : Pagination

On souhaite ajouter comme paramètre de l'url la variable page qui va contenir la page où on est :

Ex . : localhost:8000/?page=2

Pour faire ça, il va falloir calculer le nombre d'articles que l'on a pour savoir combien de pages il y en aura au maximun.

1. Avant de faire la requête pour récupérer les posts, on va calculer le nombre d'articles.

    ```
    $count = $pdo->query('SELECT COUNT(id) FROM post')->fetch(PDO::FETCH_NUM)[0];
    ```

    - On va compter tous les id's qui correspondre au nombre total d'articles

    - Ce résultat va nous retourner un pdo statement, on peut alors faire un fetch directement et on va demander à ce qu'il nous retourne un tableau numérique, les indexés par le numéro.

    - On ne récupérera que la première colonne, ainsi dans une seule ligne on peut récupérer le nombre d'articles

2. Attention, si on fait un **dd($count)** on voit que l'on récupère une string "50", on va forcer le type à recevoir dans la requête en forçant un entier :

    ```
    (int)$pdo->query('SELECT COUNT(id) FROM post')->fetch(PDO::FETCH_NUM)[0];
    ```
3. Initialisation de la page actuelle :

    ```
    $currentPage = (int)$_GET['page'];
    ```

4. On fait un dd($currentPage) pour voir ce qu'est retourné :

    - Si on met n'importe quoi, on a comme réponse 0

    - Si on ne met rien après l'url on a le message : Undefined index page

5. Alors, on va préciser que si aucune 'page' n'est envoyé sa valeur par défaut sera 1.

    ```
    $currentPage = (int)$_GET['page'] ?? 1;
    ```

6. Pour traiter le cas où la page envoyé est 0 (on envoi n'importe quoi) on va envoyer une exception.

    ```
    if ($currentPage <= 0) {
        throw new Exception('Numéro de page invalide');
    }
    ```

7. Maintenant qu'on a la page courante et le nombre d'articles de notre bdd, il suffit de faire le calcul pour savoir combien de pages on en aura..

    ```
    $pages = $count / 12;
    ```

    - Si on débug $pages, ça va pas nous donner un nombre rond. On ne peut pas mettre 4.16 pages. On va donc arrondir le résultat au chiffre supérieur.

    ```
    $pages = ceil($count / 12);
    ```

8. On va créer une exception au cas ou la page demandée n'existe pas.

    ```
    if ($currentPage > $pages) {
        throw new Exception('Cette page n\'existe pas');
    }
    ```

    On ne regroupe pas les deux conditions, parce que si jamais la page demandé est invalide on ne fais pas requête économisant la performance côté serveur.

9. On va stocker le nombre d'aricles souhaités dans une variable.

    ```
    $perPage = 12;
    $pages = ceil($count / $perPage);
    ```

10. On peut utiliser cette variable dans la requête aussi. Ici, pas besoin de l'échapper, parce que ce sera nous qui allons la gérer, pas les utilisateurs.

    ```
    $query = $pdo->query('SELECT * FROM post ORDER BY created_at DESC LIMIT ' . $perPage);
    ```

11. On teste en mettant une page qui n'existe pas et on a bien le message.


12. On va gérer le OFFSET de la page, pour rappel offset nous permet de ne retourner qu'une partie des résultats, si on a un offset de 12 on va zapper les 12 premiers articles, on sera sur la page 2, si on a un offset de 0 on sera sur la page 1.

    ```
    $offset = $perPage * ($currentPage -1);
    ```

    - **$currentPage -1** : Si on est à la page 1 => 1 - 1 = 0, alors 12 * 0 = 0 on sera donc à la page 1 et ainsi de suite...

13. On peut ajouter $offset à notre requête :

    ```
    $query = $pdo->query("SELECT * FROM post ORDER BY created_at DESC LIMIT $perPage OFFSET $offset");
    ```

    - On a mies les "" pour ne pas avoir besoin de concaténer les variables.

    - $offset sera une page choisi par l'utilisateur, mais comme il est lié à $currentPage et on a précisé que $currentPage est un entier, alors on sait qu'on doit recevoir un entier, pas la peine donc de l'échapper.

14. On teste, et on voit sur la page 2 que l'on reçoit les articles de 2007, sur la page 5 on n'a que deux articles, ça marche :-)

### Afficher la pagination en bas 

1. On crée le code html suivant :

    ```
    <div class="d-flex justify-content-between my-4">
        <?php if ($currentPage > 1): ?>
            <a href="<?= $router->url('home') ?>?page=<?= $currentPage - 1 ?>" class="btn btn-primary">&laquo; Page précédente</a>
        <?php endif ?>
        <?php if ($currentPage < $pages): ?>
            <a href="<?= $router->url('home') ?>?page=<?= $currentPage + 1 ?>" class="btn btn-primary"> Page suivante &raquo;</a>
        <?php endif ?>
    </div>
    ```

    - **&laquo &raquo** : on échappe les signes < > pour pas qu'il pense que c'est une ouverture html.

2. On teste et ça marche!

3. Pour corriger le lien de la page 1 et le mettre à droite le mec a ajouté *ml-auto* à la classe du lien , mais pour mois ça ne pas marché.

    C'est juste du design, alors c'est pas grave je laisse comme ça.

4. Un autre détail, quand on est à la page 1 on a pas besoin de le préciser, parce que c'est la page principale. On va faire en sorte que la page ne soit précisé qu'à partir de la page 2.

    ```
    <?php
    $link = $router->url('home');
    if ($currentPage > 2) $link .= '?page=' . ($currentPage - 1);
    ?>
    ```

5. On teste et ça ne marchait pas parce que j'avais oublié d'entourer cette partie du code, aff...

    ```
    $currentPage = (int)($_GET['page'] ?? 1);
    ```

6. Si l'utilisateur s'amuse à mettre d'autres choses après l'url de la page, ça peut impacter au niveau du référencement, alors pour éviter cela :

    ```
    $page = $_GET['page'] ?? 1;

    $currentPage = (int)$page;
    ```

    ```
    if (!filter_var($page, FILTER_VALIDATE_INT)) {
        throw new Exception('Numéro de page invalide');
    }
    ```

7. Maintenant, si on réactualise la page on a le message comme quoi le numéro est invalide;

8. On ne veut plus qu'on puisse écrire */?page=1* si c'est le cas on veut que la page soit redirigé vers */* :

    ```
    if ($page === '1') {
        header('Location: ' .$router->url('home'));
        http_response_code((301));
        exit;
    }
    ```

    Pour tester, aller sur la console et vérifier les données reçues dans l'onglet réseau.

    





