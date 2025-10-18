<?php
session_start();
require_once '../includes/db.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: ../vge64/connexion.php');
    exit;
}

// Vérifier si l'utilisateur est admin
if ($_SESSION['role'] !== 'admin') {
    header('Location: ../index.php?error=access_denied');
    exit;
}

// Vérifier en base que le rôle est toujours valide
try {
    $stmt = $pdo->prepare("SELECT role, statut FROM utilisateurs WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user || $user['role'] !== 'admin' || $user['statut'] !== 'actif') {
        session_destroy();
        header('Location: ../vge64/connexion.php?error=access_revoked');
        exit;
    }
} catch (PDOException $e) {
    error_log("Erreur vérification admin: " . $e->getMessage());
}
?>