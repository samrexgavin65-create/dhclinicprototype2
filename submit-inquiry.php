<?php
declare(strict_types=1);

function redirect_to_contact(string $query = ''): void
{
    $target = 'contact.html';
    if ($query !== '') {
        $target .= '?' . $query;
    }

    header('Location: ' . $target);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    redirect_to_contact();
}

$honeypot = trim((string)($_POST['website'] ?? ''));
if ($honeypot !== '') {
    // Silent success for bots.
    redirect_to_contact('sent=1');
}

$name = trim((string)($_POST['name'] ?? ''));
$email = trim((string)($_POST['email'] ?? ''));
$phone = trim((string)($_POST['phone'] ?? ''));
$message = trim((string)($_POST['message'] ?? ''));

if (strlen($name) < 2 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($message) < 8) {
    redirect_to_contact('error=validation');
}

$strip_newlines = static function (string $value): string {
    return str_replace(["\r", "\n"], ' ', $value);
};

$safe_name = $strip_newlines($name);
$safe_email = $strip_newlines($email);
$safe_phone = $strip_newlines($phone);

$to = 'info@dhclinic.co.uk';
$subject = 'Website enquiry - Davenport House Clinic';
$body = "A new enquiry was submitted via the website contact form.\n\n"
    . "Name: {$safe_name}\n"
    . "Email: {$safe_email}\n"
    . "Phone: {$safe_phone}\n\n"
    . "Message:\n{$message}\n";

$headers = [
    'From: Davenport House Clinic <info@dhclinic.co.uk>',
    'Reply-To: ' . $safe_email,
    'Content-Type: text/plain; charset=UTF-8',
];

$sent = @mail($to, $subject, $body, implode("\r\n", $headers));

if ($sent) {
    redirect_to_contact('sent=1');
}

redirect_to_contact('error=mail');
