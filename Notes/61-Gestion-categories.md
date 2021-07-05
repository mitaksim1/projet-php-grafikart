## ahpitre 61 - Gestion des catégories

A la fin de la vidéo précédente on avait comme challenge créer la même chose que l'on avait fait pour les articles, mais pour les catégories et ajouter au header un lien pour les article et un autre pour les catégories.

On va commencer par créer un noveau template pour l'admin de la partie articles et le lien sur le header.

1. Dans le dossier **views/admin/** on va créer un nouveau dossier **layouts** où on va créer le fichier default.php qui va contenir la même chose que le fichier default.php que l'on avait crée avant plus le lien qui va nous méner à la page des articles.

2. Dans la méthode *run()* dans **Router.php**, on va ajouter la variable $layout qui va contenir la condition pour savoir quel des deux layouts on doit afficher :

    ```
    $isAdmin = strpos($view, 'admin/') !== false;
    $layout = $isAdmin ? 'admin/layouts/default' : 'layout/default';
    ```

    On n'oublie pas de changer l'appel à $layout dans le *require* :

    ```
    require $this->viewPath . DIRECTORY_SEPARATOR . $layout . '.php';
    ```

3. On teste et ça marche.