<?php
/**
 * Primește datele formularului, le salvează local și trimite email direct.
 * Nu folosește mailto.
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

if ($telefon === '' || $email === '' || $zona === '' || $descriere === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Toate campurile sunt obligatorii.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Email invalid.']);
    exit;
}

$to = 'contact@ticuconstruct.ro';
$from = 'contact@ticuconstruct.ro';
$subject = 'Cerere oferta - Ticu Construct';
$body = "Cerere oferta noua\n\n" .
        "Telefon client: {$telefon}\n" .
        "Email client: {$email}\n" .
        "Locatie/Zona: {$zona}\n\n" .
        "Descriere:\n{$descriere}\n";

// Salveaza local o copie a datelor clientului
$csvFile = __DIR__ . DIRECTORY_SEPARATOR . 'oferte.csv';
$csvHandle = fopen($csvFile, 'a');
if ($csvHandle !== false) {
    if (filesize($csvFile) === 0) {
        fputcsv($csvHandle, ['data', 'telefon', 'email', 'zona', 'descriere']);
    }
    fputcsv($csvHandle, [date('Y-m-d H:i:s'), $telefon, $email, $zona, $descriere]);
    fclose($csvHandle);
}

$headers = [];
$headers[] = 'MIME-Version: 1.0';
$headers[] = 'Content-Type: text/plain; charset=UTF-8';
$headers[] = 'From: Ticu Construct <' . $from . '>';
$headers[] = 'Reply-To: ' . $email;
$headers[] = 'X-Mailer: PHP/' . phpversion();

$sent = mail($to, $subject, $body, implode("\r\n", $headers));

if (!$sent) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => 'Serverul nu a putut trimite emailul. Verifica setarile de mail pe hosting.'
    ]);
    exit;
}

echo json_encode(['ok' => true]);
