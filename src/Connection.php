<?php
namespace App;

use PDO;

class Connection {

    public static function getPDO(): \PDO
    {
        return new PDO('mysql:dbname=tutoblog;host=127.0.0.1', 'root', 'Root*', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
    }
}