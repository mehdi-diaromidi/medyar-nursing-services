# پوشهٔ دانش پروژهٔ سامانهٔ خدمات پرستاری مدیار (MNS)

> ساخته‌شده: ۹ نوامبر ۲۰۲۶ (۱۹ آبان ۱۴۰۵)
> هدف: یک مرجع ساختاریافته برای ادامهٔ توسعه، هم برای توسعه‌دهنده و هم برای هر دستیار هوش مصنوعی (Claude / Cursor / ...).
> **هیچ رمز، کلید API، اطلاعات ورود FTP یا توکن در این پوشه نیست و نباید اضافه شود.** فقط محل نگهداری آن‌ها ذکر شده.

## منابع ترکیب‌شده

| منبع | محتوا | وزن |
|---|---|---|
| `For Clouad/MEDYAR-PROJECT-KNOWLEDGE.md` (Cursor، ۹ نوامبر) | زیرساخت، قالب، پلاگین‌ها، پچ‌ها، پیامک، وضعیت کد MNS، باگ‌ها | **حقیقت دربارهٔ کد و سرور** |
| چت مشترک Claude.ai (۲۰ اکتبر تا ۹ نوامبر) — لینک اشتراک `claude.ai/share/65ea38ad-…` | تحلیل کارفرما، پرسش‌وپاسخ، بریف، تصمیم‌های مرحله‌به‌مرحلهٔ صفحات بیمار/پرستار | **حقیقت دربارهٔ تصمیم‌های محصول (جدیدترین)** |
| `docs/` ریشه پروژه | پچ‌ها، پیامک، چت، بریف اولیه | تکمیلی |

**قاعدهٔ تعارض:** برای رفتار محصول، آخرین تصمیم کاربر در چت برنده است؛ برای واقعیت کد/سرور، دانش‌نامهٔ Cursor و خود کد. تعارض‌های شناخته‌شده در [`07-open-items/inconsistencies.md`](07-open-items/inconsistencies.md) آمده است.

## نقشهٔ پوشه

```
knowledge/
├─ README.md                         ← همین فایل (ایندکس + قواعد)
├─ 00-overview/
│   ├─ project-summary.md            خلاصهٔ یک‌دقیقه‌ای + وضعیت امروز
│   ├─ roles-and-actors.md           نقش‌ها (ادمین/پرستار/بیمار) + capabilityها
│   └─ glossary.md                   واژه‌نامه
├─ 01-infrastructure/
│   ├─ environments-and-server.md    آدرس‌ها، سرور، دیسک، گیت
│   ├─ repo-structure.md             ساختار ریپوی محلی
│   ├─ deploy-and-verify.md          deploy.ps1 و حلقهٔ راستی‌آزمایی
│   └─ working-rules-and-preferences.md  قوانین Cursor/skillها + ترجیحات کاربر
├─ 02-site-ecosystem/
│   ├─ theme-dr-plus.md
│   ├─ plugins-inventory.md          فهرست همهٔ پلاگین‌ها
│   ├─ custom-plugins.md             ARP / MSQ / MTW / MCP / MMAD / Lab / Pharmacy / medical-records
│   ├─ dependency-map.md
│   ├─ sms-ownership-and-queue.md
│   └─ third-party-patches.md
├─ 03-nursing-project/               ← قلب محصول پرستاری
│   ├─ business-brief.md             بریف کسب‌وکار
│   ├─ decisions-log.md              همهٔ تصمیم‌های قطعی (به ترتیب موضوع)
│   ├─ order-lifecycle.md            وضعیت‌ها و ماشین وضعیت
│   ├─ rules-assignment-pricing-billing.md
│   ├─ sms-events.md
│   └─ client-deliverables.md        چیزهایی که کارفرما هنوز نداده
├─ 04-pages/                         ← مشخصات تک‌تک صفحات (P-xx)
│   ├─ README.md                     نقشهٔ صفحات + وضعیت طراحی/پیاده‌سازی
│   ├─ patient/  (new-order, dashboard, my-orders, invoices, recurring, order-view)
│   ├─ nurse/    (dashboard, order-view, checklist, drug-step, report, regions-schedule)
│   └─ admin/    (admin-panel)
├─ 05-implementation/
│   ├─ mns-plugin-technical.md       مرجع فنی کد MNS
│   ├─ ui-design-system.md           توکن‌ها، کامپوننت‌ها، ARP-kit
│   ├─ current-status.md             چه پیاده شده و چه نه
│   └─ backend-blueprint.md          جداول، AJAX، قفل‌ها، کران‌ها (پیشنهادی)
├─ 06-lessons/
│   ├─ gotchas.md                    باگ‌ها و درس‌ها
│   └─ security-and-server-health.md
├─ 07-open-items/
│   ├─ open-questions.md             سؤال‌های باز کارفرما
│   ├─ todo.md                       کارهای باز توسعه
│   └─ inconsistencies.md            ناهماهنگی بین اسناد/کد/تصمیم‌ها
└─ 08-history/
    └─ changelog.md                  تاریخچهٔ کار و نسخه‌ها
```

## برچسب‌ها در همهٔ اسناد

| برچسب | معنی |
|---|---|
| **[قطعی]** | تصمیم صریح کارفرما/کاربر |
| **[فرض]** | پیشنهاد/فرض خودمان؛ هنوز تأیید نشده — قابل تغییر |
| **[باز]** | سؤال بی‌پاسخ |
| **[اجرا شده]** | در کد MNS (استاتیک) وجود دارد |
| **[طراحی شده]** | مشخصات نوشته شده ولی در کد نیست |

## شناسه‌های صفحه و اکشن

صفحات با `P-xx` و اکشن‌ها با `A-xx` در چت شماره‌گذاری شده‌اند (مثلاً `P-28` جزئیات سفارش پرستار، `A-13` بررسی دسترسی پرستار). این شناسه‌ها در همهٔ اسناد این پوشه حفظ شده‌اند تا بشود به بحث‌های قبلی ارجاع داد. نقشهٔ کامل در [`04-pages/README.md`](04-pages/README.md).

## چطور از این پوشه استفاده کنیم

- **شروع یک جلسهٔ جدید با AI:** `00-overview/project-summary.md` + `03-nursing-project/decisions-log.md` + `07-open-items/inconsistencies.md` را بده.
- **ساخت یک صفحه:** فایل همان صفحه در `04-pages/` + `05-implementation/ui-design-system.md` + `01-infrastructure/working-rules-and-preferences.md`.
- **کار روی بک‌اند:** `05-implementation/backend-blueprint.md` + `03-nursing-project/rules-assignment-pricing-billing.md` + `02-site-ecosystem/dependency-map.md`.
- **هر تصمیم جدید کارفرما** را در `decisions-log.md` اضافه کن، و اگر قبلی را باطل می‌کند، مورد قدیمی را خط بزن (نه حذف) و در `inconsistencies.md` یادداشت کن.
- **هر پچ روی کد ثالث** طبق قانون Cursor در `docs/` ریشه مستند شود (نه فقط اینجا).

## محدودیت‌های این نسخه

- چت مشترک تا آنجا خوانده شد که منتشر شده بود (پایان: درخواست ساخت zip، بدون پاسخ). صفحه‌های **P-52 پرسشنامه، P-43 بیماران من، P-47/48 وفاداری، P-49/P-20 پیگیری، کیف پول پرستار، P-32 امتیازهای من، ثبت‌نام/وضعیت پرستار (P-01/02)، P-05 پروفایل، پنل ادمین (P-21..P-58 به تفصیل)** هنوز در چت طراحی نشده بودند؛ فقط خطوط کلی از بریف دارند.
- بخش‌هایی از چت که نسخهٔ «بازتولیدشدهٔ» متن قبلی بودند (سفارش‌های من، فاکتورها) با نسخهٔ نهایی ترکیب شدند.
- در متن چت جملاتی از کارفرما دربارهٔ برآورد هزینه/زمان بود؛ به درخواست کاربر («زمان‌بندی را نمی‌خواهم») **عمداً وارد این پوشه نشد**.
