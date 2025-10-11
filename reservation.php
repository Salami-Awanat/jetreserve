<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réservation - JetReserve</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="reservation.css">
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
                    <li><a href="#"><i class="fas fa-ticket-alt"></i> Billetterie</a></li>
                    <li><a href="#"><i class="fas fa-map-marked-alt"></i> Destinations</a></li>
                    <li><a href="#"><i class="fas fa-tags"></i> Offres spéciales</a></li>
                </ul>
                <div class="contact-info">
                    <a href="#"><i class="fas fa-phone-alt"></i> Service client</a>
                </div>
            </nav>
        </div>
    </header>

    <!-- Processus de réservation -->
    <main class="container">
        <div class="reservation-header">
            <h1>Finaliser votre réservation</h1>
            <div class="reservation-steps">
                <div class="step active">
                    <span class="step-number">1</span>
                    <span class="step-text">Détails du vol</span>
                </div>
                <div class="step">
                    <span class="step-number">2</span>
                    <span class="step-text">Informations voyageurs</span>
                </div>
                <div class="step">
                    <span class="step-number">3</span>
                    <span class="step-text">Options supplémentaires</span>
                </div>
                <div class="step">
                    <span class="step-number">4</span>
                    <span class="step-text">Paiement</span>
                </div>
            </div>
        </div>

        <div class="reservation-content">
            <!-- Section Détails du vol -->
            <section class="reservation-section">
                <h2><i class="fas fa-plane"></i> Détails de votre vol</h2>
                
                <?php
                // Récupération des données du vol depuis l'URL
                $flightData = [
                    'airline' => $_GET['airline'] ?? 'Air France',
                    'flight_number' => $_GET['flight_number'] ?? 'AF1234',
                    'from' => $_GET['from'] ?? 'Paris (CDG)',
                    'to' => $_GET['to'] ?? 'New York (JFK)',
                    'departure_date' => $_GET['departure_date'] ?? date('Y-m-d', strtotime('+7 days')),
                    'return_date' => $_GET['return_date'] ?? '',
                    'passengers' => $_GET['passengers'] ?? '1',
                    'class' => $_GET['class'] ?? 'Économique',
                    'price' => $_GET['price'] ?? '450'
                ];
                
                // Calcul du nombre de voyageurs
                $passengerCount = 1;
                if (preg_match('/(\d+)/', $flightData['passengers'], $matches)) {
                    $passengerCount = intval($matches[1]);
                }
                ?>

                <div class="flight-details-card">
                    <div class="flight-header">
                        <div class="airline-info">
                            <i class="fas fa-plane fa-2x"></i>
                            <div>
                                <h3><?php echo htmlspecialchars($flightData['airline']); ?></h3>
                                <p>Vol <?php echo htmlspecialchars($flightData['flight_number']); ?></p>
                            </div>
                        </div>
                        <div class="flight-price">
                            <span class="price"><?php echo htmlspecialchars($flightData['price']); ?>€</span>
                            <span class="price-detail">par personne</span>
                        </div>
                    </div>

                    <div class="flight-route">
                        <div class="route-segment">
                            <div class="route-time">
                                <strong>10:30</strong>
                                <span><?php echo htmlspecialchars($flightData['from']); ?></span>
                            </div>
                            <div class="route-duration">
                                <div class="duration-line">
                                    <i class="fas fa-plane"></i>
                                </div>
                                <span>8h 15min</span>
                            </div>
                            <div class="route-time">
                                <strong>16:45</strong>
                                <span><?php echo htmlspecialchars($flightData['to']); ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="flight-info-grid">
                        <div class="info-item">
                            <label>Date de départ</label>
                            <strong><?php echo date('d/m/Y', strtotime($flightData['departure_date'])); ?></strong>
                        </div>
                        <div class="info-item">
                            <label>Classe</label>
                            <strong><?php echo htmlspecialchars($flightData['class']); ?></strong>
                        </div>
                        <div class="info-item">
                            <label>Passagers</label>
                            <strong><?php echo htmlspecialchars($flightData['passengers']); ?></strong>
                        </div>
                        <?php if(!empty($flightData['return_date'])): ?>
                        <div class="info-item">
                            <label>Date de retour</label>
                            <strong><?php echo date('d/m/Y', strtotime($flightData['return_date'])); ?></strong>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </section>

            <!-- Section Informations voyageurs -->
            <section class="reservation-section">
                <h2><i class="fas fa-users"></i> Informations des voyageurs</h2>
                
                <form id="passengerForm" class="passenger-form">
                    <?php for($i = 1; $i <= $passengerCount; $i++): ?>
                    <div class="passenger-card">
                        <h3>Voyageur <?php echo $i; ?></h3>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="passenger_civility_<?php echo $i; ?>">Civilité *</label>
                                <select id="passenger_civility_<?php echo $i; ?>" name="passenger[<?php echo $i; ?>][civility]" required>
                                    <option value="">Sélectionner</option>
                                    <option value="M">Monsieur</option>
                                    <option value="Mme">Madame</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="passenger_lastname_<?php echo $i; ?>">Nom *</label>
                                <input type="text" id="passenger_lastname_<?php echo $i; ?>" name="passenger[<?php echo $i; ?>][lastname]" required>
                            </div>
                            <div class="form-group">
                                <label for="passenger_firstname_<?php echo $i; ?>">Prénom *</label>
                                <input type="text" id="passenger_firstname_<?php echo $i; ?>" name="passenger[<?php echo $i; ?>][firstname]" required>
                            </div>
                            <div class="form-group">
                                <label for="passenger_birthdate_<?php echo $i; ?>">Date de naissance *</label>
                                <input type="date" id="passenger_birthdate_<?php echo $i; ?>" name="passenger[<?php echo $i; ?>][birthdate]" required>
                            </div>
                            <div class="form-group">
                                <label for="passenger_email_<?php echo $i; ?>">Email *</label>
                                <input type="email" id="passenger_email_<?php echo $i; ?>" name="passenger[<?php echo $i; ?>][email]" required>
                            </div>
                            <div class="form-group">
                                <label for="passenger_phone_<?php echo $i; ?>">Téléphone *</label>
                                <input type="tel" id="passenger_phone_<?php echo $i; ?>" name="passenger[<?php echo $i; ?>][phone]" required>
                            </div>
                        </div>
                    </div>
                    <?php endfor; ?>
                </form>
            </section>

            <!-- Section Options supplémentaires -->
            <section class="reservation-section">
                <h2><i class="fas fa-plus-circle"></i> Options supplémentaires</h2>
                
                <div class="options-grid">
                    <div class="option-card">
                        <div class="option-header">
                            <h3>Bagages supplémentaires</h3>
                            <span class="option-price">+ 35€</span>
                        </div>
                        <p>Ajoutez 23kg de bagage en soute supplémentaire</p>
                        <div class="option-select">
                            <input type="checkbox" id="extra_baggage" name="extra_baggage">
                            <label for="extra_baggage">Sélectionner</label>
                        </div>
                    </div>

                    <div class="option-card">
                        <div class="option-header">
                            <h3>Assurance voyage</h3>
                            <span class="option-price">+ 29€</span>
                        </div>
                        <p>Protection annulation, bagages et assistance médicale</p>
                        <div class="option-select">
                            <input type="checkbox" id="travel_insurance" name="travel_insurance">
                            <label for="travel_insurance">Sélectionner</label>
                        </div>
                    </div>

                    <div class="option-card">
                        <div class="option-header">
                            <h3>Siège premium</h3>
                            <span class="option-price">+ 25€</span>
                        </div>
                        <p>Siège avec plus d'espace pour les jambes</p>
                        <div class="option-select">
                            <input type="checkbox" id="premium_seat" name="premium_seat">
                            <label for="premium_seat">Sélectionner</label>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Récapitulatif et paiement -->
            <section class="reservation-section">
                <h2><i class="fas fa-credit-card"></i> Récapitulatif et paiement</h2>
                
                <div class="summary-payment">
                    <div class="summary-card">
                        <h3>Récapitulatif de commande</h3>
                        <div class="summary-items">
                            <div class="summary-item">
                                <span>Vol <?php echo htmlspecialchars($flightData['airline']); ?></span>
                                <span><?php echo htmlspecialchars($flightData['price']); ?>€ × <?php echo $passengerCount; ?></span>
                            </div>
                            <div class="summary-item optional" id="baggage-item" style="display: none;">
                                <span>Bagage supplémentaire</span>
                                <span>35€</span>
                            </div>
                            <div class="summary-item optional" id="insurance-item" style="display: none;">
                                <span>Assurance voyage</span>
                                <span>29€</span>
                            </div>
                            <div class="summary-item optional" id="seat-item" style="display: none;">
                                <span>Siège premium</span>
                                <span>25€</span>
                            </div>
                            <div class="summary-total">
                                <strong>Total</strong>
                                <strong id="total-price"><?php echo htmlspecialchars($flightData['price'] * $passengerCount); ?>€</strong>
                            </div>
                        </div>
                    </div>

                    <div class="payment-card">
                        <h3>Informations de paiement</h3>
                        <form id="paymentForm" class="payment-form">
                            <div class="form-group">
                                <label for="card_number">Numéro de carte *</label>
                                <input type="text" id="card_number" name="card_number" placeholder="1234 5678 9012 3456" required maxlength="19">
                            </div>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="expiry_date">Date d'expiration *</label>
                                    <input type="text" id="expiry_date" name="expiry_date" placeholder="MM/AA" required maxlength="5">
                                </div>
                                <div class="form-group">
                                    <label for="cvv">CVV *</label>
                                    <input type="text" id="cvv" name="cvv" placeholder="123" required maxlength="4">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="card_holder">Nom du titulaire *</label>
                                <input type="text" id="card_holder" name="card_holder" required>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-large payment-btn">
                                <i class="fas fa-lock"></i> Payer et confirmer la réservation
                            </button>
                        </form>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-grid">
                <div class="footer-column">
                    <h4>JetReserve</h4>
                    <p style="color: #cbd5e1; margin-bottom: 20px; font-size: 14px;">
                        Votre partenaire de confiance pour des voyages inoubliables depuis 2023.
                    </p>
                </div>
                <div class="footer-column">
                    <h4>Assistance réservation</h4>
                    <div class="footer-links">
                        <li><a href="#"><i class="fas fa-chevron-right"></i> Conditions générales</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right"></i> Politique de remboursement</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right"></i> FAQ</a></li>
                    </div>
                </div>
                <div class="footer-column">
                    <h4>Contact urgent</h4>
                    <div class="contact-info-footer">
                        <div class="contact-item">
                            <i class="fas fa-phone"></i>
                            <span>+33 1 23 45 67 89</span>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-envelope"></i>
                            <span>reservation@jetreserve.com</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2023 JetReserve. Tous droits réservés.</p>
            </div>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="reservation.js"></script>
</body>
</html>