<?php
session_start();
require_once __DIR__ . '/../../includes/db.php';

// Vérifier si l'utilisateur est connecté et est admin
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../vge64/connexion.php');
    exit;
}

// Récupérer les statistiques pour le dashboard
try {
    // Nombre total d'utilisateurs clients
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM users WHERE role = 'client'");
    $stmt->execute();
    $total_clients = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Nombre total de réservations
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM reservations");
    $stmt->execute();
    $total_reservations = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Nombre total de vols
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM vols");
    $stmt->execute();
    $total_vols = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Chiffre d'affaires total
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(prix_total), 0) as ca FROM reservations WHERE statut = 'confirmé'");
    $stmt->execute();
    $chiffre_affaires = $stmt->fetch(PDO::FETCH_ASSOC)['ca'];

    // Réservations en attente
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM reservations WHERE statut = 'en attente'");
    $stmt->execute();
    $reservations_attente = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

} catch (PDOException $e) {
    $total_clients = 0;
    $total_reservations = 0;
    $total_vols = 0;
    $chiffre_affaires = 0;
    $reservations_attente = 0;
}
?>