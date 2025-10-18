<?php
session_start();
require_once 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Vérifier si l'utilisateur est connecté
        if (!isset($_SESSION['user_id'])) {
            throw new Exception("Vous devez être connecté pour effectuer une réservation");
        }

        // Récupérer les données
        $vol_id = $_POST['vol_id'] ?? null;
        $passagers = $_POST['passagers'] ?? 1;
        $classe = $_POST['classe'] ?? 'économique';
        $sieges = $_POST['sieges'] ?? [];

        // Validation
        if (!$vol_id) {
            throw new Exception("Vol non spécifié");
        }

        if (empty($sieges)) {
            throw new Exception("Aucun siège sélectionné");
        }

        if (count($sieges) != $passagers) {
            throw new Exception("Nombre de sièges sélectionnés ne correspond pas au nombre de passagers");
        }

        // Récupérer les infos du vol pour le prix
        $stmt = $pdo->prepare("SELECT v.*, c.nom_compagnie FROM vols v 
                              JOIN compagnies c ON v.id_compagnie = c.id_compagnie 
                              WHERE v.id_vol = ?");
        $stmt->execute([$vol_id]);
        $vol = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$vol) {
            throw new Exception("Vol non trouvé");
        }

        // Calculer le prix total (prix de base + suppléments sièges)
        $prix_total = 0;
        $supplements = 0;

        // Récupérer les suppléments pour chaque siège
        $stmt_siege = $pdo->prepare("SELECT supplement_prix FROM sieges_avion WHERE id_siege = ?");
        foreach ($sieges as $siege_id) {
            $stmt_siege->execute([$siege_id]);
            $siege = $stmt_siege->fetch(PDO::FETCH_ASSOC);
            $supplements += $siege['supplement_prix'] ?? 0;
        }

        $prix_total = ($vol['prix'] * $passagers) + $supplements;

        // Commencer une transaction
        $pdo->beginTransaction();

        try {
            // 1. Créer la réservation
            $stmt_reservation = $pdo->prepare("
                INSERT INTO reservations (id_user, id_vol, statut, nombre_passagers, prix_total) 
                VALUES (?, ?, 'en attente', ?, ?)
            ");
            $stmt_reservation->execute([
                $_SESSION['user_id'],
                $vol_id,
                $passagers,
                $prix_total
            ]);
            $reservation_id = $pdo->lastInsertId();

            // 2. Associer les sièges à la réservation
            $stmt_siege_reservation = $pdo->prepare("
                INSERT INTO reservation_sieges (id_reservation, id_siege, prix_paye) 
                VALUES (?, ?, ?)
            ");

            $prix_par_siege = $prix_total / count($sieges);
            
            foreach ($sieges as $siege_id) {
                $stmt_siege_reservation->execute([
                    $reservation_id,
                    $siege_id,
                    $prix_par_siege
                ]);
            }

            // 3. Valider la transaction
            $pdo->commit();

            // Rediriger vers la page de paiement
            header('Location: payment.php?reservation_id=' . $reservation_id);
            exit;

        } catch (Exception $e) {
            // Annuler la transaction en cas d'erreur
            $pdo->rollBack();
            throw $e;
        }

    } catch (Exception $e) {
        // Rediriger vers une page d'erreur
        header('Location: error.php?message=' . urlencode($e->getMessage()));
        exit;
    }
} else {
    // Si pas en POST, rediriger vers l'accueil
    header('Location: index.php');
    exit;
}
?>