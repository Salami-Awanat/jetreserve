<?php
require_once 'includes/db.php';
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['id_user'])) {
    header('Location: vge64/connexion.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

// Récupérer l'ID du vol depuis l'URL
$id_vol = isset($_GET['id_vol']) ? intval($_GET['id_vol']) : 0;

if ($id_vol <= 0) {
    header('Location: index.php');
    exit;
}

// Récupérer les détails du vol
try {
    $stmt = $pdo->prepare("
        SELECT v.*, c.nom_compagnie, c.code_compagnie, a.modele as avion_modele,
               a.capacite_total, a.configuration_sieges
        FROM vols v 
        JOIN compagnies c ON v.id_compagnie = c.id_compagnie 
        JOIN avions a ON v.id_avion = a.id_avion
        WHERE v.id_vol = ?
    ");
    $stmt->execute([$id_vol]);
    $vol = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$vol) {
        header('Location: index.php');
        exit;
    }
} catch (PDOException $e) {
    die("Erreur lors de la récupération du vol: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails du vol - JetReserve</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Variables CSS identiques à votre page d'accueil */
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
            --gray: #7f8c8d;
            --light-gray: #f9fafb;
            --border-color: #dee2e6;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--light-gray);
            color: var(--dark);
            line-height: 1.6;
        }
        
        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }
        
        /* Header Styles identique */
        header {
            background-color: var(--white);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid var(--border-color);
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
        
        .auth-buttons {
            display: flex;
            gap: 15px;
        }
        
        .btn {
            padding: 8px 16px;
            border-radius: 4px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            font-family: 'Poppins', sans-serif;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        
        .btn-outline {
            background-color: transparent;
            border: 1px solid var(--primary);
            color: var(--primary);
        }
        
        .btn-primary {
            background-color: var(--primary);
            color: var(--white);
        }
        
        .btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }
        
        .btn-lg {
            padding: 12px 24px;
            font-size: 1.1rem;
        }
        
        /* Container principal des détails */
        .vol-details-container {
            max-width: 1000px;
            margin: 2rem auto;
            background: var(--white);
            border-radius: 8px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }
        
        /* En-tête du vol */
        .vol-header {
            background: linear-gradient(135deg, var(--primary), #34495e);
            color: var(--white);
            padding: 2rem;
            position: relative;
        }
        
        .vol-header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .vol-title {
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .vol-subtitle {
            opacity: 0.9;
            font-size: 1.1rem;
        }
        
        .vol-price {
            font-size: 2.5rem;
            font-weight: 700;
        }
        
        /* Timeline du vol */
        .vol-timeline {
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            gap: 2rem;
            padding: 2rem;
            background: var(--white);
            border-bottom: 1px solid var(--border-color);
        }
        
        .aeroport {
            text-align: center;
        }
        
        .aeroport.depart {
            text-align: left;
        }
        
        .aeroport.arrivee {
            text-align: right;
        }
        
        .heure {
            font-size: 2.5rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.5rem;
        }
        
        .ville {
            font-size: 1.3rem;
            color: var(--primary);
            font-weight: 500;
            margin-bottom: 0.25rem;
        }
        
        .date {
            color: var(--secondary);
            font-size: 1rem;
        }
        
        .duree-vol {
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        
        .duree-icon {
            background: var(--light);
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            color: var(--primary);
            font-size: 1.5rem;
            border: 2px solid var(--border-color);
        }
        
        .duree-text {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.25rem;
        }
        
        .escales-text {
            color: var(--secondary);
            font-size: 0.9rem;
        }
        
        /* Détails complémentaires */
        .vol-infos {
            padding: 2rem;
        }
        
        .section-title {
            color: var(--primary);
            margin-bottom: 25px;
            font-weight: 600;
            font-size: 1.8rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .section-title i {
            color: var(--primary);
        }
        
        .details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 2rem;
        }
        
        .detail-card {
            background: var(--white);
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border-left: 4px solid var(--primary);
        }
        
        .detail-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }
        
        .detail-label {
            font-weight: 600;
            color: var(--secondary);
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .detail-value {
            font-size: 1.1rem;
            color: var(--dark);
            font-weight: 500;
        }
        
        /* Actions */
        .vol-actions {
            padding: 2rem;
            background: var(--light);
            border-top: 1px solid var(--border-color);
            text-align: center;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        /* Badge de classe */
        .class-badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            background: var(--success);
            color: var(--white);
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            margin-left: 1rem;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .vol-timeline {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .aeroport.depart,
            .aeroport.arrivee {
                text-align: center;
            }
            
            .vol-header-content {
                flex-direction: column;
                text-align: center;
            }
            
            .action-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .btn-lg {
                width: 100%;
                max-width: 300px;
            }
            
            .details-grid {
                grid-template-columns: 1fr;
            }
            
            .heure {
                font-size: 2rem;
            }
            
            .ville {
                font-size: 1.1rem;
            }
            
            .vol-price {
                font-size: 2rem;
            }
        }
        
        /* Animation pour le prix */
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .vol-price {
            animation: pulse 2s ease-in-out infinite;
        }
        
        /* Styles pour le séparateur de timeline */
        .timeline-separator {
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }
        
        .timeline-line {
            width: 2px;
            height: 60px;
            background: var(--border-color);
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <header>
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
        <div class="vol-details-container">
            <!-- En-tête avec le prix -->
            <div class="vol-header">
                <div class="vol-header-content">
                    <div>
                        <h1 class="vol-title">
                            <?php echo htmlspecialchars($vol['depart']); ?> → <?php echo htmlspecialchars($vol['arrivee']); ?>
                            <span class="class-badge"><?php echo htmlspecialchars($vol['classe']); ?></span>
                        </h1>
                        <p class="vol-subtitle">
                            <?php echo htmlspecialchars($vol['nom_compagnie']); ?> • Vol <?php echo htmlspecialchars($vol['code_compagnie'] . $vol['numero_vol']); ?>
                        </p>
                    </div>
                    <div class="vol-price">
                        <?php echo number_format($vol['prix'], 2, ',', ' '); ?>€
                    </div>
                </div>
            </div>
            
            <!-- Timeline du vol -->
            <div class="vol-timeline">
                <div class="aeroport depart">
                    <div class="heure"><?php echo date('H:i', strtotime($vol['date_depart'])); ?></div>
                    <div class="ville"><?php echo htmlspecialchars($vol['depart']); ?></div>
                    <div class="date"><?php echo date('d F Y', strtotime($vol['date_depart'])); ?></div>
                </div>
                
                <div class="duree-vol">
                    <div class="duree-icon">
                        <i class="fas fa-plane"></i>
                    </div>
                    <div class="duree-text">
                        <?php 
                        $depart_time = new DateTime($vol['date_depart']);
                        $arrivee_time = new DateTime($vol['date_arrivee']);
                        $interval = $depart_time->diff($arrivee_time);
                        echo $interval->format('%hh%im');
                        ?>
                    </div>
                    <div class="escales-text">
                        <?php echo $vol['escales'] == 0 ? 'Vol direct' : $vol['escales'] . ' escale(s)'; ?>
                    </div>
                </div>
                
                <div class="aeroport arrivee">
                    <div class="heure"><?php echo date('H:i', strtotime($vol['date_arrivee'])); ?></div>
                    <div class="ville"><?php echo htmlspecialchars($vol['arrivee']); ?></div>
                    <div class="date"><?php echo date('d F Y', strtotime($vol['date_arrivee'])); ?></div>
                </div>
            </div>
            
            <!-- Détails complémentaires -->
            <div class="vol-infos">
                <h2 class="section-title">
                    <i class="fas fa-info-circle"></i>
                    Informations du vol
                </h2>
                
                <div class="details-grid">
                    <div class="detail-card">
                        <div class="detail-label">Compagnie aérienne</div>
                        <div class="detail-value"><?php echo htmlspecialchars($vol['nom_compagnie']); ?></div>
                    </div>
                    
                    <div class="detail-card">
                        <div class="detail-label">Numéro de vol</div>
                        <div class="detail-value"><?php echo htmlspecialchars($vol['code_compagnie'] . $vol['numero_vol']); ?></div>
                    </div>
                    
                    <div class="detail-card">
                        <div class="detail-label">Type d'avion</div>
                        <div class="detail-value"><?php echo htmlspecialchars($vol['avion_modele']); ?></div>
                    </div>
                    
                    <div class="detail-card">
                        <div class="detail-label">Classe</div>
                        <div class="detail-value"><?php echo htmlspecialchars($vol['classe']); ?></div>
                    </div>
                    
                    <div class="detail-card">
                        <div class="detail-label">Places disponibles</div>
                        <div class="detail-value"><?php echo htmlspecialchars($vol['places_disponibles']); ?> sièges</div>
                    </div>
                    
                    <div class="detail-card">
                        <div class="detail-label">Escales</div>
                        <div class="detail-value"><?php echo $vol['escales'] == 0 ? 'Aucune' : $vol['escales'] . ' escale(s)'; ?></div>
                    </div>
                </div>
            </div>
            
            <!-- Actions -->
            <div class="vol-actions">
                <div class="action-buttons">
                    <a href="reservation.php?id_vol=<?php echo $vol['id_vol']; ?>" class="btn btn-primary btn-lg">
                        <i class="fas fa-shopping-cart"></i> Réserver maintenant
                    </a>
                    <a href="index.php" class="btn btn-outline btn-lg">
                        <i class="fas fa-arrow-left"></i> Retour à l'accueil
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>