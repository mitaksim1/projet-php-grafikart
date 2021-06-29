<?php
namespace App;

use AltoRouter;

class Router {

    /**
     * Pour que les autres méthodes puissent accèder à cette variable, on doit la créer comme une propriété de la classe
     * @var string
     */
    private $viewPath; 

    /**
     * On aura besoin d'AltoRouter 
     * @var AltoRouter
     */
    private $router;

    // Cette méthode prendra comme paramètre le chemin vers le dossier views
    public function __construct(string $viewPath)
    {
        // La propriété $viewPath, va stocker la valeur reçue dans le paramètre $viewPath
        $this->viewPath = $viewPath;
        // Pour pouvoir utiliser AltoRouter dans toutes les méthodes on doit l'intancier dès l'intance de la classe
        $this->router = new AltoRouter();
    }

    // Méthode qui va gérer les routes de l'application
    public function get(string $url, string $view, ?string $name = null): self
    {
        $this->router->map('GET', $url, $view, $name);
        // Méthode fluent permet de retourner la classe elle même et ainsi enchaîber les méthodes
        return $this;
    }

    public function post(string $url, string $view, ?string $name = null): self
    {
        $this->router->map('POST', $url, $view, $name);
        // Méthode fluent permet de retourner la classe elle même et ainsi enchaîber les méthodes
        return $this;
    }

    // Méthode qui va gérer les routes, elle prendra en paramètre le nom de la route et un array avec les paramètres pour cette route
    public function url(string $name, array $params = [])
    {
        return $this->router->generate($name, $params);
    }
    
    public function run(): self
    {
        // match : vérifie si il y a une correspondance entre l'url et une des routes enregistrées
        $match = $this->router->match();
        // dd($match);
        // Ca envoi un tableau associatif contenant les correspondances
        $view = $match['target'];
        $params = $match['params'];
        // ob_start va sauvegarder le require
        // Pour donner acccès à toutes les variables contenues dans AltoRouter
        $router = $this;
        ob_start();
        require $this->viewPath . DIRECTORY_SEPARATOR . $view . '.php';
        // ob_get_clean va récupérer la ligne ou les lignes contenues entre ob_start et lui, dans ce cas il va sauvegarder dans $content le require ci-dessus
        $content = ob_get_clean();
        // Une fois le require récupéré on va appeler la vue (à créer) default.php
        require $this->viewPath . DIRECTORY_SEPARATOR . 'layouts/default.php';

        return $this;
    }
}