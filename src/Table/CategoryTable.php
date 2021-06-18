<?php
namespace App\Table;

use App\Model\Category;
use App\Table\Exceptions\NotFoundException;
use PDO;

class CategoryTable extends Table {

   public function find(int $id): Category
   {
        // Comme on va recevoir des paramètres envoyés par l'utilisateur on fait une rquête préparé
        $query = $this->pdo->prepare('SELECT * FROM category WHERE id = :id');
        // On précise que l'id correspondra à l'id envoyé par l'utilisateur
        $query->execute(['id' => $id]);
        $query->setFetchMode(PDO::FETCH_CLASS, Category::class);
        $result = $query->fetch();
        if ($result === false) {
            throw new NotFoundException('category', $id);
        }
        return $result;
    }
    
}