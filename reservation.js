// Script pour la page de réservation
$(document).ready(function() {
    // Gestion des options supplémentaires
    $('input[type="checkbox"]').on('change', function() {
        updateSummary();
    });

    // Formatage du numéro de carte
    $('#card_number').on('input', function() {
        let value = $(this).val().replace(/\s+/g, '').replace(/[^0-9]/gi, '');
        let formattedValue = value.match(/.{1,4}/g)?.join(' ');
        $(this).val(formattedValue || value);
    });

    // Formatage de la date d'expiration
    $('#expiry_date').on('input', function() {
        let value = $(this).val().replace(/\D/g, '');
        if (value.length >= 2) {
            value = value.substring(0, 2) + '/' + value.substring(2, 4);
        }
        $(this).val(value);
    });

    // Validation du formulaire de paiement
    $('#paymentForm').on('submit', function(e) {
        e.preventDefault();
        
        if (validatePaymentForm()) {
            processPayment();
        }
    });

    // Mise à jour du récapitulatif
    function updateSummary() {
        const basePrice = parseInt('<?php echo $flightData["price"]; ?>');
        const passengerCount = parseInt('<?php echo $passengerCount; ?>');
        let total = basePrice * passengerCount;

        // Bagage supplémentaire
        if ($('#extra_baggage').is(':checked')) {
            $('#baggage-item').show();
            total += 35;
        } else {
            $('#baggage-item').hide();
        }

        // Assurance voyage
        if ($('#travel_insurance').is(':checked')) {
            $('#insurance-item').show();
            total += 29;
        } else {
            $('#insurance-item').hide();
        }

        // Siège premium
        if ($('#premium_seat').is(':checked')) {
            $('#seat-item').show();
            total += 25;
        } else {
            $('#seat-item').hide();
        }

        $('#total-price').text(total + '€');
    }

    // Validation du formulaire de paiement
    function validatePaymentForm() {
        const cardNumber = $('#card_number').val().replace(/\s+/g, '');
        const expiryDate = $('#expiry_date').val();
        const cvv = $('#cvv').val();
        const cardHolder = $('#card_holder').val();

        // Validation numéro de carte (simplifiée)
        if (cardNumber.length !== 16 || !/^\d+$/.test(cardNumber)) {
            showNotification('Veuillez saisir un numéro de carte valide (16 chiffres)', 'error');
            return false;
        }

        // Validation date d'expiration
        if (!/^\d{2}\/\d{2}$/.test(expiryDate)) {
            showNotification('Format de date d\'expiration invalide (MM/AA)', 'error');
            return false;
        }

        // Validation CVV
        if (cvv.length < 3 || cvv.length > 4 || !/^\d+$/.test(cvv)) {
            showNotification('CVV invalide (3 ou 4 chiffres)', 'error');
            return false;
        }

        // Validation nom du titulaire
        if (cardHolder.trim().length < 2) {
            showNotification('Veuillez saisir le nom du titulaire de la carte', 'error');
            return false;
        }

        return true;
    }

    // Traitement du paiement
    function processPayment() {
        const paymentBtn = $('.payment-btn');
        const originalText = paymentBtn.html();
        
        // Simulation de traitement
        paymentBtn.html('<i class="fas fa-spinner fa-spin"></i> Traitement en cours...');
        paymentBtn.prop('disabled', true);

        setTimeout(() => {
            // Simulation de succès
            showNotification('Paiement accepté ! Votre réservation est confirmée.', 'success');
            
            // Redirection vers la page de confirmation
            setTimeout(() => {
                const reservationNumber = 'JR' + Date.now() + Math.floor(Math.random() * 1000);
                window.location.href = 'confirmation.php?reservation=' + reservationNumber;
            }, 2000);
            
        }, 3000);
    }

    // Affichage des notifications
    function showNotification(message, type) {
        const notification = $('<div class="notification"></div>');
        const bgColor = type === 'success' ? '#10b981' : '#ef4444';
        
        notification.css({
            position: 'fixed',
            top: '20px',
            right: '20px',
            background: bgColor,
            color: 'white',
            padding: '15px 20px',
            borderRadius: '8px',
            zIndex: '10000',
            boxShadow: '0 4px 12px rgba(0,0,0,0.15)',
            animation: 'slideInRight 0.3s ease-out',
            maxWidth: '400px',
            fontWeight: '500'
        });
        
        notification.text(message);
        $('body').append(notification);
        
        setTimeout(() => {
            notification.animate({
                right: '-500px'
            }, 300, function() {
                $(this).remove();
            });
        }, 5000);
    }

    // Style pour les champs en erreur
    $('head').append(`
        <style>
            .error {
                border-color: #ef4444 !important;
                box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1) !important;
            }
            
            @keyframes slideInRight {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
        </style>
    `);

    // Initialisation
    updateSummary();
});