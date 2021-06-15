<?php
namespace App;

use Exception;
use PDO;

class PaginatedQuery {

    private $query;

    private $queryCount;

    private $classMapping;

    private $pdo;

    private $perPage;

    public function __construct(string $query, string $queryCount, string $classMapping, ?\PDO $pdo = null, int $perPage = 12)
    {
        $this->query = $query;
        $this->queryCount = $queryCount;
        $this->classMapping = $classMapping;
        // Si PDO n'est pas définit on prend la connexion globale de l'appli
        $this->pdo = $pdo ?: Connection::getPDO();
        $this->perPage = $perPage;
    }

    public function getItems(): array
    {
        // 1. On a besoin de la page courante
        $currentPage = URL::getPositiveInt('page', 1);

        // 2. On a besoin de compter les résultats
        // Récupère le nombre des articles pour la catégorie donnée
        $count = (int)$this->pdo
            //->query('SELECT COUNT(category_id) FROM post_category WHERE category_id = ' . $category->getId())
            ->query($this->queryCount)
            ->fetch(PDO::FETCH_NUM)[0];

        // 3. Calcule le nombre d'articles qu'on mettra par page
        $pages = ceil($count / $this->perPage);
        // dd($pages);  
        
        // 4. Envoi une exception si la page n'existe pas
        if ($currentPage > $pages) {
            throw new Exception('Cette page n\'existe pas');
        }
        // 5. On calcule le offset par page
        $offset = $this->perPage * ($currentPage -1);

        // On récupére les articles les plus récents
        return $this->pdo->query($this->query .
            " LIMIT {$this->perPage} 
            OFFSET $offset
        ")
        ->fetchAll(PDO::FETCH_CLASS, $this->classMapping);
    }   
}