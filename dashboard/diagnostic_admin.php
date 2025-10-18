<?php
session_start();
require_once 'includes/db.php';

echo '<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnostic Admin - JetReserve</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: \'Poppins\', sans-serif; background: #f8fafc; padding: 20px; color: #334155; }
        .container { max-width: 1200px; margin: 0 auto; }
        h1, h2, h3 { color: #1e293b; margin-bottom: 15px; }
        .card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-bottom: 20px; }
        .success { color: #10b981; }
        .error { color: #ef4444; }
        .warning { color: #f59e0b; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e2e8f0; }
        th { background: #f8fafc; color: #475569; font-weight: 600; }
        .btn { display: inline-block; padding: 10px 20px; background: #2563eb; color: white; text-decoration: none; border-radius: 6px; margin: 5px; }
        .btn:hover { background: #1d4ed8; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Diagnostic Administrateur</h1>';

try {
    echo '<div class="card"><h2>📊 État de la Base de Données</h2>';
    
    // Test connexion
    echo '<p class="success">✅ Connexion à la base de données réussie</p>';
    
    // Structure table utilisateurs
    $stmt = $pdo->query("SHOW COLUMNS FROM utilisateurs");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo '<h3>🏗️ Structure de la table utilisateurs</h3>';
    echo '<table>';
    echo '<tr><th>Champ</th><th>Type</th><th>Null</th><th>Clé</th><th>Défaut</th></tr>';
    foreach ($columns as $col) {
        echo '<tr>';
        echo '<td>' . $col['Field'] . '</td>';
        echo '<td>' . $col['Type'] . '</td>';
        echo '<td>' . $col['Null'] . '</td>';
        echo '<td>' . $col['Key'] . '</td>';
        echo '<td>' . ($col['Default'] ?: 'NULL') . '</td>';
        echo '</tr>';
    }
    echo '</table>';
    
    // Utilisateurs existants
    echo '<h3>👥 Utilisateurs existants</h3>';
    $stmt = $pdo->query("SELECT id, nom, prenom, email, role, statut, date_inscription FROM utilisateurs ORDER BY role, date_inscription DESC");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($users)) {
        echo '<p class="error">❌ Aucun utilisateur trouvé</p>';
    } else {
        echo '<table>';
        echo '<tr><th>ID</th><th>Nom</th><th>Email</th><th>Rôle</th><th>Statut</th><th>Inscription</th></tr>';
        foreach ($users as $user) {
            $role_class = $user['role'] === 'admin' ? 'success' : '';
            $statut_class = $user['statut'] === 'actif' ? 'success' : 'error';
            echo '<tr>';
            echo '<td>' . $user['id'] . '</td>';
            echo '<td>' . $user['prenom'] . ' ' . $user['nom'] . '</td>';
            echo '<td>' . $user['email'] . '</td>';
            echo '<td class="' . $role_class . '">' . $user['role'] . '</td>';
            echo '<td class="' . $statut_class . '">' . $user['statut'] . '</td>';
            echo '<td>' . date('d/m/Y', strtotime($user['date_inscription'])) . '</td>';
            echo '</tr>';
        }
        echo '</table>';
    }
    
    echo '</div>';
    
} catch (PDOException $e) {
    echo '<div class="card error">❌ Erreur de connexion: ' . $e->getMessage() . '</div>';
}

echo '<div class="card">
    <h2>🚀 Actions Rapides</h2>
    <a href="create_admin.php" class="btn">👑 Créer Admin</a>
    <a href="vge64/connexion.php" class="btn">🔑 Connexion</a>
    <a href="dashboard/" class="btn">📊 Dashboard</a>
</div>';

echo '</div></body></html>';
?>