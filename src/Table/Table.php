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
}
