<?php
session_start();
require_once '../includes/db.php';

// Vérifier si l'utilisateur est admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../vge64/connexion.php');
    exit;
}

// Récupérer l'ID de l'avion depuis l'URL
$avion_id = isset($_GET['avion_id']) ? intval($_GET['avion_id']) : 0;

// Récupérer tous les avions pour le sélecteur
$avions = $pdo->query("SELECT * FROM avions ORDER BY modele")->fetchAll(PDO::FETCH_ASSOC);

// Si un avion est sélectionné, récupérer ses sièges
$sieges = [];
$avion_selected = null;

if ($avion_id > 0) {
    // Récupérer les informations de l'avion
    $stmt = $pdo->prepare("SELECT * FROM avions WHERE id_avion = ?");
    $stmt->execute([$avion_id]);
    $avion_selected = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Récupérer les sièges de l'avion
    $stmt = $pdo->prepare("SELECT * FROM sieges_avion WHERE id_avion = ? ORDER BY rang, position");
    $stmt->execute([$avion_id]);
    $sieges = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Traitement des actions
$message = '';
$message_type = '';

// Générer automatiquement les sièges
if (isset($_POST['generate_sieges'])) {
    $avion_id = intval($_POST['avion_id']);
    $config_type = $_POST['configuration_sieges'];
    
    try {
        // Supprimer les sièges existants
        $stmt = $pdo->prepare("DELETE FROM sieges_avion WHERE id_avion = ?");
        $stmt->execute([$avion_id]);
        
        // Configurations prédéfinies
        $configurations = [
            'affaires_economique' => [
                'rangees_affaires' => 6,
                'rangees_economique' => 24,
                'colonnes_affaires' => ['A', 'B', 'E', 'F'],
                'colonnes_economique' => ['A', 'B', 'C', 'D', 'E', 'F']
            ],
            'tout_economique' => [
                'rangees_affaires' => 0,
                'rangees_economique' => 30,
                'colonnes_affaires' => [],
                'colonnes_economique' => ['A', 'B', 'C', 'D', 'E', 'F']
            ],
            'premium_economique' => [
                'rangees_affaires' => 4,
                'rangees_premium' => 8,
                'rangees_economique' => 18,
                'colonnes_affaires' => ['A', 'B', 'E', 'F'],
                'colonnes_premium' => ['A', 'B', 'C', 'D', 'E', 'F'],
                'colonnes_economique' => ['A', 'B', 'C', 'D', 'E', 'F']
            ]
        ];
        
        $config = $configurations[$config_type];
        $rangee_num = 1;
        
        // CORRECTION : Sièges Affaires - ajouter les variables manquantes
        for ($i = 0; $i < $config['rangees_affaires']; $i++) {
            foreach ($config['colonnes_affaires'] as $colonne) {
                $rang = $rangee_num; // ← VARIABLE AJOUTÉE
                $position = $colonne; // ← VARIABLE AJOUTÉE
                $classe = 'affaires';
                $supplement_prix = 150.00;
                
                // CORRECTION : Uniformiser les noms de colonnes
                $stmt = $pdo->prepare("INSERT INTO sieges_avion (id_avion, rang, position, classe, supplement_prix) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$avion_id, $rang, $position, $classe, $supplement_prix]);
            }
            $rangee_num++;
        }
        
        // Sièges Premium (si configuré)
        if (isset($config['rangees_premium'])) {
            for ($i = 0; $i < $config['rangees_premium']; $i++) {
                foreach ($config['colonnes_premium'] as $colonne) {
                    $rang = $rangee_num; // ← VARIABLE AJOUTÉE
                    $position = $colonne; // ← VARIABLE AJOUTÉE
                    $classe = 'premium'; // ← CORRECTION : 'type' → 'classe'
                    $supplement_prix = 75.00; // ← CORRECTION : 'prix_supplement' → 'supplement_prix'
                    
                    // CORRECTION : Uniformiser les noms de colonnes
                    $stmt = $pdo->prepare("INSERT INTO sieges_avion (id_avion, rang, position, classe, supplement_prix) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$avion_id, $rang, $position, $classe, $supplement_prix]);
                }
                $rangee_num++;
            }
        }
        
        // Sièges Économique
        for ($i = 0; $i < $config['rangees_economique']; $i++) {
            foreach ($config['colonnes_economique'] as $colonne) {
                $rang = $rangee_num;
                $position = $colonne;
                $cote = ($colonne < 'D') ? 'gauche' : 'droite'; // CORRECTION : $position → $colonne
                $classe = 'economique';
                $supplement_prix = 0.00;
                
                // CORRECTION : Variable $id_avion → $avion_id
                $stmt = $pdo->prepare("INSERT INTO sieges_avion (id_avion, rang, position, cote, classe, supplement_prix) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$avion_id, $rang, $position, $cote, $classe, $supplement_prix]);
            }
            $rangee_num++;
        }
        
        $message = "Configuration des sièges générée avec succès.";
        $message_type = 'success';
        
        // Recharger les sièges
        $stmt = $pdo->prepare("SELECT * FROM sieges_avion WHERE id_avion = ? ORDER BY rang, position");
        $stmt->execute([$avion_id]);
        $sieges = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        $message = "Erreur lors de la génération des sièges: " . $e->getMessage();
        $message_type = 'error';
    }
}

// Modifier un siège
if (isset($_POST['edit_siege'])) {
    $siege_id = intval($_POST['id_siege']);
    $classe = $_POST['classe']; // ← CORRECTION : $type → $classe
    $supplement_prix = floatval($_POST['supplement_prix']); // ← CORRECTION : $prix_supplement → $supplement_prix
    
    try {
        // CORRECTION : Uniformiser les noms de colonnes
        $stmt = $pdo->prepare("UPDATE sieges_avion SET classe = ?, supplement_prix = ? WHERE id_siege = ?");
        $stmt->execute([$classe, $supplement_prix, $siege_id]);
        
        $message = "Siège modifié avec succès.";
        $message_type = 'success';
        
        // Recharger les sièges
        $stmt = $pdo->prepare("SELECT * FROM sieges_avion WHERE id_avion = ? ORDER BY rang, position");
        $stmt->execute([$avion_id]);
        $sieges = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        $message = "Erreur lors de la modification du siège: " . $e->getMessage();
        $message_type = 'error';
    }
}

// Supprimer un siège
if (isset($_GET['delete_siege'])) {
    $siege_id = intval($_GET['delete_siege']);
    
    try {
        // Vérifier si le siège est réservé
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM reservation_sieges WHERE id_siege = ?");
        $stmt->execute([$siege_id]);
        $reservations_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        if ($reservations_count > 0) {
            $message = "Impossible de supprimer ce siège car il est réservé.";
            $message_type = 'error';
        } else {
            $stmt = $pdo->prepare("DELETE FROM sieges_avion WHERE id_siege = ?");
            $stmt->execute([$siege_id]);
            
            $message = "Siège supprimé avec succès.";
            $message_type = 'success';
            
            // Recharger les sièges
            $stmt = $pdo->prepare("SELECT * FROM sieges_avion WHERE id_avion = ? ORDER BY rang, position");
            $stmt->execute([$avion_id]);
            $sieges = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e) {
        $message = "Erreur lors de la suppression du siège: " . $e->getMessage();
        $message_type = 'error';
    }
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

    <!-- Sélection de l'avion -->
    <div class="card">
        <div class="card-header">
            <h2>Gestion des Sièges</h2>
        </div>
        <div class="card-body">
            <form method="GET" class="form">
                <div class="form-group">
                    <label class="form-label" for="avion_id">Sélectionner un avion *</label>
                    <select class="form-control" id="avion_id" name="avion_id" required onchange="this.form.submit()">
                        <option value="">Choisir un avion</option>
                        <?php foreach ($avions as $avion): ?>
                        <option value="<?php echo $avion['id_avion']; ?>" 
                            <?php echo $avion_id == $avion['id_avion'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($avion['modele']); ?> (<?php echo $avion['capacite_total']; ?> places)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
        </div>
    </div>

    <?php if ($avion_selected): ?>
    <!-- Génération automatique des sièges -->
    <div class="card">
        <div class="card-header">
            <h3>Configuration Automatique</h3>
        </div>
        <div class="card-body">
            <p>Générer automatiquement une configuration de sièges pour l'avion <strong><?php echo htmlspecialchars($avion_selected['modele']); ?></strong> :</p>
            
            <form method="POST" class="form">
                <input type="hidden" name="avion_id" value="<?php echo $avion_id; ?>">
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="config_type">Type de configuration *</label>
                        <select class="form-control" id="configuration_sieges" name="configuration_sieges" required>
                            <option value="affaires_economique">Cabine Affaires + Économique (180 sièges)</option>
                            <option value="premium_economique">Cabine Premium + Économique (180 sièges)</option>
                            <option value="tout_economique">Tout Économique (180 sièges)</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" name="generate_sieges" class="btn btn-primary" 
                            onclick="return confirmAction('Cette action va supprimer tous les sièges existants. Continuer ?')">
                        <i class="fas fa-magic"></i> Générer la configuration
                    </button>
                </div>
            </form>
            
            <div style="margin-top: 20px; padding: 15px; background: #f0f9ff; border-radius: 6px;">
                <h4 style="color: #2563eb; margin-bottom: 10px;">Informations</h4>
                <ul style="color: #64748b; margin: 0;">
                    <li><strong>Cabine Affaires + Économique:</strong> 6 rangées affaires (2-2) + 24 rangées économique (3-3)</li>
                    <li><strong>Cabine Premium + Économique:</strong> 4 rangées affaires + 8 rangées premium + 18 rangées économique</li>
                    <li><strong>Tout Économique:</strong> 30 rangées économique (3-3)</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Carte visuelle des sièges -->
    <div class="card">
        <div class="card-header">
            <h3>Carte des Sièges - <?php echo htmlspecialchars($avion_selected['modele']); ?></h3>
            <div class="header-actions">
                <span class="text-success"><?php echo count($sieges); ?> sièges configurés</span>
            </div>
        </div>
        <div class="card-body">
            <?php if (!empty($sieges)): ?>
            <!-- Légende -->
            <div class="seat-legend" style="margin-bottom: 30px;">
                <div class="legend-item">
                    <div class="legend-color" style="background: #8b5cf6; border-color: #7c3aed;"></div>
                    <span>Affaires (+150€)</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: #f59e0b; border-color: #d97706;"></div>
                    <span>Premium (+75€)</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: #10b981; border-color: #059669;"></div>
                    <span>Économique</span>
                </div>
            </div>

            <!-- Carte des sièges -->
            <div class="seat-map" style="max-width: 800px; margin: 0 auto;">
                <!-- Cabine Affaires -->
                <?php
                $rangees_affaires = array_filter($sieges, function($siege) {
                    return $siege['classe'] === 'affaires';
                });
                if (!empty($rangees_affaires)):
                ?>
                <div class="cabin-section">
                    <h4 style="text-align: center; color: #8b5cf6; margin-bottom: 15px;">Cabine Affaires</h4>
                    <?php
                    $rangees = [];
                    foreach ($sieges as $siege) {
                        if ($siege['classe'] === 'affaires') {
                            $rangees[$siege['rang']][] = $siege;
                        }
                    }
                    ksort($rangees);
                    
                    foreach ($rangees as $rangee_num => $sieges_rangee):
                    ?>
                    <div class="seat-row">
                        <div class="row-number"><?php echo $rangee_num; ?></div>
                        <?php
                        // Configuration 2-2 pour la cabine affaires
                        $colonnes_affaires = ['A', 'B', 'E', 'F'];
                        foreach ($colonnes_affaires as $col_index => $colonne):
                            $siege_trouve = null;
                            foreach ($sieges_rangee as $siege) {
                                if ($siege['position'] === $colonne) {
                                    $siege_trouve = $siege;
                                    break;
                                }
                            }
                        ?>
                        <div class="seat-visual <?php echo $siege_trouve ? 'seat-' . $siege_trouve['classe'] : 'seat-empty'; ?>" 
                             title="<?php echo $siege_trouve ? $colonne . $rangee_num . ' - ' . ucfirst($siege_trouve['classe']) . ' (+' . $siege_trouve['supplement_prix'] . '€)' : 'Siège non configuré'; ?>">
                            <?php if ($siege_trouve): ?>
                            <?php echo $colonne; ?>
                            <?php endif; ?>
                        </div>
                        <?php if ($col_index === 1): ?>
                        <div class="aisle-visual">Allée</div>
                        <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="cabin-divider"></div>
                <?php endif; ?>

                <!-- Cabine Économique -->
                <div class="cabin-section">
                    <h4 style="text-align: center; color: #10b981; margin-bottom: 15px;">Cabine Économique</h4>
                    <?php
                    $rangees_economique = array_filter($sieges, function($siege) {
                        return $siege['classe'] === 'economiques' || $siege['classe'] === 'premiere';
                    });
                    
                    $rangees = [];
                    foreach ($sieges as $siege) {
                        if ($siege['classe'] === 'economique' || $siege['classe'] === 'première') {
                            $rangees[$siege['rang']][] = $siege;
                        }
                    }
                    ksort($rangees);
                    
                    foreach ($rangees as $rangee_num => $sieges_rangee):
                    ?>
                    <div class="seat-row">
                        <div class="row-number"><?php echo $rangee_num; ?></div>
                        <?php
                        // Configuration 3-3 pour la cabine économique
                        $colonnes_economique = ['A', 'B', 'C', 'D', 'E', 'F'];
                        foreach ($colonnes_economique as $col_index => $colonne):
                            $siege_trouve = null;
                            foreach ($sieges_rangee as $siege) {
                                if ($siege['position'] === $colonne) {
                                    $siege_trouve = $siege;
                                    break;
                                }
                            }
                        ?>
                        <div class="seat-visual <?php echo $siege_trouve ? 'seat-' . $siege_trouve['classe'] : 'seat-empty'; ?>" 
                             title="<?php echo $siege_trouve ? $colonne . $rangee_num . ' - ' . ucfirst($siege_trouve['classe']) . ' (+' . $siege_trouve['supplement_prix'] . '€)' : 'Siège non configuré'; ?>">
                            <?php if ($siege_trouve): ?>
                            <?php echo $colonne; ?>
                            <?php endif; ?>
                        </div>
                        <?php if ($col_index === 2): ?>
                        <div class="aisle-visual">Allée</div>
                        <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Liste détaillée des sièges -->
            <div style="margin-top: 40px;">
                <h4>Liste Détailée des Sièges</h4>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Rang</th>
                                <th>Siège</th>
                                <th>Type</th>
                                <th>Prix</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sieges as $siege): ?>
                            <tr>
                                <td><?php echo $siege['rang']; ?></td>
                                <td>
                                    <strong><?php echo $siege['position']; ?></strong>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo $siege['classe']; ?>">
                                        <?php echo ucfirst($siege['classe']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($siege['supplement_prix'] > 0): ?>
                                    <span class="text-success">+<?php echo number_format($siege['supplement_prix'], 2, ',', ' '); ?>€</span>
                                    <?php else: ?>
                                    <span style="color: #94a3b8;">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn btn-primary btn-sm" title="Modifier" 
                                                onclick="editSiege(<?php echo $siege['id_siege']; ?>, '<?php echo $siege['classe']; ?>', <?php echo $siege['supplement_prix']; ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <a href="sieges.php?avion_id=<?php echo $avion_id; ?>&delete_siege=<?php echo $siege['id_siege']; ?>" 
                                           class="btn btn-danger btn-sm" 
                                           title="Supprimer"
                                           onclick="return confirmAction('Êtes-vous sûr de vouloir supprimer ce siège ?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <?php else: ?>
            <div style="text-align: center; padding: 40px; color: #64748b;">
                <i class="fas fa-chair" style="font-size: 3rem; margin-bottom: 15px; opacity: 0.5;"></i>
                <h3>Aucun siège configuré</h3>
                <p>Utilisez la configuration automatique pour générer les sièges de cet avion.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal pour modifier un siège -->
    <div id="editSiegeModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Modifier le Siège</h3>
                <button class="modal-close" onclick="closeEditModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form method="POST" id="editSiegeForm">
                    <input type="hidden" name="siege_id" id="edit_siege_id">
                    
                    <div class="form-group">
                        <label class="form-label" for="edit_type">Type de siège *</label>
                        <select class="form-control" id="edit_type" name="type" required>
                            <option value="standard">Économique Standard</option>
                            <option value="premium">Économique Premium</option>
                            <option value="affaires">Affaires</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="edit_prix_supplement">Supplément de prix (€) *</label>
                        <input type="number" class="form-control" id="edit_prix_supplement" name="prix_supplement" 
                               step="0.01" min="0" required>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" name="edit_siege" class="btn btn-primary">
                            <i class="fas fa-save"></i> Modifier le siège
                        </button>
                        <button type="button" class="btn btn-outline" onclick="closeEditModal()">Annuler</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
.seat-map {
    font-family: 'Poppins', sans-serif;
}

.cabin-section {
    margin-bottom: 30px;
}

.seat-row {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-bottom: 10px;
    gap: 5px;
}

.row-number {
    width: 30px;
    text-align: center;
    font-weight: 600;
    color: #64748b;
    font-size: 0.9rem;
}

.seat-visual {
    width: 35px;
    height: 35px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
    font-weight: 600;
    color: white;
    cursor: pointer;
    transition: all 0.3s;
}

.seat-visual:hover {
    transform: scale(1.1);
}

.seat-standard {
    background: #10b981;
    border: 2px solid #059669;
}

.seat-premium {
    background: #f59e0b;
    border: 2px solid #d97706;
}

.seat-affaires {
    background: #8b5cf6;
    border: 2px solid #7c3aed;
}

.seat-empty {
    background: #e2e8f0;
    border: 2px solid #cbd5e1;
    color: #64748b;
}

.aisle-visual {
    width: 40px;
    text-align: center;
    color: #64748b;
    font-size: 0.7rem;
    font-weight: 600;
}

.cabin-divider {
    height: 2px;
    background: linear-gradient(90deg, transparent, #64748b, transparent);
    margin: 20px 0;
}

.seat-legend {
    display: flex;
    justify-content: center;
    gap: 20px;
    margin: 20px 0;
    flex-wrap: wrap;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 8px;
}

.legend-color {
    width: 20px;
    height: 20px;
    border-radius: 4px;
    border: 2px solid;
}
</style>

<script>
setPageTitle('Gestion des Sièges');

// Ouvrir la modal d'édition
function editSiege(siegeId, type, prixSupplement) {
    document.getElementById('edit_siege_id').value = siegeId;
    document.getElementById('edit_type').value = type;
    document.getElementById('edit_prix_supplement').value = prixSupplement;
    document.getElementById('editSiegeModal').style.display = 'flex';
}

// Fermer la modal d'édition
function closeEditModal() {
    document.getElementById('editSiegeModal').style.display = 'none';
}

// Fermer la modal en cliquant à l'extérieur
window.onclick = function(event) {
    const modal = document.getElementById('editSiegeModal');
    if (event.target === modal) {
        closeEditModal();
    }
}

// Soumettre le formulaire d'édition
document.getElementById('editSiegeForm').addEventListener('submit', function(e) {
    // Validation supplémentaire si nécessaire
    const prix = parseFloat(document.getElementById('edit_prix_supplement').value);
    if (prix < 0) {
        e.preventDefault();
        alert('Le supplément de prix ne peut pas être négatif.');
        return;
    }
});
</script>

</body>
</html>