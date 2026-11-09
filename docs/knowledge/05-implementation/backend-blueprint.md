# نقشهٔ بک‌اند (پیشنهادی — از چت استخراج شده)

> هیچ‌کدام هنوز در کد نیست. جدول‌ها از نکات پیاده‌سازی هر صفحه جمع شده‌اند؛ نام‌ها پیشنهادی (پیشوند `{$wpdb->prefix}mns_`).

## جداول
| جدول | محتوا |
|---|---|
| `orders` | حساب، بیمار، خدمت، شهر/منطقه، تاریخ+بازه، جنسیت، پرستار، وضعیت، علامت‌ها (`review_flag`, `urgent_flag`), `source` (site/phone), `plan_id`+`session_date` (کلید یکتا)، `started_at`, `ended_at`, `duration_minutes`, اطلاعات ضروری (JSON)، توضیحات، دلیل لغو |
| `order_log` | تغییر وضعیت/تخصیص/ویرایش/بازگردانی (چه کسی، چه زمانی، قبل/بعد) |
| `order_edits` | ویرایش بیمار (مقدار قبلی/جدید) |
| `patients` | پروفایل بیمار (نام، گروه سنی، جنسیت) متعلق به حساب |
| `nurses` / user meta | وضعیت عضویت، مدارک، بیمارستان، قرارداد (نسخه/زمان/IP)، عکس (تأییدشده؟)، کلید پذیرش، ماتریس خدمات |
| `nurse_regions`, `nurse_schedule`, `nurse_leaves`, `nurse_schedule_log` | بنگر `regions-schedule.md` |
| `services`, `categories`, `tokens`, `consumables`, `service_token`, `service_consumable` | + `repeatable`, `max_qty`(خالی)، `has_drug_step` |
| `checklists` | سفارش، نسخه، وضعیت (پیش‌نویس/ثبت‌شده/باز شده)، داده (JSON) |
| `checklist_items` | توکن‌ها/اقلام با تعداد |
| `drug_steps`, `drug_items`, `drug_files`, `drug_consults`, `consult_doctors` | بنگر `drug-step.md` |
| `reports`, `report_files` | نسخهٔ فرم؛ فیلدهای اختصاصی JSON؛ علامت فوری |
| `invoices` | سفارش، **نسخه**، وضعیت (unpaid/dispute/paid)، ردیف‌ها (JSON)، مبلغ نهایی، **سهم پرستار (snapshot)**، توکن لینک، پرداخت (روش/شماره/زمان) |
| `invoice_disputes` | ردیف‌ها، توضیح، وضعیت |
| `surveys` | سفارش (یکتا)، سه امتیاز، متن، نوع (توضیح/پیشنهاد/شکایت)، توکن لینک |
| `complaints` | پرونده نارضایتی (وضعیت، یادداشت، نتیجه متنی) |
| `loyalty_stamps`, `discount_codes`, `discount_rules` | مهر (کلید یکتا ضد دوبار)، کد یک‌بارمصرف، قواعد |
| `recurring_plans` | بنگر `recurring.md` |
| `notifications` | اعلان‌های داشبورد پرستار (نوع، سفارش، دیده‌شده) |
| `settings` | `mns_settings` option یا جدول |

ایندکس‌ها: سفارش‌ها (account, status, created)، (nurse, status, date)، `UNIQUE(plan_id, session_date)`؛ فاکتور (order, version)، `UNIQUE(order, version)`؛ مهر `UNIQUE(order)`؛ پرسشنامه `UNIQUE(order)`.

## قفل‌ها و هم‌زمانی
`UPDATE … WHERE status IN (…)` برای لغو/شروع/پایان؛ قفل ردیف پرستار (`SELECT … FOR UPDATE`) برای تخصیص+ظرفیت+مرخصی؛ صدور فاکتور یک‌بار برای هر نسخهٔ چک‌لیست؛ شارژ کیف پول با کد یکتا؛ ساخت نوبت دوره‌ای با کلید یکتا؛ پرداخت در برابر بازگشایی قفل: پرداخت برنده.

## نقاط AJAX/REST (`mns/v1`، nonce، `permission_callback`، `?_envelope=1`)
- بیمار: dashboard, orders (فیلتر/تب/شمارنده), order, cancel, edit-limited, availability-check (A-13, rate limit), create-order (کلید یکتا), patients CRUD, invoices (لیست/جزئیات/dispute/pay-init), survey-get/submit, loyalty, recurring (list/pause/resume/end/skip/edit).
- پرستار: dashboard, orders, order, start, end, checklist (get/save-draft/finalize), drug (get/save/consult/finalize), report (get/save/finalize), upload (فایل خصوصی), regions (get/save), leaves (add/delete), toggle-accepting, notifications-ack, wallet (از MTW).
- ادمین: assign, reassign, manual-order, mark-paid, edit-invoice (فقط unpaid), unlock-step, revert-start/end, clear-review-flag, approve-nurse, schedule edit.
- پرداخت: init (بررسی نسخه/اعتراض/پرداخت‌نشده) → زرین‌پال → callback verify → mark paid + wallet credit (یک‌بار).
- کران‌ها: ساخت نوبت دوره‌ای (روزانه + پس از ذخیره)، `needs_review` پایان روز بدون شروع، انقضای اعلان‌ها (۴۸ ساعت)، یادآوری فاکتور، (اختیاری) هشدار خدمت طولانی.

## دسترسی
مالکیت سمت سرور (حساب/پرستار)؛ whitelist فیلدها برای هر نقش؛ هیچ داده حساس در HTML ثابت؛ فایل‌ها فقط با endpoint دارای بررسی (`uploads` خصوصی، بررسی نوع واقعی، حذف EXIF/GPS، حجم/تعداد)؛ لاگ هر تغییر؛ لینک‌های پیامکی با شناسهٔ غیرقابل‌حدس + ورود OTP.

## ترتیب پیشنهادی ساخت
1. ماشین وضعیت + `orders` + لاگ + تنظیمات مرکزی.
2. خدمات/توکن/اقلام/شهر-منطقه + ثبت سفارش + A-13 + برنامه/مرخصی/تخصیص.
3. پنل پرستار: شروع/پایان → چک‌لیست → دارو → گزارش → صدور فاکتور.
4. فاکتور/پرداخت/اعتراض/کیف پول.
5. پرسشنامه/وفاداری/تخفیف.
6. دوره‌ای.
7. مدیریت/گزارش/پیامک‌ها (پیامک‌ها از ابتدا همراه هر رویداد).
