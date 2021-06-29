<?php
namespace App;

use Exception;

class Auth {
    /**
     * Vérifie si l'utilisateur est bien connecté
     */
    public static function check() {
        if (!isset($_GET['admin'])) {
            throw new Exception('Accès interdit');
        }
        // TODO : Ecrire le code
    }
}