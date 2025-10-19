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

// Récupérer les sièges disponibles pour ce vol
try {
    $stmt = $pdo->prepare("
        SELECT sa.* 
        FROM sieges_avion sa
        WHERE sa.id_avion = ? 
        AND sa.statut = 'actif'
        AND sa.id_siege NOT IN (
            SELECT rs.id_siege 
            FROM reservation_sieges rs
            JOIN reservations r ON rs.id_reservation = r.id_reservation
            WHERE r.id_vol = ? AND r.statut IN ('confirmé', 'en attente')
        )
        ORDER BY sa.classe, sa.rang, sa.position
    ");
    $stmt->execute([$vol['id_avion'], $id_vol]);
    $sieges_disponibles = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $sieges_disponibles = [];
}

// Traitement du formulaire de réservation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reserver'])) {
    $nombre_passagers = intval($_POST['nombre_passagers']);
    $sieges_selectionnes = isset($_POST['sieges']) ? $_POST['sieges'] : [];
    
    // Validation
    if ($nombre_passagers <= 0 || $nombre_passagers > 10) {
        $erreur = "Nombre de passagers invalide";
    } elseif (count($sieges_selectionnes) !== $nombre_passagers) {
        $erreur = "Vous devez sélectionner exactement " . $nombre_passagers . " siège(s)";
    } else {
        try {
            // Commencer une transaction
            $pdo->beginTransaction();
            
            // Calculer le prix total
            $prix_total = $vol['prix'] * $nombre_passagers;
            
            // Ajouter les suppléments des sièges
            foreach ($sieges_selectionnes as $id_siege) {
                $stmt = $pdo->prepare("SELECT supplement_prix FROM sieges_avion WHERE id_siege = ?");
                $stmt->execute([$id_siege]);
                $siege = $stmt->fetch(PDO::FETCH_ASSOC);
                $prix_total += $siege['supplement_prix'];
            }
            
            // Créer la réservation
            $stmt = $pdo->prepare("
                INSERT INTO reservations (id_user, id_vol, statut, nombre_passagers, prix_total)
                VALUES (?, ?, 'en attente', ?, ?)
            ");
            $stmt->execute([$_SESSION['id_user'], $id_vol, $nombre_passagers, $prix_total]);
            $id_reservation = $pdo->lastInsertId();
            
            // Associer les sièges à la réservation
            foreach ($sieges_selectionnes as $id_siege) {
                $stmt = $pdo->prepare("
                    INSERT INTO reservation_sieges (id_reservation, id_siege, prix_paye)
                    VALUES (?, ?, ?)
                ");
                $stmt_siege = $pdo->prepare("SELECT supplement_prix FROM sieges_avion WHERE id_siege = ?");
                $stmt_siege->execute([$id_siege]);
                $siege = $stmt_siege->fetch(PDO::FETCH_ASSOC);
                
                $prix_siege = $vol['prix'] + $siege['supplement_prix'];
                $stmt->execute([$id_reservation, $id_siege, $prix_siege]);
            }
            
            // Mettre à jour les places disponibles
            $stmt = $pdo->prepare("
                UPDATE vols 
                SET places_disponibles = places_disponibles - ? 
                WHERE id_vol = ?
            ");
            $stmt->execute([$nombre_passagers, $id_vol]);
            
            // Confirmer la transaction
            $pdo->commit();
            
            // Rediriger vers la page de confirmation
            header('Location: confirmation_reservation.php?id_reservation=' . $id_reservation);
            exit;
            
        } catch (PDOException $e) {
            $pdo->rollBack();
            $erreur = "Erreur lors de la réservation: " . $e->getMessage();
        }
    }
}
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
        
        /* Container principal */
        .reservation-container {
            max-width: 1000px;
            margin: 2rem auto;
            background: var(--white);
            border-radius: 8px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }
        
        /* En-tête */
        .reservation-header {
            background: linear-gradient(135deg, var(--primary), #34495e);
            color: var(--white);
            padding: 2rem;
        }
        
        .reservation-title {
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        /* Résumé du vol */
        .vol-resume {
            padding: 2rem;
            border-bottom: 1px solid var(--border-color);
        }
        
        .vol-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .info-card {
            background: var(--light);
            padding: 1rem;
            border-radius: 6px;
            border-left: 4px solid var(--primary);
        }
        
        .info-label {
            font-weight: 600;
            color: var(--secondary);
            font-size: 0.9rem;
        }
        
        .info-value {
            font-size: 1.1rem;
            color: var(--dark);
            font-weight: 500;
        }
        
        /* Formulaire de réservation */
        .reservation-form {
            padding: 2rem;
        }
        
        .form-section {
            margin-bottom: 2rem;
        }
        
        .section-title {
            color: var(--primary);
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
            font-weight: 500;
            color: var(--dark);
        }
        
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            font-family: 'Poppins', sans-serif;
        }
        
        /* Sélection des sièges */
        .sieges-container {
            margin-top: 1rem;
        }
        
        .avion-schema {
            background: var(--light);
            padding: 2rem;
            border-radius: 8px;
            margin-bottom: 1rem;
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
            font-weight: 600;
            color: var(--secondary);
        }
        
        .siege {
            width: 40px;
            height: 40px;
            border: 2px solid var(--border-color);
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .siege.disponible {
            background: var(--white);
            border-color: var(--success);
        }
        
        .siege.disponible:hover {
            background: var(--success);
            color: var(--white);
        }
        
        .siege.selectionne {
            background: var(--primary);
            color: var(--white);
            border-color: var(--primary);
        }
        
        .siege.occupe {
            background: var(--danger);
            color: var(--white);
            border-color: var(--danger);
            cursor: not-allowed;
        }
        
        .legende-sieges {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin-top: 1rem;
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
        
        /* Résumé du prix */
        .resume-prix {
            background: var(--light);
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        
        .ligne-prix {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }
        
        .prix-total {
            border-top: 2px solid var(--border-color);
            padding-top: 0.5rem;
            margin-top: 0.5rem;
            font-weight: 600;
            font-size: 1.2rem;
            color: var(--primary);
        }
        
        /* Message d'erreur */
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
        
        /* Responsive */
        @media (max-width: 768px) {
            .vol-info-grid {
                grid-template-columns: 1fr;
            }
            
            .rangee-sieges {
                flex-wrap: wrap;
            }
            
            .legende-sieges {
                flex-direction: column;
                gap: 1rem;
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
                    <a href="vge64/index2.php" class="btn btn-outline">Mon compte</a>
                    <a href="vge64/deconnexion.php" class="btn btn-primary">Déconnexion</a>
                </div>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="reservation-container">
            <!-- En-tête -->
            <div class="reservation-header">
                <h1 class="reservation-title">Réservation de vol</h1>
                <p>Finalisez votre réservation en choisissant vos sièges</p>
            </div>
            
            <!-- Résumé du vol -->
            <div class="vol-resume">
                <h2 class="section-title">Résumé du vol</h2>
                <div class="vol-info-grid">
                    <div class="info-card">
                        <div class="info-label">Itinéraire</div>
                        <div class="info-value"><?php echo htmlspecialchars($vol['depart']); ?> → <?php echo htmlspecialchars($vol['arrivee']); ?></div>
                    </div>
                    <div class="info-card">
                        <div class="info-label">Date et heure</div>
                        <div class="info-value"><?php echo date('d/m/Y H:i', strtotime($vol['date_depart'])); ?></div>
                    </div>
                    <div class="info-card">
                        <div class="info-label">Compagnie</div>
                        <div class="info-value"><?php echo htmlspecialchars($vol['nom_compagnie']); ?></div>
                    </div>
                    <div class="info-card">
                        <div class="info-label">Classe</div>
                        <div class="info-value"><?php echo htmlspecialchars($vol['classe']); ?></div>
                    </div>
                </div>
            </div>
            
            <!-- Formulaire de réservation -->
            <form method="POST" class="reservation-form">
                <?php if (isset($erreur)): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i> <?php echo $erreur; ?>
                    </div>
                <?php endif; ?>
                
                <!-- Sélection du nombre de passagers -->
                <div class="form-section">
                    <h3 class="section-title">Nombre de passagers</h3>
                    <div class="form-group">
                        <label for="nombre_passagers">Nombre de passagers *</label>
                        <select name="nombre_passagers" id="nombre_passagers" class="form-control" required>
                            <?php for ($i = 1; $i <= min(10, $vol['places_disponibles']); $i++): ?>
                                <option value="<?php echo $i; ?>" <?php echo isset($_POST['nombre_passagers']) && $_POST['nombre_passagers'] == $i ? 'selected' : ''; ?>>
                                    <?php echo $i; ?> passager(s)
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
                
                <!-- Sélection des sièges -->
                <div class="form-section">
                    <h3 class="section-title">Choix des sièges</h3>
                    <div class="sieges-container">
                        <div class="avion-schema">
                            <?php
                            // Organiser les sièges par rangée
                            $sieges_par_rang = [];
                            foreach ($sieges_disponibles as $siege) {
                                $sieges_par_rang[$siege['rang']][] = $siege;
                            }
                            ksort($sieges_par_rang);
                            
                            foreach ($sieges_par_rang as $rang => $sieges_rang):
                            ?>
                                <div class="rangee-sieges">
                                    <div class="numero-rangee"><?php echo $rang; ?></div>
                                    <?php foreach ($sieges_rang as $siege): ?>
                                        <label class="siege disponible" title="Siège <?php echo $siege['position'].$rang; ?> - Supplément: <?php echo $siege['supplement_prix']; ?>€">
                                            <input type="checkbox" name="sieges[]" value="<?php echo $siege['id_siege']; ?>" 
                                                   style="display: none;" 
                                                   onchange="this.parentElement.classList.toggle('selectionne')">
                                            <?php echo $siege['position']; ?>
                                        </label>
                                        <?php if ($siege['position'] == 'C'): ?>
                                            <div style="width: 20px;"></div> <!-- Couloir -->
                                        <?php endif; ?>
                                    <?php endforeach; ?>
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
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Résumé du prix -->
                <div class="form-section">
                    <h3 class="section-title">Résumé du prix</h3>
                    <div class="resume-prix">
                        <div class="ligne-prix">
                            <span>Prix de base par passager:</span>
                            <span><?php echo number_format($vol['prix'], 2, ',', ' '); ?>€</span>
                        </div>
                        <div class="ligne-prix">
                            <span>Suppléments sièges:</span>
                            <span id="supplements-total">0,00€</span>
                        </div>
                        <div class="ligne-prix prix-total">
                            <span>Total:</span>
                            <span id="prix-total"><?php echo number_format($vol['prix'], 2, ',', ' '); ?>€</span>
                        </div>
                    </div>
                </div>
                
                <!-- Bouton de réservation -->
                <div class="form-section">
                    <button type="submit" name="reserver" class="btn btn-primary btn-lg" style="width: 100%;">
                        <i class="fas fa-credit-card"></i> Confirmer la réservation
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Calcul dynamique du prix
        document.getElementById('nombre_passagers').addEventListener('change', calculerPrix);
        document.querySelectorAll('input[name="sieges[]"]').forEach(checkbox => {
            checkbox.addEventListener('change', calculerPrix);
        });
        
        function calculerPrix() {
            const prixBase = <?php echo $vol['prix']; ?>;
            const nbPassagers = parseInt(document.getElementById('nombre_passagers').value);
            let supplements = 0;
            
            // Calculer les suppléments des sièges sélectionnés
            document.querySelectorAll('input[name="sieges[]"]:checked').forEach(checkbox => {
                // Dans une vraie application, vous récupéreriez le supplément depuis une data-attribute
                // Pour l'instant, on simule avec une valeur fixe
                supplements += 0; // À remplacer par la vraie valeur
            });
            
            const total = (prixBase * nbPassagers) + supplements;
            
            document.getElementById('supplements-total').textContent = supplements.toFixed(2).replace('.', ',') + '€';
            document.getElementById('prix-total').textContent = total.toFixed(2).replace('.', ',') + '€';
        }
        
        // Initialiser le calcul du prix
        calculerPrix();
    </script>
</body>
</html>