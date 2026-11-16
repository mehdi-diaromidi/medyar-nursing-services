# ۰۴ — قرارداد API (UI ↔ بک‌اند)

> **اصل:** شکل پاسخ‌ها همان شکل داده‌ٔ fixtureهای فعلی است (`public/static-fixtures/**`) تا viewها با کمترین تغییر به داده واقعی وصل شوند. هر جا fixture با این سند فرق داشت، **این سند برنده است** و fixture/view با آن هم‌خوان می‌شود.
> فقط برش اول (M1–M3) کامل شرح شده؛ M4+ در انتها خلاصه است و با شروع milestone کامل می‌شود.

## ۱. قواعد عمومی
- **REST namespace:** `mns/v1` (`/wp-json/mns/v1/...`). احراز هویت: کوکی وردپرس + هدر `X-WP-Nonce` (nonce `wp_rest`)؛ **هر route `permission_callback` صریح دارد** (هرگز `__return_true` مگر public واقعی).
- **Envelope:** همهٔ پاسخ‌ها `{"ok":true,"data":{…},"meta":{…}?}` یا `{"ok":false,"error":{"code":"ERR_…","message":"…فارسی…","field":"…"?,"data":{…}?}}`. کد HTTP واقعی (200/201/400/401/403/404/409/422/429/500) **مگر** `?_envelope=1` که همیشه 200 است (CDN بدنهٔ 4xx/5xx را عوض می‌کند — الگوی `medyar-chat-pro`). کلاینت همیشه با `?_envelope=1` صدا می‌زند.
- **زمان/تاریخ:** تاریخ خدمت `YYYY-MM-DD`، زمان‌ها `YYYY-MM-DD HH:MM` به وقت `Asia/Tehran`، `*_ts` عدد ثانیهٔ UNIX برای شمارندهٔ زنده. **ارقام انگلیسی** در API؛ فارسی‌سازی فقط در UI.
- **Idempotency:** POSTهای ساخت (سفارش، پرداخت، اعتراض) هدر `Idempotency-Key` (UUID) می‌گیرند؛ ذخیره ۲۴ ساعته (transient/جدول) ⇒ تکرار همان پاسخ.
- **Rate limit:** `429 ERR_RATE_LIMIT` (A-13: ۳۰/دقیقه/کاربر؛ ورودی‌های حساس مطابق سیاست).
- **صفحه‌بندی:** `?p=1&per=10` ⇒ `meta:{total,pages,per,p}`.
- **ورودی:** `wp_unslash` ← sanitize مناسب ← validate ← استفاده؛ ورودی ناشناس رد/نادیده؛ فیلد اضافه نادیده.
- **خروجی:** whitelist ستون‌ها؛ هرگز `SELECT *`→پاسخ؛ داده حساس طبق R-PRV.
- **جهت‌دهی دسترسی:** `Auth: patient|nurse|admin|public`. مالکیت (حساب/پرستار) **همیشه سمت سرور** (۴۰۴ برای مال دیگران، نه ۴۰۳، تا وجود سفارش لو نرود).

### کدهای خطا (فهرست زنده)
`ERR_AUTH` 401 · `ERR_FORBIDDEN` 403 · `ERR_NOT_FOUND` 404 · `ERR_VALIDATION` 422 (+`field`) · `ERR_RATE_LIMIT` 429 · `ERR_CONFLICT` 409 · `ERR_SLOT_TAKEN` 409 («همین حالا این زمان پر شد») · `ERR_NO_NURSE` 409 (A-13 قرمز در ثبت) · `ERR_START_WINDOW` 409 · `ERR_BAD_STATE` 409 (انتقال غیرمجاز؛ پیام «این سفارش دیگر این اقدام را نمی‌پذیرد») · `ERR_LOCKED` 409 · `ERR_SLOT_HAS_ORDER` 409 (R-SCH-2) · `ERR_ALREADY_PAID` 409 · `ERR_FILE_TYPE/SIZE/COUNT` 422 · `ERR_CONTRACT_REQUIRED` 422 · `ERR_SERVER` 500 (پیام عمومی؛ جزئیات فقط در لاگ).

## ۲. عضویت و پروفایل پرستار

### API-NRS-1 ارسال درخواست عضویت — `POST admin-post.php?action=mns_nurse_apply` (multipart؛ nonce `mns_nurse_apply`؛ Auth: کاربر واردشده)
فرم سمت سرور رندر می‌شود (الگوی ARP). فیلدها: `first_name*, last_name*, mobile*` (از حساب پیش‌پر، قفل)، `gender*, national_id*(10 رقم، checksum کد ملی)`, `license_no*`, `qualification*`, `experience*`, `workplace*`, `about`, فایل‌ها: `national_card*`, `military_card*(فقط gender=male)`, `license_card*`, `profile_photo*`، `accept_contract*(1)` + `contract_version`. اعتبارسنجی فایل: jpg/png/webp/pdf (pdf فقط مدارک نه عکس پروفایل)، ≤ ۵MB هر کدام؛ نوع واقعی با `wp_check_filetype_and_ext`؛ ذخیرهٔ خصوصی (E-003). موفق ⇒ `nurses.status=pending` + ثبت قرارداد (نسخه/زمان/IP) + پیامک #1 (ادمین‌ها+پرستار) + redirect به صفحهٔ «در حال بررسی». خطا ⇒ بازگشت به فرم با `?mns_err=code&mns_field=…` و فیلدهای پرشده. ارسال مجدد بعد از `rejected` مجاز.
### API-NRS-2 `GET /nurse/me` — Auth nurse(approved)
`{status, display_name, first_name, last_name, email, mobile, birthday, national_id, gender, qualification, license_no, experience, workplace, about, avatar_url|null, accepting, contract:{version,accepted_at}}` (شکل `nurse/profile_full.php`).
### API-NRS-3 `PATCH /nurse/me` — Auth nurse
ویرایش‌پذیر: `display_name*, first_name, last_name, email*, birthday, national_id, qualification, license_no, experience*, workplace, about(≤200)`. **غیرقابل‌ویرایش: `mobile`, `gender`** (ERR_FORBIDDEN اگر فرستاده شود). `POST /nurse/me/photo` (multipart، ≤ ۵MB) ⇒ `photo_approved=0` تا ادمین تأیید کند؛ `DELETE /nurse/me/photo`.
### API-NRS-4 `POST /nurse/me/accepting` `{on:bool}` ⇒ `{accepting, status:{tone,text,sub}}` (همان `MNS_Nurse_Status::compute()`).
### وضعیت زنده (`status`)
`{tone:'ok|warn', text, sub, code:'available|off_hours|away|no_plan|paused'}`؛ منطق: خاموش ⇒ `paused`؛ بدون ردیف برنامه یا بدون منطقه ⇒ `no_plan`؛ امروز همهٔ بازه‌های روشن نیست ⇒ `away`؛ الان در بازهٔ روشن نیست ⇒ `off_hours`؛ وگرنه `available`.

## ۳. برنامه و مناطق پرستار

### API-NRS-5 `GET /nurse/availability` — Auth nurse
```json
{ "status":{…}, "accepting":true,
  "slots":[{"id":"4-8","label":"4:00 – 8:00","name":"صبح زود","closed":false}],
  "week":[["4-8","8-12"],[...],...],               // 7 آرایه شنبه…جمعه
  "days":[{"iso":"2026-11-16","y":1405,"m":8,"d":25,"dow":1,"past":false,"today":true,
           "base":["4-8"],"on":["4-8"],"orders":["12-16"]}],
  "horizon_days":90,
  "regions":[{"prov":"THR","city":"تهران","city_id":1234,"districts":[1,3,6]}],
  "region_orders":{"THR|تهران|3":2},
  "districts":{"THR|تهران":["منطقه 1", …]} }
```
(شکل `availability.php`/config view). `orders` = بازه‌های دارای سفارش فعال همان روز. `days` از ابتدای ماه شمسی جاری تا افق.
### API-NRS-6 `PUT /nurse/schedule/days`
بدنه: `{"days":[{"iso":"2026-11-20","on":["8-12","12-16"]}, …]}` (فقط روزهای تغییرکرده؛ ≤ ۹۰ روز)؛ سرور برای هر روز: انحراف از `week` را به ردیف `day_slots` تبدیل می‌کند (برابر پایه ⇒ حذف ردیف). **R-SCH-2:** بازهٔ دارای سفارش نمی‌تواند خاموش شود ⇒ `ERR_SLOT_HAS_ORDER` (+`data:{iso,slot}`) و **هیچ تغییری ذخیره نمی‌شود** (همه‌یا‌هیچ). پاسخ: وضعیت تازه + `days` بازخوانی‌شده.
### API-NRS-7 `PUT /nurse/regions`
`{"regions":[{"prov":"THR","city_id":1234,"districts":[1,3,6]},{"prov":"ABZ","city_id":0,"districts":[]}]}` ⇒ اعتبارسنجی R-REG-1؛ پاسخ شامل `warning:{orders:N,text}` اگر منطقه‌ای با سفارش تخصیص‌یافته حذف شده (ذخیره می‌شود، فقط هشدار).
*(منبع فهرست استان/شهر: `assets/data/iran-locations.json` فایل ثابت؛ API فهرست جدا نیاز نیست؛ شناسهٔ پایدار شهر `city_id` از همان فایل.)*

## ۴. بیمار و ثبت سفارش

### API-PAT-1 `GET /patient/bootstrap` — Auth patient
شکل `patient/new_order.php`: `{default_phone, service:{id,slug,name,price_min,price_max}|null, patients:[{id,name,label,meta,age_group,gender,last_order?}], districts:{…}, time_slots:[{id,label,name,disabled}], service_fields:{…از service.config.order_fields…}}` + `services:[{id,slug,name}]` (برای چیپ تغییر خدمت). `?service=<slug>`.
### API-PAT-2 بیماران: `GET/POST /patient/patients`, `PUT/DELETE /patient/patients/{id}` (DELETE = `deleted_at`)؛ مالکیت حساب؛ «خودم» خودکار (R-PAT-1).
### API-A13-1 `GET /availability/check` — Auth patient (یا ادمین)
ورودی: `service, prov, city, district?, date, slot, gender`. خروجی: `{state:'green'|'yellow'|'red', message, alt:[{date,slot,label}]?}` (R-A13-*). بدون شمارش/نام.
### API-ORD-1 `POST /orders` — Auth patient، `Idempotency-Key`
```json
{ "patient_id":4, "service_id":1, "prov":"THR","city_id":1234,"district":3,
  "address":"…", "contact_phone":"09…", "date":"2026-11-18","slot":"12-16",
  "gender":"female", "essentials":{"serum_type":"نرمال سالین","vein_access":"normal","allergy":"no"},
  "notes":"…", "otp_token":"…?" }
```
قواعد: `patient_id` متعلق به حساب؛ `contact_phone` اگر با شمارهٔ تأییدشدهٔ حساب فرق دارد ⇒ `otp_token` اجباری (R-OTP: مرحلهٔ بررسی، تصمیم #97 [پیشنهاد]؛ در برش اول: **پذیرفتن شمارهٔ حساب بدون OTP و رد شمارهٔ متفاوت با `ERR_OTP_REQUIRED` تا مالک تصمیم بگیرد**)؛ اعتبارسنجی `essentials` طبق `service.config.order_fields` (حساسیت «دارد» ⇒ نام ماده الزامی؛ سرم «طبق نسخه» ⇒ فایل نسخه `rx_file_id`). تراکنش: A-13 مجدد → درج سفارش (کد ۵رقمی) → لاگ → (auto) تخصیص. پاسخ `201 {order:{code,status,service,patient,when,address_short,price_min,price_max,nurse|null},next:{track_url}}`.
**خطاها:** `ERR_NO_NURSE`, `ERR_SLOT_TAKEN`, `ERR_VALIDATION(field)`.
### API-ORD-2 `GET /orders` — Auth patient (فهرست «سفارش‌های من»)
ورودی: `tab=all|active|unpaid|done|cancelled, q, from, to, service, recurring, sort, p`. خروجی شکل `patient/orders.php`: `{items:[{code,service,patient,when,status,status_label,tone,nurse_name?,amount?,unpaid?:bool,recurring?}], tabs:{all:N,…}}` + meta. (نگاشت وضعیت به زبان بیمار سمت سرور؛ فیلتر «بر اساس بیمار» نداریم.)
### API-ORD-3 `GET /orders/{code}` — Auth patient
شکل `patient/order_view.php` (وضعیت به‌زبان بیمار، timeline فقط تاریخ، پرستار بدون شماره، فاکتور خلاصه، گزارش whitelist، `can_cancel`, `can_repick`, `can_edit`).
### API-ORD-4 `POST /orders/{code}/cancel` `{reason?}` (R-ORD-8)؛ `PATCH /orders/{code}` `{notes?, essentials?}` (R-ORD-7).

## ۵. پرستار: سفارش‌ها (خواندنی در برش اول؛ اجرا در M4)
### API-NOR-1 `GET /nurse/orders?tab=upcoming|pending|done&q&month&status&p` — شکل `nurse/orders.php` (سهم/امتیاز طبق R-PRV-2؛ تب‌ها و مجموع‌ها از سرور).
### API-NOR-2 `GET /nurse/orders/{code}` — شکل `nurse/order_view.php` با whitelist R-PRV-1 (تلفن/آدرس/map_url فقط در وضعیت‌های مجاز).
### API-NOR-3/4 (M4) `POST /nurse/orders/{code}/start`, `/end`.
### API-NDB-1 `GET /nurse/dashboard` — داشبورد (شکل `nurse/dashboard.php`) — M4.

## ۶. ادمین (wp-admin؛ `admin-post` + nonce + `current_user_can`)
- فهرست/جزئیات درخواست‌های عضویت (`WP_List_Table`)، تأیید/رد/تعلیق، مشاهدهٔ مدارک (از API-FIL-1)، تنظیم خدمات مجاز، برنامهٔ پایه، تأیید عکس.
- سفارش‌ها: فهرست مرکزی (فوری/نیازمند بررسی بالا)، جزئیات، تخصیص/تغییر پرستار (`API-ADM-ASG`)، لغو با گزینهٔ نرخ لغو، ثبت سفارش دستی، لیست «بدون پرستار» + ۵ پیشنهاد.
- خدمات (ویرایش نام/فعال/قیمت/`has_drug_step`/`config` JSON با اعتبارسنجی شکل)، تنظیمات (`06`)، قرارداد (نسخهٔ جدید)، ابزار بازنشانی، صفحهٔ self-test (dev).
### API-FIL-1 `GET /files/{id}` — Auth: ادمین یا مالک (یا پرستار برای عکس پروفایل تأییدشده‌ٔ پرستار همان سفارش به بیمار) ⇒ stream با `Content-Type` درست، `Cache-Control: private, no-store`.

## ۷. M4+ (خلاصه؛ در شروع milestone کامل می‌شود)
`POST /nurse/orders/{code}/checklist/finalize` · `GET/POST drug (+consult)` · `POST report/finalize` · `GET /nurse/ratings` · `GET/PATCH nurse profile` (برش ۱) · فاکتور: `GET /invoices`, `/invoices/{no}`, `POST /invoices/{no}/dispute`, `POST /invoices/{no}/pay-init`، callback زرین‌پال `GET /payments/return` (public با token/authority) · پرسشنامه `GET/POST /surveys/{token}` · دوره‌ای `…/recurring`.
