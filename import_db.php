<?php
// Script d'importation de la base de données
$host = 'localhost';
$username = 'root';
$password = '';

try {
    // Connexion au serveur MySQL sans sélectionner de base de données
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Création de la base de données si elle n'existe pas
    $pdo->exec("CREATE DATABASE IF NOT EXISTS jetreserve CHARACTER SET utf8 COLLATE utf8_general_ci");
    echo "Base de données 'jetreserve' créée ou déjà existante.<br>";
    
    // Sélection de la base de données
    $pdo->exec("USE jetreserve");
    
    // Lecture du fichier SQL
    $sql = file_get_contents('jetreserve.sql');
    
    // Exécution des requêtes SQL
    $pdo->exec($sql);
    echo "Importation réussie! La base de données a été configurée.<br>";
    
    // Vérification des tables
    $tables = ['users', 'vols', 'reservations', 'paiements', 'compagnies', 'emails'];
    $allTablesExist = true;
    
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() == 0) {
            echo "Erreur: La table '$table' n'existe pas.<br>";
            $allTablesExist = false;
        }
    }
    
    if ($allTablesExist) {
        echo "Toutes les tables ont été créées avec succès.<br>";
        echo "<a href='admin/index1.php'>Accéder au tableau de bord administrateur</a>";
    }
    
} catch (PDOException $e) {
    die("Erreur: " . $e->getMessage());
}
?>