# مرجع فنی پلاگین MNS (وضعیت کد، نسخهٔ 0.5.13)

مسیر: `wp-content/plugins/medyar-nursing-services/` · پیشوند `MNS_`/`mns_`/`mns-` · text domain `medyar-nursing-services` · PHP 8.0+ · WP 5.8+.

## ثابت‌ها و بوت
`MNS_VERSION '0.5.14'` · `MNS_DB_VERSION '0.0.0'` · `MNS_PLUGIN_FILE/DIR/URL/BASENAME` · `MNS_UI_STATIC true` (اگر از قبل تعریف نشده؛ `false` در wp-config همیشه برنده). `MNS_DEV_TOOLS` فقط چک می‌شود (false = خاموش). اختیاری `MNS_DELETE_DATA_ON_UNINSTALL`.
بوت: `MNS_Autoloader::register()` → activation/deactivation → `plugins_loaded` → `MNS_Plugin::instance()->init()` (maybe_upgrade → roles → textdomain → admin → public → assets → rewrite).
**هیچ جدول دیتابیسی ندارد.** `MNS_Rewrite` خالی است (URL تمیز نداریم؛ همه از `$_GET`).

## کلاس‌ها
- `includes/`: `MNS_Plugin`, `MNS_Activator`, `MNS_Roles`, `MNS_Config` (+ `mns_assumption()`), `domain/MNS_Order_Status`.
- `admin/`: `MNS_Admin`, `MNS_Admin_Menu`, `MNS_Admin_Ui`.
- `public/`: `MNS_Public`, `MNS_Assets`, `MNS_Rewrite`, `MNS_Pages`, `MNS_Page_Templates`, `MNS_Static_Fixtures`, `MNS_Format`, `MNS_Copy`, `MNS_Dev`, `MNS_UI`, `MNS_Patient_Orders`, `MNS_Patient_Invoices`.
- `MNS_Pages`: `panel_url($panel,$page)`, `patient_slugs()`, `nurse_slugs()`, `current_panel()`, `current_page()`, `catalog_url()`, `catalog_service_url()`.
- `MNS_Format`: `digits()` (ارقام فارسی)، `number()` (جداکنندهٔ `٬`)، `toman()`، `mobile()`، `g2j()`/`j2g()`، `month_names()`، `weekday_names()` (شنبه‌اول)، `jdate()`, `rel()`, `human()`, `js_calendar()`.
- `MNS_UI`: `page_header()`, `alert()`, `alert_card()`, `section_head()`, `list_item()`, `status_badge()`, `order_timeline()`, `empty_state()`, `error_state()`, `skeleton()`, `support_call()`, `support_phone()` (پیش‌فرض `02188607015`، override با `mns_settings[support_phone]`), `support_tel_href()`, `stamp_card()`, `bottom_nav()`, `boot_icons()`, `partial()`.
- `MNS_Copy::t('key')` (دیکشنری متون با کلید نقطه‌دار)، `MNS_Dev::is('state')`, `MNS_Static_Fixtures::get('patient/orders')`, `MNS_Config::setting()`.
- آیکون‌ها: `mns_icon($name)` در `public/views/partials/icons.php` (SVG 24، stroke 1.8): home, plus, list, users, invoice, calendar, star, heart, grid, user, user-check, wallet, clock, logout, external, chevron, chevron-down, check, check-circle, x, x-circle, alert, info, phone, copy, lock, camera, upload, file, trash, edit, map-pin, activity, pill, flag, shield, search, more, timer, repeat, bandage, droplet, filter, headphones, download, card, user-plus, file-text, doc-list, gift.

## مسیریابی
- page templateها (`public/templates/`): `mns-patient.php`, `mns-nurse.php`, `mns-catalog.php`, `mns-nurse-signup.php`.
- در `mns-patient.php` متغیر `$page` باید **بعد از** `get_header()` مقداردهی شود (وردپرس `$page` سراسری را بازنویسی می‌کند). حالت چاپ فاکتور (`invoice-view` + `mns_print`) خروجی مستقل `invoice-print.php`.
- اسلاگ‌های بیمار: `dashboard`, `new-order`, `orders`, `order-view`, `patients`, `invoices`, `invoice-view`, `recurring`, `loyalty`, `surveys` (+ `ui-kit` در dev). نامعتبر → `not-found`. اسلاگ‌های پرستار: `dashboard`, `orders`, `order-view`, `availability`, `ratings`, `profile` (+ `ui-kit`).
  > اسلاگ‌های جدید لازم (طبق طراحی): پرستار `checklist`, `drug`, `report`, `regions`, `schedule`(?)، `wallet`؛ بیمار `survey` (P-52).
- bottom-nav بیمار: داشبورد، سفارش‌ها، سفارش جدید (اصلی)، فاکتورها، بیشتر؛ روی `new-order` و `ui-kit` مخفی.
- پارامترها: `mns_panel`, `mns_page`, `mns_state`, `mns_order`, `mns_invoice`, `mns_patient`, `mns_service`, `mns_tab`, `mns_p`, `mns_print`, `mns_autoprint`, `mns_result` (success/failed/pending), `mns_dispute`, `mns_paid`, `mns_repeat`, `mns_same_nurse`, `mns_recurring`, `mns_survey`, `mns_form`، فیلترها `q, from, to, service, recurring, type, sort, order`.
- nonceها: `mns_public`, `mns_order_cancel`, `mns_invoice_dispute`.

## حالت‌های Dev (`?mns_state=`)
فقط وقتی `MNS_DEV_TOOLS` ≠ false، `ui_static` روشن، و کاربر `manage_options`. سوییچر شناور در فوتر + لینک UI kit. فیلتر `mns_dev_states`.
- پایه: `default, loading, empty, error, long`. dashboard: `unpaid, active, survey, new_user` · order-view: ۱۰ وضعیت · new-order: `success, no_nurse` · patients: `form` · invoice-view: `dispute, paid, success, failed, pending, guest, not_owner` · invoices/orders: `filter-empty, tab-empty` · loyalty: `full` · surveys: `submitted` · nurse dashboard: `offline` · nurse order-view: `assigned, in_progress, service_ended, needs_review` · signup: `form, pending, rejected, suspended, approved`.

## Fixtureها (`public/static-fixtures/`)
patient: `nav, profile` (سارا محمدی، phone، wallet_balance), `dashboard, new_order, orders, order_view, invoices, patients_list, recurring, loyalty, surveys` · nurse: `nav, profile, profile_full, dashboard, orders, order_view, availability, ratings` · catalog: `categories` (۶)، `services`, `service_sample` · admin: `admin/static-fixtures/hub.php`. تاریخ‌ها نسبت به «اکنون».

## نقش‌ها، تنظیمات، فرض‌ها
- نقش `mns_nurse`: `read`, `mns_view_nurse_panel`. capها: `manage_mns`, `mns_manage_orders`, `mns_manage_nurses`, `mns_view_nurse_panel`, `mns_view_patient_panel`.
- option `mns_settings`: `assignment_mode='manual'`, `ui_static=true`, `assumptions_ver=1`, (`support_phone`). option `mns_db_version`.
- فرض‌های موقت `includes/config/assumptions.php`: `nurse_must_accept_assignment=false` ✔(هم‌خوان با «رد ندارد»), `nurse_share_mode='percent'`, **`nurse_share_percent=90`** ✔ (0.5.14), **`invoice_after='all_required_steps'`** ✔ (0.5.14), `survey_after='service_end'` ✔, قیمت شب/تعطیل/فوری خاموش, `patient_cancel_until='in_progress'` ✔.

## Assets
- `MNS_Assets::enqueue_public()` فقط روی templateهای MNS: `mns-public` (css/js، نسخه `MNS_VERSION`) + `mnsPublic` (ajaxUrl, restUrl `mns/v1`, nonce, uiStatic, devTools, i18n, calendar, supportTel).
- روی بیمار `orders/invoices/invoice-view`: کیت ARP (`arp-public`) + `pw-datepicker-js`؛ dequeue `drplus-pdp`/`drplus-pd`؛ `arp-public` وابستگی `mns-public`.
- `public.js` (IIFE، بدون وابستگی؛ boot روی DOMContentLoaded یا فوراً — سازگار با تأخیر JS در WP Rocket): `initDrawer, initIranMobile, initCustomSelects, initMockForms, initSwitches, initAvailabilityCalendar, initStars, initRowToggles, initOrderForm, initMockActions, initDevTools, initAlerts, initDialogs` (دیالوگ به body منتقل), `initCopyButtons` (`[data-mns-copy]`), `initLiveSearch`, `initOrdersList`, `initInvoiceView`.
- `public.css` (~۶۹۵۰ خط): بخش‌ها: پوسته/سایدبار، کامپوننت‌های فاز ۱، هدر صفحه، داشبورد، My orders (`.mns-orders/.mns-olist/.mns-order`)، Invoices (`.mns-inv*/.mns-invd*`)، Order wizard (`.mns-owizard*, .mns-osum*, .mns-patient-cards, .mns-date-rail, .mns-slot-chips, .mns-nurse-gender, .mns-avail--green|yellow|red, .mns-qty, .mns-upload-zone, .mns-review-edit, .mns-otp, .mns-oconfirm*, .mns-owizard__mob-bar`). توکن‌ها: `ui-design-system.md`.

## منوی ادمین
«خدمات پرستاری» (`mns-dashboard`, cap `manage_mns`, position 27): پیشخوان، رصد مرکزی (`mns-monitor`)، تخصیص، عضویت پرستاران، سفارش تلفنی، مالی و گزارش، تنظیمات — همه stub.

## بدهی فنی شناخته‌شده
`MNS_Rewrite` خالی؛ هیچ جدول؛ همه fixture؛ `UI-UX.md` توکن‌هایی متفاوت با کد دارد (success، urgent، 900/1200).
