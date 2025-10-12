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

.new-message-btn {
    background: var(--info);
    color: var(--white);
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.3s ease;
    cursor: pointer;
}

.new-message-btn:hover {
    background: var(--primary);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(52, 152, 219, 0.3);
}

.messages-content {
    display: flex;
    min-height: 600px;
}

/* Liste des conversations */
.conversations-list {
    width: 350px;
    border-right: 1px solid var(--border-color);
    background: var(--light-gray);
}

.conversations-search {
    padding: 1rem;
    border-bottom: 1px solid var(--border-color);
}

.search-input {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    font-family: 'Poppins', sans-serif;
    background: var(--white);
    transition: all 0.3s ease;
}

.search-input:focus {
    outline: none;
    border-color: var(--info);
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
}

.conversations {
    max-height: 500px;
    overflow-y: auto;
}

.conversation-item {
    padding: 1rem;
    border-bottom: 1px solid var(--border-color);
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    gap: 1rem;
    background: var(--white);
}

.conversation-item:hover {
    background: var(--light-gray);
    transform: translateX(3px);
}

.conversation-item.active {
    background: #e0f2fe;
    border-left: 3px solid var(--info);
}

.conversation-avatar {
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

.conversation-info {
    flex: 1;
    min-width: 0;
}

.conversation-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.25rem;
}

.conversation-name {
    font-weight: 600;
    color: var(--dark);
    font-size: 0.95rem;
}

.conversation-time {
    font-size: 0.75rem;
    color: var(--gray);
}

.conversation-preview {
    font-size: 0.85rem;
    color: var(--gray);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.conversation-badge {
    background: var(--danger);
    color: var(--white);
    border-radius: 50%;
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.7rem;
    margin-top: 0.25rem;
    flex-shrink: 0;
}

/* Zone de chat */
.chat-area {
    flex: 1;
    display: flex;
    flex-direction: column;
    background: var(--white);
}

.chat-header {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid var(--border-color);
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: var(--light-gray);
}

.chat-contact {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.chat-contact-avatar {
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

.chat-contact-info h4 {
    margin: 0;
    font-size: 1rem;
    color: var(--dark);
    font-weight: 600;
}

.chat-contact-info p {
    margin: 0;
    font-size: 0.8rem;
    color: var(--gray);
}

.chat-actions {
    display: flex;
    gap: 0.5rem;
}

.chat-action-btn {
    background: none;
    border: none;
    color: var(--gray);
    cursor: pointer;
    padding: 0.5rem;
    border-radius: 5px;
    transition: all 0.3s ease;
    font-size: 1rem;
}

.chat-action-btn:hover {
    background: var(--light-gray);
    color: var(--dark);
}

.messages-area {
    flex: 1;
    padding: 1.5rem;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 1rem;
    background: var(--light-gray);
}

.message {
    max-width: 70%;
    padding: 1rem;
    border-radius: 15px;
    position: relative;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.message.received {
    align-self: flex-start;
    background: var(--white);
    border-bottom-left-radius: 5px;
    border: 1px solid var(--border-color);
}

.message.sent {
    align-self: flex-end;
    background: linear-gradient(135deg, var(--info), var(--primary));
    color: var(--white);
    border-bottom-right-radius: 5px;
}

.message-text {
    font-size: 0.9rem;
    line-height: 1.4;
}

.message-time {
    font-size: 0.7rem;
    margin-top: 0.5rem;
    opacity: 0.7;
    text-align: right;
}

.message-input-area {
    padding: 1rem 1.5rem;
    border-top: 1px solid var(--border-color);
    display: flex;
    gap: 1rem;
    background: var(--white);
}

.message-input {
    flex: 1;
    padding: 0.75rem 1rem;
    border: 1px solid var(--border-color);
    border-radius: 25px;
    font-family: 'Poppins', sans-serif;
    resize: none;
    background: var(--light-gray);
    transition: all 0.3s ease;
}

.message-input:focus {
    outline: none;
    border-color: var(--info);
    background: var(--white);
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
}

.send-btn {
    background: var(--info);
    color: var(--white);
    border: none;
    width: 45px;
    height: 45px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
    flex-shrink: 0;
}

.send-btn:hover {
    background: var(--primary);
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(52, 152, 219, 0.3);
}

/* État vide */
.empty-state {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 2rem;
    color: var(--gray);
}

.empty-state i {
    font-size: 3rem;
    margin-bottom: 1rem;
    color: var(--border-color);
}

/* Responsive */
@media (max-width: 768px) {
    .messages-content {
        flex-direction: column;
    }
    
    .conversations-list {
        width: 100%;
        border-right: none;
        border-bottom: 1px solid var(--border-color);
        max-height: 300px;
    }
    
    .conversations {
        max-height: 200px;
    }
    
    .message {
        max-width: 85%;
    }
    
    .chat-header {
        padding: 1rem;
    }
    
    .messages-area {
        padding: 1rem;
    }
    
    .message-input-area {
        padding: 1rem;
    }
}

@media (max-width: 480px) {
    .messages-header {
        flex-direction: column;
        gap: 1rem;
        align-items: flex-start;
    }
    
    .conversation-item {
        padding: 0.75rem;
    }
    
    .conversation-avatar {
        width: 40px;
        height: 40px;
        font-size: 1rem;
    }
    
    .chat-contact {
        gap: 0.75rem;
    }
    
    .chat-contact-avatar {
        width: 35px;
        height: 35px;
    }
    
    .chat-contact-info h4 {
        font-size: 0.9rem;
    }
    
    .message {
        max-width: 90%;
        padding: 0.75rem;
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
                            <a class="nav-link" href="../reservation.php">
                                <i class="fas fa-plane-departure me-2"></i>
                                Mes réservations
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../paiement.php">
                                <i class="fas fa-credit-card me-2"></i>
                                Mes paiements
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="messages.php">
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
                <div class="messages-container">
                    <div class="messages-header">
                        <h2><i class="fas fa-envelope"></i> Mes Messages</h2>
                        <button class="new-message-btn" id="newMessageBtn">
                            <i class="fas fa-plus"></i> Nouveau message
                        </button>
                    </div>
                    
                    <div class="messages-content">
                        <!-- Conversations List -->
                        <div class="conversations-list">
                            <div class="conversations-search">
                                <input type="text" class="search-input" placeholder="Rechercher un message...">
                            </div>
                            <div class="conversations">
                                <!-- Conversation 1 -->
                                <div class="conversation-item active" data-conversation="1">
                                    <div class="conversation-avatar" style="background: var(--success);">
                                        <span>SC</span>
                                    </div>
                                    <div class="conversation-info">
                                        <div class="conversation-header">
                                            <div class="conversation-name">Service Client</div>
                                            <div class="conversation-time">10:30</div>
                                        </div>
                                        <div class="conversation-preview">Votre réservation Paris-New York a été confirmée...</div>
                                    </div>
                                    <div class="conversation-badge">2</div>
                                </div>
                                
                                <!-- Conversation 2 -->
                                <div class="conversation-item" data-conversation="2">
                                    <div class="conversation-avatar" style="background: var(--warning);">
                                        <span>PR</span>
                                    </div>
                                    <div class="conversation-info">
                                        <div class="conversation-header">
                                            <div class="conversation-name">Promotions</div>
                                            <div class="conversation-time">Hier</div>
                                        </div>
                                        <div class="conversation-preview">Nouvelle offre spéciale pour vos prochains voyages...</div>
                                    </div>
                                </div>
                                
                                <!-- Conversation 3 -->
                                <div class="conversation-item" data-conversation="3">
                                    <div class="conversation-avatar" style="background: var(--danger);">
                                        <span>AS</span>
                                    </div>
                                    <div class="conversation-info">
                                        <div class="conversation-header">
                                            <div class="conversation-name">Assistance Bagages</div>
                                            <div class="conversation-time">15/06</div>
                                        </div>
                                        <div class="conversation-preview">Votre demande d'assistance a été traitée...</div>
                                    </div>
                                </div>
                                
                                <!-- Conversation 4 -->
                                <div class="conversation-item" data-conversation="4">
                                    <div class="conversation-avatar" style="background: #8b5cf6;">
                                        <span>FP</span>
                                    </div>
                                    <div class="conversation-info">
                                        <div class="conversation-header">
                                            <div class="conversation-name">Fidélité Premium</div>
                                            <div class="conversation-time">12/06</div>
                                        </div>
                                        <div class="conversation-preview">Nouveaux avantages pour les membres Premium...</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Chat Area -->
                        <div class="chat-area">
                            <div class="chat-header">
                                <div class="chat-contact">
                                    <div class="chat-contact-avatar" style="background: var(--success);">
                                        <span>SC</span>
                                    </div>
                                    <div class="chat-contact-info">
                                        <h4>Service Client JetReserve</h4>
                                        <p>En ligne • Réponse sous 24h</p>
                                    </div>
                                </div>
                                <div class="chat-actions">
                                    <button class="chat-action-btn">
                                        <i class="fas fa-phone-alt"></i>
                                    </button>
                                    <button class="chat-action-btn">
                                        <i class="fas fa-video"></i>
                                    </button>
                                    <button class="chat-action-btn">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="messages-area" id="messagesArea">
                                <!-- Message reçu -->
                                <div class="message received">
                                    <div class="message-text">
                                        Bonjour <?php echo $_SESSION['prenom'] ?? 'Client'; ?>, votre réservation pour le vol Paris-New York (JET1234) du 15 juin a été confirmée. Votre numéro de réservation est JR20230615001.
                                    </div>
                                    <div class="message-time">10:15</div>
                                </div>
                                
                                <!-- Message envoyé -->
                                <div class="message sent">
                                    <div class="message-text">
                                        Merci pour la confirmation. J'aimerais ajouter un bagage supplémentaire à ma réservation.
                                    </div>
                                    <div class="message-time">10:22</div>
                                </div>
                                
                                <!-- Message reçu -->
                                <div class="message received">
                                    <div class="message-text">
                                        Bien sûr, je peux vous aider avec cela. Un bagage supplémentaire de 23kg coûte 35€. Souhaitez-vous que je l'ajoute à votre réservation ?
                                    </div>
                                    <div class="message-time">10:30</div>
                                </div>
                            </div>
                            
                            <div class="message-input-area">
                                <textarea class="message-input" placeholder="Tapez votre message..." rows="1"></textarea>
                                <button class="send-btn">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
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
    <script>
        $(document).ready(function() {
            // Gestion de la sélection des conversations
            $('.conversation-item').on('click', function() {
                $('.conversation-item').removeClass('active');
                $(this).addClass('active');
                
                // Ici, vous chargeriez les messages de la conversation sélectionnée
                const conversationId = $(this).data('conversation');
                loadConversation(conversationId);
            });
            
            // Nouveau message
            $('#newMessageBtn').on('click', function() {
                alert('Fonctionnalité de nouveau message à implémenter');
                // Ouvrir un modal pour composer un nouveau message
            });
            
            // Envoi de message
            $('.send-btn').on('click', function() {
                sendMessage();
            });
            
            // Envoi avec la touche Entrée
            $('.message-input').on('keypress', function(e) {
                if (e.which === 13 && !e.shiftKey) {
                    e.preventDefault();
                    sendMessage();
                }
            });
            
            // Fonction pour envoyer un message
            function sendMessage() {
                const messageInput = $('.message-input');
                const messageText = messageInput.val().trim();
                
                if (messageText) {
                    // Ajouter le message à l'interface
                    const timestamp = new Date().toLocaleTimeString('fr-FR', { 
                        hour: '2-digit', 
                        minute: '2-digit' 
                    });
                    
                    const messageHtml = `
                        <div class="message sent">
                            <div class="message-text">${messageText}</div>
                            <div class="message-time">${timestamp}</div>
                        </div>
                    `;
                    
                    $('#messagesArea').append(messageHtml);
                    messageInput.val('');
                    
                    // Faire défiler vers le bas
                    $('#messagesArea').scrollTop($('#messagesArea')[0].scrollHeight);
                    
                    // Simulation de réponse automatique après 2 secondes
                    setTimeout(function() {
                        const responses = [
                            "Nous avons bien reçu votre message et nous vous répondrons dans les plus brefs délais.",
                            "Merci pour votre message. Un de nos conseillers vous répondra rapidement.",
                            "Votre demande a été transmise à notre équipe. Nous reviendrons vers vous rapidement."
                        ];
                        
                        const randomResponse = responses[Math.floor(Math.random() * responses.length)];
                        
                        const responseHtml = `
                            <div class="message received">
                                <div class="message-text">${randomResponse}</div>
                                <div class="message-time">${new Date().toLocaleTimeString('fr-FR', { 
                                    hour: '2-digit', 
                                    minute: '2-digit' 
                                })}</div>
                            </div>
                        `;
                        
                        $('#messagesArea').append(responseHtml);
                        $('#messagesArea').scrollTop($('#messagesArea')[0].scrollHeight);
                    }, 2000);
                }
            }
            
            // Fonction pour charger une conversation (simulée)
            function loadConversation(conversationId) {
                // En production, vous feriez un appel AJAX pour charger les messages
                console.log('Chargement de la conversation ' + conversationId);
                
                // Simulation de chargement
                $('#messagesArea').html('<div class="empty-state"><i class="fas fa-spinner fa-spin"></i><p>Chargement des messages...</p></div>');
                
                setTimeout(function() {
                    // Messages simulés selon la conversation
                    let messagesHtml = '';
                    
                    if (conversationId == 1) {
                        messagesHtml = `
                            <div class="message received">
                                <div class="message-text">
                                    Bonjour ${$('.conversation-name').first().text()}, votre réservation pour le vol Paris-New York (JET1234) du 15 juin a été confirmée. Votre numéro de réservation est JR20230615001.
                                </div>
                                <div class="message-time">10:15</div>
                            </div>
                            <div class="message sent">
                                <div class="message-text">
                                    Merci pour la confirmation. J'aimerais ajouter un bagage supplémentaire à ma réservation.
                                </div>
                                <div class="message-time">10:22</div>
                            </div>
                            <div class="message received">
                                <div class="message-text">
                                    Bien sûr, je peux vous aider avec cela. Un bagage supplémentaire de 23kg coûte 35€. Souhaitez-vous que je l'ajoute à votre réservation ?
                                </div>
                                <div class="message-time">10:30</div>
                            </div>
                        `;
                    } else if (conversationId == 2) {
                        messagesHtml = `
                            <div class="message received">
                                <div class="message-text">
                                    Bonjour ! Profitez de notre offre spéciale été : -30% sur tous les vols vers l'Europe jusqu'au 31 août.
                                </div>
                                <div class="message-time">Hier</div>
                            </div>
                        `;
                    }
                    
                    $('#messagesArea').html(messagesHtml);
                    $('#messagesArea').scrollTop($('#messagesArea')[0].scrollHeight);
                }, 500);
            }
            
            // Ajustement automatique de la hauteur du textarea
            $('.message-input').on('input', function() {
                this.style.height = 'auto';
                this.style.height = (this.scrollHeight) + 'px';
            });
        });
    </script>
</body>
</html>