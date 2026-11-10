<?php
require_once __DIR__ . '/lib.php';

const OWNER_EMAIL = 'ladyenglishcourse@gmail.com';

function back(string $code): void {
    header('Location: index.php?sent=' . $code . '#contact');
    exit;
}

function message_length(string $value): int {
    return function_exists('mb_strlen')
        ? mb_strlen($value, 'UTF-8')
        : strlen($value);
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Location: index.php#contact');
    exit;
}

session_start([
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax'
]);

// Spam protection
if (!empty($_POST['website'])) {
    back('1');
}

// Read and validate form fields
$name = trim(preg_replace('/[\r\n]+/', ' ', (string)($_POST['name'] ?? '')));
$email = trim((string)($_POST['email'] ?? ''));
$message = trim((string)($_POST['message'] ?? ''));

if (
    $name === '' ||
    $message === '' ||
    message_length($name) > 100 ||
    message_length($message) > 3000 ||
    strlen($email) > 150
) {
    back('2');
}

if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    back('2');
}

// Rate limit
if (
    isset($_SESSION['last_msg']) &&
    time() - (int)$_SESSION['last_msg'] < 30
) {
    back('3');
}

// Save message
ensure_data_dir();

$file = DATA_DIR . '/messages.json';

$messages = [];

if (is_file($file)) {
    $existing = json_decode((string)file_get_contents($file), true);

    if (is_array($existing)) {
        $messages = $existing;
    }
}

$messages[] = [
    'id' => bin2hex(random_bytes(8)),
    'name' => $name,
    'email' => $email,
    'message' => $message,
    'date' => date('Y-m-d H:i:s'),
    'read' => false
];

$saved = file_put_contents(
    $file,
    json_encode($messages, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
    LOCK_EX
);

if ($saved === false) {
    back('0');
}

$_SESSION['last_msg'] = time();

// Optional email notification
$subject = 'New message from Lady English website';

$body =
    "New website message\n\n" .
    "Name: $name\n" .
    "Email: " . ($email !== '' ? $email : 'Not provided') . "\n\n" .
    "Message:\n$message";

$headers = [
    'From: Lady English <noreply@ladyenglish.com>',
    'Content-Type: text/plain; charset=UTF-8'
];

if ($email !== '') {
    $headers[] = 'Reply-To: ' . $email;
}

@mail(
    OWNER_EMAIL,
    '=?UTF-8?B?' . base64_encode($subject) . '?=',
    $body,
    implode("\r\n", $headers)
);

// The message is safely stored even if email fails.
back('1');