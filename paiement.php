<?php
session_start();
require_once 'includes/db.php';

// Vérifier si l'ID de réservation est présent
if (!isset($_GET['reservation_id'])) {
    header('Location: index.php');
    exit;
}

$reservation_id = intval($_GET['reservation_id']);
$error = $_GET['error'] ?? null;

// Récupérer les données de la réservation
try {
    $sql = "SELECT r.*, v.depart, v.arrivee, v.date_depart, v.date_arrivee, 
                   c.nom_compagnie, c.code_compagnie, a.modele as avion_modele
            FROM reservations r
            JOIN vols v ON r.id_vol = v.id_vol
            JOIN compagnies c ON v.id_compagnie = c.id_compagnie
            JOIN avions a ON v.id_avion = a.id_avion
            WHERE r.id_reservation = ? AND r.id_user = ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$reservation_id, $_SESSION['id_user']]);
    $reservation = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$reservation) {
        header('Location: index.php?error=reservation_non_trouvee');
        exit;
    }
    
    // Si la réservation est déjà confirmée, rediriger vers confirmation
    if ($reservation['statut'] === 'confirmé') {
        header('Location: confirmation.php?id_reservation=' . $reservation_id);
        exit;
    }
    
} catch (PDOException $e) {
    error_log("Erreur récupération réservation: " . $e->getMessage());
    header('Location: index.php?error=erreur_base_donnees');
    exit;
}

// Récupérer les sièges réservés
$sql_sieges = "SELECT sa.rang, sa.position, sa.supplement_prix
               FROM reservation_sieges rs 
               JOIN sieges_avion sa ON rs.id_siege = sa.id_siege 
               WHERE rs.id_reservation = ?";
$stmt_sieges = $pdo->prepare($sql_sieges);
$stmt_sieges->execute([$reservation_id]);
$sieges = $stmt_sieges->fetchAll(PDO::FETCH_ASSOC);

$total_price = floatval($reservation['prix_total']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paiement Sécurisé - JetReserve</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #0054A4;
            --secondary: #6c757d;
            --success: #28a745;
            --danger: #dc3545;
            --warning: #ffc107;
            --light: #f8f9fa;
            --dark: #343a40;
            --white: #ffffff;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: var(--dark);
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        /* Header */
        header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            font-size: 28px;
            font-weight: 700;
            color: var(--primary);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .logo span {
            color: var(--danger);
        }
        
        .security-badge {
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--success);
            font-weight: 500;
        }
        
        /* Progress Bar */
        .progress-container {
            background: white;
            border-radius: 10px;
            padding: 2rem;
            margin: 2rem auto;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            max-width: 800px;
        }
        
        .progress-steps {
            display: flex;
            justify-content: space-between;
            position: relative;
            margin-bottom: 2rem;
        }
        
        .progress-steps::before {
            content: "";
            position: absolute;
            top: 15px;
            left: 0;
            right: 0;
            height: 3px;
            background: #e9ecef;
            z-index: 1;
        }
        
        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            z-index: 2;
        }
        
        .step-number {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: var(--white);
            border: 3px solid #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-bottom: 0.5rem;
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
            color: var(--secondary);
            font-weight: 500;
        }
        
        .step.active .step-label {
            color: var(--primary);
            font-weight: 600;
        }
        
        /* Payment Container */
        .payment-container {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 30px;
            margin: 2rem auto;
        }
        
        @media (max-width: 968px) {
            .payment-container {
                grid-template-columns: 1fr;
            }
        }
        
        /* Payment Form */
        .payment-form {
            background: white;
            border-radius: 15px;
            padding: 2.5rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .form-section {
            margin-bottom: 2rem;
        }
        
        .section-title {
            color: var(--primary);
            margin-bottom: 1.5rem;
            font-weight: 600;
            font-size: 1.4rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--dark);
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(0, 84, 164, 0.1);
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        
        /* Payment Methods */
        .payment-methods {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .payment-method {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 1.5rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: white;
        }
        
        .payment-method:hover {
            border-color: var(--primary);
            transform: translateY(-2px);
        }
        
        .payment-method.selected {
            border-color: var(--primary);
            background: linear-gradient(135deg, #f0f9ff 0%, #e1f5fe 100%);
            box-shadow: 0 5px 15px rgba(0, 84, 164, 0.2);
        }
        
        .payment-method i {
            font-size: 2rem;
            margin-bottom: 0.75rem;
            color: var(--secondary);
        }
        
        .payment-method.selected i {
            color: var(--primary);
        }
        
        /* Order Summary */
        .order-summary {
            position: sticky;
            top: 120px;
            height: fit-content;
        }
        
        .summary-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .flight-info {
            padding-bottom: 1.5rem;
            border-bottom: 2px solid #e9ecef;
            margin-bottom: 1.5rem;
        }
        
        .flight-route {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .route {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--primary);
        }
        
        .price {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--primary);
        }
        
        .flight-details {
            color: var(--secondary);
            font-size: 0.9rem;
        }
        
        .seats-list {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 1rem;
        }
        
        .seat-tag {
            background: var(--primary);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        .price-breakdown {
            space-y: 1rem;
        }
        
        .price-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e9ecef;
        }
        
        .price-total {
            border-top: 2px solid #e9ecef;
            padding-top: 1rem;
            margin-top: 1rem;
            font-weight: 700;
            font-size: 1.3rem;
            color: var(--primary);
        }
        
        /* Security Features */
        .security-features {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #e9ecef;
        }
        
        .security-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--success);
            font-size: 0.9rem;
        }
        
        /* Buttons */
        .btn-submit {
            background: linear-gradient(135deg, var(--primary), #003366);
            color: white;
            border: none;
            padding: 1.25rem 2rem;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 84, 164, 0.3);
        }
        
        .btn-submit:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        /* Loading */
        .loading {
            display: none;
            text-align: center;
            padding: 2rem;
        }
        
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid var(--primary);
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin: 0 auto 1rem;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Error Messages */
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        /* Bank Logos */
        .bank-logos {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-top: 1rem;
            opacity: 0.7;
        }
        
        .bank-logo {
            font-size: 1.5rem;
            color: var(--secondary);
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
                <div class="security-badge">
                    <i class="fas fa-lock"></i>
                    <span>Paiement 100% Sécurisé</span>
                </div>
            </div>
        </div>
    </header>

    <div class="container">
        <!-- Barre de progression -->
        <div class="progress-container">
            <div class="progress-steps">
                <div class="step completed">
                    <div class="step-number"><i class="fas fa-check"></i></div>
                    <div class="step-label">Recherche</div>
                </div>
                <div class="step completed">
                    <div class="step-number"><i class="fas fa-check"></i></div>
                    <div class="step-label">Sélection</div>
                </div>
                <div class="step completed">
                    <div class="step-number"><i class="fas fa-check"></i></div>
                    <div class="step-label">Réservation</div>
                </div>
                <div class="step active">
                    <div class="step-number">4</div>
                    <div class="step-label">Paiement</div>
                </div>
                <div class="step">
                    <div class="step-number">5</div>
                    <div class="step-label">Confirmation</div>
                </div>
            </div>
        </div>

        <div class="payment-container">
            <!-- Formulaire de paiement -->
            <div class="payment-section">
                <div class="payment-form">
                    <h2 class="section-title">
                        <i class="fas fa-credit-card"></i>
                        Paiement Sécurisé
                    </h2>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i>
                            <div>
                                <?php 
                                switch($error) {
                                    case 'carte_refusee': echo 'Votre carte a été refusée. Veuillez essayer une autre carte.'; break;
                                    case 'erreur_paiement': echo 'Une erreur est survenue lors du traitement. Veuillez réessayer.'; break;
                                    default: echo 'Une erreur est survenue. Veuillez réessayer.';
                                }
                                ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <div class="form-section">
                        <h3 style="margin-bottom: 1rem; color: var(--dark);">Moyen de paiement</h3>
                        <div class="payment-methods">
                            <div class="payment-method selected" data-method="carte">
                                <i class="fas fa-credit-card"></i>
                                <div>Carte bancaire</div>
                            </div>
                            <div class="payment-method" data-method="paypal">
                                <i class="fab fa-paypal"></i>
                                <div>PayPal</div>
                            </div>
                            <div class="payment-method" data-method="applepay">
                                <i class="fab fa-apple-pay"></i>
                                <div>Apple Pay</div>
                            </div>
                        </div>
                    </div>

                    <form action="process_payment.php" method="POST" id="paymentForm">
                        <input type="hidden" name="reservation_id" value="<?php echo $reservation_id; ?>">
                        <input type="hidden" name="payment_method" value="carte" id="paymentMethod">
                        
                        <!-- Section Carte Bancaire -->
                        <div id="cardSection">
                            <div class="form-group">
                                <label for="card_name">Nom sur la carte *</label>
                                <input type="text" id="card_name" name="card_name" class="form-control" placeholder="JEAN DUPONT" required>
                            </div>
                            <div class="form-group">
                                <label for="card_number">Numéro de carte *</label>
                                <input type="text" id="card_number" name="card_number" class="form-control" placeholder="1234 5678 9012 3456" required maxlength="19">
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="expiry_date">Date d'expiration *</label>
                                    <input type="text" id="expiry_date" name="expiry_date" class="form-control" placeholder="MM/AA" required maxlength="5">
                                </div>
                                <div class="form-group">
                                    <label for="cvv">Code de sécurité *</label>
                                    <input type="text" id="cvv" name="cvv" class="form-control" placeholder="123" required maxlength="3">
                                </div>
                            </div>
                        </div>

                        <div class="security-features">
                            <div class="security-item">
                                <i class="fas fa-shield-alt"></i>
                                <span>Chiffrement SSL 256-bit</span>
                            </div>
                            <div class="security-item">
                                <i class="fas fa-user-shield"></i>
                                <span>Protection des données</span>
                            </div>
                        </div>

                        <div class="bank-logos">
                            <i class="fab fa-cc-visa bank-logo"></i>
                            <i class="fab fa-cc-mastercard bank-logo"></i>
                            <i class="fab fa-cc-amex bank-logo"></i>
                        </div>

                        <button type="submit" class="btn-submit" id="submitBtn">
                            <i class="fas fa-lock"></i>
                            Payer <?php echo number_format($total_price, 2, ',', ' '); ?>€
                        </button>
                    </form>

                    <div class="loading" id="loading">
                        <div class="spinner"></div>
                        <p>Traitement de votre paiement en cours...</p>
                        <p style="font-size: 0.9rem; color: var(--secondary); margin-top: 0.5rem;">
                            Veuillez ne pas fermer cette page
                        </p>
                    </div>
                </div>
            </div>

            <!-- Récapitulatif de la commande -->
            <div class="order-summary">
                <div class="summary-card">
                    <h3 class="section-title">
                        <i class="fas fa-receipt"></i>
                        Votre réservation
                    </h3>
                    
                    <div class="flight-info">
                        <div class="flight-route">
                            <div class="route"><?php echo htmlspecialchars($reservation['depart']); ?> → <?php echo htmlspecialchars($reservation['arrivee']); ?></div>
                            <div class="price"><?php echo number_format($reservation['prix_total'], 2, ',', ' '); ?>€</div>
                        </div>
                        <div class="flight-details">
                            <?php echo date('d/m/Y H:i', strtotime($reservation['date_depart'])); ?> • 
                            <?php echo htmlspecialchars($reservation['nom_compagnie']); ?>
                        </div>
                        <?php if (!empty($sieges)): ?>
                        <div style="margin-top: 1rem;">
                            <div style="font-size: 0.9rem; color: var(--secondary); margin-bottom: 0.5rem;">Sièges réservés :</div>
                            <div class="seats-list">
                                <?php foreach ($sieges as $siege): ?>
                                    <div class="seat-tag">
                                        <?php echo $siege['position'] . $siege['rang']; ?>
                                        <?php if ($siege['supplement_prix'] > 0): ?>
                                            <small style="font-size: 0.7rem;">(+<?php echo $siege['supplement_prix']; ?>€)</small>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="price-breakdown">
                        <div class="price-item">
                            <span>Passager x<?php echo $reservation['nombre_passagers']; ?></span>
                            <span><?php echo number_format($reservation['prix_total'] - 34, 2, ',', ' '); ?>€</span>
                        </div>
                        <div class="price-item">
                            <span>Frais de service</span>
                            <span>9,00€</span>
                        </div>
                        <div class="price-item">
                            <span>Taxes aéroport</span>
                            <span>25,00€</span>
                        </div>
                        <div class="price-item price-total">
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
                document.querySelectorAll('.payment-method').forEach(m => {
                    m.classList.remove('selected');
                });
                this.classList.add('selected');
                document.getElementById('paymentMethod').value = this.dataset.method;
            });
        });

        // Formatage automatique du numéro de carte
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

        // Validation renforcée du formulaire
        document.getElementById('paymentForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (!validatePaymentForm()) {
                return false;
            }
            
            // Simulation de traitement
            const submitBtn = document.getElementById('submitBtn');
            const loading = document.getElementById('loading');
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Traitement en cours...';
            loading.style.display = 'block';
            
            // Soumission réelle après un petit délai pour l'effet
            setTimeout(() => {
                this.submit();
            }, 1500);
        });

        function validatePaymentForm() {
            const paymentMethod = document.getElementById('paymentMethod').value;
            
            if (paymentMethod === 'carte') {
                const cardName = document.getElementById('card_name').value.trim();
                const cardNumber = document.getElementById('card_number').value.replace(/\s/g, '');
                const expiryDate = document.getElementById('expiry_date').value;
                const cvv = document.getElementById('cvv').value;
                
                if (cardName.length < 2) {
                    alert('Veuillez entrer le nom figurant sur votre carte');
                    return false;
                }
                
                if (cardNumber.length !== 16 || isNaN(cardNumber)) {
                    alert('Veuillez entrer un numéro de carte valide (16 chiffres)');
                    return false;
                }
                
                if (!/^\d{2}\/\d{2}$/.test(expiryDate)) {
                    alert('Format de date d\'expiration invalide (MM/AA)');
                    return false;
                }
                
                // Vérification de la date d'expiration
                const [month, year] = expiryDate.split('/');
                const now = new Date();
                const currentYear = now.getFullYear() % 100;
                const currentMonth = now.getMonth() + 1;
                
                if (parseInt(month) < 1 || parseInt(month) > 12) {
                    alert('Mois d\'expiration invalide');
                    return false;
                }
                
                if (parseInt(year) < currentYear || (parseInt(year) === currentYear && parseInt(month) < currentMonth)) {
                    alert('Votre carte est expirée');
                    return false;
                }
                
                if (cvv.length !== 3 || isNaN(cvv)) {
                    alert('Code de sécurité invalide (3 chiffres au dos de votre carte)');
                    return false;
                }
            }
            
            return true;
        }

        // Effets visuels pour les champs
        document.querySelectorAll('.form-control').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'translateY(-2px)';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'translateY(0)';
            });
        });
    </script>
</body>
</html>