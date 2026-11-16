# ۰۵ — یکپارچه‌سازی‌ها (وردپرس، قالب، پیامک، کیف پول، زرین‌پال، فایل)

> **قانون طلایی:** هیچ فایلی خارج از `wp-content/plugins/medyar-nursing-services/` ویرایش نمی‌شود (نه قالب `dr-plus`، نه ARP/MTW/MSQ، نه Persian WooCommerce). هر نیاز به آن‌ها فقط با **فراخوانی API عمومی آن‌ها** یا **hook/filter** است. اگر API لازم نبود ⇒ ایجنت متوقف شود و از ناظر بپرسد.

## ۱. وردپرس: کاربران، نقش‌ها، capability
- هر کاربر سایت = حساب `wp_users`؛ **بیمار = هر کاربر واردشده** (cap `mns_view_patient_panel` به `subscriber`/`customer` داده شده)، **پرستار = `mns_nurse`** (فقط بعد از تأیید)، **ادمین = `administrator`** (+ capهای `manage_mns`, `mns_manage_orders`, `mns_manage_nurses`).
- نقش‌ها در `MNS_Roles::register()` (موجود). تغییر نقش فقط از `WP_User::add_role()/remove_role()`.
- موبایل کاربر: از `\DrPlus\Utils\User::get_phone($uid)` (و `find_user_by_mobile($mobile)`)؛ اگر کلاس نبود، meta `drplus_mobile`/`mobile`/`billing_phone`. نام: `first_name`/`last_name`/`display_name`. کد ملی `\DrPlus\Utils\User::get_nid($uid)` (یا meta `drplus_nid`/`nid`). تاریخ تولد meta `birthday` (timestamp؛ **تابع get_birthday قالب خالی را «اکنون» می‌کند، meta را مستقیم بخوان** — درس ARP).
- شمارهٔ موبایل = شناسهٔ ورود؛ **ویرایش‌ناپذیر** در پنل (فقط پشتیبانی).

## ۲. ورود/ثبت‌نام (OTP قالب Doctor Plus)
- ورود با OTP پیامکی خود قالب (`themes/dr-plus/inc/AJAX/Auth.php`: `send_otp`/`check_otp`؛ اگر کاربری با موبایل نبود ثبت‌نام خودکار) — **MNS OTP جدا نمی‌سازد.**
- صفحهٔ ورود: `/?login=1&redirect_to=<URL-encoded>`. برای هر صفحهٔ محافظت‌شدهٔ MNS (فرم عضویت پرستار، پنل‌ها) **gate**: اگر لاگین نیست ⇒ `wp_safe_redirect( login_url($current) )` با همان الگوی `Arp_Affiliate_Access::login_url()` (فیلتر `mns_login_url`).
- **سوییچ ورود** روی صفحهٔ ورود قالب: هوک `drplus/auth/form/start` (الگوی `Arp_Affiliate_Access::auth_role_switch`) ⇒ لینک‌های «ورود کاربر / ورود پرستار» که `redirect_to` را به فرم/پنل پرستار می‌برند (CSS درون‌خطی فقط وقتی `?login` هست).
- آیتم هدر/منوی حساب: فیلتر `drplus/account_menu_items` و `woocommerce_account_menu_items` برای «پنل پرستار» (فقط اگر `mns_nurse`) و «پنل بیمار» — الگوی `header_account_item` در ARP. **حتماً با `class_exists`/`function_exists` محافظت شود.**
- **OTP اضافه هنگام ثبت سفارش** (شمارهٔ تماس متفاوت) — **در برش اول پیاده نمی‌شود** (بنگر `04-api-contract.md` API-ORD-1): شمارهٔ متفاوت ⇒ `ERR_OTP_REQUIRED` تا مالک تصمیم بگیرد (ProfileOTP قالب راه‌حل بالقوه است).

## ۳. فرم درخواست عضویت پرستار — الگوی «طرح همکاران» (ARP) که باید عیناً دنبال شود
مرجع (فقط بخوان، تغییر نده): `affiliate-referral/public/views/signup.php`، `includes/class-arp-affiliate-applications.php`، `public/class-arp-affiliate-access.php`، `public/class-arp-panel-actions.php`، `admin/list-tables/class-arp-applications-table.php`، CSS `.arp-submit*`, `.arp-signup*`, `.arp-how-steps*`.
| ARP | MNS (معادل) |
|---|---|
| قالب صفحهٔ `signup` + gate لاگین | قالب موجود `mns-nurse-signup.php` + `MNS_Nurse_Access::gate_signup()` |
| user meta `arp_affiliate_status` (`pending/approved/rejected`) | جدول `mns_nurses.status` (E-001؛ منبع حقیقت) + meta آینه‌ای اختیاری نه |
| `Arp_Affiliate_Applications::approve()` ⇒ `add_role` | `MNS_Nurse_Applications::approve($user_id,$admin_id)` ⇒ `add_role('mns_nurse')` + پیامک #2 + هوک `mns_nurse_approved` |
| `reject($note)` ⇒ `remove_role` + پیامک | `reject()` ⇒ `rejected`+دلیل؛ پیامک #3؛ فرم پرشده برای ارسال مجدد |
| `store_documents()` با `media_handle_upload` و `post_status=private` | **ذخیره در `mns_files` خصوصی** (نه کتابخانهٔ رسانه) — بهبود برای مدارک هویتی |
| `admin_post_arp_affiliate_approve/reject` + nonce + cap | `admin_post_mns_nurse_approve/reject/suspend` + nonce + `mns_manage_nurses` |
| `Arp_Applications_Table` (WP_List_Table) | `MNS_Nurse_Applications_Table` (فیلتر وضعیت، جستجو، مشاهدهٔ مدارک) |
| صفحه‌های `signup-pending/rejected/already` | ۴ حالت: در حال بررسی · ردشده (دلیل+ارسال مجدد) · تعلیق · تأییدشده (لینک پنل) |
UI صفحه: کارت فرم + کارت «چطور کار می‌کند» با ۴ گام (تکمیل اطلاعات ← بررسی ادمین ← تنظیم برنامه و مناطق ← دریافت سفارش). فیلدهای فرم: `01-backend-decisions.md §۴`. کلاس‌های CSS با پیشوند `mns-` (کپی ساختاری از `.arp-submit*` با توکن‌های `--mns-*`)؛ از `.mns-field-block*` موجود استفاده شود.

## ۴. پیامک — `medyar-sms-queue` (MSQ)
- **تنها مسیر ارسال:** `MSQ_Client::enqueue( array )` (پیش از آن `MSQ_Client::is_ready()`؛ اگر نبود ⇒ لاگ + اعلان ادمین، **هرگز ارسال مستقیم Faraz**).
- آرگومان‌ها: `channel='nursing'`, `event=<slug>`, `phone`, `pattern` (کد پترن Faraz از تنظیمات) یا `body`, `vars` (**کلیدهای پترن کوتاه؛ سقف طول Faraz**؛ از `{}` استفاده نکن)، `priority` (`PRIORITY_ADMIN=10` برای پیامک ادمین‌ها/فوری، 20 عادی)، `dedupe_key` (`mns:{event}:{order_id}:{recipient}`)، `dedupe` (`skip` برای رویدادهای یک‌بار؛ `replace` برای مثل «تغییر پرستار»)، `meta` برای هوک `msq_job_sent_nursing`.
- **درج در صف خارج/بعد از commit تراکنش DB** (هوک `mns_order_transitioned` بعد از COMMIT)؛ شکست پیامک **هرگز** عملیات اصلی (ثبت سفارش، گزارش، علامت) را برنمی‌گرداند؛ خطا در لاگ MSQ و پنل ادمین دیده می‌شود.
- ۱۲ رویداد (بنگر `../03-nursing-project/sms-events.md`): هر رویداد یک **ثابت** `MNS_Sms::EVT_*`، یک کلید تنظیم پترن `S-SMS-<EVT>` (خالی ⇒ از `body` فارسی پیش‌فرض متن ساده استفاده نشود؛ ارسال انجام نمی‌شود و فقط در لاگ ثبت می‌شود تا پترن ساخته شود)، گیرنده‌ها، متغیرهای مجاز. **متغیرها/متن بالینی ممنوع:** فقط `کد سفارش`, `نام پرستار`, `تاریخ`, لینک.
- شمارهٔ ادمین‌های اعلان: تنظیم `S-SMS-ADMINS` (آرایهٔ موبایل).
- رویدادهای برش اول: #1 ثبت درخواست عضویت · #2 تأیید · #3 رد · #4 ثبت سفارش · #5 تخصیص/تغییر پرستار · #11 سفارش بدون پرستار · #12 لغو.

## ۵. کیف پول پرستار — `medyar-toman-wallet` (MTW) — M5
- واریز سهم: `Wallet_Manager::get_instance()->add_balance( $nurse_user_id, $amount, 'booking_payout'‑مانند, array( 'transaction_code' => 'MNS-N-{invoice_id}', 'description' => …, 'status'=>'completed'? ) )` — **نوع عملیات و فیلدهای `transaction_data` را از `class-booking-specialist-payout.php` (الگوی واریز سهم متخصص) کپی کن؛ نوع جدید ثبت‌شده با فیلتر `register_ledger_type`**. مقدار بازگشتی `true|WP_Error`؛ تراکنش اتمیک خود MTW (قفل ردیف موجودی).
- ایدمپوتنسی دوطرفه: `mns_wallet_credits UNIQUE(invoice_id)` + `transaction_code` یکتا در MTW (`is_reference_id_used`/جستجو با `get_transactions_by_code`).
- موجودی/تراکنش‌ها فقط **خواندنی** در MNS (`get_balance`)؛ برداشت و ادمین مالی در MTW می‌ماند (قطعی: کیف پول کامل است).

## ۶. زرین‌پال — پرداخت فاکتور — M5
- **از MTW کپی منطقی (نه ویرایش):** merchant = option `mtw_zarinpal_merchant_id` یا تنظیمات `WC_ZPal` (`merchantcode`)؛ endpointها: `payment/request.json`, `verify.json`, `inquiry.json` نسخهٔ v4، `StartPay/{authority}`؛ **`currency=IRT`** (مبلغ به تومان همان‌طور که MTW می‌فرستد — **قبل از کدنویسی با نمونهٔ `Zarinpal_Gateway::initiate_payment` در MTW تطبیق شود؛ اگر MTW ×۱۰ می‌کند همان را اعمال کن**).
- کلاس `MNS_Payment_Gateway_Zarinpal` پشت interface `MNS_Payment_Gateway` (`initiate($payment)`, `verify($authority,$status)`, `inquiry($authority)`). **callback اختصاصی MNS** (route `mns/v1/payments/return` یا query `mns_zp_return`) — نه endpoint MTW (`compelete-transactions`).
- امنیت: مبلغ فقط از سرور و از فاکتور؛ `authority` یکتا؛ transition ایدمپوتنت (R-PAY-2)؛ لاگ بدون شمارهٔ کارت کامل؛ timeout ۳۰ثانیه؛ `processing` گیرکرده ⇒ cron inquiry.
- صفحهٔ نتیجه: موفق / ناموفق (تلاش دوباره) / نامشخص («در حال بررسی»، بدون دکمهٔ پرداخت).

## ۷. آپلود و فایل خصوصی
- پوشه: `wp-content/uploads/mns-private/{yyyy}/{mm}/` با `.htaccess` (`Require all denied` / `deny from all`) و `index.php`؛ نام فایل `bin2hex(random_bytes(16)).ext`؛ **هرگز در کتابخانهٔ رسانهٔ وردپرس**.
- اعتبارسنجی: پسوند/MIME واقعی (`wp_check_filetype_and_ext` + `finfo`)، سقف حجم (S-FIL-MAX، ۵MB مدارک، ۱۰MB عکس دارو)، تعداد، حذف EXIF/GPS (باز-ذخیره با GD/Imagick برای jpg)، ریزش تصویر به ≤ ۲۰۴۸px.
- دسترسی: API-FIL-1؛ **هیچ URL مستقیم فایل** به کاربر داده نمی‌شود.
- پاک‌سازی: هنگام ریست MNS فایل‌ها هم پاک شود (ابزار بازنشانی).

## ۸. وابستگی‌های اختیاری با مراقبت
`Persian WooCommerce` (`pw-datepicker-js`) برای تقویم شمسی (موجود در `MNS_Assets`)؛ `Faraz SMS` (فقط از طریق MSQ)؛ ووکامرس **استفاده نمی‌شود** (سفارش/فاکتور ما جدول خودمان است) مگر منوی حساب. همهٔ کلاس‌های خارجی با `class_exists`/`function_exists` محافظت شوند؛ نبودنشان یعنی ویژگی غیرفعال + اعلان ادمین، **نه fatal**.

## ۹. کش و CDN
WP Rocket + LiteSpeed/CDN: صفحات پنل و همهٔ endpointها **نباید کش شوند** (`DONOTCACHEPAGE`، `nocache_headers()`، `Cache-Control: private`)؛ asset با نسخهٔ mtime (موجود)؛ AJAX با `?_envelope=1`؛ تست با `?mns_nocache=1`.
