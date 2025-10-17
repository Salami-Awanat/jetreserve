<?php
require_once 'includes/db.php';
session_start();

// Valider et filtrer les paramètres de recherche
$depart = isset($_GET['depart']) ? trim($_GET['depart']) : '';
$arrivee = isset($_GET['arrivee']) ? trim($_GET['arrivee']) : '';
$date_depart = isset($_GET['date_depart']) ? trim($_GET['date_depart']) : '';
$passagers = isset($_GET['passagers']) ? max(1, intval($_GET['passagers'])) : 1;
$classe = isset($_GET['classe']) ? $_GET['classe'] : 'économique';
$direct = isset($_GET['direct']) ? intval($_GET['direct']) : 0;

// Classes valides pour prévenir les injections
$classes_valides = ['économique', 'affaires', 'première'];
if (!in_array($classe, $classes_valides)) {
    $classe = 'économique';
}

// Construire la requête de recherche de manière sécurisée
$sql = "SELECT v.*, c.nom_compagnie, c.code_compagnie, a.modele as avion_modele
        FROM vols v 
        JOIN compagnies c ON v.id_compagnie = c.id_compagnie 
        JOIN avions a ON v.id_avion = a.id_avion
        WHERE v.places_disponibles >= ? AND v.date_depart > NOW()";

$params = [$passagers];
$types = ['i']; // Types des paramètres: i = integer

if (!empty($depart)) {
    $sql .= " AND v.depart LIKE ?";
    $params[] = "%$depart%";
    $types[] = 's';
}

if (!empty($arrivee)) {
    $sql .= " AND v.arrivee LIKE ?";
    $params[] = "%$arrivee%";
    $types[] = 's';
}

if (!empty($date_depart)) {
    // Valider le format de date
    if (DateTime::createFromFormat('Y-m-d', $date_depart) !== false) {
        $sql .= " AND DATE(v.date_depart) = ?";
        $params[] = $date_depart;
        $types[] = 's';
    }
}

if ($direct) {
    $sql .= " AND v.escales = 0";
}

$sql .= " AND v.classe = ?";
$params[] = $classe;
$types[] = 's';

$sql .= " ORDER BY v.prix ASC";

try {
    $stmt = $pdo->prepare($sql);
    
    // Lier les paramètres avec leurs types
    foreach ($params as $key => $param) {
        $stmt->bindValue($key + 1, $param, $types[$key] === 'i' ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
    
    $stmt->execute();
    $vols = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Erreur de recherche de vols: " . $e->getMessage());
    $vols = [];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Résultats de recherche - JetReserve</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Styles de base */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8fafc;
            color: #334155;
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }
        
        /* Header */
        header {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
        }
        
        .logo {
            font-size: 1.8rem;
            font-weight: 700;
            text-decoration: none;
            color: #2563eb;
        }
        
        .logo span {
            color: #1e40af;
        }
        
        /* Boutons */
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
            margin-left: 0.5rem;
        }
        
        .btn-primary:hover {
            background: #1d4ed8;
            transform: translateY(-2px);
        }
        
        .btn-outline {
            border-color: #2563eb;
            color: #2563eb;
            background: transparent;
        }
        
        .btn-outline:hover {
            background: #2563eb;
            color: white;
        }
        
        /* Résultats de vol */
        .flight-result {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            border-left: 4px solid #2563eb;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .flight-result:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.1);
        }
        
        .flight-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .flight-airline {
            font-weight: 600;
            color: #1e293b;
        }
        
        .flight-price {
            font-size: 1.5rem;
            font-weight: 700;
            color: #2563eb;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .header-top {
                flex-direction: column;
                gap: 1rem;
            }
            
            .auth-buttons {
                display: flex;
                gap: 0.5rem;
            }
            
            .flight-header {
                flex-direction: column;
                align-items: flex-start;
            }
        }
        
        /* Message aucun résultat */
        .no-results {
            text-align: center;
            padding: 3rem;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }
        
        .no-results h3 {
            color: #64748b;
            margin-bottom: 1rem;
        }
        
        .search-summary {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        .search-summary h2 {
            color: #334155;
            margin-bottom: 0.5rem;
        }
        
        .search-criteria {
            color: #64748b;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="header-top">
                <a href="index.php" class="logo">Jet<span>Reserve</span></a>
                <div class="auth-buttons">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="dashboard.php" class="btn btn-outline">Mon compte</a>
                        <a href="logout.php" class="btn btn-primary">Déconnexion</a>
                    <?php else: ?>
                        <a href="vge64/connexion.php" class="btn btn-outline">Connexion</a>
                        <a href="vge64/inscription.php" class="btn btn-primary">Inscription</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <div class="container" style="padding: 2rem 0;">
        <!-- Résumé de la recherche -->
        <div class="search-summary">
            <h2>Résultats de recherche</h2>
            <div class="search-criteria">
                <?php
                $criteria = [];
                if (!empty($depart)) $criteria[] = "Départ: " . htmlspecialchars($depart);
                if (!empty($arrivee)) $criteria[] = "Arrivée: " . htmlspecialchars($arrivee);
                if (!empty($date_depart)) $criteria[] = "Date: " . htmlspecialchars($date_depart);
                $criteria[] = "Passagers: " . $passagers;
                $criteria[] = "Classe: " . htmlspecialchars($classe);
                if ($direct) $criteria[] = "Vol direct";
                
                echo implode(" • ", $criteria);
                ?>
            </div>
        </div>
        
        <?php if (empty($vols)): ?>
            <div class="no-results">
                <h3>Aucun vol trouvé</h3>
                <p>Essayez de modifier vos critères de recherche</p>
                <a href="index.php" class="btn btn-primary" style="margin-top: 1rem;">Nouvelle recherche</a>
            </div>
        <?php else: ?>
            <div class="search-results">
                <?php foreach ($vols as $vol): ?>
                <div class="flight-result">
                    <div class="flight-header">
                        <div class="flight-airline">
                            <i class="fas fa-plane"></i>
                            <?php echo htmlspecialchars($vol['nom_compagnie']); ?> - 
                            <?php echo htmlspecialchars($vol['code_compagnie'] . $vol['numero_vol']); ?>
                        </div>
                        <div class="flight-price"><?php echo number_format($vol['prix'], 2, ',', ' '); ?>€</div>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr auto 1fr; gap: 1rem; align-items: center; margin-bottom: 1rem;">
                        <div>
                            <div style="font-size: 1.25rem; font-weight: 600;">
                                <?php echo date('H:i', strtotime($vol['date_depart'])); ?>
                            </div>
                            <div style="color: #64748b; font-size: 0.9rem;">
                                <?php echo htmlspecialchars($vol['depart']); ?>
                            </div>
                            <div style="color: #94a3b8; font-size: 0.8rem;">
                                <?php echo date('d/m/Y', strtotime($vol['date_depart'])); ?>
                            </div>
                        </div>
                        
                        <div style="text-align: center; color: #64748b;">
                            <div style="font-size: 0.9rem;">
                                <?php 
                                $depart_time = new DateTime($vol['date_depart']);
                                $arrivee_time = new DateTime($vol['date_arrivee']);
                                $interval = $depart_time->diff($arrivee_time);
                                echo $interval->format('%hh%im');
                                ?>
                            </div>
                            <div style="border-top: 2px solid #e2e8f0; width: 80px; margin: 5px auto; position: relative;">
                                <i class="fas fa-plane" style="position: absolute; top: -8px; left: 50%; transform: translateX(-50%); background: white; padding: 0 5px; color: #2563eb;"></i>
                            </div>
                            <div style="font-size: 0.8rem;">
                                <?php echo $vol['escales'] == 0 ? 'Direct' : $vol['escales'] . ' escale(s)'; ?>
                            </div>
                        </div>
                        
                        <div>
                            <div style="font-size: 1.25rem; font-weight: 600;">
                                <?php echo date('H:i', strtotime($vol['date_arrivee'])); ?>
                            </div>
                            <div style="color: #64748b; font-size: 0.9rem;">
                                <?php echo htmlspecialchars($vol['arrivee']); ?>
                            </div>
                            <div style="color: #94a3b8; font-size: 0.8rem;">
                                <?php echo date('d/m/Y', strtotime($vol['date_arrivee'])); ?>
                            </div>
                        </div>
                    </div>
                    
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                        <div>
                            <span style="color: #64748b; font-size: 0.9rem;">
                                <i class="fas fa-chair"></i> Classe: <?php echo htmlspecialchars($vol['classe']); ?> | 
                                <i class="fas fa-plane"></i> Avion: <?php echo htmlspecialchars($vol['avion_modele']); ?> |
                                <i class="fas fa-users"></i> Places: <?php echo htmlspecialchars($vol['places_disponibles']); ?>
                            </span>
                        </div>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <a href="seat_selection.php?vol_id=<?php echo $vol['id_vol']; ?>" class="btn btn-primary">
                                <i class="fas fa-chair"></i> Choisir un siège
                            </a>
                        <?php else: ?>
                            <a href="vge64/connexion.php?redirect=search_results" class="btn btn-primary">
                                <i class="fas fa-sign-in-alt"></i> Se connecter pour réserver
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <footer style="background: #1e293b; color: white; padding: 2rem 0; margin-top: 3rem;">
        <div class="container">
            <div style="text-align: center;">
                <p>&copy; 2024 JetReserve. Tous droits réservés.</p>
            </div>
        </div>
    </footer>
</body>
</html>