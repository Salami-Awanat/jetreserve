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
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container">
            <div class="header-top">
                <a href="#" class="logo">Jet<span>Reserve</span></a>
                <div class="auth-buttons">
                    <a href="vge64/connexion.php" class="btn btn-outline">Connexion</a>
                    <a href="vge64/inscription.php" class="btn btn-primary">Inscription</a>
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
            <form id="searchForm">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="from"><i class="fas fa-plane-departure"></i> Départ de</label>
                        <input type="text" id="from" class="form-control" placeholder="Ville ou aéroport de départ">
                    </div>
                    <div class="form-group">
                        <label for="to"><i class="fas fa-plane-arrival"></i> Destination</label>
                        <input type="text" id="to" class="form-control" placeholder="Ville ou aéroport d'arrivée">
                    </div>
                    <div class="form-group">
                        <label for="departure"><i class="far fa-calendar-alt"></i> Date de départ</label>
                        <input type="date" id="departure" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="return"><i class="far fa-calendar-alt"></i> Date de retour</label>
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

        <!-- Offers Section -->
        <section class="offers">
            <h2 class="section-title">Destinations tendance</h2>
            <p style="text-align: center; color: var(--secondary); margin-bottom: 30px;">
                Découvrez nos destinations les plus populaires avec des prix imbattables
            </p>
            <div class="offers-grid">
                <!-- Offer 1 -->
                <div class="card">
                    <div class="card-badge">Populaire</div>
                    <img src="images/paris.jpg" alt="Paris">
                    <div class="card-content">
                        <h3>Paris, France</h3>
                        <p>Découvrez la ville lumière avec nos offres spéciales</p>
                        <div class="price">À partir de 129€</div>
                        <button class="btn btn-primary btn-sm offer-btn" data-destination="Paris, France">
                            <i class="fas fa-eye"></i> Voir les vols
                        </button>
                    </div>
                </div>
                
                <!-- Offer 2 -->
                <div class="card">
                    <div class="card-badge">Coup de cœur</div>
                    <img src="images/italy.jpg" alt="Rome">
                    <div class="card-content">
                        <h3>Rome, Italie</h3>
                        <p>Explorez la cité éternelle et ses trésors historiques</p>
                        <div class="price">À partir de 159€</div>
                        <button class="btn btn-primary btn-sm offer-btn" data-destination="Rome, Italie">
                            <i class="fas fa-eye"></i> Voir les vols
                        </button>
                    </div>
                </div>
                
                <!-- Offer 3 -->
                <div class="card">
                    <div class="card-badge">Exotique</div>
                    <img src="images/inde.jpg" alt="Bali">
                    <div class="card-content">
                        <h3>Bali, Indonésie</h3>
                        <p>Évadez-vous vers cette île paradisiaque</p>
                        <div class="price">À partir de 699€</div>
                        <button class="btn btn-primary btn-sm offer-btn" data-destination="Bali, Indonésie">
                            <i class="fas fa-eye"></i> Voir les vols
                        </button>
                    </div>
                </div>
                
                <!-- Offer 4 -->
                <div class="card">
                    <div class="card-badge">Ville</div>
                    <img src="images/usa.jpg" alt="New York">
                    <div class="card-content">
                        <h3>New York, USA</h3>
                        <p>Vivez l'expérience new-yorkaise</p>
                        <div class="price">À partir de 459€</div>
                        <button class="btn btn-primary btn-sm offer-btn" data-destination="New York, USA">
                            <i class="fas fa-eye"></i> Voir les vols
                        </button>
                    </div>
                </div>
            </div>
        </section>

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
            const departure = document.getElementById('departure').value;
            const passengers = document.getElementById('passengers').value;
            const flightClass = document.getElementById('class').value;
            
            if (!from || !to) {
                alert('Veuillez remplir les champs de départ et destination');
                return;
            }
            
            // Simulation de recherche
            const searchBtn = document.querySelector('.search-btn');
            const originalText = searchBtn.innerHTML;
            searchBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Recherche en cours...';
            searchBtn.disabled = true;
            
            // Simuler un délai de recherche
            setTimeout(() => {
                searchBtn.innerHTML = originalText;
                searchBtn.disabled = false;
                
                // Afficher les résultats de recherche
                displayFlightResults(from, to, departure, passengers, flightClass);
            }, 2000);
        });

        // Définir la date de départ à aujourd'hui et la date de retour à dans 7 jours
        const today = new Date();
        const nextWeek = new Date();
        nextWeek.setDate(today.getDate() + 7);
        
        document.getElementById('departure').valueAsDate = today;
        document.getElementById('return').valueAsDate = nextWeek;

        // Fonctionnalité pour les boutons "Voir l'offre" - remplir automatiquement le formulaire
        document.querySelectorAll('.offer-btn').forEach(button => {
            button.addEventListener('click', function() {
                // Récupérer la destination depuis l'attribut data-destination
                const destination = this.getAttribute('data-destination');
                
                // Remplir automatiquement le champ "Vers" avec la destination
                document.getElementById('to').value = destination;
                
                // Faire défiler jusqu'au formulaire de recherche
                document.querySelector('.search-form').scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
                
                // Mettre en évidence le champ "Vers" pour indiquer qu'il a été pré-rempli
                const toField = document.getElementById('to');
                toField.focus();
                toField.style.borderColor = '#060f22ff'; 
                toField.style.boxShadow = '0 0 5px rgba(30, 41, 66, 0.5)'; 
                
                // Retirer la mise en évidence après 2 secondes
                setTimeout(() => {
                    toField.style.borderColor = '';
                    toField.style.boxShadow = '';
                }, 2000);
                
                // Afficher un message de confirmation professionnel
                const confirmation = document.createElement('div');
                confirmation.textContent = `✨ Destination "${destination}" ajoutée à votre recherche !`;
                confirmation.style.cssText = `
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    background: #2f3543ff; 
                    color: white;
                    padding: 15px 20px;
                    border-radius: 8px;
                    z-index: 1000;
                    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                    animation: fadeInOut 3s ease-in-out;
                    font-weight: 500;
                    border-left: 4px solid #2563eb;
                `;
                document.body.appendChild(confirmation);
                
                // Supprimer le message après 3 secondes
                setTimeout(() => {
                    confirmation.remove();
                }, 3000);
            });
        });

        // Fonction pour afficher les résultats de vols
        function displayFlightResults(from, to, departure, passengers, flightClass) {
            // Créer un conteneur pour les résultats
            const resultsContainer = document.createElement('div');
            resultsContainer.id = 'flightResults';
            resultsContainer.style.cssText = `
                background: white;
                border-radius: 12px;
                box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
                padding: 25px;
                margin: 30px 0;
                max-width: 1100px;
                margin-left: auto;
                margin-right: auto;
            `;
            
            // Titre des résultats
            const title = document.createElement('h2');
            title.textContent = `✈️ Vols de ${from} à ${to}`;
            title.style.cssText = `
                color: #2563eb;
                margin-bottom: 10px;
                text-align: center;
                font-size: 24px;
            `;
            
            const subtitle = document.createElement('p');
            subtitle.textContent = `Nous avons trouvé les meilleures options pour votre voyage`;
            subtitle.style.cssText = `
                color: #64748b;
                text-align: center;
                margin-bottom: 20px;
                font-size: 14px;
            `;
            
            resultsContainer.appendChild(title);
            resultsContainer.appendChild(subtitle);
            
            // Générer des vols fictifs
            const flights = generateMockFlights(from, to, departure, flightClass);
            
            // Afficher chaque vol
            flights.forEach(flight => {
                const flightElement = createFlightElement(flight, passengers);
                resultsContainer.appendChild(flightElement);
            });
            
            // Supprimer les résultats précédents s'ils existent
            const existingResults = document.getElementById('flightResults');
            if (existingResults) {
                existingResults.remove();
            }
            
            // Insérer les résultats après le formulaire de recherche
            const searchForm = document.querySelector('.search-form');
            searchForm.parentNode.insertBefore(resultsContainer, searchForm.nextSibling);
            
            // Faire défiler jusqu'aux résultats
            resultsContainer.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }

        // Fonction pour générer des vols fictifs
        function generateMockFlights(from, to, departure, flightClass) {
            const airlines = [
                { name: 'Air France', code: 'AF', logo: '✈️' },
                { name: 'Air line', code: 'AL', logo: '✈️' },
                { name: 'Air Cote dIvoire', code: 'AC', logo: '✈️' },
                { name: 'Emirates', code: 'EK', logo: '✈️' },
                { name: 'Air Maroc', code: 'AM', logo: '✈️' },
                { name: 'Iberia', code: 'IB', logo: '✈️' }
            ];
            
            const flights = [];
            
            // Générer 4-6 vols aléatoires
            const numFlights = Math.floor(Math.random() * 3) + 4;
            
            for (let i = 0; i < numFlights; i++) {
                const airline = airlines[Math.floor(Math.random() * airlines.length)];
                const departureTime = new Date(departure);
                
                // Heure de départ aléatoire entre 6h et 22h
                const departureHour = Math.floor(Math.random() * 16) + 6;
                departureTime.setHours(departureHour, Math.floor(Math.random() * 60));
                
                // Durée de vol aléatoire entre 1h et 8h
                const durationHours = Math.floor(Math.random() * 7) + 1;
                const durationMinutes = Math.floor(Math.random() * 60);
                
                const arrivalTime = new Date(departureTime);
                arrivalTime.setHours(departureTime.getHours() + durationHours, departureTime.getMinutes() + durationMinutes);
                
                // Prix de base selon la classe
                let basePrice;
                switch(flightClass) {
                    case 'Économique':
                        basePrice = 150 + Math.random() * 200;
                        break;
                    case 'Premium Éco':
                        basePrice = 300 + Math.random() * 200;
                        break;
                    case 'Affaires':
                        basePrice = 600 + Math.random() * 400;
                        break;
                    case 'Première':
                        basePrice = 1000 + Math.random() * 600;
                        break;
                    default:
                        basePrice = 150 + Math.random() * 200;
                }
                
                flights.push({
                    airline: airline.name,
                    airlineCode: airline.code,
                    airlineLogo: airline.logo,
                    flightNumber: `${airline.code}${Math.floor(Math.random() * 2000) + 100}`,
                    departureTime: departureTime,
                    arrivalTime: arrivalTime,
                    duration: { hours: durationHours, minutes: durationMinutes },
                    price: Math.round(basePrice),
                    class: flightClass,
                    stops: Math.random() > 0.7 ? 1 : 0, // 30% de chance d'avoir une escale
                    featured: i === 0 // Premier vol est recommandé
                });
            }
            
            // Trier par prix
            return flights.sort((a, b) => a.price - b.price);
        }

        // Fonction pour créer un élément de vol
        // Fonction pour créer un élément de vol
function createFlightElement(flight, passengers) {
    const flightElement = document.createElement('div');
    flightElement.style.cssText = `
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 15px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: all 0.3s ease;
        position: relative;
    `;
    
    if (flight.featured) {
        flightElement.style.borderLeft = '4px solid #2563eb';
        flightElement.style.background = 'linear-gradient(135deg, #f8fafc, white)';
    }
    
    flightElement.onmouseenter = () => {
        flightElement.style.boxShadow = '0 4px 12px rgba(0,0,0,0.1)';
        flightElement.style.transform = 'translateY(-2px)';
    };
    
    flightElement.onmouseleave = () => {
        flightElement.style.boxShadow = 'none';
        flightElement.style.transform = 'translateY(0)';
    };
    
    // Format des heures
    const formatTime = (date) => {
        return date.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
    };
    
    // Badge recommandé
    if (flight.featured) {
        const badge = document.createElement('div');
        badge.textContent = '⭐ Recommandé';
        badge.style.cssText = `
            position: absolute;
            top: -10px;
            left: 20px;
            background: #2563eb;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        `;
        flightElement.appendChild(badge);
    }
    
    // Informations du vol (gauche)
    const flightInfo = document.createElement('div');
    flightInfo.style.cssText = `
        display: flex;
        align-items: center;
        gap: 20px;
    `;
    
    // Compagnie aérienne
    const airlineDiv = document.createElement('div');
    airlineDiv.innerHTML = `
        <div style="font-weight: bold; font-size: 16px;">${flight.airline}</div>
        <div style="color: #64748b; font-size: 14px;">${flight.flightNumber}</div>
    `;
    
    // Horaires
    const scheduleDiv = document.createElement('div');
    scheduleDiv.innerHTML = `
        <div style="display: flex; align-items: center; gap: 10px;">
            <div>
                <div style="font-weight: bold; font-size: 18px;">${formatTime(flight.departureTime)}</div>
                <div style="color: #64748b; font-size: 12px;">${document.getElementById('from').value || 'Départ'}</div>
            </div>
            <div style="text-align: center;">
                <div style="color: #64748b; font-size: 12px;">${flight.duration.hours}h${flight.duration.minutes.toString().padStart(2, '0')}</div>
                <div style="border-top: 2px solid #e2e8f0; width: 80px; margin: 5px 0;"></div>
                <div style="color: #64748b; font-size: 12px;">${flight.stops === 0 ? 'Direct' : '1 escale'}</div>
            </div>
            <div>
                <div style="font-weight: bold; font-size: 18px;">${formatTime(flight.arrivalTime)}</div>
                <div style="color: #64748b; font-size: 12px;">${document.getElementById('to').value || 'Arrivée'}</div>
            </div>
        </div>
    `;
    
    flightInfo.appendChild(airlineDiv);
    flightInfo.appendChild(scheduleDiv);
    
    // Prix et bouton de réservation (droite)
    const bookingDiv = document.createElement('div');
    bookingDiv.style.cssText = `
        text-align: right;
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 10px;
    `;
    
    const priceDiv = document.createElement('div');
    priceDiv.innerHTML = `
        <div style="font-size: 24px; font-weight: bold; color: #131a29ff;">${flight.price}€</div>
        <div style="color: #151b24ff; font-size: 12px;">${flight.class} • Par personne</div>
    `;
    
    const bookButton = document.createElement('button');
    bookButton.textContent = 'Réserver ce vol';
    bookButton.style.cssText = `
        background: #0d121cff;
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 6px;
        font-weight: bold;
        cursor: pointer;
        transition: background 0.3s ease;
        display: flex;
        align-items: center;
        gap: 8px;
    `;
    
    bookButton.onmouseenter = () => {
        bookButton.style.background = '#0f1525ff';
    };
    
    bookButton.onmouseleave = () => {
        bookButton.style.background = '#0c121eff';
    };
    
    // MODIFICATION : Redirection vers reservation.php avec les paramètres
    bookButton.onclick = () => {
        // Récupérer les données du formulaire
        const from = document.getElementById('from').value;
        const to = document.getElementById('to').value;
        const departure = document.getElementById('departure').value;
        const returnDate = document.getElementById('return').value;
        const passengers = document.getElementById('passengers').value;
        const flightClass = document.getElementById('class').value;
        
        // Créer les paramètres URL
        const params = new URLSearchParams({
            airline: flight.airline,
            flight_number: flight.flightNumber,
            from: from,
            to: to,
            departure_date: departure,
            return_date: returnDate,
            passengers: passengers,
            class: flightClass,
            price: flight.price
        });
        
        // Redirection vers la page de réservation
        window.location.href = 'reservation.php?' + params.toString();
    };
    
    bookingDiv.appendChild(priceDiv);
    bookingDiv.appendChild(bookButton);
    
    flightElement.appendChild(flightInfo);
    flightElement.appendChild(bookingDiv);
    
    return flightElement;
}
        // Ajouter le style pour l'animation du message
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fadeInOut {
                0% { opacity: 0; transform: translateY(-20px); }
                20% { opacity: 1; transform: translateY(0); }
                80% { opacity: 1; transform: translateY(0); }
                100% { opacity: 0; transform: translateY(-20px); }
            }
            
            .card-badge {
                position: absolute;
                top: 10px;
                right: 10px;
                background: #2563eb;
                color: white;
                padding: 4px 8px;
                border-radius: 4px;
                font-size: 12px;
                font-weight: 600;
                z-index: 2;
            }
            
            .destination-tag {
                position: absolute;
                bottom: 20px;
                left: 20px;
                background: rgba(37, 99, 235, 0.9);
                color: white;
                padding: 8px 12px;
                border-radius: 4px;
                font-weight: 600;
            }
            
            .destination-features {
                display: flex;
                flex-direction: column;
                gap: 10px;
                margin: 20px 0;
            }
            
            .feature-item {
                display: flex;
                align-items: center;
                gap: 10px;
                color: #64748b;
            }
            
            .feature-item i {
                color: #2563eb;
                width: 20px;
            }
            
            .newsletter-section {
                background: linear-gradient(135deg, #1e293b, #374151);
                color: white;
                padding: 50px 0;
                margin-top: 50px;
                border-radius: 12px;
            }
            
            .newsletter-content {
                text-align: center;
                max-width: 600px;
                margin: 0 auto;
            }
            
            .newsletter-content h3 {
                font-size: 28px;
                margin-bottom: 15px;
            }
            
            .newsletter-form {
                display: flex;
                gap: 10px;
                margin-top: 20px;
            }
            
            .newsletter-form input {
                flex: 1;
                padding: 12px 15px;
                border: none;
                border-radius: 6px;
                font-size: 14px;
            }
            
            .contact-info-footer {
                margin-bottom: 20px;
            }
            
            .contact-item {
                display: flex;
                align-items: center;
                gap: 10px;
                margin-bottom: 8px;
                color: #cbd5e1;
                font-size: 14px;
            }
            
            .contact-item i {
                width: 16px;
                color: #2563eb;
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>