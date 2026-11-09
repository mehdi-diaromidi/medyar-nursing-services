# نقشهٔ مرجع UX — affiliate-referral → medyar-nursing-services

فاز A · فقط مطالعه · بدون تغییر کد محصول.  
منبع: `wp-content/plugins/affiliate-referral` (+ سند `UI-UX.md` همان پلاگین) · تأیید بصری live روی `https://develop.medyarco.com/partner/` (۱۲۸۰ و ۳۷۵).

**دامنهٔ مرجع تأییدشده توسط صاحب پروژه:** همهٔ صفحات **سمت کاربر** ARP قابل استنادند، شامل: شِل/`dashboard`/`submit`/`referrals`/`commissions`/`invited`/`profile`/`signup`(+pending/rejected/already) و `myaccount/invited-colleagues`. ادمین ARP فقط برای صفحات ادمین MNS.

**تصاویر گرافیکی:** ایجنت تصویر نمی‌سازد. جای خالی بماند؛ پس از اتمام کارت/بخش مربوط، پرامت ساخت تصویر جداگانه تحویل صاحب پروژه می‌شود.

**قواعد استفاده در فازهای بعدی**
1. قواعد کسب‌وکار بریف پرستاری بر الگوهای ARP مقدم‌اند.
2. الگو = رفتار/سلسله‌مراتب/تعامل؛ نه کپی کلاس/CSS و نه وابستگی runtime.
3. ظاهر بصری MNS (توکن‌ها، رنگ، فونت، آیکن‌ست) دست‌نخورده می‌ماند؛ فقط ساختار و منطق UX هم‌تراز می‌شود.

---

## ۱. قالب کلی صفحه

| جنبه | الگوی ARP | چرا کار می‌کند | فایل / کامپوننت | موبایل |
| --- | --- | --- | --- | --- |
| عرض محتوا | `.arp-page-template__inner` = `min(1280px, 100% − 32px)`؛ پنل داخلش grid سایدبار+مین | محتوا از لبه‌ها فاصله دارد و روی عریض کش نمی‌آید | `assets/css/public.css` · templates `arp-dashboard.php` / `arp-signup.php` | ≤767: عرض `100%−24px` · margin عمودی کمتر |
| شِل | `.arp-dash` grid دو ستون · gap ۲۴ · سایدبار ۳۰۶px · main `1fr` | تشخیص دائمی «کجا هستم» + فضای کار جدا | `layout.php` + CSS `.arp-dash` | ≤767: تک‌ستون؛ سایدبار drawer |
| پس‌زمینه | سایدبار سفید/کارت؛ هدر سایدبار primary؛ main `#f7f7f7` radius ۲۴ | تضاد کافی: مین خاکستری، کارت سفید روی مین — بدون پنل خاکستریِ تودرتوی دوم | `.arp-dash__main` · `.arp-card` | همان؛ padding مین ۱۲–۲۰ |
| کارت | سفید · border `#e6e6e6` · radius ۱۶ · **بدون سایه** · padding ۱۶–۱۸ | سطح‌ها با border جدا می‌شوند نه با shadow یا کانتینر خاکستری اضافه | `.arp-card` | padding کمتر روی profile card |
| فاصله عمودی بدنه | `.arp-dash-body` flex · gap **۲۲** | ریتم ثابت بین بخش‌ها؛ هیچ عنوانی به بخش قبلی نمی‌چسبد | `page-dashboard.php` | همان gap |
| تودرتویی | حداکثر: شِل → کارت → محتوای کارت (۲ سطح محتوایی) | حس «اپ تخت»؛ جلوگیری از کارت-در-کارت خاکستری | همهٔ `page-*` | — |

**نکته برای MNS:** main خاکستری + کارت سفید مستقیم روی آن (مثل ARP). پنل خاکستریِ داخلیِ اضافه حذف/ساده شود.

---

## ۲. ناوبری

| جنبه | الگو | چرا | فایل | موبایل |
| --- | --- | --- | --- | --- |
| ساختار | `<aside>` + `<nav>` + `<ul><li><a>`؛ داخلی با `Arp_Pages::page_url`؛ خارجی با `url` ثابت | یک منوی قابل پیش‌بینی؛ deep link با `?arp_page=` | `layout.php` | drawer از راست (RTL) |
| ترتیب | داشبورد → اقدام اصلی ثبت → لیست‌ها → مالی خروجی → پروفایل → پنل مدیار → خروج | اقدام اصلی نزدیک بالای منو؛ خروجی‌ها پایین و جدا | همان | همان ترتیب |
| Active | `li.is-active` · `a` bg primary · متن سفید · font-size ۱rem · بدون فلش · `aria-current="page"` | یک شکل واحد در همه صفحات؛ فاصله/padding ثابت ۸×۱۶ | CSS nav | همان داخل drawer |
| Hover (غیر active) | bg primary-soft · padding ۸×۱۶ · فلش مربعی ظاهر می‌شود | بازخورد بدون قاطی‌شدن با active | CSS | روی لمس کم‌کاربرد |
| خارجی | «برداشت کیف پول» · «پنل مدیار» · بدون `is-active` · `target="_self"` | مشخص است که از پنل خارج می‌شوی | layout menu items با `url` | — |
| خروج | `li.arp-dash__nav-logout` · رنگ danger · hover bg صورتی کم‌رنگ | جدا از آیتم‌های عادی | layout | — |
| هویت سایدبار | badge نقش · آواتار · نام · تلفن · (اختیاری) کیف پول · آیکن‌های تزئینی | زمینهٔ کاربر بدون تکرار در هر صفحه | `.arp-dash__sidebar-header` | همان در drawer + دکمه بستن |

**تلفن در هدر سایدبار:** `direction: ltr` روی `.arp-dash__user-phone` (مرکز ستون آبی؛ بدون text-align چپ جدا).  
**تلفن در کارت پروفایل داشبورد:** `direction: ltr; text-align: right` تا رقم LTR باشد ولی بلوک با نام راست‌چین بماند — **الگوی الزامی برای MNS**.

---

## ۳. هدر صفحه (PageHeader)

| جنبه | الگو | چرا | فایل | موبایل |
| --- | --- | --- | --- | --- |
| ساختار | `.arp-refs__head` → intro → `h1.arp-refs__title` (آیکن دایره‌ای + متن) + `p.arp-refs__lead` | عنوان = نام منو؛ lead کوتاه و واقعی | `page-referrals.php` · `page-submit.php` · `signup.php` | عنوان ۱.۱۲۵–۱.۲۵rem |
| اقدام اصلی در هدر | معمولاً **نیست**؛ CTA ثبت در داشبورد (کارت پروفایل موبایل) یا بنر پایین لیست | جلوگیری از تکرار CTA ناوبری | `page-dashboard` profile CTA · `arp-refs-cta` | CTA پروفایل فقط ≤۷۶۷ |
| Breadcrumb | ندارد؛ برگشت با منو / مرورگر | پنل تخت depth کم | — | — |
| آیکن عنوان | `drplus-simple-icon-wrap icon-has-bg` | نشانهٔ بصری صفحه بدون شلوغی | صفحات لیست/فرم | کوچکتر |

---

## ۴. داشبورد

ترتیب ثابت در `page-dashboard.php`:

1. **کارت هویت فشرده** (آواتار + نام + تلفن) + CTA «ثبت معرفی بیمار» فقط موبایل  
2. **کارت دعوت** (اگر لینک هست) — ابزار ثانویه نزدیک بالا  
3. **برد متریک** — عدد قهرمان مالی اول، سپس شمارش‌ها با نقطهٔ رنگی وضعیت  
4. **نمودارها** (وضعیت | روند) — بینش، نه اقدام  
5. **جدول آخرین موارد** + `arp-btn-outline` «مشاهده همه» در **همان خط عنوان** (`.arp-card__head`)

| قاعده | جزئیات |
| --- | --- |
| یک اقدام اصلی | ثبت معرفی — در منو همیشه؛ روی موبایل در کارت هویت هم هست |
| اعداد | `Arp_Format::money` / `fa` · واحد «تومان» جدا کنار رقم |
| Section link | عنوان بخش راست · «مشاهده همه» چپ همان ردیف با فاصلهٔ معقول (flex space-between + gap ۱۰) |
| Empty جدول | یک سلول `.arp-table__empty` با جملهٔ کوتاه |

**برای MNS:** ترتیب پیشنهادی هم‌تراز: اقدام فوری (فاکتور پرداخت‌نشده / شروع خدمت) → میانبر ثبت سفارش → پیش‌روها + «مشاهده همه» → وفاداری فشرده → (نه تکرار ناوبری خالص). نمودارها الزامی نیستند (بریف متفاوت).

---

## ۵. لیست‌ها و جدول‌ها

| جنبه | الگو | چرا | فایل | موبایل |
| --- | --- | --- | --- | --- |
| فیلتر | کارت جدا `.arp-refs-filters` · `role="search"` · GET · AJAX با `data-arp-list` | فیلتر نزدیک نتایج؛ autosubmit وضعیت/خدمت | `page-referrals.php` | تاریخ و جستجو full-width؛ selectها ۵۰/۵۰ |
| جستجو | `type="search"` داخل همان control با دکمهٔ آیکن submit · cancel وب‌کیت مخفی | یک واحد بصری؛ نه دکمهٔ متنی جداافتاده | CSS field action | full width |
| Reset فیلتر | pill قرمز «پاک کردن فیلترها» فقط وقتی فیلتر فعال | وضعیت فعال دیده می‌شود | `filters-reset.php` | — |
| جدول | `.arp-table` در `.arp-table-wrap` · th/td وسط · hover ردیف · مبلغ bold+واحد | اسکن ستونی؛ overflow-x روی موبایل | partials results | اسکرول افقی |
| ردیف جزئیات | expandable: کلیک ردیف / toggle · `aria-expanded` · chevron | جزئیات بدون ترک صفحه | `.arp-table--expandable` | همان |
| صفحه‌بندی | info «نمایش x تا y از z» + prev/pages/next | شفافیت حجم | `pager.php` | compact pages |
| CTA پایین لیست | بنر `.arp-refs-cta` با یک دکمهٔ primary «ثبت … جدید» | مسیر بعدی بعد از مرور لیست | referrals | تصویر/padding تنظیم‌شده |

**تب با شمارنده:** در فرانت همکار کمتر؛ در ادمین `views` با count فارسی. برای MNS (سفارش‌ها/فاکتورها) الگوی ادمین ARP + نیاز بریف → تب + شمارنده توصیه می‌شود حتی اگر فرانت ARP جدول‌محور است.

---

## ۶. Badge و وضعیت‌ها

| جنبه | الگو | چرا | فایل |
| --- | --- | --- | --- |
| شکل | pill `.arp-status` · متن + رنگ · radius ۹۹۹ · weight ۶۰۰ · ~۰.۸۱۲۵rem | خوانا کنار سلول جدول | public.css |
| معنا | waiting / progress / accepted / done / cancel / failed + dynamic از ادمین | یک مفهوم = یک رنگ | `Arp_Ui::status_style` |
| کنتراست | bg نرم رنگی + متن تیرهٔ هم‌خانواده | از خاکستری روی خاکستری پرهیز | پیوست A در UI-UX.md |

**MNS:** StatusBadge مشترک با اندازه/کنتراست حداقل هم‌سطح `.arp-status`؛ نه badge ریز کم‌رنگ دور از عنوان.

---

## ۷. فرم‌ها

| جنبه | الگو | چرا | فایل | موبایل |
| --- | --- | --- | --- | --- |
| PageHeader + lead | مثل لیست | زمینه قبل از فیلدها | submit / signup / profile | — |
| شبکه | `.arp-submit__grid`: فرم گسترده + کارت how کنار (دسکتاپ) | how راهنمای جریان است نه stepper تعاملی | `page-submit.php` | ≤۱۱۰۰ تک‌ستون؛ how زیر فرم |
| فیلد | `.arp-field-block` داخل label · control min-h ۵۰ · radius ۱۶ · آیکن primary | کلیک لیبل = فوکوس؛ هدف لمسی خوب | CSS + views | row ۲ستونه → ۱ستون ≤۶۴۰ |
| الزامی | `*` با کلاس req | کوتاه | — | — |
| موبایل ایران | `data-arp-iran-mobile` · نرمال‌سازی زنده · `dir=ltr` · `inputmode=tel` | خطای کم | public.js | — |
| اعتبارسنجی | `.is-invalid` روی block · focus اولین خطا · پاک‌شدن invalid با input | بازخورد درجا | JS AJAX forms | — |
| Submit | primary full · min ۵۲ · loading spinner · `aria-busy` · جلوگیری double-submit | وعده و تحویل | `.arp-submit-btn` | sticky لازم نیست (فرم کوتاه) |
| How card | عنوان «چطور کار می‌کنید؟» · ۴ گام آیکن+عنوان+توضیح · CTA متنی پایین | آموزش بدون تکرار منو | `.arp-how-steps` | زیر فرم |

**تفاوت مهم با ویزارد MNS:** ARP ثبت معرفی **تک‌صفحه‌ای** است؛ how = توضیح پس از ثبت، نه stepper مرحله‌ای. برای `new-order` چندمرحله‌ای، الگوی هدف پرامت (Stepper) مقدم است؛ از ARP فقط PageHeader، field-block، how-as-aside، و رفتار loading/validation قرض گرفته شود.

---

## ۸. مودال / toast / تأیید

| جنبه | الگو ARP | کاربرد در MNS |
| --- | --- | --- |
| Flash شِل | `.arp-notice` سبز بعد از `?arp_msg=` | موفقیت ثبت / ذخیره پروفایل |
| Notice خطا | `--error` · `role=alert` | خطاهای فرم |
| Live form notice | `.arp-form-notice` · scrollIntoView | AJAX |
| Confirm | `confirm()` بومی برای حذف آواتار؛ ادمین `[data-confirm]` | اقدام‌های برگشت‌ناپذیر پرستاری (لغو، ثبت نهایی گزارش) → ConfirmDialog اختصاصی MNS با پیامد صریح (ARP تأیید غنی ندارد؛ الگوی حداقل + ارتقا طبق پرامت) |
| Toast کپی | tip «کپی شد» · `aria-live` | کد پیگیری / لینک |
| Dialog اختصاصی | تقریباً ندارد | MNS می‌تواند partial موجود را با الگوی proximity نگه دارد |

---

## ۹. Loading / empty / error

| حالت | ARP | فایل |
| --- | --- | --- |
| Loading لیست | results `is-loading` · opacity · اسپینر · `aria-busy` | public.js list |
| Loading دکمه | متن transparent + اسپینر | submit |
| Empty جدول | جمله در سلول | dashboard / results |
| Empty سرویس/شغل | notice خطا + disable submit | signup / submit |
| Error شبکه | notice قرمز | JS |
| Pending عضویت | صفحهٔ فقط پیام + notice صبر — **بدون فرم** | `signup-pending.php` |
| Rejected | پیام + فرم مجدد | `signup-rejected.php` |

Skeleton هم‌شکل ردیف در ARP کم است؛ برای MNS طبق پرامت skeleton مجاز است اگر حس محصول حفظ شود.

---

## ۱۰. کپی و میکروکپی

| الگو | نمونه ARP |
| --- | --- |
| عنوان صفحه = برچسب منو | «معرفی‌های من» / «داشبورد» / «پروفایل من» |
| Lead کوتاه واقعی | «لیست تمامی معرفی‌های ثبت‌شده شما، به همراه وضعیت و جزئیات» |
| دکمه = فعل مشخص | «ثبت معرفی بیمار» · «مشاهده همه» · «ارسال درخواست طرح همکاران» |
| Empty انسانی | «هنوز معرفی‌ای ثبت نکرده‌اید.» |
| Pending | «نیازی به ارسال مجدد درخواست نیست.» |
| لحن | رسمیِ دوستانه، بدون اصطلاح فنی انگلیسی |

---

## ۱۱. ریسپانسیو و موبایل

| Breakpoint | رفتار |
| --- | --- |
| ≤۷۶۷ | drawer سایدبار · تب لبه `sidebar-mobile-expand` · backdrop · Escape · focus return · scroll lock · `html.arp-drawer-open` |
| ≤۶۴۰ | متریک‌ها grid · فرم ۲ستونه تک‌ستون |
| ≤۹۰۰ / ۱۱۰۰ / ۱۲۰۰ | نمودار و فیلتر و submit grid ساده‌تر |
| ≥۱۰۲۴ + sticky flag | سایدبار sticky زیر هدر Elementor/admin bar |

تأیید بصری: در ۳۷۵، هدر صفحه + فیلتر کارت‌ها روی هم؛ منو پشت تب آبی لبهٔ راست.

---

## ۱۲. جزئیات ریز

| جزئیات | ARP |
| --- | --- |
| Hover ردیف جدول | `#fafcfe` |
| Focus فیلد | border primary روی control (outline خود input خاموش — شکاف a11y شناخته‌شده) |
| انیمیشن drawer | ۴۲۰ms باز / ۲۸۰ms بسته · `prefers-reduced-motion` ≈ قطع |
| آیکن کنار متن منو | ۱۸px stroke SVG |
| مبلغ | رقم فارسی جداشده + «تومان» خارج از strong |
| کد / URL | `dir=ltr` · monospace یا Inter برای tracking |
| تلفن | LTR + هم‌ترازی راست در متن |
| کپی | Clipboard + حالت `is-copied` |
| فلش «مشاهده همه» | SVG chevron سمت چپ متن در RTL |

**فرمت پول ARP:** `number_format` سپس ارقام فارسی → جداکننده غالباً `,` لاتین. بریف MNS جداکنندهٔ فارسی `٬` را می‌خواهد → در پیاده‌سازی MNS بریف مقدم است (`TODO` همسوسازی با `toLocaleString('fa-IR')`).

---

## ۱۳. نگاشت صفحات (۳۱ صفحهٔ MNS → نزدیک‌ترین مرجع ARP)

شمارش ۳۱: بیمار ۱۰ + پرستار ۶ + عضویت ۴ حالت + کاتالوگ ۲ + ادمین ۷ + not-found بیمار/پرستار ۲.

| # | صفحه MNS | نزدیک‌ترین مرجع ARP | الگوی ترکیبی / توضیح |
| --- | --- | --- | --- |
| 1 | بیمار / `dashboard` | `page-dashboard.php` | متریک+آخرین‌ها+CTA؛ بدون نمودار الزامی؛ AlertCard فاکتور ≈ KPI/notice اقدام‌پذیر ادمین |
| 2 | بیمار / `new-order` | `page-submit.php` + how card | ویزارد چندمرحله‌ای در ARP نیست → PageHeader/fields/how از submit؛ Stepper از پرامت ۸ |
| 3 | بیمار / `orders` | `page-referrals.php` | فیلتر+جستجو+لیست+CTA؛ ترجیح ListItem کارتی به‌جای جدول اگر موبایل‌اول |
| 4 | بیمار / `order-view` | ردیف expandable referrals + ادمین `referral-edit` timeline | جزئیات+تایم‌لاین+اقدام وضعیت |
| 5 | بیمار / `patients` | referrals list (ستون نام/تلفن) | لیست موجودیت‌های ذخیره‌شده |
| 6 | بیمار / `invoices` | `page-commissions.php` | لیست مالی+وضعیت پرداخت+مبلغ |
| 7 | بیمار / `invoice-view` | commissions row + wallet bridge ذهنیت | جزئیات مبلغ + CTA پرداخت |
| 8 | بیمار / `recurring` | referrals فیلترشده / جدول | لیست برنامه‌های تکراری |
| 9 | بیمار / `loyalty` | — | نزدیک‌ترین: کارت متریک فشرده داشبورد (نه الگوی مستقیم)؛ StampCard طبق پرامت ۸ |
| 10 | بیمار / `surveys` | referrals list | لیست کار باقی‌مانده + CTA پر کردن |
| 11 | پرستار / `dashboard` | `page-dashboard.php` | آمار سفارش‌های پیش‌رو + اقدام بعدی |
| 12 | پرستار / `orders` | `page-referrals.php` | صف کار با وضعیت و اقدام |
| 13 | پرستار / `order-view` | referral-edit timeline + submit فرم بخش‌ها | چک‌لیست/گزارش = فرمهای بخش‌بندی‌شده |
| 14 | پرستار / `availability` | — | نزدیک‌ترین: فیلتر تاریخ+کارت تنظیمات؛ الگوی تقویم از ARP datepicker فقط برای انتخاب روز |
| 15 | پرستار / `ratings` | commissions / metrics | میانگین امتیاز (بریف: فقط میانگین) |
| 16 | پرستار / `profile` | `page-profile.php` | hero + فرم + امنیت/اعتماد |
| 17 | عضویت `form` | `signup.php` | فرم + how چهار مرحله |
| 18 | عضویت `pending` | `signup-pending.php` | فقط پیام+notice |
| 19 | عضویت `rejected` | `signup-rejected.php` | پیام+فرم مجدد |
| 20 | عضویت `suspended` | pending (پیام مسدود) + notice danger | ARP تعلیق اختصاصی ندارد؛ ترکیب pending+error notice |
| 21 | کاتالوگ آرشیو | — | نزدیک‌ترین: کارت‌های خدمت شبیه metrics tiles / how bubbles؛ CTA به new-order |
| 22 | کاتالوگ خدمت | — | PageHeader + قیمت + CTA «ثبت سفارش» مثل بنر `arp-refs-cta` |
| 23 | ادمین hub | `admin/views/dashboard.php` | KPI کلیک‌پذیر + صف‌ها |
| 24 | ادمین monitor | `admin/views/referrals.php` + list-table | فیلتر+جدول+bubble |
| 25 | ادمین assign | referrals waiting queue روی پیشخوان ادمین | صف تخصیص |
| 26 | ادمین nurses | `admin/views/applications.php` | تأیید/رد عضویت |
| 27 | ادمین manual-order | `page-submit.php` (فرم معرفی) در پوستهٔ ادمین | فرم دستی |
| 28 | ادمین finance | `admin/views/finance/*` | تب‌ها + جداول |
| 29 | ادمین settings | `admin/views/settings/*` + `tabbed.php` | تب تنظیمات |
| 30 | بیمار `not-found` | متن «صفحه در دسترس نیست» در layout | EmptyState + لینک داشبورد |
| 31 | پرستار `not-found` | همان | همان |

---

## ۱۴. فرض‌ها و محدودیت‌های فاز A

- مقایسهٔ بصری live برای ARP در ۱۲۸۰ (داشبورد) و ۳۷۵ (لیست معرفی‌ها) انجام شد؛ صفحهٔ submit در ۳۷۵ از طریق DOM تأیید شد. اسکرین‌شات پهلو‌به‌پهلوی MNS در این فاز گرفته نشد (فاز B/D).
- ARP ویزارد چندمرحله‌ای و StampCard وفاداری ندارد؛ برای آن‌ها الگوی پرامت ۸ + قضاوت UX با ذکر دلیل در فاز D.
- جداکنندهٔ هزارگان پول در ARP با بریف MNS یکی نیست؛ MNS از `fa-IR` پیروی می‌کند.
- `TODO(client-decision)`: آیا لیست سفارش بیمار باید جدول‌محور (مثل ARP) بماند یا ListItem کارتی موبایل‌اول؟ پیشنهاد پیش‌فرض: ListItem کارتی فشرده با همان سلسله‌مراتب ستون‌های ARP.

---

## ۱۵. نقشهٔ فایل مرجع سریع

| موضوع | مسیر ARP |
| --- | --- |
| سند کامل | `affiliate-referral/UI-UX.md` |
| شِل | `public/views/dashboard/layout.php` |
| داشبورد | `…/page-dashboard.php` |
| لیست | `…/page-referrals.php` · `page-commissions.php` · `page-invited.php` |
| فرم | `…/page-submit.php` · `…/page-profile.php` · `public/views/signup*.php` |
| پارشال | `…/partials/{date-range,pager,filters-reset,invite-url}.php` |
| CSS/JS | `assets/css/public.css` · `assets/js/public.js` |
| فرمت | `includes/class-arp-format.php` |
| ادمین | `admin/views/dashboard.php` · list-tables · `class-arp-admin-ui.php` |

---

*پایان فاز A — توقف طبق پروتکل. مرحلهٔ بعد: فاز B (`docs/ux-audit.md`).*
