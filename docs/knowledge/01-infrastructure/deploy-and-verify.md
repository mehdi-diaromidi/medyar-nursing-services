# Deploy و راستی‌آزمایی

## `deploy.ps1`

```powershell
.\deploy.ps1 wp-content/plugins/medyar-nursing-services/public/assets/css/public.css other/file.php
```

- `.ftp.env` کنار اسکریپت را می‌خواند (`KEY=VALUE`، کامنت `#`). الزامی: `FTP_HOST`, `FTP_USER`, `FTP_PASS`؛ اختیاری: `FTP_ROOT`, `FTP_PORT` (پیش‌فرض 21).
- برای هر مسیر: `\`→`/`، بررسی وجود فایل محلی، ساخت پوشه‌های ریموت (`MakeDirectory`؛ خطای «وجود دارد» نادیده)، آپلود باینری Passive با `FtpWebRequest` (`Proxy = $null`)، چاپ `OK <path>`.
- ⚠️ **آپلود مستقیم روی فایل مقصد** است؛ اگر وسط کار خطا بدهد (مثل 552) فایل ریموت خالی/ناقص می‌ماند. **پیشنهاد آینده:** آپلود به نام موقت + Rename.
- `deploy.sh`: اگر `lftp` باشد `mkdir -p` + `put -O` (SSL خاموش)؛ وگرنه fallback به `pwsh -File deploy.ps1` یا `powershell.exe`.

## محیط ویندوز

- شل PowerShell → `;` به‌جای `&&`.
- `php` روی PATH نیست → `php -l` محلی ممکن نیست؛ بعد از آپلود صفحه را در مرورگر چک کن.
- مسیرهای فارسی در PowerShell با `-LiteralPath` مشکلی ندارند.

## حلقهٔ راستی‌آزمایی (skill «ftp-auto»)

1. Lint (`php -l` اگر موجود).
2. آپلود فقط فایل‌های تغییرکرده. **هرگز** `wp-config.php`، `.env`، `.ftp.env`، `.git`، `node_modules`، بکاپ‌ها، لاگ‌ها.
3. در مرورگر: hard reload با `mns_nocache=1&v=…`؛ تأیید UI، نبود خطای PHP/صفحهٔ سفید، نبود خطای کنسول و 404/500؛ فرم/AJAX را واقعاً اجرا کن.
4. شکست → رفع علت ریشه‌ای → آپلود → تأیید؛ بعد از ۳ شکست گزارش بده.
5. شکست آپلود → یک بار تلاش مجدد → گزارش.
6. هرگز «تمام شد» بدون تأیید موفق. در حلقه ساکت باش، نتیجه را یک خطی گزارش کن.
7. بعد از هر آپلود، اندازهٔ فایل ریموت را با محلی مقایسه کن (بعد از رویداد ۵۵۲).

نکات عملی:
- اسکرین‌شات CDP گاهی هنگ می‌کند → تأیید با `Runtime.evaluate` (`getComputedStyle` / `getBoundingClientRect` / وضعیت DOM).
- موبایل: `Emulation.setDeviceMetricsOverride` (390×844) و در پایان `clearDeviceMetricsOverride`.
- هر تغییر CSS/JS → `MNS_VERSION` را بالا ببر (کش WP Rocket/LiteSpeed).
