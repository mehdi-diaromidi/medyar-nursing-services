# Medyar Nursing Services — مرجع UI/UX فرانت

RTL · فارسی · تم Dr Plus · الگوی رفتاری/بصری `affiliate-referral/UI-UX.md`  
ادمین وردپرس در این سند پوشش داده نمی‌شود (استاندارد `wp-admin`).  
طراحی با اصول ui0ux؛ پیاده‌سازی پیکسلی در فاز ۳ روی همین توکن‌ها.

---

## 1. Problem & Goal

**بیمار** باید بتواند خدمت ببیند، سفارش بدهد و وضعیت/فاکتور/پرداخت را بدون سردرگمی پیگیری کند.  
**پرستار** باید سفارش‌های تخصیص‌یافته، چک‌لیست، گزارش و کیف پول را در یک شِل آشنا (مثل پنل همکار) انجام دهد.  
**کسب‌وکار:** اعتماد مدیار، کاهش تماس پشتیبانی، تکمیل مسیر تا پرداخت و امتیاز.

هدف UI: یک سیستم بصری واحد با ARP (شناخت برند)، دو شِل نقش‌محور، و قرارداد دادهٔ ثابت تا bind بک‌اند بدون بازنویسی مارکاپ.

---

## 2. Assumptions & Open Questions

### Assumptions (توسعه)
- بدون dark mode (مثل ARP).
- OTP و هدر سایت از تم؛ پنل‌ها داخل محتوای صفحه قالب پلاگین.
- ارقام فارسی در UI؛ phone / tracking / URL / NID با `dir="ltr"`.
- فاز ۳ با `MNS_UI_STATIC` و fixture؛ اکشن‌ها no-op یا mock notice.
- اصلاح a11y نسبت به ARP: focus ring روی فیلد (≥3:1)، لمس pager/toggle ترجیحاً ۴۴px، `prefers-reduced-motion`.

### Open (کارفرما / بعداً)
- متن قرارداد عضویت پرستار، فیلدهای دقیق per-service، شهربندی واقعی.
- پذیرش/رد تخصیص توسط پرستار (فرض فعلی: تخصیص قطعی).

---

## 3. User & Context

| نقش | زمینه | Job اصلی | اضطراب |
| --- | --- | --- | --- |
| بیمار / سفارش‌دهنده | موبایل زیاد، عجله، گاهی برای دیگری سفارش می‌دهد | ثبت سریع + دیدن «الان کجاست سفارش» | قیمت مبهم، نبود پرستار، پرداخت |
| پرستار | بین مراجعات، موبایل/دسکتاپ | شروع/پایان، چک‌لیست، گزارش بدون اشتباه مبلغ | فاکتور اشتباه، مورد فوری |
| مهمان کاتالوگ | قبل از ورود | فهم خدمت و قیمت پایه | نمی‌داند کدام خدمت |

---

## 4. Proposed Solution

### 4.1 Information architecture

```
کاتالوگ عمومی
  ├ آرشیو دسته‌ها (۶)
  └ صفحه خدمت → CTA ثبت سفارش (لاگین)

عضویت پرستار (خارج شِل یا گیت)
  └ pending | rejected | suspended | → پنل

پنل بیمار (?mns_panel=patient&mns_page=)
  dashboard | new-order | orders | order-view | patients
  invoices | recurring | loyalty | surveys | account↗ | logout

پنل پرستار (?mns_panel=nurse&mns_page=)
  dashboard | orders | order-view | availability | wallet↗
  ratings | profile | account↗ | logout
```

یک primary action per صفحه (Jakob + Hick): داشبورد بیمار → «ثبت سفارش»؛ جزئیات پرستار در حین خدمت → «ثبت چک‌لیست / پایان».

### 4.2 Shell (آناتومی — آینه ARP)

```
┌──── 306px ────┬──────── 1fr ────────┐
│ Header آبی    │ Main #f7f7f7 r24    │
│ badge·avatar  │ notice?             │
│ name·phone    │ page body           │
│ Nav           │                     │
└───────────────┴─────────────────────┘
```

- `dir="rtl"` روی `.mns-dash`
- `data-mns-panel` / `data-mns-page`
- موبایل ≤767: drawer از راست + expand لبه + backdrop + Escape + focus return
- دسکتاپ ≥1024: sticky زیر هدر Elementor/admin bar (`--mns-sticky-top`)

### 4.3 Navigation (ثابت ترتیب)

**بیمار:** dashboard · new-order · orders · patients · invoices · recurring · loyalty · surveys · پنل مدیار (خارجی) · خروج (danger)

**پرستار:** dashboard · orders · availability · wallet (خارجی برداشت در صورت وجود) · ratings · profile · پنل مدیار · خروج

قواعد: active فقط داخلی؛ `aria-current="page"`؛ فلش hover فقط غیرفعال.

### 4.4 Visual tokens (`--mns-*`)

منبع اجرایی: `assets/css/public.css`.

| Token | Value | Use |
| --- | --- | --- |
| `--mns-primary` | `var(--primary-100, #0272b1)` | CTA, focus, nav active |
| `--mns-primary-soft` | `rgba(2,114,177,0.12)` | icon wells, soft hover |
| `--mns-primary-hover` | `#015a8c` | primary button hover |
| `--mns-text` | `#383838` | body |
| `--mns-heading` | `#373434` | titles |
| `--mns-muted` | `#a6a6a6` | secondary |
| `--mns-muted-strong` | `#747474` | labels / lead |
| `--mns-border` | `#e6e6e6` | cards / fields |
| `--mns-bg` | `#f7f7f7` | shell |
| `--mns-card` | `#ffffff` | surfaces |
| `--mns-radius` | `16px` | cards / fields |
| `--mns-radius-shell` | `24px` | sidebar / main |
| `--mns-success` | `#1a7a4c` | done / paid |
| `--mns-success-bg` | `#e8f7ee` | pills |
| `--mns-danger` | `#d60043` | errors / logout hover |
| `--mns-danger-bg` | `#fde8ec` | invalid |
| `--mns-warn` | `#b7791f` | in progress |
| `--mns-warn-bg` | `#fff5e0` | progress pill |
| `--mns-metrics-ink` | `#114b5f` | KPI numbers |
| `--mns-metrics-accent` | `#0d5c63` | hero bar |
| `--mns-space-1`…`8` | 4 / 8 / 12 / 16 / 24 / 32 / 40 / 48 | 8pt grid |
| `--mns-field-min-h` | `50px` | controls |
| `--mns-touch-min` | `44px` | a11y target (بهبود نسبت به ARP) |
| `--mns-focus-ring` | `0 0 0 3px rgba(2,114,177,0.35)` | focus-visible |

**بدون** سایه کارت پیش‌فرض. سایه فقط منوی select / datepicker / avatar / تب موبایل (مقادیر ARP).

Breakpoints: **640 / 767 / 900 / 1023 / 1100 / 1200**.  
Container: `min(1280px, calc(100% - 32px))`.

Typography: `var(--main-font)`; scale 12 / 14 / 16 / 18 / 20 / 24 / 28; page title 24/800; body 16/400 LH 2 در شِل؛ label 14; Inter فقط tracking code.

### 4.5 Order status pills (فرانت)

| slug | tone | bg / text |
| --- | --- | --- |
| submitted | waiting | soft primary / `#0272b1` |
| assigned | progress | warn palette |
| in_progress | progress | warn |
| service_ended | accepted | success soft |
| medication | progress | warn |
| invoiced | waiting | primary soft |
| paid | done | success |
| completed | done | success / teal feel `#0f766e` text ok |
| cancelled | cancel | danger |
| needs_review | failed | `#f1f3f5` / `#6b7280` |

همیشه متن + رنگ (نه فقط رنگ).

### 4.6 Components (فاز ۳ باید پوشش دهد)

| Component | Class root | States |
| --- | --- | --- |
| Field block | `.mns-field-block` | default, focus-within, filled, invalid, readonly, disabled |
| Primary button | `.mns-submit-btn` | default, hover, loading, disabled |
| Outline button | `.mns-btn-outline` | … |
| Notice | `.mns-notice` | success, error (role status/alert) |
| Status pill | `.mns-status` | tones بالا |
| Metrics | `.mns-metrics` | hero / open / success |
| Table | `.mns-table` | hover, empty, expandable |
| Pager | `.mns-pager` | current, disabled, compact≤767 |
| Custom select | `.mns-select` | open, filled, search |
| Date range | `.mns-date-range` | jdate readonly + Delete clears |
| Drawer | `.mns-dash__sidebar` | mobile-expanded |

Motion: field 150–200ms ease; drawer open 420ms `cubic-bezier(0.22,1,0.36,1)`; close 280ms; reduced-motion ≈ 0.01ms.

### 4.7 Forms — microcopy نمونه

- ثبت سفارش CTA: «ثبت درخواست خدمت»
- نبود پرستار: «برای این زمان و منطقه پرستاری در دسترس نیست. زمان دیگری انتخاب کنید یا با پشتیبانی تماس بگیرید.»
- چک‌لیست: «مبالغ از تعرفه سیستم محاسبه می‌شود؛ مبلغ دستی وارد نکنید.»
- گزارش فوری: «با علامت فوری، ادمین مدیار فوراً مطلع می‌شود.»

### 4.8 Data contract (UI → backend)

هر صفحه فقط `MNS_View_Data` / آرایه DTO می‌گیرد:

- Static: `MNS_Static_Fixtures::get( $key )`
- Live (فاز ۵+): همان shape از Repository

نام فیلدهای فرم از روز اول قفل می‌شوند (پیوست B همین فایل در طول فاز ۳ تکمیل می‌شود). لیست‌ها: partial HTML مشترک برای mock AJAX و live.

---

## 5. Rationale

- **Jakob's Law:** شِل ARP = هزینه یادگیری صفر برای کاربران مدیار.
- **One primary action:** کاهش خطا در مسیر اورژانسی/پرستاری.
- **Recognition over recall:** وضعیت سفارش با pill رنگی + برچسب فارسی.
- **Fitts:** CTA بلوک min-height 52؛ لمس ۴۴px.
- **Consistency:** توکن و رفتار فیلد عین ARP با rename `--mns-*`.

---

## 6. Accessibility & Edge Cases

- Landmarks: aside/nav/main؛ aria-current؛ aria-live برای notice و لیست.
- Focus ring اجباری روی control (شکاف ARP را اینجا نمی‌آوریم).
- Empty / loading / error / success / no-nurse / pending membership / wallet missing.
- بدون JS: native select دیده شود؛ فیلتر GET کار کند.

---

## 7. Risks & Trade-offs

| Risk | Mitigation |
| --- | --- |
| دو پنل = دوبرابر مارکاپ | partials مشترک + یک CSS |
| Fixture از DTO واقعی فاصله بگیرد | shape در fixtures از همین سند قفل |
| پرش به بک‌اند زود | `MNS_UI_STATIC` تا پذیرش UI |

---

## 8. Next Steps (فاز ۳ اجرا)

1. توکن CSS + شِل بیمار/پرستار + drawer  
2. پارشال‌ها + fixtures پایه  
3. کاتالوگ → پنل بیمار صفحات → پنل پرستار → عضویت  
4. JS: select، jdate، mobile، list mock  
5. چک‌لیست a11y و حالت‌ها روی هر صفحه  

سنجه‌های بعداً: نرخ تکمیل ثبت سفارش، زمان تا اولین سفارش، خطای اعتبارسنجی، SUS پنل پرستار.

---

## پیوست — نقشه فایل UI

| موضوع | مسیر |
| --- | --- |
| CSS | `assets/css/public.css` |
| JS | `assets/js/public.js` |
| شِل بیمار/پرستار | `public/views/patient/layout.php` · `nurse/layout.php` |
| صفحات | `public/views/**/page-*.php` |
| Fixtures | `public/static-fixtures/*.php` |
| Templates | `public/templates/mns-*.php` |
| Loader | `public/class-mns-static-fixtures.php` · `class-mns-pages.php` |
