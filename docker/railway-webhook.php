<?php
// Fail startup if Telegram did not actually accept the webhook.
function railwayRegisterWebhook(array $parameters, callable $request, callable $wait, callable $log): bool
{
    $waited = 0;
    for ($attempt = 1; $attempt <= 6; $attempt++) {
        $response = $request('setWebhook', $parameters);
        if (is_array($response) && !empty($response['ok'])) {
            return true;
        }
        $code = is_array($response) ? (int) ($response['error_code'] ?? 0) : 0;
        if ($code !== 429) {
            $log("[railway] Telegram rejected webhook registration (error {$code}). Check token, HTTPS domain and network.");
            return false;
        }
        $retryAfter = max(1, (int) ($response['parameters']['retry_after'] ?? 2));
        // Never retry sooner than Telegram requested. Bound startup waiting.
        if ($attempt === 6 || $retryAfter >= 120 - $waited) {
            $log("[railway] Webhook rate limit persists. Wait at least {$retryAfter} seconds before restarting; check for another deployment using this bot token.");
            return false;
        }
        $delay = $retryAfter + 1;
        $log("[railway] Telegram rate limited webhook registration; waiting {$delay} seconds (attempt {$attempt}/6).");
        $wait($delay);
        $waited += $delay;
    }
    return false;
}

define('FAOXIMA_LAZY_MYSQLI', true);
require_once dirname(__DIR__) . '/botapi.php';
$secret = FaoximaWebhookAuth::secret((string) $APIKEY);
if (!preg_match('/^[A-Za-z0-9_-]{1,256}$/D', $secret)) {
    fwrite(STDERR, "[railway] Invalid TELEGRAM_WEBHOOK_SECRET format.\n");
    exit(1);
}
$parameters = [
    'url' => 'https://' . $domainhosts . '/index.php',
    'secret_token' => $secret,
    'allowed_updates' => json_encode(['message', 'edited_message', 'channel_post', 'edited_channel_post', 'callback_query', 'inline_query', 'my_chat_member', 'chat_member', 'chat_join_request', 'pre_checkout_query']),
];
if (!railwayRegisterWebhook($parameters, 'telegram', 'sleep', static function ($message) {
    fwrite(STDERR, $message . "\n");
})) {
    exit(1);
}
echo "[railway] Telegram accepted webhook registration.\n";
