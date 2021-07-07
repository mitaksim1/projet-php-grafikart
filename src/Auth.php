<?php
namespace App;

use App\Security\ForbiddenException;
use Exception;

class Auth {
    /**
     * Vérifie si l'utilisateur est bien connecté
     */
    public static function check() {
        // Vérifie si la session est activée
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['auth'])) {
            throw new ForbiddenException();
        }
    }
}