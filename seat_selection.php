<?php
require_once 'includes/db.php';
session_start();

// Récupérer l'ID du vol depuis l'URL
$vol_id = isset($_GET['vol_id']) ? intval($_GET['vol_id']) : 0;

if (!$vol_id) {
    header('Location: index.php');
    exit;
}

// Récupérer les informations du vol
$stmt = $pdo->prepare("SELECT v.*, c.nom_compagnie, a.modele as avion_modele, a.capacite, a.id_avion
                      FROM vols v 
                      JOIN compagnies c ON v.id_compagnie = c.id_compagnie 
                      JOIN avions a ON v.id_avion = a.id_avion 
                      WHERE v.id_vol = ?");
$stmt->execute([$vol_id]);
$vol = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$vol) {
    header('Location: index.php');
    exit;
}

// Récupérer les paramètres de réservation
$passagers = isset($_GET['passagers']) ? intval($_GET['passagers']) : 1;
$classe = $_GET['classe'] ?? 'économique';

// Récupérer tous les sièges de l'avion
$stmt = $pdo->prepare("SELECT * FROM sieges_avion WHERE id_avion = ? ORDER BY rangee, colonne");
$stmt->execute([$vol['id_avion']]);
$sieges = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les sièges déjà réservés pour ce vol
$stmt = $pdo->prepare("SELECT rs.siege_id 
                      FROM reservation_sieges rs 
                      JOIN reservations r ON rs.reservation_id = r.id 
                      WHERE r.id_vol = ? AND r.statut IN ('confirmée', 'en_attente')");
$stmt->execute([$vol_id]);
$sieges_reserves = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Organiser les sièges par rangée
$sieges_par_rangee = [];
foreach ($sieges as $siege) {
    $sieges_par_rangee[$siege['rangee']][] = $siege;
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
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #f8fafc;
            color: #334155;
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
        }

        .btn-primary {
            background: #2563eb;
            color: white;
        }

        .btn-primary:hover {
            background: #1d4ed8;
        }

        .flight-info {
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin: 20px 0;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }

        .seat-map-container {
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin: 20px 0;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }

        .cabin-section {
            margin-bottom: 30px;
        }

        .cabin-title {
            text-align: center;
            color: #2563eb;
            margin-bottom: 20px;
            font-size: 1.2rem;
            font-weight: 600;
        }

        .seat-row {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 15px;
            gap: 5px;
        }

        .row-number {
            width: 30px;
            text-align: center;
            font-weight: 600;
            color: #64748b;
        }

        .seat {
            position: relative;
            width: 40px;
            height: 40px;
            margin: 2px;
        }

        .seat input[type="checkbox"] {
            display: none;
        }

        .seat label {
            display: block;
            width: 100%;
            height: 100%;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid transparent;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            font-weight: 600;
            color: white;
        }

        .seat.available label {
            background: #10b981;
            border-color: #059669;
        }

        .seat.selected label {
            background: #2563eb;
            border-color: #1d4ed8;
            transform: scale(1.1);
        }

        .seat.reserved label {
            background: #ef4444;
            border-color: #dc2626;
            cursor: not-allowed;
        }

        .seat.premium label {
            background: #f59e0b;
            border-color: #d97706;
        }

        .seat.business label {
            background: #8b5cf6;
            border-color: #7c3aed;
        }

        .seat.emergency-exit label {
            background: #f97316;
            border-color: #ea580c;
        }

        .seat input[type="checkbox"]:disabled + label {
            cursor: not-allowed;
            opacity: 0.6;
        }

        .seat.available input[type="checkbox"]:checked + label {
            background: #2563eb;
            border-color: #1d4ed8;
        }

        .aisle {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: #64748b;
            font-size: 12px;
        }

        .seat-legend {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin: 30px 0;
            flex-wrap: wrap;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .legend-color {
            width: 20px;
            height: 20px;
            border-radius: 4px;
            border: 2px solid;
        }

        .legend-available { background: #10b981; border-color: #059669; }
        .legend-selected { background: #2563eb; border-color: #1d4ed8; }
        .legend-reserved { background: #ef4444; border-color: #dc2626; }
        .legend-premium { background: #f59e0b; border-color: #d97706; }
        .legend-business { background: #8b5cf6; border-color: #7c3aed; }

        .selection-summary {
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin: 20px 0;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            position: sticky;
            bottom: 20px;
            border: 2px solid #2563eb;
        }

        .selected-seats-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin: 15px 0;
        }

        .seat-badge {
            background: #2563eb;
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }

        .btn-outline {
            border: 2px solid #2563eb;
            color: #2563eb;
            background: transparent;
        }

        .btn-outline:hover {
            background: #2563eb;
            color: white;
        }

        .cabin-layout {
            position: relative;
            margin: 30px 0;
        }

        .cabin-divider {
            height: 2px;
            background: linear-gradient(90deg, transparent, #64748b, transparent);
            margin: 20px 0;
        }

        .wing-indicator {
            text-align: center;
            color: #64748b;
            font-style: italic;
            margin: 10px 0;
        }

        .seat-info {
            margin-top: 10px;
            font-size: 0.8rem;
            color: #64748b;
            text-align: center;
        }

        @media (max-width: 768px) {
            .seat {
                width: 35px;
                height: 35px;
            }
            
            .aisle {
                width: 35px;
                height: 35px;
            }
            
            .row-number {
                width: 25px;
                font-size: 0.8rem;
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
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <span>Bonjour, <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Utilisateur'); ?></span>
                    <?php else: ?>
                        <a href="vge64/connexion.php" class="btn btn-outline">Connexion</a>
                        <a href="vge64/inscription.php" class="btn btn-primary">Inscription</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <div class="container">
        <!-- Informations du vol -->
        <div class="flight-info">
            <h2>Sélection des sièges</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-top: 15px;">
                <div>
                    <strong><?php echo htmlspecialchars($vol['nom_compagnie']); ?></strong>
                    <div style="color: #64748b;">Vol <?php echo htmlspecialchars($vol['numero_vol']); ?></div>
                </div>
                <div>
                    <strong><?php echo htmlspecialchars($vol['depart']); ?> → <?php echo htmlspecialchars($vol['arrivee']); ?></strong>
                    <div style="color: #64748b;">
                        <?php echo date('d/m/Y H:i', strtotime($vol['date_depart'])); ?>
                    </div>
                </div>
                <div>
                    <strong>Classe: <?php echo htmlspecialchars($classe); ?></strong>
                    <div style="color: #64748b;">Passagers: <?php echo $passagers; ?></div>
                </div>
                <div>
                    <strong>Avion: <?php echo htmlspecialchars($vol['avion_modele']); ?></strong>
                    <div style="color: #64748b;">Capacité: <?php echo $vol['capacite']; ?> sièges</div>
                </div>
            </div>
        </div>

        <!-- Carte des sièges -->
        <div class="seat-map-container">
            <h3>Carte des sièges de l'avion</h3>
            
            <!-- Légende -->
            <div class="seat-legend">
                <div class="legend-item">
                    <div class="legend-color legend-available"></div>
                    <span>Disponible</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color legend-selected"></div>
                    <span>Sélectionné</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color legend-reserved"></div>
                    <span>Réservé</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color legend-premium"></div>
                    <span>Premium</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color legend-business"></div>
                    <span>Affaires</span>
                </div>
            </div>

            <div class="cabin-layout">
                <!-- Cabine Affaires (premières rangées) -->
                <?php if ($classe === 'affaires' || $classe === 'première'): ?>
                <div class="cabin-section">
                    <div class="cabin-title">Cabine Affaires</div>
                    <?php
                    $rangees_affaires = array_slice($sieges_par_rangee, 0, 6, true);
                    foreach ($rangees_affaires as $rangee => $sieges_rangee): 
                    ?>
                    <div class="seat-row">
                        <div class="row-number"><?php echo $rangee; ?></div>
                        <?php
                        // Configuration 2-2 pour la cabine affaires
                        $colonnes_affaires = ['A', 'B', 'E', 'F'];
                        foreach ($colonnes_affaires as $col_index => $colonne): 
                            $siege_trouve = null;
                            foreach ($sieges_rangee as $siege) {
                                if ($siege['colonne'] === $colonne) {
                                    $siege_trouve = $siege;
                                    break;
                                }
                            }
                            
                            if ($siege_trouve): 
                                $est_reserve = in_array($siege_trouve['id_siege'], $sieges_reserves);
                                $classe_siege = $est_reserve ? 'reserved' : 'available business';
                        ?>
                        <div class="seat <?php echo $classe_siege; ?>" data-siege-id="<?php echo $siege_trouve['id_siege']; ?>">
                            <input type="checkbox" id="siege_<?php echo $siege_trouve['id_siege']; ?>" 
                                   name="sieges[]" value="<?php echo $siege_trouve['id_siege']; ?>" 
                                   <?php echo $est_reserve ? 'disabled' : ''; ?>>
                            <label for="siege_<?php echo $siege_trouve['id_siege']; ?>">
                                <?php echo $siege_trouve['colonne'] . $rangee; ?>
                            </label>
                        </div>
                        <?php 
                            endif;
                            
                            // Ajouter l'allée après les 2 premiers sièges
                            if ($col_index === 1): 
                        ?>
                        <div class="aisle">AISLE</div>
                        <?php 
                            endif;
                        endforeach; 
                        ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="cabin-divider"></div>
                <?php endif; ?>

                <!-- Cabine Économique -->
                <div class="cabin-section">
                    <div class="cabin-title">Cabine Économique</div>
                    <?php
                    $rangees_economique = ($classe === 'affaires' || $classe === 'première') ? 
                                         array_slice($sieges_par_rangee, 6, null, true) : 
                                         $sieges_par_rangee;
                    
                    foreach ($rangees_economique as $rangee => $sieges_rangee): 
                    ?>
                    <div class="seat-row">
                        <div class="row-number"><?php echo $rangee; ?></div>
                        <?php
                        // Configuration 3-3 pour la cabine économique
                        $colonnes_economique = ['A', 'B', 'C', 'D', 'E', 'F'];
                        foreach ($colonnes_economique as $col_index => $colonne): 
                            $siege_trouve = null;
                            foreach ($sieges_rangee as $siege) {
                                if ($siege['colonne'] === $colonne) {
                                    $siege_trouve = $siege;
                                    break;
                                }
                            }
                            
                            if ($siege_trouve): 
                                $est_reserve = in_array($siege_trouve['id_siege'], $sieges_reserves);
                                $classe_siege = $est_reserve ? 'reserved' : 'available';
                                if ($siege_trouve['type'] === 'premium') $classe_siege .= ' premium';
                        ?>
                        <div class="seat <?php echo $classe_siege; ?>" data-siege-id="<?php echo $siege_trouve['id_siege']; ?>">
                            <input type="checkbox" id="siege_<?php echo $siege_trouve['id_siege']; ?>" 
                                   name="sieges[]" value="<?php echo $siege_trouve['id_siege']; ?>" 
                                   <?php echo $est_reserve ? 'disabled' : ''; ?>>
                            <label for="siege_<?php echo $siege_trouve['id_siege']; ?>">
                                <?php echo $siege_trouve['colonne'] . $rangee; ?>
                            </label>
                        </div>
                        <?php 
                            endif;
                            
                            // Ajouter l'allée après les 3 premiers sièges
                            if ($col_index === 2): 
                        ?>
                        <div class="aisle">AISLE</div>
                        <?php 
                            endif;
                        endforeach; 
                        ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Indicateur des ailes -->
            <div class="wing-indicator">
                <i class="fas fa-plane"></i> Zone des ailes (Rangées 10-20)
            </div>
            
            <div class="seat-info">
                Les sièges près des ailes offrent généralement plus de stabilité pendant le vol
            </div>
        </div>

        <!-- Résumé de la sélection -->
        <div class="selection-summary" id="selectionSummary" style="display: none;">
            <h3>Sièges sélectionnés</h3>
            <div class="selected-seats-list" id="selectedSeatsList">
                <!-- Les sièges sélectionnés apparaîtront ici -->
            </div>
            <div style="margin: 15px 0; padding: 10px; background: #f0f9ff; border-radius: 6px;">
                <strong>Sièges sélectionnés :</strong> <span id="selectedCount">0</span> sur <?php echo $passagers; ?>
            </div>
            <div class="action-buttons">
                <a href="reservation.php?vol_id=<?php echo $vol_id; ?>&passagers=<?php echo $passagers; ?>&classe=<?php echo $classe; ?>" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Retour
                </a>
                <button onclick="processSeatSelection()" class="btn btn-primary">
                    <i class="fas fa-credit-card"></i> Procéder au paiement
                </button>
            </div>
        </div>
    </div>

    <script>
        let selectedSeats = [];
        const maxSeats = <?php echo $passagers; ?>;

        function updateSelectionSummary() {
            const summary = document.getElementById('selectionSummary');
            const seatsList = document.getElementById('selectedSeatsList');
            const selectedCount = document.getElementById('selectedCount');
            
            seatsList.innerHTML = '';
            selectedSeats.forEach(seat => {
                const badge = document.createElement('div');
                badge.className = 'seat-badge';
                badge.innerHTML = `<i class="fas fa-chair"></i> ${seat}`;
                seatsList.appendChild(badge);
            });
            
            selectedCount.textContent = selectedSeats.length;
            
            if (selectedSeats.length > 0) {
                summary.style.display = 'block';
            } else {
                summary.style.display = 'none';
            }
        }

        function processSeatSelection() {
            if (selectedSeats.length === 0) {
                alert('Veuillez sélectionner au moins un siège.');
                return;
            }

            if (selectedSeats.length !== maxSeats) {
                alert(`Vous devez sélectionner exactement ${maxSeats} siège(s) pour ${maxSeats} passager(s).`);
                return;
            }

            // Créer un formulaire pour soumettre les données
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'reservation.php';

            const volInput = document.createElement('input');
            volInput.type = 'hidden';
            volInput.name = 'vol_id';
            volInput.value = '<?php echo $vol_id; ?>';
            form.appendChild(volInput);

            const passagersInput = document.createElement('input');
            passagersInput.type = 'hidden';
            passagersInput.name = 'passagers';
            passagersInput.value = '<?php echo $passagers; ?>';
            form.appendChild(passagersInput);

            const classeInput = document.createElement('input');
            classeInput.type = 'hidden';
            classeInput.name = 'classe';
            classeInput.value = '<?php echo $classe; ?>';
            form.appendChild(classeInput);

            selectedSeats.forEach(seatId => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'sieges[]';
                input.value = seatId;
                form.appendChild(input);
            });

            document.body.appendChild(form);
            form.submit();
        }

        // Gestion des sélections de sièges
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.seat input[type="checkbox"]').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const seatElement = this.closest('.seat');
                    const seatId = seatElement.dataset.siegeId;
                    const seatLabel = this.nextElementSibling.textContent;

                    if (this.checked) {
                        if (selectedSeats.length >= maxSeats) {
                            this.checked = false;
                            alert(`Vous ne pouvez sélectionner que ${maxSeats} siège(s) maximum.`);
                            return;
                        }
                        selectedSeats.push(seatId);
                        seatElement.classList.add('selected');
                    } else {
                        selectedSeats = selectedSeats.filter(id => id !== seatId);
                        seatElement.classList.remove('selected');
                    }
                    
                    updateSelectionSummary();
                });
            });
        });
    </script>
</body>
</html>