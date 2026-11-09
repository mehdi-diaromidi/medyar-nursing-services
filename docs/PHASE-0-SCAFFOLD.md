# فاز ۰ — اسکلت پلاگین (جزئیات اجرا)

منبع بالادستی: `../PLAN.md`  
الگوی فنی: `wp-content/plugins/affiliate-referral`  
هدف این سند: طوری نوشته شده که بدون ابهام قابل پیاده‌سازی باشد؛ خروجی فقط bootstrap است، نه دامنه کسب‌وکار و نه UI واقعی.

---

## 0. مرز اسکوپ

### داخل فاز ۰
- فایل bootstrap پلاگین و ثابت‌های نسخه/مسیر
- Autoloader map-based (سبک ARP)
- Singleton `MNS_Plugin` و wiring حداقلی hooks
- `MNS_Activator` / deactivation بدون ساخت جدول دامنه (جداول = فاز ۱)
- `MNS_Roles` خام: نقش پرستار + capabilityهای ادمین/پرستار/بیمار
- اسکلت پوشه‌ها و فایل‌های خالی/stub امن
- Enqueue خالی (handle ثبت می‌شود؛ CSS/JS تقریباً خالی)
- پرچم `MNS_UI_STATIC` و helper خواندنش
- `includes/config/assumptions.php` با فرض‌های موقت `PLAN.md`
- منوی ادمین stub تک‌صفحه‌ای «به‌زودی» (فقط برای تأیید فعال‌بودن)
- `index.php` خاموش‌کننده لیست‌دایرکتوری در هر پوشه عمومی
- `uninstall.php` محافظت‌شده (فعلاً چیزی پاک نمی‌کند مگر ثابت تأیید)

### خارج از فاز ۰ (عمداً نکن)
- `dbDelta` و جداول سفارش/خدمت/پرستار
- SMS / کیف پول / زرین‌پال
- صفحه قالب پنل بیمار/پرستار و CSS کامل
- Rewrite قوانین پنل
- REST routes دامنه
- Seed خدمات واقعی

---

## 1. قراردادهای نام‌گذاری (قفل)

| مورد | قرارداد |
| --- | --- |
| Directory / slug | `medyar-nursing-services` |
| Main file | `medyar-nursing-services.php` |
| Text domain | `medyar-nursing-services` |
| Class prefix | `MNS_` (مثلاً `MNS_Plugin`) |
| Function prefix | `mns_` (تقریباً فقط helpers procedural) |
| Hook prefix | `mns_` (مثلاً `mns_order_status_changed`) |
| Option keys | `mns_*` (مثلاً `mns_db_version`, `mns_settings`) |
| Capability | `manage_mns`, `mns_view_patient_panel`, `mns_view_nurse_panel`, `mns_manage_orders`, … |
| Role slug | `mns_nurse` |
| AJAX action | `mns_*` |
| REST namespace (رزرو) | `mns/v1` |
| CSS/JS handle | `mns-public`, `mns-admin` |
| CSS class BEM root | `mns-` (مثلاً `.mns-dash`) — پیاده‌سازی UI در فاز ۳ |
| i18n | همه رشته‌های کاربر با `__() / esc_html__() / esc_attr__()` و text domain بالا |
| فایل کلاس | `class-mns-{name}.php` زیر مسیر map |

کلاس‌ها WordPress-style با underscore؛ نه PSR-4 namespace واقعی (هم‌سبک ARP).

---

## 2. درخت فایل دقیق که در فاز ۰ ساخته می‌شود

```
medyar-nursing-services/
├── medyar-nursing-services.php          # bootstrap
├── uninstall.php                        # محافظت‌شده
├── PLAN.md                              # موجود
├── index.php                            # silence
├── readme.txt                           # هدر کوتاه WP.org-style (اختیاری داخلی)
├── docs/
│   ├── PHASE-0-SCAFFOLD.md              # همین فایل
│   └── (UI-UX.md later in phase 3)
├── includes/
│   ├── index.php
│   ├── class-mns-autoloader.php
│   ├── class-mns-plugin.php
│   ├── class-mns-activator.php
│   ├── class-mns-roles.php
│   ├── class-mns-assets.php              # یا public/class-mns-assets.php — یک جا، ترجیح public/
│   ├── config/
│   │   ├── index.php
│   │   └── assumptions.php              # آرایه برگشتی؛ نه کلاس اجباری
│   ├── domain/                          # فقط index.php در فاز ۰
│   │   └── index.php
│   └── integrations/                    # فقط index.php در فاز ۰
│       └── index.php
├── admin/
│   ├── index.php
│   ├── class-mns-admin.php               # init منو
│   ├── class-mns-admin-menu.php
│   ├── class-mns-admin-ui.php            # helper خالی tabs بعداً
│   ├── list-tables/
│   │   └── index.php
│   └── views/
│       ├── index.php
│       └── dashboard-stub.php           # «پلاگین فعال است»
├── public/
│   ├── index.php
│   ├── class-mns-public.php
│   ├── class-mns-assets.php
│   ├── class-mns-rewrite.php             # stub: register هیچ قانون؛ فقط کلاس آماده
│   ├── views/
│   │   ├── index.php
│   │   ├── patient/
│   │   │   └── index.php
│   │   ├── nurse/
│   │   │   └── index.php
│   │   ├── catalog/
│   │   │   └── index.php
│   │   └── partials/
│   │       └── index.php
│   └── static-fixtures/
│       └── index.php                    # محتوا در فاز ۳
├── assets/
│   ├── index.php
│   ├── css/
│   │   ├── index.php
│   │   ├── public.css                   # فقط کامنت + :root خالی `--mns-primary`
│   │   └── admin.css                    # خالی/حداقلی
│   ├── js/
│   │   ├── index.php
│   │   ├── public.js                    # IIFE خالی با `window.MNS = window.MNS || {}`
│   │   └── admin.js                     # خالی
│   ├── img/
│   │   └── index.php
│   └── fonts/
│       └── index.php
└── languages/
    └── index.php
```

قانون: هر پوشه‌ای که از وب قابل سرو شدن است `index.php` با `<?php // Silence is golden.` دارد.

---

## 3. ترتیب پیاده‌سازی (گام‌به‌گام)

### گام 0.1 — فایل اصلی

`medyar-nursing-services.php`:

```php
/**
 * Plugin Name: Medyar Nursing Services
 * Description: سامانه سفارش، تخصیص و انجام خدمات پرستاری در منزل (مدیار)
 * Version: 0.1.0
 * Author: Medyar
 * Text Domain: medyar-nursing-services
 * Requires at least: 5.8
 * Requires PHP: 8.0
 * Domain Path: /languages
 */
```

ثابت‌ها (الزامی):

| ثابت | مقدار اولیه |
| --- | --- |
| `MNS_VERSION` | `0.1.0` |
| `MNS_DB_VERSION` | `0.0.0` (جداول هنوز نیست؛ فاز ۱ به `1.0.0` می‌برد) |
| `MNS_PLUGIN_FILE` | `__FILE__` |
| `MNS_PLUGIN_DIR` | `plugin_dir_path( __FILE__ )` |
| `MNS_PLUGIN_URL` | `plugin_dir_url( __FILE__ )` |
| `MNS_PLUGIN_BASENAME` | `plugin_basename( __FILE__ )` |

پرچم استاتیک UI:

```php
if ( ! defined( 'MNS_UI_STATIC' ) ) {
	define( 'MNS_UI_STATIC', true ); // تا پایان اتصال فرانت؛ در wp-config قابل override
}
```

سپس:

1. `require_once` autoloader  
2. `MNS_Autoloader::register()`  
3. `register_activation_hook( __FILE__, array( 'MNS_Activator', 'activate' ) )`  
4. `register_deactivation_hook( __FILE__, array( 'MNS_Activator', 'deactivate' ) )`  
5. `add_action( 'plugins_loaded', … MNS_Plugin::instance()->init() )` با اولویت پیش‌فرض  

Load textdomain در `init` داخل `MNS_Plugin` (نه خیلی زود قبل از `init` در WP جدید).

### گام 0.2 — Autoloader

کپی الگوی `Arp_Autoloader`: آرایه `$map` کلاس → مسیر نسبی از ریشه پلاگین.

نقشه اولیه فاز ۰ (فقط کلاس‌های موجود):

| Class | Path |
| --- | --- |
| `MNS_Plugin` | `includes/class-mns-plugin.php` |
| `MNS_Activator` | `includes/class-mns-activator.php` |
| `MNS_Roles` | `includes/class-mns-roles.php` |
| `MNS_Admin` | `admin/class-mns-admin.php` |
| `MNS_Admin_Menu` | `admin/class-mns-admin-menu.php` |
| `MNS_Admin_Ui` | `admin/class-mns-admin-ui.php` |
| `MNS_Public` | `public/class-mns-public.php` |
| `MNS_Assets` | `public/class-mns-assets.php` |
| `MNS_Rewrite` | `public/class-mns-rewrite.php` |

`load()`: اگر کلاس در map نبود return؛ اگر فایل readable بود `require_once`.

هر کلاس جدیدی در فازهای بعد **اول به map اضافه شود**، بعد فایل.

### گام 0.3 — `MNS_Plugin`

- Singleton `instance()`  
- `init()`:
  1. `MNS_Activator::maybe_upgrade()` (در فاز ۰ practically no-op روی جداول؛ فقط option نسخه را هم‌تراز می‌کند)
  2. `MNS_Roles::register()`
  3. `load_plugin_textdomain( 'medyar-nursing-services', false, dirname( MNS_PLUGIN_BASENAME ) . '/languages' )` روی `init` جدا یا همین‌جا اگر مطمئنیم بعد از `plugins_loaded` هستیم — ترجیح: `add_action( 'init', …, 0 )` برای textdomain
  4. اگر `is_admin()` → `MNS_Admin::instance()->init()`
  5. `MNS_Public::instance()->init()`
  6. `MNS_Assets::init()` (یا از Public صدا زده شود — یک مسیر واحد؛ ترجیح Assets جدا و از Plugin صدا شود)
  7. `MNS_Rewrite::instance()->init()` (stub)

هیچ integration در فاز ۰ init نشود.

### گام 0.4 — `MNS_Activator`

**activate():**
1. `MNS_Roles::register()`
2. اگر `mns_settings` نبود → `add_option( 'mns_settings', self::default_settings() )` با آرایه مینیمال:

```php
array(
	'assignment_mode' => 'manual', // auto|manual — فاز ۷
	'ui_static'       => true,     // آینه option کنار constant
	'assumptions_ver' => 1,
)
```

3. `update_option( 'mns_db_version', MNS_DB_VERSION )`  
4. **ساختن جدول نکن**  
5. `flush_rewrite_rules( false )` فقط اگر rewrite stub فلگ «نیاز» بگذارد؛ در فاز ۰ ترجیحاً flush نکن مگر rewrite واقعی ثبت شود (از flush بیهوده پرهیز)

**deactivate():**
- `MNS_Roles::remove_caps_on_deactivate()` — فقط capهای ادمین اضافه را بردارد؛ نقش `mns_nurse` را حذف نکند (کاربران نشکنند؛ مثل ARP)

**maybe_upgrade():**
```
if get_option('mns_db_version') !== MNS_DB_VERSION → Roles::register + update option
```
فاز ۱ اینجا `create_tables` را اضافه می‌کند.

### گام 0.5 — `MNS_Roles`

ثابت‌ها:

| Constant | Value | معنی |
| --- | --- | --- |
| `ROLE_NURSE` | `mns_nurse` | نقش پرستار |
| `CAP_MANAGE` | `manage_mns` | منوی ادمین کامل |
| `CAP_MANAGE_ORDERS` | `mns_manage_orders` | رصد/تخصیص |
| `CAP_MANAGE_NURSES` | `mns_manage_nurses` | تأیید پرستار |
| `CAP_NURSE_PANEL` | `mns_view_nurse_panel` | پنل پرستار |
| `CAP_PATIENT_PANEL` | `mns_view_patient_panel` | پنل بیمار (به نقش subscriber/customer هم قابل اعطا) |

رفتار `register()`:
1. اگر نقش پرستار نبود → `add_role( ROLE_NURSE, 'پرستار', array( 'read' => true, CAP_NURSE_PANEL => true ) )` وگرنه caps را sync کن.
2. روی `administrator` همه capهای MNS را `add_cap`.
3. روی نقش‌های `customer` (اگر ووکامرس) و `subscriber`: فقط `CAP_PATIENT_PANEL` (بیمار نقش جدا نمی‌گیرد مگر بعداً لازم شود).

Helperهای فاز ۰ (بدون استفاده سنگین هنوز):
- `user_is_nurse( ?int $user_id = null ): bool`
- `user_can_manage( ?int $user_id = null ): bool` → `user_can( …, CAP_MANAGE )`

### گام 0.6 — ادمین stub

`MNS_Admin::init()` → `MNS_Admin_Menu::register()`.

منو:
- Top-level: «خدمات پرستاری»  
- slug: `mns-dashboard`  
- capability: `manage_mns`  
- icon: `dashicons-heart` (یا `dashicons-plus-alt`)  
- position: حدود `27` (کنار ARP روی 26)  
- callback: view `admin/views/dashboard-stub.php`

محتوای stub:
- عنوان  
- متن: نسخه پلاگین، وضعیت `MNS_UI_STATIC`، یادآوری که فاز ۰ است  
- بدون فرم ذخیره

`MNS_Admin_Ui`: کلاس خالی با متد رزرو `tabs( array $tabs, string $current )` که بعداً مثل ARP پر می‌شود — در فاز ۰ می‌تواند فقط `return` یا render ساده نداشته باشد.

Enqueue ادمین: فقط روی صفحات `toplevel_page_mns-dashboard` (و بعداً `mns_*`) → `mns-admin` css/js با `MNS_VERSION`.

### گام 0.7 — Public + Assets stub

`MNS_Public::init()`:
- فعلاً هیچ shortcode/template؛ فقط هوک خالی یا کامنت TODO فاز ۳
- اختیاری: shortcode `[mns_status]` که در فرانت فقط اگر `current_user_can` و debug، «MNS OK» نشان دهد — **ترجیح: نساز** تا سطح حمله/سرویس عمومی اضافه نشود

`MNS_Assets::init()`:
- `wp_enqueue_scripts` → اگر بعداً شرط صفحه MNS؛ در فاز ۰ **هیچ صفحه‌ای enqueue نکند** مگر ثابت `MNS_ENQUEUE_ALWAYS` (پیش‌فرض false)
- متدهای آماده: `enqueue_public()`, `enqueue_admin()` برای فراخوانی از منو/صفحات بعدی
- `wp_localize_script( 'mns-public', 'mnsPublic', array( 'ajaxUrl' => admin_url( 'admin-ajax.php' ), 'restUrl' => rest_url( 'mns/v1' ), 'nonce' => …, 'uiStatic' => (bool) MNS_UI_STATIC ) )` فقط وقتی اسکریپت واقعاً enqueue شد

`public.css` حداقل:

```css
:root {
	--mns-primary: var(--primary-100, #0272b1);
}
```

`public.js` حداقل:

```js
(function () {
	'use strict';
	window.MNS = window.MNS || {};
})();
```

### گام 0.8 — Rewrite stub

`MNS_Rewrite`:
- `init()` خالی یا `add_action( 'init', array( $this, 'register' ) )` با `register()` خالی و کامنت: query vars `mns_panel`, `mns_page` در فاز ۳
- متد استاتیک رزرو `patient_url( $page )` / `nurse_url( $page )` که فعلاً `home_url( '/' )` برمی‌گردانند یا `#` — بهتر: هنوز پیاده نشوند تا قرارداد URL در فاز ۳ قفل شود

### گام 0.9 — Assumptions

`includes/config/assumptions.php` برگرداند:

```php
return array(
	'nurse_must_accept_assignment' => false,
	'nurse_share_mode'             => 'percent', // percent|fixed
	'nurse_share_percent'          => 70,        // موقت
	'invoice_after'                => 'checklist', // checklist|report
	'survey_after'                 => 'service_end',
	'pricing_night_enabled'        => false,
	'pricing_holiday_enabled'      => false,
	'pricing_urgent_enabled'       => false,
	'patient_cancel_until'         => 'in_progress', // قبل از این وضعیت
);
```

Helper در Roles یا یک `MNS_Config::assumption( $key, $default )` — اگر کلاس Config می‌سازیم به autoload map اضافه شود. در فاز ۰ یک تابع `mns_assumption( string $key, $default = null )` در فایل assumptions یا کلاس نازک `MNS_Config` کافی است.

### گام 0.10 — uninstall.php

```php
defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

// فاز ۰: پاکسازی مخرب انجام نشود مگر:
if ( ! defined( 'MNS_DELETE_DATA_ON_UNINSTALL' ) || ! MNS_DELETE_DATA_ON_UNINSTALL ) {
	return;
}
// بعداً: حذف options، جداول، caps — فاز ۱۱
```

### گام 0.11 — Helper پرچم استاتیک

در `MNS_Plugin` یا `MNS_Config`:

```php
public static function is_ui_static(): bool {
	if ( defined( 'MNS_UI_STATIC' ) && ! MNS_UI_STATIC ) {
		return false;
	}
	$settings = get_option( 'mns_settings', array() );
	if ( isset( $settings['ui_static'] ) ) {
		return (bool) $settings['ui_static'];
	}
	return (bool) MNS_UI_STATIC;
}
```

اولویت پیشنهادی: ثابت `false` در wp-config همیشه برنده است (خاموش‌کردن اضطراری fixture در پروداکشن).

---

## 4. امنیت و کیفیت (چک‌لیست حین کدنویسی)

- [ ] همه PHP: `defined( 'ABSPATH' ) || exit;` (به‌جز uninstall که `WP_UNINSTALL_PLUGIN` دارد)
- [ ] هیچ `echo` دیباگ / `var_dump`
- [ ] منوی ادمین پشت `manage_mns`
- [ ] نسخه روی enqueue برای cache-bust
- [ ] بدون ویرایش فایل‌های تم یا پلاگین دیگر
- [ ] WPCS فاصله/brace هم‌سبک ARP (tabs در PHP اگر ARP tab است — از همان فایل‌های مرجع پیروی کن)

---

## 5. معیار پذیرش فاز ۰

| # | معیار | روش تأیید |
| --- | --- | --- |
| 1 | پلاگین در لیست Plugins دیده می‌شود | WP Admin → Plugins |
| 2 | Activate بدون fatal / notice قرمز | Activate |
| 3 | نقش `mns_nurse` ساخته شده | Users → یا `wp role list` |
| 4 | administrator دارای `manage_mns` است | بررسی cap |
| 5 | منوی «خدمات پرستاری» دیده می‌شود و stub رندر می‌شود | کلیک منو |
| 6 | Deactivate نقش پرستار را پاک نمی‌کند | Deactivate + چک نقش |
| 7 | Reactivate بدون خطا | دوباره Activate |
| 8 | `MNS_UI_STATIC === true` و `mns_settings` موجود است | کد/option |
| 9 | درخت پوشه مطابق بخش ۲ کامل است | فایل‌سیستم |
| 10 | هیچ جدول `wp_mns_*` ساخته نشده | DB |

---

## 6. بعد از پذیرش — ورودی فاز ۱

فاز ۱ روی همین اسکلت سوار می‌شود:
- پر کردن `MNS_Activator::create_tables()`
- `MNS_DB_VERSION = 1.0.0`
- repos زیر `includes/domain` یا `includes/db`
- گسترش caps اگر لازم

فاز ۰ را merge/commit جدا نگه دار تا ریگریشن bootstrap مشخص باشد.

---

## 7. تخمین حجم کار اجرا

| بخش | تقریبی |
| --- | --- |
| درخت + indexها + assets خالی | کوتاه |
| bootstrap + autoload + plugin + activator + roles | اصلی |
| admin stub + assets hooks | کوتاه |
| assumptions + uninstall + پذیرش دستی | کوتاه |

یک نشست پیاده‌سازی متمرکز کافی است؛ بدون UI فاز ۳ و بدون اسکیما.

---

## 8. ترتیب commit پیشنهادی (وقتی کاربر خواست)

1. `chore(mns): scaffold plugin bootstrap and folder tree`  
2. (اختیاری جدا) `feat(mns): admin stub menu and roles registration`

تا وقتی کاربر صریحاً commit نخواهد، فقط فایل‌ها ساخته شوند.

---

*پایان سند فاز ۰. پیاده‌سازی فقط پس از تأیید شروع این فاز.*
