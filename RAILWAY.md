# اجرای Faoxima v1.0.5 روی Railway

این بسته از آخرین نسخه سورس اصلی Faoxima ساخته شده و لایه اجرای Railway را به آن اضافه می‌کند. در هر Deploy، تنظیمات از Variables ساخته می‌شوند، ساختار دیتابیس بدون حذف اطلاعات قبلی به‌روزرسانی می‌شود، وب‌هوک تلگرام تنظیم می‌شود و Cron داخلی هر دقیقه اجرا می‌شود.

در Railway به مسیر `/installer` نروید؛ نصب و به‌روزرسانی به‌صورت خودکار انجام می‌شود.

## 1. ساخت سرویس‌ها

1. در Railway یک Project بسازید.
2. با `+ New` یک سرویس **MySQL** اضافه کنید.
3. مخزن GitHub حاوی این فایل‌ها را به‌عنوان سرویس دوم اضافه کنید.
4. در سرویس ربات، از بخش **Settings > Networking** گزینه **Generate Domain** را بزنید.

## 2. Variables سرویس ربات

در سرویس ربات، این متغیرها را اضافه کنید. اگر نام سرویس دیتابیس شما `MySQL` نیست، نام داخل Referenceها را متناسب با آن تغییر دهید.

```text
DB_HOST=${{MySQL.MYSQLHOST}}
DB_NAME=${{MySQL.MYSQLDATABASE}}
DB_USER=${{MySQL.MYSQLUSER}}
DB_PASS=${{MySQL.MYSQLPASSWORD}}
TELEGRAM_BOT_TOKEN=توکن_ربات
TELEGRAM_ADMIN_ID=آیدی_عددی_ادمین
FAOXIMA_AUTO_MIGRATE=1
```

`DOMAIN` و `PORT` را دستی نسازید. Railway آن‌ها را در زمان اجرا می‌سازد و برنامه از `RAILWAY_PUBLIC_DOMAIN` استفاده می‌کند.

`TELEGRAM_WEBHOOK_SECRET` اختیاری است. اگر آن را وارد نکنید، برنامه مقدار امن و ثابتی را از توکن ربات تولید می‌کند. در صورت تعریف دستی، فقط از حروف انگلیسی، اعداد، `_` و `-` استفاده کنید.

## 3. Deploy و آزمایش

بعد از ثبت Variables، Deploy را اجرا کنید. پایان صحیح Deploy Log باید شامل پیام زیر باشد:

```text
[railway] Faoxima is ready on port ...
```

سپس این آدرس را باز کنید:

```text
https://YOUR-DOMAIN/health.php
```

پاسخ صحیح:

```json
{"ok":true,"service":"faoxima"}
```

حالا در تلگرام `/start` را ارسال کنید. پنل مدیریت را با اسلش انتهایی باز کنید تا Railway پورت داخلی را وارد Redirect نکند:

```text
https://YOUR-DOMAIN/panel/
```

مینی‌اپ نیز از این آدرس در دسترس است:

```text
https://YOUR-DOMAIN/app/
```

## انتقال اطلاعات نسخه قبلی

1. از دیتابیس قبلی یک فایل SQL Export بگیرید.
2. قبل از اولین Deploy، فایل SQL را داخل MySQL جدید Import کنید.
3. Variables بالا را به دیتابیس جدید متصل کنید و Deploy را انجام دهید.
4. بعد از مشاهده پیام Ready و آزمایش `/start`، سرویس قبلی را متوقف کنید تا دو Webhook هم‌زمان فعال نباشند.

اجرای خودکار `table.php` اطلاعات قبلی را پاک نمی‌کند؛ فقط جدول‌ها و ستون‌های لازم نسخه جدید را ایجاد یا به‌روزرسانی می‌کند. با این حال همیشه فایل SQL بکاپ را تا پایان آزمایش نگه دارید.

## به‌روزرسانی از فورک قبلی

اگر سرویس Railway شما همین حالا به مخزن قبلی وصل است، فایل‌های این بسته را روی همان Repository جایگزین و Commit کنید. MySQL را حذف نکنید. Railway به‌صورت خودکار نسخه جدید را Build می‌کند و Migration دیتابیس در زمان شروع اجرا می‌شود.

برای جایگزینی تمیز، دو مورد قدیمی زیر را هم از Repository قبلی حذف کنید؛ در سورس اصلی جدید دیگر وجود ندارند:

```text
discounts.php
app/assets/v1.0.0/
```

## رفع خطاهای رایج

- `AH00534: More than one MPM loaded`: باید همین `Dockerfile` و `docker/railway-entrypoint.sh` در ریشه مخزن وجود داشته باشند.
- `Missing required variables`: یکی از Variables بخش 2 خالی است یا Reference دیتابیس اشتباه نوشته شده است.
- `Wrong response from the webhook: 500`: Deploy Log را بررسی کنید؛ معمولاً اتصال دیتابیس یا متغیرهای ربات اشتباه است.
- پنل بدون اسلش باز نمی‌شود: از آدرس `/panel/` استفاده کنید.
- تغییرات Variables اعمال نشده: از منوی سرویس یک Redeploy کامل انجام دهید.

## فایل‌های مخصوص Railway

- `Dockerfile`
- `railway.json`
- `health.php`
- `docker/railway-entrypoint.sh`
- `docker/faoxima.cron`
- `docker/php-entrypoint-configure.php`

این فایل‌ها را هنگام دریافت آپدیت بعدی سورس اصلی حفظ کنید.
