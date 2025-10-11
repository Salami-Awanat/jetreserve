<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paiement Sécurisé - JetReserve</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="paiement.css">
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container">
            <div class="header-top">
                <a href="index.php" class="logo">Jet<span>Reserve</span></a>
                <div class="auth-buttons">
                    <a href="vge64/connexion.php" class="btn btn-outline">Connexion</a>
                    <a href="vge64/inscription.php" class="btn btn-primary">Inscription</a>
                </div>
            </div>
            <nav class="nav-menu">
                <ul class="nav-links">
                    <li><a href="index.php"><i class="fas fa-home"></i> Accueil</a></li>
                    <li><a href="#"><i class="fas fa-plane-departure"></i> Vols</a></li>
                    <li><a href="#"><i class="fas fa-suitcase-rolling"></i> Forfaits</a></li>
                </ul>
                <div class="contact-info">
                    <a href="#"><i class="fas fa-phone-alt"></i> Service client</a>
                </div>
            </nav>
        </div>
    </header>

    <!-- Processus de paiement -->
    <main class="container">
        <div class="payment-header">
            <h1><i class="fas fa-lock"></i> Paiement Sécurisé</h1>
            <div class="payment-steps">
                <div class="step completed">
                    <span class="step-number">1</span>
                    <span class="step-text">Réservation</span>
                </div>
                <div class="step completed">
                    <span class="step-number">2</span>
                    <span class="step-text">Voyageurs</span>
                </div>
                <div class="step active">
                    <span class="step-number">3</span>
                    <span class="step-text">Paiement</span>
                </div>
                <div class="step">
                    <span class="step-number">4</span>
                    <span class="step-text">Confirmation</span>
                </div>
            </div>
        </div>

        <div class="payment-content">
            <div class="payment-grid">
                <!-- Section Récapitulatif -->
                <div class="summary-section">
                    <h2><i class="fas fa-receipt"></i> Récapitulatif</h2>
                    
                    <?php
                    // Récupération des données depuis l'URL ou session
                    $reservationData = [
                        'airline' => $_GET['airline'] ?? 'Air France',
                        'flight_number' => $_GET['flight_number'] ?? 'AF1234',
                        'from' => $_GET['from'] ?? 'Paris (CDG)',
                        'to' => $_GET['to'] ?? 'New York (JFK)',
                        'departure_date' => $_GET['departure_date'] ?? date('Y-m-d', strtotime('+7 days')),
                        'passengers' => $_GET['passengers'] ?? '1 voyageur',
                        'class' => $_GET['class'] ?? 'Économique',
                        'base_price' => $_GET['base_price'] ?? '450',
                        'options' => [
                            'baggage' => $_GET['baggage'] ?? '0',
                            'insurance' => $_GET['insurance'] ?? '0',
                            'seat' => $_GET['seat'] ?? '0'
                        ]
                    ];

                    // Calcul du total
                    $total = intval($reservationData['base_price']);
                    $passengerCount = intval(preg_replace('/[^0-9]/', '', $reservationData['passengers']));
                    
                    if ($reservationData['options']['baggage'] === '1') $total += 35;
                    if ($reservationData['options']['insurance'] === '1') $total += 29;
                    if ($reservationData['options']['seat'] === '1') $total += 25;
                    
                    $total *= $passengerCount;
                    ?>

                    <div class="flight-summary">
                        <div class="summary-header">
                            <h3>Vol <?php echo htmlspecialchars($reservationData['airline']); ?></h3>
                            <span class="flight-number"><?php echo htmlspecialchars($reservationData['flight_number']); ?></span>
                        </div>
                        
                        <div class="route-info">
                            <div class="city">
                                <strong><?php echo htmlspecialchars($reservationData['from']); ?></strong>
                                <span>10:30</span>
                            </div>
                            <div class="duration">
                                <div class="line"></div>
                                <span>8h 15min</span>
                            </div>
                            <div class="city">
                                <strong><?php echo htmlspecialchars($reservationData['to']); ?></strong>
                                <span>16:45</span>
                            </div>
                        </div>

                        <div class="flight-details">
                            <div class="detail-item">
                                <span>Date de départ:</span>
                                <strong><?php echo date('d/m/Y', strtotime($reservationData['departure_date'])); ?></strong>
                            </div>
                            <div class="detail-item">
                                <span>Passagers:</span>
                                <strong><?php echo htmlspecialchars($reservationData['passengers']); ?></strong>
                            </div>
                            <div class="detail-item">
                                <span>Classe:</span>
                                <strong><?php echo htmlspecialchars($reservationData['class']); ?></strong>
                            </div>
                        </div>
                    </div>

                    <div class="price-breakdown">
                        <h4>Détail du prix</h4>
                        <div class="price-item">
                            <span>Vol × <?php echo $passengerCount; ?></span>
                            <span><?php echo htmlspecialchars($reservationData['base_price']); ?>€</span>
                        </div>
                        
                        <?php if ($reservationData['options']['baggage'] === '1'): ?>
                        <div class="price-item">
                            <span>Bagage supplémentaire</span>
                            <span>35€</span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($reservationData['options']['insurance'] === '1'): ?>
                        <div class="price-item">
                            <span>Assurance voyage</span>
                            <span>29€</span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($reservationData['options']['seat'] === '1'): ?>
                        <div class="price-item">
                            <span>Siège premium</span>
                            <span>25€</span>
                        </div>
                        <?php endif; ?>
                        
                        <div class="price-total">
                            <strong>Total</strong>
                            <strong><?php echo $total; ?>€</strong>
                        </div>
                    </div>

                    <div class="security-badges">
                        <div class="security-item">
                            <i class="fas fa-shield-alt"></i>
                            <span>Paiement 100% sécurisé</span>
                        </div>
                        <div class="security-item">
                            <i class="fas fa-lock"></i>
                            <span>Données cryptées</span>
                        </div>
                        <div class="security-item">
                            <i class="fas fa-check-circle"></i>
                            <span>Garantie de remboursement</span>
                        </div>
                    </div>
                </div>

                <!-- Section Formulaire de paiement -->
                <div class="payment-section">
                    <h2><i class="fas fa-credit-card"></i> Informations de paiement</h2>
                    
                    <form id="paymentForm" class="payment-form">
                        <!-- Méthodes de paiement -->
                        <div class="payment-methods">
                            <h4>Méthode de paiement</h4>
                            <div class="methods-grid">
                                <div class="method-card active" data-method="card">
                                    <i class="fab fa-cc-visa"></i>
                                    <span>Carte bancaire</span>
                                </div>
                                <div class="method-card" data-method="paypal">
                                    <i class="fab fa-paypal"></i>
                                    <span>PayPal</span>
                                </div>
                                <div class="method-card" data-method="applepay">
                                    <i class="fab fa-apple-pay"></i>
                                    <span>Apple Pay</span>
                                </div>
                            </div>
                        </div>

                        <!-- Formulaire carte bancaire -->
                        <div id="cardForm" class="payment-method-form active">
                            <div class="form-group">
                                <label for="card_number">Numéro de carte *</label>
                                <div class="input-with-icon">
                                    <input type="text" id="card_number" name="card_number" placeholder="1234 5678 9012 3456" required maxlength="19">
                                    <i class="fas fa-credit-card"></i>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="expiry_date">Date d'expiration *</label>
                                    <input type="text" id="expiry_date" name="expiry_date" placeholder="MM/AA" required maxlength="5">
                                </div>
                                <div class="form-group">
                                    <label for="cvv">CVV *</label>
                                    <div class="input-with-icon">
                                        <input type="text" id="cvv" name="cvv" placeholder="123" required maxlength="4">
                                        <i class="fas fa-question-circle" title="3 chiffres au dos de votre carte"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="card_holder">Nom du titulaire *</label>
                                <input type="text" id="card_holder" name="card_holder" placeholder="M. DUPONT Jean" required>
                            </div>
                        </div>

                        <!-- Formulaire PayPal (caché par défaut) -->
                        <div id="paypalForm" class="payment-method-form">
                            <div class="paypal-info">
                                <p>Vous serez redirigé vers PayPal pour finaliser votre paiement.</p>
                                <button type="button" class="btn btn-paypal">
                                    <i class="fab fa-paypal"></i> Payer avec PayPal
                                </button>
                            </div>
                        </div>

                        <!-- Formulaire Apple Pay (caché par défaut) -->
                        <div id="applepayForm" class="payment-method-form">
                            <div class="applepay-info">
                                <p>Utilisez Apple Pay pour un paiement rapide et sécurisé.</p>
                                <button type="button" class="btn btn-applepay">
                                    <i class="fab fa-apple-pay"></i> Payer avec Apple Pay
                                </button>
                            </div>
                        </div>

                        <!-- Informations de facturation -->
                        <div class="billing-section">
                            <h4>Adresse de facturation</h4>
                            <div class="form-group">
                                <label for="billing_address">Adresse *</label>
                                <input type="text" id="billing_address" name="billing_address" required>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="billing_city">Ville *</label>
                                    <input type="text" id="billing_city" name="billing_city" required>
                                </div>
                                <div class="form-group">
                                    <label for="billing_zip">Code postal *</label>
                                    <input type="text" id="billing_zip" name="billing_zip" required>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="billing_country">Pays *</label>
                                <select id="billing_country" name="billing_country" required>
                                    <option value="">Sélectionner un pays</option>
                                    <option value="FR">France</option>
                                    <option value="BE">Belgique</option>
                                    <option value="CH">Suisse</option>
                                    <option value="LU">Luxembourg</option>
                                </select>
                            </div>
                        </div>

                        <!-- Conditions générales -->
                        <div class="terms-section">
                            <div class="checkbox-group">
                                <input type="checkbox" id="terms" name="terms" required>
                                <label for="terms">
                                    J'accepte les <a href="#" target="_blank">conditions générales de vente</a> 
                                    et la <a href="#" target="_blank">politique de confidentialité</a> *
                                </label>
                            </div>
                            
                            <div class="checkbox-group">
                                <input type="checkbox" id="newsletter" name="newsletter">
                                <label for="newsletter">
                                    Je souhaite recevoir les offres promotionnelles de JetReserve
                                </label>
                            </div>
                        </div>

                        <!-- Bouton de paiement -->
                        <button type="submit" class="btn btn-primary btn-payment">
                            <i class="fas fa-lock"></i>
                            <span>Payer <?php echo $total; ?>€</span>
                            <small>Paiement sécurisé SSL</small>
                        </button>

                        <div class="payment-guarantee">
                            <i class="fas fa-shield-check"></i>
                            <span>Garantie satisfait ou remboursé sous 30 jours</span>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-bottom">
                <p>&copy; 2023 JetReserve. Tous droits réservés. | SAS au capital de 1.000.000€ | RCS Paris 123 456 789</p>
                <div class="payment-icons">
                    <i class="fab fa-cc-visa" title="Visa"></i>
                    <i class="fab fa-cc-mastercard" title="Mastercard"></i>
                    <i class="fab fa-cc-amex" title="American Express"></i>
                    <i class="fab fa-cc-paypal" title="PayPal"></i>
                    <i class="fab fa-apple-pay" title="Apple Pay"></i>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="paiement.js"></script>
</body>
</html>