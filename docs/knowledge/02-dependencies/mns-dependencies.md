# وابستگی‌های MNS به بقیهٔ سایت

فقط چیزهایی که پروژهٔ پرستاری مستقیم استفاده می‌کند یا باید رعایت کند. جزئیات سایر بخش‌های سایت (آزمایشگاه، داروخانه، چت، رزرو، پچ‌های قالب) عمداً اینجا نیست.

## ۱. قالب Doctor Plus (`dr-plus`)

- قالب والد تجاری (بدون child theme)؛ **هرگز دستکاری نکن**. هر نیاز → CSS/JS/فیلتر در MNS.
- توکن‌ها: `--primary-100` در سایت مدیار از Redux = **`#0272b1`**؛ `--main-font: "IRANYekanXFANum"`؛ `--red #d60043`؛ `--text-main #383838`؛ شعاع‌ها 24/16px.
- **ریسه‌های رفتاری قالب:**
  - `section, aside { display:block }` بر `[hidden]` غلبه می‌کند → در MNS: `.mns-dash [hidden], .is-hidden { display:none !important }`.
  - دیالوگ‌ها `display:flex` دارند → `[hidden]` باید `!important` باشد.
  - date picker قالب (`drplus-pdp` / `drplus-pd`) با `persianDate` سراسری تداخل دارد → در صفحات MNS dequeue و از `pw-datepicker-js` (Persian WooCommerce) استفاده شود.
- OTP سایت (`drplus_otp`): ثبت‌نام/ورود بیمار و پرستار از همین است («ثبت‌نام و لاگین سایت اوکی است»)؛ فقط تعیین نقش پرستار بعد از ثبت‌نام اضافه می‌شود.
- متخصصان قدیمی (`subtitle = پرستار`) و فرم گرویتی #6 («پرستاری در منزل» قدیمی، پیامک ادمین از `Lab_Admin_Sms_Settings`) سیستم **قبلی**اند؛ تکلیف‌شان با MNS باز است.

## ۲. affiliate-referral (ARP) — مرجع UI

MNS از کیت UI آن استفاده می‌کند (در صفحات orders، invoices، invoice-view؛ بدون ARP به select/تاریخ بومی برمی‌گردد):
- توکن‌ها `--arp-primary` (= #0272b1)، `--arp-bg`, `--arp-card`, `--arp-radius`؛ پوستهٔ `.arp-dash`؛
- select سفارشی `select[data-arp-select]` → `.arp-select__trigger/menu/option`؛
- تقویم شمسی `[data-arp-jdate]` با `$.fn.persianDatepicker` (تم `pdp-arp`)؛
- فیلتر `.arp-refs-filters` (ارسال خودکار AJAX)، جدول `.arp-table`، صفحه‌بندی `.arp-refs-pager`، هدر `arp-refs__head`، کارت `arp-card__head`، دکمه `arp-btn-outline`.
- `arp-public` وابستگی `mns-public` می‌شود تا MNS برنده باشد. سند کامل: `affiliate-referral/UI-UX.md`.
- **الگوی معماری** (AJAX، جدول لیست ادمین `WP_List_Table`، پنل کاربر، دسترسی نقش‌ها) از ARP گرفته می‌شود.

## ۳. کیف پول تومانی (MTW)

- موجودی: user meta `wallet_toman_balance` (تومان صحیح)؛ تغییرات اتمیک (`START TRANSACTION` + `SELECT … FOR UPDATE`).
- API: `Wallet_Manager::get_balance/add_balance/reduce_balance`؛ جدول `{prefix}wallet_transactions` با `transaction_code` **یکتا** و `operation_type` (charge, withdraw, purchase, refund, admin_add, admin_reduce, booking_payout, …).
- درخواست برداشت و تأیید ادمین از قبل کامل است [قطعی: «کیف پول کامل است»]: IBAN `IR`+۲۴ رقم، حداکثر ۲ درخواست/۲۴ ساعت، حداقل 10000.
- `get_balance()` اگر جدول Sheyda (کیف پول قالب) باشد موجودی «در دسترس» Sheyda را برمی‌گرداند؛ `MTW_Sheyda_Balance_Bridge` همگام می‌کند.
- **نقش در MNS:** (۱) سهم پرستار بعد از پرداخت فاکتور با کد یکتا (پیشنهاد: `MNS-N-{invoiceVersionId}`)؛ (۲) نمایش «موجودی شما» در سایدبار (فعلاً fixture).
- ⚠️ `_mtw_probe.php` در ریشهٔ MTW قابل دسترس از وب است؛ باید حذف شود.

## ۴. صف پیامک (MSQ) + سرویس پیامک

- همهٔ پیامک‌های پرستاری از `MSQ_Client::enqueue()` (پیشنهاد `channel=nursing`) با کلید dedupe؛ حالت‌ها: `skip` / `pending` / `replace`.
- اولویت: ادمین 10، عادی 20، کم 30. Backoff: 30s، 120s، 600s، 1800s، سپس `dead`. `max_attempts=3`.
- درج صف **داخل تراکنش DB**، اجرا بعد از COMMIT (الگوی MTW).
- اجرا: loopback در `shutdown` → کران واقعی سرور `admin-ajax.php?action=msq_cron&key=…` هر دقیقه (الزامی) → WP-Cron.
- سرویس: Faraz / IranPayamak (پترنی). **پترن‌ها را توسعه‌دهنده می‌سازد** [قطعی]. متغیر پترن طولانی → خطای «داده درخواستی یافت نشد» (سقف طول؛ مثلاً `price` ≤ 25).
- پیامک جدید برای نبودِ تعریف‌شده در `03-nursing-project/sms-events.md` اضافه نشود.

## ۵. درگاه پرداخت

زرین‌پال REST v4 (تومان×10=ریال برای درگاه). هر پلاگین کلاینت جدا دارد؛ الگوی MTW/Lab. merchant در option `mtw_zarinpal_merchant_id` یا تنظیمات `WC_ZPal`. endpoint بازگشت MTW: `compelete-transactions` (املای غلط در کد). **verify فقط سمت سرور**، کد تراکنش یکتا.

## ۶. شهرها

«کتابخانهٔ شهرهای ایران در پروژه» [قطعی]؛ شناسهٔ پایدار شهر ذخیره شود (نه نام). مناطق را ادمین تعریف می‌کند.

## ۷. کش و CDN

WP Rocket + LiteSpeed. برای AJAX از الگوی `?_envelope=1` (CDN بدنهٔ 4xx/5xx را عوض می‌کند) استفاده کن؛ تست با `mns_nocache=1`. mu-plugin سرور `medyar-wp-rocket-wc-fragments` فقط مربوط به ووکامرس است.
