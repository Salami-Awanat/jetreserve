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

// Ajouter une compagnie
if (isset($_POST['add_compagnie'])) {
    $nom_compagnie = trim($_POST['nom_compagnie']);
    $code_compagnie = trim($_POST['code_compagnie']);
    $pays = trim($_POST['pays']);
      
    try {
        // Vérifier si le code compagnie existe déjà
        $stmt = $pdo->prepare("SELECT id_compagnie FROM compagnies WHERE code_compagnie = ?");
        $stmt->execute([$code_compagnie]);
        
        if ($stmt->rowCount() > 0) {
            $message = "Une compagnie avec ce code existe déjà.";
            $message_type = 'error';
        } else {
            $stmt = $pdo->prepare("INSERT INTO compagnies (nom_compagnie, code_compagnie, pays, description) VALUES (?, ?, ?, ?)");
            $stmt->execute([$nom_compagnie, $code_compagnie, $pays, $description]);
            
            $message = "Compagnie ajoutée avec succès.";
            $message_type = 'success';
        }
    } catch (PDOException $e) {
        $message = "Erreur lors de l'ajout de la compagnie: " . $e->getMessage();
        $message_type = 'error';
    }
}

// Modifier une compagnie
if (isset($_POST['edit_compagnie'])) {
    $compagnie_id = intval($_POST['compagnie_id']);
    $nom_compagnie = trim($_POST['nom_compagnie']);
    $code_compagnie = trim($_POST['code_compagnie']);
    $pays = trim($_POST['pays']);
    $description = trim($_POST['description']);
    
    try {
        $stmt = $pdo->prepare("UPDATE compagnies SET nom_compagnie = ?, code_compagnie = ?, pays = ? WHERE id_compagnie = ?");
        $stmt->execute([$nom_compagnie, $code_compagnie, $pays, $compagnie_id]);
        
        $message = "Compagnie modifiée avec succès.";
        $message_type = 'success';
    } catch (PDOException $e) {
        $message = "Erreur lors de la modification: " . $e->getMessage();
        $message_type = 'error';
    }
}

// Supprimer une compagnie
if (isset($_GET['delete'])) {
    $compagnie_id = intval($_GET['delete']);
    
    try {
        // Vérifier si la compagnie a des vols
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM vols WHERE id_compagnie = ?");
        $stmt->execute([$compagnie_id]);
        $vols_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        if ($vols_count > 0) {
            $message = "Impossible de supprimer cette compagnie car elle a des vols associés.";
            $message_type = 'error';
        } else {
            $stmt = $pdo->prepare("DELETE FROM compagnies WHERE id_compagnie = ?");
            $stmt->execute([$compagnie_id]);
            
            $message = "Compagnie supprimée avec succès.";
            $message_type = 'success';
        }
    } catch (PDOException $e) {
        $message = "Erreur lors de la suppression: " . $e->getMessage();
        $message_type = 'error';
    }
}

// Récupérer toutes les compagnies
$stmt = $pdo->query("
    SELECT c.*, 
           (SELECT COUNT(*) FROM vols v WHERE v.id_compagnie = c.id_compagnie) as vols_count
    FROM compagnies c 
    ORDER BY c.nom_compagnie
");
$compagnies = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer une compagnie spécifique pour édition
$compagnie_to_edit = null;
if (isset($_GET['edit'])) {
    $compagnie_id = intval($_GET['edit']);
    $stmt = $pdo->prepare("SELECT * FROM compagnies WHERE id_compagnie = ?");
    $stmt->execute([$compagnie_id]);
    $compagnie_to_edit = $stmt->fetch(PDO::FETCH_ASSOC);
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
            <h2><?php echo $compagnie_to_edit ? 'Modifier la compagnie' : 'Ajouter une compagnie'; ?></h2>
        </div>
        <div class="card-body">
            <form method="POST" class="form">
                <?php if ($compagnie_to_edit): ?>
                    <input type="hidden" name="compagnie_id" value="<?php echo $compagnie_to_edit['id_compagnie']; ?>">
                <?php endif; ?>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="nom_compagnie">Nom de la compagnie *</label>
                        <input type="text" class="form-control" id="nom_compagnie" name="nom_compagnie" 
                               value="<?php echo $compagnie_to_edit ? htmlspecialchars($compagnie_to_edit['nom_compagnie']) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="code_compagnie">Code compagnie *</label>
                        <input type="text" class="form-control" id="code_compagnie" name="code_compagnie" 
                               value="<?php echo $compagnie_to_edit ? htmlspecialchars($compagnie_to_edit['code_compagnie']) : ''; ?>" 
                               required maxlength="2" style="text-transform: uppercase;">
                        <small style="color: #64748b;">2 lettres (ex: AF, BA, LH)</small>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="pays">Pays *</label>
                    <input type="text" class="form-control" id="pays" name="pays" 
                           value="<?php echo $compagnie_to_edit ? htmlspecialchars($compagnie_to_edit['pays']) : ''; ?>" required>
                </div>
               
                
                <div class="form-actions">
                    <?php if ($compagnie_to_edit): ?>
                        <button type="submit" name="edit_compagnie" class="btn btn-primary">
                            <i class="fas fa-save"></i> Modifier la compagnie
                        </button>
                        <a href="compagnies.php" class="btn btn-outline">Annuler</a>
                    <?php else: ?>
                        <button type="submit" name="add_compagnie" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Ajouter la compagnie
                        </button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Liste des compagnies -->
    <div class="card">
        <div class="card-header">
            <h2>Liste des Compagnies Aériennes</h2>
            <div class="header-actions">
                <input type="text" id="searchCompagnies" placeholder="Rechercher une compagnie..." class="form-control" style="width: 250px;">
            </div>
        </div>
        <div class="card-body">
            <div class="table-container">
                <table class="table" id="compagniesTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Compagnie</th>
                            <th>Code</th>
                            <th>Pays</th>
                            <th>Vols</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($compagnies as $compagnie): ?>
                        <tr>
                            <td><?php echo $compagnie['id_compagnie']; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($compagnie['nom_compagnie']); ?></strong>
                            </td>
                            <td>
                                <span class="status-badge status-active"><?php echo htmlspecialchars($compagnie['code_compagnie']); ?></span>
                            </td>
                            <td><?php echo htmlspecialchars($compagnie['pays']); ?></td>
                            <td>
                                <span class="<?php echo $compagnie['vols_count'] == 0 ? 'text-warning' : 'text-success'; ?>">
                                    <?php echo $compagnie['vols_count']; ?> vol(s)
                                </span>
                            </td>
                          
                            <td>
                                <div class="action-buttons">
                                    <a href="compagnies.php?edit=<?php echo $compagnie['id_compagnie']; ?>" class="btn btn-primary btn-sm" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="compagnies.php?delete=<?php echo $compagnie['id_compagnie']; ?>" 
                                       class="btn btn-danger btn-sm" 
                                       title="Supprimer"
                                       onclick="return confirmAction('Êtes-vous sûr de vouloir supprimer cette compagnie ?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if (empty($compagnies)): ?>
            <div style="text-align: center; padding: 40px; color: #64748b;">
                <i class="fas fa-building" style="font-size: 3rem; margin-bottom: 15px; opacity: 0.5;"></i>
                <h3>Aucune compagnie trouvée</h3>
                <p>Commencez par ajouter votre première compagnie aérienne.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
setPageTitle('Gestion des Compagnies');

// Mettre en majuscule le code compagnie
document.getElementById('code_compagnie')?.addEventListener('input', function() {
    this.value = this.value.toUpperCase();
});

// Recherche en temps réel
document.getElementById('searchCompagnies').addEventListener('input', function() {
    const filter = this.value.toLowerCase();
    const rows = document.querySelectorAll('#compagniesTable tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
});
</script>

</body>
</html>