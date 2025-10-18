<?php
session_start();
require_once 'includes/db.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['id_user'])) {
    header('Location: vge64/connexion.php');
    exit;
}

// Récupérer l'ID de réservation
$reservation_id = intval($_GET['reservation_id'] ?? 0);

if (!$reservation_id) {
    header('Location: index.php');
    exit;
}

try {
    // Récupérer les détails de la réservation
    $sql = "SELECT r.*, v.*, c.nom_compagnie, c.code_compagnie, a.modele as avion_modele
            FROM reservations r
            JOIN vols v ON r.id_vol = v.id_vol
            JOIN compagnies c ON v.id_compagnie = c.id_compagnie
            JOIN avions a ON v.id_avion = a.id_avion
            WHERE r.id_reservation = ? AND r.id_user = ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$reservation_id, $_SESSION['id_user']]);
    $reservation = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$reservation) {
        header('Location: index.php?error=reservation_non_trouvee');
        exit;
    }
    
} catch (PDOException $e) {
    error_log("Erreur récupération réservation: " . $e->getMessage());
    header('Location: index.php?error=erreur_base_donnees');
    exit;
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
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #f8fafc;
            color: #334155;
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        header {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.8rem;
            font-weight: 700;
            text-decoration: none;
            color: #1e293b;
        }

        .logo span {
            color: #2563eb;
        }

        .btn {
            display: inline-block;
            padding: 0.5rem 1.5rem;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .btn-primary {
            background: #2563eb;
            color: white;
        }

        .btn-primary:hover {
            background: #1d4ed8;
        }

        .btn-outline {
            border-color: #2563eb;
            color: #2563eb;
            background: transparent;
            margin-right: 0.5rem;
        }

        .btn-outline:hover {
            background: #2563eb;
            color: white;
        }

        .confirmation-container {
            max-width: 800px;
            margin: 30px auto;
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }

        .success-icon {
            text-align: center;
            margin-bottom: 30px;
        }

        .success-icon i {
            font-size: 4rem;
            color: #10b981;
            margin-bottom: 15px;
        }

        .success-title {
            font-size: 2rem;
            font-weight: 700;
            color: #10b981;
            text-align: center;
            margin-bottom: 10px;
        }

        .success-subtitle {
            text-align: center;
            color: #64748b;
            margin-bottom: 30px;
        }

        .reservation-details {
            background: #f8fafc;
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 30px;
        }

        .detail-section {
            margin-bottom: 20px;
        }

        .detail-section:last-child {
            margin-bottom: 0;
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 15px;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 5px;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .detail-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e2e8f0;
        }

        .detail-item:last-child {
            border-bottom: none;
        }

        .detail-label {
            font-weight: 500;
            color: #64748b;
        }

        .detail-value {
            font-weight: 600;
            color: #1e293b;
        }

        .flight-info {
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            gap: 20px;
            align-items: center;
            margin: 20px 0;
            padding: 20px;
            background: white;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }

        .flight-time {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
        }

        .flight-city {
            color: #64748b;
            font-size: 0.9rem;
        }

        .flight-date {
            color: #94a3b8;
            font-size: 0.8rem;
            margin-top: 5px;
        }

        .flight-duration {
            text-align: center;
            color: #64748b;
        }

        .flight-duration i {
            color: #2563eb;
            font-size: 1.5rem;
        }

        .price-summary {
            background: #f0f9ff;
            border-radius: 8px;
            padding: 20px;
            border-left: 4px solid #2563eb;
        }

        .price-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .price-total {
            display: flex;
            justify-content: space-between;
            font-weight: bold;
            font-size: 1.25rem;
            color: #1e293b;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 2px solid #e2e8f0;
        }

        .actions {
            text-align: center;
            margin-top: 30px;
        }

        .actions .btn {
            margin: 0 10px;
        }

        @media (max-width: 768px) {
            .detail-grid {
                grid-template-columns: 1fr;
            }
            
            .flight-info {
                grid-template-columns: 1fr;
                text-align: center;
            }
            
            .actions .btn {
                display: block;
                margin: 10px 0;
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="header-top">
                <a href="index.php" class="logo">Jet<span>Reserve</span></a>
                <div class="auth-buttons">
                    <span style="margin-right: 15px; color: #64748b;">Bonjour, <?php echo htmlspecialchars($_SESSION['prenom'] ?? 'Utilisateur'); ?></span>
                    <a href="vge64/index2.php" class="btn btn-outline">Mon compte</a>
                    <a href="vge64/logout.php" class="btn btn-primary">Déconnexion</a>
                </div>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="confirmation-container">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
                <h1 class="success-title">Réservation confirmée !</h1>
                <p class="success-subtitle">Votre vol a été réservé avec succès</p>
            </div>

            <div class="reservation-details">
                <div class="detail-section">
                    <h3 class="section-title">Détails de la réservation</h3>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <span class="detail-label">Numéro de réservation</span>
                            <span class="detail-value">#<?php echo str_pad($reservation['id_reservation'], 6, '0', STR_PAD_LEFT); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Date de réservation</span>
                            <span class="detail-value"><?php echo date('d/m/Y à H:i', strtotime($reservation['date_reservation'])); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Statut</span>
                            <span class="detail-value" style="color: #10b981;"><?php echo ucfirst($reservation['statut']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Nombre de passagers</span>
                            <span class="detail-value"><?php echo $reservation['nombre_passagers']; ?></span>
                        </div>
                    </div>
                </div>

                <div class="detail-section">
                    <h3 class="section-title">Informations du vol</h3>
                    <div class="flight-info">
                        <div>
                            <div class="flight-time"><?php echo date('H:i', strtotime($reservation['date_depart'])); ?></div>
                            <div class="flight-city"><?php echo htmlspecialchars($reservation['depart']); ?></div>
                            <div class="flight-date"><?php echo date('d/m/Y', strtotime($reservation['date_depart'])); ?></div>
                        </div>
                        <div class="flight-duration">
                            <i class="fas fa-plane"></i>
                            <div style="margin-top: 10px;">
                                <?php 
                                $departure = new DateTime($reservation['date_depart']);
                                $arrival = new DateTime($reservation['date_arrivee']);
                                $duration = $departure->diff($arrival);
                                echo $duration->format('%hh%im');
                                ?>
                            </div>
                            <div style="font-size: 0.8rem; margin-top: 5px;">
                                <?php echo $reservation['escales'] == 0 ? 'Direct' : $reservation['escales'] . ' escale(s)'; ?>
                            </div>
                        </div>
                        <div>
                            <div class="flight-time"><?php echo date('H:i', strtotime($reservation['date_arrivee'])); ?></div>
                            <div class="flight-city"><?php echo htmlspecialchars($reservation['arrivee']); ?></div>
                            <div class="flight-date"><?php echo date('d/m/Y', strtotime($reservation['date_arrivee'])); ?></div>
                        </div>
                    </div>
                    
                    <div class="detail-grid">
                        <div class="detail-item">
                            <span class="detail-label">Compagnie</span>
                            <span class="detail-value"><?php echo htmlspecialchars($reservation['nom_compagnie']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Numéro de vol</span>
                            <span class="detail-value"><?php echo htmlspecialchars($reservation['code_compagnie'] . $reservation['numero_vol']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Classe</span>
                            <span class="detail-value"><?php echo htmlspecialchars($reservation['classe']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Avion</span>
                            <span class="detail-value"><?php echo htmlspecialchars($reservation['avion_modele']); ?></span>
                        </div>
                    </div>
                </div>

                <div class="detail-section">
                    <h3 class="section-title">Récapitulatif des prix</h3>
                    <div class="price-summary">
                        <div class="price-item">
                            <span>Vol (<?php echo $reservation['nombre_passagers']; ?> passager(s))</span>
                            <span><?php echo number_format($reservation['prix'] * $reservation['nombre_passagers'], 2, ',', ' '); ?>€</span>
                        </div>
                        <div class="price-item">
                            <span>Frais de service</span>
                            <span>9,00€</span>
                        </div>
                        <div class="price-item">
                            <span>Taxes aéroport</span>
                            <span>25,00€</span>
                        </div>
                        <div class="price-total">
                            <span>Total payé</span>
                            <span><?php echo number_format($reservation['prix_total'], 2, ',', ' '); ?>€</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="actions">
                <a href="vge64/index2.php" class="btn btn-outline">
                    <i class="fas fa-user"></i> Mon compte
                </a>
                <a href="index.php" class="btn btn-primary">
                    <i class="fas fa-search"></i> Nouvelle recherche
                </a>
            </div>
        </div>
    </div>
</body>
</html>