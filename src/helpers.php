<?php

// Fonction qui va convertir les données en entité html
function e(string $string) {
    return htmlentities($string);
}
