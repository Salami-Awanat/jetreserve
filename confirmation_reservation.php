<?php
session_start();
require_once 'includes/db.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['id_user'])) {
    header('Location: vge64/connexion.php');
    exit;
}

// Vérifier l'ID de réservation
if (!isset($_GET['id_reservation'])) {
    header('Location: index.php');
    exit;
    
}

$id_reservation = intval($_GET['id_reservation']);

// Récupérer les détails complets de la réservation
try {
    // Détails de base de la réservation
    $sql = "
        SELECT r.*, v.depart, v.arrivee, v.date_depart, v.date_arrivee, v.duree_vol,
               c.nom_compagnie, c.code_compagnie, v.numero_vol, v.porte_embarquement,
               a.modele as avion_modele, a.configuration_sieges,
               p.date_paiement, p.mode_paiement,
               u.prenom, u.nom, u.email, u.telephone
        FROM reservations r
        JOIN vols v ON r.id_vol = v.id_vol
        JOIN compagnies c ON v.id_compagnie = c.id_compagnie
        JOIN avions a ON v.id_avion = a.id_avion
        LEFT JOIN paiements p ON r.id_reservation = p.id_reservation
        JOIN users u ON r.id_user = u.id_user
        WHERE r.id_reservation = ? AND r.id_user = ?
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_reservation, $_SESSION['id_user']]);
    $reservation = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$reservation) {
        throw new Exception("Réservation non trouvée");
    }

    // Récupérer les sièges réservés
    $sql_sieges = "
        SELECT sa.position, sa.rang, sa.supplement_prix, sa.classe
        FROM reservation_sieges rs
        JOIN sieges_avion sa ON rs.id_siege = sa.id_siege
        WHERE rs.id_reservation = ?
        ORDER BY sa.rang, sa.position
    ";
    $stmt_sieges = $pdo->prepare($sql_sieges);
    $stmt_sieges->execute([$id_reservation]);
    $sieges = $stmt_sieges->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer les options de bagage
    $sql_bagages = "
        SELECT ob.nom_option, ob.description, ob.poids_max, rb.quantite, rb.prix_applique
        FROM reservation_bagages rb
        JOIN options_bagage ob ON rb.id_option = ob.id_option
        WHERE rb.id_reservation = ?
    ";
    $stmt_bagages = $pdo->prepare($sql_bagages);
    $stmt_bagages->execute([$id_reservation]);
    $bagages = $stmt_bagages->fetchAll(PDO::FETCH_ASSOC);

    // Calculer le détail du prix
    $prix_base = $reservation['prix_total'] - 34; // Retirer frais fixes
    $supplements_sieges = 0;
    foreach ($sieges as $siege) {
        $supplements_sieges += $siege['supplement_prix'];
    }
    $supplements_bagages = 0;
    foreach ($bagages as $bagage) {
        $supplements_bagages += $bagage['prix_applique'] * $bagage['quantite'];
    }

} catch (Exception $e) {
    error_log("Erreur confirmation réservation: " . $e->getMessage());
    header('Location: index.php?error=reservation_not_found');
    exit;
}

// Générer un code QR factice (en production, utiliser un service comme Google Charts)
$qr_code_url = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . 
               urlencode("JetReserve|REF:" . $id_reservation . "|" . $reservation['depart'] . "-" . $reservation['arrivee']);

// Marquer la réservation comme vue
try {
    $sql_update = "UPDATE reservations SET date_visualisation = NOW() WHERE id_reservation = ?";
    $stmt_update = $pdo->prepare($sql_update);
    $stmt_update->execute([$id_reservation]);
} catch (Exception $e) {
    // Ne pas bloquer l'affichage en cas d'erreur mineure
    error_log("Erreur mise à jour visualisation: " . $e->getMessage());
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
            --primary: #2c3e50;
            --secondary: #7f8c8d;
            --success: #27ae60;
            --danger: #e74c3c;
            --warning: #f39c12;
            --info: #3498db;
            --light: #f8f9fa;
            --dark: #2c3e50;
            --white: #ffffff;
            --gold: #ffd700;
            --silver: #c0c0c0;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
            background-color: rgba(255, 255, 255, 0.95);
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
            backdrop-filter: blur(10px);
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
            color: var(--primary);
            text-decoration: none;
        }
        
        .logo span {
            color: var(--danger);
        }
        
        .confirmation-container {
            max-width: 1000px;
            margin: 2rem auto;
            background: var(--white);
            border-radius: 20px;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            position: relative;
        }
        
        .confirmation-header {
            background: linear-gradient(135deg, var(--success), #2ecc71);
            color: var(--white);
            padding: 3rem 2rem;
            position: relative;
            overflow: hidden;
        }
        
        .confirmation-header::before {
            content: "";
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
            transform: rotate(45deg);
        }
        
        .confirmation-icon {
            font-size: 5rem;
            margin-bottom: 1rem;
            position: relative;
            text-shadow: 0 5px 15px rgba(0,0,0,0.2);
            animation: bounce 2s infinite;
        }
        
        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        
        .confetti {
            position: absolute;
            width: 10px;
            height: 10px;
            background: var(--gold);
            border-radius: 50%;
            animation: confetti-fall 5s linear infinite;
        }
        
        @keyframes confetti-fall {
            0% { transform: translateY(-100px) rotate(0deg); opacity: 1; }
            100% { transform: translateY(1000px) rotate(360deg); opacity: 0; }
        }
        
        .confirmation-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            position: relative;
        }
        
        .confirmation-subtitle {
            font-size: 1.2rem;
            opacity: 0.9;
        }
        
        .confirmation-body {
            padding: 2rem;
        }
        
        .reservation-card {
            background: var(--white);
            border-radius: 15px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.08);
            margin-bottom: 2rem;
            overflow: hidden;
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--primary), #34495e);
            color: white;
            padding: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .card-body {
            padding: 1.5rem;
        }
        
        .detail-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .detail-section {
            background: var(--light);
            padding: 1.5rem;
            border-radius: 10px;
            border-left: 4px solid var(--primary);
        }
        
        .detail-section h4 {
            color: var(--primary);
            margin-bottom: 1rem;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .detail-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.8rem;
            padding-bottom: 0.8rem;
            border-bottom: 1px solid rgba(0,0,0,0.1);
        }
        
        .detail-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .detail-label {
            font-weight: 500;
            color: var(--secondary);
        }
        
        .detail-value {
            font-weight: 600;
            color: var(--dark);
            text-align: right;
        }
        
        .price-breakdown {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            padding: 1.5rem;
            border-radius: 10px;
            margin-top: 1rem;
        }
        
        .price-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }
        
        .price-total {
            border-top: 2px solid var(--primary);
            padding-top: 0.8rem;
            margin-top: 0.8rem;
            font-weight: 700;
            font-size: 1.3rem;
            color: var(--primary);
        }
        
        .qr-section {
            text-align: center;
            padding: 2rem;
            background: var(--light);
            border-radius: 15px;
            margin: 2rem 0;
        }
        
        .qr-code {
            width: 150px;
            height: 150px;
            margin: 0 auto 1rem;
            border: 2px solid var(--primary);
            border-radius: 10px;
            padding: 10px;
            background: white;
        }
        
        .boarding-pass {
            background: white;
            border: 2px solid var(--primary);
            border-radius: 15px;
            padding: 2rem;
            margin: 2rem 0;
            position: relative;
        }
        
        .boarding-pass::before {
            content: "";
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 90%;
            height: 1px;
            background: repeating-linear-gradient(90deg, transparent, transparent 10px, var(--primary) 10px, var(--primary) 20px);
        }
        
        .passenger-info {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 2rem;
        }
        
        .flight-info {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            text-align: center;
        }
        
        .info-box {
            padding: 1rem;
            background: var(--light);
            border-radius: 8px;
        }
        
        .info-label {
            font-size: 0.8rem;
            color: var(--secondary);
            margin-bottom: 0.5rem;
        }
        
        .info-value {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--primary);
        }
        
        .btn {
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            font-family: 'Poppins', sans-serif;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-align: center;
            margin: 0 10px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary), #34495e);
            color: var(--white);
            box-shadow: 0 4px 15px rgba(44, 62, 80, 0.3);
        }
        
        .btn-success {
            background: linear-gradient(135deg, var(--success), #2ecc71);
            color: var(--white);
            box-shadow: 0 4px 15px rgba(39, 174, 96, 0.3);
        }
        
        .btn-outline {
            background: transparent;
            border: 2px solid var(--primary);
            color: var(--primary);
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.15);
        }
        
        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-top: 2rem;
            flex-wrap: wrap;
        }
        
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
            margin: 0.25rem;
        }
        
        .badge-primary {
            background: var(--primary);
            color: white;
        }
        
        .badge-success {
            background: var(--success);
            color: white;
        }
        
        .badge-info {
            background: var(--info);
            color: white;
        }
        
        .important-notice {
            background: linear-gradient(135deg, #fff3cd, #ffeaa7);
            border: 1px solid #f39c12;
            border-radius: 10px;
            padding: 1.5rem;
            margin: 2rem 0;
        }
        
        .notice-header {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
            color: #e67e22;
        }
        
        .notice-list {
            list-style: none;
            padding-left: 0;
        }
        
        .notice-list li {
            margin-bottom: 0.5rem;
            padding-left: 1.5rem;
            position: relative;
        }
        
        .notice-list li::before {
            content: "•";
            color: #e67e22;
            font-weight: bold;
            position: absolute;
            left: 0;
        }
        
        @media (max-width: 768px) {
            .detail-grid {
                grid-template-columns: 1fr;
            }
            
            .passenger-info {
                grid-template-columns: 1fr;
            }
            
            .flight-info {
                grid-template-columns: 1fr;
            }
            
            .action-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .btn {
                width: 100%;
                max-width: 300px;
                justify-content: center;
            }
        }
        
        .print-only {
            display: none;
        }
        
        @media print {
            .no-print {
                display: none;
            }
            
            .print-only {
                display: block;
            }
            
            body {
                background: white;
            }
            
            .confirmation-container {
                box-shadow: none;
                margin: 0;
            }
        }
    </style>
</head>
<body>
    <header class="no-print">
        <div class="container">
            <div class="header-top">
                <a href="index.php" class="logo">Jet<span>Reserve</span></a>
                <div class="auth-buttons">
                    <a href="vge64/index2.php" class="btn btn-outline">Mon compte</a>
                    <a href="vge64/deconnexion.php" class="btn btn-primary">Déconnexion</a>
                </div>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="confirmation-container">
            <!-- En-tête de confirmation -->
            <div class="confirmation-header">
                <div class="confirmation-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h1 class="confirmation-title">Réservation Confirmée !</h1>
                <p class="confirmation-subtitle">Votre voyage est réservé avec succès</p>
                
                <!-- Confettis animés -->
                <div id="confetti-container"></div>
            </div>
            
            <div class="confirmation-body">
                <!-- Message de succès -->
                <div style="text-align: center; margin-bottom: 2rem;">
                    <h2 style="color: var(--success); margin-bottom: 1rem;">
                        <i class="fas fa-check-circle"></i> Félicitations <?php echo htmlspecialchars($reservation['prenom']); ?> !
                    </h2>
                    <p style="font-size: 1.1rem; color: var(--secondary);">
                        Votre réservation a été confirmée. Un email de confirmation a été envoyé à 
                        <strong><?php echo htmlspecialchars($reservation['email']); ?></strong>
                    </p>
                </div>

                <!-- Carte de réservation principale -->
                <div class="reservation-card">
                    <div class="card-header">
                        <div>
                            <h3 style="margin: 0; font-size: 1.3rem;">
                                <i class="fas fa-plane"></i> Détails du Vol
                            </h3>
                            <p style="margin: 0.5rem 0 0; opacity: 0.9;">
                                Référence: <strong>JR<?php echo str_pad($id_reservation, 6, '0', STR_PAD_LEFT); ?></strong>
                            </p>
                        </div>
                        <div style="text-align: right;">
                            <div class="badge badge-success">
                                <i class="fas fa-check"></i> Confirmé
                            </div>
                            <p style="margin: 0.5rem 0 0; font-size: 0.9rem;">
                                <?php echo date('d/m/Y à H:i', strtotime($reservation['date_reservation'])); ?>
                            </p>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <div class="detail-grid">
                            <!-- Informations du vol -->
                            <div class="detail-section">
                                <h4><i class="fas fa-route"></i> Itinéraire</h4>
                                <div class="detail-item">
                                    <span class="detail-label">Vol</span>
                                    <span class="detail-value">
                                        <?php echo htmlspecialchars($reservation['code_compagnie'] . $reservation['numero_vol']); ?>
                                    </span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Trajet</span>
                                    <span class="detail-value">
                                        <?php echo htmlspecialchars($reservation['depart']); ?> → 
                                        <?php echo htmlspecialchars($reservation['arrivee']); ?>
                                    </span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Date et heure</span>
                                    <span class="detail-value">
                                        <?php echo date('d/m/Y à H:i', strtotime($reservation['date_depart'])); ?>
                                    </span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Durée</span>
                                    <span class="detail-value">
                                        <?php echo $reservation['duree_vol'] ?? '2h 30min'; ?>
                                    </span>
                                </div>
                            </div>

                            <!-- Informations compagnie et avion -->
                            <div class="detail-section">
                                <h4><i class="fas fa-info-circle"></i> Informations</h4>
                                <div class="detail-item">
                                    <span class="detail-label">Compagnie</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($reservation['nom_compagnie']); ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Appareil</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($reservation['avion_modele']); ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Porte d'embarquement</span>
                                    <span class="detail-value"><?php echo $reservation['porte_embarquement'] ?? 'À déterminer'; ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Classe</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($reservation['classe']); ?></span>
                                </div>
                            </div>

                            <!-- Détails des passagers -->
                            <div class="detail-section">
                                <h4><i class="fas fa-users"></i> Passagers</h4>
                                <div class="detail-item">
                                    <span class="detail-label">Nombre</span>
                                    <span class="detail-value"><?php echo $reservation['nombre_passagers']; ?> personne(s)</span>
                                </div>
                                <?php if (!empty($sieges)): ?>
                                <div class="detail-item">
                                    <span class="detail-label">Sièges</span>
                                    <span class="detail-value">
                                        <?php 
                                        $sieges_list = [];
                                        foreach ($sieges as $siege) {
                                            $sieges_list[] = $siege['position'] . $siege['rang'];
                                        }
                                        echo htmlspecialchars(implode(', ', $sieges_list));
                                        ?>
                                    </span>
                                </div>
                                <?php endif; ?>
                                <div class="detail-item">
                                    <span class="detail-label">Réservé par</span>
                                    <span class="detail-value">
                                        <?php echo htmlspecialchars($reservation['prenom'] . ' ' . $reservation['nom']); ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Options supplémentaires -->
                        <?php if (!empty($bagages)): ?>
                        <div class="detail-section">
                            <h4><i class="fas fa-suitcase"></i> Options sélectionnées</h4>
                            <?php foreach ($bagages as $bagage): ?>
                            <div class="detail-item">
                                <span class="detail-label">
                                    <?php echo htmlspecialchars($bagage['nom_option']); ?>
                                    <?php if ($bagage['quantite'] > 1): ?>
                                        (x<?php echo $bagage['quantite']; ?>)
                                    <?php endif; ?>
                                </span>
                                <span class="detail-value">
                                    +<?php echo number_format($bagage['prix_applique'] * $bagage['quantite'], 2, ',', ' '); ?>€
                                </span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>

                        <!-- Détail du prix -->
                        <div class="price-breakdown">
                            <h4 style="color: var(--primary); margin-bottom: 1rem;">
                                <i class="fas fa-receipt"></i> Détail du prix
                            </h4>
                            <div class="price-item">
                                <span>Prix de base (<?php echo $reservation['nombre_passagers']; ?> passagers)</span>
                                <span><?php echo number_format($prix_base, 2, ',', ' '); ?>€</span>
                            </div>
                            <?php if ($supplements_sieges > 0): ?>
                            <div class="price-item">
                                <span>Suppléments sièges</span>
                                <span><?php echo number_format($supplements_sieges, 2, ',', ' '); ?>€</span>
                            </div>
                            <?php endif; ?>
                            <?php if ($supplements_bagages > 0): ?>
                            <div class="price-item">
                                <span>Suppléments bagages</span>
                                <span><?php echo number_format($supplements_bagages, 2, ',', ' '); ?>€</span>
                            </div>
                            <?php endif; ?>
                            <div class="price-item">
                                <span>Frais de service</span>
                                <span>9,00€</span>
                            </div>
                            <div class="price-item">
                                <span>Taxes aéroport</span>
                                <span>25,00€</span>
                            </div>
                            <div class="price-item price-total">
                                <span>Total payé</span>
                                <span><?php echo number_format($reservation['prix_total'], 2, ',', ' '); ?>€</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Carte d'embarquement simulée -->
                <div class="boarding-pass">
                    <div class="passenger-info">
                        <div>
                            <h4 style="color: var(--primary); margin-bottom: 1rem;">
                                <i class="fas fa-ticket-alt"></i> Carte d'embarquement
                            </h4>
                            <div class="detail-item">
                                <span class="detail-label">Passager</span>
                                <span class="detail-value">
                                    <?php echo htmlspecialchars($reservation['prenom'] . ' ' . $reservation['nom']); ?>
                                </span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Vol</span>
                                <span class="detail-value">
                                    <?php echo htmlspecialchars($reservation['code_compagnie'] . $reservation['numero_vol']); ?>
                                </span>
                            </div>
                        </div>
                        <div class="qr-section" style="padding: 0;">
                            <div class="qr-code">
                                <img src="<?php echo $qr_code_url; ?>" alt="QR Code" style="width: 100%; height: 100%;">
                            </div>
                            <small style="color: var(--secondary);">Scanner à l'embarquement</small>
                        </div>
                    </div>
                    
                    <div class="flight-info">
                        <div class="info-box">
                            <div class="info-label">Départ</div>
                            <div class="info-value"><?php echo htmlspecialchars($reservation['depart']); ?></div>
                        </div>
                        <div class="info-box">
                            <div class="info-label">Arrivée</div>
                            <div class="info-value"><?php echo htmlspecialchars($reservation['arrivee']); ?></div>
                        </div>
                        <div class="info-box">
                            <div class="info-label">Date</div>
                            <div class="info-value"><?php echo date('d/m/Y', strtotime($reservation['date_depart'])); ?></div>
                        </div>
                        <div class="info-box">
                            <div class="info-label">Heure</div>
                            <div class="info-value"><?php echo date('H:i', strtotime($reservation['date_depart'])); ?></div>
                        </div>
                        <div class="info-box">
                            <div class="info-label">Siège</div>
                            <div class="info-value">
                                <?php echo !empty($sieges) ? $sieges[0]['position'] . $sieges[0]['rang'] : 'À assigner'; ?>
                            </div>
                        </div>
                        <div class="info-box">
                            <div class="info-label">Porte</div>
                            <div class="info-value"><?php echo $reservation['porte_embarquement'] ?? '--'; ?></div>
                        </div>
                    </div>
                </div>

                <!-- Informations importantes -->
                <div class="important-notice">
                    <div class="notice-header">
                        <i class="fas fa-exclamation-circle"></i>
                        <h4 style="margin: 0;">Informations importantes</h4>
                    </div>
                    <ul class="notice-list">
                        <li>Présentez-vous à l'aéroport au moins 2 heures avant le décollage</li>
                        <li>Ayez sur vous une pièce d'identité valide et cette confirmation</li>
                        <li>L'enregistrement en ligne ouvre 24h avant le vol</li>
                        <li>Les bagages en soute doivent être déposés au plus tard 1h avant le décollage</li>
                        <li>Consultez notre politique de bagage pour les restrictions de taille et poids</li>
                    </ul>
                </div>

                <!-- Actions -->
                <div class="action-buttons">
                    <button onclick="window.print()" class="btn btn-outline">
                        <i class="fas fa-print"></i> Imprimer la confirmation
                    </button>
                    <a href="index.php" class="btn btn-primary">
                        <i class="fas fa-home"></i> Retour à l'accueil
                    </a>
                    <a href="mes_reservations.php" class="btn btn-success">
                        <i class="fas fa-list"></i> Mes réservations
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Générer des confettis
        function createConfetti() {
            const container = document.getElementById('confetti-container');
            const colors = ['#f39c12', '#e74c3c', '#3498db', '#2ecc71', '#9b59b6'];
            
            for (let i = 0; i < 50; i++) {
                const confetti = document.createElement('div');
                confetti.className = 'confetti';
                confetti.style.left = Math.random() * 100 + 'vw';
                confetti.style.background = colors[Math.floor(Math.random() * colors.length)];
                confetti.style.animationDelay = Math.random() * 5 + 's';
                confetti.style.width = Math.random() * 10 + 5 + 'px';
                confetti.style.height = confetti.style.width;
                container.appendChild(confetti);
            }
        }

        // Lancer les confettis au chargement
        document.addEventListener('DOMContentLoaded', createConfetti);

        // Animation de compteur pour le prix total
        function animateValue(element, start, end, duration) {
            let startTimestamp = null;
            const step = (timestamp) => {
                if (!startTimestamp) startTimestamp = timestamp;
                const progress = Math.min((timestamp - startTimestamp) / duration, 1);
                const value = Math.floor(progress * (end - start) + start);
                element.textContent = value.toFixed(2).replace('.', ',') + '€';
                if (progress < 1) {
                    window.requestAnimationFrame(step);
                }
            };
            window.requestAnimationFrame(step);
        }

        // Démarrer l'animation du prix
        document.addEventListener('DOMContentLoaded', () => {
            const priceElement = document.querySelector('.price-total .detail-value');
            if (priceElement) {
                const price = parseFloat(priceElement.textContent.replace('€', '').replace(',', '.'));
                animateValue(priceElement, 0, price, 2000);
            }
        });
    </script>
</body>
</html>