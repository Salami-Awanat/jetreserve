<?php
session_start();
include('../includes/connexion.php');

$message = "";

if (isset($_POST['connecter'])) {
    $email = trim($_POST['email']);
    $mdp = $_POST['motdepasse'];

    $query = $bdd->prepare("SELECT * FROM users WHERE email = ?");
    $query->execute([$email]);
    $user = $query->fetch();

    if ($user && password_verify($mdp, $user['motdepasse'])) {
        $_SESSION['id_user'] = $user['id'];
        $_SESSION['prenom'] = $user['prenom'];
        $_SESSION['nom'] = $user['nom'];
        $_SESSION['email'] = $user['email'];

        header("Location: index.php");
        exit;
    } else {
        $message = "❌ Email ou mot de passe incorrect.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion | JetReserve</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css">
    <link rel="stylesheet" href="style1.css">
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container">
            <div class="header-top">
                <a href="../index.php" class="logo">Jet<span>Reserve</span></a>
                <div class="auth-buttons">
                    <a href="connexion.php" class="btn btn-outline">Connexion</a>
                    <a href="inscription.php" class="btn btn-primary">Inscription</a>
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
                    <h2>Connectez-vous à votre compte</h2>
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
        
    <!-- Section de connexion -->
    <section class="auth-section">
        <div class="auth-container">
            <h2>Connectez-vous à votre compte <span style="color:#e74c3c;">JetReserve</span></h2>
            <?php if (!empty($message)) echo "<div class='message'>$message</div>"; ?>

            <form method="post">
                <label>Email :</label>
                <input type="email" name="email" placeholder="Votre adresse email" required>

                <label>Mot de passe :</label>
                <input type="password" name="motdepasse" placeholder="Votre mot de passe" required>

                <button type="submit" name="connecter">Se connecter</button>
            </form>

            <p>Pas encore de compte ? <a href="inscription.php">Inscrivez-vous</a></p>
            <p><a href="../index.php">⬅ Retour à l'accueil</a></p>
        </div>
    </section>

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