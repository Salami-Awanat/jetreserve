<?php
// Configuration SMTP pour PHPMailer
return [
    // Activer l'utilisation de SMTP (true) ou utiliser la fonction mail() de PHP (false)
    'use_smtp' => true,

    // Paramètres SMTP (si use_smtp = true)
    'host' => 'smtp.gmail.com', // ou autre service SMTP
    'username' => 'agbalessifloriane69@gmail.com',
    'password' => 'sgyd alra jjme wwpr', // Mot de passe d'application Gmail
    'port' => 587,
    'encryption' => 'tls', // 'ssl' or 'tls' or ''

    // Expéditeur par défaut
    'from_email' => 'noreply@jetreserve.com',
    'from_name' => 'JetReserve',

    // Adresse de copie cachée (admin)
    'admin_bcc' => 'admin@jetreserve.com',
];