<?php
session_start();
include('../includes/db.php');

$message = "";

if (isset($_POST['connecter'])) {
    $email = trim($_POST['email']);
    $mdp = $_POST['password'] ?? '';

    // CORRECTION : Utiliser password_verify pour les mots de passe hashés
    $query = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $query->execute([$email]);
    $user = $query->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Vérifier le mot de passe (supporte les anciens mots de passe en clair et les nouveaux hashés)
        $password_valid = false;
        
        // Si le mot de passe est hashé (commence par $2y$)
        if (password_verify($mdp, $user['password'])) {
            $password_valid = true;
        } 
        // Si le mot de passe est en clair (pour la transition)
        elseif ($user['password'] === $mdp) {
            $password_valid = true;
            // Optionnel : re-hasher le mot de passe pour la sécurité
            $hashed_password = password_hash($mdp, PASSWORD_DEFAULT);
            $update_stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id_user = ?");
            $update_stmt->execute([$hashed_password, $user['id_user']]);
        }

        if ($password_valid) {
            // CORRECTION : Utiliser les mêmes noms que dans votre header
            $_SESSION['id_user'] = $user['id_user'];
            $_SESSION['nom'] = $user['nom'];
            $_SESSION['prenom'] = $user['prenom'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['statut'] = $user['statut'];

            if ($user['statut'] === 'inactif') {
                $message = "⚠️ Votre compte est inactif. Veuillez contacter l'administrateur.";
            } else {
                // Gestion des redirections
                $redirect_url = "index2.php"; // Par défaut pour les clients
                
                // CORRECTION : Chemin vers le dashboard admin
                if ($user['role'] === 'admin') {
                    $redirect_url = "../dashboard/index.php";
                }
                
                // Redirection vers la page demandée ou le tableau de bord
                if (isset($_GET['redirect']) && isset($_GET['id_vol'])) {
                    // Si l'utilisateur voulait voir les détails d'un vol
                    header("Location: ../vol_details.php?id_vol=" . $_GET['id_vol']);
                } elseif (isset($_GET['redirect'])) {
                    // Autres redirections
                    header("Location: ../" . $_GET['redirect'] . ".php");
                } else {
                    // Redirection normale
                    header("Location: " . $redirect_url);
                }
                exit;
            }
        } else {
            $message = "❌ Email ou mot de passe incorrect.";
        }
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
    <link rel="stylesheet" href="auth-styles.css">
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
    </div>
        
    <!-- Section de connexion -->
    <section class="auth-section">
        <div class="auth-container">
            <div class="text-center mb-4">
                <i class="fas fa-user-circle fa-3x text-primary mb-3"></i>
                <h2>Connectez-vous à votre compte <span style="color:#e74c3c;">JetReserve</span></h2>
                <p class="text-muted">Accédez à votre espace personnel</p>
                
                <!-- Message d'information pour les tests -->
                <?php if (isset($_GET['redirect'])): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        Veuillez vous connecter pour accéder à cette page.
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if (!empty($message)): ?>
                <div class="message alert alert-danger">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <form method="post">
                <div class="mb-4">
                    <label for="email">Adresse email :</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-envelope"></i>
                        </span>
                        <input type="email" id="email" name="email" 
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                               placeholder="exemple@email.com" required>
                    </div>
                </div>

                <div class="mb-4">
                    <div class="d-flex justify-content-between">
                        <label for="password">Mot de passe :</label>
                        <a href="#" class="small">Mot de passe oublié?</a>
                    </div>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-lock"></i>
                        </span>
                        <input type="password" id="password" name="password" placeholder="Votre mot de passe" required>
                    </div>
                </div>

                <div class="mb-4 form-check">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember">Se souvenir de moi</label>
                </div>

                <button type="submit" name="connecter">
                    <i class="fas fa-sign-in-alt me-2"></i>Se connecter
                </button>
            </form>

            <div class="form-footer mt-4">
                <p>Pas encore de compte ? <a href="inscription.php" class="fw-bold">Inscrivez-vous</a></p>
                <p><a href="../index.php">⬅ Retour à l'accueil</a></p>
            </div>
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