<?php
session_start();
require_once 'includes/db.php';

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Vous devez être connecté pour effectuer un paiement.";
    header('Location: vge64/connexion.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validation des données
        $reservation_id = $_POST['reservation_id'] ?? null;
        $payment_method = $_POST['payment_method'] ?? null;
        
        if (!$reservation_id) {
            throw new Exception("ID de réservation manquant.");
        }

        // Récupérer et vérifier la réservation
        $stmt = $pdo->prepare("SELECT r.*, v.date_depart, v.places_disponibles 
                              FROM reservations r 
                              JOIN vols v ON r.id_vol = v.id_vol 
                              WHERE r.id_reservation = ? AND r.id_user = ?");
        $stmt->execute([$reservation_id, $_SESSION['user_id']]);
        $reservation = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$reservation) {
            throw new Exception("Réservation non trouvée ou vous n'y avez pas accès.");
        }

        // Vérifier le statut de la réservation
        if ($reservation['statut'] !== 'en attente') {
            throw new Exception("Cette réservation ne peut plus être payée.");
        }

        // Vérifier que le vol n'a pas déjà décollé
        $departure_time = new DateTime($reservation['date_depart']);
        $now = new DateTime();
        if ($departure_time <= $now) {
            throw new Exception("Impossible de payer un vol qui a déjà décollé.");
        }

        // Vérifier qu'il reste des places
        if ($reservation['places_disponibles'] < $reservation['nombre_passagers']) {
            throw new Exception("Plus assez de places disponibles sur ce vol.");
        }

        // Simuler le traitement du paiement (à remplacer par un vrai système de paiement)
        $payment_result = processPaymentSimulation($_POST);

        if ($payment_result['success']) {
            // Commencer une transaction
            $pdo->beginTransaction();

            try {
                // 1. Mettre à jour le statut de la réservation
                $stmt = $pdo->prepare("UPDATE reservations SET statut = 'confirmé' WHERE id_reservation = ?");
                $stmt->execute([$reservation_id]);

                // 2. Créer l'enregistrement de paiement
                $stmt = $pdo->prepare("
                    INSERT INTO paiements (id_reservation, montant, mode_paiement, statut, date_paiement) 
                    VALUES (?, ?, ?, 'réussi', NOW())
                ");
                $stmt->execute([
                    $reservation_id,
                    $reservation['prix_total'] + 34, // + frais service et taxes
                    $payment_method
                ]);

                // 3. Mettre à jour les places disponibles sur le vol
                $stmt = $pdo->prepare("UPDATE vols SET places_disponibles = places_disponibles - ? WHERE id_vol = ?");
                $stmt->execute([$reservation['nombre_passagers'], $reservation['id_vol']]);

                // 4. Marquer les sièges comme réservés (en s'assurant qu'ils ne sont pas déjà pris)
                $stmt = $pdo->prepare("
                    SELECT rs.id_siege 
                    FROM reservation_sieges rs 
                    WHERE rs.id_reservation = ? 
                    AND NOT EXISTS (
                        SELECT 1 FROM reservation_sieges rs2 
                        JOIN reservations r2 ON rs2.id_reservation = r2.id_reservation 
                        WHERE rs2.id_siege = rs.id_siege 
                        AND r2.statut IN ('confirmé', 'en attente')
                        AND r2.id_reservation != ?
                    )
                ");
                $stmt->execute([$reservation_id, $reservation_id]);
                $valid_sieges = $stmt->fetchAll(PDO::FETCH_COLUMN);

                if (count($valid_sieges) !== $reservation['nombre_passagers']) {
                    throw new Exception("Un ou plusieurs sièges ne sont plus disponibles.");
                }

                // 5. Envoyer un email de confirmation (simulation)
                sendConfirmationEmail($reservation_id, $_SESSION['user_id']);

                // Valider la transaction
                $pdo->commit();

                // Rediriger vers la page de confirmation
                header('Location: confirmation.php?reservation_id=' . $reservation_id . '&payment_id=' . $pdo->lastInsertId());
                exit;

            } catch (Exception $e) {
                // Annuler la transaction en cas d'erreur
                $pdo->rollBack();
                throw $e;
            }

        } else {
            // Enregistrer l'échec du paiement
            $stmt = $pdo->prepare("
                INSERT INTO paiements (id_reservation, montant, mode_paiement, statut, date_paiement) 
                VALUES (?, ?, ?, 'échoué', NOW())
            ");
            $stmt->execute([
                $reservation_id,
                $reservation['prix_total'] + 34,
                $payment_method
            ]);

            throw new Exception("Le paiement a échoué : " . $payment_result['message']);
        }

    } catch (Exception $e) {
        // Journaliser l'erreur
        error_log("Erreur paiement réservation " . ($reservation_id ?? 'inconnue') . ": " . $e->getMessage());
        
        // Rediriger vers la page d'erreur
        $_SESSION['payment_error'] = $e->getMessage();
        header('Location: payment.php?reservation_id=' . ($reservation_id ?? '') . '&error=1');
        exit;
    }
} else {
    header('Location: index.php');
    exit;
}

/**
 * Simulation du traitement du paiement
 * À remplacer par une vraie intégration de paiement (Stripe, PayPal, etc.)
 */
function processPaymentSimulation($payment_data) {
    // Simulation de délai de traitement
    sleep(2);
    
    // Vérifications de base pour la simulation
    if ($payment_data['payment_method'] === 'carte') {
        $card_number = str_replace(' ', '', $payment_data['card_number'] ?? '');
        $expiry_date = $payment_data['expiry_date'] ?? '';
        $cvv = $payment_data['cvv'] ?? '';
        
        // Vérification basique de la carte
        if (strlen($card_number) !== 16 || !is_numeric($card_number)) {
            return ['success' => false, 'message' => 'Numéro de carte invalide'];
        }
        
        if (!preg_match('/^\d{2}\/\d{2}$/', $expiry_date)) {
            return ['success' => false, 'message' => 'Date d\'expiration invalide'];
        }
        
        // Vérifier que la carte n'est pas expirée
        $expiry = explode('/', $expiry_date);
        $month = intval($expiry[0]);
        $year = intval($expiry[1]) + 2000; // Supposons format AA -> 20AA
        
        $now = new DateTime();
        $expiry_date_obj = DateTime::createFromFormat('Y-m', $year . '-' . $month);
        $expiry_date_obj->modify('last day of this month');
        
        if ($expiry_date_obj < $now) {
            return ['success' => false, 'message' => 'Carte expirée'];
        }
        
        if (strlen($cvv) !== 3 || !is_numeric($cvv)) {
            return ['success' => false, 'message' => 'CVV invalide'];
        }
        
        // Simulation d'un refus aléatoire (5% de chance)
        if (mt_rand(1, 20) === 1) {
            return ['success' => false, 'message' => 'Paiement refusé par la banque'];
        }
    }
    
    // Simulation réussie dans 95% des cas
    return ['success' => true, 'message' => 'Paiement traité avec succès'];
}

/**
 * Simulation d'envoi d'email de confirmation
 */
function sendConfirmationEmail($reservation_id, $user_id) {
    // Dans une vraie application, vous enverriez un email ici
    // Pour l'instant, on se contente de logger
    error_log("Email de confirmation envoyé pour la réservation $reservation_id à l'utilisateur $user_id");
    
    // Vous pourriez utiliser PHPMailer ou une autre librairie :
    /*
    $mail = new PHPMailer();
    $mail->setFrom('noreply@jetreserve.com', 'JetReserve');
    $mail->addAddress($user_email);
    $mail->Subject = 'Confirmation de votre réservation';
    $mail->Body = "Votre réservation #$reservation_id a été confirmée.";
    $mail->send();
    */
    
    return true;
}