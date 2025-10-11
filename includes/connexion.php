<?php
$host = 'localhost';
$dbname = 'jetreserve';
$username = 'root';
$password = '';

try {
    // Connexion sans spécifier la base de données d'abord
    $bdd = new PDO("mysql:host=$host;charset=utf8", $username, $password);
    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Vérifier si la base de données existe, sinon la créer
    $bdd->exec("CREATE DATABASE IF NOT EXISTS `$dbname`");
    $bdd->exec("USE `$dbname`");
    
    // Définir les constantes pour les noms de tables
    define('TABLE_USERS', 'users');
    define('TABLE_VOLS', 'vols');
    define('TABLE_RESERVATIONS', 'reservations');
    define('TABLE_PAIEMENTS', 'paiements');
    define('TABLE_COMPAGNIES', 'compagnies');
    define('TABLE_EMAILS', 'emails');
    
    // Vérifier si les tables existent, sinon les créer
    $tables_check = $bdd->query("SHOW TABLES");
    $tables = $tables_check->fetchAll(PDO::FETCH_COLUMN);
    
    if (count($tables) == 0) {
        // Créer les tables si elles n'existent pas
        $bdd->exec("
            CREATE TABLE IF NOT EXISTS `users` (
              `id_user` int(11) NOT NULL AUTO_INCREMENT,
              `nom` varchar(50) NOT NULL,
              `prenom` varchar(50) NOT NULL,
              `email` varchar(100) NOT NULL,
              `telephone` varchar(20) DEFAULT NULL,
              `password` varchar(255) NOT NULL,
              `role` enum('client','admin') DEFAULT 'client',
              `date_creation` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
              PRIMARY KEY (`id_user`),
              UNIQUE KEY `email` (`email`)
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1;
            
            CREATE TABLE IF NOT EXISTS `compagnies` (
              `id_compagnie` int(11) NOT NULL AUTO_INCREMENT,
              `nom_compagnie` varchar(100) NOT NULL,
              `code_compagnie` varchar(10) NOT NULL,
              PRIMARY KEY (`id_compagnie`),
              UNIQUE KEY `code_compagnie` (`code_compagnie`)
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1;
            
            CREATE TABLE IF NOT EXISTS `vols` (
              `id_vol` int(11) NOT NULL AUTO_INCREMENT,
              `id_compagnie` int(11) NOT NULL,
              `depart` varchar(50) NOT NULL,
              `arrivee` varchar(50) NOT NULL,
              `date_depart` datetime NOT NULL,
              `date_arrivee` datetime NOT NULL,
              `prix` decimal(10,2) NOT NULL,
              `escales` int(11) DEFAULT '0',
              `classe` enum('économique','affaires','première') DEFAULT 'économique',
              PRIMARY KEY (`id_vol`),
              KEY `id_compagnie` (`id_compagnie`)
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1;
            
            CREATE TABLE IF NOT EXISTS `reservations` (
              `id_reservation` int(11) NOT NULL AUTO_INCREMENT,
              `id_user` int(11) NOT NULL,
              `id_vol` int(11) NOT NULL,
              `statut` enum('confirmé','en attente','annulé') DEFAULT 'en attente',
              `date_reservation` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
              PRIMARY KEY (`id_reservation`),
              KEY `id_user` (`id_user`),
              KEY `id_vol` (`id_vol`)
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1;
            
            CREATE TABLE IF NOT EXISTS `paiements` (
              `id_paiement` int(11) NOT NULL AUTO_INCREMENT,
              `id_reservation` int(11) NOT NULL,
              `montant` decimal(10,2) NOT NULL,
              `mode_paiement` enum('carte','mobile money','paypal') NOT NULL,
              `statut` enum('réussi','en attente','échoué') DEFAULT 'en attente',
              `date_paiement` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
              PRIMARY KEY (`id_paiement`),
              KEY `id_reservation` (`id_reservation`)
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1;
            
            CREATE TABLE IF NOT EXISTS `emails` (
              `id_email` int(11) NOT NULL AUTO_INCREMENT,
              `id_user` int(11) NOT NULL,
              `sujet` varchar(150) NOT NULL,
              `contenu` text,
              `type` enum('confirmation','recu','ticket') DEFAULT 'confirmation',
              `date_envoi` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
              `statut` enum('envoyé','échoué') DEFAULT 'envoyé',
              PRIMARY KEY (`id_email`),
              KEY `id_user` (`id_user`)
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1;
        ");
        
        // Insérer un utilisateur admin par défaut
        $bdd->exec("
            INSERT INTO `users` (`nom`, `prenom`, `email`, `telephone`, `password`, `role`) 
            VALUES ('Admin', 'System', 'admin@jetreserve.com', '0700000000', 'admin123', 'admin')
        ");
    }
    
} catch (PDOException $e) {
    die('Erreur de connexion à la base de données : ' . $e->getMessage());
}
?>
