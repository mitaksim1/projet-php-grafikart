<?php
namespace App\Table;

use App\Model\Post;
use App\PaginatedQuery;

final class PostTable extends Table {

    protected $table = "post";
    protected $class = Post::class;

    public function createPost (Post $post): void
    {
        $id = $this->create([
            'name' => $post->getName(),
            'slug' => $post->getSlug(),
            'content' => $post->getContent(),
            'created_at' => $post->getCreatedAt()->format('Y-m-d H:i:s')
        ]);
        $post->setId($id);
    }

    public function updatePost(Post $post): void 
    {
        $this->update([
            'id' => $post->getId(),
            'name' => $post->getName(),
            'slug' => $post->getSlug(),
            'content' => $post->getContent(),
            'created_at' => $post->getCreatedAt()->format('Y-m-d H:i:s')
        ], $post->getId());
    }

    public function findPaginated()
    {
        $paginatedQuery = new PaginatedQuery(
            // Premier requête liste les articles
            "SELECT * FROM {$this->table} ORDER BY created_at DESC",
            // Deuxième requête récupères le nombre d'articles total
            "SELECT COUNT(id) FROM {$this->table}",
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
                FROM {$this->table} p 
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
  