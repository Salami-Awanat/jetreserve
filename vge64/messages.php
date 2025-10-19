<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['id_user'])) {
    header('Location: connexion.php');
    exit;
}

// Récupérer tous les emails de l'utilisateur
try {
    $stmt = $pdo->prepare("
        SELECT e.*, 
               DATE_FORMAT(e.date_envoi, '%d/%m/%Y %H:%i') as date_formatee,
               DATE_FORMAT(e.date_envoi, '%H:%i') as heure_envoi
        FROM emails e
        WHERE e.id_user = ?
        ORDER BY e.date_envoi DESC
    ");
    $stmt->execute([$_SESSION['id_user']]);
    $emails = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $emails = [];
}

// Récupérer les emails non lus
try {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as nb_non_lus 
        FROM emails 
        WHERE id_user = ? AND statut = 'envoyé'
    ");
    $stmt->execute([$_SESSION['id_user']]);
    $non_lus = $stmt->fetch(PDO::FETCH_ASSOC)['nb_non_lus'];
} catch (PDOException $e) {
    $non_lus = 0;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - JetReserve</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style1.css">
    <style>
        /* ===== STYLES POUR LA PAGE MESSAGES ===== */

/* Container des messages */
.messages-container {
    background: var(--white);
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    overflow: hidden;
    margin: 2rem 0;
    border: 1px solid var(--border-color);
}

.messages-header {
    padding: 1.5rem;
    border-bottom: 1px solid var(--border-color);
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: var(--light-gray);
}

.messages-header h2 {
    margin: 0;
    color: var(--dark);
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 1.4rem;
    font-weight: 600;
}

.email-count {
    background: var(--info);
    color: var(--white);
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 500;
}

/* Liste des emails */
.emails-list {
    max-height: 600px;
    overflow-y: auto;
}

.email-item {
    padding: 1.5rem;
    border-bottom: 1px solid var(--border-color);
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    gap: 1rem;
    background: var(--white);
}

.email-item:hover {
    background: var(--light-gray);
    transform: translateX(3px);
}

.email-item.unread {
    background: #e0f2fe;
    border-left: 3px solid var(--info);
}

.email-item.active {
    background: var(--light-gray);
    border-left: 3px solid var(--primary);
}

.email-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: var(--info);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--white);
    font-weight: 600;
    font-size: 1.2rem;
    flex-shrink: 0;
}

.email-info {
    flex: 1;
    min-width: 0;
}

.email-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
}

.email-sender {
    font-weight: 600;
    color: var(--dark);
    font-size: 1rem;
}

.email-time {
    font-size: 0.8rem;
    color: var(--gray);
}

.email-subject {
    font-weight: 600;
    color: var(--dark);
    font-size: 0.95rem;
    margin-bottom: 0.25rem;
}

.email-preview {
    font-size: 0.85rem;
    color: var(--gray);
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.email-type {
    display: inline-block;
    padding: 0.2rem 0.6rem;
    border-radius: 12px;
    font-size: 0.7rem;
    font-weight: 500;
    margin-top: 0.5rem;
}

.type-confirmation { background: #d1fae5; color: #065f46; }
.type-recu { background: #fef3c7; color: #92400e; }
.type-ticket { background: #e0e7ff; color: #3730a3; }

/* Zone de lecture */
.email-reader {
    flex: 1;
    display: flex;
    flex-direction: column;
    background: var(--white);
}

.email-reader-header {
    padding: 1.5rem;
    border-bottom: 1px solid var(--border-color);
    background: var(--light-gray);
}

.email-reader-subject {
    font-size: 1.3rem;
    font-weight: 600;
    color: var(--dark);
    margin-bottom: 1rem;
}

.email-reader-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}

.email-sender-info {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.email-sender-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: var(--info);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--white);
    font-weight: 600;
    flex-shrink: 0;
}

.email-sender-details h4 {
    margin: 0;
    font-size: 1rem;
    color: var(--dark);
    font-weight: 600;
}

.email-sender-details p {
    margin: 0;
    font-size: 0.8rem;
    color: var(--gray);
}

.email-date {
    font-size: 0.9rem;
    color: var(--gray);
}

.email-content {
    flex: 1;
    padding: 2rem;
    overflow-y: auto;
    line-height: 1.6;
    color: var(--dark);
}

.email-content p {
    margin-bottom: 1rem;
}

.email-actions {
    padding: 1rem 1.5rem;
    border-top: 1px solid var(--border-color);
    display: flex;
    gap: 1rem;
    background: var(--white);
}

.email-action-btn {
    background: var(--light-gray);
    border: 1px solid var(--border-color);
    padding: 0.5rem 1rem;
    border-radius: 6px;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.email-action-btn:hover {
    background: var(--info);
    color: var(--white);
    border-color: var(--info);
}

/* État vide */
.empty-state {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 3rem;
    color: var(--gray);
}

.empty-state i {
    font-size: 4rem;
    margin-bottom: 1rem;
    color: var(--border-color);
}

.empty-state h3 {
    margin-bottom: 0.5rem;
    color: var(--dark);
}

/* Responsive */
@media (max-width: 768px) {
    .messages-content {
        flex-direction: column;
    }
    
    .emails-list {
        width: 100%;
        border-right: none;
        border-bottom: 1px solid var(--border-color);
        max-height: 400px;
    }
    
    .email-item {
        padding: 1rem;
    }
    
    .email-avatar {
        width: 40px;
        height: 40px;
        font-size: 1rem;
    }
    
    .email-reader-header {
        padding: 1rem;
    }
    
    .email-content {
        padding: 1rem;
    }
    
    .email-actions {
        padding: 1rem;
    }
}

@media (max-width: 480px) {
    .messages-header {
        flex-direction: column;
        gap: 1rem;
        align-items: flex-start;
    }
    
    .email-reader-meta {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
    
    .email-sender-info {
        width: 100%;
    }
}
    </style>
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
                            <li><a class="dropdown-item" href="index2.php"><i class="fas fa-home me-2"></i>Accueil client</a></li>
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
                    <h2>Mes Messages</h2>
                </div>
            </div>
            <div class="item banner-2">
                <div class="header-text">
                    <h2>Communication avec JetReserve</h2>
                </div>
            </div>
            <div class="item banner-3">
                <div class="header-text">
                    <h2>Notifications importantes</h2>
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
                            <a class="nav-link" href="mes_paiements.php">
                                <i class="fas fa-credit-card me-2"></i>
                                Mes paiements
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="messages.php">
                                <i class="fas fa-envelope me-2"></i>
                                Messages
                                <?php if ($non_lus > 0): ?>
                                    <span class="badge bg-danger ms-2"><?php echo $non_lus; ?></span>
                                <?php endif; ?>
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
                <div class="messages-container">
                    <div class="messages-header">
                        <h2><i class="fas fa-envelope"></i> Mes Messages</h2>
                        <div class="email-count">
                            <?php echo count($emails); ?> message(s)
                            <?php if ($non_lus > 0): ?>
                                • <?php echo $non_lus; ?> non lu(s)
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="messages-content">
                        <!-- Liste des emails -->
                        <div class="emails-list">
                            <?php if (empty($emails)): ?>
                                <div class="empty-state">
                                    <i class="fas fa-envelope-open"></i>
                                    <h3>Aucun message</h3>
                                    <p>Vous n'avez pas encore reçu de messages de JetReserve.</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($emails as $index => $email): ?>
                                    <div class="email-item <?php echo $index === 0 ? 'active' : ''; ?>" 
                                         data-email-id="<?php echo $email['id_email']; ?>"
                                         onclick="loadEmail(<?php echo $email['id_email']; ?>)">
                                        <div class="email-avatar" style="background: 
                                            <?php 
                                            switch($email['type']) {
                                                case 'confirmation': echo 'var(--success);'; break;
                                                case 'recu': echo 'var(--warning);'; break;
                                                case 'ticket': echo 'var(--info);'; break;
                                                default: echo 'var(--primary);';
                                            }
                                            ?>">
                                            <span>JR</span>
                                        </div>
                                        <div class="email-info">
                                            <div class="email-header">
                                                <div class="email-sender">JetReserve</div>
                                                <div class="email-time"><?php echo $email['heure_envoi']; ?></div>
                                            </div>
                                            <div class="email-subject"><?php echo htmlspecialchars($email['sujet']); ?></div>
                                            <div class="email-preview">
                                                <?php 
                                                $preview = strip_tags($email['contenu']);
                                                echo strlen($preview) > 100 ? substr($preview, 0, 100) . '...' : $preview;
                                                ?>
                                            </div>
                                            <span class="email-type type-<?php echo $email['type']; ?>">
                                                <?php 
                                                switch($email['type']) {
                                                    case 'confirmation': echo 'Confirmation'; break;
                                                    case 'recu': echo 'Reçu'; break;
                                                    case 'ticket': echo 'Billet'; break;
                                                    default: echo ucfirst($email['type']);
                                                }
                                                ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Zone de lecture -->
                        <div class="email-reader">
                            <?php if (!empty($emails)): ?>
                                <div class="email-reader-header">
                                    <div class="email-reader-subject">
                                        <?php echo htmlspecialchars($emails[0]['sujet']); ?>
                                    </div>
                                    <div class="email-reader-meta">
                                        <div class="email-sender-info">
                                            <div class="email-sender-avatar" style="background: 
                                                <?php 
                                                switch($emails[0]['type']) {
                                                    case 'confirmation': echo 'var(--success);'; break;
                                                    case 'recu': echo 'var(--warning);'; break;
                                                    case 'ticket': echo 'var(--info);'; break;
                                                    default: echo 'var(--primary);';
                                                }
                                                ?>">
                                                <span>JR</span>
                                            </div>
                                            <div class="email-sender-details">
                                                <h4>JetReserve</h4>
                                                <p>Service Client</p>
                                            </div>
                                        </div>
                                        <div class="email-date">
                                            <?php echo $emails[0]['date_formatee']; ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="email-content" id="emailContent">
                                    <?php echo nl2br(htmlspecialchars($emails[0]['contenu'])); ?>
                                </div>
                                
                                <div class="email-actions">
                                    <button class="email-action-btn" onclick="replyToEmail()">
                                        <i class="fas fa-reply"></i> Répondre
                                    </button>
                                    <button class="email-action-btn" onclick="forwardEmail()">
                                        <i class="fas fa-share"></i> Transférer
                                    </button>
                                    <button class="email-action-btn" onclick="printEmail()">
                                        <i class="fas fa-print"></i> Imprimer
                                    </button>
                                    <button class="email-action-btn" onclick="deleteEmail()">
                                        <i class="fas fa-trash"></i> Supprimer
                                    </button>
                                </div>
                            <?php else: ?>
                                <div class="empty-state">
                                    <i class="fas fa-envelope-open"></i>
                                    <h3>Aucun message sélectionné</h3>
                                    <p>Sélectionnez un message dans la liste pour le lire.</p>
                                </div>
                            <?php endif; ?>
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
        });

        function loadEmail(emailId) {
            // Mettre à jour la sélection visuelle
            $('.email-item').removeClass('active');
            $(`.email-item[data-email-id="${emailId}"]`).addClass('active');
            
            // En production, vous feriez un appel AJAX pour charger l'email complet
            // Pour l'instant, on simule le chargement
            console.log('Chargement de l\'email ID: ' + emailId);
            
            // Ici vous feriez un appel AJAX pour récupérer l'email
            // $.ajax({
            //     url: 'get_email.php',
            //     method: 'POST',
            //     data: { email_id: emailId },
            //     success: function(response) {
            //         // Mettre à jour l'interface avec les données de l'email
            //     }
            // });
        }

        function replyToEmail() {
            const activeEmail = $('.email-item.active');
            const subject = activeEmail.find('.email-subject').text();
            alert('Répondre à: ' + subject);
            // Ouvrir un formulaire de réponse
        }

        function forwardEmail() {
            const activeEmail = $('.email-item.active');
            const subject = activeEmail.find('.email-subject').text();
            alert('Transférer: ' + subject);
            // Ouvrir un formulaire de transfert
        }

        function printEmail() {
            window.print();
        }

        function deleteEmail() {
            const activeEmail = $('.email-item.active');
            const emailId = activeEmail.data('email-id');
            
            if (confirm('Êtes-vous sûr de vouloir supprimer ce message ?')) {
                // En production, vous feriez un appel AJAX pour supprimer l'email
                console.log('Suppression de l\'email ID: ' + emailId);
                
                // Simulation de suppression
                activeEmail.fadeOut(300, function() {
                    $(this).remove();
                    // Si plus d'emails, afficher l'état vide
                    if ($('.email-item').length === 0) {
                        $('.email-reader').html(`
                            <div class="empty-state">
                                <i class="fas fa-envelope-open"></i>
                                <h3>Aucun message sélectionné</h3>
                                <p>Sélectionnez un message dans la liste pour le lire.</p>
                            </div>
                        `);
                    }
                });
            }
        }
    </script>
</body>
</html>