<?php
require_once 'includes/db.php';
session_start();

// Récupérer l'ID du vol depuis l'URL
$vol_id = isset($_GET['vol_id']) ? intval($_GET['vol_id']) : 0;

if (!$vol_id) {
    header('Location: vols.php');
    exit;
}

// Récupérer les informations du vol
$stmt = $pdo->prepare("SELECT v.*, c.nom_compagnie, c.code_compagnie, a.modele as avion_modele
                      FROM vols v 
                      JOIN compagnies c ON v.id_compagnie = c.id_compagnie 
                      JOIN avions a ON v.id_avion = a.id_avion
                      WHERE v.id_vol = ?");
$stmt->execute([$vol_id]);
$vol = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$vol) {
    header('Location: vols.php');
    exit;
}

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: connexion.php?redirect=seat_selection.php?vol_id=' . $vol_id);
    exit;
}

$user_id = $_SESSION['user_id'];

// Récupérer tous les sièges de l'avion
$stmt = $pdo->prepare("SELECT * FROM sieges_avion WHERE id_avion = ? ORDER BY rang, position");
$stmt->execute([$vol['id_avion']]);
$sieges = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les sièges déjà réservés pour ce vol
$stmt = $pdo->prepare("SELECT rs.id_siege 
                      FROM reservation_sieges rs 
                      JOIN reservations r ON rs.id_reservation = r.id_reservation 
                      WHERE r.id_vol = ? AND r.statut IN ('confirmé', 'en attente')");
$stmt->execute([$vol_id]);
$sieges_reserves = $stmt->fetchAll(PDO::FETCH_COLUMN);
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

        .header-top {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 1rem 0;
            margin-bottom: 2rem;
        }

        .header-content {
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

        .flight-info {
            text-align: center;
            flex: 1;
        }

        .flight-info h1 {
            font-size: 1.5rem;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }

        .flight-details {
            color: #64748b;
            font-size: 0.9rem;
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
        }

        .btn-primary:hover {
            background: #1d4ed8;
        }

        .cabin-section {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            margin-bottom: 2rem;
        }

        .cabin-title {
            text-align: center;
            margin-bottom: 2rem;
            color: #1e293b;
        }

        .fuselage {
            position: relative;
            width: 800px;
            background: linear-gradient(135deg, #e2e8f0, #cbd5e1);
            border-radius: 20px;
            padding: 40px 20px;
            margin: 0 auto;
        }

        .cockpit {
            position: absolute;
            top: -30px;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 60px;
            background: #374151;
            border-radius: 50% 50% 0 0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 12px;
        }

        .seats-container {
            display: grid;
            grid-template-columns: 1fr 60px 1fr;
            gap: 20px;
            margin-top: 30px;
        }

        .aisle {
            display: flex;
            align-items: center;
            justify-content: center;
            color: #64748b;
            font-size: 12px;
            writing-mode: vertical-lr;
            text-orientation: mixed;
        }

        .seat-row {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .row-number {
            text-align: center;
            font-weight: bold;
            color: #374151;
            margin-bottom: 5px;
        }

        .seats-group {
            display: flex;
            gap: 8px;
            justify-content: center;
        }

        .seat-item {
            position: relative;
        }

        .seat-checkbox {
            display: none;
        }

        .seat-label {
            width: 40px;
            height: 40px;
            background: #22c55e;
            border-radius: 8px 8px 4px 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: white;
            font-size: 10px;
            font-weight: bold;
            transition: all 0.3s ease;
            position: relative;
        }

        .seat-label:hover {
            transform: scale(1.1);
        }

        .seat-checkbox:checked + .seat-label {
            background: #ef4444;
        }

        .seat-checkbox:disabled + .seat-label {
            background: #94a3b8;
            cursor: not-allowed;
            transform: none;
        }

        .seat-number {
            position: absolute;
            bottom: -20px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 10px;
            color: #64748b;
        }

        .seat-legend {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin: 2rem 0;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .legend-seat {
            width: 20px;
            height: 20px;
            border-radius: 4px;
        }

        .available-seat {
            background: #22c55e;
        }

        .selected-seat {
            background: #ef4444;
        }

        .reserved-seat {
            background: #94a3b8;
        }

        .selected-seats-section {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            margin-top: 2rem;
        }

        .selected-seats-list {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin: 1rem 0;
        }

        .selected-seat-badge {
            background: #2563eb;
            color: white;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 14px;
        }

        .price-summary {
            text-align: center;
            margin-top: 1rem;
        }

        .total-price {
            font-size: 1.5rem;
            font-weight: bold;
            color: #1e293b;
        }
    </style>
</head>
<body>
    <div class="header-top">
        <div class="container">
            <div class="header-content">
                <a href="index.php" class="logo">Jet<span>Reserve</span></a>
                
                <div class="flight-info">
                    <h1><?php echo htmlspecialchars($vol['depart']); ?> → <?php echo htmlspecialchars($vol['arrivee']); ?></h1>
                    <div class="flight-details">
                        <?php echo htmlspecialchars($vol['nom_compagnie']); ?> • 
                        Vol <?php echo htmlspecialchars($vol['code_compagnie'] . $vol['numero_vol']); ?> • 
                        <?php echo date('d M Y, H:i', strtotime($vol['date_depart'])); ?> • 
                        Classe: <?php echo htmlspecialchars($vol['classe']); ?>
                    </div>
                </div>

                <a href="vols.php" class="btn-primary">
                    <i class="fas fa-arrow-left"></i> Retour
                </a>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Légende des sièges -->
        <div class="seat-legend">
            <div class="legend-item">
                <div class="legend-seat available-seat"></div>
                <span>Disponible</span>
            </div>
            <div class="legend-item">
                <div class="legend-seat selected-seat"></div>
                <span>Sélectionné</span>
            </div>
            <div class="legend-item">
                <div class="legend-seat reserved-seat"></div>
                <span>Réservé</span>
            </div>
        </div>

        <!-- Cabine de l'avion -->
        <div class="cabin-section">
            <h2 class="cabin-title">Cabine <?php echo ucfirst($vol['classe']); ?> - Sélectionnez vos sièges</h2>
            
            <div class="fuselage">
                <div class="cockpit">
                    COCKPIT
                </div>
                
                <div class="seats-container">
                    <!-- Côté gauche -->
                    <div class="seat-row left-side">
                        <?php
                        $sieges_gauche = array_filter($sieges, function($siege) {
                            return $siege['cote'] === 'gauche';
                        });
                        
                        $sieges_par_rang = [];
                        foreach ($sieges_gauche as $siege) {
                            $sieges_par_rang[$siege['rang']][] = $siege;
                        }
                        ksort($sieges_par_rang);
                        
                        foreach ($sieges_par_rang as $rang => $sieges_rang) {
                            echo "<div class='row-number'>Rang {$rang}</div>";
                            echo "<div class='seats-group'>";
                            foreach ($sieges_rang as $siege) {
                                $est_reserve = in_array($siege['id_siege'], $sieges_reserves);
                                $disabled = $est_reserve ? 'disabled' : '';
                                
                                echo "<div class='seat-item'>";
                                echo "<input type='checkbox' class='seat-checkbox' id='siege_{$siege['id_siege']}' 
                                      name='sieges[]' value='{$siege['id_siege']}' {$disabled} 
                                      data-rang='{$siege['rang']}' data-position='{$siege['position']}'>";
                                echo "<label for='siege_{$siege['id_siege']}' class='seat-label'>";
                                echo "{$siege['position']}";
                                echo "</label>";
                                echo "<div class='seat-number'>Siège {$siege['rang']}{$siege['position']}</div>";
                                echo "</div>";
                            }
                            echo "</div>";
                        }
                        ?>
                    </div>

                    <!-- Allée -->
                    <div class="aisle">
                        ALLÉE
                    </div>

                    <!-- Côté droit -->
                    <div class="seat-row right-side">
                        <?php
                        $sieges_droit = array_filter($sieges, function($siege) {
                            return $siege['cote'] === 'droit';
                        });
                        
                        $sieges_par_rang = [];
                        foreach ($sieges_droit as $siege) {
                            $sieges_par_rang[$siege['rang']][] = $siege;
                        }
                        ksort($sieges_par_rang);
                        
                        foreach ($sieges_par_rang as $rang => $sieges_rang) {
                            echo "<div class='row-number'>Rang {$rang}</div>";
                            echo "<div class='seats-group'>";
                            foreach ($sieges_rang as $siege) {
                                $est_reserve = in_array($siege['id_siege'], $sieges_reserves);
                                $disabled = $est_reserve ? 'disabled' : '';
                                
                                echo "<div class='seat-item'>";
                                echo "<input type='checkbox' class='seat-checkbox' id='siege_{$siege['id_siege']}' 
                                      name='sieges[]' value='{$siege['id_siege']}' {$disabled} 
                                      data-rang='{$siege['rang']}' data-position='{$siege['position']}'>";
                                echo "<label for='siege_{$siege['id_siege']}' class='seat-label'>";
                                echo "{$siege['position']}";
                                echo "</label>";
                                echo "<div class='seat-number'>Siège {$siege['rang']}{$siege['position']}</div>";
                                echo "</div>";
                            }
                            echo "</div>";
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sièges sélectionnés et prix -->
        <div class="selected-seats-section">
            <h3>Vos sièges sélectionnés</h3>
            <div class="selected-seats-list" id="selectedSeatsList">
                <div style="color: #64748b;">Aucun siège sélectionné</div>
            </div>
            
            <div class="price-summary">
                <div>Prix par siège: <strong><?php echo $vol['prix']; ?>€</strong></div>
                <div class="total-price" id="totalPrice">Total: 0€</div>
                
                <button class="btn-primary" style="margin-top: 1rem; padding: 15px 30px;" 
                        onclick="processSeatSelection()" id="continueBtn" disabled>
                    <i class="fas fa-credit-card"></i> Continuer vers le paiement
                </button>
            </div>
        </div>
    </div>

    <script>
        let prixParSiege = <?php echo $vol['prix']; ?>;
        let selectedSeats = [];

        function updateSelection() {
            const selectedSeatsList = document.getElementById('selectedSeatsList');
            const totalPriceElement = document.getElementById('totalPrice');
            const continueBtn = document.getElementById('continueBtn');
            
            if (selectedSeats.length > 0) {
                selectedSeatsList.innerHTML = selectedSeats.map(seat => 
                    `<div class="selected-seat-badge">Rang ${seat.rang}${seat.position}</div>`
                ).join('');
                
                const total = selectedSeats.length * prixParSiege;
                totalPriceElement.textContent = `Total: ${total}€`;
                continueBtn.disabled = false;
            } else {
                selectedSeatsList.innerHTML = '<div style="color: #64748b;">Aucun siège sélectionné</div>';
                totalPriceElement.textContent = 'Total: 0€';
                continueBtn.disabled = true;
            }
        }

        document.querySelectorAll('.seat-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const siegeId = this.value;
                const rang = this.dataset.rang;
                const position = this.dataset.position;
                
                if (this.checked) {
                    selectedSeats.push({ id: siegeId, rang: rang, position: position });
                } else {
                    selectedSeats = selectedSeats.filter(seat => seat.id !== siegeId);
                }
                
                updateSelection();
            });
        });

        function processSeatSelection() {
            if (selectedSeats.length === 0) {
                alert('Veuillez sélectionner au moins un siège.');
                return;
            }

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'paiement.php';

            const volInput = document.createElement('input');
            volInput.type = 'hidden';
            volInput.name = 'vol_id';
            volInput.value = '<?php echo $vol_id; ?>';
            form.appendChild(volInput);

            selectedSeats.forEach(function(seat) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'sieges[]';
                input.value = seat.id;
                form.appendChild(input);
            });

            document.body.appendChild(form);
            form.submit();
        }
    </script>
</body>
</html>