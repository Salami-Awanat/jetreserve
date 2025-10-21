<?php
session_start();
require_once 'includes/db.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['id_user'])) {
    header('Location: connexion.php');
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

// Récupérer les sièges disponibles ET occupés pour ce vol
try {
    // Récupérer tous les sièges de l'avion
    $stmt = $pdo->prepare("
        SELECT sa.* 
        FROM sieges_avion sa
        WHERE sa.id_avion = ? 
        AND sa.statut = 'actif'
        ORDER BY sa.rang, sa.position
    ");
    $stmt->execute([$vol['id_avion']]);
    $tous_sieges = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer les sièges occupés
    $stmt_occupes = $pdo->prepare("
        SELECT rs.id_siege
        FROM reservation_sieges rs
        JOIN reservations r ON rs.id_reservation = r.id_reservation
        WHERE r.id_vol = ? AND r.statut IN ('confirmé', 'en attente')
    ");
    $stmt_occupes->execute([$id_vol]);
    $sieges_occupes = $stmt_occupes->fetchAll(PDO::FETCH_COLUMN);

} catch (PDOException $e) {
    $tous_sieges = [];
    $sieges_occupes = [];
}

// Récupérer les options de bagage
try {
    $stmt_bagages = $pdo->prepare("SELECT * FROM options_bagage WHERE statut = 'actif' ORDER BY prix_supplement");
    $stmt_bagages->execute();
    $options_bagage = $stmt_bagages->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $options_bagage = [];
}

// Traitement du formulaire de réservation
$erreur = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reserver'])) {
    
    $nombre_passagers = intval($_POST['nombre_passagers']);
    $sieges_selectionnes = isset($_POST['sieges']) ? array_map('intval', $_POST['sieges']) : [];
    $bagages_selectionnes = isset($_POST['bagages']) ? $_POST['bagages'] : [];
    
    // Validation
    $erreurs = [];
    
    if ($nombre_passagers <= 0 || $nombre_passagers > 10) {
        $erreurs[] = "Nombre de passagers invalide";
    }
    
    if (count($sieges_selectionnes) === 0) {
        $erreurs[] = "Vous devez sélectionner au moins un siège";
    }
    
    // Vérification de la disponibilité des sièges
    foreach ($sieges_selectionnes as $siege_id) {
        if (in_array($siege_id, $sieges_occupes)) {
            $erreurs[] = "Un des sièges sélectionnés n'est plus disponible";
            break;
        }
    }
    
    if (empty($erreurs)) {
        try {
            $pdo->beginTransaction();
            

            $prix_total = $vol['prix'] * $nombre_passagers;
            
            // Suppléments sièges
            foreach ($sieges_selectionnes as $id_siege) {
                foreach ($tous_sieges as $siege) {
                    if ($siege['id_siege'] == $id_siege) {
                        $prix_total += $siege['supplement_prix'];
                        break;
                    }
                }
            }
            
            // Suppléments bagages
            foreach ($bagages_selectionnes as $id_option => $quantite) {
                $quantite = intval($quantite);
                if ($quantite > 0) {
                    foreach ($options_bagage as $option) {
                        if ($option['id_option'] == $id_option) {
                            $prix_total += $option['prix_supplement'] * $quantite;
                            break;
                        }
                    }
                }
            }
            
            // Frais fixes
            $prix_total += 9.00 + 25.00;
            
            // CRÉATION DE LA RÉSERVATION
            $stmt = $pdo->prepare("
                INSERT INTO reservations (id_user, id_vol, statut, nombre_passagers, prix_total, date_reservation)
                VALUES (?, ?, 'en attente', ?, ?, NOW())
            ");
            $stmt->execute([$_SESSION['id_user'], $id_vol, $nombre_passagers, $prix_total]);
            $id_reservation = $pdo->lastInsertId();
            
            if (!$id_reservation) {
                throw new Exception("Échec de la création de la réservation");
            }
            
            // ASSOCIATION DES SIÈGES
            $stmt_siege = $pdo->prepare("
                INSERT INTO reservation_sieges (id_reservation, id_siege, prix_paye)
                VALUES (?, ?, ?)
            ");
            
            foreach ($sieges_selectionnes as $id_siege) {
                $prix_siege = $vol['prix'];
                foreach ($tous_sieges as $siege) {
                    if ($siege['id_siege'] == $id_siege) {
                        $prix_siege += $siege['supplement_prix'];
                        break;
                    }
                }
                $stmt_siege->execute([$id_reservation, $id_siege, $prix_siege]);
            }
            
            // ASSOCIATION DES BAGAGES
            if (!empty($bagages_selectionnes)) {
                $stmt_bagage = $pdo->prepare("
                    INSERT INTO reservation_bagages (id_reservation, id_option, quantite, prix_applique)
                    VALUES (?, ?, ?, ?)
                ");
                
                foreach ($bagages_selectionnes as $id_option => $quantite) {
                    $quantite = intval($quantite);
                    if ($quantite > 0) {
                        $prix_option = 0;
                        foreach ($options_bagage as $option) {
                            if ($option['id_option'] == $id_option) {
                                $prix_option = $option['prix_supplement'];
                                break;
                            }
                        }
                        $stmt_bagage->execute([$id_reservation, $id_option, $quantite, $prix_option]);
                    }
                }
            }
            
            $pdo->commit();
            
            error_log("=== RÉSERVATION RÉUSSIE - ID: $id_reservation ===");
            
            header('Location: paiement.php?reservation_id=' . $id_reservation);
            exit;
            
        } catch (Exception $e) {
            $pdo->rollBack();

            $erreurs[] = "Erreur lors de la réservation: " . $e->getMessage();
            

            $erreur_message = "Erreur lors de la réservation: " . $e->getMessage();
            $erreurs[] = $erreur_message;
            error_log("=== ERREUR RÉSERVATION: " . $e->getMessage() . " ===");
            echo "<div style='background: red; color: white; padding: 20px; margin: 20px;'>ERREUR: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
        
    }
    
    if (!empty($erreurs)) {
        $erreur = implode("<br>", $erreurs);
        echo "<div style='background: orange; color: white; padding: 20px; margin: 20px;'>ERREURS DE VALIDATION:<br>" . htmlspecialchars($erreur) . "</div>";
    }
}

// Organiser les sièges par rangée pour l'affichage
$sieges_par_rang = [];
foreach ($tous_sieges as $siege) {
    $sieges_par_rang[$siege['rang']][] = $siege;
}
ksort($sieges_par_rang);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réservation - JetReserve</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
        
        .reservation-container {
            max-width: 1000px;
            margin: 2rem auto;
            background: var(--white);
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .reservation-header {
            background: linear-gradient(135deg, var(--corsair-dark-blue), var(--corsair-dark-blue));
            color: var(--white);
            padding: 2rem;
        }
        
        .reservation-title {
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .vol-resume {
            padding: 2rem;
            border-bottom: 1px solid #dee2e6;
        }
        
        .vol-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .info-card {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            border-left: 4px solid var(--corsair-blue);
        }
        
        .info-label {
            font-weight: 600;
            color: var(--corsair-blue);
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }
        
        .info-value {
            font-size: 1.1rem;
            color: var(--dark);
            font-weight: 600;
        }
        
        .reservation-form {
            padding: 2rem;
        }
        
        .form-section {
            margin-bottom: 2rem;
            padding: 1.5rem;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            background: var(--white);
        }
        
        .section-title {
            color: var(--corsair-blue);
            margin-bottom: 1rem;
            font-weight: 600;
            font-size: 1.3rem;
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }
        
        .form-control {
            width: 100%;
            padding: 10px 12px;
            border: 2px solid #dee2e6;
            border-radius: 6px;
            font-family: 'Poppins', sans-serif;
        }
        
        .avion-schema {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        
        .cockpit {
            background: var(--corsair-blue);
            color: white;
            padding: 1rem;
            text-align: center;
            border-radius: 8px 8px 0 0;
            margin-bottom: 1.5rem;
            font-weight: bold;
        }
        
        .rangee-sieges {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 1rem;
            gap: 10px;
        }
        
        .numero-rangee {
            width: 30px;
            text-align: center;
            font-weight: 700;
            color: var(--corsair-blue);
        }
        
        .siege {
            width: 40px;
            height: 40px;
            border: 2px solid #dee2e6;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-weight: 600;
            background: var(--white);
        }
        
        .siege.disponible {
            border-color: var(--success);
        }
        
        .siege.disponible:hover {
            background: var(--success);
            color: var(--white);
        }
        
        .siege.selectionne {
            background: var(--corsair-blue);
            color: var(--white);
            border-color: var(--corsair-blue);
        }
        
        .siege.occupe {
            background: var(--danger);
            color: var(--white);
            border-color: var(--danger);
            cursor: not-allowed;
        }
        
        .couloir {
            width: 40px;
            text-align: center;
            color: var(--secondary);
        }
        
        .legende-sieges {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-top: 1rem;
            flex-wrap: wrap;
        }
        
        .legende-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .legende-couleur {
            width: 20px;
            height: 20px;
            border-radius: 4px;
        }
        
        .bagages-container {
            display: grid;
            gap: 1rem;
            margin-top: 1rem;
        }
        
        .bagage-option {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 1rem;
            background: var(--white);
        }
        
        .bagage-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }
        
        .bagage-price {
            background: var(--success);
            color: white;
            padding: 0.3rem 0.8rem;
            border-radius: 15px;
            font-weight: bold;
        }
        
        .bagage-controls {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 1rem;
        }
        
        .btn-qty {
            width: 35px;
            height: 35px;
            border: 1px solid #dee2e6;
            background: #f8f9fa;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .bagage-qty {
            width: 60px;
            text-align: center;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 0.5rem;
        }
        
        .resume-prix {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        
        .ligne-prix {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.8rem;
            padding-bottom: 0.8rem;
            border-bottom: 1px solid #dee2e6;
        }
        
        .prix-total {
            border-top: 2px solid #dee2e6;
            padding-top: 0.8rem;
            margin-top: 0.8rem;
            font-weight: 700;
            font-size: 1.2rem;
            color: var(--corsair-blue);
        }
        
        .alert {
            padding: 1rem;
            border-radius: 6px;
            margin-bottom: 1rem;
        }
        
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .btn {
            padding: 12px 24px;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            border: none;
            font-family: 'Poppins', sans-serif;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-success {
            background: var(--success);
            color: var(--white);
            font-weight: 600;
            font-size: 1.1rem;
        }
        
        .btn-full {
            width: 100%;
            justify-content: center;
        }
        
        .timer {
            background: var(--warning);
            color: var(--dark);
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-weight: bold;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }
        
        @media (max-width: 768px) {
            .vol-info-grid {
                grid-template-columns: 1fr;
            }
            
            .rangee-sieges {
                flex-wrap: wrap;
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
                    JetReserve
                </a>
                <div>
                    <span>Bonjour, <?php echo htmlspecialchars($_SESSION['prenom'] ?? 'Utilisateur'); ?></span>
                </div>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="reservation-container">
            <div class="reservation-header">
                <h1 class="reservation-title"><i class="fas fa-plane"></i> Réservation de vol</h1>
                <p>Finalisez votre réservation en choisissant vos sièges et options</p>
                
                <div class="timer">
                    <i class="fas fa-clock"></i>
                    <span id="reservation-timer">15:00</span>
                </div>
            </div>
            
            <div class="vol-resume">
                <h2 class="section-title">Résumé du vol</h2>
                
                <div class="vol-info-grid">
                    <div class="info-card">
                        <div class="info-label">Départ</div>
                        <div class="info-value"><?php echo htmlspecialchars($vol['depart']); ?></div>
                    </div>
                    <div class="info-card">
                        <div class="info-label">Arrivée</div>
                        <div class="info-value"><?php echo htmlspecialchars($vol['arrivee']); ?></div>
                    </div>
                    <div class="info-card">
                        <div class="info-label">Date et heure</div>
                        <div class="info-value"><?php echo date('d/m/Y H:i', strtotime($vol['date_depart'])); ?></div>
                    </div>
                    <div class="info-card">
                        <div class="info-label">Compagnie</div>
                        <div class="info-value"><?php echo htmlspecialchars($vol['nom_compagnie']); ?></div>
                    </div>
                </div>
            </div>
            
            <form method="POST" class="reservation-form" id="reservationForm">
                <?php if (isset($erreur)): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i> 
                        <?php echo $erreur; ?>
                    </div>
                <?php endif; ?>
                
                <div class="form-section">
                    <h3 class="section-title">Nombre de passagers</h3>
                    <div class="form-group">
                        <label for="nombre_passagers">Nombre de passagers *</label>
                        <select name="nombre_passagers" id="nombre_passagers" class="form-control" required>
                            <?php for ($i = 1; $i <= min(10, $vol['places_disponibles']); $i++): ?>
                                <option value="<?php echo $i; ?>">
                                    <?php echo $i; ?> passager(s)
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-section">
                    <h3 class="section-title">Choix des sièges</h3>
                    
                    <div class="avion-schema">
                        <div class="cockpit">
                            COCKPIT
                        </div>
                        
                        <?php foreach ($sieges_par_rang as $rang => $sieges_rang): ?>
                            <div class="rangee-sieges">
                                <div class="numero-rangee"><?php echo $rang; ?></div>
                                
                                <?php 
                                $last_position = '';
                                foreach ($sieges_rang as $siege): 
                                    $is_occupe = in_array($siege['id_siege'], $sieges_occupes);
                                    $classe_siege = $is_occupe ? 'occupe' : 'disponible';
                                ?>
                                    <label class="siege <?php echo $classe_siege; ?>">
                                        <input type="checkbox" name="sieges[]" value="<?php echo $siege['id_siege']; ?>" 
                                               style="display: none;" 
                                               <?php echo $is_occupe ? 'disabled' : ''; ?>
                                               onchange="toggleSiege(this)">
                                        <?php echo $siege['position']; ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                        
                        <div class="legende-sieges">
                            <div class="legende-item">
                                <div class="legende-couleur" style="background: var(--success);"></div>
                                <span>Disponible</span>
                            </div>
                            <div class="legende-item">
                                <div class="legende-couleur" style="background: var(--corsair-blue);"></div>
                                <span>Sélectionné</span>
                            </div>
                            <div class="legende-item">
                                <div class="legende-couleur" style="background: var(--danger);"></div>
                                <span>Occupé</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="form-section">
                    <h3 class="section-title">Options de bagage</h3>
                    
                    <div class="bagages-container">
                        <?php foreach ($options_bagage as $option): ?>
                            <?php if ($option['nom_option'] !== 'Bagage à main'): ?>
                                <div class="bagage-option">
                                    <div class="bagage-header">
                                        <h4><?php echo htmlspecialchars($option['nom_option']); ?></h4>
                                        <span class="bagage-price">+<?php echo number_format($option['prix_supplement'], 2, ',', ' '); ?>€</span>
                                    </div>
                                    
                                    <?php if (!empty($option['description'])): ?>
                                        <p><?php echo htmlspecialchars($option['description']); ?></p>
                                    <?php endif; ?>
                                    
                                    <div class="bagage-controls">
                                        <button type="button" class="btn-qty minus" data-target="bagage_<?php echo $option['id_option']; ?>">-</button>
                                        <input type="number" 
                                               name="bagages[<?php echo $option['id_option']; ?>]" 
                                               id="bagage_<?php echo $option['id_option']; ?>"
                                               class="bagage-qty" 
                                               value="0" 
                                               min="0" 
                                               max="10">
                                        <button type="button" class="btn-qty plus" data-target="bagage_<?php echo $option['id_option']; ?>">+</button>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="form-section">
                    <h3 class="section-title">Résumé du prix</h3>
                    <div class="resume-prix">
                        <div class="ligne-prix">
                            <span>Prix de base (<span id="passagers-count">1</span> passager(s)):</span>
                            <span id="prix-base"><?php echo number_format($vol['prix'], 2, ',', ' '); ?>€</span>
                        </div>
                        <div class="ligne-prix">
                            <span>Suppléments sièges:</span>
                            <span id="supplements-total">0,00€</span>
                        </div>
                        <div class="ligne-prix">
                            <span>Suppléments bagages:</span>
                            <span id="supplements-bagage">0,00€</span>
                        </div>
                        <div class="ligne-prix">
                            <span>Frais de service:</span>
                            <span>9,00€</span>
                        </div>
                        <div class="ligne-prix">
                            <span>Taxes aéroport:</span>
                            <span>25,00€</span>
                        </div>
                        <div class="ligne-prix prix-total">
                            <span>Total:</span>
                            <span id="prix-total"><?php echo number_format($vol['prix'] + 9 + 25, 2, ',', ' '); ?>€</span>
                        </div>
                    </div>
                </div>
                
                <div class="form-section">
                    <button type="submit" name="reserver" class="btn btn-success btn-full">
                        <i class="fas fa-credit-card"></i> Confirmer et procéder au paiement
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Variables globales
        const prixBase = <?php echo $vol['prix']; ?>;
        const fraisService = 9.00;
        const taxesAeroport = 25.00;
        const supplementsSieges = {};
        
        <?php foreach ($tous_sieges as $siege): ?>
        supplementsSieges[<?php echo $siege['id_siege']; ?>] = <?php echo $siege['supplement_prix']; ?>;
        <?php endforeach; ?>

        // Fonction pour calculer le prix total
        function calculerPrix() {
            const nbPassagers = parseInt(document.getElementById('nombre_passagers').value);
            let supplements = 0;
            let supplementBagages = 0;
            
            // Suppléments sièges
            document.querySelectorAll('input[name="sieges[]"]:checked').forEach(checkbox => {
                const siegeId = parseInt(checkbox.value);
                supplements += supplementsSieges[siegeId] || 0;
            });
            
            // Suppléments bagages
            document.querySelectorAll('input.bagage-qty').forEach(input => {
                const optionId = parseInt(input.name.match(/\[(\d+)\]/)[1]);
                const quantite = parseInt(input.value) || 0;
                
                // Trouver le prix de l'option (simplifié)
                const prixOption = parseFloat(input.closest('.bagage-option').querySelector('.bagage-price').textContent.replace('+', '').replace('€', '').replace(',', '.'));
                supplementBagages += prixOption * quantite;
            });
            
            const total = (prixBase * nbPassagers) + supplements + supplementBagages + fraisService + taxesAeroport;
            
            // Mise à jour de l'affichage
            document.getElementById('passagers-count').textContent = nbPassagers;
            document.getElementById('prix-base').textContent = (prixBase * nbPassagers).toFixed(2).replace('.', ',') + '€';
            document.getElementById('supplements-total').textContent = supplements.toFixed(2).replace('.', ',') + '€';
            document.getElementById('supplements-bagage').textContent = supplementBagages.toFixed(2).replace('.', ',') + '€';
            document.getElementById('prix-total').textContent = total.toFixed(2).replace('.', ',') + '€';
        }

        // Fonction pour gérer la sélection des sièges
        function toggleSiege(checkbox) {
            const siege = checkbox.parentElement;
            const maxSieges = parseInt(document.getElementById('nombre_passagers').value);
            const siegesSelectionnes = document.querySelectorAll('input[name="sieges[]"]:checked');
            
            if (checkbox.checked && siegesSelectionnes.length > maxSieges) {
                checkbox.checked = false;
                siege.classList.remove('selectionne');
                alert(`Vous ne pouvez sélectionner que ${maxSieges} siège(s) pour ${maxSieges} passager(s)`);
                return;
            }
            
            siege.classList.toggle('selectionne', checkbox.checked);
            calculerPrix();
        }

        // Gestion des boutons quantité
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('btn-qty')) {
                const target = e.target.getAttribute('data-target');
                const input = document.getElementById(target);
                let value = parseInt(input.value) || 0;
                
                if (e.target.classList.contains('plus')) {
                    value++;
                } else if (e.target.classList.contains('minus') && value > 0) {
                    value--;
                }
                
                input.value = value;
                calculerPrix();
            }
        });

        // Timer de réservation
        let timer = 15 * 60;
        const timerElement = document.getElementById('reservation-timer');

        function updateTimer() {
            const minutes = Math.floor(timer / 60);
            const seconds = timer % 60;
            timerElement.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            
            if (timer <= 0) {
                clearInterval(timerInterval);
                alert('Temps écoulé ! Votre réservation a expiré.');
                window.location.href = 'index.php';
            }
            
            timer--;
        }

        const timerInterval = setInterval(updateTimer, 1000);

        // Événements pour le calcul du prix et la soumission
        document.getElementById('nombre_passagers').addEventListener('change', function() {
            const maxSieges = parseInt(this.value);
            const siegesSelectionnes = document.querySelectorAll('input[name="sieges[]"]:checked');
            
            // Désélectionner les sièges en trop
            if (siegesSelectionnes.length > maxSieges) {
                for (let i = maxSieges; i < siegesSelectionnes.length; i++) {
                    siegesSelectionnes[i].checked = false;
                    siegesSelectionnes[i].parentElement.classList.remove('selectionne');
                }
            }
            
            calculerPrix();
        });


        // Validation du formulaire
        document.getElementById('reservationForm').addEventListener('submit', function(e) {
            const nbPassagers = parseInt(document.getElementById('nombre_passagers').value);
            const siegesSelectionnes = document.querySelectorAll('input[name="sieges[]"]:checked');
            
            if (siegesSelectionnes.length !== nbPassagers) {
                e.preventDefault();
                alert(`Vous devez sélectionner exactement ${nbPassagers} siège(s) pour ${nbPassagers} passager(s)`);
                return;
            }
        });

        // Initialiser le calcul du prix
        calculerPrix();

        // beforeunload temporairement désactivé pour debug
        console.log('beforeunload désactivé pour debug');


        // Initialisation
        calculerPrix();
    </script>
</body>
</html>