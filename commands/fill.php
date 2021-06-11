<?php

use App\Connection;

require dirname(__DIR__) . '/vendor/autoload.php';

$faker = Faker\Factory::create('fr_FR');

$pdo = Connection::getPDO();

// Pour que ça marche, il faut pas qu'il tienne compte des clé étrangères
$pdo->exec('SET FOREIGN_KEY_CHECKS = 0');

// On commence par effacer les données que l'on avait saisies manuellement
$pdo->exec('TRUNCATE TABLE post_category');
$pdo->exec('TRUNCATE TABLE post');
$pdo->exec('TRUNCATE TABLE category');
$pdo->exec('TRUNCATE TABLE user');

// Une fois que les tables sont vidées on peut réabiliter les clés étrangères
$pdo->exec('SET FOREIGN_KEY_CHECKS = 1');

$posts = [];
$categories = [];

// Post : Maintenant que la bdd est vide, on pourra la remplir avec des faux articles
for ($i = 0; $i < 50; $i++) {
    $pdo->exec("INSERT INTO post SET name='{$faker->sentence()}', slug='{$faker->slug}', created_at='{$faker->date} {$faker->time}', content='{$faker->paragraphs(rand(3,15), true)}'");
    // Quand on fait un INSERT on peut récupérer le dernier id inseré
    $posts[] = $pdo->lastInsertId();
    
}

// Category : sentence(3) on a pas besoin d'un nom trop long
for ($i = 0; $i < 5; $i++) {
    $pdo->exec("INSERT INTO category SET name='{$faker->sentence(3)}', slug='{$faker->slug}'");
    $categories[] = $pdo->lastInsertId();
}

// Relation entre les tables post et category
// Pour chaque article du tableau 
foreach($posts as $post) {
    // On donne une catégorie aléatoire grâce à la méthode randomElements de Faker
    $randomCategories = $faker->randomElements($categories, rand(0, count($categories)));
    // Pour chaque catégorie alèatoire on va faire un insert dans la tables post_category
    foreach ($randomCategories as $category) {
        $pdo->exec("INSERT INTO post_category SET post_id=$post, category_id=$category");  
    }
}

// On hash le password
$password = password_hash('admin', PASSWORD_BCRYPT);
$pdo->exec("INSERT INTO user SET username='admin', password='$password'");