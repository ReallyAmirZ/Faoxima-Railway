<?php

$rootDirectory   = dirname(__DIR__) . '/';
$configDirectory = $rootDirectory . 'config.php';

if (!is_file($configDirectory)) {
    fwrite(STDERR, "[php-entrypoint-configure] config.php not found at {$configDirectory}\n");
    exit(1);
}

function env_or($name, $default = '')
{
    $value = getenv($name);
    return ($value === false || $value === '') ? $default : $value;
}

function fetch_bot_username($token)
{
    if ($token === '') {
        return '';
    }
    $ctx = stream_context_create(['http' => ['timeout' => 10]]);
    $raw = @file_get_contents("https://api.telegram.org/bot{$token}/getMe", false, $ctx);
    if ($raw === false) {
        return '';
    }
    $data = json_decode($raw, true);
    if (is_array($data) && !empty($data['ok']) && isset($data['result']['username'])) {
        return (string) $data['result']['username'];
    }
    return '';
}

$botToken = env_or('TELEGRAM_BOT_TOKEN');
$botUsername = ltrim(env_or('TELEGRAM_BOT_USERNAME'), '@');
if ($botUsername === '') {
    $botUsername = fetch_bot_username($botToken);
}
$urlPath = trim(env_or('URL_PATH'), '/');
$domain = rtrim(preg_replace('#^https?://#i', '', trim(env_or('DOMAIN', env_or('RAILWAY_PUBLIC_DOMAIN')))), '/');
if ($urlPath !== '') {
    $domain .= '/' . $urlPath;
}

$replacements = [
    '{database_name}' => env_or('DB_NAME'),
    '{username_db}'   => env_or('DB_USER'),
    '{password_db}'   => env_or('DB_PASS'),
    '{db_host}'       => env_or('DB_HOST', 'db'),
    '{API_KEY}'       => $botToken,
    '{admin_number}'  => env_or('TELEGRAM_ADMIN_ID'),
    '{domain_name}'   => $domain,
    '{username_bot}'  => $botUsername,
];

function updateConfigValues($configContents, array $placeholderValues, &$replacementCount = 0)
{
    $replacementCount = 0;
    $variableMap = [
        'dbname'      => $placeholderValues['{database_name}'] ?? '',
        'usernamedb'  => $placeholderValues['{username_db}'] ?? '',
        'passworddb'  => $placeholderValues['{password_db}'] ?? '',
        'dbhost'      => $placeholderValues['{db_host}'] ?? '',
        'APIKEY'      => $placeholderValues['{API_KEY}'] ?? '',
        'adminnumber' => $placeholderValues['{admin_number}'] ?? '',
        'domainhosts' => $placeholderValues['{domain_name}'] ?? '',
        'usernamebot' => $placeholderValues['{username_bot}'] ?? '',
    ];
    $updatedConfig = $configContents;
    foreach ($variableMap as $variable => $value) {
        $pattern = '/(\$' . preg_quote($variable, '/') . '\s*=\s*)(?:\'(?:\\\\.|[^\'\\\\])*\'|"(?:\\\\.|[^"\\\\])*")(\s*;)/s';
        $updatedConfig = preg_replace_callback(
            $pattern,
            function ($matches) use ($value, &$replacementCount) {
                $replacementCount++;
                return $matches[1] . var_export((string) $value, true) . $matches[2];
            },
            $updatedConfig,
            1
        );
    }
    return $updatedConfig;
}

$rawConfigData     = file_get_contents($configDirectory);
$replacementCount  = 0;
$newConfigData     = updateConfigValues($rawConfigData, $replacements, $replacementCount);

if ($replacementCount !== 8 || file_put_contents($configDirectory, $newConfigData, LOCK_EX) === false) {
    fwrite(STDERR, "[php-entrypoint-configure] failed to rewrite config.php (replacementCount={$replacementCount})\n");
    exit(1);
}

echo "[php-entrypoint-configure] config.php templated from environment ({$replacementCount} fields).\n";
