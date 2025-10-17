<?php
require_once 'includes/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: connexion.php');
    exit;
}

$reservation_id = isset($_GET['reservation_id']) ? intval($_GET['reservation_id']) : 0;

if (!$reservation_id) {
    header('Location: vols.php');
    exit;
}

// Récupérer les détails de la réservation
$stmt = $pdo->prepare("SELECT r.*, v.*, c.nom_compagnie, c.code_compagnie, u.nom, u.prenom, u.email
                      FROM reservations r
                      JOIN vols v ON r.id_vol = v.id_vol
                      JOIN compagnies c ON v.id_compagnie = c.id_compagnie
                      JOIN users u ON r.id_user = u.id_user
                      WHERE r.id_reservation = ? AND r.id_user = ?");
$stmt->execute([$reservation_id, $_SESSION['user_id']]);
$reservation = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$reservation) {
    header('Location: vols.php');
    exit;
}

// Récupérer les sièges réservés
$stmt = $pdo->prepare("SELECT rs.*, s.rang, s.position 
                      FROM reservation_sieges rs
                      JOIN sieges_avion s ON rs.id_siege = s.id_siege
                      WHERE rs.id_reservation = ?");
$stmt->execute([$reservation_id]);
$sieges = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
        .container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 20px;
        }
        
        .confirmation-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            text-align: center;
        }
        
        .success-icon {
            font-size: 4rem;
            color: #22c55e;
            margin-bottom: 1rem;
        }
        
        .btn-primary {
            background: #2563eb;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin: 10px;
        }
        
        .reservation-details {
            background: #f8fafc;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            text-align: left;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .seat-badge {
            background: #2563eb;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            margin-right: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="confirmation-card">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            
            <h1>Réservation Confirmée !</h1>
            <p>Votre réservation a été traitée avec succès.</p>
            
            <div class="reservation-details">
                <h3>Détails de la réservation</h3>
                <div class="detail-row">
                    <span>Numéro de réservation:</span>
                    <strong>#<?php echo $reservation['id_reservation']; ?></strong>
                </div>
                <div class="detail-row">
                    <span>Vol:</span>
                    <span><?php echo htmlspecialchars($reservation['depart']); ?> → <?php echo htmlspecialchars($reservation['arrivee']); ?></span>
                </div>
                <div class="detail-row">
                    <span>Compagnie:</span>
                    <span><?php echo htmlspecialchars($reservation['nom_compagnie']); ?></span>
                </div>
                <div class="detail-row">
                    <span>Date de départ:</span>
                    <span><?php echo date('d M Y, H:i', strtotime($reservation['date_depart'])); ?></span>
                </div>
                <div class="detail-row">
                    <span>Passagers:</span>
                    <span><?php echo $reservation['nombre_passagers']; ?> personne(s)</span>
                </div>
                <div class="detail-row">
                    <span>Sièges:</span>
                    <span>
                        <?php foreach ($sieges as $siege): ?>
                            <span class="seat-badge"><?php echo $siege['rang'] . $siege['position']; ?></span>
                        <?php endforeach; ?>
                    </span>
                </div>
                <div class="detail-row">
                    <span>Montant total:</span>
                    <strong><?php echo $reservation['prix_total']; ?>€</strong>
                </div>
            </div>
            
            <p>Un email de confirmation a été envoyé à <strong><?php echo htmlspecialchars($reservation['email']); ?></strong></p>
            
            <div>
                <a href="index.php" class="btn-primary">
                    <i class="fas fa-home"></i> Retour à l'accueil
                </a>
                <a href="mes_reservations.php" class="btn-primary">
                    <i class="fas fa-list"></i> Mes réservations
                </a>
            </div>
        </div>
    </div>
</body>
</html>