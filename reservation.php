
<?php
session_start();
require_once 'includes/db.php';

// Vérifier si l'ID du vol est présent
if (!isset($_GET['vol_id'])) {
    header('Location: index.php');
    exit;
}

$vol_id = intval($_GET['vol_id']);

// Récupérer les paramètres optionnels
$passagers = isset($_GET['passagers']) ? max(1, intval($_GET['passagers'])) : 1;
$classe = $_GET['classe'] ?? 'économique';
$sieges = $_GET['sieges'] ?? [];

try {
    // Récupérer les données du vol depuis la base de données
    $sql = "SELECT v.*, c.nom_compagnie, c.code_compagnie, a.modele as avion_modele, a.id_avion
            FROM vols v 
            JOIN compagnies c ON v.id_compagnie = c.id_compagnie 
            JOIN avions a ON v.id_avion = a.id_avion
            WHERE v.id_vol = ? AND v.places_disponibles >= ? AND v.date_depart > NOW()";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$vol_id, $passagers]);
    $vol = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$vol) {
        header('Location: index.php?error=vol_non_trouve');
        exit;
    }
    
} catch (PDOException $e) {
    error_log("Erreur récupération vol: " . $e->getMessage());
    header('Location: index.php?error=erreur_base_donnees');
    exit;
}

// Calculer le prix total avec suppléments des sièges
$price_per_passenger = floatval($vol['prix']);
$supplements = 0;

// Récupérer les suppléments pour chaque siège sélectionné
if (!empty($sieges)) {
    $placeholders = str_repeat('?,', count($sieges) - 1) . '?';
    $sql_sieges = "SELECT SUM(supplement_prix) as total_supplement FROM sieges_avion WHERE id_siege IN ($placeholders)";
    $stmt_sieges = $pdo->prepare($sql_sieges);
    $stmt_sieges->execute($sieges);
    $result_sieges = $stmt_sieges->fetch(PDO::FETCH_ASSOC);
    $supplements = $result_sieges['total_supplement'] ?? 0;
}

$total_price = ($passagers * $price_per_passenger) + $supplements;

// Préparer les données pour l'affichage
$vol_data = [
    'id_vol' => $vol['id_vol'],
    'airline' => $vol['nom_compagnie'],
    'flight_number' => $vol['code_compagnie'] . $vol['numero_vol'],
    'from' => $vol['depart'],
    'to' => $vol['arrivee'],
    'departure_date' => $vol['date_depart'],
    'arrival_date' => $vol['date_arrivee'],
    'class' => $classe,
    'price' => $vol['prix'],
    'aircraft' => $vol['avion_modele'],
    'escales' => $vol['escales']
];
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

        .reservation-container {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 30px;
            margin: 30px auto;
        }

        @media (max-width: 968px) {
            .reservation-container {
                grid-template-columns: 1fr;
            }
        }

        .flight-summary {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }

        .booking-form {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }

        .price-summary {
            position: sticky;
            top: 100px;
            height: fit-content;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #374151;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s ease;
            font-family: 'Poppins', sans-serif;
        }

        .form-control:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .btn-submit {
            background: #2563eb;
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            transition: background 0.3s ease;
            font-family: 'Poppins', sans-serif;
        }

        .btn-submit:hover {
            background: #1d4ed8;
        }

        .price-breakdown {
            background: #f8fafc;
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
        }

        .price-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e2e8f0;
        }

        .price-total {
            display: flex;
            justify-content: space-between;
            font-weight: bold;
            font-size: 18px;
            color: #1e293b;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 2px solid #e2e8f0;
        }

        .flight-details {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .flight-route {
            text-align: center;
            flex: 1;
        }

        .flight-time {
            font-size: 24px;
            font-weight: bold;
            color: #1e293b;
        }

        .flight-city {
            color: #64748b;
            font-size: 14px;
        }

        .flight-duration {
            text-align: center;
            color: #64748b;
            margin: 0 20px;
            position: relative;
        }

        .flight-duration::before {
            content: "";
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 2px;
            background: #e2e8f0;
            z-index: 1;
        }

        .flight-duration i {
            background: white;
            padding: 0 10px;
            position: relative;
            z-index: 2;
        }

        .flight-airline {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #e2e8f0;
        }

        .passenger-section {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e2e8f0;
        }

        .passenger-section:last-child {
            border-bottom: none;
        }

        .section-title {
            margin-bottom: 20px;
            color: #1e293b;
            font-size: 1.25rem;
        }

        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            background: #fef3cd;
            border: 1px solid #fde68a;
            color: #92400e;
        }

        .success {
            background: #d1fae5;
            border: 1px solid #a7f3d0;
            color: #065f46;
        }

        .error {
            background: #fee2e2;
            border: 1px solid #fecaca;
            color: #dc2626;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }

        .progress-bar {
            display: flex;
            justify-content: space-between;
            margin: 30px 0;
            position: relative;
        }

        .progress-step {
            text-align: center;
            flex: 1;
            position: relative;
            z-index: 2;
        }

        .progress-step .step-number {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e2e8f0;
            color: #64748b;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            font-weight: bold;
        }

        .progress-step.active .step-number {
            background: #2563eb;
            color: white;
        }

        .progress-step.completed .step-number {
            background: #10b981;
            color: white;
        }

        .progress-bar::before {
            content: "";
            position: absolute;
            top: 20px;
            left: 0;
            right: 0;
            height: 2px;
            background: #e2e8f0;
            z-index: 1;
        }

        .selected-seats {
            background: #f0f9ff;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            border-left: 4px solid #2563eb;
        }

        .seat-badge {
            background: #2563eb;
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            margin: 5px;
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
                        <span style="margin-right: 15px; color: #64748b;">Bonjour, <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Utilisateur'); ?></span>
                        <a href="vge64/index2.php" class="btn btn-outline">Mon compte</a>
                        <a href="vge64/logout.php" class="btn btn-primary">Déconnexion</a>
                    <?php else: ?>
                        <a href="vge64/connexion.php" class="btn btn-outline">Connexion</a>
                        <a href="vge64/inscription.php" class="btn btn-primary">Inscription</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <div class="container">
        <!-- Barre de progression -->
        <div class="progress-bar">
            <div class="progress-step completed">
                <div class="step-number">1</div>
                <div>Recherche</div>
            </div>
            <div class="progress-step completed">
                <div class="step-number">2</div>
                <div>Sélection</div>
            </div>
            <div class="progress-step active">
                <div class="step-number">3</div>
                <div>Réservation</div>
            </div>
            <div class="progress-step">
                <div class="step-number">4</div>
                <div>Paiement</div>
            </div>
            <div class="progress-step">
                <div class="step-number">5</div>
                <div>Confirmation</div>
            </div>
        </div>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert error">
                <i class="fas fa-exclamation-circle"></i>
                <?php
                $errors = [
                    'vol_non_trouve' => 'Le vol sélectionné n\'existe plus.',
                    'plus_de_places' => 'Plus de places disponibles pour ce vol.',
                    'erreur_reservation' => 'Une erreur est survenue lors de la réservation.'
                ];
                echo $errors[$_GET['error']] ?? 'Une erreur est survenue.';
                ?>
            </div>
        <?php endif; ?>

        <?php if (!isset($_SESSION['id_user'])): ?>
        <div class="alert">
            <i class="fas fa-exclamation-circle"></i>
            Vous devez être connecté pour effectuer une réservation. 
            <a href="vge64/connexion.php" style="color: #2563eb; text-decoration: underline;">Connectez-vous</a> 
            ou <a href="vge64/inscription.php" style="color: #2563eb; text-decoration: underline;">inscrivez-vous</a>.
        </div>
        <?php endif; ?>

        <div class="reservation-container">
            <!-- Formulaire de réservation -->
            <div class="booking-section">
                <div class="flight-summary">
                    <h2 class="section-title">Détails du vol</h2>
                    
                    <!-- Affichage des sièges sélectionnés -->
                    <?php if (!empty($sieges)): ?>
                    <div class="selected-seats">
                        <h4 style="color: #2563eb; margin-bottom: 10px;">Sièges sélectionnés</h4>
                        <?php
                        // Récupérer les noms des sièges
                        if (!empty($sieges)) {
                            $placeholders = str_repeat('?,', count($sieges) - 1) . '?';
                            $sql_sieges_info = "SELECT id_siege, rang, position FROM sieges_avion WHERE id_siege IN ($placeholders)";
                            $stmt_sieges_info = $pdo->prepare($sql_sieges_info);
                            $stmt_sieges_info->execute($sieges);
                            $sieges_info = $stmt_sieges_info->fetchAll(PDO::FETCH_ASSOC);
                            
                            foreach ($sieges_info as $siege): 
                        ?>
                            <div class="seat-badge">
                                <i class="fas fa-chair"></i> <?php echo $siege['position'] . $siege['rang']; ?>
                            </div>
                        <?php endforeach; } ?>
                    </div>
                    <?php endif; ?>
                    
                    <div class="flight-details">
                        <div class="flight-route">
                            <div class="flight-time"><?php echo date('H:i', strtotime($vol_data['departure_date'])); ?></div>
                            <div class="flight-city"><?php echo htmlspecialchars($vol_data['from']); ?></div>
                            <div style="color: #94a3b8; font-size: 0.8rem; margin-top: 5px;">
                                <?php echo date('d/m/Y', strtotime($vol_data['departure_date'])); ?>
                            </div>
                        </div>
                        <div class="flight-duration">
                            <i class="fas fa-plane" style="color: #2563eb;"></i>
                            <div style="margin-top: 5px; font-size: 12px;">
                                <?php 
                                $departure = new DateTime($vol_data['departure_date']);
                                $arrival = new DateTime($vol_data['arrival_date']);
                                $duration = $departure->diff($arrival);
                                echo $duration->format('%hh%im');
                                ?>
                            </div>
                            <div style="font-size: 11px; color: #64748b; margin-top: 2px;">
                                <?php echo $vol_data['escales'] == 0 ? 'Direct' : $vol_data['escales'] . ' escale(s)'; ?>
                            </div>
                        </div>
                        <div class="flight-route">
                            <div class="flight-time"><?php echo date('H:i', strtotime($vol_data['arrival_date'])); ?></div>
                            <div class="flight-city"><?php echo htmlspecialchars($vol_data['to']); ?></div>
                            <div style="color: #94a3b8; font-size: 0.8rem; margin-top: 5px;">
                                <?php echo date('d/m/Y', strtotime($vol_data['arrival_date'])); ?>
                            </div>
                        </div>
                    </div>
                    <div class="flight-airline">
                        <i class="fas fa-plane" style="color: #2563eb;"></i>
                        <div>
                            <strong><?php echo htmlspecialchars($vol_data['airline']); ?></strong>
                            <div style="color: #64748b; font-size: 14px;">
                                Vol <?php echo htmlspecialchars($vol_data['flight_number']); ?> • 
                                <?php echo htmlspecialchars($vol_data['class']); ?> • 
                                <?php echo htmlspecialchars($vol_data['aircraft']); ?>
                            </div>
                        </div>
                    </div>
                </div>

                <form action="process_reservation.php" method="POST" class="booking-form" id="reservationForm">
                    <h3 class="section-title">Informations personnelles</h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="civilite">Civilité *</label>
                            <select id="civilite" name="civilite" class="form-control" required <?php echo !isset($_SESSION['user_id']) ? 'disabled' : ''; ?>>
                                <option value="">Sélectionnez</option>
                                <option value="M">Monsieur</option>
                                <option value="Mme">Madame</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="phone">Téléphone *</label>
                            <input type="tel" id="phone" name="phone" class="form-control" required <?php echo !isset($_SESSION['user_id']) ? 'disabled' : ''; ?>>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="prenom">Prénom *</label>
                            <input type="text" id="prenom" name="prenom" class="form-control" required <?php echo !isset($_SESSION['user_id']) ? 'disabled' : ''; ?>>
                        </div>
                        <div class="form-group">
                            <label for="nom">Nom *</label>
                            <input type="text" id="nom" name="nom" class="form-control" required <?php echo !isset($_SESSION['user_id']) ? 'disabled' : ''; ?>>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" class="form-control" required <?php echo !isset($_SESSION['user_id']) ? 'disabled' : ''; ?>>
                    </div>

                    <h3 class="section-title">Informations des passagers</h3>
                    
                    <?php for($i = 1; $i <= $passagers; $i++): ?>
                    <div class="passenger-section">
                        <h4>Passager <?php echo $i; ?></h4>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="passenger<?php echo $i; ?>_civilite">Civilité *</label>
                                <select id="passenger<?php echo $i; ?>_civilite" name="passengers[<?php echo $i; ?>][civilite]" class="form-control" required <?php echo !isset($_SESSION['user_id']) ? 'disabled' : ''; ?>>
                                    <option value="">Sélectionnez</option>
                                    <option value="M">Monsieur</option>
                                    <option value="Mme">Madame</option>
                                    <option value="Enfant">Enfant</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="passenger<?php echo $i; ?>_naissance">Date de naissance *</label>
                                <input type="date" id="passenger<?php echo $i; ?>_naissance" name="passengers[<?php echo $i; ?>][naissance]" class="form-control" required <?php echo !isset($_SESSION['user_id']) ? 'disabled' : ''; ?>>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="passenger<?php echo $i; ?>_prenom">Prénom *</label>
                                <input type="text" id="passenger<?php echo $i; ?>_prenom" name="passengers[<?php echo $i; ?>][prenom]" class="form-control" required <?php echo !isset($_SESSION['user_id']) ? 'disabled' : ''; ?>>
                            </div>
                            <div class="form-group">
                                <label for="passenger<?php echo $i; ?>_nom">Nom *</label>
                                <input type="text" id="passenger<?php echo $i; ?>_nom" name="passengers[<?php echo $i; ?>][nom]" class="form-control" required <?php echo !isset($_SESSION['user_id']) ? 'disabled' : ''; ?>>
                            </div>
                        </div>
                    </div>
                    <?php endfor; ?>

                    <!-- Champs cachés avec les données du vol -->
                    <input type="hidden" name="vol_id" value="<?php echo $vol_data['id_vol']; ?>">
                    <input type="hidden" name="passagers" value="<?php echo $passagers; ?>">
                    <input type="hidden" name="classe" value="<?php echo htmlspecialchars($classe); ?>">
                    <input type="hidden" name="total_price" value="<?php echo $total_price; ?>">
                    <?php foreach ($sieges as $siege): ?>
                        <input type="hidden" name="sieges[]" value="<?php echo $siege; ?>">
                    <?php endforeach; ?>

                    <div class="form-group">
                        <label style="display: flex; align-items: start; gap: 10px; font-weight: normal; cursor: pointer;">
                            <input type="checkbox" name="conditions" required style="margin-top: 3px;" <?php echo !isset($_SESSION['user_id']) ? 'disabled' : ''; ?>>
                            <span>J'accepte les <a href="#" style="color: #2563eb;">conditions générales</a> et la <a href="#" style="color: #2563eb;">politique de confidentialité</a> *</span>
                        </label>
                    </div>

                    <?php if (isset($_SESSION['user_id'])): ?>
                        <button type="submit" class="btn-submit">
                            <i class="fas fa-arrow-right"></i> Continuer vers le paiement
                        </button>
                    <?php else: ?>
                        <a href="vge64/connexion.php" class="btn-submit" style="text-align: center; text-decoration: none; display: block;">
                            <i class="fas fa-sign-in-alt"></i> Se connecter pour réserver
                        </a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Récapitulatif du prix -->
            <div class="price-summary">
                <div class="booking-form">
                    <h3 class="section-title">Récapitulatif</h3>
                    <div class="price-breakdown">
                        <div class="price-item">
                            <span>Passager x<?php echo $passagers; ?> (<?php echo htmlspecialchars($classe); ?>)</span>
                            <span><?php echo number_format($price_per_passenger * $passagers, 2, ',', ' '); ?>€</span>
                        </div>
                        <?php if ($supplements > 0): ?>
                        <div class="price-item">
                            <span>Suppléments sièges</span>
                            <span><?php echo number_format($supplements, 2, ',', ' '); ?>€</span>
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
                        <div class="price-total">
                            <span>Total</span>
                            <span><?php echo number_format($total_price + 9 + 25, 2, ',', ' '); ?>€</span>
                        </div>
                    </div>
                    
                    <div style="margin-top: 20px; padding: 15px; background: #f0f9ff; border-radius: 6px; border-left: 4px solid #2563eb;">
                        <h4 style="color: #2563eb; margin-bottom: 10px;">Informations importantes</h4>
                        <ul style="color: #64748b; font-size: 0.9rem; padding-left: 20px;">
                            <li>Annulation gratuite jusqu'à 24h avant le vol</li>
                            <li>Bagage cabine inclus</li>
                            <li>Modification possible avec frais</li>
                            <li>Présentation d'une pièce d'identité obligatoire</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Validation du formulaire
        document.getElementById('reservationForm')?.addEventListener('submit', function(e) {
            if (!validateForm()) {
                e.preventDefault();
            }
        });

        function validateForm() {
            // Validation des dates de naissance
            const today = new Date();
            for (let i = 1; i <= <?php echo $passagers; ?>; i++) {
                const birthDate = new Date(document.getElementById('passenger' + i + '_naissance').value);
                if (birthDate > today) {
                    alert('La date de naissance du passager ' + i + ' ne peut pas être dans le futur');
                    return false;
                }
            }

            return true;
        }

        // Pré-remplir avec les données utilisateur si connecté
        <?php if (isset($_SESSION['user_id'])): ?>
        document.addEventListener('DOMContentLoaded', function() {
            // Pré-remplir les champs utilisateur si disponibles en session
            const userData = {
                nom: '<?php echo $_SESSION['user_name'] ?? ''; ?>',
                email: '<?php echo $_SESSION['user_email'] ?? ''; ?>',
                // Ajouter d'autres champs si disponibles
            };

            if (userData.nom) {
                document.getElementById('nom').value = userData.nom;
            }
            if (userData.email) {
                document.getElementById('email').value = userData.email;
            }
        });
        <?php endif; ?>
    </script>
</body>
</html>