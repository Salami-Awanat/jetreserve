<?php
session_start();
require_once 'includes/db.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['id_user'])) {
    header('Location: vge64/connexion.php');
    exit;
}

// Vérifier les données du formulaire
if (!isset($_POST['reservation_id']) || !isset($_POST['payment_method'])) {
    header('Location: index.php?error=donnees_manquantes');
    exit;
}

$reservation_id = intval($_POST['reservation_id']);
$payment_method = $_POST['payment_method'];

// Récupérer la réservation
try {
    $sql = "SELECT r.*, v.depart, v.arrivee, v.date_depart, v.prix as prix_vol
            FROM reservations r
            JOIN vols v ON r.id_vol = v.id_vol
            WHERE r.id_reservation = ? AND r.id_user = ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$reservation_id, $_SESSION['id_user']]);
    $reservation = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$reservation) {
        header('Location: index.php?error=reservation_invalide');
        exit;
    }
    
    // Vérifier si la réservation est déjà confirmée
    if ($reservation['statut'] === 'confirmé') {
        header('Location: confirmation.php?id_reservation=' . $reservation_id);
        exit;
    }
    
} catch (PDOException $e) {
    error_log("Erreur récupération réservation: " . $e->getMessage());
    header('Location: index.php?error=erreur_systeme');
    exit;
}

// Simuler un délai de traitement (comme une vraie banque)
sleep(2);

// Simuler une vérification de carte (toujours réussie en simulation)
$card_valid = true;

if ($card_valid) {
    try {
        $pdo->beginTransaction();
        
        // Mettre à jour le statut de la réservation
        $stmt = $pdo->prepare("UPDATE reservations SET statut = 'confirmé' WHERE id_reservation = ?");
        $stmt->execute([$reservation_id]);
        
        // Générer une référence de paiement réaliste
        $reference = 'PAY-' . date('YmdHis') . '-' . str_pad($reservation_id, 6, '0', STR_PAD_LEFT);
        
        // Enregistrer le paiement
        $stmt_payment = $pdo->prepare("
            INSERT INTO paiements (id_reservation, montant, mode_paiement, statut, reference_paiement, date_paiement)
            VALUES (?, ?, ?, 'réussi', ?, NOW())
        ");
        
        $stmt_payment->execute([
            $reservation_id, 
            $reservation['prix_total'], 
            $payment_method, 
            $reference
        ]);
        
        // Mettre à jour les places disponibles du vol
        $stmt_vol = $pdo->prepare("
            UPDATE vols 
            SET places_disponibles = GREATEST(0, places_disponibles - ?) 
            WHERE id_vol = ?
        ");
        $stmt_vol->execute([$reservation['nombre_passagers'], $reservation['id_vol']]);
        
        $pdo->commit();
        
        // Rediriger vers la page de confirmation
        header('Location: confirmation.php?id_reservation=' . $reservation_id . '&reference=' . $reference);
        exit;
        
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Erreur traitement paiement: " . $e->getMessage());
        header('Location: paiement.php?reservation_id=' . $reservation_id . '&error=erreur_paiement');
        exit;
    }
} else {
    header('Location: paiement.php?reservation_id=' . $reservation_id . '&error=carte_refusee');
    exit;
}
?>