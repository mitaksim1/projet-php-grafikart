<?php
namespace App\Table;

use App\Model\Category;
use App\Model\Post;
use App\PaginatedQuery;
use App\Table\Exceptions\NotFoundException;
use PDO;

class PostTable extends Table {

    public function find(int $id): Post
    {
        // Comme on va recevoir des paramètres envoyés par l'utilisateur on fait une rquête préparé
        $query = $this->pdo->prepare('SELECT * FROM post WHERE id = :id');
        // On précise que l'id correspondra à l'id envoyé par l'utilisateur
        $query->execute(['id' => $id]);
        $query->setFetchMode(PDO::FETCH_CLASS, Post::class);
        $result = $query->fetch();
        if ($result === false) {
            throw new NotFoundException('post', $id);
        }
        return $result;
    }


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

        // On appelle la méthode hydratePosts()
        (new CategoryTable($this->pdo))->hydratePosts($posts);

        return [$posts, $paginatedQuery];
    }

    public function findPaginatedForCategory(int $categoryId) 
    {
        $paginatedQuery = new PaginatedQuery(
            "SELECT p.* 
                FROM post p 
                JOIN post_category pc ON pc.post_id = p.id
                WHERE pc.category_id = {$categoryId}
                ORDER BY created_at DESC", 
            "SELECT COUNT(category_id) FROM post_category WHERE category_id = {$categoryId}"
        );
        
        $posts = $paginatedQuery->getItems(Post::class);
        // dd($posts);
        
        // On appelle la méthode hydratePosts()
        (new CategoryTable($this->pdo))->hydratePosts($posts);

        return [$posts, $paginatedQuery];    
    }
}
  