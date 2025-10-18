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

// Données de paiement
$card_name = trim($_POST['card_name'] ?? '');
$card_number = trim($_POST['card_number'] ?? '');
$expiry_date = trim($_POST['expiry_date'] ?? '');
$cvv = trim($_POST['cvv'] ?? '');

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

if (empty($card_name) || empty($card_number) || empty($expiry_date) || empty($cvv)) {
    $errors[] = "Tous les champs de paiement sont obligatoires";
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
    header('Location: reservation.php?vol_id=' . $vol_id . '&error=' . urlencode(implode(', ', $errors)));
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
    
    // Insérer la réservation
    $sql_reservation = "INSERT INTO reservations (id_user, id_vol, statut, nombre_passagers, prix_total) 
                        VALUES (?, ?, 'en attente', ?, ?)";
    
    $stmt_reservation = $pdo->prepare($sql_reservation);
    $stmt_reservation->execute([$_SESSION['id_user'], $vol_id, $passagers, $prix_total_final]);
    
    $reservation_id = $pdo->lastInsertId();
    
    // Insérer le paiement
    $sql_paiement = "INSERT INTO paiements (id_reservation, montant, mode_paiement, statut) 
                     VALUES (?, ?, 'carte', 'réussi')";
    
    $stmt_paiement = $pdo->prepare($sql_paiement);
    $stmt_paiement->execute([$reservation_id, $prix_total_final]);
    
    // Mettre à jour le nombre de places disponibles
    $sql_update_places = "UPDATE vols SET places_disponibles = places_disponibles - ? WHERE id_vol = ?";
    $stmt_update = $pdo->prepare($sql_update_places);
    $stmt_update->execute([$passagers, $vol_id]);
    
    // Insérer un email de confirmation
    $sql_email = "INSERT INTO emails (id_user, sujet, contenu, type, statut) 
                  VALUES (?, ?, ?, 'confirmation', 'envoyé')";
    
    $contenu_email = "Votre réservation pour le vol " . $vol['code_compagnie'] . $vol['numero_vol'] . 
                     " de " . $vol['depart'] . " vers " . $vol['arrivee'] . 
                     " est confirmée. Montant total: " . number_format($prix_total_final, 2, ',', ' ') . "€";
    
    $stmt_email = $pdo->prepare($sql_email);
    $stmt_email->execute([$_SESSION['id_user'], 'Confirmation de réservation', $contenu_email]);
    
    // Valider la transaction
    $pdo->commit();
    
    // Rediriger vers la page de confirmation
    header('Location: confirmation.php?reservation_id=' . $reservation_id);
    exit;
    
} catch (Exception $e) {
    // Annuler la transaction en cas d'erreur
    $pdo->rollBack();
    
    error_log("Erreur lors de la réservation: " . $e->getMessage());
    header('Location: reservation.php?vol_id=' . $vol_id . '&error=erreur_reservation');
    exit;
}
?>
