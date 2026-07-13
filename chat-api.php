<?php
// DO NOT share this file publicly
define('KILO_API_KEY', 'kilo_xxxxxxxxxxxxxxxxxxxxxxxx');
define('KILO_API_URL', 'https://api.kilo.ai/api/gateway/chat/completions');
define('KILO_MODEL', 'kilo-auto/free');
define('SYSTEM_PROMPT', 'You are Dola, support assistant for [YourWebsite.com]. Answer only about products, shipping, hours, policies. Keep replies short & friendly. If unsure: "Please contact support."');

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$userMsg = trim($input['message'] ?? '');
if (!$userMsg) {
    echo json_encode(['reply' => 'Please type a question.']);
    exit;
}

$body = json_encode([
    'model' => KILO_MODEL,
    'messages' => [
        ['role' => 'system', 'content' => SYSTEM_PROMPT],
        ['role' => 'user', 'content' => $userMsg]
    ],
    'temperature' => 0.3,
    'max_tokens' => 300
]);

$ch = curl_init(KILO_API_URL);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . KILO_API_KEY
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Timeout and DNS options
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
curl_setopt($ch, CURLOPT_DNS_SERVERS, '8.8.8.8,8.8.4.4');

$res = curl_exec($ch);
$err = curl_error($ch);
curl_close($ch);

if ($err) {
    echo json_encode(['reply' => 'Connection error: ' . $err]);
    exit;
}

$data = json_decode($res, true);
$reply = $data['choices'][0]['message']['content'] ?? 'Sorry, try again later.';
echo json_encode(['reply' => $reply]);
