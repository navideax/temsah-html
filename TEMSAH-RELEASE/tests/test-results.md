# گزارش نتایج آزمون‌های خودکار تمساح (End-to-End Test Results)

**تاریخ اجرا**: ۲۰۲۶-۱۱-۲۱  
**محیط تست**: `WordPress 6.7.2` + `PHP 8.5.12` + `sqlite-database-integration` (`PHPRequestHandler` در مسیر `/tmp/wp-site`)

---

## ۱. بررسی ساختار و نحو کدها (Syntax & Static Audits)
| آزمون | ابزار / روش | نتیجه |
|---|---|---|
| بررسی نحو PHP (`temsah-core` + `temsah-theme`) | `php-wasm-cli -l` روی هر ۷۷ فایل `.php` | **۷۷ / ۷۷ بدون خطا (PASS)** |
| بررسی نحو JavaScript (`theme-ui.js`, `home-sliders.js`) | `node --check` | **۲ / ۲ بدون خطا (PASS)** |
| بررسی گارد `ABSPATH` در تمام فایل‌های PHP | اسکریپت ممیزی روی `wp-src/` | **۷۷ / ۷۷ دارای گارد (PASS)** |
| ممیزی خطاهای اجرایی و Deprecation وردپرس | بررسی `/tmp/wp-site/wp-content/debug.log` | **۰ خطا/اخطار از `temsah-core` و `temsah-theme` (PASS)** |

---

## ۲. آزمون کامل HTTP و منطق تجاری روی وردپرس واقعی (۸۹ / ۸۹ PASS)
اجرای اسکریپت `/tmp/wp-env-check/e2e-suite.mjs` پس از آخرین تغییر:

### الف) حالت خالی Production و پاک‌سازی ایزوله (`Temsah_Seeder`)
- صفحه اصلی، فروشگاه، فروشندگان، سبد خرید و `/wp-json/temsah/v1/search` پیش از Seed بدون محصول جعلی با HTTP 200 و پیام استاندارد حالت خالی رندر شدند (**PASS**).
- اجرای `Temsah_Seeder::install_demo_data()` تعداد دقیق **۱۲ محصول** و **۴ فروشنده** را ساخت (**PASS**).
- اجرای `Temsah_Seeder::purge_demo_data()` پس از آزمون‌ها حالت را دقیقاً به وضعیت خالی Production بازگرداند (**PASS**).

### ب) احراز هویت، نقش‌ها و چرخه کامل فروشنده و خرید
- ورود واقعی از طریق HTTP POST با CSRF `_wpnonce` برای خریدار (`test_buyer_ali`) و فروشنده (`test_seller_sara`) انجام و کوکی نشست صادر شد (**PASS**).
- ارسال فرم ۵ مرحله‌ای فروشندگی با کد ملی معتبر پیمانه ۱۱ و شبا، تایید توسط مدیر، و تغییر وضعیت فروشنده به `approved` (**PASS**).
- افزودن محصول توسط فروشنده تاییدشده، انتشار، آپلود فایل ZIP در Vault با هش SHA-256 (**PASS**).
- سبد خرید و محاسبه قطعی سمت سرور: محصول ۴۰۰,۰۰۰ + سرویس نصب ۳۹۰,۰۰۰ − ۲۰٪ کوپن `TEMSAH20` = **۶۳۲,۰۰۰ تومان** (**PASS**).
- تایید پرداخت امضاشده با HMAC درگاه Sandbox، صدور لایسنس UUID، و واریز کمیسیون ۷۰٪ فروشنده (**۴۴۲,۴۰۰ تومان**) (**PASS**).
- ثبت درخواست برداشت ۲۰۰,۰۰۰ تومان (موجودی: ۲۴۲,۴۰۰) و تست رد درخواست توسط ادمین با بازگشت خودکار به موجودی (**۴۴۲,۴۰۰ تومان**) (**PASS**).

### پ) پوشش ۱۰۰٪ مسیرهای قالب (HTTP 200 بدون خطا و RTL)
- **۱۴ مسیر عمومی و احراز هویت** (`/`, `?post_type=temsah_product`, `sellers`, `seller`, `articles`, `about`, `contact`, `support`, `terms`, `become-seller`, `cart`, `login`, `register`, `lost-password`): **۱۴ / ۱۴ HTTP 200 (PASS)**
- **۱۴ مسیر پنل خریدار** (`account`, `downloads`, `orders`, `order-detail`, `licenses`, `wishlist`, `customer-club`, `tickets`, `ticket-new`, `ticket-view`, `announcements`, `settings`, `seller-application`, `seller-application-status`): **۱۴ / ۱۴ HTTP 200 (PASS)**
- **۱۹ مسیر/نمای پنل فروشنده** (`vendor`, `products`, `product-drafts`, `new-product`, `edit-product`, `license`, `finance`, `earnings`, `withdrawals`, `support-report`, `tickets`, `ticket-view`, `questions`, `reviews`, `ready-replies`, `announcements`, `profile`, `store-settings`, `verify`): **۱۹ / ۱۹ HTTP 200 (PASS)**
- **تسویه‌حساب (`checkout`)** با سبد خرید واقعی: **HTTP 200 + کلاس `.t13-*` (PASS)**
- **REST API** با Nonce معتبر: `cart/add` (۲۰۰) و `account/orders` (۲۰۰) (**PASS**)
