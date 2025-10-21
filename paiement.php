<?php
// Déclarations use doivent être en tout début de fichier
require_once __DIR__ . '/phpmailer/phpmailer/src/Exception.php';
require_once __DIR__ . '/phpmailer/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/phpmailer/phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();
require_once 'includes/db.php';

// Vérifier si l'utilisateur est connecté et a une réservation en cours
if (!isset($_SESSION['id_user']) || !isset($_GET['reservation_id'])) {
    header('Location: index.php');
    exit;
}

$user_id = $_SESSION['id_user'];
$reservation_id = intval($_GET['reservation_id']);

// Récupérer les informations de la réservation
try {
    $stmt = $pdo->prepare("
        SELECT r.*, v.depart, v.arrivee, v.date_depart, v.date_arrivee, 
               v.numero_vol, c.nom_compagnie, c.code_compagnie,
               u.nom, u.prenom, u.email
        FROM reservations r
        JOIN vols v ON r.id_vol = v.id_vol
        JOIN compagnies c ON v.id_compagnie = c.id_compagnie
        JOIN users u ON r.id_user = u.id_user
        WHERE r.id_reservation = ? AND r.id_user = ? AND r.statut = 'en attente'
    ");
    $stmt->execute([$reservation_id, $user_id]);
    $reservation = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$reservation) {
        die("Réservation non trouvée ou déjà traitée");
    }
    
} catch (PDOException $e) {
    die("Erreur de base de données: " . $e->getMessage());
}

// Traitement du paiement
$erreur = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simuler_paiement'])) {
    // Simulation de paiement réussi
    try {
        $pdo->beginTransaction();
        
        // Mettre à jour le statut de la réservation
        $stmt = $pdo->prepare("
            UPDATE reservations 
            SET statut = 'confirmé' 
            WHERE id_reservation = ? AND statut = 'en attente'
        ");
        $stmt->execute([$reservation_id]);
        
        // Enregistrer le paiement
        $stmt = $pdo->prepare("
            INSERT INTO paiements (id_reservation, montant, mode_paiement, statut, date_paiement)
            VALUES (?, ?, 'carte', 'réussi', NOW())
        ");
        $stmt->execute([$reservation_id, $reservation['prix_total']]);
        
        // Mettre à jour les places disponibles
        $stmt = $pdo->prepare("
            UPDATE vols 
            SET places_disponibles = GREATEST(0, places_disponibles - ?)
            WHERE id_vol = ?
        ");
        $stmt->execute([$reservation['nombre_passagers'], $reservation['id_vol']]);
        
        // Envoyer un email de confirmation avec PHPMailer
        try {
            $mail = new PHPMailer(true);
            
            // Configuration du serveur SMTP (à adapter selon votre configuration)
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // Remplacez par votre serveur SMTP
            $mail->SMTPAuth = true;
            $mail->Username = 'agbalessifloriane69@gmail.com'; // Remplacez par votre email
            $mail->Password ='sgyd alra jjme wwpr'; // Remplacez par votre mot de passe d'application
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            $mail->CharSet = 'UTF-8';
            
            // Destinataires
            $mail->setFrom('noreply@jetreserve.com', 'JetReserve');
            $mail->addAddress($reservation['email'], $reservation['prenom'] . ' ' . $reservation['nom']);
            
            // Contenu de l'email
            $mail->isHTML(true);
            $mail->Subject = 'Confirmation de votre réservation JetReserve #' . $reservation_id;
            
            $mail->Body = "
                <!DOCTYPE html>
                <html>
                <head>
                    <meta charset='UTF-8'>
                    <style>
                        body { font-family: Arial, sans-serif; color: #333; line-height: 1.6; }
                        .header { background: #0054A4; color: white; padding: 20px; text-align: center; }
                        .content { padding: 20px; }
                        .details { background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #0054A4; }
                        .footer { background: #343a40; color: white; padding: 15px; text-align: center; margin-top: 20px; }
                        .success { color: #28a745; font-weight: bold; }
                        .info { color: #0054A4; }
                    </style>
                </head>
                <body>
                    <div class='header'>
                        <h1>✈ JetReserve</h1>
                        <h2>Confirmation de Réservation</h2>
                    </div>
                    <div class='content'>
                        <p>Bonjour <strong>{$reservation['prenom']} {$reservation['nom']}</strong>,</p>
                        <p class='success'>Votre réservation a été confirmée avec succès !</p>
                        
                        <div class='details'>
                            <h3 class='info'>Détails de votre réservation :</h3>
                            <p><strong>Référence :</strong> JTR" . str_pad($reservation_id, 6, '0', STR_PAD_LEFT) . "</p>
                            <p><strong>Vol :</strong> {$reservation['depart']} → {$reservation['arrivee']}</p>
                            <p><strong>Compagnie :</strong> {$reservation['nom_compagnie']} ({$reservation['code_compagnie']}{$reservation['numero_vol']})</p>
                            <p><strong>Date de départ :</strong> " . date('d/m/Y à H:i', strtotime($reservation['date_depart'])) . "</p>
                            <p><strong>Date d'arrivée :</strong> " . date('d/m/Y à H:i', strtotime($reservation['date_arrivee'])) . "</p>
                            <p><strong>Nombre de passagers :</strong> {$reservation['nombre_passagers']}</p>
                            <p><strong>Montant payé :</strong> " . number_format($reservation['prix_total'], 2, ',', ' ') . " €</p>
                        </div>
                        
                        <h3 class='info'>Instructions importantes :</h3>
                        <ul>
                            <li>Présentez-vous à l'aéroport 2 heures avant le décollage</li>
                            <li>Ayez votre pièce d'identité et cette confirmation avec vous</li>
                            <li>L'enregistrement en ligne ouvrira 24h avant le vol</li>
                            <li>Les bagages en soute doivent être déposés au plus tard 1h avant le décollage</li>
                        </ul>
                        
                        <p>Merci de votre confiance et bon voyage !</p>
                    </div>
                    <div class='footer'>
                        <p><strong>JetReserve - Service Client</strong></p>
                        <p>📧 contact@jetreserve.com | 📞 +33 1 23 45 67 89</p>
                    </div>
                </body>
                </html>
            ";
            
            $mail->AltBody = "Confirmation de réservation JetReserve. Référence: JTR" . str_pad($reservation_id, 6, '0', STR_PAD_LEFT) . ". Vol: {$reservation['depart']} - {$reservation['arrivee']}. Date: " . date('d/m/Y H:i', strtotime($reservation['date_depart'])) . ". Montant: " . number_format($reservation['prix_total'], 2, ',', ' ') . " €. Présentez-vous 2h avant le décollage.";
            
            $mail->send();
            
            // Enregistrer dans la table emails
            $stmt_email = $pdo->prepare("
                INSERT INTO emails (id_user, sujet, contenu, type, statut, date_envoi)
                VALUES (?, ?, ?, 'confirmation', 'envoyé', NOW())
            ");
            $stmt_email->execute([
                $user_id, 
                'Confirmation de votre réservation JetReserve #' . $reservation_id,
                "Email de confirmation envoyé pour la réservation JTR" . str_pad($reservation_id, 6, '0', STR_PAD_LEFT) . " - Vol: {$reservation['depart']} -> {$reservation['arrivee']} - Montant: " . number_format($reservation['prix_total'], 2, ',', ' ') . " €",
            ]);
            
        } catch (Exception $e) {
            // En cas d'erreur d'envoi d'email, on continue mais on log l'erreur
            error_log("Erreur envoi email: " . $e->getMessage());
            
            // Enregistrer quand même dans la table emails
            $stmt_email = $pdo->prepare("
                INSERT INTO emails (id_user, sujet, contenu, type, statut, date_envoi)
                VALUES (?, ?, ?, 'confirmation', 'échoué', NOW())
            ");
            $stmt_email->execute([
                $user_id, 
                'Confirmation de votre réservation JetReserve #' . $reservation_id,
                "Échec envoi email pour réservation JTR" . str_pad($reservation_id, 6, '0', STR_PAD_LEFT) . " - Erreur: " . $e->getMessage(),
            ]);
        }
        
        $pdo->commit();
        
        // REDIRECTION IMMÉDIATE VERS LA CONFIRMATION
        header('Location: confirmation.php?id_reservation=' . $reservation_id);
        exit;
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $erreur = "Erreur lors du traitement du paiement: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paiement Sécurisé - JetReserve</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
    <style>
        :root {
            --primary: #0054A4;
            --secondary: #7f8c8d;
            --success: #28a745;
            --danger: #dc3545;
            --warning: #ffc107;
            --info: #17a2b8;
            --light: #f8f9fa;
            --dark: #343a40;
            --white: #ffffff;
            --corsair-blue: #0054A4;
            --corsair-dark-blue: #003366;
            --corsair-light-blue: #0077CC;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            color: var(--dark);
            line-height: 1.6;
            min-height: 100vh;
        }
        
        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }
        
        header {
            background-color: var(--white);
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
        }
        
        .logo {
            font-size: 28px;
            font-weight: 700;
            color: var(--corsair-blue);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .payment-container {
            max-width: 1000px;
            margin: 2rem auto;
            background: var(--white);
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .payment-header {
            background: linear-gradient(135deg, var(--corsair-blue), var(--corsair-dark-blue));
            color: var(--white);
            padding: 2.5rem;
            text-align: center;
        }
        
        .payment-title {
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .payment-content {
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
            padding: 2rem;
        }
        
        .order-summary {
            flex: 1;
            min-width: 300px;
            background: var(--light);
            border-radius: 10px;
            padding: 2rem;
        }
        
        .payment-form {
            flex: 2;
            min-width: 500px;
        }
        
        .section-title {
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            color: var(--corsair-blue);
            border-bottom: 2px solid var(--corsair-blue);
            padding-bottom: 0.5rem;
        }
        
        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #ddd;
        }
        
        .total {
            font-size: 1.3rem;
            font-weight: bold;
            color: var(--corsair-blue);
            border-top: 2px solid #ddd;
            padding-top: 1rem;
            margin-top: 1rem;
        }
        
        .payment-methods {
            display: flex;
            gap: 15px;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }
        
        .payment-method {
            flex: 1;
            min-width: 150px;
            text-align: center;
            padding: 1.5rem;
            border: 2px solid #ddd;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .payment-method:hover {
            border-color: var(--corsair-blue);
        }
        
        .payment-method.selected {
            border-color: var(--corsair-blue);
            background: rgba(0, 84, 164, 0.1);
        }
        
        .payment-icon {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            color: var(--corsair-blue);
        }
        
        .payment-details {
            margin-top: 2rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--dark);
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #ddd;
            border-radius: 6px;
            font-family: 'Poppins', sans-serif;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--corsair-blue);
            box-shadow: 0 0 0 3px rgba(0, 84, 164, 0.1);
        }
        
        .form-row {
            display: flex;
            gap: 15px;
        }
        
        .form-row .form-group {
            flex: 1;
        }
        
        .security-notice {
            background: #e3f2fd;
            border: 1px solid var(--corsair-blue);
            border-radius: 8px;
            padding: 1.5rem;
            margin: 2rem 0;
            text-align: center;
        }
        
        .security-notice i {
            color: var(--success);
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 6px;
            margin-bottom: 1.5rem;
        }
        
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        
        .loading {
            display: none;
            text-align: center;
            padding: 2rem;
        }
        
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid var(--corsair-blue);
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin: 0 auto 1rem;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .btn {
            padding: 15px 30px;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            border: none;
            font-family: 'Poppins', sans-serif;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }
        
        .btn-success {
            background: linear-gradient(135deg, var(--success), #1e7e34);
            color: var(--white);
            font-weight: 600;
        }
        
        .btn-full {
            width: 100%;
            justify-content: center;
        }
        
        .bank-cards {
            display: flex;
            gap: 10px;
            margin-top: 1rem;
            justify-content: center;
        }
        
        .bank-card {
            width: 60px;
            height: 40px;
            background: var(--light);
            border: 1px solid #ddd;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: var(--corsair-blue);
        }
        
        .qr-container {
            text-align: center;
            margin: 2rem 0;
            padding: 1rem;
            background: var(--light);
            border-radius: 8px;
        }
        
        .qr-code {
            margin: 0 auto;
            background: white;
            padding: 10px;
            border-radius: 8px;
            display: inline-block;
        }
        
        .simulation-notice {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 1rem;
            margin: 1rem 0;
            text-align: center;
            color: #856404;
        }
        
        @media (max-width: 768px) {
            .payment-content {
                flex-direction: column;
            }
            
            .payment-form, .order-summary {
                min-width: 100%;
            }
            
            .payment-methods {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="header-top">
                <a href="index.php" class="logo">
                    <i class="fas fa-plane"></i>
                    Jet<span>Reserve</span>
                </a>
                <div>
                    <span>Bonjour, <?php echo htmlspecialchars($reservation['prenom']); ?></span>
                </div>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="payment-container">
            <div class="payment-header">
                <h1 class="payment-title"><i class="fas fa-credit-card"></i> Paiement Sécurisé</h1>
                <p>Finalisez votre réservation en procédant au paiement</p>
            </div>
            
            <?php if (isset($erreur)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo $erreur; ?>
                </div>
            <?php endif; ?>
            
            <div class="payment-content">
                <div class="order-summary">
                    <h3 class="section-title">Résumé de la commande</h3>
                    
                    <div class="summary-item">
                        <span>Référence:</span>
                        <span><strong>JTR<?php echo str_pad($reservation_id, 6, '0', STR_PAD_LEFT); ?></strong></span>
                    </div>
                    
                    <div class="summary-item">
                        <span>Vol:</span>
                        <span><?php echo htmlspecialchars($reservation['depart']); ?> → <?php echo htmlspecialchars($reservation['arrivee']); ?></span>
                    </div>
                    
                    <div class="summary-item">
                        <span>Date:</span>
                        <span><?php echo date('d/m/Y H:i', strtotime($reservation['date_depart'])); ?></span>
                    </div>
                    
                    <div class="summary-item">
                        <span>Passagers:</span>
                        <span><?php echo $reservation['nombre_passagers']; ?></span>
                    </div>
                    
                    <div class="summary-item">
                        <span>Compagnie:</span>
                        <span><?php echo htmlspecialchars($reservation['nom_compagnie']); ?></span>
                    </div>
                    
                    <div class="total">
                        <span>Total à payer:</span>
                        <span><?php echo number_format($reservation['prix_total'], 2, ',', ' '); ?> €</span>
                    </div>

                    <div class="qr-container">
                        <h4><i class="fas fa-qrcode"></i> Code QR de Paiement</h4>
                        <div class="qr-code" id="qrCode"></div>
                        <p style="margin-top: 1rem; font-size: 0.9rem; color: var(--secondary);">
                            Scannez ce code pour payer via votre application mobile
                        </p>
                    </div>
                </div>
                
                <div class="payment-form">
                    <form method="POST" id="paymentForm">
                        <h3 class="section-title">Méthode de paiement</h3>
                        
                        <div class="payment-methods">
                            <div class="payment-method selected" data-method="carte">
                                <div class="payment-icon"><i class="fas fa-credit-card"></i></div>
                                <div>Carte bancaire</div>
                            </div>
                            <div class="payment-method" data-method="paypal">
                                <div class="payment-icon"><i class="fab fa-paypal"></i></div>
                                <div>PayPal</div>
                            </div>
                            <div class="payment-method" data-method="mobile">
                                <div class="payment-icon"><i class="fas fa-mobile-alt"></i></div>
                                <div>Mobile Money</div>
                            </div>
                        </div>
                        
                        <input type="hidden" name="mode_paiement" id="modePaiement" value="carte">
                        
                        <div class="payment-details" id="carteDetails">
                            <div class="form-group">
                                <label for="numero_carte">Numéro de carte</label>
                                <input type="text" id="numero_carte" name="numero_carte" 
                                       class="form-control" placeholder="1234 5678 9012 3456" 
                                       maxlength="19" required value="4242 4242 4242 4242">
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="date_expiration">Date d'expiration</label>
                                    <input type="text" id="date_expiration" name="date_expiration" 
                                           class="form-control" placeholder="MM/AA" 
                                           maxlength="5" required value="12/25">
                                </div>
                                <div class="form-group">
                                    <label for="cvv">Code de sécurité</label>
                                    <input type="text" id="cvv" name="cvv" 
                                           class="form-control" placeholder="123" 
                                           maxlength="3" required value="123">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="titulaire_carte">Nom du titulaire</label>
                                <input type="text" id="titulaire_carte" name="titulaire_carte" 
                                       class="form-control" placeholder="JOHN DOE" required 
                                       value="<?php echo htmlspecialchars($reservation['prenom'] . ' ' . $reservation['nom']); ?>">
                            </div>
                            
                            <div class="bank-cards">
                                <div class="bank-card"><i class="fab fa-cc-visa"></i></div>
                                <div class="bank-card"><i class="fab fa-cc-mastercard"></i></div>
                                <div class="bank-card"><i class="fab fa-cc-amex"></i></div>
                                <div class="bank-card"><i class="fab fa-cc-discover"></i></div>
                            </div>
                        </div>
                        
                        <div class="payment-details" id="paypalDetails" style="display: none;">
                            <div class="alert alert-info">
                                <i class="fab fa-paypal"></i>
                                Vous serez redirigé vers PayPal pour finaliser votre paiement.
                            </div>
                        </div>
                        
                        <div class="payment-details" id="mobileDetails" style="display: none;">
                            <div class="alert alert-info">
                                <i class="fas fa-mobile-alt"></i>
                                Instructions de paiement par Mobile Money seront affichées après confirmation.
                            </div>
                        </div>
                        
                        
                        
                        <div class="security-notice">
                            <i class="fas fa-lock"></i>
                            <h4>Paiement 100% sécurisé</h4>
                            <p>Vos données sont cryptées et protégées par le système de sécurité JetReserve</p>
                        </div>
                        
                        <div class="loading" id="loading">
                            <div class="spinner"></div>
                            <p>Traitement de votre paiement...</p>
                            <p style="font-size: 0.9rem; color: var(--secondary);">
                                Vérification en cours avec notre processeur de paiement...
                            </p>
                        </div>
                        
                        <button type="submit" name="simuler_paiement" class="btn btn-success btn-full" id="submitBtn">
                            <i class="fas fa-lock"></i> Confirmer le paiement de <?php echo number_format($reservation['prix_total'], 2, ',', ' '); ?> €
                        </button>
                        
                        <p style="text-align: center; margin-top: 1rem; color: var(--secondary); font-size: 0.9rem;">
                            <i class="fas fa-shield-alt"></i> Transaction sécurisée SSL 256-bit
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const paymentMethods = document.querySelectorAll('.payment-method');
            const modePaiement = document.getElementById('modePaiement');
            const paymentDetails = document.querySelectorAll('.payment-details');
            
            // Générer le QR Code
            const qrData = `JETRESERVE|REF:JTR<?php echo str_pad($reservation_id, 6, '0', STR_PAD_LEFT); ?>|MONTANT:<?php echo $reservation['prix_total']; ?>EUR|DATE:<?php echo date('Y-m-d H:i:s'); ?>`;
            QRCode.toCanvas(document.getElementById('qrCode'), qrData, {
                width: 150,
                height: 150,
                margin: 1,
                color: {
                    dark: '#0054A4',
                    light: '#FFFFFF'
                }
            }, function(error) {
                if (error) console.error(error);
            });
            
            // Gérer la sélection de la méthode de paiement
            paymentMethods.forEach(method => {
                method.addEventListener('click', function() {
                    paymentMethods.forEach(m => m.classList.remove('selected'));
                    this.classList.add('selected');
                    
                    const methodValue = this.getAttribute('data-method');
                    modePaiement.value = methodValue;
                    
                    paymentDetails.forEach(detail => {
                        detail.style.display = 'none';
                    });
                    
                    document.getElementById(methodValue + 'Details').style.display = 'block';
                });
            });
            
            // Formater le numéro de carte
            const numeroCarte = document.getElementById('numero_carte');
            if (numeroCarte) {
                numeroCarte.addEventListener('input', function(e) {
                    let value = e.target.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
                    let formattedValue = '';
                    
                    for (let i = 0; i < value.length; i++) {
                        if (i > 0 && i % 4 === 0) {
                            formattedValue += ' ';
                        }
                        formattedValue += value[i];
                    }
                    
                    e.target.value = formattedValue;
                });
            }
            
            // Formater la date d'expiration
            const dateExpiration = document.getElementById('date_expiration');
            if (dateExpiration) {
                dateExpiration.addEventListener('input', function(e) {
                    let value = e.target.value.replace(/\//g, '').replace(/[^0-9]/gi, '');
                    
                    if (value.length >= 2) {
                        value = value.substring(0, 2) + '/' + value.substring(2, 4);
                    }
                    
                    e.target.value = value;
                });
            }
            
            // Gestion de la soumission du formulaire
            let formSubmitted = false;
            document.getElementById('paymentForm').addEventListener('submit', function(e) {
                if (formSubmitted) {
                    e.preventDefault();
                    return false;
                }
                
                // Validation basique
                const numeroCarte = document.getElementById('numero_carte').value.replace(/\s/g, '');
                const dateExpiration = document.getElementById('date_expiration').value;
                const cvv = document.getElementById('cvv').value;
                
                if (numeroCarte.length !== 16 || !/^\d+$/.test(numeroCarte)) {
                    alert('Veuillez entrer un numéro de carte valide (16 chiffres)');
                    e.preventDefault();
                    return false;
                }
                
                if (!/^\d{2}\/\d{2}$/.test(dateExpiration)) {
                    alert('Format de date d\'expiration invalide (MM/AA)');
                    e.preventDefault();
                    return false;
                }
                
                if (cvv.length < 3 || !/^\d+$/.test(cvv)) {
                    alert('Code de sécurité invalide');
                    e.preventDefault();
                    return false;
                }
                
                formSubmitted = true;
                
                // Afficher l'animation de traitement
                document.getElementById('loading').style.display = 'block';
                document.getElementById('submitBtn').disabled = true;
                document.getElementById('submitBtn').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Traitement en cours...';
                
                // Le formulaire sera soumis normalement et redirigera vers la confirmation
                return true;
            });
        });
    </script>
</body>
</html>