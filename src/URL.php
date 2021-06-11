<?php
namespace App;

class URL {

    public static function getInt(string $name, ?int $default = null): ?int
    {
        if (!isset($_GET[$name])) return $default;
    
        // Si la valeur saisi dans $page n'est pas un entier
        if (!filter_var($_GET[$name], FILTER_VALIDATE_INT)) {
            throw new \Exception("Le paramètre '$name' dans l'url n'est pas un entier");
        }
        return (int)$_GET[$name];
    }
}