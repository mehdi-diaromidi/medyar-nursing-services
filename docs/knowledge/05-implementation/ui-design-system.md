# سیستم طراحی UI (MNS)

## اصول
مرجع UX = ARP؛ طرح PNG قانون است؛ توکن‌های سایت را عوض نکن؛ RTL؛ ارقام فارسی؛ تاریخ شمسی؛ «تومان»؛ پوسته: سایدبار دسکتاپ / drawer موبایل / bottom-nav؛ ناوبری همهٔ صفحات دقیقاً مثل داشبورد؛ گرافیک نساز (placeholder هم‌اندازه).

## توکن‌ها (`public.css`)
`--mns-primary: var(--primary-100, #0272b1)` · `--mns-primary-soft` · `--mns-primary-hover #015a8c` · `--mns-navy #114b5f` · `--mns-bg #f7f7f7` · `--mns-card #fff` · `--mns-text #383838` · `--mns-heading #373434` · `--mns-muted #a6a6a6` · `--mns-muted-strong #747474` · `--mns-border #e6e6e6` · `--mns-radius 16px` · `--mns-radius-shell 24px` · `--mns-success #16a34a/#dcfce7` · `--mns-danger #d60043/#fde8ec` · `--mns-warn #b7791f/#fff5e0` · `--mns-field-min-h 50px` · `--mns-touch-min 44px` · `--mns-space-1..8` (4…48px) · `--mns-sidebar-width 306px` · `--mns-font: var(--main-font, Tahoma…)`.
بریک‌پوینت‌ها: 1100، 1023، 991، 767 (موبایل)، 640، 480، min 768؛ container query `mns-olist` (min-width 740px).

## قواعد تعاملی
- فیلد ≥16px (ضد زوم iOS)، هدف لمسی ≥44px، بدون اسکرول افقی، کنتراست ≥4.5:1 (WCAG 2.2 AA)، حرکت 100–400ms با `prefers-reduced-motion`.
- وضعیت: متن + رنگ (نه فقط رنگ). رنگ معنادار: سبز کامل، آبی در حال انجام، **نارنجی نیاز به پرداخت (تنها عنصر پررنگ)**، قرمز مشکل/لغو.
- هر دکمهٔ ثبت بعد از لمس غیرفعال + کلید یکتا سرور. خطا زیر خود فیلد.
- همهٔ حالت‌ها: خالی، skeleton (نه spinner)، خطا با «تلاش دوباره» بدون پاک شدن فیلتر/پیش‌نویس، آفلاین، اولین استفاده.
- `[hidden]` همیشه `display:none !important` (قالب `section/aside{display:block}` و دیالوگ‌های flex را می‌شکند).
- دیالوگ/backdrop به `body` منتقل شود؛ سایدبار را reparent نکن.
- پیش‌نویس‌ها در `localStorage` با try/catch.
- مرحله‌ای/چندبخشی موبایل: آکاردئون با نقطهٔ نارنجی برای الزامی ناتمام؛ نوار پایین ثابت (۶۴px بالای bottom-nav در ویزارد).

## کیت ARP که استفاده می‌شود
`.arp-dash`, `select[data-arp-select]` → `.arp-select__*`, `[data-arp-jdate]` (+ `$.fn.persianDatepicker`، تم `pdp-arp`), `.arp-refs-filters`, `.arp-table`, `.arp-refs-pager`, `arp-refs__head`, `arp-card__head`, `arp-btn-outline`. بدون ARP: select/تاریخ بومی.

## اندازه‌های PNG
دسکتاپ 1440 (PNG با ضریب 1.5 = 2160/2400)، موبایل 390؛ فونت PNGها DejaVu است — فونت واقعی سایت (IRANYekanXFANum) استفاده شود.

## فهرست PNG
`desgin/png/ثبت سفارش/`: `medyar-desktop-1-patient.png` (2400×2592), `…2-time-place` (2792), `…3-service-details` (2568), `…4-review` (2472), `…5-confirmation` (2288), `medyar-order-mobile.png` (۴ گوشی + «سه حالت بررسی دسترسی»). `سفارش های من/`: `medyar-my-orders-desktop.png` (2160×2666), `…-mobile.png`. `فاکتورها و پرداخت ها/`: `medyar-invoices-desktop/-mobile.png` (لیست، جزئیات، اعتراض، نتیجه + «سایر حالت‌ها»), `medyar-invoice-detail-desktop.png`, `medyar-invoice-a4.png`.
