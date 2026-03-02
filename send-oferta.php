<?php
/**
 * Primește datele formularului și trimite email de la contact@ticuconstruct.ro către contact@ticuconstruct.ro
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Metoda neacceptată']);
    exit;
}

$telefon   = trim($_POST['telefon'] ?? '');
$email     = trim($_POST['email'] ?? '');
$zona      = trim($_POST['zona'] ?? '');
$descriere = trim($_POST['descriere'] ?? '');

if (empty($telefon) || empty($email) || empty($zona) || empty($descriere)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Toate câmpurile sunt obligatorii']);
    exit;
}

$configPath = __DIR__ . '/config.php';
if (!file_exists($configPath)) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Lipsește config.php. Copiază config.php.example în config.php și completează datele SMTP.']);
    exit;
}

$config = require $configPath;

// Încarcă PHPMailer (după composer install)
require __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = $config['smtp_host'];
    $mail->SMTPAuth   = true;
    $mail->Username   = $config['smtp_user'];
    $mail->Password   = $config['smtp_pass'];
    $mail->SMTPSecure = $config['smtp_secure'] ?? PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = $config['smtp_port'] ?? 587;
    $mail->CharSet    = 'UTF-8';

    // De la și către contact@ticuconstruct.ro
    $mail->setFrom($config['smtp_user'], 'Ticu Construct - Cerere Ofertă');
    $mail->addAddress($config['smtp_user'], 'Ticu Construct');

    $mail->Subject = 'Cerere ofertă - Ticu Construct';
    $mail->Body    = "Cerere ofertă nouă\n\n" .
                     "Telefon client: $telefon\n" .
                     "Email client: $email\n" .
                     "Locație/Zonă: $zona\n\n" .
                     "Descriere:\n$descriere";

    $mail->send();
    echo json_encode(['ok' => true]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Eroare la trimitere: ' . $mail->ErrorInfo]);
}
