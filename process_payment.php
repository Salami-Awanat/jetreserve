<?php
session_start();
require_once 'includes/db.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['id_user'])) {
    header('Location: vge64/connexion.php');
    exit;
}

// Vérifier si c'est une requête POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// Récupérer les données du formulaire
$reservation_id = isset($_POST['reservation_id']) ? intval($_POST['reservation_id']) : 0;
$payment_method = isset($_POST['payment_method']) ? $_POST['payment_method'] : 'carte';
$card_name = isset($_POST['card_name']) ? trim($_POST['card_name']) : '';
$card_number = isset($_POST['card_number']) ? preg_replace('/\s+/', '', $_POST['card_number']) : '';
$expiry_date = isset($_POST['expiry_date']) ? trim($_POST['expiry_date']) : '';
$cvv = isset($_POST['cvv']) ? trim($_POST['cvv']) : '';

// Validation
$erreurs = [];

if ($reservation_id <= 0) {
    $erreurs[] = "ID de réservation invalide";
}

if ($payment_method === 'carte') {
    if (empty($card_name)) {
        $erreurs[] = "Le nom sur la carte est requis";
    }
    if (empty($card_number) || strlen($card_number) !== 16 || !is_numeric($card_number)) {
        $erreurs[] = "Numéro de carte invalide (16 chiffres requis)";
    }
    if (empty($expiry_date) || !preg_match('/^\d{2}\/\d{2}$/', $expiry_date)) {
        $erreurs[] = "Date d'expiration invalide (format MM/AA requis)";
    }
    if (empty($cvv) || strlen($cvv) !== 3 || !is_numeric($cvv)) {
        $erreurs[] = "CVV invalide (3 chiffres requis)";
    }
}

if (empty($erreurs)) {
    try {
        // Récupérer la réservation
        $stmt = $pdo->prepare("
            SELECT r.*, v.depart, v.arrivee, v.date_depart, v.date_arrivee, 
                   c.nom_compagnie, c.code_compagnie, a.modele as avion_modele,
                   u.prenom, u.nom, u.email
            FROM reservations r
            JOIN vols v ON r.id_vol = v.id_vol
            JOIN compagnies c ON v.id_compagnie = c.id_compagnie
            JOIN avions a ON v.id_avion = a.id_avion
            JOIN users u ON r.id_user = u.id_user
            WHERE r.id_reservation = ? AND r.id_user = ? AND r.statut = 'en attente'
        ");
        $stmt->execute([$reservation_id, $_SESSION['id_user']]);
        $reservation = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$reservation) {
            throw new Exception("Réservation non trouvée ou déjà traitée");
        }
        
        // DÉBUT DE LA TRANSACTION
        $pdo->beginTransaction();
        
        // Simuler le traitement du paiement (toujours réussi pour la démo)
        $payment_status = 'réussi';
        $transaction_id = 'TXN' . time() . rand(1000, 9999);
        
        // Enregistrer le paiement
        $stmt_paiement = $pdo->prepare("
            INSERT INTO paiements (id_reservation, montant, mode_paiement, statut, date_paiement)
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt_paiement->execute([$reservation_id, $reservation['prix_total'], $payment_method, $payment_status]);
        
        // Mettre à jour le statut de la réservation
        $stmt_update = $pdo->prepare("
            UPDATE reservations 
            SET statut = 'confirmé' 
            WHERE id_reservation = ?
        ");
        $stmt_update->execute([$reservation_id]);
        
        // Envoyer un email de confirmation
        try {
            $stmt_email = $pdo->prepare("
                INSERT INTO emails (id_user, sujet, contenu, type, statut, date_envoi)
                VALUES (?, ?, ?, 'confirmation', 'envoyé', NOW())
            ");
            $sujet_email = "Confirmation de votre réservation JetReserve #" . $reservation_id;
            $contenu_email = "
Bonjour " . $reservation['prenom'] . ",

Votre réservation a été confirmée avec succès !

Détails de votre réservation :
- Référence : JR" . str_pad($reservation_id, 6, '0', STR_PAD_LEFT) . "
- Vol : " . $reservation['depart'] . " → " . $reservation['arrivee'] . "
- Date : " . date('d/m/Y à H:i', strtotime($reservation['date_depart'])) . "
- Compagnie : " . $reservation['nom_compagnie'] . "
- Passagers : " . $reservation['nombre_passagers'] . "
- Montant total : " . number_format($reservation['prix_total'], 2, ',', ' ') . "€

Mode de paiement : " . ucfirst($payment_method) . "
Transaction : " . $transaction_id . "

Merci d'avoir choisi JetReserve !

Cordialement,
L'équipe JetReserve
            ";
            $stmt_email->execute([$_SESSION['id_user'], $sujet_email, $contenu_email]);
        } catch (Exception $e) {
            // Ne pas bloquer le processus pour une erreur d'email
            error_log("Erreur envoi email: " . $e->getMessage());
        }
        
        // VALIDATION DE LA TRANSACTION
        $pdo->commit();
        
        // Rediriger vers la page de confirmation
        header('Location: confirmation.php?id_reservation=' . $reservation_id);
        exit;
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $erreur_message = "Erreur lors du traitement du paiement: " . $e->getMessage();
        $erreurs[] = $erreur_message;
        error_log("Erreur paiement: " . $e->getMessage());
    }
}

// Si on arrive ici, c'est qu'il y a eu une erreur
// Rediriger vers la page de paiement avec l'erreur
$error_message = implode(", ", $erreurs);
header('Location: paiement.php?reservation_id=' . $reservation_id . '&error=' . urlencode($error_message));
exit;
?>