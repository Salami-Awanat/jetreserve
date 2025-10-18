<?php
// create_admin.php - À METTRE DANS LE DOSSIER RACINE (jetreserve/)
require_once 'includes/db.php';

echo "<h2>Création du compte Admin</h2>";

$email = "admin@jetreserve.com";
$password = "admin123"; // Mot de passe en clair
$nom = "Admin";
$prenom = "System";
$role = "admin";
$statut = "actif";

try {
    // Vérifier si l'admin existe déjà
    $stmt = $pdo->prepare("SELECT id_user FROM users WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->rowCount() > 0) {
        echo "<p style='color: orange;'>⚠️ L'admin existe déjà</p>";
        
        // Afficher les infos de l'admin existant
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "<p><strong>Admin existant:</strong> " . $admin['prenom'] . " " . $admin['nom'] . " (" . $admin['email'] . ")</p>";
    } else {
        // Créer le compte admin (mot de passe en clair)
        $stmt = $pdo->prepare("INSERT INTO users (nom, prenom, email, password, role, statut) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$nom, $prenom, $email, $password, $role, $statut]);
        
        echo "<p style='color: green;'>✅ Compte admin créé !</p>";
        echo "<p><strong>Email:</strong> $email</p>";
        echo "<p><strong>Mot de passe:</strong> $password</p>";
        echo "<p><strong>Rôle:</strong> $role</p>";
    }
    
    // Afficher tous les admins
    echo "<h3>Liste des administrateurs :</h3>";
    $stmt = $pdo->query("SELECT id_user, nom, prenom, email, role, statut FROM users WHERE role = 'admin'");
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($admins)) {
        echo "<p style='color: red;'>❌ Aucun administrateur trouvé</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Nom</th><th>Email</th><th>Rôle</th><th>Statut</th></tr>";
        foreach ($admins as $admin) {
            echo "<tr>";
            echo "<td>{$admin['id_user']}</td>";
            echo "<td>{$admin['prenom']} {$admin['nom']}</td>";
            echo "<td>{$admin['email']}</td>";
            echo "<td>{$admin['role']}</td>";
            echo "<td>{$admin['statut']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erreur: " . $e->getMessage() . "</p>";
    
    // Afficher le chemin pour debug
    echo "<p><strong>Chemin actuel:</strong> " . __DIR__ . "</p>";
    echo "<p><strong>Fichier recherché:</strong> includes/db.php</p>";
}
?>