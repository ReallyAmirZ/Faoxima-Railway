# Verification — 2026-09-28

Source: v1.1.5 / 68eccf1981f1bd7f21c8000ad5fff198a0f6c175.

Passed locally:

- All 276 non-vendor application PHP files parsed with php-parser and PHP 8.2.33 TOKEN_PARSE in an isolated WASM runtime.
- Environment rewrite: eight fields, escaping quotes/backslashes/dollars/newlines, repeat-startup idempotence.
- Actual ManagePanel class against 21 simulated HTTP cases: 200/201 success, 401/409/202 rejection, invalid body rejection, subscription fallback, XUI success validation. Exactly one create request in each case. The original v1.1.1 source reproduces the erroneous 201 rejection.
- Six simulated delivery cases: photo accepted/rejected/invalid/exception, QR success/failure, preserved buttons.
- Mini App information-card success/rejection and temporary-file cleanup.
- Six simulated startup scenarios: fast boot, delayed boot, unrelated image failure, script failure, rejected import, long wait and recovery.
- Entrypoint shell syntax.
- All 29 versioned Mini App JavaScript files passed syntax checks; 97 relative static import targets exist.
- The actual upstream config.php template still rewrites all eight environment-backed fields and remains valid PHP.

Not tested here: real Docker build, actual MySQL migrations, live panel/Telegram APIs, real Telegram WebView, production load, Railway deployment. Mock-based tests are not an end-to-end delivery guarantee. health.php only indicates that the HTTP/PHP process responds.

After deployment: verify webhook-ready logs, /start, panel login, Mini App, one test purchase/delivery and cron activity before accepting normal purchases. Retain an SQL backup and the previous code version.
