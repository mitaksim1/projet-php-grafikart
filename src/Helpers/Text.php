<?php
namespace App\Helpers;

class Text {

    // Méthode qui va afficher un extrait
    public static function excerpt(string $content, int $limit = 60)
    {
        // Vérifie si le texte dépasse la limite
        if (mb_strlen($content) < $limit) {
            // si inférieur on retourne le contenu
            return $content;
        }
        // Si supérieur on appelle la méthode substr, on passe le contenu à couper et on donne les mésures que l'on souhaite
        // Les trois points c'est juste pour signaler à l'utilisateur qu'il y a une suite
        return substr($content, 0, $limit) . '...';
    }
}