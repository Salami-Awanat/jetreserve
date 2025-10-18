<?php
session_start();
require_once '../includes/db.php';

// Vérifier si l'utilisateur est admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../vge64/connexion.php');
    exit;
}

// Traitement des actions
$message = '';
$message_type = '';

// Récupérer les compagnies et avions pour les formulaires
$compagnies = $pdo->query("SELECT * FROM compagnies ORDER BY nom_compagnie")->fetchAll(PDO::FETCH_ASSOC);
$avions = $pdo->query("SELECT * FROM avions ORDER BY modele")->fetchAll(PDO::FETCH_ASSOC);

// Ajouter un vol
if (isset($_POST['add_vol'])) {
    $numero_vol = trim($_POST['numero_vol']);
    $id_compagnie = intval($_POST['id_compagnie']);
    $id_avion = intval($_POST['id_avion']);
    $depart = trim($_POST['depart']);
    $arrivee = trim($_POST['arrivee']);
    $date_depart = $_POST['date_depart'];
    $date_arrivee = $_POST['date_arrivee'];
    $prix = floatval($_POST['prix']);
    $places_disponibles = intval($_POST['places_disponibles']);
    $escales = intval($_POST['escales']);
    $classe = $_POST['classe'];
    
    try {
        // Vérifier si le numéro de vol existe déjà
        $stmt = $pdo->prepare("SELECT id_vol FROM vols WHERE numero_vol = ? AND id_compagnie = ?");
        $stmt->execute([$numero_vol, $id_compagnie]);
        
        if ($stmt->rowCount() > 0) {
            $message = "Un vol avec ce numéro existe déjà pour cette compagnie.";
            $message_type = 'error';
        } else {
            $stmt = $pdo->prepare("INSERT INTO vols (numero_vol, id_compagnie, id_avion, depart, arrivee, date_depart, date_arrivee, prix, places_disponibles, escales, classe) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$numero_vol, $id_compagnie, $id_avion, $depart, $arrivee, $date_depart, $date_arrivee, $prix, $places_disponibles, $escales, $classe]);
            
            $message = "Vol ajouté avec succès.";
            $message_type = 'success';
        }
    } catch (PDOException $e) {
        $message = "Erreur lors de l'ajout du vol: " . $e->getMessage();
        $message_type = 'error';
    }
}

// Modifier un vol
if (isset($_POST['edit_vol'])) {
    $vol_id = intval($_POST['vol_id']);
    $numero_vol = trim($_POST['numero_vol']);
    $id_compagnie = intval($_POST['id_compagnie']);
    $id_avion = intval($_POST['id_avion']);
    $depart = trim($_POST['depart']);
    $arrivee = trim($_POST['arrivee']);
    $date_depart = $_POST['date_depart'];
    $date_arrivee = $_POST['date_arrivee'];
    $prix = floatval($_POST['prix']);
    $places_disponibles = intval($_POST['places_disponibles']);
    $escales = intval($_POST['escales']);
    $classe = $_POST['classe'];
    
    try {
        $stmt = $pdo->prepare("UPDATE vols SET numero_vol = ?, id_compagnie = ?, id_avion = ?, depart = ?, arrivee = ?, date_depart = ?, date_arrivee = ?, prix = ?, places_disponibles = ?, escales = ?, classe = ? WHERE id_vol = ?");
        $stmt->execute([$numero_vol, $id_compagnie, $id_avion, $depart, $arrivee, $date_depart, $date_arrivee, $prix, $places_disponibles, $escales, $classe, $vol_id]);
        
        $message = "Vol modifié avec succès.";
        $message_type = 'success';
    } catch (PDOException $e) {
        $message = "Erreur lors de la modification du vol: " . $e->getMessage();
        $message_type = 'error';
    }
}

// Supprimer un vol
if (isset($_GET['delete'])) {
    $vol_id = intval($_GET['delete']);
    
    try {
        // Vérifier si le vol a des réservations
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM reservations WHERE id_vol = ?");
        $stmt->execute([$vol_id]);
        $reservations_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        if ($reservations_count > 0) {
            $message = "Impossible de supprimer ce vol car il a des réservations associées.";
            $message_type = 'error';
        } else {
            $stmt = $pdo->prepare("DELETE FROM vols WHERE id_vol = ?");
            $stmt->execute([$vol_id]);
            
            $message = "Vol supprimé avec succès.";
            $message_type = 'success';
        }
    } catch (PDOException $e) {
        $message = "Erreur lors de la suppression du vol: " . $e->getMessage();
        $message_type = 'error';
    }
}

// Récupérer tous les vols avec les informations des compagnies et avions
$stmt = $pdo->query("
    SELECT v.*, c.nom_compagnie, c.code_compagnie, a.modele as avion_modele,
           (SELECT COUNT(*) FROM reservations r WHERE r.id_vol = v.id_vol) as reservations_count
    FROM vols v 
    JOIN compagnies c ON v.id_compagnie = c.id_compagnie 
    JOIN avions a ON v.id_avion = a.id_avion 
    ORDER BY v.date_depart DESC
");
$vols = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer un vol spécifique pour édition
$vol_to_edit = null;
if (isset($_GET['edit'])) {
    $vol_id = intval($_GET['edit']);
    $stmt = $pdo->prepare("SELECT * FROM vols WHERE id_vol = ?");
    $stmt->execute([$vol_id]);
    $vol_to_edit = $stmt->fetch(PDO::FETCH_ASSOC);
}

include 'includes/header.php';
?>

<div class="content">
    <!-- Message de notification -->
    <?php if ($message): ?>
    <div class="notification notification-<?php echo $message_type; ?>" style="margin-bottom: 20px;">
        <div class="notification-content">
            <i class="fas fa-<?php echo $message_type === 'success' ? 'check' : 'exclamation'; ?>-circle"></i>
            <span><?php echo $message; ?></span>
        </div>
        <button class="notification-close" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <?php endif; ?>

    <!-- Formulaire d'ajout/modification -->
    <div class="card">
        <div class="card-header">
            <h2><?php echo $vol_to_edit ? 'Modifier le vol' : 'Ajouter un vol'; ?></h2>
        </div>
        <div class="card-body">
            <form method="POST" class="form">
                <?php if ($vol_to_edit): ?>
                    <input type="hidden" name="vol_id" value="<?php echo $vol_to_edit['id_vol']; ?>">
                <?php endif; ?>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="numero_vol">Numéro de vol *</label>
                        <input type="text" class="form-control" id="numero_vol" name="numero_vol" 
                               value="<?php echo $vol_to_edit ? htmlspecialchars($vol_to_edit['numero_vol']) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="id_compagnie">Compagnie *</label>
                        <select class="form-control" id="id_compagnie" name="id_compagnie" required>
                            <option value="">Sélectionnez une compagnie</option>
                            <?php foreach ($compagnies as $compagnie): ?>
                            <option value="<?php echo $compagnie['id_compagnie']; ?>" 
                                <?php echo ($vol_to_edit && $vol_to_edit['id_compagnie'] == $compagnie['id_compagnie']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($compagnie['nom_compagnie']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="depart">Départ *</label>
                        <input type="text" class="form-control" id="depart" name="depart" 
                               value="<?php echo $vol_to_edit ? htmlspecialchars($vol_to_edit['depart']) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="arrivee">Arrivée *</label>
                        <input type="text" class="form-control" id="arrivee" name="arrivee" 
                               value="<?php echo $vol_to_edit ? htmlspecialchars($vol_to_edit['arrivee']) : ''; ?>" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="date_depart">Date et heure de départ *</label>
                        <input type="datetime-local" class="form-control" id="date_depart" name="date_depart" 
                               value="<?php echo $vol_to_edit ? date('Y-m-d\TH:i', strtotime($vol_to_edit['date_depart'])) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="date_arrivee">Date et heure d'arrivée *</label>
                        <input type="datetime-local" class="form-control" id="date_arrivee" name="date_arrivee" 
                               value="<?php echo $vol_to_edit ? date('Y-m-d\TH:i', strtotime($vol_to_edit['date_arrivee'])) : ''; ?>" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="id_avion">Avion *</label>
                        <select class="form-control" id="id_avion" name="id_avion" required>
                            <option value="">Sélectionnez un avion</option>
                            <?php foreach ($avions as $avion): ?>
                            <option value="<?php echo $avion['id_avion']; ?>" 
                                <?php echo ($vol_to_edit && $vol_to_edit['id_avion'] == $avion['id_avion']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($avion['modele']); ?> (<?php echo $avion['capacite']; ?> places)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="places_disponibles">Places disponibles *</label>
                        <input type="number" class="form-control" id="places_disponibles" name="places_disponibles" 
                               value="<?php echo $vol_to_edit ? $vol_to_edit['places_disponibles'] : ''; ?>" required min="1">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="prix">Prix (€) *</label>
                        <input type="number" class="form-control" id="prix" name="prix" step="0.01"
                               value="<?php echo $vol_to_edit ? $vol_to_edit['prix'] : ''; ?>" required min="0">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="escales">Nombre d'escales</label>
                        <input type="number" class="form-control" id="escales" name="escales" 
                               value="<?php echo $vol_to_edit ? $vol_to_edit['escales'] : '0'; ?>" min="0" max="5">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="classe">Classe *</label>
                    <select class="form-control" id="classe" name="classe" required>
                        <option value="économique" <?php echo ($vol_to_edit && $vol_to_edit['classe'] === 'économique') ? 'selected' : ''; ?>>Économique</option>
                        <option value="affaires" <?php echo ($vol_to_edit && $vol_to_edit['classe'] === 'affaires') ? 'selected' : ''; ?>>Affaires</option>
                        <option value="première" <?php echo ($vol_to_edit && $vol_to_edit['classe'] === 'première') ? 'selected' : ''; ?>>Première</option>
                    </select>
                </div>
                
                <div class="form-actions">
                    <?php if ($vol_to_edit): ?>
                        <button type="submit" name="edit_vol" class="btn btn-primary">
                            <i class="fas fa-save"></i> Modifier le vol
                        </button>
                        <a href="vols.php" class="btn btn-outline">Annuler</a>
                    <?php else: ?>
                        <button type="submit" name="add_vol" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Ajouter le vol
                        </button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Liste des vols -->
    <div class="card">
        <div class="card-header">
            <h2>Liste des Vols</h2>
            <div class="header-actions">
                <input type="text" id="searchVols" placeholder="Rechercher un vol..." class="form-control" style="width: 250px;">
            </div>
        </div>
        <div class="card-body">
            <div class="table-container">
                <table class="table" id="volsTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Vol</th>
                            <th>Itinéraire</th>
                            <th>Dates</th>
                            <th>Avion</th>
                            <th>Places</th>
                            <th>Prix</th>
                            <th>Classe</th>
                            <th>Réservations</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($vols as $vol): ?>
                        <tr>
                            <td><?php echo $vol['id_vol']; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($vol['code_compagnie'] . $vol['numero_vol']); ?></strong>
                                <div style="color: #64748b; font-size: 0.8rem;"><?php echo htmlspecialchars($vol['nom_compagnie']); ?></div>
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($vol['depart']); ?> → <?php echo htmlspecialchars($vol['arrivee']); ?></strong>
                                <?php if ($vol['escales'] > 0): ?>
                                <div style="color: #f59e0b; font-size: 0.8rem;"><?php echo $vol['escales']; ?> escale(s)</div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="font-size: 0.9rem;">
                                    <div><strong>Départ:</strong> <?php echo date('d/m/Y H:i', strtotime($vol['date_depart'])); ?></div>
                                    <div><strong>Arrivée:</strong> <?php echo date('d/m/Y H:i', strtotime($vol['date_arrivee'])); ?></div>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($vol['avion_modele']); ?></td>
                            <td>
                                <span class="<?php echo $vol['places_disponibles'] < 10 ? 'text-danger' : 'text-success'; ?>">
                                    <?php echo $vol['places_disponibles']; ?>
                                </span>
                            </td>
                            <td><?php echo number_format($vol['prix'], 2, ',', ' '); ?>€</td>
                            <td>
                                <span class="status-badge status-<?php echo $vol['classe']; ?>">
                                    <?php echo ucfirst($vol['classe']); ?>
                                </span>
                            </td>
                            <td><?php echo $vol['reservations_count']; ?></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="vols.php?edit=<?php echo $vol['id_vol']; ?>" class="btn btn-primary btn-sm" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="vols.php?delete=<?php echo $vol['id_vol']; ?>" 
                                       class="btn btn-danger btn-sm" 
                                       title="Supprimer"
                                       onclick="return confirmAction('Êtes-vous sûr de vouloir supprimer ce vol ?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if (empty($vols)): ?>
            <div style="text-align: center; padding: 40px; color: #64748b;">
                <i class="fas fa-plane" style="font-size: 3rem; margin-bottom: 15px; opacity: 0.5;"></i>
                <h3>Aucun vol trouvé</h3>
                <p>Commencez par ajouter votre premier vol.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.text-danger { color: #ef4444; font-weight: 600; }
.text-success { color: #10b981; font-weight: 600; }
.text-warning { color: #f59e0b; font-weight: 600; }
</style>

<script>
setPageTitle('Gestion des Vols');

// Recherche en temps réel
document.getElementById('searchVols').addEventListener('input', function() {
    const filter = this.value.toLowerCase();
    const rows = document.querySelectorAll('#volsTable tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
});

// Validation des dates
document.addEventListener('DOMContentLoaded', function() {
    const dateDepart = document.getElementById('date_depart');
    const dateArrivee = document.getElementById('date_arrivee');
    
    if (dateDepart && dateArrivee) {
        dateDepart.addEventListener('change', function() {
            if (dateArrivee.value && new Date(dateArrivee.value) <= new Date(this.value)) {
                alert('La date d\'arrivée doit être postérieure à la date de départ.');
                this.value = '';
            }
        });
        
        dateArrivee.addEventListener('change', function() {
            if (dateDepart.value && new Date(this.value) <= new Date(dateDepart.value)) {
                alert('La date d\'arrivée doit être postérieure à la date de départ.');
                this.value = '';
            }
        });
    }
});
</script>

</body>
</html>