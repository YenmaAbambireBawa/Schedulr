<?php
/**
 * Email Configuration
 * Schedulr — config/email.php
 *
 * Uses PHPMailer via Composer.
 * Install PHPMailer first:
 *   cd C:/xampp/htdocs/schedulr
 *   composer require phpmailer/phpmailer
 */

// ─── CHANGE THESE TO YOUR OWN VALUES ────────────────────────────────────────

define('MAIL_HOST',       'smtp.gmail.com');   // Gmail SMTP server
define('MAIL_PORT',       587);                // 587 = TLS (recommended)
define('MAIL_USERNAME',   'schedulr.au@gmail.com');  // Your Gmail address
define('MAIL_PASSWORD',   'uwvi toph auiz wfvs');   // Gmail App Password (NOT your real password)
define('MAIL_FROM',       'schedulr.au@gmail.com');  // Sender address
define('MAIL_FROM_NAME',  'Schedulr');              // Sender display name
define('MAIL_ENCRYPTION', 'tls');                   // 'tls' or 'ssl'

// Your app's base URL — used to build the verification link in the email
define('APP_BASE_URL', 'http://localhost/schedulr');

// ─────────────────────────────────────────────────────────────────────────────

/*
 * HOW TO GET A GMAIL APP PASSWORD:
 * 1. Go to your Google Account → Security
 * 2. Turn on 2-Step Verification (required)
 * 3. Go to Security → App passwords
 * 4. Create a new App Password for "Mail" / "Windows Computer"
 * 5. Copy the 16-character password and paste it above (spaces are fine)
 *
 * WHY NOT YOUR REAL GMAIL PASSWORD?
 * Google blocks direct SMTP logins unless you use an App Password.
 * App Passwords are safer — you can revoke them anytime.
 *
 * ALTERNATIVE — Outlook/Office365:
 *   MAIL_HOST     = 'smtp.office365.com'
 *   MAIL_PORT     = 587
 *   MAIL_USERNAME = 'you@yourdomain.com'
 *   MAIL_PASSWORD = 'your actual password'
 *   MAIL_FROM     = 'you@yourdomain.com'
 */