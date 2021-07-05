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

### La partie Catégories

1. Dans **views/admin**, on va dupliquer le dossier **post**, parce qu'on veut les mêmes fonctionnalités.

    On renomme le dossier **category**.

2. Dans **src/Model** on avait déjà crée la classe **Category.php**.

    On va créer les setters qu'il manque.

3. Ensuite, on va créer un validator, parce qu'on aura besoin de valider les catégories.

    On fait simplement un C/C de PostValidator, on change juste le nom de la variable de $postId par $id pour que ce soit plus cohérent.

    ```
    <?php
    namespace App\Validators;

    use App\Table\CategoryTable;

    class CategoryValidator extends AbstractValidator {

        public function __construct(array $data, CategoryTable $table, ?int $id = null)
        {
            parent::__construct($data);
            // Valide l'existence du titre
            $this->validator->rule('required', ['name', 'slug']);
            // valide la longueur du titre
            $this->validator->rule('lengthBetween', ['name', 'slug'], 10, 200);
            // Valide le champs slug
            $this->validator->rule('slug', 'slug');
            // On crée notre propre validator
            $this->validator->rule(function ($field, $value) use ($table, $id) {
                return !$table->exists($field, $value, $id);
            }, ['slug', 'name'], 'Cette valeur est déjà utilisé'); 
        }
    }
    ```

4. 


