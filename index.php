<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TravelFinder - Trouvez vos voyages idéaux</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css">
    <link  rel="stylesheet" href="style.css">

</head>
<body>
    <!-- Header -->
    <header>
        <div class="container">
            <div class="header-top">
                <a href="#" class="logo">Travel<span>Finder</span></a>
                <div class="auth-buttons">
                    <button class="btn btn-outline">Connexion</button>
                    <button class="btn btn-primary">Inscription</button>
                </div>
            </div>
            <nav class="nav-menu">
                <ul class="nav-links">
                    <li><a href="#"><i class="fas fa-plane"></i> Vols</a></li>
                    <li><a href="#"><i class="fas fa-hotel"></i> Hôtels</a></li>
                    <li><a href="#"><i class="fas fa-suitcase"></i> Vols + Hôtels</a></li>
                    <li><a href="#"><i class="fas fa-car"></i> Voitures</a></li>
                    <li><a href="#"><i class="fas fa-ticket-alt"></i> Activités</a></li>
                    <li><a href="#"><i class="fas fa-percentage"></i> Offres</a></li>
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
                </div>
            </div>
            <div class="item banner-2">
                <div class="header-text">
                    <h2>Découvrez des destinations uniques </h2>
                </div>
            </div>
            <div class="item banner-3">
                <div class="header-text">
                    <h2>Explorez de nouveaux horizons</h2>
                </div>
            </div>
            <div class="item banner-4">
                <div class="header-text">
                    <h2>Voyagez en toute sérénité</h2>
                </div>
            </div>
            <div class="item banner-5">
                <div class="header-text">
                    <h2>Des offres exceptionnelles vous attendent</h2>
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
            <form id="searchForm">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="from"><i class="fas fa-plane-departure"></i> De</label>
                        <input type="text" id="from" class="form-control" placeholder="lieu de départ">
                    </div>
                    <div class="form-group">
                        <label for="to"><i class="fas fa-plane-arrival"></i> Vers</label>
                        <input type="text" id="to" class="form-control" placeholder="Destination">
                    </div>
                    <div class="form-group">
                        <label for="departure"><i class="far fa-calendar-alt"></i> Départ le</label>
                        <input type="date" id="departure" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="return"><i class="far fa-calendar-alt"></i> Retour le</label>
                        <input type="date" id="return" class="form-control">
                    </div>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="passengers"><i class="fas fa-user-friends"></i> Voyageurs</label>
                        <select id="passengers" class="form-control">
                            <option>1 voyageur</option>
                            <option>2 voyageurs</option>
                            <option>3 voyageurs</option>
                            <option>4 voyageurs</option>
                            <option>5+ voyageurs</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="class"><i class="fas fa-chair"></i> Classe</label>
                        <select id="class" class="form-control">
                            <option>Économique</option>
                            <option>Premium Éco</option>
                            <option>Affaires</option>
                            <option>Première</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <div class="checkbox-group" style="margin-top: 30px;">
                            <input type="checkbox" id="direct" name="direct">
                            <label for="direct">Vols directs uniquement</label>
                        </div>
                    </div>
                </div>
                <button type="submit" class="search-btn">
                    <i class="fas fa-search"></i> Rechercher un vol
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

        <!-- Offers Section -->
        <section class="offers">
            <h2 class="section-title">Destinations populaires</h2>
            <div class="offers-grid">
                <!-- Offer 1 -->
                <div class="card">
                    <img src="images/paris.jpg" alt="Paris">
                    <div class="card-content">
                        <h3>Paris, France</h3>
                        <p>Découvrez la ville lumière avec nos offres spéciales</p>
                        <div class="price">À partir de 129€</div>
                        <button class="btn btn-primary btn-sm">Voir l'offre</button>
                    </div>
                </div>
                
                <!-- Offer 2 -->
                <div class="card">
                    <img src="images/italy.jpg" alt="Rome">
                    <div class="card-content">
                        <h3>Rome, Italie</h3>
                        <p>Explorez la cité éternelle et ses trésors historiques</p>
                        <div class="price">À partir de 159€</div>
                        <button class="btn btn-primary btn-sm">Voir l'offre</button>
                    </div>
                </div>
                
                <!-- Offer 3 -->
                <div class="card">
                    <img src="images/inde.jpg" alt="Bali">
                    <div class="card-content">
                        <h3>Bali, Indonésie</h3>
                        <p>Évadez-vous vers cette île paradisiaque</p>
                        <div class="price">À partir de 699€</div>
                        <button class="btn btn-primary btn-sm">Voir l'offre</button>
                    </div>
                </div>
                
                <!-- Offer 4 -->
                <div class="card">
                    <img src="images/usa.jpg" alt="New York">
                    <div class="card-content">
                        <h3>New York, USA</h3>
                        <p>Vivez l'expérience new-yorkaise</p>
                        <div class="price">À partir de 459€</div>
                        <button class="btn btn-primary btn-sm">Voir l'offre</button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Madeira Section -->
        <section class="madeira-section">
            <h2 class="section-title">Découvrez nos destinations coup de cœur</h2>
            <div class="madeira-content">
                <div class="madeira-image">
                    <img src="images/madere.jpg" alt="Madère">
                </div>
                <div class="madeira-text">
                    <h3>Découvrez Madère</h3>
                    <p>Quand vous rêvez de journées sans fin, vous rêvez de Madère. Cette île portugaise située dans l'océan Atlantique offre des paysages à couper le souffle, un climat printanier toute l'année et une culture riche et authentique.</p>
                    <button class="btn btn-primary">Je réserve</button>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section class="features">
            <div class="container">
                <h2 class="section-title">Pourquoi choisir TravelFinder ?</h2>
                <div class="features-grid">
                    <div class="feature">
                        <div class="feature-icon">
                            <i class="fas fa-percentage"></i>
                        </div>
                        <h3>Meilleur prix garanti</h3>
                        <p>Nous comparons des centaines de sites pour vous trouver les meilleures offres</p>
                    </div>
                    <div class="feature">
                        <div class="feature-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h3>Paiement sécurisé</h3>
                        <p>Vos transactions sont protégées par les meilleures technologies de sécurité</p>
                    </div>
                    <div class="feature">
                        <div class="feature-icon">
                            <i class="fas fa-headset"></i>
                        </div>
                        <h3>Service client 24/7</h3>
                        <p>Notre équipe est disponible à tout moment pour vous assister</p>
                    </div>
                    <div class="feature">
                        <div class="feature-icon">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <h3>Application mobile</h3>
                        <p>Gérez vos réservations où que vous soyez avec notre app</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-grid">
                <div class="footer-column">
                    <h4>TravelFinder</h4>
                    <ul class="footer-links">
                        <li><a href="#">À propos de nous</a></li>
                        <li><a href="#">Carrières</a></li>
                        <li><a href="#">Presse</a></li>
                        <li><a href="#">Blog</a></li>
                        <li><a href="#">Aide</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h4>Produits</h4>
                    <ul class="footer-links">
                        <li><a href="#">Vols</a></li>
                        <li><a href="#">Hôtels</a></li>
                        <li><a href="#">Vols + Hôtels</a></li>
                        <li><a href="#">Location de voiture</a></li>
                        <li><a href="#">Activités</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h4>Informations</h4>
                    <ul class="footer-links">
                        <li><a href="#">Conditions générales</a></li>
                        <li><a href="#">Politique de confidentialité</a></li>
                        <li><a href="#">Cookies</a></li>
                        <li><a href="#">Mentions légales</a></li>
                        <li><a href="#">Plan du site</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h4>Contact</h4>
                    <ul class="footer-links">
                        <li><a href="#">Service client</a></li>
                        <li><a href="#">Nous contacter</a></li>
                        <li><a href="#">Centres d'aide</a></li>
                        <li><a href="#">Assistance</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2023 TravelFinder. Tous droits réservés.</p>
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
                
                // Animation de changement d'onglet
                document.getElementById('searchForm').style.opacity = '0.7';
                setTimeout(() => {
                    document.getElementById('searchForm').style.opacity = '1';
                }, 200);
            });
        });

        // Gestion de la soumission du formulaire
        document.getElementById('searchForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const from = document.getElementById('from').value;
            const to = document.getElementById('to').value;
            
            if (!from || !to) {
                alert('Veuillez remplir les champs de départ et destination');
                return;
            }
            
            // Simulation de recherche
            const searchBtn = document.querySelector('.search-btn');
            const originalText = searchBtn.innerHTML;
            searchBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Recherche en cours...';
            searchBtn.disabled = true;
            
            setTimeout(() => {
                searchBtn.innerHTML = originalText;
                searchBtn.disabled = false;
                alert(`Recherche de vols de ${from} à ${to} effectuée!`);
            }, 2000);
        });

        // Définir la date de départ à aujourd'hui et la date de retour à dans 7 jours
        const today = new Date();
        const nextWeek = new Date();
        nextWeek.setDate(today.getDate() + 7);
        
        document.getElementById('departure').valueAsDate = today;
        document.getElementById('return').valueAsDate = nextWeek;
    </script>
</body>
</html>