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

// Ajouter un avion


// Modifier un avion
if (isset($_POST['add_avion'])) {
    $modele = trim($_POST['modele']);
    $id_compagnie = intval($_POST['id_compagnie']);
    $capacite_total = intval($_POST['capacite_total']);
    $configuration = $_POST['configuration_sieges'] ? json_encode($_POST['configuration_sieges']) : null;

    try {
        $stmt = $pdo->prepare("INSERT INTO avions (modele, id_compagnie, capacite_total, configuration_sieges) VALUES (?, ?, ?, ?)");
        $stmt->execute([$modele, $id_compagnie, $capacite_total, $configuration]);

        $message = "Avion ajouté avec succès.";
        $message_type = 'success';
    } catch (PDOException $e) {
        $message = "Erreur lors de l'ajout de l'avion: " . $e->getMessage();
        $message_type = 'error';
    }
}
// Supprimer un avion
if (isset($_GET['delete'])) {
    $avion_id = intval($_GET['delete']);
    
    try {
        // Vérifier si l'avion a des vols
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM vols WHERE id_avion = ?");
        $stmt->execute([$avion_id]);
        $vols_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        if ($vols_count > 0) {
            $message = "Impossible de supprimer cet avion car il est utilisé dans des vols.";
            $message_type = 'error';
        } else {
            // Supprimer d'abord les sièges
            $stmt = $pdo->prepare("DELETE FROM sieges_avion WHERE id_avion = ?");
            $stmt->execute([$avion_id]);
            
            // Puis supprimer l'avion
            $stmt = $pdo->prepare("DELETE FROM avions WHERE id_avion = ?");
            $stmt->execute([$avion_id]);
            
            $message = "Avion supprimé avec succès.";
            $message_type = 'success';
        }
    } catch (PDOException $e) {
        $message = "Erreur lors de la suppression: " . $e->getMessage();
        $message_type = 'error';
    }
}

// Récupérer tous les avions
$stmt = $pdo->query("
    SELECT a.*, 
           (SELECT COUNT(*) FROM vols v WHERE v.id_avion = a.id_avion) as vols_count,
           (SELECT COUNT(*) FROM sieges_avion s WHERE s.id_avion = a.id_avion) as sieges_count
    FROM avions a 
    ORDER BY a.modele
");
$avions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer un avion spécifique pour édition
$avion_to_edit = null;
if (isset($_GET['edit'])) {
    $avion_id = intval($_GET['edit']);
    $stmt = $pdo->prepare("SELECT * FROM avions WHERE id_avion = ?");
    $stmt->execute([$avion_id]);
    $avion_to_edit = $stmt->fetch(PDO::FETCH_ASSOC);
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
            <h2><?php echo $avion_to_edit ? 'Modifier l\'avion' : 'Ajouter un avion'; ?></h2>
        </div>
        <div class="card-body">
            <form method="POST" class="form">
                <?php if ($avion_to_edit): ?>
                    <input type="hidden" name="avion_id" value="<?php echo $avion_to_edit['id_avion']; ?>">
                <?php endif; ?>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="modele">Modèle de l'avion *</label>
                        <input type="text" class="form-control" id="modele" name="modele" 
                               value="<?php echo $avion_to_edit ? htmlspecialchars($avion_to_edit['modele']) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="capacite">Capacité (sièges) *</label>
                        <input type="number" class="form-control" id="capacite_total" name="capacite_total" 
                               value="<?php echo $avion_to_edit ? $avion_to_edit['capacite_total'] : ''; ?>" required min="1" max="1000">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="configuration_sieges">configuration des sieges(km/h)</label>
                        <input type="number" class="form-control" id="configuration_sieges" name="configuration_sieges" 
                               value="<?php echo $avion_to_edit ? $avion_to_edit['configuration_sieges'] : ''; ?>" min="0">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="created_at">crée à (heure)</label>
                        <input type="number" class="form-control" id="created_at" name="created_at" 
                               value="<?php echo $avion_to_edit ? $avion_to_edit['created_at'] : ''; ?>" min="0">
                    </div>
                </div>
                
             
                
                <div class="form-actions">
                    <?php if ($avion_to_edit): ?>
                        <button type="submit" name="edit_avion" class="btn btn-primary">
                            <i class="fas fa-save"></i> Modifier l'avion
                        </button>
                        <a href="avions.php" class="btn btn-outline">Annuler</a>
                    <?php else: ?>
                        <button type="submit" name="add_avion" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Ajouter l'avion
                        </button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Liste des avions -->
    <div class="card">
        <div class="card-header">
            <h2>Flotte d'Avions</h2>
            <div class="header-actions">
                <input type="text" id="searchAvions" placeholder="Rechercher un avion..." class="form-control" style="width: 250px;">
            </div>
        </div>
        <div class="card-body">
            <div class="table-container">
                <table class="table" id="avionsTable">
                    <thead>
                        <tr>
                            <th>Modèles</th>
                            <th>Capacités</th>
                            <th>Vitesse</th>
                            <th>Dates</th>
                            <th>Vols</th>
                            <th>Sièges</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($avions as $avion): ?>
                        <tr>
                            <td>
                                <span class="text-success"><?php echo $avion['modele']; ?> sièges</span>
                                  
                            </td>
                            <td>
                                <span class="text-success"><?php echo $avion['capacite_total']; ?> sièges</span>
                            </td>
                            
                            <td>
                              <?php if (!empty($avion['configuration_sieges'])): ?>
                              <?php 
                              $config = json_decode($avion['configuration_sieges'], true);
                              if (is_array($config)) {
                               echo "<strong>1ère :</strong> " . ($config['premiere'] ?? 0);
                               echo " | <strong>Affaires :</strong> " . ($config['affaires'] ?? 0);
                               echo " | <strong>Éco :</strong> " . ($config['economique'] ?? 0);
                             } else {
                                echo "<span style='color:#94a3b8;'>Format invalide</span>";
                              }
                                ?>
                               <?php else: ?>
                              <span style="color:#94a3b8;">-</span>
                              <?php endif; ?>
                            </td>

                            <td>
                                <span class="text-success"><?php echo $avion['created_at']; ?> sièges</span>
                            </td>

                            <td>
                                <span class="<?php echo $avion['vols_count'] == 0 ? 'text-warning' : 'text-success'; ?>">
                                    <?php echo $avion['vols_count']; ?> vol(s)
                                </span>
                            </td>
                            <td>
                                <span class="<?php echo $avion['sieges_count'] == 0 ? 'text-danger' : 'text-success'; ?>">
                                    <?php echo $avion['sieges_count']; ?> configuré(s)
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="avions.php?edit=<?php echo $avion['id_avion']; ?>" class="btn btn-primary btn-sm" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="sieges.php?avion_id=<?php echo $avion['id_avion']; ?>" class="btn btn-info btn-sm" title="Gérer les sièges">
                                        <i class="fas fa-chair"></i>
                                    </a>
                                    <a href="avions.php?delete=<?php echo $avion['id_avion']; ?>" 
                                       class="btn btn-danger btn-sm" 
                                       title="Supprimer"
                                       onclick="return confirmAction('Êtes-vous sûr de vouloir supprimer cet avion ?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if (empty($avions)): ?>
            <div style="text-align: center; padding: 40px; color: #64748b;">
                <i class="fas fa-plane" style="font-size: 3rem; margin-bottom: 15px; opacity: 0.5;"></i>
                <h3>Aucun avion trouvé</h3>
                <p>Commencez par ajouter votre premier avion à la flotte.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
setPageTitle('Gestion des Avions');

// Recherche en temps réel
document.getElementById('searchAvions').addEventListener('input', function() {
    const filter = this.value.toLowerCase();
    const rows = document.querySelectorAll('#avionsTable tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
});
</script>

</body>
</html>