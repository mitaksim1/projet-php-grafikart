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



3. 
