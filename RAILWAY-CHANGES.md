# Railway release — 2026-09-28

Revision r2: prevents duplicate migration/webhook registration on Railway; retries HTTP 429 using Telegram retry_after. See RAILWAY-HOTFIX-429.md.

Upstream: Mmd-Amir/Faoxima v1.1.5, commit 68eccf1981f1bd7f21c8000ad5fff198a0f6c175.
The complete upstream snapshot is included, not merely the previous package with its version changed.

Railway modifications are merged from the 2026-09-25 r2 package:

- PHP 8.2 Apache Dockerfile, single prefork MPM, dynamic PORT, Railway health endpoint.
- Environment-based configuration with safe PHP quoting, DB readiness checks, upstream migrations and checked Telegram webhook registration.
- Proxy-aware app/panel redirects and PHP stderr logging.
- Cron with explicit runuser/PHP paths; absent optional cron scripts skipped.
- Marzban/Pasarguard create accepts HTTP 200/201 only with a valid username; XUI checks successful response. Preserves upstream new rename/retry/error-detail logic.
- Subscription fetch failure preserves original links instead of passing an array to explode.
- Failed Telegram photo sends do not falsely count as successful delivery; existing text/button fallback remains available.
- Mini App: six-second delay keeps the loading skeleton, thirty-second delay offers retry without claiming a module failure; genuine startup errors remain visible.
- Preserves the user's existing Nexus images.jpeg from the previous Railway package.

Credentials, database contents and production uploads are not included. Config values come from Railway Variables. Database migrations are the upstream table.php and can change data/schema; back up first. Do not run the VPS in-bot updater on this deployment.
