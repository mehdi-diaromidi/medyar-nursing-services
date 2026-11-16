# ۱۱ — نقشهٔ UI ↔ داده (جایگزینی fixtureها)

> هر صفحهٔ فعلی از چه fixture‌ای داده می‌گیرد، در بک‌اند از کجا بیاید و در کدام milestone وصل شود. **ستون «شکل»** یعنی کلیدهای fixture همان کلیدهای پاسخ سرور بمانند.
> **[قطعی؛ تأیید مالک ۱۶ نوامبر]** تصمیم «جایگزینی یک‌جا» برای صفحاتی که بک‌اندشان در M4+ می‌آید به این معنی گرفته شد: **لایهٔ داده یک‌بار ساخته و سوییچ می‌شود، ولی صفحهٔ M4+ تا ساخته‌شدن بک‌اندش از fixture می‌خواند و بنر «نمایشی» دارد** (وگرنه خالی/خراب می‌شد). اگر مالک غیر از این می‌خواهد، همان لحظه اعلام شود.
> مسیر fixtureها: `public/static-fixtures/`. «سرویس» = کلاس دامنه‌ای که صفحه را تغذیه می‌کند.

## بیمار
| صفحه (slug) | fixture فعلی | منبع واقعی | سرویس/endpoint | M |
|---|---|---|---|---|
| ناوبری/پروفایل سایدبار | `patient/nav.php`, `patient/profile.php` | کاربر وردپرس (نام، موبایل) + شمارندهٔ فاکتور پرداخت‌نشده (M5) | `MNS_Patient_Context` | M3 |
| داشبورد (`dashboard`) | `patient/dashboard.php` | سفارش‌های حساب: فعال/اخیر؛ فاکتور پرداخت‌نشده (M5)؛ پرسشنامه (M6)؛ بنر «نیازمند اقدام» | `MNS_Patient_Orders::dashboard()` | M3 (+M5/M6 بلوک‌ها) |
| ثبت سفارش (`new-order`) | `patient/new_order.php` | `mns_patients`, `mns_services.config.order_fields`, `S-SLOTS`, `iran-locations.json`, `S-REG-DISTRICTS`؛ **`availability` fixture حذف** ⇒ API-A13-1 | API-PAT-1، API-A13-1، API-ORD-1 | M3 |
| سفارش‌های من (`orders`) | `patient/orders.php` | `mns_orders` ⨝ services/patients/nurses/invoices | API-ORD-2 | M3 |
| جزئیات سفارش (`order-view`) | `patient/order_view.php` | `mns_orders` + log + (M4: report whitelist، M5: invoice) | API-ORD-3/4 | M3 (+M4/M5) |
| بیماران من (`patients`) | `patient/patients_list.php` | `mns_patients` | API-PAT-2 | M3 |
| فاکتورها (`invoices`, `invoice-view`) | `patient/invoices.php` | `mns_invoices` | M5 | M5 |
| خدمات دوره‌ای (`recurring`) | `patient/recurring.php` | `mns_recurring_plans` | M7 | M7 |
| وفاداری (`loyalty`) | `patient/loyalty.php` | `mns_loyalty_stamps`, `discount_codes` | M6 | M6 |
| پرسشنامه‌ها (`surveys`) | `patient/surveys.php` | `mns_surveys` | M6 | M6 |

## پرستار
| صفحه | fixture | منبع واقعی | endpoint/سرویس | M |
|---|---|---|---|---|
| ناوبری/پروفایل سایدبار | `nurse/nav.php`, `nurse/profile.php` | کاربر + `mns_nurses` + (موجودی کیف پول از MTW در M5) | `MNS_Nurse_Context` | M1 |
| فرم عضویت (`/nursing-nurse-signup/`) | `views/nurse/signup-stub.php` (stub) | API-NRS-1 | `MNS_Nurse_Applications` | M1 |
| پروفایل (`profile`) | `nurse/profile_full.php` | `wp_users` + `mns_nurses` | API-NRS-2/3 | M1 |
| برنامه و دسترسی (`availability`) | `nurse/availability.php` | `mns_nurse_week/day_slots/regions`, `S-SLOTS`, `S-REG-DISTRICTS`, سفارش‌های فعال | API-NRS-4..7 | M2 |
| سفارش‌ها (`orders`) | `nurse/orders.php` | `mns_orders` (نرمال‌شده طبق R-PRV) | API-NOR-1 | M3 (+سهم M5) |
| جزئیات سفارش (`order-view`) | `nurse/order_view.php` | سفارش + بیمار + محل (R-PRV-1) | API-NOR-2 | M3 (خواندنی)، M4 (اجرا) |
| داشبورد (`dashboard`) | `nurse/dashboard.php` | «الان»، «بعدی»، ناتمام، اعلان‌ها، امروز/پیش‌رو، کیف پول (M5)، امتیاز (M6)، عملکرد، برنامهٔ هفتگی | API-NDB-1 | M3 (بلوک سفارش/اعلان/برنامه)، M4 (الان/ناتمام)، M5 (کیف پول)، M6 (امتیاز) |
| چک‌لیست (`checklist`) | `nurse/checklist.php` | `mns_services.config.checklist` + `mns_checklists` | M4 | M4 |
| مرحلهٔ دارو (`drug`) | `nurse/drug.php` | `mns_drug_*`, `consult_doctors` | M4 | M4 |
| گزارش (`report`) | `nurse/report.php` | `mns_reports`, `mns_files` | M4 | M4 |
| امتیازهای من (`ratings`) | `nurse/ratings.php` | `mns_surveys` (میانگین) + `S-BDG-*` | M6 | M6 |

## کاتالوگ و عمومی
| صفحه | fixture | منبع | M |
|---|---|---|---|
| دسته‌ها (`/nursing-services/`) | `catalog/categories.php` | `mns_services.category` + متن‌های `config` | M3 (فقط ۲ خدمت/دسته) |
| صفحهٔ خدمت (`?mns_service=`) | `catalog/services.php`, `service_sample.php` | `mns_services` (`config.description` …) | M3 |

## شکل‌های کلیدی که نباید شکسته شوند (قرارداد با JS/view)
- `nurse/availability` ⇒ config JSON با کلیدهای `slots, allSlots(قدیمی)…`: **view فعلی** `data-config` را می‌سازد؛ در بک‌اند همان view، مقادیر را از `MNS_Nurse_Availability::view_model($nurse_id)` بگیرد (کلیدها: `case,statuses,pausedText,pausedSub,slots,days{iso,y,m,d,dow,past,today,base,on,orders},monthNames,regions,districts,regionOrders,locationsUrl`) و `initNurseSchedule` فقط endpointهای ذخیره را اضافه کند.
- `patient/new_order` ⇒ `data-mns-*` attributeهای فرم (`districts`, `locations`, `time_slots`) از همان bootstrap.
- ارقام و تاریخ‌ها: API انگلیسی/میلادی؛ view با `MNS_Format::digits()` و `MNS_Format::jdate()`.

## قواعد مهاجرت صفحه به داده واقعی (هر صفحه)
1. view یک `view model` از سرویس می‌گیرد (آرایه‌ای هم‌شکل fixture)؛ تغییر در markup ممنوع مگر لازم.
2. `MNS_Dev` حالت‌ها (`mns_case`/`mns_state`) فقط برای ادمین و فقط با داده‌ٔ فیک روی همان view model (برای بررسی UI)؛ نه روی داده واقعی.
3. حالت خالی: view model خالی ⇒ همان empty-state موجود.
4. fixtureٔ صفحه حذف نمی‌شود (برای dev/مستند)، فقط از مسیر زنده خارج می‌شود.
5. هر صفحه در موبایل و دسکتاپ بعد از وصل بررسی شود (AC-UI-EMPTY).
