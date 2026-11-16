# پوشهٔ دانش پروژهٔ سامانهٔ خدمات پرستاری مدیار (MNS)

> ساخته‌شده: ۹ نوامبر ۲۰۲۶ (۱۹ آبان ۱۴۰۵)
> دامنه: **فقط پروژهٔ پرستاری و پلاگین `medyar-nursing-services`**. بقیهٔ سایت مدیار (رزرو، آزمایشگاه، داروخانه، چت، پچ‌های قالب و …) فقط در حدی که MNS به آن وابسته است آمده (`02-dependencies/`).
> **هیچ رمز، کلید API، اطلاعات ورود FTP یا توکن در این پوشه نیست و نباید اضافه شود.**

## منابع ترکیب‌شده

| منبع | محتوا | وزن |
|---|---|---|
| `For Clouad/MEDYAR-PROJECT-KNOWLEDGE.md` (Cursor، ۹ نوامبر) | وضعیت کد MNS، UI، ابزار، باگ‌ها، ترجیحات کاربر | **حقیقت دربارهٔ کد** |
| چت مشترک Claude.ai (۲۰ اکتبر تا ۹ نوامبر، `claude.ai/share/65ea38ad-…`) | بریف کارفرما، پرسش‌وپاسخ، تصمیم‌های مرحله‌ای، طراحی صفحات بیمار/پرستار | **حقیقت دربارهٔ تصمیم‌های محصول (جدیدترین)** |
| `docs/گزارش بررسی…پرستاری.md` و `desgin/` | بریف اولیه، PNG و بریف UI | تکمیلی |

**قاعدهٔ تعارض:** برای رفتار محصول، آخرین تصمیم کاربر در چت برنده است؛ برای واقعیت کد، خود کد. تفاوت‌های شناخته‌شده: [`07-open-items/inconsistencies.md`](07-open-items/inconsistencies.md).

> **اول [`PROJECT-LOGIC.md`](PROJECT-LOGIC.md) را بخوان:** خلاصهٔ جامع منطق‌ها، قواعد، وضعیت کد و قانون‌های کار (به‌روزتر از بقیهٔ اسناد این پوشه؛ تاریخ ۱۶ نوامبر ۲۰۲۶).

> **مسیر توسعهٔ بک‌اند و روش نظارت بر ایجنت سازنده:** [`09-development/README.md`](09-development/README.md).

## نقشهٔ پوشه

```
knowledge/
├─ README.md
├─ PROJECT-LOGIC.md    ← نقطهٔ شروع هر ایجنت (منطق جامع پروژه)
├─ 00-overview/        project-summary · roles-and-actors · glossary
├─ 01-infrastructure/  environments-and-server · deploy-and-verify · working-rules-and-preferences
├─ 02-dependencies/    mns-dependencies   (قالب، ARP، کیف پول، صف پیامک، زرین‌پال — فقط آنچه MNS لازم دارد)
├─ 03-nursing-project/
│   ├─ business-brief            بریف کسب‌وکار و دامنه
│   ├─ decisions-log             ۹۳ تصمیم شمارهٔ‌دار ([قطعی]/[فرض])
│   ├─ order-lifecycle           وضعیت‌ها، ماشین وضعیت، اتمیک‌ها، استثناها
│   ├─ rules-assignment-pricing-billing   تخصیص، قیمت، فاکتور، پرداخت، پرسشنامه، تخفیف
│   ├─ sms-events                فهرست پیامک‌ها برای ساخت پترن
│   └─ client-deliverables       چیزهایی که کارفرما باید بدهد
├─ 04-pages/           README (نقشهٔ P-xx و وضعیت) + patient/ nurse/ admin/
├─ 05-implementation/  mns-plugin-technical · ui-design-system · current-status · backend-blueprint
├─ 06-lessons/         gotchas · security-and-server-health
├─ 07-open-items/      open-questions · todo · inconsistencies
├─ 08-history/         changelog
└─ 09-development/     مسیر ساخت بک‌اند: تصمیم‌ها، مدل داده، قواعد، API، نقشهٔ راه، کارت‌های وظیفه، راهنمای نظارت، سناریوهای پذیرش
```

## برچسب‌ها

**[قطعی]** تصمیم صریح کاربر/کارفرما · **[فرض]** پیشنهاد Claude (تأییدشده یا نه، در متن مشخص است) · **[باز]** بی‌پاسخ · ✔/✘ در جدول صفحات: طراحی/کد موجود یا نه.

شناسه‌های `P-xx` (صفحه) و `A-xx` (اکشن) همان‌هایی‌اند که در چت شماره‌گذاری شده‌اند؛ برای ارجاع به بحث‌های قبلی حفظ شده‌اند.

## چطور استفاده کنیم

| می‌خواهم… | بخوان |
|---|---|
| جلسهٔ جدید با AI شروع کنم | **`PROJECT-LOGIC.md`** + `00-overview/project-summary` + `03-nursing-project/decisions-log` + `07-open-items/inconsistencies` |
| صفحه‌ای را بسازم | فایل همان صفحه در `04-pages/` + `05-implementation/ui-design-system` + `01-infrastructure/working-rules-and-preferences` |
| بک‌اند بنویسم | `05-implementation/backend-blueprint` + `03-nursing-project/order-lifecycle` + `rules-assignment-pricing-billing` + `02-dependencies/mns-dependencies` |
| دیپلوی کنم | `01-infrastructure/deploy-and-verify` |
| با کارفرما صحبت کنم | `07-open-items/open-questions` + `03-nursing-project/client-deliverables` |

نگهداری: تصمیم جدید → خط جدید در `decisions-log` (قدیمی را خط بزن، حذف نکن) و در صورت تعارض با کد، ردیف در `inconsistencies`. تغییر کد → `05-implementation/current-status` و `08-history/changelog`.

## محدودیت‌های این نسخه

- طراحی نهایی در چت برای این صفحات **انجام نشده بود**: P-52 پرسشنامه، P-43 بیماران من، P-47/48 وفاداری و کدها، P-49/P-20 پیگیری، P-32 امتیازهای من، کیف پول پرستار، P-01/02/05 ثبت‌نام/وضعیت/پروفایل پرستار، جزئیات پنل ادمین. فقط خطوط کلی بریف آمده.
- چت از ۲۰ اکتبر تا ۹ نوامبر خوانده شد؛ پیام آخر کاربر (درخواست zip) بی‌پاسخ ماند. در بخش‌هایی که چت یک متن را چند بار بازتولید کرده، آخرین نسخه ملاک بود.
- پرسش‌های برآورد هزینه/زمان در چت عمداً وارد نشد (خواستهٔ کاربر).
- فایل‌های واقعی پلاگین (PHP/CSS/JS) در این مرحله دوباره بازبینی نشدند؛ توصیف کد از دانش‌نامهٔ Cursor است. قبل از تغییر، با کد تطبیق بده.
