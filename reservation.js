// Script pour la page de réservation
$(document).ready(function() {
    // Gestion des options supplémentaires
    $('input[type="checkbox"]').on('change', function() {
        updateSummary();
        updateHiddenFields();
    });

    // Validation du formulaire de réservation
    $('#reservationForm').on('submit', function(e) {
        if (!validateReservationForm()) {
            e.preventDefault();
        }
        // Si validation OK, le formulaire se soumet normalement vers paiement.php
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
        $('#final-total').text(total + '€');
    }

    // Mise à jour des champs cachés pour le paiement
    function updateHiddenFields() {
        $('#hidden_baggage').val($('#extra_baggage').is(':checked') ? '1' : '0');
        $('#hidden_insurance').val($('#travel_insurance').is(':checked') ? '1' : '0');
        $('#hidden_seat').val($('#premium_seat').is(':checked') ? '1' : '0');
    }

    // Validation du formulaire de réservation
    function validateReservationForm() {
        let isValid = true;

        // Validation des informations voyageurs
        const passengerCount = parseInt('<?php echo $passengerCount; ?>');
        
        for (let i = 1; i <= passengerCount; i++) {
            const civility = $(`#passenger_civility_${i}`).val();
            const lastname = $(`#passenger_lastname_${i}`).val();
            const firstname = $(`#passenger_firstname_${i}`).val();
            const birthdate = $(`#passenger_birthdate_${i}`).val();
            const email = $(`#passenger_email_${i}`).val();
            const phone = $(`#passenger_phone_${i}`).val();

            if (!civility || !lastname || !firstname || !birthdate || !email || !phone) {
                isValid = false;
                showNotification('Veuillez remplir tous les champs obligatoires pour chaque voyageur', 'error');
                break;
            }

            // Validation email
            if (email && !isValidEmail(email)) {
                isValid = false;
                showNotification(`Format d'email invalide pour le voyageur ${i}`, 'error');
                break;
            }
        }

        // Validation conditions générales
        if (!$('#terms').is(':checked')) {
            isValid = false;
            showNotification('Vous devez accepter les conditions générales', 'error');
        }

        return isValid;
    }

    // Validation email
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
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

    // Style pour les notifications
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
            
            .security-guarantee {
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 10px;
                padding: 15px;
                background: var(--success-light);
                border-radius: 6px;
                color: var(--success-dark);
                font-weight: 500;
                margin-top: 20px;
            }
            
            .btn-payment:disabled {
                opacity: 0.7;
                cursor: not-allowed;
            }
        </style>
    `);

    // Initialisation
    updateSummary();
    updateHiddenFields();
});