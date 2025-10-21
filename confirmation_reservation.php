<?php
require_once 'includes/db.php';
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['id_user'])) {
    header('Location: vge64/connexion.php');
    exit;
}

// Récupérer l'ID de la réservation
$id_reservation = isset($_GET['id_reservation']) ? intval($_GET['id_reservation']) : 0;

if ($id_reservation <= 0) {
    header('Location: index.php');
    exit;
}

// Récupérer les détails de la réservation
try {
    $stmt = $pdo->prepare("
        SELECT r.*, v.*, c.nom_compagnie, c.code_compagnie, a.modele as avion_modele,
               u.nom, u.prenom, u.email
        FROM reservations r
        JOIN vols v ON r.id_vol = v.id_vol
        JOIN compagnies c ON v.id_compagnie = c.id_compagnie
        JOIN avions a ON v.id_avion = a.id_avion
        JOIN users u ON r.id_user = u.id_user
        WHERE r.id_reservation = ? AND r.id_user = ?
    ");
    $stmt->execute([$id_reservation, $_SESSION['id_user']]);
    $reservation = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$reservation) {
        header('Location: index.php');
        exit;
    }
    
    // Récupérer les sièges réservés
    $stmt_sieges = $pdo->prepare("
        SELECT sa.position, sa.rang, sa.classe, sa.supplement_prix, rs.prix_paye
        FROM reservation_sieges rs
        JOIN sieges_avion sa ON rs.id_siege = sa.id_siege
        WHERE rs.id_reservation = ?
    ");
    $stmt_sieges->execute([$id_reservation]);
    $sieges = $stmt_sieges->fetchAll(PDO::FETCH_ASSOC);
    
    // Récupérer les bagages
    $stmt_bagages = $pdo->prepare("
        SELECT ob.nom_option, ob.poids_max, rb.quantite, rb.prix_applique
        FROM reservation_bagages rb
        JOIN options_bagage ob ON rb.id_option = ob.id_option
        WHERE rb.id_reservation = ?
    ");
    $stmt_bagages->execute([$id_reservation]);
    $bagages = $stmt_bagages->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    die("Erreur lors de la récupération des données: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation de réservation - JetReserve</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #005baa;
            --secondary: #ff6600;
            --success: #28a745;
            --danger: #dc3545;
            --warning: #ffc107;
            --info: #17a2b8;
            --light: #f8f9fa;
            --dark: #343a40;
            --white: #ffffff;
            --gray: #6c757d;
            --light-gray: #e9ecef;
            --border-color: #dee2e6;
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
            padding: 20px 0;
        }
        
        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .logo {
            font-size: 48px;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }
        
        .logo i {
            color: var(--secondary);
            font-size: 52px;
        }
        
        .logo span {
            color: var(--secondary);
        }
        
        .success-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, var(--success), #34ce57);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            box-shadow: 0 10px 30px rgba(40, 167, 69, 0.3);
        }
        
        .success-icon i {
            font-size: 50px;
            color: white;
        }
        
        .confirmation-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--success);
            margin-bottom: 10px;
        }
        
        .confirmation-subtitle {
            font-size: 1.2rem;
            color: var(--gray);
            margin-bottom: 40px;
        }
        
        .confirmation-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
            margin-bottom: 40px;
        }
        
        .confirmation-card {
            background: var(--white);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border: 1px solid var(--light-gray);
        }
        
        .card-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--light-gray);
        }
        
        .flight-info {
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            gap: 20px;
            align-items: center;
            margin-bottom: 30px;
            padding: 20px;
            background: linear-gradient(135deg, var(--light), var(--light-gray));
            border-radius: 15px;
        }
        
        .airport {
            text-align: center;
        }
        
        .airport-code {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 5px;
        }
        
        .airport-name {
            color: var(--gray);
            font-size: 0.9rem;
        }
        
        .flight-details {
            text-align: center;
        }
        
        .flight-number {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 10px;
        }
        
        .flight-date {
            color: var(--gray);
            font-size: 0.9rem;
        }
        
        .flight-plane {
            font-size: 2rem;
            color: var(--secondary);
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .info-item {
            background: var(--light);
            padding: 15px;
            border-radius: 10px;
            border-left: 4px solid var(--primary);
        }
        
        .info-label {
            font-weight: 600;
            color: var(--gray);
            font-size: 0.9rem;
            margin-bottom: 5px;
        }
        
        .info-value {
            font-size: 1.1rem;
            color: var(--dark);
            font-weight: 600;
        }
        
        .reservation-number {
            background: linear-gradient(135deg, var(--primary), #0077cc);
            color: white;
            padding: 20px;
            border-radius: 15px;
            text-align: center;
            margin-bottom: 30px;
        }
        
        .reservation-number h3 {
            font-size: 1.3rem;
            margin-bottom: 10px;
        }
        
        .reservation-number .number {
            font-size: 2rem;
            font-weight: 700;
            letter-spacing: 2px;
        }
        
        .seats-list {
            margin-bottom: 20px;
        }
        
        .seat-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 15px;
            background: var(--light);
            border-radius: 8px;
            margin-bottom: 8px;
        }
        
        .seat-position {
            font-weight: 600;
            color: var(--dark);
        }
        
        .seat-class {
            background: var(--primary);
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
        }
        
        .price-summary {
            background: var(--light);
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 20px;
        }
        
        .price-line {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .price-line:last-child {
            border-bottom: none;
            font-weight: 700;
            font-size: 1.2rem;
            color: var(--primary);
            margin-top: 10px;
            padding-top: 10px;
            border-top: 2px solid var(--primary);
        }
        
        .actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 40px;
        }
        
        .btn {
            padding: 15px 30px;
            border-radius: 25px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
            font-size: 1rem;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary), #0077cc);
            color: var(--white);
            box-shadow: 0 4px 15px rgba(0, 91, 170, 0.3);
        }
        
        .btn-success {
            background: linear-gradient(135deg, var(--success), #34ce57);
            color: var(--white);
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }
        
        @media (max-width: 768px) {
            .confirmation-grid {
                grid-template-columns: 1fr;
            }
            
            .flight-info {
                grid-template-columns: 1fr;
                text-align: center;
            }
            
            .actions {
                flex-direction: column;
            }
            
            .confirmation-title {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">
                <i class="fas fa-plane"></i>
                Jet<span>Reserve</span>
            </div>
            
            <div class="success-icon">
                <i class="fas fa-check"></i>
            </div>
            
            <h1 class="confirmation-title">Réservation confirmée !</h1>
            <p class="confirmation-subtitle">Votre vol a été réservé avec succès</p>
        </div>
        
        <div class="confirmation-grid">
            <div class="confirmation-card">
                <h2 class="card-title">
                    <i class="fas fa-receipt"></i> Détails de votre réservation
                </h2>
                
                <div class="reservation-number">
                    <h3>Numéro de réservation</h3>
                    <div class="number"><?php echo str_pad($id_reservation, 6, '0', STR_PAD_LEFT); ?></div>
                </div>
                
                <div class="flight-info">
                    <div class="airport">
                        <div class="airport-code"><?php echo substr($reservation['depart'], 0, 3); ?></div>
                        <div class="airport-name"><?php echo htmlspecialchars($reservation['depart']); ?></div>
                    </div>
                    <div class="flight-details">
                        <div class="flight-plane">✈</div>
                        <div class="flight-number"><?php echo htmlspecialchars($reservation['numero_vol']); ?></div>
                        <div class="flight-date"><?php echo date('d/m/Y H:i', strtotime($reservation['date_depart'])); ?></div>
                    </div>
                    <div class="airport">
                        <div class="airport-code"><?php echo substr($reservation['arrivee'], 0, 3); ?></div>
                        <div class="airport-name"><?php echo htmlspecialchars($reservation['arrivee']); ?></div>
                    </div>
                </div>
                
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Compagnie</div>
                        <div class="info-value"><?php echo htmlspecialchars($reservation['nom_compagnie']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Passagers</div>
                        <div class="info-value"><?php echo $reservation['nombre_passagers']; ?> personne(s)</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Avion</div>
                        <div class="info-value"><?php echo htmlspecialchars($reservation['avion_modele']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Classe</div>
                        <div class="info-value"><?php echo htmlspecialchars($reservation['classe']); ?></div>
                    </div>
                </div>
                
                <?php if (!empty($sieges)): ?>
                <h3 style="margin-bottom: 15px; color: var(--primary);">
                    <i class="fas fa-chair"></i> Sièges réservés
                </h3>
                <div class="seats-list">
                    <?php foreach ($sieges as $siege): ?>
                    <div class="seat-item">
                        <span class="seat-position">Siège <?php echo $siege['position'] . $siege['rang']; ?></span>
                        <span class="seat-class"><?php echo htmlspecialchars($siege['classe']); ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="confirmation-card">
                <h2 class="card-title">
                    <i class="fas fa-user"></i> Informations passager
                </h2>
                
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Nom</div>
                        <div class="info-value"><?php echo htmlspecialchars($reservation['nom']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Prénom</div>
                        <div class="info-value"><?php echo htmlspecialchars($reservation['prenom']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Email</div>
                        <div class="info-value"><?php echo htmlspecialchars($reservation['email']); ?></div>
                    </div>
                </div>
                
                <h3 style="margin: 25px 0 15px; color: var(--primary);">
                    <i class="fas fa-euro-sign"></i> Récapitulatif des prix
                </h3>
                <div class="price-summary">
                    <div class="price-line">
                        <span>Billets de base:</span>
                        <span><?php echo number_format($reservation['prix'] * $reservation['nombre_passagers'], 2, ',', ' '); ?>€</span>
                    </div>
                    <?php if (!empty($sieges)): ?>
                    <div class="price-line">
                        <span>Suppléments sièges:</span>
                        <span><?php echo number_format(array_sum(array_column($sieges, 'prix_paye')) - ($reservation['prix'] * $reservation['nombre_passagers']), 2, ',', ' '); ?>€</span>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($bagages)): ?>
                    <div class="price-line">
                        <span>Suppléments bagages:</span>
                        <span><?php echo number_format(array_sum(array_column($bagages, 'prix_applique')), 2, ',', ' '); ?>€</span>
                    </div>
                    <?php endif; ?>
                    <div class="price-line">
                        <span>Frais de service:</span>
                        <span>9,00€</span>
                    </div>
                    <div class="price-line">
                        <span>Taxes aéroport:</span>
                        <span>25,00€</span>
                    </div>
                    <div class="price-line">
                        <span>Total payé:</span>
                        <span><?php echo number_format($reservation['prix_total'], 2, ',', ' '); ?>€</span>
                    </div>
                </div>
                
                <div style="background: var(--light); padding: 15px; border-radius: 10px; margin-top: 20px;">
                    <h4 style="color: var(--success); margin-bottom: 10px;">
                        <i class="fas fa-check-circle"></i> Paiement confirmé
                    </h4>
                    <p style="color: var(--gray); font-size: 0.9rem;">
                        Votre paiement a été traité avec succès. Vous recevrez un email de confirmation sous peu.
                    </p>
                </div>
            </div>
        </div>
        
        <div class="actions">
            <a href="vge64/index2.php" class="btn btn-primary">
                <i class="fas fa-home"></i> Retour au tableau de bord
            </a>
            <a href="vols.php" class="btn btn-success">
                <i class="fas fa-search"></i> Réserver un autre vol
            </a>
        </div>
    </div>
</body>
</html>
