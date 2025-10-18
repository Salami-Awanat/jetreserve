<?php
session_start();
require_once '../includes/db.php';

// Vérifier si l'utilisateur est admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../vge64/connexion.php');
    exit;
}

// Récupérer les statistiques
$stats = [];

try {
    // Nombre total d'utilisateurs
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
    $stats['total_utilisateurs'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Utilisateurs actifs (connectés dans les 30 derniers jours)
    $stmt = $pdo->query("SELECT COUNT(*) as actifs FROM users WHERE last_login >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
    $stats['utilisateurs_actifs'] = $stmt->fetch(PDO::FETCH_ASSOC)['actifs'];

    // Nombre d'admins
    $stmt = $pdo->query("SELECT COUNT(*) as admins FROM users WHERE role = 'admin'");
    $stats['total_admins'] = $stmt->fetch(PDO::FETCH_ASSOC)['admins'];

    // Nombre d'utilisateurs normaux
    $stmt = $pdo->query("SELECT COUNT(*) as users FROM users WHERE role = 'user'");
    $stats['total_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['users'];

    // Nombre total de réservations
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM reservations");
    $stats['total_reservations'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Réservations du mois
    $stmt = $pdo->query("SELECT COUNT(*) as mois FROM reservations WHERE MONTH(date_reservation) = MONTH(CURRENT_DATE())");
    $stats['reservations_mois'] = $stmt->fetch(PDO::FETCH_ASSOC)['mois'];

    // Chiffre d'affaires total
    $stmt = $pdo->query("SELECT SUM(prix_total) as ca FROM reservations WHERE statut = 'confirmée'");
    $stats['chiffre_affaires'] = $stmt->fetch(PDO::FETCH_ASSOC)['ca'] ?? 0;

    // Nombre de vols
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM vols");
    $stats['total_vols'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Vols à venir
    $stmt = $pdo->query("SELECT COUNT(*) as futurs FROM vols WHERE date_depart > NOW()");
    $stats['vols_futurs'] = $stmt->fetch(PDO::FETCH_ASSOC)['futurs'];

    // Réservations récentes
    $stmt = $pdo->prepare("
        SELECT r.*, u.nom, u.prenom, v.depart, v.arrivee, v.date_depart
        FROM reservations r 
        JOIN users u ON r.id_utilisateur = u.id_user 
        JOIN vols v ON r.id_vol = v.id_vol 
        ORDER BY r.date_reservation DESC 
        LIMIT 8
    ");
    $stmt->execute();
    $reservations_recentes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Utilisateurs récents
    $stmt = $pdo->prepare("
        SELECT id_user, nom, prenom, email, role, statut, date_inscription 
        FROM users 
        ORDER BY date_inscription DESC 
        LIMIT 8
    ");
    $stmt->execute();
    $utilisateurs_recents = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    error_log("Erreur dashboard: " . $e->getMessage());
    // Valeurs par défaut en cas d'erreur
    $stats = [
        'total_utilisateurs' => 0,
        'utilisateurs_actifs' => 0,
        'total_admins' => 0,
        'total_users' => 0,
        'total_reservations' => 0,
        'reservations_mois' => 0,
        'chiffre_affaires' => 0,
        'total_vols' => 0,
        'vols_futurs' => 0
    ];
    $reservations_recentes = [];
    $utilisateurs_recents = [];
}

include 'includes/header.php';
?>

<div class="content">
    <!-- Message de bienvenue -->
    <div class="card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
        <div class="card-body">
            <h1 style="color: white; margin-bottom: 10px;">Bienvenue, <?php echo $_SESSION['prenom'] . ' ' . $_SESSION['nom']; ?>! 👋</h1>
            <p style="color: rgba(255,255,255,0.9); margin: 0;">Vous êtes connecté en tant qu'administrateur</p>
        </div>
    </div>

    <!-- Statistics Grid -->
    <div class="stats-grid">
        <div class="stat-card success">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-value"><?php echo $stats['total_utilisateurs']; ?></div>
            <div class="stat-label">Utilisateurs</div>
        </div>
        
        <div class="stat-card info">
            <div class="stat-icon">
                <i class="fas fa-ticket-alt"></i>
            </div>
            <div class="stat-value"><?php echo $stats['total_reservations']; ?></div>
            <div class="stat-label">Réservations</div>
        </div>
        
        <div class="stat-card warning">
            <div class="stat-icon">
                <i class="fas fa-plane"></i>
            </div>
            <div class="stat-value"><?php echo $stats['total_vols']; ?></div>
            <div class="stat-label">Vols</div>
        </div>
        
        <div class="stat-card danger">
            <div class="stat-icon">
                <i class="fas fa-euro-sign"></i>
            </div>
            <div class="stat-value"><?php echo number_format($stats['chiffre_affaires'], 0, ',', ' '); ?>€</div>
            <div class="stat-label">Chiffre d'Affaires</div>
        </div>
    </div>

    <div class="dashboard-grid">
        <!-- Réservations récentes -->
        <div class="card">
            <div class="card-header">
                <h2>Réservations Récentes</h2>
                <a href="reservations.php" class="btn btn-outline btn-sm">Voir tout</a>
            </div>
            <div class="card-body">
                <?php if (!empty($reservations_recentes)): ?>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Utilisateur</th>
                                <th>Vol</th>
                                <th>Date</th>
                                <th>Statut</th>
                                <th>Prix</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reservations_recentes as $reservation): ?>
                            <tr>
                                <td>#<?php echo $reservation['id']; ?></td>
                                <td><?php echo htmlspecialchars($reservation['prenom'] . ' ' . $reservation['nom']); ?></td>
                                <td><?php echo htmlspecialchars($reservation['depart'] . ' → ' . $reservation['arrivee']); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($reservation['date_reservation'])); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $reservation['statut']; ?>">
                                        <?php echo ucfirst($reservation['statut']); ?>
                                    </span>
                                </td>
                                <td><?php echo number_format($reservation['prix_total'], 2, ',', ' '); ?>€</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div style="text-align: center; padding: 40px; color: #64748b;">
                    <i class="fas fa-ticket-alt" style="font-size: 3rem; margin-bottom: 15px; opacity: 0.5;"></i>
                    <p>Aucune réservation récente</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Utilisateurs récents -->
        <div class="card">
            <div class="card-header">
                <h2>Utilisateurs Récents</h2>
                <a href="utilisateurs.php" class="btn btn-outline btn-sm">Voir tout</a>
            </div>
            <div class="card-body">
                <?php if (!empty($utilisateurs_recents)): ?>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Email</th>
                                <th>Rôle</th>
                                <th>Inscription</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($utilisateurs_recents as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <span class="role-badge role-<?php echo $user['role']; ?>">
                                        <?php echo ucfirst($user['role']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($user['date_inscription'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div style="text-align: center; padding: 40px; color: #64748b;">
                    <i class="fas fa-users" style="font-size: 3rem; margin-bottom: 15px; opacity: 0.5;"></i>
                    <p>Aucun utilisateur récent</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="card">
        <div class="card-header">
            <h2>Statistiques Rapides</h2>
        </div>
        <div class="card-body">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div>
                    <h4>Utilisateurs</h4>
                    <div style="display: grid; gap: 10px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid #e2e8f0;">
                            <span>Total</span>
                            <strong><?php echo $stats['total_utilisateurs']; ?></strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid #e2e8f0;">
                            <span>Administrateurs</span>
                            <strong><?php echo $stats['total_admins']; ?></strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid #e2e8f0;">
                            <span>Utilisateurs</span>
                            <strong><?php echo $stats['total_users']; ?></strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 0;">
                            <span>Actifs (30j)</span>
                            <strong><?php echo $stats['utilisateurs_actifs']; ?></strong>
                        </div>
                    </div>
                </div>
                <div>
                    <h4>Activité</h4>
                    <div style="display: grid; gap: 10px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid #e2e8f0;">
                            <span>Réservations ce mois</span>
                            <strong><?php echo $stats['reservations_mois']; ?></strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid #e2e8f0;">
                            <span>Vols à venir</span>
                            <strong><?php echo $stats['vols_futurs']; ?></strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 0;">
                            <span>CA moyen</span>
                            <strong>
                                <?php 
                                $ca_moyen = $stats['total_reservations'] > 0 ? $stats['chiffre_affaires'] / $stats['total_reservations'] : 0;
                                echo number_format($ca_moyen, 2, ',', ' ') . '€';
                                ?>
                            </strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="card">
        <div class="card-header">
            <h2>Actions Rapides</h2>
        </div>
        <div class="card-body">
            <div class="quick-actions">
                <a href="utilisateurs.php?action=add" class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <h3>Ajouter Utilisateur</h3>
                    <p>Créer un nouveau compte utilisateur</p>
                </a>
                
                <a href="vols.php?action=add" class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-plane"></i>
                    </div>
                    <h3>Nouveau Vol</h3>
                    <p>Programmer un nouveau vol</p>
                </a>
                
                <a href="reservations.php" class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-ticket-alt"></i>
                    </div>
                    <h3>Gérer Réservations</h3>
                    <p>Voir et modifier les réservations</p>
                </a>
                
                <a href="compagnies.php?action=add" class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-building"></i>
                    </div>
                    <h3>Nouvelle Compagnie</h3>
                    <p>Ajouter une compagnie aérienne</p>
                </a>
            </div>
        </div>
    </div>
</div>

<style>
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin: 20px 0;
}

.stat-card {
    background: white;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    text-align: center;
    border-left: 4px solid;
}

.stat-card.success { border-left-color: #10b981; }
.stat-card.info { border-left-color: #2563eb; }
.stat-card.warning { border-left-color: #f59e0b; }
.stat-card.danger { border-left-color: #ef4444; }

.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 15px;
    font-size: 1.5rem;
}

.stat-card.success .stat-icon { background: #d1fae5; color: #065f46; }
.stat-card.info .stat-icon { background: #e0e7ff; color: #3730a3; }
.stat-card.warning .stat-icon { background: #fef3c7; color: #92400e; }
.stat-card.danger .stat-icon { background: #fee2e2; color: #dc2626; }

.stat-value {
    font-size: 2rem;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 5px;
}

.stat-label {
    color: #64748b;
    font-size: 0.9rem;
}

.dashboard-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin: 20px 0;
}

.quick-actions {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
}

.action-card {
    background: white;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    text-align: center;
    text-decoration: none;
    color: inherit;
    transition: transform 0.3s;
    border: 1px solid #e2e8f0;
}

.action-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 15px rgba(0,0,0,0.1);
}

.action-icon {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    background: #e0e7ff;
    color: #2563eb;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 15px;
    font-size: 1.2rem;
}

.role-badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: uppercase;
}

.role-admin { background: #fef3c7; color: #92400e; }
.role-user { background: #d1fae5; color: #065f46; }

.status-badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 500;
    display: inline-block;
}

.status-confirmée { background: #d1fae5; color: #065f46; }
.status-en_attente { background: #fef3c7; color: #92400e; }
.status-annulée { background: #fee2e2; color: #dc2626; }
</style>

<script>
    setPageTitle('Tableau de Bord');
</script>

</body>
</html>