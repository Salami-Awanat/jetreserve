<?php

require_once 'includes/db.php';
session_start();

// Récupération des destinations depuis la BDD, avec prix min, id_vol du vol le moins cher et nombre de vols
try {
	$sql = "SELECT v.arrivee AS ville, MIN(v.prix) AS min_prix, COUNT(*) AS nb_vols, 
	               (SELECT id_vol FROM vols WHERE arrivee = v.arrivee ORDER BY prix ASC LIMIT 1) AS id_vol_min
	        FROM vols v
	        WHERE v.arrivee IS NOT NULL AND v.arrivee <> ''
	        GROUP BY v.arrivee
	        ORDER BY nb_vols DESC, min_prix ASC
	        LIMIT 12";
	$stmt = $pdo->query($sql);
	$destinations = $stmt->fetchAll(PDO::FETCH_ASSOC);
	
} catch (PDOException $e) {
	$destinations = [];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Destinations - JetReserve</title>
	<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
	<link rel="stylesheet" href="style.css">
	<style>
		.offers-grid {
			display: grid;
			grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
			gap: 30px;
			margin-bottom: 50px;
		}
		
		.card:hover {
			transform: translateY(-10px);
			box-shadow: 0 20px 40px rgba(0,0,0,0.15);
		}
		
		.card:hover img {
			transform: scale(1.05);
		}
		
		.feature:hover {
			transform: translateY(-5px);
			box-shadow: 0 15px 35px rgba(0,0,0,0.15);
		}
		
		.btn:hover {
			transform: translateY(-2px);
			box-shadow: 0 8px 20px rgba(0,0,0,0.2);
		}
		
		@media (max-width: 768px) {
			.offers-grid {
				grid-template-columns: 1fr;
				gap: 20px;
			}
			
			.hero-destinations h1 {
				font-size: 2rem !important;
			}
			
			.hero-destinations p {
				font-size: 1rem !important;
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
					<li><a href="destinations.php"><i class="fas fa-map-marked-alt"></i> Destinations</a></li>
					<li><a href="#"><i class="fas fa-tags"></i> Offres spéciales</a></li>
				</ul>
				<div class="contact-info">
					<a href="#"><i class="fas fa-phone-alt"></i> Service client</a>
				</div>
			</nav>
		</div>
	</header>

	<main class="container" style="min-height:60vh;">
		<!-- Hero Section -->
		<section class="hero-destinations" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 60px 0; margin: 30px 0; border-radius: 15px; text-align: center;">
			<div class="container">
				<h1 style="font-size: 3rem; font-weight: 700; margin-bottom: 20px; text-shadow: 2px 2px 4px rgba(0,0,0,0.3);">
					<i class="fas fa-globe-americas" style="margin-right: 15px;"></i>
					Nos Destinations
				</h1>
				<p style="font-size: 1.2rem; margin-bottom: 30px; opacity: 0.9;">
					Découvrez le monde avec JetReserve - Des destinations exceptionnelles vous attendent
				</p>
				<div style="display: flex; justify-content: center; gap: 20px; flex-wrap: wrap;">
					<div style="background: rgba(255,255,255,0.2); padding: 15px 25px; border-radius: 25px; backdrop-filter: blur(10px);">
						<i class="fas fa-plane" style="margin-right: 8px;"></i>
						<span>Vols directs</span>
					</div>
					<div style="background: rgba(255,255,255,0.2); padding: 15px 25px; border-radius: 25px; backdrop-filter: blur(10px);">
						<i class="fas fa-shield-alt" style="margin-right: 8px;"></i>
						<span>Paiement sécurisé</span>
					</div>
					<div style="background: rgba(255,255,255,0.2); padding: 15px 25px; border-radius: 25px; backdrop-filter: blur(10px);">
						<i class="fas fa-headset" style="margin-right: 8px;"></i>
						<span>Support 24/7</span>
					</div>
				</div>
			</div>
		</section>

		<section class="offers">
			<h2 class="section-title" style="text-align: center; margin-bottom: 10px;">
				<i class="fas fa-map-marked-alt"></i> Destinations populaires
			</h2>
			<p style="text-align:center; color: var(--secondary); margin-bottom: 40px; font-size: 1.1rem;">
				Explorez nos destinations les plus demandées et trouvez votre prochaine aventure
			</p>
			<div class="offers-grid">
				<?php if (!empty($destinations)): ?>
					<?php foreach ($destinations as $dest): ?>
						<?php
							$ville = $dest['ville'];
							$villeLower = mb_strtolower($ville, 'UTF-8');
							// Sélection d'image simple selon la ville
							if (strpos($villeLower, 'paris') !== false) {
								$image_file = 'paris.jpg';
							} elseif (strpos($villeLower, 'duba') !== false) { // Dubai / Dubaï
								$image_file = 'dubai.jpg';
							} elseif (strpos($villeLower, 'rome') !== false || strpos($villeLower, 'ital') !== false) {
								$image_file = 'italy.jpg';
							} elseif (strpos($villeLower, 'new york') !== false || strpos($villeLower, 'usa') !== false || strpos($villeLower, 'états-unis') !== false) {
								$image_file = 'usa.jpg';
							} elseif (strpos($villeLower, 'lond') !== false) { // Londres / London
								$image_file = 'londre.jpg';
							} else {
								$image_file = 'voyage.jpg';
							}
							$image_path = 'images/' . $image_file;
						?>
						<div class="card" style="position: relative; overflow: hidden; border-radius: 15px; box-shadow: 0 8px 25px rgba(0,0,0,0.1); transition: all 0.3s ease; border: none;">
							<div style="height: 220px; overflow: hidden; position: relative;">
								<img src="<?php echo $image_path; ?>" alt="<?php echo htmlspecialchars($ville); ?>" style="width:100%; height:100%; object-fit: cover; transition: transform 0.3s ease;">
								<div style="position: absolute; top: 15px; right: 15px; background: rgba(0,0,0,0.7); color: white; padding: 5px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 600;">
									<?php echo (int)$dest['nb_vols']; ?> vols
								</div>
								<div style="position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(transparent, rgba(0,0,0,0.8)); padding: 20px 15px 15px;">
									<h3 style="color: white; margin: 0; font-size: 1.4rem; font-weight: 700; text-shadow: 1px 1px 2px rgba(0,0,0,0.5);">
										<?php echo htmlspecialchars($ville); ?>
									</h3>
								</div>
							</div>
							<div class="card-content" style="padding: 25px;">
								<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
									<div>
										<div style="color: var(--secondary); font-size: 0.9rem; margin-bottom: 5px;">Prix à partir de</div>
										<div class="price" style="font-size: 1.8rem; font-weight: 800; color: var(--primary); margin: 0;">
											<?php echo number_format((float)$dest['min_prix'], 2, ',', ' '); ?>€
										</div>
									</div>
									<div style="text-align: center;">
										<div style="color: var(--success); font-weight: 600; font-size: 0.9rem;">
											<i class="fas fa-check-circle" style="margin-right: 5px;"></i>
											Disponible
										</div>
									</div>
								</div>
								<div style="display:flex; gap:10px; flex-wrap:wrap;">
									<a class="btn btn-outline btn-sm" href="vols.php?arrivee=<?php echo urlencode($ville); ?>" style="flex: 1; text-align: center; padding: 12px 20px; border-radius: 25px; font-weight: 600; transition: all 0.3s ease;">
										<i class="fas fa-search"></i> Voir les vols
									</a>
									<?php if (!empty($dest['id_vol_min'])): ?>
										<a class="btn btn-primary btn-sm" href="reservation.php?id_vol=<?php echo (int)$dest['id_vol_min']; ?>" style="flex: 1; text-align: center; padding: 12px 20px; border-radius: 25px; font-weight: 600; background: linear-gradient(135deg, var(--primary), #0077cc); box-shadow: 0 4px 15px rgba(0, 91, 170, 0.3); transition: all 0.3s ease;">
											<i class="fas fa-credit-card"></i> Réserver
										</a>
									<?php endif; ?>
								</div>
							</div>
						</div>
					<?php endforeach; ?>
				<?php else: ?>
					<p style="text-align:center; color: var(--secondary);">Aucune destination à afficher pour le moment.</p>
				<?php endif; ?>
			</div>
		</section>

		<section class="features" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); padding: 80px 0; margin: 60px 0; border-radius: 20px;">
			<div class="container">
				<h2 class="section-title" style="text-align: center; font-size: 2.5rem; margin-bottom: 15px;">
					<i class="fas fa-star" style="color: #ffd700; margin-right: 15px;"></i>
					Pourquoi choisir JetReserve ?
				</h2>
				<p style="text-align: center; color: var(--secondary); margin-bottom: 60px; font-size: 1.2rem; max-width: 600px; margin-left: auto; margin-right: auto;">
					Des services premium et une expérience de voyage exceptionnelle pour chaque client
				</p>
				<div class="features-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 40px;">
					<div class="feature" style="background: white; padding: 40px 30px; border-radius: 20px; text-align: center; box-shadow: 0 10px 30px rgba(0,0,0,0.1); transition: all 0.3s ease; border-top: 4px solid #28a745;">
						<div class="feature-icon" style="width: 80px; height: 80px; background: linear-gradient(135deg, #28a745, #20c997); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 25px; font-size: 2rem; color: white; box-shadow: 0 8px 20px rgba(40, 167, 69, 0.3);">
							<i class="fas fa-percentage"></i>
						</div>
						<h3 style="color: var(--primary); margin-bottom: 15px; font-size: 1.4rem; font-weight: 700;">Meilleur prix garanti</h3>
						<p style="color: var(--secondary); line-height: 1.6; font-size: 1rem;">Comparez en un clin d'œil et réservez au tarif le plus bas du marché avec notre garantie prix.</p>
					</div>
					<div class="feature" style="background: white; padding: 40px 30px; border-radius: 20px; text-align: center; box-shadow: 0 10px 30px rgba(0,0,0,0.1); transition: all 0.3s ease; border-top: 4px solid #007bff;">
						<div class="feature-icon" style="width: 80px; height: 80px; background: linear-gradient(135deg, #007bff, #0056b3); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 25px; font-size: 2rem; color: white; box-shadow: 0 8px 20px rgba(0, 123, 255, 0.3);">
							<i class="fas fa-shield-alt"></i>
						</div>
						<h3 style="color: var(--primary); margin-bottom: 15px; font-size: 1.4rem; font-weight: 700;">Paiement 100% sécurisé</h3>
						<p style="color: var(--secondary); line-height: 1.6; font-size: 1rem;">Vos paiements sont protégés par les dernières normes bancaires et cryptage SSL.</p>
					</div>
					<div class="feature" style="background: white; padding: 40px 30px; border-radius: 20px; text-align: center; box-shadow: 0 10px 30px rgba(0,0,0,0.1); transition: all 0.3s ease; border-top: 4px solid #fd7e14;">
						<div class="feature-icon" style="width: 80px; height: 80px; background: linear-gradient(135deg, #fd7e14, #e55a00); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 25px; font-size: 2rem; color: white; box-shadow: 0 8px 20px rgba(253, 126, 20, 0.3);">
							<i class="fas fa-headset"></i>
						</div>
						<h3 style="color: var(--primary); margin-bottom: 15px; font-size: 1.4rem; font-weight: 700;">Support premium 24/7</h3>
						<p style="color: var(--secondary); line-height: 1.6; font-size: 1rem;">Une assistance réactive et professionnelle avant, pendant et après votre voyage.</p>
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
					<h4>JetReserve</h4>
					<p style="color: #cbd5e1; margin-bottom: 20px; font-size: 14px;">Votre partenaire de confiance pour des voyages inoubliables depuis 2023.</p>
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
						<div class="contact-item"><i class="fas fa-phone"></i><span>+33 1 23 45 67 89</span></div>
						<div class="contact-item"><i class="fas fa-envelope"></i><span>contact@jetreserve.com</span></div>
						<div class="contact-item"><i class="fas fa-clock"></i><span>Lun-Ven: 8h-20h • Sam-Dim: 9h-18h</span></div>
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
</body>
</html>


