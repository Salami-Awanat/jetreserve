<?php
// reservation_sieges.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

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

    // Filtrer les sièges disponibles
    $sieges_disponibles = array_filter($tous_sieges, function($siege) use ($sieges_occupes) {
        return !in_array($siege['id_siege'], $sieges_occupes);
    });

} catch (PDOException $e) {
    error_log("Erreur sièges: " . $e->getMessage());
    $sieges_disponibles = [];
    $sieges_occupes = [];
}

// Traitement de la sélection des sièges
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selectionner_sieges'])) {
    $nombre_passagers = intval($_POST['nombre_passagers']);
    $sieges_selectionnes = isset($_POST['sieges']) ? array_map('intval', $_POST['sieges']) : [];
    
    // Validation
    $erreurs = [];
    
    if ($nombre_passagers <= 0 || $nombre_passagers > 10) {
        $erreurs[] = "Nombre de passagers invalide";
    }
    
    if (count($sieges_selectionnes) !== $nombre_passagers) {
        $erreurs[] = "Vous devez sélectionner exactement " . $nombre_passagers . " siège(s)";
    }
    
    // Vérification de la disponibilité des sièges
    foreach ($sieges_selectionnes as $siege_id) {
        if (in_array($siege_id, $sieges_occupes)) {
            $erreurs[] = "Un des sièges sélectionnés n'est plus disponible";
            break;
        }
    }
    
    if (empty($erreurs)) {
        // Stocker la sélection en session pour l'étape suivante
        $_SESSION['reservation_temp'] = [
            'id_vol' => $id_vol,
            'nombre_passagers' => $nombre_passagers,
            'sieges_selectionnes' => $sieges_selectionnes
        ];
        
        // Rediriger vers la page des bagages
        header('Location: reservation_bagages.php');
        exit;
    }
    
    if (!empty($erreurs)) {
        $erreur = implode("<br>", $erreurs);
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sélection des sièges - JetReserve</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #e2001a;
            --secondary: #0033a0;
            --success: #28a745;
            --danger: #dc3545;
            --warning: #ffc107;
            --light: #f8f9fa;
            --dark: #343a40;
            --white: #ffffff;
            --gray: #6c757d;
            --light-gray: #e9ecef;
            --border-color: #dee2e6;
            --premium: #d4af37;
            --business: #6f42c1;
            --economy: #20c997;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e3e8f0 100%);
            color: var(--dark);
            line-height: 1.6;
            min-height: 100vh;
        }
        
        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        header {
            background: var(--white);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
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
            font-size: 32px;
            font-weight: 700;
            color: var(--primary);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .logo span {
            color: var(--secondary);
        }
        
        .progress-steps {
            display: flex;
            justify-content: center;
            margin: 30px 0;
            position: relative;
        }
        
        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            z-index: 2;
            margin: 0 50px;
        }
        
        .step-number {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--light-gray);
            border: 3px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-bottom: 8px;
            transition: all 0.3s ease;
        }
        
        .step.active .step-number {
            background: var(--primary);
            color: var(--white);
            border-color: var(--primary);
        }
        
        .step.completed .step-number {
            background: var(--success);
            color: var(--white);
            border-color: var(--success);
        }
        
        .step-label {
            font-size: 0.9rem;
            color: var(--gray);
            font-weight: 500;
        }
        
        .step.active .step-label {
            color: var(--primary);
            font-weight: 600;
        }
        
        .reservation-card {
            background: var(--white);
            border-radius: 20px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
            padding: 40px;
            margin: 30px 0;
        }
        
        .vol-resume {
            background: linear-gradient(135deg, var(--primary), #ff3344);
            color: white;
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 10px;
            font-weight: 500;
            color: var(--dark);
            font-size: 1.1rem;
        }
        
        .form-control {
            width: 100%;
            padding: 15px;
            border: 2px solid var(--border-color);
            border-radius: 10px;
            font-family: 'Poppins', sans-serif;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: var(--light);
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(226, 0, 26, 0.1);
        }
        
        .avion-schema {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            padding: 30px;
            border-radius: 15px;
            margin: 30px 0;
            position: relative;
            border: 2px solid var(--light-gray);
        }
        
        .cockpit {
            background: linear-gradient(135deg, var(--primary), #ff3344);
            color: white;
            padding: 15px;
            text-align: center;
            border-radius: 15px 15px 0 0;
            margin-bottom: 30px;
            font-weight: bold;
            font-size: 1.1rem;
        }
        
        .rangee-sieges {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 20px;
            gap: 15px;
        }
        
        .numero-rangee {
            width: 40px;
            text-align: center;
            font-weight: 600;
            color: var(--gray);
            background: var(--white);
            padding: 8px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .siege {
            width: 50px;
            height: 50px;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
            position: relative;
            background: var(--white);
        }
        
        .siege.disponible {
            border-color: var(--success);
            background: var(--white);
        }
        
        .siege.disponible:hover {
            background: var(--success);
            color: var(--white);
            transform: scale(1.1);
        }
        
        .siege.selectionne {
            background: var(--primary);
            color: var(--white);
            border-color: var(--primary);
            transform: scale(1.1);
        }
        
        .siege.occupe {
            background: var(--danger);
            color: var(--white);
            border-color: var(--danger);
            cursor: not-allowed;
            opacity: 0.7;
        }
        
        .siege.premium {
            border-color: var(--premium);
            background: linear-gradient(135deg, #fff9e6, #ffefb3);
        }
        
        .siege.business {
            border-color: var(--business);
            background: linear-gradient(135deg, #f0e6ff, #d9c8ff);
        }
        
        .siege.economy {
            border-color: var(--economy);
        }
        
        .siege::after {
            content: attr(data-supplement);
            position: absolute;
            top: -25px;
            font-size: 0.7rem;
            color: var(--secondary);
            font-weight: 600;
            background: var(--white);
            padding: 2px 5px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .couloir {
            width: 60px;
            text-align: center;
            color: var(--gray);
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .legende-sieges {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-top: 30px;
        }
        
        .legende-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            background: var(--light);
            border-radius: 10px;
        }
        
        .legende-couleur {
            width: 25px;
            height: 25px;
            border-radius: 6px;
            border: 2px solid var(--border-color);
        }
        
        .selected-items {
            background: linear-gradient(135deg, var(--light), var(--light-gray));
            padding: 20px;
            border-radius: 15px;
            margin-top: 20px;
            border: 2px solid var(--border-color);
        }
        
        .selected-title {
            color: var(--primary);
            font-weight: 600;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .item-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--white);
            color: var(--dark);
            padding: 8px 15px;
            border-radius: 20px;
            margin: 5px;
            font-size: 0.9rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border: 1px solid var(--border-color);
        }
        
        .btn {
            padding: 15px 30px;
            border-radius: 25px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            font-family: 'Poppins', sans-serif;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 1rem;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary), #ff3344);
            color: var(--white);
        }
        
        .btn-success {
            background: linear-gradient(135deg, var(--success), #34ce57);
            color: var(--white);
        }
        
        .btn-full {
            width: 100%;
            justify-content: center;
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            border: 2px solid transparent;
        }
        
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
        }
        
        .class-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-left: 10px;
        }
        
        .badge-premium {
            background: var(--premium);
            color: white;
        }
        
        .badge-business {
            background: var(--business);
            color: white;
        }
        
        .badge-economy {
            background: var(--economy);
            color: white;
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
            </div>
        </div>
    </header>

    <div class="container">
        <!-- Barre de progression -->
        <div class="progress-steps">
            <div class="step completed">
                <div class="step-number"><i class="fas fa-check"></i></div>
                <div class="step-label">Vol</div>
            </div>
            <div class="step active">
                <div class="step-number">2</div>
                <div class="step-label">Sièges</div>
            </div>
            <div class="step">
                <div class="step-number">3</div>
                <div class="step-label">Bagages</div>
            </div>
            <div class="step">
                <div class="step-number">4</div>
                <div class="step-label">Paiement</div>
            </div>
        </div>

        <div class="reservation-card">
            <!-- Résumé du vol -->
            <div class="vol-resume">
                <h2 style="margin-bottom: 15px;"><?php echo htmlspecialchars($vol['depart']); ?> → <?php echo htmlspecialchars($vol['arrivee']); ?></h2>
                <p><strong>Date :</strong> <?php echo date('d/m/Y', strtotime($vol['date_depart'])); ?></p>
                <p><strong>Vol :</strong> <?php echo htmlspecialchars($vol['nom_compagnie']); ?> - <?php echo htmlspecialchars($vol['numero_vol']); ?></p>
                <p><strong>Avion :</strong> <?php echo htmlspecialchars($vol['avion_modele']); ?></p>
            </div>

            <!-- Formulaire de sélection des sièges -->
            <form method="POST" id="siegesForm">
                <?php if (isset($erreur)): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i> 
                        <div><strong>Erreur :</strong> <?php echo $erreur; ?></div>
                    </div>
                <?php endif; ?>

                <!-- Sélection du nombre de passagers -->
                <div class="form-group">
                    <label for="nombre_passagers">
                        <i class="fas fa-users"></i> Nombre de passagers
                    </label>
                    <select name="nombre_passagers" id="nombre_passagers" class="form-control" required>
                        <option value="">Sélectionnez le nombre de passagers</option>
                        <?php for ($i = 1; $i <= min(10, $vol['places_disponibles']); $i++): ?>
                            <option value="<?php echo $i; ?>" <?php echo isset($_POST['nombre_passagers']) && $_POST['nombre_passagers'] == $i ? 'selected' : ''; ?>>
                                <?php echo $i; ?> passager(s)
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>

                <!-- Sélection des sièges -->
                <div class="form-group">
                    <label>
                        <i class="fas fa-chair"></i> Choix des sièges
                    </label>
                    
                    <!-- Affichage des sièges sélectionnés -->
                    <div class="selected-items" id="selected-seats-display" style="display: none;">
                        <h4 class="selected-title">
                            <i class="fas fa-check-circle"></i> Sièges sélectionnés
                        </h4>
                        <div id="selected-seats-list"></div>
                    </div>

                    <div class="avion-schema">
                        <div class="cockpit">
                            <i class="fas fa-plane"></i> COCKPIT - VOL <?php echo htmlspecialchars($vol['numero_vol']); ?>
                        </div>
                        
                        <?php
                        // Organiser les sièges par rangée
                        $sieges_par_rang = [];
                        foreach ($sieges_disponibles as $siege) {
                            $sieges_par_rang[$siege['rang']][] = $siege;
                        }
                        
                        // Trier par rangée
                        ksort($sieges_par_rang);
                        
                        foreach ($sieges_par_rang as $rang => $sieges_rang):
                            // Déterminer la configuration des sièges
                            $positions = array_column($sieges_rang, 'position');
                            $has_couloir = in_array('C', $positions);
                        ?>
                            <div class="rangee-sieges">
                                <div class="numero-rangee"><?php echo $rang; ?></div>
                                
                                <?php 
                                $last_position = '';
                                foreach ($sieges_rang as $siege): 
                                    // Ajouter un espace pour le couloir si nécessaire
                                    if ($has_couloir && $last_position === 'A' && $siege['position'] === 'C') {
                                        echo '<div class="couloir">Couloir</div>';
                                    }
                                    
                                    $classe_siege = 'disponible';
                                    if (in_array($siege['id_siege'], $sieges_occupes)) {
                                        $classe_siege = 'occupe';
                                    }
                                    
                                    // Ajouter classe pour siège premium/business
                                    $classe_siege .= ' ' . $siege['classe'];
                                ?>
                                    <label class="siege <?php echo $classe_siege; ?>" 
                                           data-supplement="<?php echo $siege['supplement_prix'] > 0 ? '+' . $siege['supplement_prix'] . '€' : ''; ?>"
                                           title="Siège <?php echo $siege['position'].$rang; ?> - <?php echo $siege['classe']; ?><?php echo $siege['supplement_prix'] > 0 ? ' - Supplément: ' . $siege['supplement_prix'] . '€' : ''; ?>">
                                        <input type="checkbox" name="sieges[]" value="<?php echo $siege['id_siege']; ?>" 
                                               style="display: none;" 
                                               <?php echo in_array($siege['id_siege'], $sieges_occupes) ? 'disabled' : ''; ?>
                                               onchange="toggleSiege(this)">
                                        <?php echo $siege['position']; ?>
                                    </label>
                                <?php 
                                    $last_position = $siege['position'];
                                endforeach; 
                                ?>
                            </div>
                        <?php endforeach; ?>
                        
                        <!-- Légende -->
                        <div class="legende-sieges">
                            <div class="legende-item">
                                <div class="legende-couleur" style="background: var(--success);"></div>
                                <span>Disponible</span>
                            </div>
                            <div class="legende-item">
                                <div class="legende-couleur" style="background: var(--primary);"></div>
                                <span>Sélectionné</span>
                            </div>
                            <div class="legende-item">
                                <div class="legende-couleur" style="background: var(--danger);"></div>
                                <span>Occupé</span>
                            </div>
                            <div class="legende-item">
                                <div class="legende-couleur" style="background: linear-gradient(135deg, #fff9e6, #ffefb3); border-color: var(--premium);"></div>
                                <span>Première classe</span>
                            </div>
                            <div class="legende-item">
                                <div class="legende-couleur" style="background: linear-gradient(135deg, #f0e6ff, #d9c8ff); border-color: var(--business);"></div>
                                <span>Affaires</span>
                            </div>
                            <div class="legende-item">
                                <div class="legende-couleur" style="background: var(--white); border-color: var(--economy);"></div>
                                <span>Économique</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bouton de validation -->
                <button type="submit" name="selectionner_sieges" class="btn btn-success btn-full" id="submit-btn">
                    <i class="fas fa-arrow-right"></i> Continuer vers les bagages
                </button>
            </form>
        </div>
    </div>

    <script>
        // Stocker les suppléments des sièges
        const supplementsSieges = {};
        <?php foreach ($sieges_disponibles as $siege): ?>
        supplementsSieges[<?php echo $siege['id_siege']; ?>] = <?php echo $siege['supplement_prix']; ?>;
        <?php endforeach; ?>

        // Stocker les noms des sièges
        const nomsSieges = {};
        <?php foreach ($sieges_disponibles as $siege): ?>
        nomsSieges[<?php echo $siege['id_siege']; ?>] = '<?php echo $siege['position'] . $siege['rang']; ?>';
        <?php endforeach; ?>

        // Stocker les classes des sièges
        const classesSieges = {};
        <?php foreach ($sieges_disponibles as $siege): ?>
        classesSieges[<?php echo $siege['id_siege']; ?>] = '<?php echo $siege['classe']; ?>';
        <?php endforeach; ?>

        function afficherSiegesSelectionnes(siegesIds) {
            const container = document.getElementById('selected-seats-display');
            const list = document.getElementById('selected-seats-list');
            
            if (siegesIds.length === 0) {
                container.style.display = 'none';
                return;
            }
            
            list.innerHTML = '';
            siegesIds.forEach(siegeId => {
                const nomSiege = nomsSieges[siegeId];
                const supplement = supplementsSieges[siegeId] || 0;
                const classe = classesSieges[siegeId];
                
                let badgeClass = '';
                switch(classe) {
                    case 'première': badgeClass = 'badge-premium'; break;
                    case 'affaires': badgeClass = 'badge-business'; break;
                    default: badgeClass = 'badge-economy';
                }
                
                const badge = document.createElement('div');
                badge.className = 'item-badge';
                badge.innerHTML = `
                    <i class="fas fa-chair"></i> 
                    <strong>${nomSiege}</strong>
                    <span class="class-badge ${badgeClass}">${classe}</span>
                    ${supplement > 0 ? '<span style="color: var(--secondary); margin-left: 5px;">(+' + supplement + '€)</span>' : ''}
                `;
                list.appendChild(badge);
            });
            
            container.style.display = 'block';
        }

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
            
            // Mettre à jour l'affichage des sièges sélectionnés
            const siegesIds = Array.from(siegesSelectionnes).map(cb => parseInt(cb.value));
            afficherSiegesSelectionnes(siegesIds);
            
            // Mettre à jour la disponibilité des autres sièges
            document.querySelectorAll('input[name="sieges[]"]').forEach(cb => {
                if (!cb.checked && cb.disabled === false) {
                    cb.disabled = (siegesSelectionnes.length >= maxSieges);
                }
            });
        }

        // Gestion de la soumission du formulaire
        document.getElementById('siegesForm').addEventListener('submit', function(e) {
            const nbPassagers = parseInt(document.getElementById('nombre_passagers').value);
            const siegesSelectionnes = document.querySelectorAll('input[name="sieges[]"]:checked');
            
            if (siegesSelectionnes.length !== nbPassagers) {
                e.preventDefault();
                alert(`Vous devez sélectionner exactement ${nbPassagers} siège(s) pour ${nbPassagers} passager(s)`);
                return;
            }
        });

        // Initialisation
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
            
            // Mettre à jour la disponibilité
            document.querySelectorAll('input[name="sieges[]"]').forEach(checkbox => {
                if (!checkbox.checked && checkbox.disabled === false) {
                    checkbox.disabled = (document.querySelectorAll('input[name="sieges[]"]:checked').length >= maxSieges);
                }
            });
            
            // Mettre à jour l'affichage
            const siegesIds = Array.from(document.querySelectorAll('input[name="sieges[]"]:checked')).map(cb => parseInt(cb.value));
            afficherSiegesSelectionnes(siegesIds);
        });
    </script>
</body>
</html>