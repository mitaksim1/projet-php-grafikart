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

    // Nombre d'éléments total en bdd
    private $count;

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
        $currentPage = $this->getCurrentPage();

        // 2. On a besoin de savoir combien de pages existent
        $pages = $this->getPages();
        
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
    
    public function previousLink(string $link): ?string 
    {
        $currentPage = $this->getCurrentPage();
        if ($currentPage <= 1) return null;
        if ($currentPage > 2) $link .= "?page=" . ($currentPage - 1);
        return <<<HTML
            <a href="{$link}" class="btn btn-primary">&laquo; Page précédente</a>
HTML;
    }

    public function nextLink(string $link): ?string
    {
        $currentPage = $this->getCurrentPage();
        // On a besoin de savoir le nombre de pages qui existente
        $pages = $this->getPages();
        // Si la page où on est est supérieur au nombre de pages total, on n'a besoin de rien faire
        if ($currentPage >= $pages) return null;
        $link .= "?page=" . ($currentPage + 1);
        return <<<HTML
            <a href="{$link}" class="btn btn-primary ml-auto">Page suivante &raquo;</a>
HTML; 
    }

    public function getCurrentPage(): int
    {
        // 1. On a besoin de la page courante
        return URL::getPositiveInt('page', 1);
    }

    private function getPages(): int
    { 
        if ($this->count === null) {
            // Récupère le nombre des articles pour la catégorie donnée
            $this->count = (int)$this->pdo
            //->query('SELECT COUNT(category_id) FROM post_category WHERE category_id = ' . $category->getId())
            ->query($this->queryCount)
            ->fetch(PDO::FETCH_NUM)[0];
        }
        
        // Calcule le nombre de pages que l'on aura
        $pages = ceil($this->count / $this->perPage);
        // dd($pages);  

        return $pages;
    }

}