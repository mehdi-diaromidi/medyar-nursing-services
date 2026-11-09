# فاز C — اصلاح سیستمی UX

نسخه: `0.2.0` · بدون تغییر توکن رنگ/فونت/رادیوس.

## چه عوض شد

- `MNS_UI::nav_active` / `nav_item_is_active` · کلید bottom «more» · سایدبار parent برای order-view/invoice-view
- جداکنندهٔ خروجی‌ها + برچسب روشن «پنل مدیار» / «برداشت کیف پول»
- تلفن سایدبار: `text-align:right` + `dir=ltr`
- نام‌گذاری: داشبورد (نه خانه) در copy / titles / هدرها
- پارشال‌های مشترک: `alert-card` · `section-head` · `list-item`
- داشبورد بیمار روی کامپوننت‌ها؛ کاشی‌ها نام کامل + آیکن معنایی؛ Stamp فشرده؛ میانبر ناوبری → آمار زمینه‌ای
- سفارش‌ها: ListItem · تب با شمارنده · جستجوی زنده داخل فیلد · `MNS_Pages::panel_url`
- ثبت سفارش: stepper فشرده موبایل + progress · summary چسبنده · phone LTR · آپلود جعلی → alert
- فیكسچر تاریخ نسبی به اکنون + سال شمسی جاری در کد پیگیری

## تست پیشنهادی

- `/nursing-patient-panel/?mns_nocache=1`
- `/nursing-patient-panel/?mns_page=orders&mns_nocache=1`
- `/nursing-patient-panel/?mns_page=new-order&mns_nocache=1`
- `/nursing-patient-panel/?mns_page=loyalty&mns_nocache=1` (bottom «بیشتر» active)
