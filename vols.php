<?php
require_once 'includes/db.php';
session_start();

// Récupérer les paramètres de recherche
$depart = trim($_GET['depart'] ?? '');
$arrivee = trim($_GET['arrivee'] ?? '');
$date_depart = $_GET['date_depart'] ?? '';
$passagers = $_GET['passagers'] ?? 1;
$classe = $_GET['classe'] ?? 'économique';
$direct = isset($_GET['direct']) ? true : false;

// Construire la requête de recherche
$sql = "SELECT v.*, c.nom_compagnie, c.code_compagnie, a.modele 
        FROM vols v 
        JOIN compagnies c ON v.id_compagnie = c.id_compagnie 
        JOIN avions a ON v.id_avion = a.id_avion 
        WHERE 1=1";

$params = [];

// Recherche
if (!empty($depart)) {
    $sql .= " AND v.depart LIKE ?";
    $params[] = "%$depart%";
}

if (!empty($arrivee)) {
    $sql .= " AND v.arrivee LIKE ?";
    $params[] = "%$arrivee%";
}

// Filtre par date si fournie
if (!empty($date_depart)) {
    $sql .= " AND DATE(v.date_depart) = ?";
    $params[] = $date_depart;
}

// Filtre par classe
if (!empty($classe) && $classe !== 'toutes') {
    $sql .= " AND v.classe = ?";
    $params[] = $classe;
}

// Filtre vol direct
if ($direct) {
    $sql .= " AND v.escales = 0";
}

$sql .= " ORDER BY v.prix ASC, v.date_depart ASC";

// Exécuter la recherche
try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $vols_trouves = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $vols_trouves = [];
}

// Compter le nombre de résultats
$nombre_vols = count($vols_trouves);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Résultats de recherche - JetReserve</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .search-results-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            min-height: 60vh;
        }

        .results-header {
            background: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            border-left: 5px solid #3498db;
        }

        .results-count {
            color: #2c3e50;
            font-size: 1.4rem;
            margin-bottom: 20px;
            font-weight: 600;
        }

        .search-criteria {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-top: 20px;
        }

        .criteria-item {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            font-size: 0.9rem;
            font-weight: 500;
            box-shadow: 0 3px 10px rgba(52, 152, 219, 0.3);
        }

        .criteria-item i {
            margin-right: 8px;
        }

        .vols-list {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .vol-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            border-left: 5px solid #3498db;
            position: relative;
            overflow: hidden;
        }

        .vol-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }

        .vol-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #3498db, #2ecc71);
        }

        .vol-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f8f9fa;
        }

        .vol-route {
            flex: 1;
        }

        .vol-depart-arrivee {
            font-size: 1.5rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .vol-depart-arrivee::after {
            content: '→';
            color: #3498db;
            font-weight: 300;
        }

        .vol-compagnie {
            color: #7f8c8d;
            font-size: 1rem;
            font-weight: 500;
        }

        .vol-price {
            text-align: right;
            min-width: 150px;
        }

        .price-amount {
            font-size: 2rem;
            font-weight: 800;
            color: #e74c3c;
            margin-bottom: 5px;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
        }

        .price-person {
            color: #7f8c8d;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .vol-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .detail-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            background: #f8f9fa;
            border-radius: 10px;
            transition: background 0.3s ease;
        }

        .detail-item:hover {
            background: #e9ecef;
        }

        .detail-icon {
            color: #3498db;
            width: 20px;
            text-align: center;
            font-size: 1.1rem;
        }

        .detail-label {
            font-weight: 600;
            color: #495057;
            font-size: 0.9rem;
            min-width: 80px;
        }

        .detail-value {
            color: #2c3e50;
            font-weight: 600;
            font-size: 1rem;
        }

        .vol-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 20px;
            border-top: 2px solid #f8f9fa;
        }

        .vol-features {
            display: flex;
            gap: 12px;
        }

        .feature-badge {
            background: linear-gradient(135deg, #e8f4fd, #d6eaf8);
            color: #3498db;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            border: 1px solid #d6eaf8;
        }

        .btn-reserver {
            background: linear-gradient(135deg, #27ae60, #219653);
            color: white;
            padding: 12px 30px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(39, 174, 96, 0.3);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-reserver:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(39, 174, 96, 0.4);
            color: white;
        }

        .no-results {
            text-align: center;
            padding: 60px 40px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .no-results i {
            font-size: 4rem;
            color: #bdc3c7;
            margin-bottom: 25px;
        }

        .no-results h3 {
            color: #2c3e50;
            margin-bottom: 15px;
            font-size: 1.5rem;
        }

        .no-results p {
            color: #7f8c8d;
            margin-bottom: 25px;
            font-size: 1.1rem;
        }

        .quick-search-links {
            display: flex;
            justify-content: center;
            gap: 15px;
            flex-wrap: wrap;
            margin: 25px 0;
        }

        .quick-link {
            background: #3498db;
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }

        .quick-link:hover {
            background: #2980b9;
            transform: translateY(-2px);
            color: white;
        }

        .modify-search {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
            padding: 12px 30px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 10px;
        }

        .modify-search:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(231, 76, 60, 0.3);
            color: white;
        }

        .duration-badge {
            background: linear-gradient(135deg, #9b59b6, #8e44ad);
            color: white;
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-left: 10px;
        }

        @media (max-width: 768px) {
            .vol-header {
                flex-direction: column;
                gap: 15px;
            }
            
            .vol-price {
                text-align: left;
                width: 100%;
            }
            
            .vol-actions {
                flex-direction: column;
                gap: 15px;
                align-items: stretch;
            }
            
            .vol-features {
                justify-content: center;
            }
            
            .quick-search-links {
                flex-direction: column;
                align-items: center;
            }
            
            .search-criteria {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container">
            <div class="header-top">
                <a href="index.php" class="logo">Jet<span>Reserve</span></a>
                <div class="auth-buttons">
                    <?php if (isset($_SESSION['id_user'])): ?>
                        <a href="vge64/index2.php" class="btn btn-outline">Mon compte</a>
                        <a href="vge64/deconnexion.php" class="btn btn-primary">Déconnexion</a>
                    <?php else: ?>
                        <a href="vge64/connexion.php" class="btn btn-outline">Connexion</a>
                        <a href="vge64/inscription.php" class="btn btn-primary">Inscription</a>
                    <?php endif; ?>
                </div>
            </div>
            <nav class="nav-menu">
                <ul class="nav-links">
                    <li><a href="index.php"><i class="fas fa-home"></i> Accueil</a></li>
                    <li><a href="vols.php"><i class="fas fa-plane-departure"></i> Vols</a></li>
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

    <div class="search-results-container">
        <div class="results-header">
            <h1 style="color: #2c3e50; margin-bottom: 10px; font-size: 2rem;">
                <i class="fas fa-search" style="color: #3498db;"></i> Résultats de recherche
            </h1>
            <div class="results-count">
                <?php if ($nombre_vols > 0): ?>
                    🎉 <strong><?php echo $nombre_vols; ?></strong> vol(s) correspondant à votre recherche
                <?php else: ?>
                    🔍 Aucun vol trouvé pour votre recherche
                <?php endif; ?>
            </div>
            
            <?php if (!empty($depart) || !empty($arrivee)): ?>
                <div class="search-criteria">
                    <?php if (!empty($depart)): ?>
                        <div class="criteria-item">
                            <i class="fas fa-plane-departure"></i> Départ: <?php echo htmlspecialchars($depart); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($arrivee)): ?>
                        <div class="criteria-item">
                            <i class="fas fa-plane-arrival"></i> Destination: <?php echo htmlspecialchars($arrivee); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($date_depart)): ?>
                        <div class="criteria-item">
                            <i class="fas fa-calendar-alt"></i> Date: <?php echo date('d/m/Y', strtotime($date_depart)); ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="criteria-item">
                        <i class="fas fa-user-friends"></i> Passagers: <?php echo $passagers; ?>
                    </div>
                    
                    <div class="criteria-item">
                        <i class="fas fa-chair"></i> Classe: <?php echo htmlspecialchars($classe); ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($nombre_vols > 0): ?>
            <div class="vols-list">
                <?php foreach ($vols_trouves as $vol): 
                    $duree = strtotime($vol['date_arrivee']) - strtotime($vol['date_depart']);
                    $heures = floor($duree / 3600);
                    $minutes = floor(($duree % 3600) / 60);
                    $duree_affichage = $heures . 'h' . ($minutes > 0 ? $minutes . 'min' : '');
                ?>
                    <div class="vol-card">
                        <div class="vol-header">
                            <div class="vol-route">
                                <div class="vol-depart-arrivee">
                                    <?php echo htmlspecialchars($vol['depart']); ?>
                                    <?php echo htmlspecialchars($vol['arrivee']); ?>
                                    <span class="duration-badge"><?php echo $duree_affichage; ?></span>
                                </div>
                                <div class="vol-compagnie">
                                    ✈️ <?php echo htmlspecialchars($vol['nom_compagnie']); ?> 
                                    • Vol <?php echo htmlspecialchars($vol['code_compagnie'] . $vol['numero_vol']); ?>
                                    • <?php echo htmlspecialchars($vol['modele']); ?>
                                </div>
                            </div>
                            <div class="vol-price">
                                <div class="price-amount">
                                    <?php echo number_format($vol['prix'], 2, ',', ' '); ?> €
                                </div>
                                <div class="price-person">
                                    par personne
                                </div>
                            </div>
                        </div>

                        <div class="vol-details">
                            <div class="detail-item">
                                <i class="fas fa-plane-departure detail-icon"></i>
                                <div>
                                    <div class="detail-label">Départ</div>
                                    <div class="detail-value"><?php echo date('d/m/Y à H:i', strtotime($vol['date_depart'])); ?></div>
                                </div>
                            </div>
                            <div class="detail-item">
                                <i class="fas fa-plane-arrival detail-icon"></i>
                                <div>
                                    <div class="detail-label">Arrivée</div>
                                    <div class="detail-value"><?php echo date('d/m/Y à H:i', strtotime($vol['date_arrivee'])); ?></div>
                                </div>
                            </div>
                            <div class="detail-item">
                                <i class="fas fa-users detail-icon"></i>
                                <div>
                                    <div class="detail-label">Places</div>
                                    <div class="detail-value"><?php echo $vol['places_disponibles']; ?> disponibles</div>
                                </div>
                            </div>
                            <div class="detail-item">
                                <i class="fas fa-star detail-icon"></i>
                                <div>
                                    <div class="detail-label">Classe</div>
                                    <div class="detail-value"><?php echo ucfirst($vol['classe']); ?></div>
                                </div>
                            </div>
                        </div>

                        <div class="vol-actions">
                            <div class="vol-features">
                                <?php if ($vol['escales'] == 0): ?>
                                    <span class="feature-badge">🚀 Vol direct</span>
                                <?php else: ?>
                                    <span class="feature-badge">🔄 <?php echo $vol['escales']; ?> escale(s)</span>
                                <?php endif; ?>
                                <span class="feature-badge">💺 <?php echo $vol['places_disponibles']; ?> places</span>
                                <span class="feature-badge">⭐ <?php echo ucfirst($vol['classe']); ?></span>
                            </div>
                            
                            <?php if (isset($_SESSION['id_user'])): ?>
                                <a href="vol_details.php?id_vol=<?php echo $vol['id_vol']; ?>&passagers=<?php echo $passagers; ?>" class="btn-reserver">
                                    <i class="fas fa-shopping-cart"></i> Réserver maintenant
                                </a>
                            <?php else: ?>
                                <a href="vge64/connexion.php?redirect=vol_details&id_vol=<?php echo $vol['id_vol']; ?>&passagers=<?php echo $passagers; ?>" class="btn-reserver">
                                    <i class="fas fa-shopping-cart"></i> Réserver maintenant
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-results">
                <i class="fas fa-search"></i>
                <h3>Aucun vol trouvé</h3>
                <p>Nous n'avons trouvé aucun vol correspondant à vos critères de recherche.</p>
                
                <div class="quick-search-links">
                    <a href="vols.php?depart=Paris&arrivee=Abidjan" class="quick-link">
                        <i class="fas fa-plane"></i> Paris → Abidjan
                    </a>
                    <a href="vols.php?depart=Dubaï&arrivee=Abidjan" class="quick-link">
                        <i class="fas fa-plane"></i> Dubaï → Abidjan
                    </a>
                    <a href="vols.php?depart=Istanbul&arrivee=Paris" class="quick-link">
                        <i class="fas fa-plane"></i> Istanbul → Paris
                    </a>
                </div>
                
                <a href="index.php" class="modify-search">
                    <i class="fas fa-arrow-left"></i> Modifier ma recherche
                </a>
            </div>
        <?php endif; ?>
    </div>

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
            <!-- Votre footer existant -->
        </div>
    </footer>
</body>
</html>