# Railway adaptation — 2026-09-25

Upstream: Mmd-Amir/Faoxima @ 8dddaa09fc76fb22699f8f1e4b7a4c2bb0619012 (1.1.1).
Reference: x4gpanell/Faoxima @ 995a861659db66b2ca50e8d01a6a738b2c6f359b.

- Retain current upstream application and bundled dependencies.
- Dockerfile: PHP 8.2 Apache, required extensions, MySQL dump client, cron, one prefork MPM.
- Read Railway DB references and bot credentials at startup; safely quote generated PHP values, including quotes, backslashes and dollar signs.
- Serve from domain root, configure PORT, support forwarded HTTPS and directory URLs for panel/app.
- Wait for MySQL, run upstream table.php migration, check Telegram webhook registration, then start Apache.
- Run cron as www-data once per minute. Forward PHP web errors and cron stderr to Railway logs; cap Apache worker count.
- Reject false success in bot info-card, QR fallback and Mini App photo delivery paths; preserve text delivery fallback.
- Preserve the custom images.jpeg from the user's previous Railway package.

The old reference fork embeds mysql.railway.internal in config.php and installer/index.php. This package reads the host through Variables so service renaming is supported. Installation is automatic rather than through the web installer.

No live database, GitHub repository or Railway deployment has been modified while preparing this package.
