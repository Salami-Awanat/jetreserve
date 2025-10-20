<?php
// Fonctions d'envoi d'email pour JetReserve
require_once __DIR__ . '/../phpmailer/phpmailer/src/Exception.php';
require_once __DIR__ . '/../phpmailer/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../phpmailer/phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Envoie un email de confirmation de réservation
 * 
 * @param PDO $pdo Instance de connexion à la base de données
 * @param int $reservation_id ID de la réservation
 * @return array Tableau avec le statut de l'envoi et un message
 */
function envoyerEmailConfirmationReservation($pdo, $reservation_id) {
    try {
        // Récupérer les détails de la réservation
        $stmt = $pdo->prepare("
            SELECT r.*, v.depart, v.arrivee, v.date_depart, v.date_arrivee, v.numero_vol, 
                   c.nom_compagnie, c.code_compagnie, 
                   u.prenom, u.nom, u.email
            FROM reservations r
            JOIN vols v ON r.id_vol = v.id_vol
            JOIN compagnies c ON v.id_compagnie = c.id_compagnie
            JOIN users u ON r.id_user = u.id_user
            WHERE r.id_reservation = ?
        ");
        $stmt->execute([$reservation_id]);
        $reservation = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$reservation) {
            return ['success' => false, 'message' => 'Réservation introuvable'];
        }
        
        // Récupérer les sièges
        $sql_sieges = "SELECT sa.rang, sa.position, sa.supplement_prix
                   FROM reservation_sieges rs 
                   JOIN sieges_avion sa ON rs.id_siege = sa.id_siege 
                   WHERE rs.id_reservation = ?";
        $stmt_sieges = $pdo->prepare($sql_sieges);
        $stmt_sieges->execute([$reservation_id]);
        $sieges = $stmt_sieges->fetchAll(PDO::FETCH_ASSOC);

        // Configuration PHPMailer
        $mail = new PHPMailer(true);

        // Charger la configuration SMTP
        $smtpConfigPath = __DIR__ . '/../dashboard/smtp_config.php';
        $smtp = [];
        if (file_exists($smtpConfigPath)) {
            $smtp = include $smtpConfigPath;
        }

        // Configuration du serveur SMTP
        if (!empty($smtp) && isset($smtp['use_smtp']) && $smtp['use_smtp']) {
            $mail->isSMTP();
            $mail->Host = $smtp['host'];
            $mail->SMTPAuth = true;
            $mail->Username = $smtp['username'];
            $mail->Password = $smtp['password'];
            $mail->SMTPSecure = $smtp['encryption'];
            $mail->Port = $smtp['port'];
            $mail->setFrom($smtp['from_email'], $smtp['from_name']);
        } else {
            // Configuration par défaut si pas de SMTP
            $mail->setFrom('noreply@jetreserve.com', 'JetReserve');
        }

        // Destinataires
        $mail->addAddress($reservation['email'], $reservation['prenom'] . ' ' . $reservation['nom']);
        
        // Contenu
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = 'Confirmation de votre réservation - JetReserve #' . $reservation_id;
        
        // Formatage de la date et heure
        $date_depart = new DateTime($reservation['date_depart']);
        $date_arrivee = new DateTime($reservation['date_arrivee']);
        
        // Construction du corps de l'email
        $body = '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e0e0e0; border-radius: 5px;">';
        $body .= '<div style="text-align: center; background-color: #0054A4; color: white; padding: 10px; border-radius: 5px 5px 0 0;">';
        $body .= '<h1 style="margin: 0;">Confirmation de Réservation</h1>';
        $body .= '</div>';
        $body .= '<div style="padding: 20px;">';
        $body .= '<p>Bonjour ' . htmlspecialchars($reservation['prenom']) . ' ' . htmlspecialchars($reservation['nom']) . ',</p>';
        $body .= '<p>Nous vous confirmons votre réservation sur JetReserve. Voici les détails de votre vol :</p>';
        
        $body .= '<div style="background-color: #f9f9f9; padding: 15px; border-radius: 5px; margin: 15px 0;">';
        $body .= '<h2 style="color: #0054A4; margin-top: 0;">Détails du Vol</h2>';
        $body .= '<p><strong>Numéro de réservation :</strong> ' . $reservation_id . '</p>';
        $body .= '<p><strong>Compagnie :</strong> ' . htmlspecialchars($reservation['nom_compagnie']) . ' (' . htmlspecialchars($reservation['code_compagnie']) . ')</p>';
        $body .= '<p><strong>Numéro de vol :</strong> ' . htmlspecialchars($reservation['numero_vol']) . '</p>';
        $body .= '<p><strong>Départ :</strong> ' . htmlspecialchars($reservation['depart']) . ' - ' . $date_depart->format('d/m/Y H:i') . '</p>';
        $body .= '<p><strong>Arrivée :</strong> ' . htmlspecialchars($reservation['arrivee']) . ' - ' . $date_arrivee->format('d/m/Y H:i') . '</p>';
        $body .= '<p><strong>Nombre de passagers :</strong> ' . $reservation['nombre_passagers'] . '</p>';
        
        // Sièges réservés
        if (!empty($sieges)) {
            $body .= '<p><strong>Sièges réservés :</strong> ';
            foreach ($sieges as $index => $siege) {
                $body .= $siege['rang'] . $siege['position'];
                if ($index < count($sieges) - 1) {
                    $body .= ', ';
                }
            }
            $body .= '</p>';
        }
        
        $body .= '<p><strong>Prix total :</strong> ' . number_format($reservation['prix_total'], 2, ',', ' ') . ' €</p>';
        $body .= '</div>';
        
        $body .= '<p>Nous vous remercions de votre confiance et vous souhaitons un excellent voyage.</p>';
        $body .= '<p>L\'équipe JetReserve</p>';
        $body .= '</div>';
        
        $body .= '<div style="text-align: center; background-color: #f5f5f5; padding: 10px; border-radius: 0 0 5px 5px; font-size: 12px; color: #666;">';
        $body .= '<p>Ce message est envoyé automatiquement, merci de ne pas y répondre.</p>';
        $body .= '<p>© ' . date('Y') . ' JetReserve - Tous droits réservés</p>';
        $body .= '</div>';
        $body .= '</div>';
        
        $mail->Body = $body;
        
        // Version texte brut alternative
        $textBody = "Confirmation de Réservation\n\n";
        $textBody .= "Bonjour " . $reservation['prenom'] . " " . $reservation['nom'] . ",\n\n";
        $textBody .= "Nous vous confirmons votre réservation sur JetReserve. Voici les détails de votre vol :\n\n";
        $textBody .= "Détails du Vol\n";
        $textBody .= "Numéro de réservation : " . $reservation_id . "\n";
        $textBody .= "Compagnie : " . $reservation['nom_compagnie'] . " (" . $reservation['code_compagnie'] . ")\n";
        $textBody .= "Numéro de vol : " . $reservation['numero_vol'] . "\n";
        $textBody .= "Départ : " . $reservation['depart'] . " - " . $date_depart->format('d/m/Y H:i') . "\n";
        $textBody .= "Arrivée : " . $reservation['arrivee'] . " - " . $date_arrivee->format('d/m/Y H:i') . "\n";
        $textBody .= "Nombre de passagers : " . $reservation['nombre_passagers'] . "\n";
        
        if (!empty($sieges)) {
            $textBody .= "Sièges réservés : ";
            foreach ($sieges as $index => $siege) {
                $textBody .= $siege['rang'] . $siege['position'];
                if ($index < count($sieges) - 1) {
                    $textBody .= ", ";
                }
            }
            $textBody .= "\n";
        }
        
        $textBody .= "Prix total : " . number_format($reservation['prix_total'], 2, ',', ' ') . " €\n\n";
        $textBody .= "Nous vous remercions de votre confiance et vous souhaitons un excellent voyage.\n\n";
        $textBody .= "L'équipe JetReserve\n\n";
        $textBody .= "Ce message est envoyé automatiquement, merci de ne pas y répondre.\n";
        $textBody .= "© " . date('Y') . " JetReserve - Tous droits réservés";
        
        $mail->AltBody = $textBody;
        
        // Envoyer l'email
        $mail->send();
        
        return ['success' => true, 'message' => 'Email de confirmation envoyé avec succès'];
        
    } catch (Exception $e) {
        error_log("Erreur d'envoi d'email: " . $e->getMessage());
        return ['success' => false, 'message' => "L'email n'a pas pu être envoyé. Erreur: " . $e->getMessage()];
    }
}