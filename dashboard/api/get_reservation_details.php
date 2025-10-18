<?php
session_start();
require_once '../../includes/db.php';

// Vérifier si l'utilisateur est admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['success' => false, 'message' => 'Accès non autorisé']);
    exit;
}

if (!isset($_GET['id'])) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['success' => false, 'message' => 'ID de réservation manquant']);
    exit;
}

$reservation_id = intval($_GET['id']);

try {
    // Récupérer les détails de la réservation
    $stmt = $pdo->prepare("
        SELECT r.*, 
               u.nom as user_nom, u.prenom as user_prenom, u.email as user_email, u.telephone as user_telephone,
               v.numero_vol, v.depart, v.arrivee, v.date_depart, v.date_arrivee, v.prix as vol_prix,
               c.nom_compagnie, c.code_compagnie,
               a.modele as avion_modele
        FROM reservations r 
        JOIN utilisateurs u ON r.id_utilisateur = u.id 
        JOIN vols v ON r.id_vol = v.id_vol 
        JOIN compagnies c ON v.id_compagnie = c.id_compagnie 
        JOIN avions a ON v.id_avion = a.id_avion 
        WHERE r.id = ?
    ");
    $stmt->execute([$reservation_id]);
    $reservation = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$reservation) {
        header('HTTP/1.1 404 Not Found');
        echo json_encode(['success' => false, 'message' => 'Réservation non trouvée']);
        exit;
    }
    
    // Récupérer les sièges réservés
    $stmt = $pdo->prepare("
        SELECT s.rangee, s.colonne, s.type, s.prix_supplement
        FROM reservation_sieges rs 
        JOIN sieges_avion s ON rs.siege_id = s.id_siege 
        WHERE rs.reservation_id = ?
    ");
    $stmt->execute([$reservation_id]);
    $sieges = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Générer le HTML des détails
    $html = '
    <div class="reservation-details">
        <div class="detail-section">
            <h4>Informations de la Réservation</h4>
            <div class="detail-grid">
                <div class="detail-item">
                    <strong>ID Réservation:</strong> #' . $reservation['id'] . '
                </div>
                <div class="detail-item">
                    <strong>Statut:</strong> <span class="status-badge status-' . $reservation['statut'] . '">' . ucfirst($reservation['statut']) . '</span>
                </div>
                <div class="detail-item">
                    <strong>Date Réservation:</strong> ' . date('d/m/Y H:i', strtotime($reservation['date_reservation'])) . '
                </div>
                <div class="detail-item">
                    <strong>Prix Total:</strong> ' . number_format($reservation['prix_total'], 2, ',', ' ') . '€
                </div>
            </div>
        </div>
        
        <div class="detail-section">
            <h4>Informations du Vol</h4>
            <div class="detail-grid">
                <div class="detail-item">
                    <strong>Vol:</strong> ' . htmlspecialchars($reservation['code_compagnie'] . $reservation['numero_vol']) . '
                </div>
                <div class="detail-item">
                    <strong>Compagnie:</strong> ' . htmlspecialchars($reservation['nom_compagnie']) . '
                </div>
                <div class="detail-item">
                    <strong>Itinéraire:</strong> ' . htmlspecialchars($reservation['depart']) . ' → ' . htmlspecialchars($reservation['arrivee']) . '
                </div>
                <div class="detail-item">
                    <strong>Départ:</strong> ' . date('d/m/Y H:i', strtotime($reservation['date_depart'])) . '
                </div>
                <div class="detail-item">
                    <strong>Arrivée:</strong> ' . date('d/m/Y H:i', strtotime($reservation['date_arrivee'])) . '
                </div>
                <div class="detail-item">
                    <strong>Avion:</strong> ' . htmlspecialchars($reservation['avion_modele']) . '
                </div>
            </div>
        </div>
        
        <div class="detail-section">
            <h4>Informations du Client</h4>
            <div class="detail-grid">
                <div class="detail-item">
                    <strong>Nom:</strong> ' . htmlspecialchars($reservation['user_prenom'] . ' ' . $reservation['user_nom']) . '
                </div>
                <div class="detail-item">
                    <strong>Email:</strong> ' . htmlspecialchars($reservation['user_email']) . '
                </div>
                <div class="detail-item">
                    <strong>Téléphone:</strong> ' . ($reservation['user_telephone'] ? htmlspecialchars($reservation['user_telephone']) : 'Non renseigné') . '
                </div>
                <div class="detail-item">
                    <strong>Passagers:</strong> ' . $reservation['nombre_passagers'] . '
                </div>
                <div class="detail-item">
                    <strong>Classe:</strong> ' . ucfirst($reservation['classe']) . '
                </div>
            </div>
        </div>';
    
    if (!empty($sieges)) {
        $html .= '
        <div class="detail-section">
            <h4>Sièges Réservés</h4>
            <div class="sieges-list">';
        
        foreach ($sieges as $siege) {
            $html .= '
                <div class="siege-item">
                    <span class="siege-number">' . $siege['colonne'] . $siege['rangee'] . '</span>
                    <span class="siege-type">' . ucfirst($siege['type']) . '</span>
                    ' . ($siege['prix_supplement'] > 0 ? '<span class="siege-supplement">+' . number_format($siege['prix_supplement'], 2, ',', ' ') . '€</span>' : '') . '
                </div>';
        }
        
        $html .= '
            </div>
        </div>';
    }
    
    $html .= '
    </div>
    <style>
    .detail-section {
        margin-bottom: 25px;
        padding-bottom: 20px;
        border-bottom: 1px solid #e2e8f0;
    }
    .detail-section:last-child {
        border-bottom: none;
    }
    .detail-section h4 {
        color: #1e293b;
        margin-bottom: 15px;
        font-size: 1.1rem;
    }
    .detail-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
    }
    .detail-item {
        padding: 8px 0;
    }
    .sieges-list {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }
    .siege-item {
        background: #f8fafc;
        padding: 10px 15px;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .siege-number {
        font-weight: 600;
        color: #1e293b;
    }
    .siege-type {
        color: #64748b;
        font-size: 0.9rem;
    }
    .siege-supplement {
        color: #10b981;
        font-weight: 600;
        font-size: 0.9rem;
    }
    </style>';
    
    echo json_encode(['success' => true, 'html' => $html]);
    
} catch (PDOException $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['success' => false, 'message' => 'Erreur serveur: ' . $e->getMessage()]);
}
?>