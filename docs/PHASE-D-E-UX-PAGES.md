# فاز D + E — تکمیل صفحات و بستن ممیزی UX

نسخه: `0.2.1` · بدون تغییر توکن رنگ/فونت/رادیوس.

## فاز D — صفحات

### بیمار
- `order-view`: deep-link با `mns_order` + `by_tracking`؛ lead تکراری needs_review حذف؛ پرداخت فقط sticky
- `invoices` / `surveys`: `panel_url` + شمارنده تب + ListItem
- `patients`: ListItem بدون `mns_service=serum` ثابت؛ `mns_patient` برای سفارش
- `invoice-view`: back هم‌نام لیست؛ دکمه PDF مخفی
- `recurring`: «ویرایش زمان» مخفی؛ برچسب «جزئیات»
- `loyalty`: قوانین تکراری حذف
- `new-order`: ترتیب ۱ خدمت → ۲ بیمار → ۳ مکان → ۴ مرور؛ کارت انتخاب بیمار؛ خلاصه sticky؛ آپلود جعلی حذف

### پرستار / عضویت
- `orders`: ListItem + counts + `panel_url`
- داشبورد: سوئیچ پذیرش فقط در availability؛ واژگان «سفارش»؛ لینک امتیازها
- `profile`: خلاصه برنامه هفتگی؛ لینک ratings
- `ratings`: ستاره + حجم نمونه
- `availability`: blocked از fixture
- `signup-stub`: PageHeader همه حالت‌ها؛ pending notice؛ approved؛ state فقط با Dev

### کاتالوگ / ادمین
- `MNS_Pages::catalog_url` / `catalog_service_url`
- قیمت خالی: «پس از ارزیابی»
- hub + stub: بنر preview؛ ردیف لینک‌دار؛ wireframe فیلتر+جدول

## فاز E — بستن ممیزی

| شناسه | وضعیت |
| --- | --- |
| SH-01…SH-06 | بسته (فاز C + D) |
| D-01…D-09 | بسته |
| NO-01…NO-06 | بسته (NO-05 کنترل جعلی حذف) |
| O-* · OV-* | بسته |
| P-01 · I-01 · IV-01 · R-01 · L-01 · S-01 · NF-01 | بسته |
| N-01…N-10 | بسته |
| SU-01…SU-05 | بسته در استاتیک (wiring واقعی بعدی) |
| C-01…C-03 · A-01…A-03 | بسته |

P0/P1 باقی‌ماندهٔ محصولی: wiring واقعی سفارش/عضویت/آپلود نسخه — خارج از UI استاتیک.

## مسیرهای تأیید سریع

- بیمار: dashboard · orders · order-view?mns_order=… · new-order · invoices · invoice-view · patients · surveys · recurring · loyalty
- پرستار: dashboard · orders · order-view · availability · ratings · profile
- عضویت: signup (+ `mns_state` فقط با Dev)
- کاتالوگ: archive · service
- ادمین: mns-dashboard · هر stub منو
