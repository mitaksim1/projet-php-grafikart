<?php
namespace App\Table;

use App\Model\Category;
use PDO;

final class CategoryTable extends Table {

    protected $table = "category";
    protected $class = Category::class;

    // Rentre dans les articles la catégorie associée
    /**
     * @param App\Model\Post[] $posts
     */
    public function hydratePosts(array $posts): void
    {
         // On récupère l'id de chaque article
         $postsById = [];
         foreach ($posts as $post) {
             // On passe l'id du post comme index du tableau $postsById
             // et la valeur de cet index sera le post lui même
             $postsById[$post->getId()] = $post;
         }
         // dd(array_keys($postsById));
 
         $categories = $this->pdo
             ->query('SELECT c.*, pc.post_id
                 FROM post_category pc
                 JOIN category c ON c.id = pc.category_id
                 WHERE pc.post_id IN (' . implode(',', array_keys($postsById)) . ')'
             )->fetchAll(PDO::FETCH_CLASS, $this->class);
         // dump($categories);
 
         // On parcourt les catégories
         foreach ($categories as $category) {
             // On trouve l'article $posts correspondant à la ligne
             // On ajoute la catégorie à l'article
             $postsById[$category->getPostId()]->addCategory($category);
         }
    }

    public function all()
    {
        return $this->queryAndFetchAll("SELECT * FROM {$this->table} ORDER BY id DESC");  
    }
    
}