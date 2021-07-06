<?php
namespace App\Table;

use App\Model\Post;
use App\Table\Exceptions\NotFoundException;
use PDO;

abstract class Table {

    protected $pdo;

    protected $table = null;

    protected $class = null;

    public function __construct(\PDO $pdo)
    {
        if ($this->table === null) {
            throw new \Exception("La classe " . get_class($this) . " n'a pas de propriété \$table");
        }
        if ($this->table === null) {
            throw new \Exception("La classe " . get_class($this) . " n'a pas de propriété \$class");
        }
        $this->pdo = $pdo;
    }

    public function find(int $id)
    {
        // Comme on va recevoir des paramètres envoyés par l'utilisateur on fait une rquête préparé
        $query = $this->pdo->prepare('SELECT * FROM ' . $this->table . ' WHERE id = :id');
        // On précise que l'id correspondra à l'id envoyé par l'utilisateur
        $query->execute(['id' => $id]);
        $query->setFetchMode(PDO::FETCH_CLASS, $this->class);
        $result = $query->fetch();
        if ($result === false) {
            throw new NotFoundException($this->table, $id);
        }
        return $result;
    }

    /**
     * Vérifie si une valeur existe dans la table
     * 
     * @param string $field Champs à rechercher
     * @param mixed $value Valeur associée au champs
     */
    public function exists(string $field, $value, ?int $exceptId = null): bool
    {
        $sql = "SELECT COUNT(id) FROM {$this->table} WHERE $field = ?";
        $params = [$value];
        if ($exceptId !== null) {
            $sql .= " AND id != ?";
            $params[] = $exceptId;
        }
        $query = $this->pdo->prepare($sql);
        $query->execute($params);
        $result = (int)$query->fetch(PDO::FETCH_NUM)[0] > 0;

        return $result;
    }

    public function all()
    {
        $sql = "SELECT * FROM {$this->table}";
        return $this->pdo->query($sql, PDO::FETCH_CLASS, $this->class)->fetchAll();
    }

    public function delete(int $id)
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

    // $data correpondra aux champs dont on aura besoin pour créer une catégorie
    public function create(array $data): int
    {
        // Récupération des champs
        $sqlFields = [];
        foreach ($data as $key => $value) {
            // Correspond à : name = :name
            $sqlFields[] = "$key = :$key";
        }
        $query = $this->pdo->prepare("INSERT INTO {$this->table} SET " . implode(', ', $sqlFields));
        $queryExecuted = $query->execute($data);
        // Condition si requête bien exécutée
        if ($queryExecuted === false) {
            throw new \Exception("Impossible de créer l'enregistrement dans la table {$this->table}");
        }
        // Retourne l'id du dernier élément crée
        return (int)$this->pdo->lastInsertId();
    }

    public function update(array $data, int $id)
    {
        // Récupération des champs
        $sqlFields = [];
        foreach ($data as $key => $value) {
            // Correspond à : name = :name
            $sqlFields[] = "$key = :$key";
        }
        $query = $this->pdo->prepare("UPDATE {$this->table} SET " . implode(', ', $sqlFields) . " WHERE id = :id");
        // array_merge, on va rajouter la clé id au tableau $data
        $queryExecuted = $query->execute(array_merge($data, ['id' => $id]));
        // Condition si requête bien exécutée
        if ($queryExecuted === false) {
            throw new \Exception("Impossible de modifier l'enregistrement dans la table {$this->table}");
        }
    }

    public function queryAndFetchAll(string $sql): array
    {
        return $this->pdo->query($sql, PDO::FETCH_CLASS, $this->class)->fetchAll();
    }
}
