# Validation — 2026-09-25

- All 269 application PHP files parsed successfully with both php-parser configured for PHP 8.2 and PHP 8.2.33's TOKEN_PARSE parser. Vendor files were preserved from upstream.
- Environment-to-config rewriting tested with quotes, backslashes, dollar signs and newlines, including a second rewrite of previously generated values.
- Six isolated PHP execution tests cover successful card delivery, Telegram rejection, null response, exception, QR success and QR rejection. Failed images preserve the text delivery path and its buttons.
- Mini App card method tested with Telegram success and failure responses; temporary image cleanup verified.
- Shell syntax check passed for railway-entrypoint.sh; railway.json parsed successfully.
- Source comparison found no removed upstream files. The change list is documented in RAILWAY-CHANGES.md.

Limits: PHP tests use an isolated WebAssembly PHP 8.2.33 runtime with stubbed Telegram/panel responses. Docker/Apache image build, real MySQL migration, live Telegram delivery, and live Railway deploy were not run in this environment. A real service test after deployment is still required. The delivery fix addresses a confirmed unchecked-response defect; without logs from the failing live order it does not establish the sole cause of the previously reported delivery failure.
