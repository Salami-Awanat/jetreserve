<?php
session_start();
require_once 'includes/db.php';

// Vérifier si l'ID de réservation est présent
if (!isset($_GET['reservation_id'])) {
    header('Location: index.php');
    exit;
}

$reservation_id = intval($_GET['reservation_id']);

// Récupérer les données de la réservation
try {
    $sql = "SELECT r.*, v.depart, v.arrivee, v.date_depart, v.date_arrivee, 
                   c.nom_compagnie, c.code_compagnie, a.modele as avion_modele
            FROM reservations r
            JOIN vols v ON r.id_vol = v.id_vol
            JOIN compagnies c ON v.id_compagnie = c.id_compagnie
            JOIN avions a ON v.id_avion = a.id_avion
            WHERE r.id_reservation = ? AND r.id_user = ? AND r.statut = 'en attente'";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$reservation_id, $_SESSION['id_user']]);
    $reservation = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$reservation) {
        // Assouplir le statut: accepter aussi 'confirmé' (cas réservation déjà confirmée)
        $sql2 = "SELECT r.*, v.depart, v.arrivee, v.date_depart, v.date_arrivee, 
                        c.nom_compagnie, c.code_compagnie, a.modele as avion_modele
                 FROM reservations r
                 JOIN vols v ON r.id_vol = v.id_vol
                 JOIN compagnies c ON v.id_compagnie = c.id_compagnie
                 JOIN avions a ON v.id_avion = a.id_avion
                 WHERE r.id_reservation = ? AND r.id_user = ? AND r.statut IN ('en attente', 'confirmé')";
        $stmt2 = $pdo->prepare($sql2);
        $stmt2->execute([$reservation_id, $_SESSION['id_user']]);
        $reservation = $stmt2->fetch(PDO::FETCH_ASSOC);
        if (!$reservation) {
            header('Location: index.php?error=reservation_non_trouvee');
            exit;
        }
    }
    
} catch (PDOException $e) {
    error_log("Erreur récupération réservation: " . $e->getMessage());
    header('Location: index.php?error=erreur_base_donnees');
    exit;
}

// Récupérer les sièges réservés
$sql_sieges = "SELECT sa.rang, sa.position 
               FROM reservation_sieges rs 
               JOIN sieges_avion sa ON rs.id_siege = sa.id_siege 
               WHERE rs.id_reservation = ?";
$stmt_sieges = $pdo->prepare($sql_sieges);
$stmt_sieges->execute([$reservation_id]);
$sieges = $stmt_sieges->fetchAll(PDO::FETCH_ASSOC);

$total_price = floatval($reservation['prix_total']) + 9 + 25; // + frais service + taxes
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paiement - JetReserve</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Reprendre le même CSS que reservation.php */
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
            max-width: 1000px;
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

        .payment-container {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 30px;
            margin: 30px auto;
        }

        @media (max-width: 968px) {
            .payment-container {
                grid-template-columns: 1fr;
            }
        }

        .payment-form {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }

        .order-summary {
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

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
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

        .payment-methods {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-bottom: 20px;
        }

        .payment-method {
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .payment-method:hover {
            border-color: #2563eb;
        }

        .payment-method.selected {
            border-color: #2563eb;
            background: #f0f9ff;
        }

        .payment-method i {
            font-size: 24px;
            margin-bottom: 10px;
            color: #64748b;
        }

        .payment-method.selected i {
            color: #2563eb;
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
            <div class="progress-step completed">
                <div class="step-number">3</div>
                <div>Réservation</div>
            </div>
            <div class="progress-step active">
                <div class="step-number">4</div>
                <div>Paiement</div>
            </div>
            <div class="progress-step">
                <div class="step-number">5</div>
                <div>Confirmation</div>
            </div>
        </div>

        <div class="payment-container">
            <!-- Formulaire de paiement -->
            <div class="payment-section">
                <div class="payment-form">
                    <h2 class="section-title">Paiement sécurisé</h2>
                    
                    <div class="payment-methods">
                        <div class="payment-method selected" data-method="carte">
                            <i class="fas fa-credit-card"></i>
                            <div>Carte bancaire</div>
                        </div>
                        <div class="payment-method" data-method="paypal">
                            <i class="fab fa-paypal"></i>
                            <div>PayPal</div>
                        </div>
                        <div class="payment-method" data-method="mobile">
                            <i class="fas fa-mobile-alt"></i>
                            <div>Mobile Money</div>
                        </div>
                    </div>

                    <form action="process_payment.php" method="POST" id="paymentForm">
                        <input type="hidden" name="reservation_id" value="<?php echo $reservation_id; ?>">
                        <input type="hidden" name="payment_method" value="carte" id="paymentMethod">
                        
                        <!-- Section Carte Bancaire (affichée par défaut) -->
                        <div id="cardSection">
                            <div class="form-group">
                                <label for="card_name">Nom sur la carte *</label>
                                <input type="text" id="card_name" name="card_name" class="form-control" placeholder="JEAN DUPONT" required>
                            </div>
                            <div class="form-group">
                                <label for="card_number">Numéro de carte *</label>
                                <input type="text" id="card_number" name="card_number" class="form-control" placeholder="1234 5678 9012 3456" required>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="expiry_date">Date d'expiration *</label>
                                    <input type="text" id="expiry_date" name="expiry_date" class="form-control" placeholder="MM/AA" required>
                                </div>
                                <div class="form-group">
                                    <label for="cvv">CVV *</label>
                                    <input type="text" id="cvv" name="cvv" class="form-control" placeholder="123" required>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn-submit">
                            <i class="fas fa-lock"></i> Payer <?php echo number_format($total_price, 2, ',', ' '); ?>€
                        </button>
                    </form>
                </div>
            </div>

            <!-- Récapitulatif de la commande -->
            <div class="order-summary">
                <div class="payment-form">
                    <h3 class="section-title">Votre réservation</h3>
                    
                    <div style="margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #e2e8f0;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                            <strong><?php echo htmlspecialchars($reservation['depart']); ?> → <?php echo htmlspecialchars($reservation['arrivee']); ?></strong>
                            <span style="color: #2563eb; font-weight: 600;"><?php echo number_format($reservation['prix_total'], 2, ',', ' '); ?>€</span>
                        </div>
                        <div style="color: #64748b; font-size: 0.9rem;">
                            <?php echo date('d/m/Y H:i', strtotime($reservation['date_depart'])); ?> • 
                            <?php echo htmlspecialchars($reservation['nom_compagnie']); ?>
                        </div>
                        <?php if (!empty($sieges)): ?>
                        <div style="margin-top: 10px;">
                            <div style="font-size: 0.9rem; color: #64748b;">Sièges :</div>
                            <?php foreach ($sieges as $siege): ?>
                                <span class="seat-badge"><?php echo $siege['position'] . $siege['rang']; ?></span>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="price-breakdown">
                        <div class="price-item">
                            <span>Passager x<?php echo $reservation['nombre_passagers']; ?></span>
                            <span><?php echo number_format($reservation['prix_total'], 2, ',', ' '); ?>€</span>
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
                            <span>Total</span>
                            <span><?php echo number_format($total_price, 2, ',', ' '); ?>€</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Gestion des méthodes de paiement
        document.querySelectorAll('.payment-method').forEach(method => {
            method.addEventListener('click', function() {
                // Désélectionner toutes les méthodes
                document.querySelectorAll('.payment-method').forEach(m => {
                    m.classList.remove('selected');
                });
                
                // Sélectionner la méthode cliquée
                this.classList.add('selected');
                
                // Mettre à jour le champ caché
                document.getElementById('paymentMethod').value = this.dataset.method;
            });
        });

        // Validation du formulaire de paiement
        document.getElementById('paymentForm').addEventListener('submit', function(e) {
            if (!validatePaymentForm()) {
                e.preventDefault();
            }
        });

        function validatePaymentForm() {
            const paymentMethod = document.getElementById('paymentMethod').value;
            
            if (paymentMethod === 'carte') {
                const cardNumber = document.getElementById('card_number').value.replace(/\s/g, '');
                const expiryDate = document.getElementById('expiry_date').value;
                const cvv = document.getElementById('cvv').value;
                
                if (cardNumber.length !== 16 || isNaN(cardNumber)) {
                    alert('Veuillez entrer un numéro de carte valide (16 chiffres)');
                    return false;
                }
                
                if (!/^\d{2}\/\d{2}$/.test(expiryDate)) {
                    alert('Format de date d\'expiration invalide (MM/AA)');
                    return false;
                }
                
                if (cvv.length !== 3 || isNaN(cvv)) {
                    alert('CVV invalide (3 chiffres)');
                    return false;
                }
            }
            
            return true;
        }

        // Formatage automatique de la carte
        document.getElementById('card_number')?.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\s/g, '');
            if (value.length > 0) {
                value = value.match(new RegExp('.{1,4}', 'g')).join(' ');
            }
            e.target.value = value;
        });

        // Formatage automatique de la date d'expiration
        document.getElementById('expiry_date')?.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length >= 2) {
                value = value.substring(0, 2) + '/' + value.substring(2, 4);
            }
            e.target.value = value;
        });
    </script>
</body>
</html>