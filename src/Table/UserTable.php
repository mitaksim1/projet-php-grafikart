<?php
namespace App\Table;

use App\Model\User;
use App\Table\Exceptions\NotFoundException;
use PDO;

final class UserTable extends Table {

    protected $table = "user";
    protected $class = User::class;

    public function findByUserName(string $username) {
        // Comme on va recevoir des paramètres envoyés par l'utilisateur on fait une rquête préparé
        $query = $this->pdo->prepare('SELECT * FROM ' . $this->table . ' WHERE username = :username');
        // On précise que l'id correspondra à l'id envoyé par l'utilisateur
        $query->execute(['username' => $username]);
        $query->setFetchMode(PDO::FETCH_CLASS, $this->class);
        $result = $query->fetch();
        if ($result === false) {
            throw new NotFoundException($this->table, $username);
        }
        return $result;
    }
    
}
  