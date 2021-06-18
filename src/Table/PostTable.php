<?php
namespace App\Table;

use App\Model\Category;
use App\Model\Post;
use App\PaginatedQuery;
use PDO;

class PostTable extends Table {

    public function findpaginated()
    {
        $paginatedQuery = new PaginatedQuery(
            // Premier requête liste les articles
            "SELECT * FROM post ORDER BY created_at DESC",
            // Deuxième requête récupères le nombre d'articles total
            "SELECT COUNT(id) FROM post",
            $this->pdo
        );
        
        // On récupère les articles
        $posts = $paginatedQuery->getItems(Post::class);

        // On hydrate les catégories
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
            )->fetchAll(PDO::FETCH_CLASS, Category::class);
        // dump($categories);

        // On parcourt les catégories
        foreach ($categories as $category) {
            // On trouve l'article $posts correspondant à la ligne
            // On ajoute la catégorie à l'article
            $postsById[$category->getPostId()]->addCategory($category);
        }
        return [$posts, $paginatedQuery];
    }

}   