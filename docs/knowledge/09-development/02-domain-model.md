# ۰۲ — مدل دامنه و دیتابیس (E-xxx)

> قواعد عمومی همهٔ جدول‌ها: پیشوند `{$wpdb->prefix}mns_` (روی develop: `Dj3i_mns_` — **هاردکد نکن**) · موتور InnoDB · `utf8mb4_unicode_520_ci` (از `$wpdb->get_charset_collate()`) · ساخت/ارتقا فقط با `dbDelta` در migration نسخه‌دار · همهٔ زمان‌ها `DATETIME` به **UTC** ذخیره (`gmdate`) و تاریخ خدمت `DATE` میلادی به‌وقت `Asia/Tehran` (نه UTC) · مبلغ‌ها `BIGINT UNSIGNED` به **تومان صحیح** (بدون اعشار) · شناسه‌ها `BIGINT UNSIGNED AUTO_INCREMENT` · نام ستون snake_case · `created_at/updated_at` در همهٔ جدول‌های اصلی · **هیچ حذف فیزیکی برای داده‌های مالی/سفارش** (فقط `deleted_at`/وضعیت).
> JSON فقط برای داده‌های «ساخت‌نیافته/تغییرپذیر»؛ هرچه روی آن فیلتر یا JOIN داریم ستون واقعی است.

## ۱. هویت و عضویت

### E-001 `mns_nurses` (یک ردیف برای هر درخواست‌دهندهٔ پرستار؛ PK = `user_id`)
| ستون | نوع | توضیح |
|---|---|---|
| `user_id` | BIGINT PK | کاربر وردپرس |
| `status` | VARCHAR(16) | `pending` · `approved` · `rejected` · `suspended` |
| `gender` | VARCHAR(8) | `female`/`male` (برای فیلتر تخصیص؛ **قطعی، غیرقابل‌تغییر توسط پرستار** — ادمین تغییر می‌دهد) |
| `national_id` | CHAR(10) | کد ملی (یکتا در میان approved/pending) |
| `license_no` | VARCHAR(32) | شمارهٔ نظام پرستاری |
| `qualification` | VARCHAR(32) | کلید مدرک (`diploma/assoc/bsc/msc/phd`) |
| `experience` | VARCHAR(8) | `0-1/1-3/3-5/5-10/10+` |
| `workplace` | VARCHAR(120) | بیمارستان/محل کار |
| `about` | VARCHAR(200) | |
| `accepting` | TINYINT(1) DEFAULT 1 | کلید «پذیرش درخواست جدید» (A-10) |
| `profile_photo_file_id` | BIGINT NULL | → `mns_files` |
| `photo_approved` | TINYINT(1) DEFAULT 0 | نمایش به بیمار فقط پس از تأیید ادمین |
| `contract_version_id` | BIGINT NULL | → `mns_contract_versions` |
| `contract_accepted_at` | DATETIME NULL | |
| `contract_accepted_ip` | VARBINARY(16) NULL | `inet_pton` |
| `applied_at`, `reviewed_at` | DATETIME | |
| `reviewed_by` | BIGINT NULL | |
| `reject_note` | TEXT NULL | |
| `meta` | LONGTEXT NULL | JSON ساخت‌نیافته (آیتم‌هایی که کارفرما بعداً اضافه می‌کند) |
ایندکس: `status`, `UNIQUE(national_id)` [فرض: یک کد ملی یک درخواست فعال]. نکته: **نقش وردپرس `mns_nurse` فقط وقتی `status=approved`** دارد؛ تعلیق ⇒ نقش برداشته نمی‌شود، دسترسی از `status` چک می‌شود (R-NRS-3). نام/موبایل/ایمیل/تاریخ تولد در `wp_users`/`user_meta` (کلیدهای `first_name`, `last_name`, `drplus_mobile`، `birthday`؛ از `\DrPlus\Utils\User` بخوان).

### E-002 `mns_contract_versions`
`id`, `version` (int)، `title`, `body` (LONGTEXT)، `active` (فقط یکی)، `created_by`, `created_at`. [متن موقت تا کارفرما بدهد.]

### E-003 `mns_files` (آپلود خصوصی)
`id`, `owner_user_id`, `order_id NULL`, `kind` (`national_card` · `military_card` · `license_card` · `profile_photo` · `rx_photo` · `drug_photo` · `report_photo`)، `rel_path` (نسبت به پوشهٔ خصوصی)، `mime`, `size`, `sha256`, `created_at`, `deleted_at NULL`. ایندکس: `(owner_user_id, kind)`, `order_id`. ذخیره خارج از webroot یا داخل `wp-content/uploads/mns-private/` با `.htaccess deny` + نام تصادفی؛ **هرگز attachment عمومی وردپرس نه**. دسترسی فقط از endpoint (API-FIL-1).

### E-004 `mns_patients` (پروفایل بیمار؛ جدا از حساب)
`id`, `owner_user_id` (حساب سفارش‌دهنده)، `kind` (`self`/`other`)، `name`, `age_group` (`child/adult/senior`)، `gender` (`female/male`)، `created_at`, `deleted_at`. ایندکس: `owner_user_id`. «خودم» با اولین ورود ساخته می‌شود (R-PAT-1). شمارهٔ تماس روی **حساب** است نه پروفایل بیمار.

## ۲. دسترسی پرستار (برنامه و مناطق)

### E-010 `mns_nurse_services` (ماتریس صلاحیت)
`nurse_id`, `service_id`, PK مرکب. ادمین تعیین می‌کند (R-NRS-4).

### E-011 `mns_nurse_regions`
| ستون | توضیح |
|---|---|
| `id` PK, `nurse_id` | |
| `province_code` CHAR(3) | کد استان در `assets/data/iran-locations.json` (مثل `THR`) |
| `city_id` INT | شناسهٔ پایدار شهر در کتابخانه؛ **`0` = همهٔ شهرهای استان** |
| `district_no` SMALLINT | `0` = کل شهر؛ `>0` = شمارهٔ منطقه (فقط شهرهای منطقه‌دار) |
`UNIQUE(nurse_id, province_code, city_id, district_no)`. قاعده: شهر منطقه‌دار باید ≥ ۱ ردیف `district_no>0` داشته باشد و ردیف `district_no=0` ندارد؛ شهر بدون منطقه فقط `district_no=0`؛ `city_id=0` فقط با `district_no=0` (R-REG-1..3). مناطق شهری: منبع داده `assets/data/iran-locations.json` + فهرست مناطق (فعلاً تهران ۲۲) در تنظیم S-REG-DISTRICTS.

### E-012 `mns_nurse_week` (برنامهٔ پایهٔ هفتگی)
`nurse_id`, `weekday` TINYINT (0=شنبه … 6=جمعه)، `slot_id` VARCHAR(8) (`4-8` …)؛ PK مرکب. وجود ردیف = آن بازه در آن روز روشن است. (نقطهٔ شروع؛ ادمین/پشتیبانی می‌دهد؛ پرستار نیز می‌تواند ویرایش کند — بنگر R-SCH-1.)

### E-013 `mns_nurse_day_slots` (استثناها و ویرایش ماهانهٔ پرستار)
`nurse_id`, `day` DATE, `slot_id`, `available` TINYINT(1); PK مرکب. **فقط انحراف از برنامهٔ پایه** ذخیره می‌شود. دسترسی مؤثر یک (روز، بازه) = اگر ردیف `day_slots` هست مقدارش، وگرنه `week` همان روز هفته. «بازگشت به برنامهٔ پایه» = حذف ردیف‌ها. حداکثر دورنما S-SCH-HORIZON (۹۰ روز).

## ۳. کاتالوگ (ساده در برش اول)

### E-020 `mns_services`
`id`, `slug` (یکتا)، `category` (`serum`/`injection`/… متن آزاد)، `name`, `active`, `price_from`, `price_to` (نمایش برآورد)، `has_drug_step` TINYINT, `sort`, **`config` LONGTEXT JSON ساخت‌نیافته**، `created_at/updated_at`.
`config` (نمونه؛ هر کلید دلخواه است و UI فقط آنچه می‌شناسد می‌خواند): `order_fields` (فیلدهای فرم ثبت سفارش؛ همان `service_fields` فیکسچر `new_order.php`)، `checklist` (مراحل/فیلدها؛ همان `services.*.steps` در `nurse/checklist.php`)، `tokens` و `consumables` (آرایهٔ ساخت‌نیافته با `id,label,price,repeatable`)، `rx_types`، `description` و متن‌های صفحهٔ خدمت (`catalog/services.php`)، `drug_step` (پیکربندی)…
> **seed:** فقط `serum` و `injection` از `catalog/services.php` و `new_order.php`؛ بقیه fixtureها (پانسمان، سوند…) seed نمی‌شوند.

## ۴. سفارش

### E-030 `mns_orders`
| ستون | نوع | توضیح |
|---|---|---|
| `id` | PK | |
| `code` | CHAR(6) UNIQUE | کد پیگیری نمایشی: **ارقام ۵تایی** (`48215`) با پیشوند `MD-` در UI؛ تصادفی با تلاش مجدد روی برخورد؛ اگر >۶۰٪ فضا پر شد ⇒ ۶رقمی (R-ORD-2). |
| `account_user_id` | BIGINT | سفارش‌دهنده |
| `patient_id` | BIGINT | |
| `service_id` | BIGINT | |
| `source` | VARCHAR(10) | `site` · `phone` · `recurring` |
| `plan_id`, `session_no` | BIGINT NULL, INT NULL | دوره‌ای |
| `status` | VARCHAR(16) | `submitted/assigned/in_progress/service_ended/invoiced/paid/completed/cancelled/needs_review` (`medication` وضعیت جدا نیست؛ زیرمرحله: بنگر `drug_step`) |
| `review_flag` | TINYINT | علامت «نیازمند بررسی» (R-ORD-6) |
| `review_reason` | VARCHAR(32) NULL | `drug_denied` · `report_incomplete` · `report_not_done` · `no_start` · `invoice_error` · `admin` |
| `urgent_flag` | TINYINT | اطلاع فوری از گزارش (فقط مدیریت می‌بیند) |
| `province_code`, `city_id`, `district_no` | | محل خدمت (همان شناسه‌های E-011) |
| `address` | TEXT | **حساس** |
| `contact_phone` | VARCHAR(16) | **حساس** (شمارهٔ هماهنگی) |
| `service_date` | DATE | تاریخ خدمت (`Asia/Tehran`) |
| `slot_id` | VARCHAR(8) | `4-8`… |
| `gender_pref` | VARCHAR(8) | `female/male` (قطعی) |
| `nurse_user_id` | BIGINT NULL | |
| `assigned_at`, `assignment_mode` | DATETIME NULL, `auto/manual` | |
| `essentials` | LONGTEXT JSON | «اطلاعات ضروری سفارش» (حساسیت، نوع سرم، دسترسی وریدی، …) — ساخت‌نیافته |
| `notes` | TEXT | توضیحات بیمار (قابل ویرایش تا قبل از شروع) |
| `started_at`, `ended_at` | DATETIME NULL | |
| `duration_minutes` | INT NULL | |
| `completed_at`, `cancelled_at` | DATETIME NULL | |
| `cancelled_by` | VARCHAR(8) NULL | `patient/admin` |
| `cancel_reason` | TEXT NULL | فقط مدیریت |
| `cancel_counts_for_nurse` | TINYINT DEFAULT 0 | لغو ادمین؛ پیش‌فرض خیر |
| `steps` | LONGTEXT JSON | زیروضعیت مراحل: `{checklist:"none|final|reopened", drug:"none|consulted|final|reopened|na", report:"none|final"}` |
| `version` | INT DEFAULT 1 | شمارنده برای optimistic lock |
| `created_at`, `updated_at` | | |
ایندکس‌ها: `UNIQUE(code)`, `(account_user_id, status, created_at)`, `(nurse_user_id, status, service_date)`, `(service_date, slot_id, status)`, `(status, service_date)`, `UNIQUE(plan_id, session_no)` (دوره‌ای)، `patient_id`, `service_id`.

### E-031 `mns_order_log` (حسابرسی)
`id`, `order_id`, `actor_user_id` (0 = سیستم/cron)، `actor_role` (`patient/nurse/admin/system`)، `action` (`create/assign/reassign/cancel/start/end/revert_start/revert_end/unlock/edit/flag/unflag/invoice/pay/...`)، `from_status`, `to_status`, `payload` JSON، `created_at`. فقط INSERT.

### E-032 `mns_order_edits`
ویرایش بیمار قبل از شروع: `order_id`, `field` (`notes`/`essentials.*`), `old`, `new`, `edited_by`, `created_at` (برای چیپ «ویرایش‌شده» و اعلان پرستار).

### E-033 `mns_notifications` (اعلان‌های داشبورد پرستار)
`id`, `user_id`, `type` (`cancelled/reassigned/time_changed/schedule_changed/region_disabled/unlocked/status_fixed/patient_edited`)، `order_id NULL`, `payload` JSON, `seen_at NULL`, `created_at`, `expires_at` (+۴۸ساعت بعد از seen یا ایجاد؛ cron پاک می‌کند). **بدون پیامک.**

## ۵. اجرای خدمت (M4)

### E-040 `mns_checklists`
`id`, `order_id`, `version` INT, `status` (`final`/`superseded`)، `data` LONGTEXT JSON (پاسخ‌ها + اقلام/توکن‌های انتخابی با تعداد)، `finalized_by`, `finalized_at`, `unlocked_by NULL`, `unlocked_at NULL`. `UNIQUE(order_id, version)`. [**دکمهٔ ذخیرهٔ پیش‌نویس نداریم** — فقط ثبت نهایی بعد از پایان خدمت؛ مالک، ۱۳ نوامبر.] بازگشایی قفل = نسخهٔ جدید + `superseded` شدن قبلی.

### E-041 `mns_drug_steps`, E-042 `mns_drug_items`, E-043 `mns_drug_consults`, E-044 `mns_consult_doctors`
- `drug_steps`: `order_id UNIQUE`, `path` (`rx`/`consult`)، `status` (`open/final/reopened`)، `finalized_at`.
- `drug_items`: `step_id`, `name`, `dose`, `route`, `given` TINYINT, `note`, `photo_file_id`.
- `drug_consults`: `step_id`, `doctor_id`, `result` (`approved/denied/no_answer`)، `at` DATETIME (زمان مشاوره؛ ≤ ۳ ساعت قبل؛ آینده ممنوع)، `note`، `recorded_at`؛ **نتیجهٔ approved/denied تغییرناپذیر** (R-DRG-3).
- `consult_doctors`: `id`, `name`, `phone`, `active`. (ادمین؛ شمارهٔ پزشک فقط پس از باز شدن مرحله از سرور.)
[قیمت دارو در فاکتور نیست؛ ردیف «مشاورهٔ پزشکی» با مبلغ تنظیم‌پذیر — **باز**.]

### E-045 `mns_reports`
`order_id UNIQUE`, `result` (`complete/partial/not_done`)، `data` JSON (بخش‌های ۱–۷ طبق `04-pages/nurse/report.md`)، `visible_to_patient` JSON (نتیجهٔ whitelist‌شده)، `urgent` TINYINT، `finalized_at`، `internal_note` (فقط ادمین؛ اصلاح بعد از ثبت)، `UNIQUE(order_id)`. عکس‌ها در `mns_files(kind=report_photo)` (≤ ۱۰، با تیک رضایت).

## ۶. مالی (M5)

### E-050 `mns_invoices`
`id`, `order_id`, `version` INT, `number` VARCHAR(16) (قالب [باز]؛ پیش‌فرض `INV-{counter}`)، `status` (`unpaid/dispute/paid`)، `lines` JSON (ردیف‌ها: label, qty, unit, amount, kind=`service|extra|consumable|consult|discount_first|discount_loyalty`)، `subtotal`, `discount_first`, `discount_loyalty`, `total`, `share_percent` (snapshot)، `nurse_share` (snapshot)، `checklist_version`, `link_token` (غیرقابل‌حدس، ≥ ۳۲ نویسه)، `issued_at`, `paid_at`, `pay_method` (`online/manual`)، `created_by`, `revised_from_id NULL`. `UNIQUE(order_id, version)`. فقط آخرین نسخهٔ غیر-superseded قابل پرداخت است؛ «قدیمی» با `superseded=1`.
### E-051 `mns_invoice_disputes`
`invoice_id`, `lines_json` (ردیف‌های مورد اعتراض)، `text` (الزامی)، `status` (`open/resolved`)، `resolved_by/at`.
### E-052 `mns_payments`
`id`, `invoice_id`, `invoice_version`, `amount` (تومان)، `gateway` (`zarinpal/manual`)، `authority` UNIQUE NULL, `ref_id` UNIQUE NULL, `status` (`initiated/processing/paid/failed/expired`)، `card_pan_masked`, `fee`?, `created_at`, `verified_at`, `raw` JSON. **انتقال وضعیت فقط با `UPDATE … WHERE status=<from>`** (R-PAY-2).
### E-053 `mns_wallet_credits` (ایدمپوتنسی واریز سهم)
`id`, `order_id`, `invoice_id`, `nurse_user_id`, `amount`, `tx_code` UNIQUE (مثل `MNS-N-{invoice_id}`)، `mtw_tx_id`, `created_at`. واریز با `Wallet_Manager::add_balance` فقط اگر ردیفی برای `invoice_id` نباشد (R-PAY-5).

## ۷. بعدی‌ها (M6–M7؛ طراحی کلی)
- E-060 `mns_surveys` (`order_id UNIQUE`, `sci`, `ethics`, `order_score`, `text`, `kind`, `created_at`, `link_token`) · E-061 `mns_complaints` · E-062 `mns_loyalty_stamps` (`order_id UNIQUE`, `patient_id`, `removed_at`) · E-063 `mns_discount_codes`/`mns_discount_rules` · E-064 `mns_recurring_plans` (`patient_id`, `service_id`, الگو JSON، `status`، `next_date`، `preferred_nurse_id`، `max_sessions`، `done`، `skipped`) · E-065 `mns_notification_log`/`mns_sms_log` (ارجاع به MSQ job id).

## ۸. نقشهٔ روابط (خلاصه)
`wp_users` 1—0..1 `mns_nurses` · `mns_nurses` 1—* `{nurse_services, nurse_regions, nurse_week, nurse_day_slots, files}` · `wp_users`(حساب) 1—* `mns_patients` · `mns_orders` *—1 `{patient, service, nurse(user)}` · `mns_orders` 1—* `{order_log, checklists(version), invoices(version)}` · `mns_orders` 1—0..1 `{drug_steps, reports}` · `mns_invoices` 1—* `payments` · `mns_invoices` 1—0..1 `wallet_credits`.

## ۹. قواعد داده‌ای مشترک
- هر نوشتن چندجدولی در **یک تراکنش** (`START TRANSACTION … COMMIT`، روی خطا `ROLLBACK`)؛ SELECT قفل‌دار (`FOR UPDATE`) فقط روی ردیف‌های لازم و به ترتیب ثابت (پرستار ← سفارش) برای جلوگیری از deadlock.
- هیچ `SELECT *` در پاسخ‌های API؛ whitelist ستون‌ها (R-PRV-*).
- JSON همیشه با `wp_json_encode` و خواندن با `json_decode(..., true)` و **اعتبارسنجی شکل** هنگام نوشتن.
- migrationها: تابع `MNS_Migrations::run()` با نسخهٔ عددی در `mns_db_version`؛ هر نسخه یک متد؛ idempotent؛ لاگ در `mns_migration_log` option؛ `MNS_DB_VERSION` برای هر تغییر schema بالا می‌رود.
