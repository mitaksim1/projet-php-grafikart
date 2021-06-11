## Chapitre 50 : Simplifions la gestion de l'URL

Pour pouvoir accèder à notre base de données, on a instancié PDO dans au moins deux fichiers différents, pour l'instant ça va, mais le soucis c'est que quand on ira passer notre site en mode de production, il va falloir changer l'adresse partout où on a instancié PDO.

Il y a plusieurs façons de gérer ce "problème", comme créer une variable globale dans *public/index.php*, en instanciant PDO globalement, mais le soucis c'est que PDO serait instancié automatiquement, même quand on aurait pas besoin, alors à éviter cette solution.

On va plutôt, créer une classe qui va s'occuper juste d'appeler PDO.

1. Dans **src** on va créer un fichier qui va s'appeler **Connection.php** et on va passer l'intanciation que l'on avait crée dans une méthode que l'on a nommé getPDO().

    ```
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
    ```

2. On aura maintenant, qu'à appeler cette méthode où on a en a besoin : *public/index.php** et *commands/fill.php*.

    Quand on ira déployer notre site et on aura besoin de changer les données de connexion à la base de données, on pourra le faire directement dans la classe Connection et ça va changer pour tous les fichiers que font appel à cette méthode.

3. Il faut qu'on écrive un code plus généralisé, au cas où on crée d'autres urls qui auront aussi besoin du paramètre *?page*.

    Par exemple si un jour, on a ce chemin : *localhost:8000/blog/tutoriels?page=1&param2=2*, il va falloir que l'on puisse enlèver le ?page=1

    ```
    if (isset($_GET['page']) && $_GET['page'] === '1') {
        dd($_SERVER);
    }
    ```

4. On voit que l'url saisi par l'utilisateur est stocké dans la clé REQUEST_URI, on va récupèrer sa valeur.

    ```
    if (isset($_GET['page']) && $_GET['page'] === '1') {
        $uri = $_SERVER['REQUEST_URI'];
    }
    ```
5. On va séparer la partie url de la partie paramètres :

    ```
    if (isset($_GET['page']) && $_GET['page'] === '1') {
        $uri = $_SERVER['REQUEST_URI'];
        $uri = explode('?', $uri)[0];
        dd($uri);
    }
    ```

    - On veut séparer à partir du "?", et comme on veut récupérer juste la première partie je précise déjà l'index de l'élément

    - On teste et on récupère bien */blog/tutoriels*

6. Il faut contruire la partie de droite, pour ça on peut utiliser la fonction **http_build_query**.

    Exemple d'utilisation :

    ```
    dd(http_build_query(['a' => 3, 'b' => 'c', 'tableau' => [1, 2, 3]]));
    ```

    - Ca nous retournera : "a=3&b=c&tableau%5B0%5D=1&tableau%5B1%5D=2&tableau%5B2%5D=3"

    Alors, dans notre cas, on va l'utiliser de la façon suivante :

    ```
    if (isset($_GET['page']) && $_GET['page'] === '1') {
        
        $uri = $_SERVER['REQUEST_URI'];
        $uri = explode('?', $uri)[0];
        $get = $_GET;
        
        unset($get['page']);
        dd(http_build_query($get));
        dd($uri);
    }
    ```

    - On sauvegarde le retour de la variable globale $_GET.

    - On détruit la valeur de la clé 'page'

    - On construit l'url avec ce qu'il reste, ça va nous retourner "param2=2"

    Quand on veut travailler aves une variable globale c'est mieux de la stocker dans une variable intermédiaire pour ne pas causer des bugs en modificant directement la valeur de la variable globale.

7. Si on teste on voit que l'on reçoit bien juste le deuxième paramètre et pas ?page, si on efface le deuxième paramètre on nous envoi vide.

    Il nous suffit de recomposer l'url :

    ```
    $query = http_build_query($get);
    if (!empty($query)) {
        $uri = $uri . '?' .$query;
    }
    ```

8. On teste en tapant les deux paramètres et ils nous retournent juste param=2. Ce code permet d'alléger index.php.

### On s'assure de recevoir toujours un entier dans le paramètre page

On va factoriser le code qui vérifie la récéption d'un entier dans l'url au moment de choisir la page en mettant le code que l'on avait crée dans **post/index;php** dans une classe que l'on a nommée **URL.php**.

1. On crée la classe URL.

    ```
    <?php
    namespace App;

    class URL {

        public static function getInt(string $name, ?int $default = null): ?int
        {
            if (!isset($_GET[$name])) return $default;

            // Si la valeur saisi dans $page n'est pas un entier
            if (!filter_var($_GET[$name], FILTER_VALIDATE_INT)) {
                throw new \Exception("Le paramètre $name dans l'url n'est pas un entier");
            }
            return (int)$_GET[$name];
        }
    }
    ```

2. On aura maintenant qu'à appeler cette méthode dans **post/index.php** en passant les arguments souhaités.

    ```
    $currentPage = URL::getInt('page', 1);
    ```

3. On teste!

### Challenge

Essayer de créer une méthode qui va gérer l'execption au cas où le numéro de page saisi soit inférieur à 0.

Moi, j'avais effacé cette partie du code tout au début de ce chapitre sans faire exprès, alors je le recopie.

```
if ($currentPage <= 0) {
    throw new Exception('Numéro de page invalide');
}
```

1. Le mec nous a donné la signature pour la méthode :

    ```
    public static function getPositiveInt(string $name, ?int $default = null): ?int
    {
    
    }
    ```

2. J'ai copié le codé que l'on avait codé auparavant et je l'ai collé dans cette méthode :

    ```
    public static function getPositiveInt(string $name, ?int $default = null): ?int
    {
        if ($name <= 0) {
            throw new Exception('Numéro de page invalide');
        }
    }
    ```

3. Après j'ai appeleé la méthode dans  **post/index.php** en passant les arguments comme suit :

    ```
    URL::getPositiveInt('page', $currentPage);
    ```

4. J'ai testé et à mon avis ça a marché, à voir avec la correction si le mec a fait d'une autre façon.

### Correction

Bien sur que le mec a fait d'une autre façon :

1. Code de getPositiveInt() :

    ```
    public static function getPositiveInt(string $name, ?int $default = null): ?int
    {
        $param = self::getInt($name, $default);
        if ($param !== null && $param <= 0) {
            throw new Exception("Le paramètre '$name' dans l'url n'est pas un entier positif");
        }
        return $param;
    }
    ```
2. On n'a qu'à appeler cette méthode à la place de getInt dans **post/index.php**.















 
