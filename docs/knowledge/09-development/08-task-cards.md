# ۰۸ — کارت‌های وظیفه (آمادهٔ تحویل به Cursor) — برش اول (M0–M3)

## ۰. هدر ثابت که در ابتدای هر پیام به Cursor می‌چسبد
```
تو سازندهٔ بک‌اند پلاگین medyar-nursing-services هستی (PHP 8.0+, WordPress 5.8+).
قبل از هر کار بخوان (به ترتیب): docs/knowledge/PROJECT-LOGIC.md و docs/knowledge/09-development/{README,01,02,03,04,05,06}.md
و فقط کارت وظیفهٔ زیر را انجام بده؛ چیزی خارج از «فایل‌های مجاز» را تغییر نده.
قوانین سخت: (۱) هیچ فایلی بیرون از wp-content/plugins/medyar-nursing-services/ ویرایش نشود (قالب، ARP، MTW، MSQ، Persian WooCommerce ممنوع).
(۲) PHP روی ماشین نیست؛ کدت باید با دقت نحوی نوشته شود و هیچ ویژگی PHP > 8.0 (enum، readonly، never، first-class callable) نیاید.
(۳) هر query با $wpdb->prepare؛ هر ورودی sanitize؛ هر خروجی escape؛ هر route/handler با nonce + capability/ownership؛ هر فایل با `if ( ! defined( 'ABSPATH' ) ) { exit; }`.
(۴) هر قاعده با شناسهٔ R-xxx در کامنت/commit ارجاع شود؛ قاعدهٔ جدید یا مبهم = بپرس، حدس نزن.
(۵) تنظیم‌پذیر بساز (06)، نه هاردکد؛ JSON ساخت‌نیافته برای هرچه [باز] است.
(۶) خروجی کار: فهرست فایل‌های تغییرکرده + فرضیات + هر پرسش باز + نتیجهٔ اجرای self-test مربوط (AC). commit نکن مگر ناظر بگوید.
(۷) دکمه/UI موجود را خراب نکن؛ viewها را فقط به داده وصل کن و شکل داده را طبق 04 نگه دار.
```

## ۱. قالب کارت
`T-m.n عنوان` · **هدف** · **بخوان** · **فایل‌های مجاز** · **تحویل** · **قواعد/AC** · **ممنوع** · **چک ناظر**.

---
## M0 — زیرساخت و ایمنی

### T-0.1 Safe boot و kill-switch
- **هدف:** بارگذاری ماژول‌های بک‌اند بدون خطر سفید شدن سایت.
- **بخوان:** `07-roadmap.md §M0`، `05 §۸`.
- **مجاز:** `medyar-nursing-services.php`, `includes/class-mns-plugin.php`, `includes/class-mns-autoloader.php`, `includes/class-mns-backend.php`(جدید).
- **تحویل:** `MNS_Backend::boot()` که لیست ماژول‌ها را در `try/catch(\Throwable)` می‌خواند؛ ثبت خطا در لاگ (`mns_log`) + option `mns_backend_error` + اعلان ادمین؛ ثابت `MNS_BACKEND_DISABLED`؛ autoloader با پشتیبانی پوشه‌های `includes/{db,domain,services,rest,admin}` (نقشهٔ دستی + الگوی `MNS_*`).
- **AC:** AC-M0-1 (خطای عمدی در یک ماژول ⇒ سایت و پنل استاتیک سالم + اعلان).
- **ممنوع:** `eval`, سرکوب خطا با `@`, تغییر رفتار صفحات استاتیک.

### T-0.2 لایهٔ DB و migration
- **هدف:** `MNS_DB` + `MNS_Migrations` + جدول‌های برش اول.
- **بخوان:** `02` کامل.
- **مجاز:** `includes/db/*`, `includes/class-mns-activator.php`, `medyar-nursing-services.php` (فقط `MNS_DB_VERSION`).
- **تحویل:** `MNS_DB::table($name)`, `MNS_DB::tx(callable)` (START/COMMIT/ROLLBACK، تودرتو=savepoint یا خطا)، `lock_row()`؛ migration نسخه ۱ با `dbDelta` (دقیقاً ساختار `02`: نوع‌ها، ایندکس‌ها، UNIQUEها)؛ اجرا روی `plugins_loaded` وقتی `mns_db_version` کمتر است؛ idempotent.
- **AC:** AC-M0-2 (اجرای دوباره بدون خطا و بدون تغییر)، AC-M0-3 (ریست و ساخت دوباره).
- **ممنوع:** DDL روی جدول‌های غیر-`mns_`، `DROP` خارج از ابزار ریست.

### T-0.3 تنظیمات
- **مجاز:** `includes/class-mns-settings.php`, `admin/class-mns-admin-settings.php`.
- **تحویل:** `MNS_Settings::definitions()/get()/set()` با رجیستری `06`؛ صفحهٔ ادمین «تنظیمات» (nonce+cap)؛ اعتبارسنجی نوع/محدوده.
- **AC:** AC-M0-4 (مقدار نامعتبر ⇒ پیش‌فرض؛ ذخیره/خواندن).

### T-0.4 خطا، لاگ، REST پایه
- **مجاز:** `includes/class-mns-error.php`, `includes/class-mns-log.php`, `includes/rest/class-mns-rest.php`.
- **تحویل:** `MNS_Error` (code,message_fa,http,field,data)؛ `MNS_Log` (جدول سبک یا option چرخشی ۲۰۰ خط)؛ `MNS_REST` (envelope، `?_envelope=1`، nonce، permission helper‌ها، rate limit با transient، `Idempotency-Key`).
- **AC:** AC-M0-5 (پاسخ‌ها طبق `04 §۱`).

### T-0.5 ماشین وضعیت و سرویس‌های خالص
- **مجاز:** `includes/domain/class-mns-order-machine.php`, `class-mns-eligibility.php`, `class-mns-availability.php`, `class-mns-nurse-schedule.php`.
- **تحویل:** `transition()` طبق جدول `03 §۱` (فقط انتقال‌های برش اول: create/assign/reassign/cancel/cancel_admin)، `order_log`; `MNS_Eligibility::nurses_for()` و `MNS_Availability::check()` دقیقاً طبق R-ELG/R-A13؛ همه **بدون وابستگی به UI** (ورودی/خروجی آرایه).
- **AC:** AC-M0-6 (انتقال غیرمجاز ⇒ `ERR_BAD_STATE`)، AC-ELG-*.

### T-0.6 Self-test و ریست
- **مجاز:** `admin/class-mns-admin-selftest.php`, `admin/class-mns-admin-reset.php`, `includes/class-mns-selftest-scenarios.php`.
- **تحویل:** صفحهٔ ادمین (cap `manage_options`) که سناریوهای `10` را با دادهٔ موقت علامت‌دار (`mns_selftest=1` در ستون/نام) روی DB اجرا و پاک می‌کند؛ خروجی جدول PASS/FAIL؛ ابزار ریست با عبارت «RESET MNS».
- **AC:** AC-M0-7.

### T-0.7 فایل خصوصی
- **مجاز:** `includes/services/class-mns-files.php`, REST `GET /files/{id}`.
- **تحویل:** R-PRV-4، `05 §۷`؛ ذخیره/اعتبارسنجی/حذف EXIF/stream.
- **AC:** AC-M0-8 (فایل از URL مستقیم 403؛ از endpoint فقط مجاز).

---
## M1 — عضویت پرستار

### T-1.1 دامنه: درخواست و تأیید
- **مجاز:** `includes/services/class-mns-nurse-applications.php`, `class-mns-contract.php`, `class-mns-nurse-access.php`.
- **تحویل:** `apply()`, `approve()`, `reject()`, `suspend()`, `reinstate()` با R-NRS-1..5؛ قرارداد نسخه‌دار با IP/زمان؛ هوک‌ها `mns_nurse_approved/rejected`؛ `MNS_Nurse_Access::gate()` برای صفحات.
- **AC:** AC-M1-1..4.

### T-1.2 صفحهٔ فرم عضویت (UI)
- **بخوان:** `05 §۳`، فایل‌های مرجع ARP (فقط خواندن).
- **مجاز:** `public/views/nurse/signup-stub.php` (جایگزین کامل)، `public/templates/mns-nurse-signup.php`, `public/class-mns-nurse-signup.php`(جدید)، `assets/css/public.css` (بلوک جدید `.mns-nsu*`)، `assets/js/public.js` (init جدید).
- **تحویل:** فرم عین «طرح همکاران» (کارت فرم + کارت چطور کار می‌کند)، ۴ حالت، `admin_post_mns_nurse_apply` (API-NRS-1)، اعتبارسنجی کد ملی، آپلودها، gate لاگین و سوییچ ورود پرستار روی `drplus/auth/form/start`.
- **AC:** AC-M1-5..7؛ ظاهر: بررسی ناظر مقابل صفحهٔ `/partner/`.

### T-1.3 ادمین: درخواست‌ها
- **مجاز:** `admin/class-mns-admin-nurses.php`, `admin/list-tables/class-mns-nurses-table.php`, `admin/views/nurses.php`.
- **تحویل:** `WP_List_Table` (وضعیت، جستجو)، جزئیات (مدارک از API-FIL-1)، تأیید/رد/تعلیق (`admin_post_*` + nonce + `mns_manage_nurses`)، تعیین خدمات مجاز، تأیید عکس، نسخهٔ قرارداد.
- **AC:** AC-M1-8.

### T-1.4 پروفایل پرستار واقعی
- **مجاز:** `includes/rest/class-mns-rest-nurse.php`, `public/views/nurse/page-profile.php` (فقط وصل داده)، `assets/js/public.js` (`initNurseProfile`).
- **تحویل:** API-NRS-2/3 + عکس؛ شمارهٔ موبایل و جنسیت ویرایش‌ناپذیر؛ ذخیره با همان UX فعلی.
- **AC:** AC-M1-9.

### T-1.5 پیامک #1–#3
- **مجاز:** `includes/services/class-mns-sms.php`.
- **تحویل:** واسط MSQ طبق `05 §۴`؛ رویدادها و dedupe؛ پترن خالی ⇒ لاگ و بدون ارسال.
- **AC:** AC-M1-10.

---
## M2 — برنامه و مناطق

### T-2.1 دامنهٔ برنامه و مناطق
- **مجاز:** `includes/services/class-mns-nurse-schedule.php`, `class-mns-nurse-regions.php`, `class-mns-locations.php`.
- **تحویل:** `effective()`، `save_days()` (R-SCH-2 همه‌یا‌هیچ + قفل ردیف)، `save_regions()` (R-REG-*، هشدار سفارش‌ها)، `MNS_Locations` (خواندن `iran-locations.json` + `S-REG-DISTRICTS`)، `MNS_Nurse_Status::compute()`.
- **AC:** AC-M2-1..6.

### T-2.2 REST و اتصال صفحهٔ «برنامه و دسترسی»
- **مجاز:** `includes/rest/class-mns-rest-nurse.php`, `public/views/nurse/page-availability.php`, `assets/js/public.js` (`initNurseSchedule` — فقط جایگزینی منبع داده و ذخیره)، `public/static-fixtures/nurse/availability.php` (حذف از مسیر زنده).
- **تحویل:** API-NRS-4..7؛ view فقط config را از سرور می‌گیرد؛ ذخیره‌ها واقعی؛ پیام‌های خطا (`ERR_SLOT_HAS_ORDER`) در UI؛ حالت‌های `?mns_case` فقط در dev.
- **AC:** AC-M2-7 + AC-UI-EMPTY.

---
## M3 — ثبت سفارش، A-13، تخصیص

### T-3.1 کاتالوگ ۲خدمتی
- **مجاز:** `includes/services/class-mns-services.php`, seed در migration، `admin/class-mns-admin-services.php`.
- **تحویل:** seed `serum`,`injection` از fixture (`config` JSON)؛ ویرایش حداقلی؛ `MNS_Services::get()/list_active()`.
- **AC:** AC-M3-1.

### T-3.2 بیماران
- **مجاز:** `includes/services/class-mns-patients.php`, REST `patients`.
- **تحویل:** R-PAT-1 (ساخت «خودم»)، CRUD، soft delete، مالکیت.
- **AC:** AC-M3-2.

### T-3.3 A-13 API
- **مجاز:** `includes/rest/class-mns-rest-patient.php`.
- **تحویل:** API-A13-1 با R-A13-*؛ نرخ‌محدودیت؛ بدون افشای تعداد/نام.
- **AC:** AC-A13-1..5.

### T-3.4 ثبت سفارش
- **مجاز:** `includes/services/class-mns-orders.php`, REST `POST /orders`, `public/views/patient/page-new-order.php` (وصل داده)، `assets/js/public.js` (`initOrderForm` — ارسال واقعی)، آپلود نسخه.
- **تحویل:** API-PAT-1 (bootstrap)، API-ORD-1 (تراکنش: A-13 مجدد → درج → لاگ → (auto) تخصیص)، کد ۵رقمی، ایدمپوتنسی، اعتبارسنجی `essentials` از `service.config`، شمارهٔ تماس متفاوت ⇒ `ERR_OTP_REQUIRED`.
- **AC:** AC-M3-3..8.

### T-3.5 تخصیص و ادمین سفارش
- **مجاز:** `includes/services/class-mns-assignment.php`, `admin/class-mns-admin-orders.php`, `admin/list-tables/class-mns-orders-table.php`.
- **تحویل:** R-ASG-1..6؛ اتمیک R-ASG-3؛ لیست «بدون پرستار» + ۵ پیشنهاد با دلیل؛ تغییر پرستار؛ لغو ادمین؛ ثبت سفارش تلفنی (ساخت حساب برای موبایل جدید)؛ رصد مرکزی.
- **AC:** AC-ASG-1..6، AC-M3-9.

### T-3.6 صفحات بیمار (سفارش‌ها و جزئیات)
- **مجاز:** `class-mns-patient-orders.php` (موجود؛ منبع داده تغییر)، `public/views/patient/page-orders.php`, `page-order-view.php`, `page-dashboard.php`.
- **تحویل:** API-ORD-2/3/4؛ نگاشت وضعیت به زبان بیمار سمت سرور؛ لغو و ویرایش محدود.
- **AC:** AC-M3-10..12.

### T-3.7 صفحات پرستار (خواندنی) و اعلان‌ها
- **مجاز:** `class-mns-nurse-orders.php`, `public/views/nurse/page-orders.php`, `page-order-view.php`, `page-dashboard.php` (بخش‌های مربوط)، `includes/services/class-mns-notifications.php`.
- **تحویل:** API-NOR-1/2 با whitelist R-PRV-1/2؛ اعلان‌های داشبورد (لغو، تغییر پرستار، ویرایش بیمار)؛ cron پاک‌سازی.
- **AC:** AC-M3-13..14.

### T-3.8 پیامک #4،#5،#11،#12
- **تحویل:** رویدادها از هوک `mns_order_transitioned` بعد از commit؛ dedupe.
- **AC:** AC-M3-15.

### T-3.9 جایگزینی fixtureها (پایان برش اول)
- **مجاز:** `public/class-mns-static-fixtures.php`, viewهای برش اول.
- **تحویل:** صفحات برش اول از fixture نمی‌خوانند؛ `MNS_Static_Fixtures` فقط برای صفحات هنوز-استاتیک (M4+) و dev؛ `ui_static` منطق جدید؛ حالت خالی همه‌جا.
- **AC:** AC-UI-EMPTY، AC-M3-16.

---
## ترتیب اجرا
T-0.1 → T-0.2 → T-0.3 ∥ T-0.4 → T-0.5 → T-0.6 → T-0.7 → T-1.1 → T-1.5 → T-1.3 → T-1.2 → T-1.4 → T-2.1 → T-2.2 → T-3.1 → T-3.2 → T-3.3 → T-3.4 → T-3.5 → T-3.6 → T-3.7 → T-3.8 → T-3.9.
**هر کارت پس از تحویل: بازبینی ناظر (`09`) + اجرای AC مربوط در self-test + تأیید مرورگر روی develop؛ بعد کارت بعدی.**
