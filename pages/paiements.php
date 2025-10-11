<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Paiements - JetReserve</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style1.css">
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container">
            <div class="header-top">
                <a href="../index.php" class="logo">Jet<span>Reserve</span></a>
                <div class="auth-buttons">
                    <div class="dropdown">
                        <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user-circle me-2"></i>
                            <?php echo $_SESSION['prenom'] ?? 'Client'; ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="index1.php"><i class="fas fa-home me-2"></i>Tableau de bord</a></li>
                            <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i>Mon profil</a></li>
                            <li><a class="dropdown-item active" href="paiements.php"><i class="fas fa-credit-card me-2"></i>Mes paiements</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="../index.php">
                                <i class="fas fa-power-off me-2"></i>Déconnexion
                            </a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <nav class="nav-menu">
                <ul class="nav-links">
                    <li><a href="../index.php"><i class="fas fa-home"></i> Accueil</a></li>
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

    
    <!-- Bannière avec carousel -->
    <div class="main-banner-container">
        <div class="main-banner owl-carousel owl-theme">
            <div class="item banner-1">
                <div class="header-text">
                    <h2>Bienvenue chez JetReserve</h2>
                </div>
            </div>
            <div class="item banner-2">
                <div class="header-text">
                    <h2>Accédez à vos réservations</h2>
                </div>
            </div>
            <div class="item banner-3">
                <div class="header-text">
                    <h2>Gérez vos voyages</h2>
                </div>
            </div>
            <div class="item banner-4">
                <div class="header-text">
                    <h2>Voyagez en toute sérénité</h2>
                </div>
            </div>
            <div class="item banner-5">
                <div class="header-text">
                    <h2>Retrouvez vos avantages</h2>
                </div>
            </div>
        </div>
    </div> 

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <h2>JetReserve</h2>
                        <p>Tableau de bord client</p>
                    </div>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="index2.php">
                                <i class="fas fa-home me-2"></i>
                                Accueil
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="reservations.php">
                                <i class="fas fa-plane-departure me-2"></i>
                                Mes réservations
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="paiements.php">
                                <i class="fas fa-credit-card me-2"></i>
                                Mes paiements
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="messages.php">
                                <i class="fas fa-envelope me-2"></i>
                                Messages
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="profile.php">
                                <i class="fas fa-user me-2"></i>
                                Mon profil
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../index.php">
                                <i class="fas fa-sign-out-alt me-2"></i>
                                Déconnexion
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>
    <!-- Contenu principal - Page Paiements -->
    <div class="container dashboard-container">
        <!-- En-tête de page -->
        <div class="page-header mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h2"><i class="fas fa-credit-card me-2"></i>Mes Paiements</h1>
                <div class="header-actions">
                    <button class="btn btn-primary">
                        <i class="fas fa-download me-2"></i>Exporter
                    </button>
                </div>
            </div>
            <p class="text-muted">Consultez l'historique complet de vos transactions et paiements</p>
        </div>

        <!-- Cartes de résumé -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="icon icon-paiements">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <h3>Total Dépensé</h3>
                    <p>Sur les 12 derniers mois</p>
                    <div class="number">1 850€</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="icon icon-reservations">
                        <i class="fas fa-receipt"></i>
                    </div>
                    <h3>Transactions</h3>
                    <p>Nombre total de paiements</p>
                    <div class="number">8</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="icon" style="background: linear-gradient(135deg, var(--success), #219653);">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h3>Paiements Validés</h3>
                    <p>Transactions réussies</p>
                    <div class="number">7</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="icon" style="background: linear-gradient(135deg, var(--warning), #e67e22);">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h3>En Attente</h3>
                    <p>Paiements en cours</p>
                    <div class="number">1</div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Historique des paiements -->
            <div class="col-lg-8">
                <div class="recent-activities">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3><i class="fas fa-history me-2"></i>Historique des Paiements</h3>
                        <div class="filter-actions">
                            <select class="form-select form-select-sm">
                                <option>Tous les statuts</option>
                                <option>Validés</option>
                                <option>En attente</option>
                                <option>Échoués</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Liste des paiements -->
                    <div class="payments-list">
                        <!-- Paiement 1 -->
                        <div class="activity-item payment-item">
                            <div class="activity-icon" style="background: linear-gradient(135deg, var(--success), #219653);">
                                <i class="fas fa-plane"></i>
                            </div>
                            <div class="activity-details flex-grow-1">
                                <h4>Vol Paris → New York</h4>
                                <p class="mb-1">Réservation JET1234 • 2 passagers</p>
                                <small class="text-muted">Référence: PAY-789456123</small>
                            </div>
                            <div class="payment-info text-end">
                                <div class="payment-amount">450€</div>
                                <div class="payment-date">15 Mars 2024</div>
                                <span class="trip-status status-confirmed">Validé</span>
                            </div>
                        </div>
                        
                        <!-- Paiement 2 -->
                        <div class="activity-item payment-item">
                            <div class="activity-icon" style="background: linear-gradient(135deg, var(--info), #2980b9);">
                                <i class="fas fa-hotel"></i>
                            </div>
                            <div class="activity-details flex-grow-1">
                                <h4>Hôtel Manhattan</h4>
                                <p class="mb-1">5 nuits • Chambre double</p>
                                <small class="text-muted">Référence: PAY-321654987</small>
                            </div>
                            <div class="payment-info text-end">
                                <div class="payment-amount">620€</div>
                                <div class="payment-date">12 Mars 2024</div>
                                <span class="trip-status status-confirmed">Validé</span>
                            </div>
                        </div>
                        
                        <!-- Paiement 3 -->
                        <div class="activity-item payment-item">
                            <div class="activity-icon" style="background: linear-gradient(135deg, var(--warning), #e67e22);">
                                <i class="fas fa-car"></i>
                            </div>
                            <div class="activity-details flex-grow-1">
                                <h4>Location de voiture</h4>
                                <p class="mb-1">New York • 7 jours • SUV</p>
                                <small class="text-muted">Référence: PAY-654987321</small>
                            </div>
                            <div class="payment-info text-end">
                                <div class="payment-amount">280€</div>
                                <div class="payment-date">10 Mars 2024</div>
                                <span class="trip-status status-pending">En attente</span>
                            </div>
                        </div>
                        
                        <!-- Paiement 4 -->
                        <div class="activity-item payment-item">
                            <div class="activity-icon" style="background: linear-gradient(135deg, var(--success), #219653);">
                                <i class="fas fa-plane"></i>
                            </div>
                            <div class="activity-details flex-grow-1">
                                <h4>Vol Lyon → Barcelone</h4>
                                <p class="mb-1">Réservation JET5678 • 1 passager</p>
                                <small class="text-muted">Référence: PAY-987321654</small>
                            </div>
                            <div class="payment-info text-end">
                                <div class="payment-amount">120€</div>
                                <div class="payment-date">05 Février 2024</div>
                                <span class="trip-status status-confirmed">Validé</span>
                            </div>
                        </div>
                        
                        <!-- Paiement 5 -->
                        <div class="activity-item payment-item">
                            <div class="activity-icon" style="background: linear-gradient(135deg, var(--success), #219653);">
                                <i class="fas fa-suitcase-rolling"></i>
                            </div>
                            <div class="activity-details flex-grow-1">
                                <h4>Assurance voyage</h4>
                                <p class="mb-1">Couverture Europe • 15 jours</p>
                                <small class="text-muted">Référence: PAY-147258369</small>
                            </div>
                            <div class="payment-info text-end">
                                <div class="payment-amount">85€</div>
                                <div class="payment-date">28 Janvier 2024</div>
                                <span class="trip-status status-confirmed">Validé</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Sidebar - Informations et actions -->
            <div class="col-lg-4">
                <!-- Méthodes de paiement -->
                <div class="quick-actions mb-4">
                    <h3><i class="fas fa-credit-card me-2"></i>Méthodes de Paiement</h3>
                    
                    <div class="payment-methods">
                        <div class="payment-method-card active">
                            <div class="method-icon">
                                <i class="fab fa-cc-visa"></i>
                            </div>
                            <div class="method-info">
                                <div class="method-name">Visa •••• 4512</div>
                                <div class="method-expiry">Expire 06/2025</div>
                            </div>
                            <div class="method-action">
                                <i class="fas fa-check text-success"></i>
                            </div>
                        </div>
                        
                        <div class="payment-method-card">
                            <div class="method-icon">
                                <i class="fab fa-cc-mastercard"></i>
                            </div>
                            <div class="method-info">
                                <div class="method-name">Mastercard •••• 7890</div>
                                <div class="method-expiry">Expire 03/2024</div>
                            </div>
                            <div class="method-action">
                                <button class="btn btn-sm btn-outline-primary">Sélectionner</button>
                            </div>
                        </div>
                        
                        <div class="text-center mt-3">
                            <button class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-plus me-1"></i>Ajouter une carte
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Aide et support -->
                <div class="quick-actions">
                    <h3><i class="fas fa-question-circle me-2"></i>Aide & Support</h3>
                    
                    <div class="action-buttons">
                        <a href="#" class="action-btn">
                            <i class="fas fa-file-invoice"></i>
                            <span>Facturation</span>
                        </a>
                        
                        <a href="#" class="action-btn">
                            <i class="fas fa-redo"></i>
                            <span>Remboursements</span>
                        </a>
                        
                        <a href="#" class="action-btn">
                            <i class="fas fa-shield-alt"></i>
                            <span>Sécurité</span>
                        </a>
                        
                        <a href="messages.php" class="action-btn">
                            <i class="fas fa-headset"></i>
                            <span>Support</span>
                        </a>
                    </div>
                    
                    <div class="support-info mt-3 p-3 bg-light rounded">
                        <h6 class="mb-2"><i class="fas fa-info-circle me-2"></i>Besoin d'aide ?</h6>
                        <p class="small text-muted mb-2">Problème de paiement ou question sur une transaction ?</p>
                        <a href="messages.php" class="btn btn-primary btn-sm w-100">
                            Contacter le support
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-grid">
                <div class="footer-column">
                    <h4>JetReserve</h4>
                    <ul class="footer-links">
                        <li><a href="#">À propos de nous</a></li>
                        <li><a href="#">Carrières</a></li>
                        <li><a href="#">Presse</a></li>
                        <li><a href="#">Blog</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h4>Informations</h4>
                    <ul class="footer-links">
                        <li><a href="#">Aide/FAQ</a></li>
                        <li><a href="#">Conditions générales</a></li>
                        <li><a href="#">Politique de confidentialité</a></li>
                        <li><a href="#">Cookies</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h4>Services</h4>
                    <ul class="footer-links">
                        <li><a href="#">Vols</a></li>
                        <li><a href="#">Hôtels</a></li>
                        <li><a href="#">Voitures</a></li>
                        <li><a href="#">Activités</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h4>Contact</h4>
                    <ul class="footer-links">
                        <li><a href="#">Service client</a></li>
                        <li><a href="#">Nous contacter</a></li>
                        <li><a href="#">Centres d'aide</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2023 JetReserve. Tous droits réservés.</p>
                <div class="social-icons">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function(){
            $(".owl-carousel").owlCarousel({
                items: 1,
                loop: true,
                autoplay: true,
                autoplayTimeout: 5000,
                autoplayHoverPause: true,
                nav: true,
                dots: true,
                animateOut: 'fadeOut',
                animateIn: 'fadeIn'
            });

            // Sélection des méthodes de paiement
            $('.payment-method-card').click(function() {
                $('.payment-method-card').removeClass('active');
                $(this).addClass('active');
            });
        });
    </script>
</body>
</html>