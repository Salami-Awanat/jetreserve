<?php
session_start();
require_once 'includes/db.php';
require_once 'classes/Reservation.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $database = new Database();
        $db = $database->getConnection();
        $reservation = new Reservation($db);
        
        // Préparer les données de réservation
        $booking_data = [
            'airline' => $_POST['airline'],
            'flight_number' => $_POST['flight_number'],
            'from' => $_POST['from'],
            'to' => $_POST['to'],
            'departure_date' => $_POST['departure_date'],
            'class' => $_POST['class'],
            'total_price' => $_POST['total_price'],
            'passengers' => $_POST['passengers']
        ];
        
        // Créer la réservation
        $result = $reservation->create($booking_data);
        
        if ($result) {
            // Rediriger vers une page de confirmation
            header('Location: booking_confirmation.php?id=' . $result);
            exit;
        } else {
            throw new Exception("Erreur lors de la création de la réservation");
        }
        
    } catch (Exception $e) {
        // Rediriger vers une page d'erreur
        header('Location: error.php?message=' . urlencode($e->getMessage()));
        exit;
    }
} else {
    header('Location: index.php');
    exit;
}
?>