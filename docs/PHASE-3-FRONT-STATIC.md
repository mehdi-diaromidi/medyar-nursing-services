# فاز ۳ — فرانت استاتیک (چک‌لیست اجرا)

پیش‌نیاز آماده در ریپو: [`UI-UX.md`](UI-UX.md) · توکن CSS · Fixtures loader · Page templates · شِل‌های خالی.  
مرجع رفتار: `affiliate-referral/UI-UX.md`.  
ادمین اپ خارج از اسکوپ این فاز است.

## پرچم‌ها

- `MNS_UI_STATIC` / `MNS_Plugin::is_ui_static()` → fixture
- Enqueue فرانت فقط روی صفحات قالب MNS

## ترتیب پیاده‌سازی پیشنهادی

1. تکمیل CSS شِل + field + table + notice + status (پورت از ARP با rename)
2. JS: drawer · select · iran-mobile · form busy · copy (list AJAX mock بعد از اولین لیست)
3. قالب‌ها و روتینگ `mns_panel` / `mns_page`
4. کاتالوگ استاتیک
5. پنل بیمار صفحه به صفحه
6. عضویت پرستار + پنل پرستار
7. حالت‌های empty/error/loading روی همه
8. پذیرش بصری دسکتاپ + ≤767

## قرارداد DTO

کلیدهای fixture اولیه:

- `patient.nav` · `patient.dashboard` · `patient.orders` · `patient.order_detail`
- `nurse.nav` · `nurse.dashboard` · `nurse.orders`
- `catalog.categories` · `catalog.service_sample`
- `meta.statuses` · `meta.cities`

هر کلید آرایه PHP برمی‌گرداند؛ view فقط کلیدهای مستند را می‌خواند.

## پذیرش فاز ۳

- تمام صفحات UI-UX.md با fixture قابل مرور
- بدون نوشتن سفارش واقعی در DB
- degrad بدون JS برای فرم/فیلتر پایه
- سند UI-UX پیوست فیلدها تکمیل شده
