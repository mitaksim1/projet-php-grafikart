## Chapitre 51 : page article

Dans ce chapitre on va mettre en place la vue qui va nous permettre de voir un article.

On avait déjà crée la base de notre code dans **public/index.php** : *->get('/blog/[*:slug]-[i:id]', 'post/show', 'post')*

1. En se basant sur la route que l'on avait crée, on va créer notre vue **show.php** dans le dossier **views/post**.

2. Dans **Reouter.php** on fait un dump($match) avec l'url d'un article précis pour voir ce qu'il nous retourne. 

    Il nous retourne bien un tableau avec les params : slug et id. On aura besoin de ces informations pour créer notre vue;

    Alors, dans la méthode **run()** :

    ```
    $params = $match['params'];
    ```

3. Maintenant dans le fichier *show.php* on aura accès à la variable $params.

    ```
    <?php
    dd($params);
    ```

3. On récupère alors ces données :

    ```
    $id = (int)$params['id'];
    $slug = $params['slug'];
    ```

4. Avec ces données on pourra récupérer les articles qui correspondent.

    ```
    $pdo = Connection::getPDO();
    
    $query = $pdo->prepare('SELECT * FROM post WHERE id = :id');
    
    $query->execute(['id' => $id]);

    $post = $query->fetchAll(PDO::FETCH_CLASS, Post::class)[0];
    ```

5. On teste en faisant un *dd($post);* et on reçoit bien l'article correspondant à l'id 33 (l'article que j'avais cliqué).

6. Le problème avec cette façon de faire c'est qui si jamais l'utilisateur met un id qui n'existe pas, on va avoir une erreur : *Undefined offset: 0*;

    On va changer alors, le mode *fetchAll* par *fetch* qui va nous retourner un seul résultat. Comme fetch n'accepte pas ces deux )paramètres, il va falloir que l'on les reçoit dans une autre variable :


    ```
    $pdo = Connection::getPDO();
    
    $query = $pdo->prepare('SELECT * FROM post WHERE id = :id');
    
    $query->execute(['id' => $id]);

    $query->setFetchMode(PDO::FETCH_CLASS, Post::class);

    $post = $query->fetchAll(PDO::FETCH_CLASS, Post::class)[0];
    ```

7. Maintenant si jamais il y a un id qui n'existe pas, ça va nous retourner *false*, dans ce cas on va créer une condition qui va générer une Exception si c'est le cas.

    ```
    if ($post === false) {
        throw new Exception('Aucun article ne correspond à cet ID');
    }
    ```

8. Pour gérer le cas du slug :

    - On commence par typer ce qui nous retourne la variable $post. 

    ```
    /**
     * On peut typer cette variable comme suit :
     * @var Post|false
     */
    $post = $query->fetch();
    ```

    - On cherche la méthode getSlug() et on la débug pour voir si ça marche.

    ```
    dd($post->getSlug());
    ```

    On reçoit bien le slug de l'article choisi.

9. Maintenant, si le slug ne correspond pas à l'id choisi :

    ```
    if ($post->getSlug() !== $slug) {
        
        $url = $router->url('post', ['slug' => $post->getSlug(), 'id' => $id]);
        dd($url);
    }
    ```

    Le dump va nous retourner l'url qu'on devrait vraiment recevoir.

10. En cas d'erreur du slug, on pourra faire la redirection vers la bonne url.

    ```
    http_response_code(301);
    header('Location: ' . $url);
    ```

11. On teste en enlèvant une lettre du slug et on est bien redirigé vers la bonne.






