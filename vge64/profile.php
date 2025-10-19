<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil - JetReserve</title>
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
                    <h2>Bienvenu sur votre compte</h2>
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
    </div> <!-- Fermeture correcte de la div main-banner-container -->
        

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
                            <a class="nav-link" href="index2.php">
                                <i class="fas fa-home me-2"></i>
                                Accueil
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="mes_reservations.php">
                                <i class="fas fa-plane-departure me-2"></i>
                                Mes réservations
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="mes_paiement.php">
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
                            <a class="nav-link active" href="profile.php">
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
                <div class="profile-container">
                    <!-- En-tête du profil -->
                    <div class="profile-header">
                        <div class="profile-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <h1 class="profile-name"><?php echo $_SESSION['prenom'] ?? 'Prénom'; ?> <?php echo $_SESSION['nom'] ?? 'Nom'; ?></h1>
                        <p class="profile-email"><?php echo $_SESSION['email'] ?? 'email@exemple.com'; ?></p>
                        <span class="profile-badge">
                            <i class="fas fa-star me-1"></i>
                            Membre depuis <?php echo $_SESSION['date_inscription'] ?? '2023'; ?>
                        </span>
                    </div>

                    <div class="profile-content">
                        <!-- Informations personnelles -->
                        <div class="section-title">
                            <i class="fas fa-user-circle"></i>
                            <span>Informations personnelles</span>
                        </div>

                        <form id="profileForm">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="prenom">Prénom *</label>
                                    <input type="text" id="prenom" name="prenom" class="form-control" 
                                           value="<?php echo $_SESSION['prenom'] ?? 'Prénom'; ?>" required>
                                </div>

                                <div class="form-group">
                                    <label for="nom">Nom *</label>
                                    <input type="text" id="nom" name="nom" class="form-control" 
                                           value="<?php echo $_SESSION['nom'] ?? 'Nom'; ?>" required>
                                </div>

                                <div class="form-group">
                                    <label for="email">Adresse email *</label>
                                    <div class="input-with-icon">
                                        <input type="email" id="email" name="email" class="form-control" 
                                               value="<?php echo $_SESSION['email'] ?? 'email@exemple.com'; ?>" required>
                                        <i class="fas fa-envelope"></i>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="telephone">Téléphone</label>
                                    <div class="input-with-icon">
                                        <input type="tel" id="telephone" name="telephone" class="form-control" 
                                               value="<?php echo $_SESSION['telephone'] ?? '+33 1 23 45 67 89'; ?>">
                                        <i class="fas fa-phone"></i>
                                    </div>
                                </div>

                                <div class="form-group full-width">
                                    <label for="adresse">Adresse</label>
                                    <input type="text" id="adresse" name="adresse" class="form-control" 
                                           value="<?php echo $_SESSION['adresse'] ?? '123 Avenue des Champs-Élysées'; ?>">
                                </div>

                                <div class="form-group">
                                    <label for="ville">Ville</label>
                                    <input type="text" id="ville" name="ville" class="form-control" 
                                           value="<?php echo $_SESSION['ville'] ?? 'Paris'; ?>">
                                </div>

                                <div class="form-group">
                                    <label for="code_postal">Code postal</label>
                                    <input type="text" id="code_postal" name="code_postal" class="form-control" 
                                           value="<?php echo $_SESSION['code_postal'] ?? '75008'; ?>">
                                </div>

                                <div class="form-group">
                                    <label for="pays">Pays</label>
                                    <select id="pays" name="pays" class="form-control">
                                        <option value="FR" <?php echo ($_SESSION['pays'] ?? 'FR') == 'FR' ? 'selected' : ''; ?>>France</option>
                                        <option value="BE" <?php echo ($_SESSION['pays'] ?? '') == 'BE' ? 'selected' : ''; ?>>Belgique</option>
                                        <option value="CH" <?php echo ($_SESSION['pays'] ?? '') == 'CH' ? 'selected' : ''; ?>>Suisse</option>
                                        <option value="LU" <?php echo ($_SESSION['pays'] ?? '') == 'LU' ? 'selected' : ''; ?>>Luxembourg</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Préférences de voyage -->
                            <div class="section-title">
                                <i class="fas fa-cog"></i>
                                <span>Préférences de voyage</span>
                            </div>

                            <div class="preferences-grid">
                                <div class="preference-card">
                                    <h4><i class="fas fa-suitcase"></i> Bagages</h4>
                                    <div class="checkbox-group">
                                        <input type="checkbox" id="bagage_main" name="bagage_main" checked>
                                        <label for="bagage_main">Bagage à main inclus</label>
                                    </div>
                                    <div class="checkbox-group">
                                        <input type="checkbox" id="bagage_soute" name="bagage_soute">
                                        <label for="bagage_soute">Bagage en soute</label>
                                    </div>
                                </div>

                                <div class="preference-card">
                                    <h4><i class="fas fa-utensils"></i> Services à bord</h4>
                                    <div class="checkbox-group">
                                        <input type="checkbox" id="repas" name="repas" checked>
                                        <label for="repas">Repas inclus</label>
                                    </div>
                                    <div class="checkbox-group">
                                        <input type="checkbox" id="wifi" name="wifi">
                                        <label for="wifi">Wi-Fi à bord</label>
                                    </div>
                                </div>

                                <div class="preference-card">
                                    <h4><i class="fas fa-bell"></i> Notifications</h4>
                                    <div class="checkbox-group">
                                        <input type="checkbox" id="notif_email" name="notif_email" checked>
                                        <label for="notif_email">Notifications par email</label>
                                    </div>
                                    <div class="checkbox-group">
                                        <input type="checkbox" id="notif_sms" name="notif_sms">
                                        <label for="notif_sms">Notifications par SMS</label>
                                    </div>
                                    <div class="checkbox-group">
                                        <input type="checkbox" id="offres" name="offres" checked>
                                        <label for="offres">Offres promotionnelles</label>
                                    </div>
                                </div>
                            </div>

                            <!-- Statistiques du compte -->
                            <div class="section-title">
                                <i class="fas fa-chart-bar"></i>
                                <span>Statistiques de votre compte</span>
                            </div>

                            <div class="stats-grid">
                                <div class="stat-card">
                                    <div class="stat-icon">
                                        <i class="fas fa-plane"></i>
                                    </div>
                                    <div class="stat-number">12</div>
                                    <div class="stat-label">Vols réservés</div>
                                </div>

                                <div class="stat-card">
                                    <div class="stat-icon" style="background: var(--success);">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                    <div class="stat-number">8</div>
                                    <div class="stat-label">Vols effectués</div>
                                </div>

                                <div class="stat-card">
                                    <div class="stat-icon" style="background: var(--warning);">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                    <div class="stat-number">2</div>
                                    <div class="stat-label">Vols à venir</div>
                                </div>

                                <div class="stat-card">
                                    <div class="stat-icon" style="background: var(--secondary);">
                                        <i class="fas fa-coins"></i>
                                    </div>
                                    <div class="stat-number">1,250</div>
                                    <div class="stat-label">Points fidélité</div>
                                </div>
                            </div>

                            <!-- Boutons d'action -->
                            <div class="action-buttons">
                                <button type="button" class="btn btn-outline" id="changePasswordBtn">
                                    <i class="fas fa-key me-2"></i>Changer le mot de passe
                                </button>
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-save me-2"></i>Enregistrer les modifications
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal changement de mot de passe -->
    <div class="modal-overlay" id="passwordModal">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title">Changer le mot de passe</h3>
                <button class="modal-close" id="closePasswordModal">&times;</button>
            </div>
            <form id="passwordForm">
                <div class="form-group">
                    <label for="current_password">Mot de passe actuel *</label>
                    <input type="password" id="current_password" name="current_password" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="new_password">Nouveau mot de passe *</label>
                    <input type="password" id="new_password" name="new_password" class="form-control" required>
                    <small style="color: var(--secondary); font-size: 0.8rem;">
                        Minimum 8 caractères avec majuscule, minuscule et chiffre
                    </small>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirmer le nouveau mot de passe *</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                </div>

                <div class="action-buttons">
                    <button type="button" class="btn btn-outline" id="cancelPasswordChange">Annuler</button>
                    <button type="submit" class="btn btn-success">Changer le mot de passe</button>
                </div>
            </form>
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
        $(document).ready(function() {

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
            // Gestion du modal de changement de mot de passe
            $('#changePasswordBtn').on('click', function() {
                $('#passwordModal').addClass('active');
            });

            $('#closePasswordModal, #cancelPasswordChange').on('click', function() {
                $('#passwordModal').removeClass('active');
                $('#passwordForm')[0].reset();
            });

            // Validation du formulaire de profil
            $('#profileForm').on('submit', function(e) {
                e.preventDefault();
                
                if (validateProfileForm()) {
                    // Simulation d'enregistrement
                    showNotification('Profil mis à jour avec succès!', 'success');
                    
                    // En production, vous feriez un appel AJAX ici
                    setTimeout(() => {
                        // Rechargement de la page pour voir les changements
                        location.reload();
                    }, 2000);
                }
            });

            // Validation du formulaire de mot de passe
            $('#passwordForm').on('submit', function(e) {
                e.preventDefault();
                
                if (validatePasswordForm()) {
                    // Simulation de changement de mot de passe
                    showNotification('Mot de passe changé avec succès!', 'success');
                    
                    setTimeout(() => {
                        $('#passwordModal').removeClass('active');
                        $('#passwordForm')[0].reset();
                    }, 2000);
                }
            });

            // Fonction de validation du formulaire de profil
            function validateProfileForm() {
                const email = $('#email').val();
                const telephone = $('#telephone').val();
                
                // Validation email
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email)) {
                    showNotification('Veuillez entrer une adresse email valide', 'error');
                    return false;
                }
                
                // Validation téléphone (optionnelle)
                if (telephone && !/^[\+]?[0-9\s\-\(\)]{10,}$/.test(telephone)) {
                    showNotification('Veuillez entrer un numéro de téléphone valide', 'error');
                    return false;
                }
                
                return true;
            }

            // Fonction de validation du formulaire de mot de passe
            function validatePasswordForm() {
                const currentPassword = $('#current_password').val();
                const newPassword = $('#new_password').val();
                const confirmPassword = $('#confirm_password').val();
                
                // Vérification de la force du mot de passe
                const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/;
                
                if (!passwordRegex.test(newPassword)) {
                    showNotification('Le nouveau mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule et un chiffre', 'error');
                    return false;
                }
                
                if (newPassword !== confirmPassword) {
                    showNotification('Les mots de passe ne correspondent pas', 'error');
                    return false;
                }
                
                // Simulation de vérification de l'ancien mot de passe
                if (currentPassword.length < 1) {
                    showNotification('Veuillez entrer votre mot de passe actuel', 'error');
                    return false;
                }
                
                return true;
            }

            // Fonction d'affichage des notifications
            function showNotification(message, type) {
                const notification = $('<div class="notification"></div>');
                const bgColor = type === 'success' ? '#10b981' : '#ef4444';
                
                notification.css({
                    position: 'fixed',
                    top: '20px',
                    right: '20px',
                    background: bgColor,
                    color: 'white',
                    padding: '15px 20px',
                    borderRadius: '8px',
                    zIndex: '10000',
                    boxShadow: '0 4px 12px rgba(0,0,0,0.15)',
                    animation: 'slideInRight 0.3s ease-out',
                    maxWidth: '400px',
                    fontWeight: '500'
                });
                
                notification.text(message);
                $('body').append(notification);
                
                setTimeout(() => {
                    notification.animate({
                        right: '-500px'
                    }, 300, function() {
                        $(this).remove();
                    });
                }, 5000);
            }

            // Ajout des styles d'animation
            $('head').append(`
                <style>
                    @keyframes slideInRight {
                        from {
                            transform: translateX(100%);
                            opacity: 0;
                        }
                        to {
                            transform: translateX(0);
                            opacity: 1;
                        }
                    }
                </style>
            `);
        });
    </script>
</body>
</html>