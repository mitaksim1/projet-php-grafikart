<?php
namespace App\Table;

use App\Model\Post;
use App\PaginatedQuery;

final class PostTable extends Table {

    protected $table = "post";
    protected $class = Post::class;

    public function create(Post $post): void
    {
        $query = $this->pdo->prepare("INSERT INTO {$this->table} SET name = :name, slug = :slug, created_at = :created, content = :content");
        $queryExecuted = $query->execute([
            'name' => $post->getName(),
            'slug' => $post->getSlug(),
            'content' => $post->getContent(),
            'created' => $post->getCreatedAt()->format('Y-m-d H:i:s')
        ]);
        // Condition si requête bien exécutée
        if ($queryExecuted === false) {
            throw new \Exception("Impossible de créer l'enregistrement dans la table {$this->table}");
        }
        $post->setId($this->pdo->lastInsertId());
    }

    public function update(Post $post): void 
    {
        // Récupère l'article dont l'id est demandée
        $query = $this->pdo->prepare("UPDATE {$this->table} SET name = :name, slug = :slug, created_at = :created, content = :content WHERE id = :id");
        // Exécute la requête qui nous retourne true/false
        $queryExecuted = $query->execute([
            'id' => $post->getId(),
            'name' => $post->getName(),
            'slug' => $post->getSlug(),
            'content' => $post->getContent(),
            'created' => $post->getCreatedAt()->format('Y-m-d H:i:s')
        ]);
        // Condition si requête bien exécutée
        if ($queryExecuted === false) {
            throw new \Exception("Impossible de modifier l'enregistrement {$post->getId()} dans la table {$this->table}");
        }
    }

    public function delete(int $id): void
    {
        // Récupère l'article dont l'id est demandée
        $query = $this->pdo->prepare("DELETE FROM {$this->table} WHERE id = ?");
        // Exécute la requête qui nous retourne true/false
        $queryExecuted = $query->execute([$id]);
        // Condition si requête bien exécutée
        if ($queryExecuted === false) {
            throw new \Exception("Impossible de supprimer l'enregistrement $id dans la table {$this->table}");
        }
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
  