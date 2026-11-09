# محیط، آدرس‌ها و گیت (فقط آنچه به MNS مربوط است)

## آدرس‌ها

| مورد | مقدار |
|---|---|
| سایت توسعه (محل کار MNS) | `https://develop.medyarco.com` |
| سایت اصلی (فعلاً MNS آنجا نیست) | `https://medyarco.com` |
| ورود ادمین (wps-hide-login) | `/medlogin/` |
| کاتالوگ خدمات | `/nursing-services/` (تک‌خدمت: `?mns_service=<slug>`) |
| پنل بیمار | `/nursing-patient-panel/?mns_panel=patient&mns_page=<slug>` |
| پنل پرستار | `/nursing-nurse-panel/?mns_panel=nurse&mns_page=<slug>` |
| عضویت پرستار | `/nursing-nurse-signup/` |
| هاب ادمین | `/wp-admin/admin.php?page=mns-dashboard` (و `mns-monitor`، `mns-assign`، …) |
| شکستن کش هنگام تست | `?mns_nocache=1` و/یا `&v=<نسخه>` + بالا بردن `MNS_VERSION` (WP Rocket و LiteSpeed فعال‌اند) |

## سرور

- میزبانی اشتراکی، FTP پورت 21، کاربر FTP مختص `develop.medyarco.com`. اطلاعات اتصال **فقط** در `.ftp.env` / `.vscode/sftp.json` (gitignored) در ریشهٔ ریپو؛ **هرگز چاپ/لاگ/کامیت/اشتراک نشود.**
- پیشوند جداول دیتابیس: **`Dj3i_`** (برای جداول MNS از `$wpdb->prefix` استفاده کن، هاردکد نکن).
- کش: WP Rocket + LiteSpeed.
- PHP 8.0+، WP 5.8+ (الزام MNS). `php` روی ماشین محلی نیست → lint محلی ممکن نیست.

### رویداد پر شدن دیسک FTP (۹ نوامبر ۲۰۲۶) — مهم برای deploy

- خطای `552 Exceeded storage allocation` وسط آپلود `public.css` → فایل ریموت ۰ بایت شد.
- با اجازهٔ کاربر ۴ zip حجیم در `wp-content` (~۹۳۵MB) حذف و فایل‌ها دوباره آپلود شدند؛ اندازهٔ ریموت = محلی.
- **اگر دوباره 552 دیدی:** اندازهٔ فایل ریموت را بلافاصله چک کن. (پیشنهاد: آپلود به نام موقت + Rename.)

## گیت

- ریشهٔ `c:\Projects\medyar-develop` ریپوی git **نیست**.
- پلاگین MNS ریپوی جدا دارد: `https://github.com/mehdi-diaromidi/medyar-nursing-services` (شاخه‌ها: `main`، `feature/front-static`). کامیت‌ها با متغیرهای محیطی `GIT_AUTHOR_*`/`GIT_COMMITTER_*` (بدون تغییر `git config`).
- این پوشهٔ `docs/knowledge/` داخل همان ریپو است؛ هیچ داده محرمانه‌ای نباید واردش شود.

## ساختار پلاگین

```
medyar-nursing-services/
├─ PLAN.md, medyar-nursing-services.php, uninstall.php, index.php, .gitignore
├─ includes/  admin/  public/  assets/  languages/
└─ docs/
    ├─ PHASE-0-SCAFFOLD.md, PHASE-3-FRONT-STATIC.md, PHASE-C-UX-SYSTEM.md, PHASE-D-E-UX-PAGES.md
    ├─ UI-UX.md, ui-audit.md, ux-audit.md, ux-reference-map.md, ui-checklist-my-orders.md
    └─ knowledge/   ← همین پوشه
```

طراحی‌های PNG و متن بریف UI در ریشهٔ ریپو: `desgin/png/{ثبت سفارش, سفارش های من, فاکتورها و پرداخت ها}` و `desgin/ui doc/*.txt` (املای `desgin` عمدی/ثابت). بریف اصلی کارفرما: `docs/گزارش بررسی و برنامه زمانبندی پروژه سامانه خدمات پرستاری.md` (در ریشهٔ ریپو، نه در پلاگین).
