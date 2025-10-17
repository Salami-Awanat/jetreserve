<?php
require_once 'includes/db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: connexion.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$vol_id = intval($_POST['vol_id']);
$sieges_ids = json_decode($_POST['sieges']);
$total_amount = floatval($_POST['total_amount']);

try {
    // Commencer une transaction
    $pdo->beginTransaction();
    
    // 1. Créer la réservation
    $stmt = $pdo->prepare("INSERT INTO reservations (id_user, id_vol, statut, date_reservation, nombre_passagers, prix_total) 
                          VALUES (?, ?, 'confirmé', NOW(), ?, ?)");
    $stmt->execute([$user_id, $vol_id, count($sieges_ids), $total_amount]);
    $reservation_id = $pdo->lastInsertId();
    
    // 2. Associer les sièges à la réservation
    $stmt = $pdo->prepare("INSERT INTO reservation_sieges (id_reservation, id_siege, prix_paye) VALUES (?, ?, ?)");
    $prix_siege = $total_amount / count($sieges_ids);
    
    foreach ($sieges_ids as $siege_id) {
        $stmt->execute([$reservation_id, $siege_id, $prix_siege]);
    }
    
    // 3. Mettre à jour les places disponibles
    $stmt = $pdo->prepare("UPDATE vols SET places_disponibles = places_disponibles - ? WHERE id_vol = ?");
    $stmt->execute([count($sieges_ids), $vol_id]);
    
    // 4. Enregistrer le paiement
    $stmt = $pdo->prepare("INSERT INTO paiements (id_reservation, montant, mode_paiement, statut, date_paiement) 
                          VALUES (?, ?, 'carte', 'réussi', NOW())");
    $stmt->execute([$reservation_id, $total_amount]);
    
    // 5. Envoyer un email de confirmation
    $stmt = $pdo->prepare("INSERT INTO emails (id_user, sujet, contenu, type, statut) 
                          VALUES (?, 'Confirmation de réservation', ?, 'confirmation', 'envoyé')");
    $email_content = "Votre réservation #{$reservation_id} pour le vol #{$vol_id} a été confirmée. Montant payé: {$total_amount}€";
    $stmt->execute([$user_id, $email_content]);
    
    // Valider la transaction
    $pdo->commit();
    
    // Rediriger vers la page de confirmation
    header('Location: confirmation.php?reservation_id=' . $reservation_id);
    exit;
    
} catch (Exception $e) {
    $pdo->rollBack();
    header('Location: error.php?message=' . urlencode('Erreur lors du paiement: ' . $e->getMessage()));
    exit;
}
?>