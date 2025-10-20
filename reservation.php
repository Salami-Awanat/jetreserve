<?php
<<<<<<< Updated upstream
// Activer l'affichage des erreurs pour le débogage
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Augmenter le temps d'exécution maximum
set_time_limit(60);
ini_set('max_execution_time', 60);

// DÉBUT DU BUFFER
ob_start();

=======
ob_start();
>>>>>>> Stashed changes
require_once 'includes/db.php';
session_start();

// Debug optionnel via ?debug=1
if (isset($_GET['debug'])) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
}

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['id_user'])) {
    header('Location: vge64/connexion.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

// Récupérer l'ID du vol depuis l'URL
$id_vol = isset($_GET['id_vol']) ? intval($_GET['id_vol']) : 0;

if ($id_vol <= 0) {
    header('Location: index.php');
    exit;
}

// Récupérer les détails du vol
try {
    $stmt = $pdo->prepare("
        SELECT v.*, c.nom_compagnie, c.code_compagnie, a.modele as avion_modele,
               a.capacite_total, a.configuration_sieges
        FROM vols v 
        JOIN compagnies c ON v.id_compagnie = c.id_compagnie 
        JOIN avions a ON v.id_avion = a.id_avion
        WHERE v.id_vol = ?
    ");
    $stmt->execute([$id_vol]);
    $vol = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$vol) {
        header('Location: index.php');
        exit;
    }
} catch (PDOException $e) {
    die("Erreur lors de la récupération du vol: " . $e->getMessage());
}

// Récupérer les sièges disponibles ET occupés pour ce vol
try {
    // Récupérer tous les sièges de l'avion
    $stmt = $pdo->prepare("
        SELECT sa.* 
        FROM sieges_avion sa
        WHERE sa.id_avion = ? 
        AND sa.statut = 'actif'
        ORDER BY sa.rang, sa.position
    ");
    $stmt->execute([$vol['id_avion']]);
    $tous_sieges = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer les sièges occupés
    $stmt_occupes = $pdo->prepare("
        SELECT rs.id_siege
        FROM reservation_sieges rs
        JOIN reservations r ON rs.id_reservation = r.id_reservation
        WHERE r.id_vol = ? AND r.statut IN ('confirmé', 'en attente')
    ");
    $stmt_occupes->execute([$id_vol]);
    $sieges_occupes = $stmt_occupes->fetchAll(PDO::FETCH_COLUMN);

    // Filtrer les sièges disponibles
    $sieges_disponibles = array_filter($tous_sieges, function($siege) use ($sieges_occupes) {
        return !in_array($siege['id_siege'], $sieges_occupes);
    });

} catch (PDOException $e) {
    error_log("Erreur sièges: " . $e->getMessage());
    $sieges_disponibles = [];
    $sieges_occupes = [];
}

// Récupérer les options de bagage
try {
    $stmt_bagages = $pdo->prepare("SELECT * FROM options_bagage WHERE statut = 'actif' ORDER BY prix_supplement");
    $stmt_bagages->execute();
    $options_bagage = $stmt_bagages->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $options_bagage = [
        ['id_option' => 1, 'nom_option' => 'Bagage à main', 'description' => 'Bagage cabine standard (8kg)', 'poids_max' => 8, 'prix_supplement' => 0],
        ['id_option' => 2, 'nom_option' => 'Bagage en soute 20kg', 'description' => 'Bagage en soute jusqu\'à 20kg', 'poids_max' => 20, 'prix_supplement' => 25],
        ['id_option' => 3, 'nom_option' => 'Bagage en soute 30kg', 'description' => 'Bagage en soute jusqu\'à 30kg', 'poids_max' => 30, 'prix_supplement' => 45],
    ];
}

// Traitement du formulaire de réservation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reserver'])) {
    
    $nombre_passagers = intval($_POST['nombre_passagers']);
    $sieges_selectionnes = isset($_POST['sieges']) ? array_map('intval', $_POST['sieges']) : [];
    $bagages_selectionnes = isset($_POST['bagages']) ? $_POST['bagages'] : [];
    
    // Validation
    $erreurs = [];
    
    if ($nombre_passagers <= 0 || $nombre_passagers > 10) {
        $erreurs[] = "Nombre de passagers invalide";
    }
    
    if (count($sieges_selectionnes) !== $nombre_passagers) {
        $erreurs[] = "Vous devez sélectionner exactement " . $nombre_passagers . " siège(s)";
    }
    
    // Vérification rapide de la disponibilité des sièges
    foreach ($sieges_selectionnes as $siege_id) {
        if (in_array($siege_id, $sieges_occupes)) {
            $erreurs[] = "Un des sièges sélectionnés n'est plus disponible";
            break;
        }
    }
    
    if (empty($erreurs)) {
        try {
            // DÉBUT DE LA TRANSACTION
            $pdo->beginTransaction();
            
            error_log("=== DÉBUT RÉSERVATION ===");
            
            // CALCUL RAPIDE DU PRIX - OPTIMISÉ
            $prix_total = $vol['prix'] * $nombre_passagers;
            
            // Suppléments sièges - OPTIMISÉ
            foreach ($sieges_selectionnes as $id_siege) {
                foreach ($tous_sieges as $siege) {
                    if ($siege['id_siege'] == $id_siege) {
                        $prix_total += $siege['supplement_prix'];
                        break;
                    }
                }
            }
            
            // Suppléments bagages - OPTIMISÉ
            foreach ($bagages_selectionnes as $id_option => $quantite) {
                $quantite = intval($quantite);
                if ($quantite > 0) {
                    foreach ($options_bagage as $option) {
                        if ($option['id_option'] == $id_option) {
                            $prix_total += $option['prix_supplement'] * $quantite;
                            break;
                        }
                    }
                }
            }
            
            // Frais fixes
            $prix_total += 9.00 + 25.00;
            
            error_log("Prix total calculé: $prix_total");
            
            // CRÉATION DE LA RÉSERVATION
            $stmt = $pdo->prepare("
                INSERT INTO reservations (id_user, id_vol, statut, nombre_passagers, prix_total, date_reservation)
                VALUES (?, ?, 'confirmé', ?, ?, NOW())
            ");
            $stmt->execute([$_SESSION['id_user'], $id_vol, $nombre_passagers, $prix_total]);
            $id_reservation = $pdo->lastInsertId();
            
            if (!$id_reservation) {
                throw new Exception("Échec de la création de la réservation");
            }
            
            error_log("Réservation créée avec ID: $id_reservation");
            
            // ASSOCIATION DES SIÈGES - OPTIMISÉ
            $stmt_siege = $pdo->prepare("
                INSERT INTO reservation_sieges (id_reservation, id_siege, prix_paye)
                VALUES (?, ?, ?)
            ");
            
            foreach ($sieges_selectionnes as $id_siege) {
                $prix_siege = $vol['prix'];
                foreach ($tous_sieges as $siege) {
                    if ($siege['id_siege'] == $id_siege) {
                        $prix_siege += $siege['supplement_prix'];
                        break;
                    }
                }
                $stmt_siege->execute([$id_reservation, $id_siege, $prix_siege]);
            }
            
            error_log("Sièges associés: " . count($sieges_selectionnes));
            
            // ASSOCIATION DES BAGAGES - OPTIMISÉ
            if (!empty($bagages_selectionnes)) {
                $stmt_bagage = $pdo->prepare("
                    INSERT INTO reservation_bagages (id_reservation, id_option, quantite, prix_applique)
                    VALUES (?, ?, ?, ?)
                ");
                
                foreach ($bagages_selectionnes as $id_option => $quantite) {
                    $quantite = intval($quantite);
                    if ($quantite > 0) {
                        $prix_option = 0;
                        foreach ($options_bagage as $option) {
                            if ($option['id_option'] == $id_option) {
                                $prix_option = $option['prix_supplement'];
                                break;
                            }
                        }
                        $stmt_bagage->execute([$id_reservation, $id_option, $quantite, $prix_option]);
                    }
                }
                error_log("Bagages associés: " . count(array_filter($bagages_selectionnes)));
            }
            
            // ENREGISTREMENT DU PAIEMENT
            $stmt_paiement = $pdo->prepare("
                INSERT INTO paiements (id_reservation, montant, mode_paiement, statut, date_paiement)
                VALUES (?, ?, 'carte', 'réussi', NOW())
            ");
            $stmt_paiement->execute([$id_reservation, $prix_total]);
            error_log("Paiement enregistré");
            
            // MISE À JOUR DES PLACES DISPONIBLES
            $stmt = $pdo->prepare("
                UPDATE vols 
                SET places_disponibles = GREATEST(0, places_disponibles - ?)
                WHERE id_vol = ?
            ");
            $stmt->execute([$nombre_passagers, $id_vol]);
            error_log("Places mises à jour");
            
            // EMAIL (optionnel - ne pas bloquer en cas d'erreur)
            try {
                $stmt_email = $pdo->prepare("
                    INSERT INTO emails (id_user, sujet, contenu, type, statut, date_envoi)
                    VALUES (?, ?, ?, 'confirmation', 'en attente', NOW())
                ");
                $sujet_email = "Confirmation de votre réservation JetReserve";
                $contenu_email = "Votre réservation n°" . $id_reservation . " pour le vol " . $vol['depart'] . " - " . $vol['arrivee'] . " a été confirmée. Montant total : " . number_format($prix_total, 2, ',', ' ') . "€";
                $stmt_email->execute([$_SESSION['id_user'], $sujet_email, $contenu_email]);
                error_log("Email programmé");
            } catch (Exception $e) {
                error_log("Erreur email non critique: " . $e->getMessage());
            }
            
            // VALIDATION DE LA TRANSACTION
            $pdo->commit();
            
<<<<<<< Updated upstream
            error_log("=== RÉSERVATION RÉUSSIE ===");
            
            // VIDER LE BUFFER ET REDIRIGER
            ob_end_clean();
            
            // Rediriger vers la page de confirmation
            header('Location: confirmation.php?id_reservation=' . $id_reservation);
            exit;
            
        } catch (Exception $e) {
            // ANNULATION DE LA TRANSACTION EN CAS D'ERREUR
=======
// 1. Nettoyer la mémoire tampon pour s'assurer qu'aucun contenu HTML ou
//    erreur n'est en attente avant l'envoi du script.
ob_clean(); 

// 2. Envoyer un script JavaScript pour désactiver l'événement de blocage
//    avant de tenter la redirection.
echo '<script type="text/javascript">';
echo '  // Désactive l\'événement onbeforeunload (responsable du "Voulez-vous quitter ?")';
echo '  if (window.onbeforeunload !== null) {';
echo '    window.onbeforeunload = null;'; 
echo '  }';

// 3. Effectuer la redirection via JavaScript (plus fiable et immédiate ici)
echo '  window.location.href = "/confirmation_reservation.php?id_reservation=' . $id_reservation . '";';
echo '</script>';

// 4. Arrêter l'exécution du script PHP
exit;


        } catch (PDOException $e) {
>>>>>>> Stashed changes
            $pdo->rollBack();
            $erreur_message = "Erreur lors de la réservation: " . $e->getMessage();
            $erreurs[] = $erreur_message;
            error_log("=== ERREUR RÉSERVATION: " . $e->getMessage() . " ===");
        }
    }
    
    if (!empty($erreurs)) {
        $erreur = implode("<br>", $erreurs);
    }
}

// Si on arrive ici, c'est qu'on affiche le formulaire ou qu'il y a une erreur
ob_end_flush();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réservation - JetReserve</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #005baa;
            --secondary: #ff6600;
            --success: #28a745;
            --danger: #dc3545;
            --warning: #ffc107;
            --info: #17a2b8;
            --light: #f8f9fa;
            --dark: #343a40;
            --white: #ffffff;
            --gray: #6c757d;
            --light-gray: #e9ecef;
            --border-color: #dee2e6;
            --premium: #d4af37;
            --business: #6f42c1;
            --economy: #20c997;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            color: var(--dark);
            line-height: 1.6;
            min-height: 100vh;
        }
        
        .container {
            width: 100%;
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        header {
            background: var(--white);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
            backdrop-filter: blur(10px);
        }
        
        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
        }
        
        .logo {
            font-size: 32px;
            font-weight: 700;
            color: var(--primary);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .logo i {
            color: var(--secondary);
        }
        
        .logo span {
            color: var(--secondary);
        }
        
        .auth-buttons {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        
        .user-welcome {
            color: var(--dark);
            font-weight: 500;
            background: var(--light-gray);
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.9rem;
        }
        
        .btn {
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            font-family: 'Poppins', sans-serif;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
        }
        
        .btn-outline {
            background-color: transparent;
            border: 2px solid var(--primary);
            color: var(--primary);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary), #0077cc);
            color: var(--white);
            box-shadow: 0 4px 15px rgba(0, 91, 170, 0.3);
        }
        
        .btn-success {
            background: linear-gradient(135deg, var(--success), #34ce57);
            color: var(--white);
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }
        
        .btn-lg {
            padding: 15px 30px;
            font-size: 1.1rem;
            border-radius: 30px;
        }
        
        .btn-full {
            width: 100%;
            justify-content: center;
        }
        
        .reservation-layout {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 30px;
            margin: 30px auto;
        }
        
        .reservation-main {
            background: var(--white);
            border-radius: 20px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
            overflow: hidden;
        }
        
        .reservation-sidebar {
            background: var(--white);
            border-radius: 20px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
            padding: 30px;
            height: fit-content;
            position: sticky;
            top: 100px;
        }
        
        .reservation-header {
            background: linear-gradient(135deg, var(--primary), #0077cc);
            color: var(--white);
            padding: 30px;
            position: relative;
            overflow: hidden;
        }
        
        .reservation-header::before {
            content: "";
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
            transform: rotate(45deg);
        }
        
        .reservation-title {
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 10px;
            position: relative;
        }
        
        .progress-steps {
            display: flex;
            justify-content: space-between;
            margin: 30px 0;
            position: relative;
        }
        
        .progress-steps::before {
            content: "";
            position: absolute;
            top: 15px;
            left: 0;
            right: 0;
            height: 3px;
            background: rgba(255,255,255,0.3);
            z-index: 1;
        }
        
        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            z-index: 2;
        }
        
        .step-number {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            border: 2px solid rgba(255,255,255,0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-bottom: 8px;
            transition: all 0.3s ease;
        }
        
        .step.active .step-number {
            background: var(--white);
            color: var(--primary);
            border-color: var(--white);
            transform: scale(1.1);
        }
        
        .step.completed .step-number {
            background: var(--success);
            color: var(--white);
            border-color: var(--success);
        }
        
        .step-label {
            font-size: 0.85rem;
            color: rgba(255,255,255,0.8);
            font-weight: 500;
        }
        
        .step.active .step-label {
            color: var(--white);
            font-weight: 600;
        }
        
        .vol-resume {
            padding: 30px;
            border-bottom: 1px solid var(--border-color);
            background: var(--white);
        }
        
        .vol-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .info-card {
            background: linear-gradient(135deg, var(--light), var(--light-gray));
            padding: 20px;
            border-radius: 15px;
            border-left: 4px solid var(--primary);
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }
        
        .info-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
        }
        
        .info-label {
            font-weight: 600;
            color: var(--gray);
            font-size: 0.9rem;
            margin-bottom: 5px;
        }
        
        .info-value {
            font-size: 1.2rem;
            color: var(--dark);
            font-weight: 600;
        }
        
        .reservation-form {
            padding: 30px;
        }
        
        .form-section {
            margin-bottom: 30px;
            padding: 25px;
            border: 2px solid var(--light-gray);
            border-radius: 15px;
            background: var(--white);
            transition: all 0.3s ease;
        }
        
        .form-section:hover {
            border-color: var(--primary);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
        }
        
        .section-title {
            color: var(--primary);
            margin-bottom: 20px;
            font-weight: 600;
            font-size: 1.4rem;
            display: flex;
            align-items: center;
            gap: 10px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--light-gray);
        }
        
        .section-title i {
            color: var(--secondary);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark);
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid var(--border-color);
            border-radius: 10px;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s ease;
            background: var(--light);
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(0, 91, 170, 0.1);
            background: var(--white);
        }
        
        .sieges-container {
            margin-top: 20px;
        }
        
        .avion-schema {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 20px;
            position: relative;
            border: 2px solid var(--light-gray);
        }
        
        .cockpit {
            background: linear-gradient(135deg, var(--primary), #0077cc);
            color: white;
            padding: 15px;
            text-align: center;
            border-radius: 15px 15px 0 0;
            margin-bottom: 30px;
            font-weight: bold;
            font-size: 1.1rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .rangee-sieges {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 15px;
            gap: 15px;
        }
        
        .numero-rangee {
            width: 35px;
            text-align: center;
            font-weight: 600;
            color: var(--gray);
            background: var(--white);
            padding: 5px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .siege {
            width: 45px;
            height: 45px;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
            position: relative;
            background: var(--white);
        }
        
        .siege.disponible {
            border-color: var(--success);
            background: var(--white);
        }
        
        .siege.disponible:hover {
            background: var(--success);
            color: var(--white);
            transform: scale(1.1);
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.4);
        }
        
        .siege.selectionne {
            background: var(--primary);
            color: var(--white);
            border-color: var(--primary);
            transform: scale(1.1);
            box-shadow: 0 4px 15px rgba(0, 91, 170, 0.4);
        }
        
        .siege.occupe {
            background: var(--danger);
            color: var(--white);
            border-color: var(--danger);
            cursor: not-allowed;
            opacity: 0.7;
        }
        
        .siege.premium {
            border-color: var(--premium);
            background: linear-gradient(135deg, #fff9e6, #ffefb3);
        }
        
        .siege.business {
            border-color: var(--business);
            background: linear-gradient(135deg, #f0e6ff, #d9c8ff);
        }
        
        .siege.economy {
            border-color: var(--economy);
        }
        
        .siege::after {
            content: attr(data-supplement);
            position: absolute;
            top: -20px;
            font-size: 0.7rem;
            color: var(--secondary);
            font-weight: 600;
            background: var(--white);
            padding: 2px 5px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .couloir {
            width: 50px;
            text-align: center;
            color: var(--gray);
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .legende-sieges {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-top: 25px;
        }
        
        .legende-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            background: var(--light);
            border-radius: 10px;
        }
        
        .legende-couleur {
            width: 25px;
            height: 25px;
            border-radius: 6px;
            border: 2px solid var(--border-color);
        }
        
        .bagages-container {
            display: grid;
            gap: 20px;
            margin-top: 20px;
        }
        
        .bagage-option {
            border: 2px solid var(--border-color);
            border-radius: 15px;
            padding: 20px;
            background: var(--white);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .bagage-option::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
            background: var(--primary);
        }
        
        .bagage-option:hover {
            border-color: var(--primary);
            box-shadow: 0 6px 20px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        
        .bagage-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .bagage-header h4 {
            color: var(--primary);
            margin: 0;
            font-size: 1.2rem;
        }
        
        .bagage-price {
            background: linear-gradient(135deg, var(--success), #34ce57);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 1rem;
            box-shadow: 0 2px 10px rgba(40, 167, 69, 0.3);
        }
        
        .bagage-description {
            color: var(--gray);
            font-size: 0.95rem;
            margin-bottom: 15px;
        }
        
        .bagage-controls {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .btn-qty {
            width: 40px;
            height: 40px;
            border: 2px solid var(--border-color);
            background: var(--light);
            border-radius: 10px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }
        
        .btn-qty:hover {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
            transform: scale(1.1);
        }
        
        .bagage-qty {
            width: 70px;
            text-align: center;
            border: 2px solid var(--border-color);
            border-radius: 10px;
            padding: 10px;
            font-weight: 600;
            background: var(--white);
        }
        
        .bagage-total {
            margin-left: auto;
            font-weight: bold;
            color: var(--primary);
            min-width: 80px;
            text-align: right;
            font-size: 1.1rem;
        }
        
        .selected-items {
            background: linear-gradient(135deg, var(--light), var(--light-gray));
            padding: 20px;
            border-radius: 15px;
            margin-top: 20px;
            border: 2px solid var(--border-color);
        }
        
        .selected-title {
            color: var(--primary);
            font-weight: 600;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .item-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--white);
            color: var(--dark);
            padding: 8px 15px;
            border-radius: 20px;
            margin: 5px;
            font-size: 0.9rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border: 1px solid var(--border-color);
        }
        
        .resume-prix {
            background: linear-gradient(135deg, var(--light), var(--light-gray));
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 20px;
            border: 2px solid var(--border-color);
        }
        
        .ligne-prix {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .ligne-prix:last-child {
            border-bottom: none;
        }
        
        .prix-total {
            border-top: 3px solid var(--primary);
            padding-top: 15px;
            margin-top: 15px;
            font-weight: 700;
            font-size: 1.4rem;
            color: var(--primary);
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            border: 2px solid transparent;
        }
        
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
        }
        
        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border-color: #a7f3d0;
        }
        
        .alert-info {
            background: #dbeafe;
            color: #1e40af;
            border-color: #93c5fd;
        }
        
        .timer {
            background: linear-gradient(135deg, var(--warning), #ffd351);
            color: white;
            padding: 12px 20px;
            border-radius: 12px;
            font-weight: bold;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(255, 193, 7, 0.3);
        }
        
        .sidebar-section {
            margin-bottom: 25px;
            padding-bottom: 25px;
            border-bottom: 2px solid var(--light-gray);
        }
        
        .sidebar-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .sidebar-title {
            color: var(--primary);
            font-weight: 600;
            margin-bottom: 15px;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .flight-summary {
            background: linear-gradient(135deg, var(--primary), #0077cc);
            color: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 20px;
        }
        
        .flight-route {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .flight-airport {
            text-align: center;
        }
        
        .airport-code {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .airport-name {
            font-size: 0.9rem;
            opacity: 0.9;
        }
        
        .flight-duration {
            text-align: center;
            flex: 1;
            padding: 0 20px;
        }
        
        .duration-line {
            height: 2px;
            background: rgba(255,255,255,0.5);
            position: relative;
            margin: 10px 0;
        }
        
        .duration-line::before {
            content: "✈";
            position: absolute;
            top: -10px;
            left: 50%;
            transform: translateX(-50%);
            background: var(--primary);
            padding: 5px;
            border-radius: 50%;
        }
        
        @media (max-width: 1024px) {
            .reservation-layout {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .reservation-sidebar {
                position: static;
                order: -1;
            }
        }
        
        @media (max-width: 768px) {
            .vol-info-grid {
                grid-template-columns: 1fr;
            }
            
            .rangee-sieges {
                flex-wrap: wrap;
            }
            
            .legende-sieges {
                grid-template-columns: 1fr;
            }
            
            .progress-steps {
                flex-wrap: wrap;
                gap: 15px;
            }
            
            .auth-buttons {
                flex-direction: column;
                gap: 10px;
            }
            
            .bagage-controls {
                flex-wrap: wrap;
            }
            
            .flight-route {
                flex-direction: column;
                gap: 15px;
            }
            
            .flight-duration {
                padding: 0;
            }
        }
        
        .loading {
            display: none;
            text-align: center;
            padding: 30px;
        }
        
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid var(--primary);
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .class-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-left: 10px;
        }
        
        .badge-premium {
            background: var(--premium);
            color: white;
        }
        
        .badge-business {
            background: var(--business);
            color: white;
        }
        
        .badge-economy {
            background: var(--economy);
            color: white;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="header-top">
                <a href="index.php" class="logo">
                    <i class="fas fa-plane"></i>
                    Jet<span>Reserve</span>
                </a>
                <div class="auth-buttons">
                    <span class="user-welcome">
                        <i class="fas fa-user-circle"></i> 
                        Bonjour, <?php echo htmlspecialchars($_SESSION['prenom'] ?? $_SESSION['nom'] ?? 'Utilisateur'); ?>
                    </span>
                    <a href="vge64/index2.php" class="btn btn-outline">
                        <i class="fas fa-user"></i> Mon compte
                    </a>
                    <a href="vge64/deconnexion.php" class="btn btn-primary">
                        <i class="fas fa-sign-out-alt"></i> Déconnexion
                    </a>
                </div>
            </div>
        </div>
    </header>

    <div class="container">
<<<<<<< Updated upstream
        <div class="reservation-layout">
            <!-- Colonne principale -->
            <div class="reservation-main">
                <!-- En-tête -->
                <div class="reservation-header">
                    <h1 class="reservation-title">
                        <i class="fas fa-plane-departure"></i> Finalisez votre réservation
                    </h1>
                    <p>Votre vol <?php echo htmlspecialchars($vol['depart']); ?> → <?php echo htmlspecialchars($vol['arrivee']); ?> du <?php echo date('d/m/Y', strtotime($vol['date_depart'])); ?></p>
=======
        <!-- Barre de progression -->
        <div class="progress-steps">
            <div class="step completed">
                <div class="step-number"><i class="fas fa-check"></i></div>
                <div class="step-label">Recherche</div>
            </div>
            <div class="step completed">
                <div class="step-number"><i class="fas fa-check"></i></div>
                <div class="step-label">Sélection</div>
            </div>
            <div class="step active">
                <div class="step-number">3</div>
                <div class="step-label">Réservation</div>
            </div>
            <div class="step">
                <div class="step-number">4</div>
                <div class="step-label">Confirmation</div>
            </div>
        </div>

        <div class="reservation-container">
            <!-- En-tête -->
            <div class="reservation-header">
                <h1 class="reservation-title"><i class="fas fa-plane"></i> Réservation de vol</h1>
                <p>Finalisez votre réservation en choisissant vos sièges et options</p>
                
                <!-- Timer de réservation -->
                <div class="timer">
                    <i class="fas fa-clock"></i>
                    <span id="reservation-timer">15:00</span>
                </div>
            </div>
            
            <!-- Résumé du vol -->
            <div class="vol-resume">
                <h2 class="section-title"><i class="fas fa-info-circle"></i> Résumé du vol</h2>
                <div class="vol-info-grid">
                    <div class="info-card">
                        <div class="info-label"><i class="fas fa-route"></i> Itinéraire</div>
                        <div class="info-value"><?php echo htmlspecialchars($vol['depart']); ?> → <?php echo htmlspecialchars($vol['arrivee']); ?></div>
                    </div>
                    <div class="info-card">
                        <div class="info-label"><i class="fas fa-calendar-alt"></i> Date et heure</div>
                        <div class="info-value"><?php echo date('d/m/Y H:i', strtotime($vol['date_depart'])); ?></div>
                    </div>
                    <div class="info-card">
                        <div class="info-label"><i class="fas fa-building"></i> Compagnie</div>
                        <div class="info-value"><?php echo htmlspecialchars($vol['nom_compagnie']); ?></div>
                    </div>
                    <div class="info-card">
                        <div class="info-label"><i class="fas fa-star"></i> Classe</div>
                        <div class="info-value"><?php echo htmlspecialchars($vol['classe']); ?></div>
                    </div>
                </div>
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <strong>Important :</strong> Vous avez 15 minutes pour compléter votre réservation. Passé ce délai, les sièges sélectionnés seront libérés.
                </div>
            </div>
            
            <!-- Formulaire de réservation -->
            <form method="POST" class="reservation-form" id="reservationForm">
                <?php if (isset($erreur)): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i> 
                        <div><?php echo $erreur; ?></div>
                    </div>
                <?php endif; ?>
                <?php if (isset($_GET['debug'])): ?>
                    <div class="alert alert-info" style="max-height:300px; overflow:auto;">
                        <strong>Debug:</strong>
                        <div style="font-family:monospace; font-size:12px; white-space:pre-wrap;">
POST = <?php echo htmlspecialchars(print_r($_POST, true)); ?>

ERREURS = <?php echo htmlspecialchars(print_r(isset($erreurs) ? $erreurs : [], true)); ?>

ID_RESERVATION = <?php echo isset($id_reservation) ? (int)$id_reservation : 'null'; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Sélection du nombre de passagers -->
                <div class="form-section">
                    <h3 class="section-title"><i class="fas fa-users"></i> Nombre de passagers</h3>
                    <div class="form-group">
                        <label for="nombre_passagers">Nombre de passagers *</label>
                        <select name="nombre_passagers" id="nombre_passagers" class="form-control" required>
                            <?php for ($i = 1; $i <= min(10, $vol['places_disponibles']); $i++): ?>
                                <option value="<?php echo $i; ?>" <?php echo isset($_POST['nombre_passagers']) && $_POST['nombre_passagers'] == $i ? 'selected' : ''; ?>>
                                    <?php echo $i; ?> passager(s) - <?php echo number_format($vol['prix'] * $i, 2, ',', ' '); ?>€
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
                
                <!-- Sélection des sièges -->
                <div class="form-section">
                    <h3 class="section-title"><i class="fas fa-chair"></i> Choix des sièges</h3>
>>>>>>> Stashed changes
                    
                    <!-- Barre de progression -->
                    <div class="progress-steps">
                        <div class="step completed">
                            <div class="step-number"><i class="fas fa-check"></i></div>
                            <div class="step-label">Recherche</div>
                        </div>
                        <div class="step completed">
                            <div class="step-number"><i class="fas fa-check"></i></div>
                            <div class="step-label">Sélection</div>
                        </div>
                        <div class="step active">
                            <div class="step-number">3</div>
                            <div class="step-label">Réservation</div>
                        </div>
                        <div class="step">
                            <div class="step-number">4</div>
                            <div class="step-label">Confirmation</div>
                        </div>
                    </div>
                    
                    <!-- Timer de réservation -->
                    <div class="timer">
                        <i class="fas fa-clock"></i>
                        <span>Temps restant : </span>
                        <span id="reservation-timer">15:00</span>
                    </div>
                </div>
                
                <!-- Formulaire de réservation -->
                <form method="POST" class="reservation-form" id="reservationForm">
                    <?php if (isset($erreur)): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i> 
                            <div><strong>Erreur :</strong> <?php echo $erreur; ?></div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Sélection du nombre de passagers -->
                    <div class="form-section">
                        <h3 class="section-title">
                            <i class="fas fa-users"></i> Nombre de passagers
                        </h3>
                        <div class="form-group">
                            <label for="nombre_passagers">Combien de passagers voyagent ? *</label>
                            <select name="nombre_passagers" id="nombre_passagers" class="form-control" required>
                                <?php for ($i = 1; $i <= min(10, $vol['places_disponibles']); $i++): ?>
                                    <option value="<?php echo $i; ?>" <?php echo isset($_POST['nombre_passagers']) && $_POST['nombre_passagers'] == $i ? 'selected' : ''; ?>>
                                        <?php echo $i; ?> passager(s) - <?php echo number_format($vol['prix'] * $i, 2, ',', ' '); ?>€
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Sélection des sièges -->
                    <div class="form-section">
                        <h3 class="section-title">
                            <i class="fas fa-chair"></i> Choix des sièges
                        </h3>
                        
                        <!-- Affichage des sièges sélectionnés -->
                        <div class="selected-items" id="selected-seats-display" style="display: none;">
                            <h4 class="selected-title">
                                <i class="fas fa-check-circle"></i> Sièges sélectionnés
                            </h4>
                            <div id="selected-seats-list"></div>
                        </div>
                        
                        <div class="sieges-container">
                            <div class="avion-schema">
                                <div class="cockpit">
                                    <i class="fas fa-plane"></i> COCKPIT - VOL <?php echo htmlspecialchars($vol['numero_vol']); ?>
                                </div>
                                
                                <?php
                                // Organiser les sièges par rangée
                                $sieges_par_rang = [];
                                foreach ($sieges_disponibles as $siege) {
                                    $sieges_par_rang[$siege['rang']][] = $siege;
                                }
                                
                                // Trier par rangée
                                ksort($sieges_par_rang);
                                
                                foreach ($sieges_par_rang as $rang => $sieges_rang):
                                    // Déterminer la configuration des sièges
                                    $positions = array_column($sieges_rang, 'position');
                                    $has_couloir = in_array('C', $positions);
                                ?>
                                    <div class="rangee-sieges">
                                        <div class="numero-rangee"><?php echo $rang; ?></div>
                                        
                                        <?php 
                                        $last_position = '';
                                        foreach ($sieges_rang as $siege): 
                                            // Ajouter un espace pour le couloir si nécessaire
                                            if ($has_couloir && $last_position === 'A' && $siege['position'] === 'C') {
                                                echo '<div class="couloir">Couloir</div>';
                                            }
                                            
                                            $classe_siege = 'disponible';
                                            if (in_array($siege['id_siege'], $sieges_occupes)) {
                                                $classe_siege = 'occupe';
                                            }
                                            
                                            // Ajouter classe pour siège premium/business
                                            $classe_siege .= ' ' . $siege['classe'];
                                        ?>
                                            <label class="siege <?php echo $classe_siege; ?>" 
                                                   data-supplement="<?php echo $siege['supplement_prix'] > 0 ? '+' . $siege['supplement_prix'] . '€' : ''; ?>"
                                                   title="Siège <?php echo $siege['position'].$rang; ?> - <?php echo $siege['classe']; ?><?php echo $siege['supplement_prix'] > 0 ? ' - Supplément: ' . $siege['supplement_prix'] . '€' : ''; ?>">
                                                <input type="checkbox" name="sieges[]" value="<?php echo $siege['id_siege']; ?>" 
                                                       style="display: none;" 
                                                       <?php echo in_array($siege['id_siege'], $sieges_occupes) ? 'disabled' : ''; ?>
                                                       onchange="toggleSiege(this)">
                                                <?php echo $siege['position']; ?>
                                            </label>
                                        <?php 
                                            $last_position = $siege['position'];
                                        endforeach; 
                                        ?>
                                    </div>
                                <?php endforeach; ?>
                                
                                <!-- Légende améliorée -->
                                <div class="legende-sieges">
                                    <div class="legende-item">
                                        <div class="legende-couleur" style="background: var(--success);"></div>
                                        <span>Disponible</span>
                                    </div>
                                    <div class="legende-item">
                                        <div class="legende-couleur" style="background: var(--primary);"></div>
                                        <span>Sélectionné</span>
                                    </div>
                                    <div class="legende-item">
                                        <div class="legende-couleur" style="background: var(--danger);"></div>
                                        <span>Occupé</span>
                                    </div>
                                    <div class="legende-item">
                                        <div class="legende-couleur" style="background: linear-gradient(135deg, #fff9e6, #ffefb3); border-color: var(--premium);"></div>
                                        <span>Première classe</span>
                                    </div>
                                    <div class="legende-item">
                                        <div class="legende-couleur" style="background: linear-gradient(135deg, #f0e6ff, #d9c8ff); border-color: var(--business);"></div>
                                        <span>Affaires</span>
                                    </div>
                                    <div class="legende-item">
                                        <div class="legende-couleur" style="background: var(--white); border-color: var(--economy);"></div>
                                        <span>Économique</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Options de bagage -->
                    <div class="form-section">
                        <h3 class="section-title">
                            <i class="fas fa-suitcase-rolling"></i> Options de bagage
                        </h3>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Inclus avec votre billet :</strong> 1 bagage à main (8kg) par passager
                        </div>
                        
                        <!-- Affichage des bagages sélectionnés -->
                        <div class="selected-items" id="selected-bagages-display" style="display: none;">
                            <h4 class="selected-title">
                                <i class="fas fa-check-circle"></i> Bagages sélectionnés
                            </h4>
                            <div id="selected-bagages-list"></div>
                        </div>
                        
                        <div class="bagages-container">
                            <?php foreach ($options_bagage as $option): ?>
                                <?php if ($option['nom_option'] !== 'Bagage à main'): ?>
                                    <div class="bagage-option">
                                        <div class="bagage-header">
                                            <h4>
                                                <?php echo htmlspecialchars($option['nom_option']); ?>
                                                <?php if ($option['poids_max']): ?>
                                                    <small>(jusqu'à <?php echo $option['poids_max']; ?>kg)</small>
                                                <?php endif; ?>
                                            </h4>
                                            <span class="bagage-price">+<?php echo number_format($option['prix_supplement'], 2, ',', ' '); ?>€</span>
                                        </div>
                                        
                                        <?php if (!empty($option['description'])): ?>
                                            <p class="bagage-description"><?php echo htmlspecialchars($option['description']); ?></p>
                                        <?php endif; ?>
                                        
                                        <div class="bagage-controls">
                                            <button type="button" class="btn-qty minus" data-target="bagage_<?php echo $option['id_option']; ?>">-</button>
                                            <input type="number" 
                                                   name="bagages[<?php echo $option['id_option']; ?>]" 
                                                   id="bagage_<?php echo $option['id_option']; ?>"
                                                   class="bagage-qty" 
                                                   value="0" 
                                                   min="0" 
                                                   max="10"
                                                   onchange="calculerPrix()">
                                            <button type="button" class="btn-qty plus" data-target="bagage_<?php echo $option['id_option']; ?>">+</button>
                                            
                                            <span class="bagage-total" id="bagage_total_<?php echo $option['id_option']; ?>">
                                                0,00€
                                            </span>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Loading indicator -->
                    <div class="loading" id="loading">
                        <div class="spinner"></div>
                        <p>Traitement de votre réservation...</p>
                    </div>
                    
                    <!-- Bouton de réservation -->
                    <div class="form-section">
                        <button type="submit" name="reserver" class="btn btn-success btn-lg btn-full" id="submit-btn">
                            <i class="fas fa-credit-card"></i> Confirmer et payer la réservation
                        </button>
                        <p style="text-align: center; margin-top: 15px; color: var(--gray); font-size: 0.9rem;">
                            <i class="fas fa-lock"></i> Paiement 100% sécurisé SSL - Vos données sont protégées
                        </p>
                    </div>
                </form>
            </div>
            
            <!-- Sidebar avec résumé -->
            <div class="reservation-sidebar">
                <!-- Résumé du vol -->
                <div class="sidebar-section">
                    <h3 class="sidebar-title">
                        <i class="fas fa-receipt"></i> Récapitulatif du vol
                    </h3>
                    <div class="flight-summary">
                        <div class="flight-route">
                            <div class="flight-airport">
                                <div class="airport-code"><?php echo substr($vol['depart'], 0, 3); ?></div>
                                <div class="airport-name"><?php echo htmlspecialchars($vol['depart']); ?></div>
                            </div>
                            <div class="flight-duration">
                                <div class="duration-line"></div>
                                <div><?php echo date('H:i', strtotime($vol['date_depart'])); ?> - <?php echo date('H:i', strtotime($vol['date_arrivee'])); ?></div>
                            </div>
                            <div class="flight-airport">
                                <div class="airport-code"><?php echo substr($vol['arrivee'], 0, 3); ?></div>
                                <div class="airport-name"><?php echo htmlspecialchars($vol['arrivee']); ?></div>
                            </div>
                        </div>
                        <div style="text-align: center; font-size: 0.9rem;">
                            <div><strong><?php echo htmlspecialchars($vol['nom_compagnie']); ?></strong> • Vol <?php echo htmlspecialchars($vol['numero_vol']); ?></div>
                            <div><?php echo htmlspecialchars($vol['avion_modele']); ?></div>
                        </div>
                    </div>
                </div>
                
                <!-- Détails du prix -->
                <div class="sidebar-section">
                    <h3 class="sidebar-title">
                        <i class="fas fa-euro-sign"></i> Détail du prix
                    </h3>
                    <div class="resume-prix">
                        <div class="ligne-prix">
                            <span>Billet(s) de base:</span>
                            <span id="sidebar-prix-base"><?php echo number_format($vol['prix'], 2, ',', ' '); ?>€</span>
                        </div>
                        <div class="ligne-prix">
                            <span>Suppléments sièges:</span>
                            <span id="sidebar-supplements">0,00€</span>
                        </div>
                        <div class="ligne-prix">
                            <span>Suppléments bagages:</span>
                            <span id="sidebar-bagages">0,00€</span>
                        </div>
                        <div class="ligne-prix">
                            <span>Frais de service:</span>
                            <span>9,00€</span>
                        </div>
                        <div class="ligne-prix">
                            <span>Taxes aéroport:</span>
                            <span>25,00€</span>
                        </div>
                        <div class="ligne-prix prix-total">
                            <span>Total à payer:</span>
                            <span id="sidebar-prix-total"><?php echo number_format($vol['prix'] + 9 + 25, 2, ',', ' '); ?>€</span>
                        </div>
                    </div>
                </div>
                
                <!-- Informations importantes -->
                <div class="sidebar-section">
                    <h3 class="sidebar-title">
                        <i class="fas fa-info-circle"></i> Informations
                    </h3>
                    <div class="alert alert-info" style="margin: 0;">
                        <i class="fas fa-clock"></i>
                        <div>
                            <strong>Enregistrement :</strong> Ouverture 2h avant le départ<br>
                            <strong>Embarquement :</strong> 40 min avant le départ
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Stocker les suppléments des sièges
        const supplementsSieges = {};
        <?php foreach ($sieges_disponibles as $siege): ?>
        supplementsSieges[<?php echo $siege['id_siege']; ?>] = <?php echo $siege['supplement_prix']; ?>;
        <?php endforeach; ?>

        // Stocker les noms des sièges
        const nomsSieges = {};
        <?php foreach ($sieges_disponibles as $siege): ?>
        nomsSieges[<?php echo $siege['id_siege']; ?>] = '<?php echo $siege['position'] . $siege['rang']; ?>';
        <?php endforeach; ?>

        // Stocker les classes des sièges
        const classesSieges = {};
        <?php foreach ($sieges_disponibles as $siege): ?>
        classesSieges[<?php echo $siege['id_siege']; ?>] = '<?php echo $siege['classe']; ?>';
        <?php endforeach; ?>

        // Stocker les options de bagage
        const optionsBagage = {};
        <?php foreach ($options_bagage as $option): ?>
        optionsBagage[<?php echo $option['id_option']; ?>] = {
            nom: '<?php echo addslashes($option['nom_option']); ?>',
            prix: <?php echo $option['prix_supplement']; ?>,
            poids: <?php echo $option['poids_max'] ?? 0; ?>
        };
        <?php endforeach; ?>

        const prixBase = <?php echo $vol['prix']; ?>;
        const fraisService = 9.00;
        const taxesAeroport = 25.00;

        function calculerPrix() {
            const nbPassagers = parseInt(document.getElementById('nombre_passagers').value);
            let supplements = 0;
            const siegesSelectionnes = [];
            let supplementBagages = 0;
            const bagagesSelectionnes = [];
            
            // Calculer les suppléments des sièges sélectionnés
            document.querySelectorAll('input[name="sieges[]"]:checked').forEach(checkbox => {
                const siegeId = parseInt(checkbox.value);
                supplements += supplementsSieges[siegeId] || 0;
                siegesSelectionnes.push(siegeId);
            });
            
            // Calculer les suppléments des bagages
            document.querySelectorAll('input.bagage-qty').forEach(input => {
                const optionId = parseInt(input.name.match(/\[(\d+)\]/)[1]);
                const quantite = parseInt(input.value) || 0;
                
                if (quantite > 0 && optionsBagage[optionId]) {
                    const supplement = optionsBagage[optionId].prix * quantite;
                    supplementBagages += supplement;
                    bagagesSelectionnes.push({
                        id: optionId,
                        nom: optionsBagage[optionId].nom,
                        quantite: quantite,
                        total: supplement
                    });
                    
                    // Mettre à jour le total par option
                    document.getElementById(`bagage_total_${optionId}`).textContent = 
                        supplement.toFixed(2).replace('.', ',') + '€';
                } else {
                    document.getElementById(`bagage_total_${optionId}`).textContent = '0,00€';
                }
            });
            
            const totalBase = prixBase * nbPassagers;
            const total = totalBase + supplements + supplementBagages + fraisService + taxesAeroport;
            
            // Mettre à jour l'affichage principal
            document.getElementById('passagers-count').textContent = nbPassagers;
            document.getElementById('prix-base').textContent = totalBase.toFixed(2).replace('.', ',') + '€';
            document.getElementById('supplements-total').textContent = supplements.toFixed(2).replace('.', ',') + '€';
            document.getElementById('supplements-bagage').textContent = supplementBagages.toFixed(2).replace('.', ',') + '€';
            document.getElementById('prix-total').textContent = total.toFixed(2).replace('.', ',') + '€';
            
            // Mettre à jour la sidebar
            document.getElementById('sidebar-prix-base').textContent = totalBase.toFixed(2).replace('.', ',') + '€';
            document.getElementById('sidebar-supplements').textContent = supplements.toFixed(2).replace('.', ',') + '€';
            document.getElementById('sidebar-bagages').textContent = supplementBagages.toFixed(2).replace('.', ',') + '€';
            document.getElementById('sidebar-prix-total').textContent = total.toFixed(2).replace('.', ',') + '€';
            
            // Mettre à jour l'affichage des sièges et bagages sélectionnés
            afficherSiegesSelectionnes(siegesSelectionnes);
            afficherBagagesSelectionnes(bagagesSelectionnes);
        }

        function afficherSiegesSelectionnes(siegesIds) {
            const container = document.getElementById('selected-seats-display');
            const list = document.getElementById('selected-seats-list');
            
            if (siegesIds.length === 0) {
                container.style.display = 'none';
                return;
            }
            
            list.innerHTML = '';
            siegesIds.forEach(siegeId => {
                const nomSiege = nomsSieges[siegeId];
                const supplement = supplementsSieges[siegeId] || 0;
                const classe = classesSieges[siegeId];
                
                let badgeClass = '';
                switch(classe) {
                    case 'première': badgeClass = 'badge-premium'; break;
                    case 'affaires': badgeClass = 'badge-business'; break;
                    default: badgeClass = 'badge-economy';
                }
                
                const badge = document.createElement('div');
                badge.className = 'item-badge';
                badge.innerHTML = `
                    <i class="fas fa-chair"></i> 
                    <strong>${nomSiege}</strong>
                    <span class="class-badge ${badgeClass}">${classe}</span>
                    ${supplement > 0 ? '<span style="color: var(--secondary); margin-left: 5px;">(+' + supplement + '€)</span>' : ''}
                `;
                list.appendChild(badge);
            });
            
            container.style.display = 'block';
        }

        function afficherBagagesSelectionnes(bagages) {
            const container = document.getElementById('selected-bagages-display');
            const list = document.getElementById('selected-bagages-list');
            
            if (bagages.length === 0) {
                container.style.display = 'none';
                return;
            }
            
            list.innerHTML = '';
            bagages.forEach(bagage => {
                const badge = document.createElement('div');
                badge.className = 'item-badge';
                badge.innerHTML = `
                    <i class="fas fa-suitcase"></i> 
                    ${bagage.quantite}x ${bagage.nom} 
                    <strong style="color: var(--primary); margin-left: 5px;">
                        (${bagage.total.toFixed(2).replace('.', ',')}€)
                    </strong>
                `;
                list.appendChild(badge);
            });
            
            container.style.display = 'block';
        }

        function toggleSiege(checkbox) {
            const siege = checkbox.parentElement;
            const maxSieges = parseInt(document.getElementById('nombre_passagers').value);
            const siegesSelectionnes = document.querySelectorAll('input[name="sieges[]"]:checked');
            
            if (checkbox.checked && siegesSelectionnes.length > maxSieges) {
                checkbox.checked = false;
                siege.classList.remove('selectionne');
                alert(`Vous ne pouvez sélectionner que ${maxSieges} siège(s) pour ${maxSieges} passager(s)`);
                return;
            }
            
            siege.classList.toggle('selectionne', checkbox.checked);
            calculerPrix();
            
            // Mettre à jour la disponibilité des autres sièges
            document.querySelectorAll('input[name="sieges[]"]').forEach(cb => {
                if (!cb.checked && cb.disabled === false) {
                    cb.disabled = (siegesSelectionnes.length >= maxSieges);
                }
            });
        }

        // Gestion des boutons quantité pour les bagages
        document.querySelectorAll('.btn-qty').forEach(btn => {
            btn.addEventListener('click', function() {
                const target = this.getAttribute('data-target');
                const input = document.getElementById(target);
                let value = parseInt(input.value) || 0;
                
                if (this.classList.contains('plus')) {
                    value++;
                } else if (this.classList.contains('minus') && value > 0) {
                    value--;
                }
                
                input.value = value;
                calculerPrix();
            });
        });

        // Timer de réservation
        let timer = 15 * 60; // 15 minutes en secondes
        const timerElement = document.getElementById('reservation-timer');

        function updateTimer() {
            const minutes = Math.floor(timer / 60);
            const seconds = timer % 60;
            timerElement.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            
            if (timer <= 0) {
                clearInterval(timerInterval);
                alert('Temps écoulé ! Votre réservation a expiré.');
                window.location.href = 'index.php';
            }
            
            // Changement de couleur pour les 5 dernières minutes
            if (timer <= 300) {
                timerElement.parentElement.style.background = 'linear-gradient(135deg, var(--danger), #e35d6a)';
            } else if (timer <= 600) {
                timerElement.parentElement.style.background = 'linear-gradient(135deg, var(--warning), #ffd351)';
            }
            
            timer--;
        }

        const timerInterval = setInterval(updateTimer, 1000);

        // Gestion de la soumission du formulaire
        document.getElementById('reservationForm').addEventListener('submit', function(e) {
            const nbPassagers = parseInt(document.getElementById('nombre_passagers').value);
            const siegesSelectionnes = document.querySelectorAll('input[name="sieges[]"]:checked');
            
            if (siegesSelectionnes.length !== nbPassagers) {
                e.preventDefault();
                alert(`Vous devez sélectionner exactement ${nbPassagers} siège(s) pour ${nbPassagers} passager(s)`);
                return;
            }
            
            // Afficher le loading
            document.getElementById('loading').style.display = 'block';
            document.getElementById('submit-btn').disabled = true;
            
            // Arrêter le timer
            clearInterval(timerInterval);
        });

        // Initialisation
        document.getElementById('nombre_passagers').addEventListener('change', function() {
            const maxSieges = parseInt(this.value);
            const siegesSelectionnes = document.querySelectorAll('input[name="sieges[]"]:checked');
            
            // Désélectionner les sièges en trop
            if (siegesSelectionnes.length > maxSieges) {
                for (let i = maxSieges; i < siegesSelectionnes.length; i++) {
                    siegesSelectionnes[i].checked = false;
                    siegesSelectionnes[i].parentElement.classList.remove('selectionne');
                }
            }
            
            // Mettre à jour la disponibilité
            document.querySelectorAll('input[name="sieges[]"]').forEach(checkbox => {
                if (!checkbox.checked && checkbox.disabled === false) {
                    checkbox.disabled = (document.querySelectorAll('input[name="sieges[]"]:checked').length >= maxSieges);
                }
            });
            
            calculerPrix();
        });

        // Initialiser le calcul du prix
        calculerPrix();

        // Empêcher la fermeture de la page pendant la réservation
        window.addEventListener('beforeunload', function(e) {
            if (document.querySelectorAll('input[name="sieges[]"]:checked').length > 0) {
                e.preventDefault();
                e.returnValue = 'Votre réservation en cours sera perdue. Êtes-vous sûr de vouloir quitter ?';
            }
        });

        // Raccourcis clavier
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'Enter') {
                document.getElementById('submit-btn').click();
            }
        });
    </script>
</body>
</html>