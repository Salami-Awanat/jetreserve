<?php
require_once 'includes/db.php';

// Démarrer la session pour vérifier si l'utilisateur est connecté
session_start();

// Récupérer les vols populaires depuis la base de données
$stmt = $pdo->prepare("SELECT v.*, c.nom_compagnie, c.code_compagnie 
                      FROM vols v 
                      JOIN compagnies c ON v.id_compagnie = c.id_compagnie 
                      WHERE v.date_depart > NOW() 
                      ORDER BY v.prix ASC 
                      LIMIT 4");
$stmt->execute();
$vols_populaires = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JetReserve - Trouvez vos voyages idéaux</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        /* Newsletter Section améliorée */
.newsletter-section {
    background: linear-gradient(135deg, #1e2942 0%, #060f22 100%);
    padding: 60px 0;
    margin: 50px 0;
    position: relative;
    overflow: hidden;
}

.newsletter-section::before {
    content: '';
    position: absolute;
    top: -50px;
    right: -50px;
    width: 200px;
    height: 200px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
}

.newsletter-section::after {
    content: '';
    position: absolute;
    bottom: -30px;
    left: -30px;
    width: 150px;
    height: 150px;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 50%;
}

.newsletter-section .container {
    position: relative;
    z-index: 2;
}

.newsletter-section .newsletter-content {
    text-align: center;
    max-width: 600px;
    margin: 0 auto;
}

.newsletter-section h3 {
    color: var(--white);
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 15px;
}

.newsletter-section p {
    color: rgba(255, 255, 255, 0.8);
    font-size: 1.1rem;
    margin-bottom: 30px;
    line-height: 1.6;
}

.newsletter-section .newsletter-form {
    display: flex;
    gap: 15px;
    max-width: 500px;
    margin: 0 auto;
}

.newsletter-section .newsletter-form input {
    flex: 1;
    padding: 15px 20px;
    border: none;
    border-radius: 8px;
    font-family: 'Poppins', sans-serif;
    font-size: 1rem;
    background: rgba(255, 255, 255, 0.95);
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.newsletter-section .newsletter-form input:focus {
    outline: none;
    background: var(--white);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
    transform: translateY(-2px);
}

.newsletter-section .newsletter-form input::placeholder {
    color: #999;
}

.newsletter-section .newsletter-form .btn {
    padding: 15px 30px;
    background: var(--danger);
    color: var(--white);
    border: none;
    border-radius: 8px;
    font-weight: 600;
    font-size: 1rem;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(231, 76, 60, 0.3);
    white-space: nowrap;
}

.newsletter-section .newsletter-form .btn:hover {
    background: #c0392b;
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(231, 76, 60, 0.4);
}

.newsletter-section .newsletter-form .btn:active {
    transform: translateY(-1px);
}

/* Responsive */
@media (max-width: 768px) {
    .newsletter-section {
        padding: 40px 20px;
        margin: 30px 0;
    }
    
    .newsletter-section h3 {
        font-size: 1.5rem;
    }
    
    .newsletter-section p {
        font-size: 1rem;
    }
    
    .newsletter-section .newsletter-form {
        flex-direction: column;
        gap: 12px;
    }
    
    .newsletter-section .newsletter-form .btn {
        width: 100%;
    }
}

@media (max-width: 480px) {
    .newsletter-section h3 {
        font-size: 1.3rem;
    }
    
    .newsletter-section .newsletter-form input {
        padding: 12px 15px;
    }
    
    .newsletter-section .newsletter-form .btn {
        padding: 12px 20px;
    }
}
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container">
            <div class="header-top">
                <a href="#" class="logo">Jet<span>Reserve</span></a>
                <div class="auth-buttons">
                    <?php if (isset($_SESSION['id_user'])): ?>
                        <!-- Si l'utilisateur est connecté -->
                        <a href="vge64/index2.php" class="btn btn-outline">Mon compte</a>
                        <a href="vge64/deconnexion.php" class="btn btn-primary">Déconnexion</a>
                    <?php else: ?>
                        <!-- Si l'utilisateur n'est pas connecté -->
                        <a href="vge64/connexion.php" class="btn btn-outline">Connexion</a>
                        <a href="vge64/inscription.php" class="btn btn-primary">Inscription</a>
                    <?php endif; ?>
                </div>
            </div>
            <nav class="nav-menu">
                <ul class="nav-links">
                    <li><a href="#"><i class="fas fa-home"></i> Accueil</a></li>
                    <li><a href="#"><i class="fas fa-plane-departure"></i>Vols</a></li>
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

    <!-- Bannière principale avec carousel -->
    <div class="main-banner-container">
        <div class="main-banner owl-carousel owl-theme">
            <div class="item banner-1">
                <div class="header-text">
                    <h2>Réservez votre vol pas cher</h2>
                    <p>Comparez les prix de plus de 600 compagnies aériennes</p>
                </div>
            </div>
            <div class="item banner-2">
                <div class="header-text">
                    <h2>Découvrez des destinations uniques</h2>
                    <p>Des offres exclusives vers les plus belles villes du monde</p>
                </div>
            </div>
            <div class="item banner-3">
                <div class="header-text">
                    <h2>Explorez de nouveaux horizons</h2>
                    <p>Voyagez en toute sécurité avec nos garanties</p>
                </div>
            </div>
            <div class="item banner-4">
                <div class="header-text">
                    <h2>Voyagez en toute sérénité</h2>
                    <p>Assistance 24h/24 et 7j/7 pendant votre voyage</p>
                </div>
            </div>
            <div class="item banner-5">
                <div class="header-text">
                    <h2>Des offres exceptionnelles vous attendent</h2>
                    <p>Jusqu'à -60% sur les vols dernière minute</p>
                </div>
            </div>
        </div>
        
        <!-- Search Form style Opodo intégré dans la bannière mais en dehors du carousel -->
        <div class="search-form">
            <div class="form-tabs">
                <div class="tab active"><i class="fas fa-exchange-alt"></i> Aller-retour</div>
                <div class="tab"><i class="fas fa-arrow-right"></i> Aller simple</div>
                <div class="tab"><i class="fas fa-route"></i> Multi-destinations</div>
            </div>
            <form id="searchForm" action="vols.php" method="GET">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="depart"><i class="fas fa-plane-departure"></i> Départ de</label>
                        <input type="text" id="depart" name="depart" class="form-control" placeholder="Ville ou aéroport de départ" required>
                    </div>
                    <div class="form-group">
                        <label for="arrivee"><i class="fas fa-plane-arrival"></i> Destination</label>
                        <input type="text" id="arrivee" name="arrivee" class="form-control" placeholder="Ville ou aéroport d'arrivée" required>
                    </div>
                    <div class="form-group">
                        <label for="date_depart"><i class="far fa-calendar-alt"></i> Date de départ</label>
                        <input type="date" id="date_depart" name="date_depart" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="passagers"><i class="fas fa-user-friends"></i> Voyageurs</label>
                        <select id="passagers" name="passagers" class="form-control">
                            <option value="1">1 voyageur</option>
                            <option value="2">2 voyageurs</option>
                            <option value="3">3 voyageurs</option>
                            <option value="4">4 voyageurs</option>
                            <option value="5">5+ voyageurs</option>
                        </select>
                    </div>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="classe"><i class="fas fa-chair"></i> Classe</label>
                        <select id="classe" name="classe" class="form-control">
                            <option value="économique">Économique</option>
                            <option value="affaires">Affaires</option>
                            <option value="première">Première</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <div class="checkbox-group" style="margin-top: 30px;">
                            <input type="checkbox" id="direct" name="direct" value="1">
                            <label for="direct">Vols directs uniquement</label>
                        </div>
                    </div>
                </div>
                <button type="submit" class="search-btn">
                    <i class="fas fa-search"></i> Rechercher des vols
                </button>
            </form>
            <div style="text-align: center; margin-top: 15px;">
                <a href="#" style="color: var(--info); text-decoration: none; font-weight: 600;">
                    <i class="fas fa-hotel"></i> Économisez jusqu'à 40% avec Vol + Hôtel
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <main class="container">

        <!-- Offers Section - Maintenant avec les vrais vols de la BDD -->
        <section class="offers">
            <h2 class="section-title">Vols Populaires</h2>
            <p style="text-align: center; color: var(--secondary); margin-bottom: 30px;">
                Découvrez nos vols les plus demandés avec des prix compétitifs
            </p>
            <div class="offers-grid">
                <?php foreach ($vols_populaires as $vol): ?>
                <!-- Offer Card -->
      <div class="card">
    <div class="card-badge"><?php echo htmlspecialchars($vol['classe']); ?></div>
    <div style="height: 200px; overflow: hidden;">
        <?php
        // Déterminer l'image en fonction des villes
        $depart = $vol['depart'];
        $arrivee = $vol['arrivee'];
        
        if ($depart == 'Paris' && $arrivee == 'Abidjan') {
            $image_file = 'paris.jpg';
        } elseif ($depart == 'Dubai' && $arrivee == 'Abidjan') {
            $image_file = 'dubai.jpg';
        } elseif ($depart == 'Istanbul' && $arrivee == 'Paris') {
            $image_file = 'inde.jpg';
        } else {
            $image_file = 'madere.jpg'; // Image par défaut
        }
        
        $image_path = "images/" . $image_file;
        
        if (file_exists($image_path)) {
            echo '<img src="' . $image_path . '" alt="' . htmlspecialchars($depart . ' → ' . $arrivee) . '" style="width: 100%; height: 100%; object-fit: cover;">';
        } else {
            echo '<div style="height: 100%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 2rem;">';
            echo '<i class="fas fa-plane"></i>';
            echo '</div>';
        }
        ?>
    </div>
    <div class="card-content">
        <h3><?php echo htmlspecialchars($vol['depart']); ?> → <?php echo htmlspecialchars($vol['arrivee']); ?></h3>
        <p><?php echo htmlspecialchars($vol['nom_compagnie']); ?> - Vol <?php echo htmlspecialchars($vol['code_compagnie'] . $vol['numero_vol']); ?></p>
        <p>Départ: <?php echo date('d M Y, H:i', strtotime($vol['date_depart'])); ?></p>
        <div class="price">À partir de <?php echo number_format($vol['prix'], 2, ',', ' '); ?>€</div>
        
        <?php if (isset($_SESSION['id_user'])): ?>
            <a href="vol_details.php?id_vol=<?php echo $vol['id_vol']; ?>" class="btn btn-primary btn-sm">
                <i class="fas fa-eye"></i> Voir les détails
            </a>
        <?php else: ?>
            <a href="vge64/connexion.php?redirect=vol_details&id_vol=<?php echo $vol['id_vol']; ?>" class="btn btn-primary btn-sm">
                <i class="fas fa-eye"></i> Voir les détails
            </a>
        <?php endif; ?>
    </div>
</div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Le reste de votre structure reste inchangé -->
        <!-- Madeira Section -->
        <section class="madeira-section">
            <h2 class="section-title">Destination coup de cœur de la semaine</h2>
            <div class="madeira-content">
                <div class="madeira-image">
                    <img src="images/madere.jpg" alt="Madère">
                    <div class="destination-tag">Meilleure destination 2024</div>
                </div>
                <div class="madeira-text">
                    <h3>Découvrez Madère, la perle de l'Atlantique</h3>
                    <p>Quand vous rêvez de journées sans fin, vous rêvez de Madère. Cette île portugaise située dans l'océan Atlantique offre des paysages à couper le souffle, un climat printanier toute l'année et une culture riche et authentique.</p>
                    <div class="destination-features">
                        <div class="feature-item">
                            <i class="fas fa-sun"></i>
                            <span>Climat idéal toute l'année</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-utensils"></i>
                            <span>Gastronomie exceptionnelle</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-hiking"></i>
                            <span>Randonnées spectaculaires</span>
                        </div>
                    </div>
                    <button class="btn btn-primary offer-btn" data-destination="Madère, Portugal">
                        <i class="fas fa-plane"></i> Réserver maintenant
                    </button>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section class="features">
            <div class="container">
                <h2 class="section-title">Pourquoi réserver avec JetReserve ?</h2>
                <p style="text-align: center; color: var(--secondary); margin-bottom: 40px;">
                    Des services premium pour une expérience de voyage exceptionnelle
                </p>
                <div class="features-grid">
                    <div class="feature">
                        <div class="feature-icon">
                            <i class="fas fa-percentage"></i>
                        </div>
                        <h3>Meilleur prix garanti</h3>
                        <p>Nous comparons des centaines de sites pour vous trouver les meilleures offres avec notre garantie prix</p>
                    </div>
                    <div class="feature">
                        <div class="feature-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h3>Paiement 100% sécurisé</h3>
                        <p>Vos transactions sont protégées par les dernières technologies de cryptage bancaire</p>
                    </div>
                    <div class="feature">
                        <div class="feature-icon">
                            <i class="fas fa-headset"></i>
                        </div>
                        <h3>Assistance premium 24/7</h3>
                        <p>Notre équipe dédiée est disponible à tout moment pour vous assister avant, pendant et après votre voyage</p>
                    </div>
                    <div class="feature">
                        <div class="feature-icon">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <h3>Application mobile</h3>
                        <p>Gérez vos réservations, recevez des alertes prix et vos billets électroniques sur mobile</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Newsletter Section -->
        <section class="newsletter-section">
            <div class="container">
                <div class="newsletter-content">
                    <h3>Ne manquez aucune offre exceptionnelle</h3>
                    <p>Inscrivez-vous à notre newsletter et soyez le premier informé de nos promotions exclusives</p>
                    <form class="newsletter-form">
                        <input type="email" placeholder="Votre adresse email" required>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> S'abonner
                        </button>
                    </form>
                </div>
            </div>
        </section>
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
                    <div class="footer-links">
                        <li><a href="#"><i class="fas fa-chevron-right"></i> À propos de nous</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right"></i> Carrières</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right"></i> Presse</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right"></i> Blog voyage</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right"></i> Centre d'aide</a></li>
                    </div>
                </div>
                <div class="footer-column">
                    <h4>Nos services</h4>
                    <div class="footer-links">
                        <li><a href="#"><i class="fas fa-chevron-right"></i> Vols réguliers</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right"></i> Vols + Hôtels</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right"></i> Location de voiture</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right"></i> Activités & excursions</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right"></i> Assurances voyage</a></li>
                    </div>
                </div>
                <div class="footer-column">
                    <h4>Informations légales</h4>
                    <div class="footer-links">
                        <li><a href="#"><i class="fas fa-chevron-right"></i> Conditions générales</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right"></i> Politique de confidentialité</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right"></i> Cookies</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right"></i> Mentions légales</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right"></i> Plan du site</a></li>
                    </div>
                </div>
                <div class="footer-column">
                    <h4>Contact & support</h4>
                    <div class="contact-info-footer">
                        <div class="contact-item">
                            <i class="fas fa-phone"></i>
                            <span>+33 1 23 45 67 89</span>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-envelope"></i>
                            <span>contact@jetreserve.com</span>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-clock"></i>
                            <span>Lun-Ven: 8h-20h • Sam-Dim: 9h-18h</span>
                        </div>
                    </div>
                    <div class="footer-links">
                        <li><a href="#"><i class="fas fa-chevron-right"></i> Service client</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right"></i> Nous contacter</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right"></i> Réclamations</a></li>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2023 JetReserve. Tous droits réservés. | SAS au capital de 1.000.000€ | RCS Paris 123 456 789</p>
                <div class="social-icons">
                    <a href="#" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" title="Twitter"><i class="fab fa-twitter"></i></a>
                    <a href="#" title="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="#" title="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#" title="YouTube"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>
    <script>
        // Initialisation du carousel
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

        // Script pour la navigation par onglets
        document.querySelectorAll('.tab').forEach(tab => {
            tab.addEventListener('click', () => {
                document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
                tab.classList.add('active');
            });
        });

        // Définir la date de départ à aujourd'hui
        const today = new Date();
        document.getElementById('date_depart').valueAsDate = today;

        // Fonctionnalité pour les boutons "Voir l'offre" - remplir automatiquement le formulaire
        document.querySelectorAll('.offer-btn').forEach(button => {
            button.addEventListener('click', function() {
                const destination = this.getAttribute('data-destination');
                document.getElementById('arrivee').value = destination;
                
                document.querySelector('.search-form').scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
                
                const toField = document.getElementById('arrivee');
                toField.focus();
                toField.style.borderColor = '#060f22ff'; 
                toField.style.boxShadow = '0 0 5px rgba(30, 41, 66, 0.5)'; 
                
                setTimeout(() => {
                    toField.style.borderColor = '';
                    toField.style.boxShadow = '';
                }, 2000);
            });
        });
    </script>
</body>
</html>