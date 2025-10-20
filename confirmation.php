<?php
session_start();
require_once 'includes/db.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['id_user'])) {
    header('Location: vge64/connexion.php');
    exit;
}

// Vérifier les paramètres
if (!isset($_GET['id_reservation'])) {
    header('Location: index.php');
    exit;
}

$reservation_id = intval($_GET['id_reservation']);
$reference = $_GET['reference'] ?? '';

// Récupérer les détails de la réservation confirmée
try {
    $sql = "SELECT r.*, v.depart, v.arrivee, v.date_depart, v.date_arrivee, 
                   c.nom_compagnie, c.code_compagnie, a.modele as avion_modele,
                   p.reference_paiement, p.date_paiement, p.mode_paiement
            FROM reservations r
            JOIN vols v ON r.id_vol = v.id_vol
            JOIN compagnies c ON v.id_compagnie = c.id_compagnie
            JOIN avions a ON v.id_avion = a.id_avion
            LEFT JOIN paiements p ON r.id_reservation = p.id_reservation
            WHERE r.id_reservation = ? AND r.id_user = ? AND r.statut = 'confirmé'";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$reservation_id, $_SESSION['id_user']]);
    $reservation = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$reservation) {
        header('Location: index.php?error=reservation_non_trouvee');
        exit;
    }
} catch (PDOException $e) {
    header('Location: index.php?error=erreur_systeme');
    exit;
}

// Récupérer les sièges
try {
    $sql_sieges = "SELECT sa.rang, sa.position, sa.supplement_prix
                   FROM reservation_sieges rs 
                   JOIN sieges_avion sa ON rs.id_siege = sa.id_siege 
                   WHERE rs.id_reservation = ?";
    $stmt_sieges = $pdo->prepare($sql_sieges);
    $stmt_sieges->execute([$reservation_id]);
    $sieges = $stmt_sieges->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $sieges = [];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation - JetReserve</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #0054A4;
            --secondary: #6c757d;
            --success: #28a745;
            --danger: #dc3545;
            --light: #f8f9fa;
            --white: #ffffff;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .confirmation-container {
            max-width: 800px;
            width: 100%;
        }
        
        .confirmation-card {
            background: white;
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .success-icon {
            font-size: 5rem;
            color: var(--success);
            margin-bottom: 1.5rem;
        }
        
        h1 {
            color: var(--primary);
            margin-bottom: 1rem;
            font-size: 2.5rem;
        }
        
        .confirmation-message {
            font-size: 1.2rem;
            color: var(--secondary);
            margin-bottom: 2rem;
        }
        
        .confirmation-details {
            background: var(--light);
            padding: 2rem;
            border-radius: 15px;
            margin: 2rem 0;
            text-align: left;
        }
        
        .detail-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #dee2e6;
        }
        
        .detail-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .detail-label {
            font-weight: 600;
            color: var(--primary);
        }
        
        .detail-value {
            text-align: right;
        }
        
        .seats-list {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }
        
        .seat-tag {
            background: var(--primary);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        .btn-group {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 1rem 2rem;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-primary {
            background: var(--primary);
            color: white;
        }
        
        .btn-outline {
            border: 2px solid var(--primary);
            color: var(--primary);
            background: white;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .email-notice {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            color: var(--secondary);
            margin-top: 1rem;
        }
        
        @media (max-width: 768px) {
            .confirmation-card {
                padding: 2rem;
            }
            
            .btn-group {
                flex-direction: column;
            }
            
            .btn {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="confirmation-container">
        <div class="confirmation-card">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            
            <h1>Paiement Confirmé !</h1>
            <p class="confirmation-message">
                Votre réservation a été confirmée et votre paiement a été accepté.
            </p>
            
            <div class="confirmation-details">
                <h3 style="color: var(--primary); margin-bottom: 1.5rem; text-align: center;">
                    <i class="fas fa-receipt"></i> Détails de votre réservation
                </h3>
                
                <div class="detail-item">
                    <span class="detail-label">Numéro de réservation :</span>
                    <span class="detail-value">#<?php echo $reservation['id_reservation']; ?></span>
                </div>
                
                <?php if (!empty($reservation['reference_paiement'])): ?>
                <div class="detail-item">
                    <span class="detail-label">Référence paiement :</span>
                    <span class="detail-value"><?php echo htmlspecialchars($reservation['reference_paiement']); ?></span>
                </div>
                <?php endif; ?>
                
                <div class="detail-item">
                    <span class="detail-label">Vol :</span>
                    <span class="detail-value"><?php echo htmlspecialchars($reservation['depart']); ?> → <?php echo htmlspecialchars($reservation['arrivee']); ?></span>
                </div>
                
                <div class="detail-item">
                    <span class="detail-label">Date et heure :</span>
                    <span class="detail-value"><?php echo date('d/m/Y H:i', strtotime($reservation['date_depart'])); ?></span>
                </div>
                
                <div class="detail-item">
                    <span class="detail-label">Compagnie :</span>
                    <span class="detail-value"><?php echo htmlspecialchars($reservation['nom_compagnie']); ?></span>
                </div>
                
                <div class="detail-item">
                    <span class="detail-label">Passagers :</span>
                    <span class="detail-value"><?php echo $reservation['nombre_passagers']; ?> personne(s)</span>
                </div>
                
                <?php if (!empty($sieges)): ?>
                <div class="detail-item">
                    <span class="detail-label">Sièges :</span>
                    <div class="detail-value">
                        <div class="seats-list">
                            <?php foreach ($sieges as $siege): ?>
                                <div class="seat-tag">
                                    <?php echo $siege['position'] . $siege['rang']; ?>
                                    <?php if ($siege['supplement_prix'] > 0): ?>
                                        <small style="font-size: 0.7rem;">(+<?php echo $siege['supplement_prix']; ?>€)</small>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="detail-item">
                    <span class="detail-label">Montant payé :</span>
                    <span class="detail-value" style="font-weight: 700; color: var(--primary);">
                        <?php echo number_format($reservation['prix_total'], 2, ',', ' '); ?>€
                    </span>
                </div>
                
                <?php if (!empty($reservation['date_paiement'])): ?>
                <div class="detail-item">
                    <span class="detail-label">Date du paiement :</span>
                    <span class="detail-value"><?php echo date('d/m/Y H:i', strtotime($reservation['date_paiement'])); ?></span>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($reservation['mode_paiement'])): ?>
                <div class="detail-item">
                    <span class="detail-label">Moyen de paiement :</span>
                    <span class="detail-value">
                        <?php 
                        switch($reservation['mode_paiement']) {
                            case 'carte': echo 'Carte bancaire'; break;
                            case 'paypal': echo 'PayPal'; break;
                            case 'applepay': echo 'Apple Pay'; break;
                            default: echo ucfirst($reservation['mode_paiement']);
                        }
                        ?>
                    </span>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="email-notice">
                <i class="fas fa-envelope"></i>
                <span>Un email de confirmation a été envoyé à votre adresse email.</span>
            </div>
            
            <div class="btn-group">
                <a href="index.php" class="btn btn-primary">
                    <i class="fas fa-home"></i> Retour à l'accueil
                </a>
                <a href="vge64/index2.php" class="btn btn-outline">
                    <i class="fas fa-list"></i> Mes réservations
                </a>
            </div>
        </div>
    </div>
</body>
</html>