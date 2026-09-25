<?php
// Fail startup if Telegram did not actually accept the webhook.
define('FAOXIMA_LAZY_MYSQLI', true);
require_once dirname(__DIR__) . '/botapi.php';
$secret = FaoximaWebhookAuth::secret((string) $APIKEY);
if (!preg_match('/^[A-Za-z0-9_-]{1,256}$/D', $secret)) {
    fwrite(STDERR, "[railway] Invalid TELEGRAM_WEBHOOK_SECRET format.\n");
    exit(1);
}
$response = telegram('setWebhook', [
    'url' => 'https://' . $domainhosts . '/index.php',
    'secret_token' => $secret,
    'allowed_updates' => json_encode(['message', 'edited_message', 'channel_post', 'edited_channel_post', 'callback_query', 'inline_query', 'my_chat_member', 'chat_member', 'chat_join_request', 'pre_checkout_query']),
]);
if (!is_array($response) || empty($response['ok'])) {
    $code = is_array($response) ? (int) ($response['error_code'] ?? 0) : 0;
    fwrite(STDERR, "[railway] Telegram rejected webhook registration (error {$code}). Check token, HTTPS domain and network.\n");
    exit(1);
}
echo "[railway] Telegram accepted webhook registration.\n";
