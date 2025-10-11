// Script pour la page de paiement
$(document).ready(function() {
    // Gestion des méthodes de paiement
    $('.method-card').on('click', function() {
        const method = $(this).data('method');
        
        // Désactiver toutes les méthodes
        $('.method-card').removeClass('active');
        $('.payment-method-form').removeClass('active');
        
        // Activer la méthode sélectionnée
        $(this).addClass('active');
        $(`#${method}Form`).addClass('active');
    });

    // Formatage du numéro de carte
    $('#card_number').on('input', function() {
        let value = $(this).val().replace(/\s+/g, '').replace(/[^0-9]/gi, '');
        let formattedValue = value.match(/.{1,4}/g)?.join(' ');
        $(this).val(formattedValue || value);
        
        // Détection du type de carte
        detectCardType(value);
    });

    // Formatage de la date d'expiration
    $('#expiry_date').on('input', function() {
        let value = $(this).val().replace(/\D/g, '');
        if (value.length >= 2) {
            value = value.substring(0, 2) + '/' + value.substring(2, 4);
        }
        $(this).val(value);
    });

    // Validation du formulaire
    $('#paymentForm').on('submit', function(e) {
        e.preventDefault();
        
        if (validatePaymentForm()) {
            processPayment();
        }
    });

    // Détection du type de carte
    function detectCardType(cardNumber) {
        const cardTypes = {
            visa: /^4/,
            mastercard: /^5[1-5]/,
            amex: /^3[47]/,
            discover: /^6(?:011|5)/
        };
        
        $('#card_number').removeClass('visa mastercard amex discover');
        
        for (const [type, pattern] of Object.entries(cardTypes)) {
            if (pattern.test(cardNumber)) {
                $('#card_number').addClass(type);
                break;
            }
        }
    }

    // Validation du formulaire
    function validatePaymentForm() {
        const currentMethod = $('.method-card.active').data('method');
        let isValid = true;

        if (currentMethod === 'card') {
            // Validation carte bancaire
            const cardNumber = $('#card_number').val().replace(/\s+/g, '');
            const expiryDate = $('#expiry_date').val();
            const cvv = $('#cvv').val();
            const cardHolder = $('#card_holder').val();

            if (!validateCardNumber(cardNumber)) {
                showError('card_number', 'Numéro de carte invalide');
                isValid = false;
            } else {
                clearError('card_number');
            }

            if (!validateExpiryDate(expiryDate)) {
                showError('expiry_date', 'Date d\'expiration invalide');
                isValid = false;
            } else {
                clearError('expiry_date');
            }

            if (!validateCVV(cvv)) {
                showError('cvv', 'CVV invalide');
                isValid = false;
            } else {
                clearError('cvv');
            }

            if (cardHolder.trim().length < 2) {
                showError('card_holder', 'Nom du titulaire requis');
                isValid = false;
            } else {
                clearError('card_holder');
            }
        }

        // Validation adresse de facturation
        const billingAddress = $('#billing_address').val();
        const billingCity = $('#billing_city').val();
        const billingZip = $('#billing_zip').val();
        const billingCountry = $('#billing_country').val();

        if (!billingAddress.trim()) {
            showError('billing_address', 'Adresse requise');
            isValid = false;
        } else {
            clearError('billing_address');
        }

        if (!billingCity.trim()) {
            showError('billing_city', 'Ville requise');
            isValid = false;
        } else {
            clearError('billing_city');
        }

        if (!billingZip.trim()) {
            showError('billing_zip', 'Code postal requis');
            isValid = false;
        } else {
            clearError('billing_zip');
        }

        if (!billingCountry) {
            showError('billing_country', 'Pays requis');
            isValid = false;
        } else {
            clearError('billing_country');
        }

        // Validation conditions générales
        if (!$('#terms').is(':checked')) {
            showError('terms', 'Vous devez accepter les conditions générales');
            isValid = false;
        } else {
            clearError('terms');
        }

        return isValid;
    }

    // Validation numéro de carte (algorithme de Luhn)
    function validateCardNumber(cardNumber) {
        if (cardNumber.length < 13 || cardNumber.length > 19) return false;
        
        let sum = 0;
        let isEven = false;
        
        for (let i = cardNumber.length - 1; i >= 0; i--) {
            let digit = parseInt(cardNumber.charAt(i));
            
            if (isEven) {
                digit *= 2;
                if (digit > 9) digit -= 9;
            }
            
            sum += digit;
            isEven = !isEven;
        }
        
        return sum % 10 === 0;
    }

    // Validation date d'expiration
    function validateExpiryDate(expiryDate) {
        if (!/^\d{2}\/\d{2}$/.test(expiryDate)) return false;
        
        const [month, year] = expiryDate.split('/');
        const now = new Date();
        const currentYear = now.getFullYear() % 100;
        const currentMonth = now.getMonth() + 1;
        
        const expMonth = parseInt(month);
        const expYear = parseInt(year);
        
        if (expMonth < 1 || expMonth > 12) return false;
        if (expYear < currentYear) return false;
        if (expYear === currentYear && expMonth < currentMonth) return false;
        
        return true;
    }

    // Validation CVV
    function validateCVV(cvv) {
        return /^\d{3,4}$/.test(cvv);
    }

    // Affichage des erreurs
    function showError(fieldId, message) {
        const field = $('#' + fieldId);
        field.addClass('error');
        
        let errorElement = field.siblings('.error-message');
        if (errorElement.length === 0) {
            errorElement = $('<div class="error-message"></div>');
            field.after(errorElement);
        }
        
        errorElement.text(message);
    }

    function clearError(fieldId) {
        const field = $('#' + fieldId);
        field.removeClass('error');
        field.siblings('.error-message').remove();
    }

    // Traitement du paiement
    function processPayment() {
        const paymentBtn = $('.btn-payment');
        const originalContent = paymentBtn.html();
        
        // Simulation de traitement
        paymentBtn.html(`
            <i class="fas fa-spinner fa-spin"></i>
            <span>Traitement en cours...</span>
            <small>Veuillez patienter</small>
        `);
        paymentBtn.prop('disabled', true);

        // Simulation de délai de traitement
        setTimeout(() => {
            // Génération d'un numéro de réservation
            const reservationNumber = 'JR' + Date.now() + Math.floor(Math.random() * 1000);
            
            // Redirection vers la page de confirmation
            window.location.href = `confirmation.php?reservation=${reservationNumber}&amount=${getTotalAmount()}`;
        }, 3000);
    }

    // Récupération du montant total
    function getTotalAmount() {
        return <?php echo $total; ?>;
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

    // Ajout des styles pour les erreurs et types de cartes
    $('head').append(`
        <style>
            .error {
                border-color: #ef4444 !important;
                box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1) !important;
            }
            
            .error-message {
                color: #ef4444;
                font-size: 0.8rem;
                margin-top: 5px;
                font-weight: 500;
            }
            
            #card_number.visa {
                background: linear-gradient(45deg, transparent 90%, #1a1f71 90%);
            }
            
            #card_number.mastercard {
                background: linear-gradient(45deg, transparent 90%, #eb001b 90%);
            }
            
            #card_number.amex {
                background: linear-gradient(45deg, transparent 90%, #2e77bc 90%);
            }
            
            #card_number.discover {
                background: linear-gradient(45deg, transparent 90%, #ff6000 90%);
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
            
            .btn-payment:disabled {
                opacity: 0.7;
                cursor: not-allowed;
            }
        </style>
    `);
});