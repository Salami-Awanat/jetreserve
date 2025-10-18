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

// Ajouter un utilisateur
if (isset($_POST['add_user'])) {
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];
    $statut = $_POST['statut'];
    
    try {
        // Vérifier si l'email existe déjà
        $stmt = $pdo->prepare("SELECT id_user FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() > 0) {
            $message = "Un utilisateur avec cet email existe déjà.";
            $message_type = 'error';
        } else {
            // Hasher le mot de passe
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("INSERT INTO users (nom, prenom, email, password, role, statut, date_creation) VALUES (?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$nom, $prenom, $email, $password_hash, $role, $statut]);
            
            $message = "Utilisateur ajouté avec succès.";
            $message_type = 'success';
        }
    } catch (PDOException $e) {
        $message = "Erreur lors de l'ajout de l'utilisateur: " . $e->getMessage();
        $message_type = 'error';
    }
}

// Modifier un utilisateur
if (isset($_POST['edit_user'])) {
    $user_id = intval($_POST['user_id']);
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];
    $statut = $_POST['statut'];
    
    try {
        // Vérifier si l'email existe déjà pour un autre utilisateur
        $stmt = $pdo->prepare("SELECT id_user FROM users WHERE email = ? AND id_user != ?");
        $stmt->execute([$email, $user_id]);
        
        if ($stmt->rowCount() > 0) {
            $message = "Un autre utilisateur avec cet email existe déjà.";
            $message_type = 'error';
        } else {
            $stmt = $pdo->prepare("UPDATE users SET nom = ?, prenom = ?, email = ?, role = ?, statut = ? WHERE id_user = ?");
            $stmt->execute([$nom, $prenom, $email, $role, $statut, $user_id]);
            
            $message = "Utilisateur modifié avec succès.";
            $message_type = 'success';
        }
    } catch (PDOException $e) {
        $message = "Erreur lors de la modification de l'utilisateur: " . $e->getMessage();
        $message_type = 'error';
    }
}

// Changer le mot de passe
if (isset($_POST['change_password'])) {
    $user_id = intval($_POST['user_id']);
    $new_password = $_POST['new_password'];
    
    try {
        $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id_user = ?");
        $stmt->execute([$password_hash, $user_id]);
        
        $message = "Mot de passe modifié avec succès.";
        $message_type = 'success';
    } catch (PDOException $e) {
        $message = "Erreur lors du changement de mot de passe: " . $e->getMessage();
        $message_type = 'error';
    }
}

// Supprimer un utilisateur
if (isset($_GET['delete'])) {
    $user_id = intval($_GET['delete']);
    
    try {
        // Vérifier si l'utilisateur a des réservations
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM reservations WHERE id_user = ?");
        $stmt->execute([$user_id]);
        $reservations_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        if ($reservations_count > 0) {
            $message = "Impossible de supprimer cet utilisateur car il a des réservations associées.";
            $message_type = 'error';
        } else {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id_user = ?");
            $stmt->execute([$user_id]);
            
            $message = "Utilisateur supprimé avec succès.";
            $message_type = 'success';
        }
    } catch (PDOException $e) {
        $message = "Erreur lors de la suppression de l'utilisateur: " . $e->getMessage();
        $message_type = 'error';
    }
}

// Récupérer tous les utilisateurs
$stmt = $pdo->query("
    SELECT u.*, 
           (SELECT COUNT(*) FROM reservations r WHERE r.id_user = u.id_user) as reservations_count,
           (SELECT COUNT(*) FROM reservations r WHERE r.id_user = u.id_user AND r.statut = 'confirmé') as reservations_confirmees
    FROM users u 
    ORDER BY u.date_creation DESC
");
$utilisateurs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer un utilisateur spécifique pour édition
$user_to_edit = null;
if (isset($_GET['edit'])) {
    $user_id = intval($_GET['edit']);
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id_user = ?");
    $stmt->execute([$user_id]);
    $user_to_edit = $stmt->fetch(PDO::FETCH_ASSOC);
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
            <h2><?php echo $user_to_edit ? 'Modifier l\'utilisateur' : 'Ajouter un utilisateur'; ?></h2>
        </div>
        <div class="card-body">
            <form method="POST" class="form">
                <?php if ($user_to_edit): ?>
                    <input type="hidden" name="user_id" value="<?php echo $user_to_edit['id_user']; ?>">
                <?php endif; ?>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="nom">Nom *</label>
                        <input type="text" class="form-control" id="nom" name="nom" 
                               value="<?php echo $user_to_edit ? htmlspecialchars($user_to_edit['nom']) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="prenom">Prénom *</label>
                        <input type="text" class="form-control" id="prenom" name="prenom" 
                               value="<?php echo $user_to_edit ? htmlspecialchars($user_to_edit['prenom']) : ''; ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="email">Email *</label>
                    <input type="email" class="form-control" id="email" name="email" 
                           value="<?php echo $user_to_edit ? htmlspecialchars($user_to_edit['email']) : ''; ?>" required>
                </div>
                
                <?php if (!$user_to_edit): ?>
                <div class="form-group">
                    <label class="form-label" for="password">Mot de passe *</label>
                    <input type="password" class="form-control" id="password" name="password" required minlength="6">
                </div>
                <?php endif; ?>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="role">Rôle *</label>
                        <select class="form-control" id="role" name="role" required>
                            <option value="client" <?php echo ($user_to_edit && $user_to_edit['role'] === 'client') ? 'selected' : ''; ?>>Client</option>
                            <option value="admin" <?php echo ($user_to_edit && $user_to_edit['role'] === 'admin') ? 'selected' : ''; ?>>Administrateur</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="statut">Statut *</label>
                        <select class="form-control" id="statut" name="statut" required>
                            <option value="actif" <?php echo ($user_to_edit && $user_to_edit['statut'] === 'actif') ? 'selected' : ''; ?>>Actif</option>
                            <option value="inactif" <?php echo ($user_to_edit && $user_to_edit['statut'] === 'inactif') ? 'selected' : ''; ?>>Inactif</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-actions">
                    <?php if ($user_to_edit): ?>
                        <button type="submit" name="edit_user" class="btn btn-primary">
                            <i class="fas fa-save"></i> Modifier l'utilisateur
                        </button>
                        <a href="utilisateurs.php" class="btn btn-outline">Annuler</a>
                    <?php else: ?>
                        <button type="submit" name="add_user" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Ajouter l'utilisateur
                        </button>
                    <?php endif; ?>
                </div>
            </form>
            
            <?php if ($user_to_edit): ?>
            <hr style="margin: 30px 0;">
            <h3>Changer le mot de passe</h3>
            <form method="POST" class="form" style="max-width: 400px;">
                <input type="hidden" name="user_id" value="<?php echo $user_to_edit['id_user']; ?>">
                <div class="form-group">
                    <label class="form-label" for="new_password">Nouveau mot de passe *</label>
                    <input type="password" class="form-control" id="new_password" name="new_password" required minlength="6">
                </div>
                <button type="submit" name="change_password" class="btn btn-warning">
                    <i class="fas fa-key"></i> Changer le mot de passe
                </button>
            </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- Liste des utilisateurs -->
    <div class="card">
        <div class="card-header">
            <h2>Liste des Utilisateurs</h2>
            <div class="header-actions">
                <input type="text" id="searchUsers" placeholder="Rechercher un utilisateur..." class="form-control" style="width: 250px;">
            </div>
        </div>
        <div class="card-body">
            <div class="table-container">
                <table class="table" id="usersTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom & Prénom</th>
                            <th>Email</th>
                            <th>Rôle</th>
                            <th>Statut</th>
                            <th>Réservations</th>
                            <th>Date d'inscription</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($utilisateurs as $user): ?>
                        <tr>
                            <td><?php echo $user['id_user']; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?></strong>
                            </td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td>
                                <span class="role-badge role-<?php echo $user['role']; ?>">
                                    <?php echo ucfirst($user['role']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="status-badge status-<?php echo $user['statut']; ?>">
                                    <?php echo ucfirst($user['statut']); ?>
                                </span>
                            </td>
                            <td>
                                <span title="Total: <?php echo $user['reservations_count']; ?>, Confirmées: <?php echo $user['reservations_confirmees']; ?>">
                                    <?php echo $user['reservations_count']; ?> (<?php echo $user['reservations_confirmees']; ?>)
                                </span>
                            </td>
                            <td><?php echo date('d/m/Y', strtotime($user['date_creation'])); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="utilisateurs.php?edit=<?php echo $user['id_user']; ?>" class="btn btn-primary btn-sm" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php if ($user['id_user'] != $_SESSION['user_id']): ?>
                                    <a href="utilisateurs.php?delete=<?php echo $user['id_user']; ?>" 
                                       class="btn btn-danger btn-sm" 
                                       title="Supprimer"
                                       onclick="return confirmAction('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                    <?php else: ?>
                                    <span class="btn btn-outline btn-sm" title="Vous ne pouvez pas vous supprimer vous-même">
                                        <i class="fas fa-trash"></i>
                                    </span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if (empty($utilisateurs)): ?>
            <div style="text-align: center; padding: 40px; color: #64748b;">
                <i class="fas fa-users" style="font-size: 3rem; margin-bottom: 15px; opacity: 0.5;"></i>
                <h3>Aucun utilisateur trouvé</h3>
                <p>Commencez par ajouter votre premier utilisateur.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.form-actions {
    margin-top: 20px;
    display: flex;
    gap: 10px;
}

.action-buttons {
    display: flex;
    gap: 5px;
}

.header-actions {
    display: flex;
    gap: 15px;
    align-items: center;
}

.notification {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    animation: slideIn 0.3s ease;
}

.notification-success {
    background: #d1fae5;
    color: #065f46;
    border: 1px solid #a7f3d0;
}

.notification-error {
    background: #fee2e2;
    color: #dc2626;
    border: 1px solid #fecaca;
}

.notification-content {
    display: flex;
    align-items: center;
    gap: 10px;
}

.notification-close {
    background: none;
    border: none;
    color: inherit;
    cursor: pointer;
    padding: 5px;
    border-radius: 4px;
    transition: background 0.3s;
}

.notification-close:hover {
    background: rgba(0,0,0,0.1);
}

@keyframes slideIn {
    from {
        transform: translateY(-10px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}
</style>

<script>
setPageTitle('Gestion des Utilisateurs');

// Confirmation pour la suppression
function confirmAction(message) {
    return confirm(message || 'Êtes-vous sûr de vouloir effectuer cette action ?');
}

// Recherche en temps réel
document.getElementById('searchUsers').addEventListener('input', function() {
    const filter = this.value.toLowerCase();
    const rows = document.querySelectorAll('#usersTable tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
});
</script>

</body>
</html>