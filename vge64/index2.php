<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - JetReserve</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style1.css">
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
                    <li><a class="dropdown-item" href="index1.php"><i class="fas fa-home me-2"></i>Accueil client</a></li>
                    <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i>Mon profil</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="../index.php">
                        <i class="fas fa-power-off me-2"></i>Déconnexion
                    </a></li>
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

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <!-- Tableau de bord client -->
                <div class="container dashboard-container">
                    <!-- Carte de bienvenue -->
                    <div class="welcome-card">
                        <h2>Bonjour, <?php echo $_SESSION['prenom'] ?? 'Client'; ?> !</h2>
                        <p>Bienvenue dans votre espace personnel JetReserve. Gérez vos réservations, vos paiements et votre profil en toute simplicité.</p>
                    </div>

                    <div class="row">
                        <!-- Statistiques principales -->
                        <div class="col-md-3">
                            <div class="stats-card">
                                <div class="icon icon-reservations">
                                    <i class="fas fa-plane"></i>
                                </div>
                                <h3>Mes réservations</h3>
                                <p>Vos vols à venir et passés</p>
                                <div class="number">3</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stats-card">
                                <div class="icon icon-paiements">
                                    <i class="fas fa-credit-card"></i>
                                </div>
                                <h3>Mes paiements</h3>
                                <p>Historique des transactions</p>
                                <div class="number">5</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stats-card">
                                <div class="icon icon-messages">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <h3>Messages</h3>
                                <p>Support et réclamations</p>
                                <div class="number">2</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stats-card">
                                <div class="icon icon-profil">
                                    <i class="fas fa-user"></i>
                                </div>
                                <h3>Mon profil</h3>
                                <p>Informations personnelles</p>
                                <div class="number">1</div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Voyages à venir -->
                        <div class="col-lg-8">
                            <div class="upcoming-trips">
                                <h3><i class="fas fa-calendar-alt"></i> Mes prochains voyages</h3>
                                
                                <div class="trip-card">
                                    <div class="trip-header">
                                        <div class="trip-destination">Paris → New York</div>
                                        <div class="trip-date">15 Juin 2023</div>
                                    </div>
                                    <div class="trip-details">
                                        <div class="trip-info">Vol JET1234 • Économique</div>
                                        <span class="trip-status status-confirmed">Confirmé</span>
                                    </div>
                                </div>
                                
                                <div class="trip-card">
                                    <div class="trip-header">
                                        <div class="trip-destination">Lyon → Barcelone</div>
                                        <div class="trip-date">22 Juillet 2023</div>
                                    </div>
                                    <div class="trip-details">
                                        <div class="trip-info">Vol JET5678 • Affaires</div>
                                        <span class="trip-status status-pending">En attente</span>
                                    </div>
                                </div>
                                
                                <div class="trip-card">
                                    <div class="trip-header">
                                        <div class="trip-destination">Marseille → Rome</div>
                                        <div class="trip-date">10 Août 2023</div>
                                    </div>
                                    <div class="trip-details">
                                        <div class="trip-info">Vol JET9012 • Première</div>
                                        <span class="trip-status status-confirmed">Confirmé</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Activités récentes -->
                            <div class="recent-activities">
                                <h3><i class="fas fa-history"></i> Activités récentes</h3>
                                
                                <div class="activity-item">
                                    <div class="activity-icon" style="background: linear-gradient(135deg, var(--primary), #1a2530);">
                                        <i class="fas fa-plane"></i>
                                    </div>
                                    <div class="activity-details">
                                        <h4>Réservation confirmée</h4>
                                        <p>Vol Paris → New York (JET1234)</p>
                                    </div>
                                    <div class="activity-time">Il y a 2 jours</div>
                                </div>
                                
                                <div class="activity-item">
                                    <div class="activity-icon" style="background: linear-gradient(135deg, var(--success), #219653);">
                                        <i class="fas fa-credit-card"></i>
                                    </div>
                                    <div class="activity-details">
                                        <h4>Paiement effectué</h4>
                                        <p>Montant: 450€ - Carte Visa</p>
                                    </div>
                                    <div class="activity-time">Il y a 3 jours</div>
                                </div>
                                
                                <div class="activity-item">
                                    <div class="activity-icon" style="background: linear-gradient(135deg, var(--warning), #e67e22);">
                                        <i class="fas fa-envelope"></i>
                                    </div>
                                    <div class="activity-details">
                                        <h4>Message envoyé</h4>
                                        <p>Demande d'assistance bagages</p>
                                    </div>
                                    <div class="activity-time">Il y a 5 jours</div>
                                </div>
                                
                                <div class="activity-item">
                                    <div class="activity-icon" style="background: linear-gradient(135deg, var(--danger), #c0392b);">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div class="activity-details">
                                        <h4>Profil mis à jour</h4>
                                        <p>Informations personnelles modifiées</p>
                                    </div>
                                    <div class="activity-time">Il y a 1 semaine</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Actions rapides -->
                        <div class="col-lg-4">
                            <div class="quick-actions">
                                <h3><i class="fas fa-bolt"></i> Actions rapides</h3>
                                
                                <div class="action-buttons">
                                    <a href="reservations.php" class="action-btn">
                                        <i class="fas fa-plane"></i>
                                        <span>Mes réservations</span>
                                    </a>
                                    
                                    <a href="paiements.php" class="action-btn">
                                        <i class="fas fa-credit-card"></i>
                                        <span>Mes paiements</span>
                                    </a>
                                    
                                    <a href="profile.php" class="action-btn">
                                        <i class="fas fa-user"></i>
                                        <span>Mon profil</span>
                                    </a>
                                    
                                    <a href="messages.php" class="action-btn">
                                        <i class="fas fa-envelope"></i>
                                        <span>Support client</span>
                                    </a>
                                    
                                    <a href="#" class="action-btn">
                                        <i class="fas fa-download"></i>
                                        <span>Télécharger billet</span>
                                    </a>
                                    
                                    <a href="#" class="action-btn">
                                        <i class="fas fa-search"></i>
                                        <span>Rechercher un vol</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        });
    </script>
</body>
</html>
   