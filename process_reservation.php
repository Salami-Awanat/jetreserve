<?php
session_start();
require_once 'includes/db.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['id_user'])) {
    header('Location: vge64/connexion.php');
    exit;
}

// Vérifier si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// Récupérer et valider les données du formulaire
$vol_id = intval($_POST['vol_id'] ?? 0);
$passagers = intval($_POST['passagers'] ?? 1);
$classe = $_POST['classe'] ?? 'économique';
$total_price = floatval($_POST['total_price'] ?? 0);

// Données personnelles
$civilite = $_POST['civilite'] ?? '';
$prenom = trim($_POST['prenom'] ?? '');
$nom = trim($_POST['nom'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');

// Données des passagers
$passengers = $_POST['passengers'] ?? [];

// Validation des données
$errors = [];

if (empty($vol_id) || $vol_id <= 0) {
    $errors[] = "ID du vol invalide";
}

if (empty($prenom) || empty($nom) || empty($email) || empty($phone)) {
    $errors[] = "Tous les champs personnels sont obligatoires";
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Adresse email invalide";
}

if (count($passengers) !== $passagers) {
    $errors[] = "Nombre de passagers incorrect";
}

// Validation des données des passagers
foreach ($passengers as $i => $passenger) {
    if (empty($passenger['prenom']) || empty($passenger['nom']) || empty($passenger['civilite']) || empty($passenger['naissance'])) {
        $errors[] = "Toutes les informations du passager " . ($i + 1) . " sont obligatoires";
    }
}

if (!empty($errors)) {
    $_SESSION['reservation_errors'] = $errors;
    header('Location: reservation.php?vol_id=' . $vol_id);
    exit;
}

try {
    // Démarrer une transaction
    $pdo->beginTransaction();
    
    // Vérifier que le vol existe et a assez de places
    $sql_vol = "SELECT v.*, c.nom_compagnie, c.code_compagnie, a.modele as avion_modele
                FROM vols v 
                JOIN compagnies c ON v.id_compagnie = c.id_compagnie 
                JOIN avions a ON v.id_avion = a.id_avion
                WHERE v.id_vol = ? AND v.places_disponibles >= ? AND v.date_depart > NOW()";
    
    $stmt_vol = $pdo->prepare($sql_vol);
    $stmt_vol->execute([$vol_id, $passagers]);
    $vol = $stmt_vol->fetch(PDO::FETCH_ASSOC);
    
    if (!$vol) {
        throw new Exception("Le vol n'existe plus ou n'a plus assez de places disponibles");
    }
    
    // Calculer le prix total avec les frais
    $frais_service = 9.00;
    $taxes_aeroport = 25.00;
    $prix_total_final = $total_price + $frais_service + $taxes_aeroport;
    
    // Insérer la réservation avec statut "en_attente_paiement"
    $sql_reservation = "INSERT INTO reservations (id_user, id_vol, statut, nombre_passagers, prix_total, classe, date_reservation) 
                        VALUES (?, ?, 'en_attente_paiement', ?, ?, ?, NOW())";
    
    $stmt_reservation = $pdo->prepare($sql_reservation);
    $stmt_reservation->execute([
        $_SESSION['id_user'], 
        $vol_id, 
        $passagers, 
        $prix_total_final,
        $classe
    ]);
    
    $reservation_id = $pdo->lastInsertId();
    
    // Insérer les informations des passagers
    $sql_passager = "INSERT INTO passagers (id_reservation, civilite, prenom, nom, date_naissance) 
                     VALUES (?, ?, ?, ?, ?)";
    $stmt_passager = $pdo->prepare($sql_passager);
    
    foreach ($passengers as $passenger) {
        $stmt_passager->execute([
            $reservation_id,
            $passenger['civilite'],
            $passenger['prenom'],
            $passenger['nom'],
            $passenger['naissance']
        ]);
    }
    
    // Valider la transaction
    $pdo->commit();
    
    // Stocker l'ID de réservation en session pour la page de paiement
    $_SESSION['reservation_id'] = $reservation_id;
    $_SESSION['prix_total'] = $prix_total_final;
    
    // Rediriger vers la page de paiement
    header('Location: payment.php');
    exit;
    
} catch (Exception $e) {
    // Annuler la transaction en cas d'erreur
    $pdo->rollBack();
    
    error_log("Erreur lors de la réservation: " . $e->getMessage());
    $_SESSION['reservation_errors'] = ["Une erreur est survenue lors de la réservation. Veuillez réessayer."];
    header('Location: reservation.php?vol_id=' . $vol_id);
    exit;
}
?>