# معماری نرم‌افزار تمساح (TEMSAH Architecture)

پلتفرم تمساح مطابق اصول مهندسی وردپرس به دو لایه کاملاً تفکیک‌شده تقسیم شده است تا تغییر یا سفارشی‌سازی ظاهر هیچ خللی در داده‌ها و منطق تجاری ایجاد نکند:

## ۱. لایه هسته (`wp-content/plugins/temsah-core/`)
| کلاس | فایل | مسئولیت اصلی |
|---|---|---|
| `Temsah_Core` | `temsah-core.php` | راه‌اندازی، چک نسخه دیتابیس و ثبت هوک‌های فعال‌سازی |
| `Temsah_DB` | `includes/class-temsah-db.php` | نصب/ارتقای ۲۶ جدول اختصاصی `wp_temsah_*` و ساخت پوشه Vault |
| `Temsah_Roles` | `includes/class-temsah-roles.php` | نقش‌های `temsah_buyer`, `temsah_vendor`, `temsah_moderator`, `temsah_marketplace_admin` و فیلترهای دسترسی |
| `Temsah_Security` | `includes/class-temsah-security.php` | کنترل مالکیت (IDOR)، غیرفعال‌سازی کش پرتال‌ها (`no-store`)، Rate Limit، Nonce، جریان دانلود امن و ثبت لاگ |
| `Temsah_CPT` | `includes/class-temsah-cpt.php` | نوع پست `temsah_product` و تاکسونومی‌های `temsah_product_cat` و `temsah_product_tag` |
| `Temsah_Router` | `includes/class-temsah-router.php` | قوانین Rewrite و کنترل دسترسی سطح مسیر (`account/*`, `vendor/*`, `checkout`) |
| `Temsah_Cart` | `includes/class-temsah-cart.php` | سبد خرید مبتنی بر توکن HTTP-only / کاربر، سرویس‌های جانبی و محاسبه قطعی سمت سرور |
| `Temsah_Payments` | `includes/class-temsah-payments.php` | رابط `Temsah_Gateway_Interface`، درگاه‌های زرین‌پال، سامان، سندباکس HMAC، کمیسیون و صدور لایسنس |
| `Temsah_Vendor` | `includes/class-temsah-vendor.php` | درخواست فروشندگی، CRUD محصولات، آپلود فایل به Vault، پاسخ‌های آماده، مالی و تسویه |
| `Temsah_Buyer` | `includes/class-temsah-buyer.php` | خریدها، دانلودها، علاقه‌مندی‌ها، اتصال دامنه به لایسنس، تیکت و دیدگاه تاییدشده |
| `Temsah_REST` | `includes/class-temsah-rest.php` | فضای نام `temsah/v1` با بررسی کامل `X-WP-Nonce` و قابلیت‌ها |
| `Temsah_Admin` | `includes/class-temsah-admin.php` | پنل‌های مدیریت فروشندگان، محصولات، سفارش‌ها، برداشت‌ها، کوپن‌ها، تیکت‌ها و گزارش‌ها |
| `Temsah_Seeder` | `includes/class-temsah-seeder.php` | تولید و پاک‌سازی ایزوله داده‌های آزمایشی (`_temsah_is_demo`) |

---

## ۲. جداول دیتابیس اختصاصی (`wp_temsah_*` — ۲۶ جدول)
- **محصولات و فایل‌ها**: `temsah_products`, `temsah_product_versions`, `temsah_product_files`, `temsah_product_gallery`
- **فروشندگان و احراز هویت**: `temsah_vendors`, `temsah_vendor_verifications`
- **سبد خرید، سفارش و مالی**: `temsah_carts`, `temsah_cart_items`, `temsah_orders`, `temsah_order_items`, `temsah_payments`, `temsah_transactions`, `temsah_coupons`, `temsah_campaigns`, `temsah_commissions`, `temsah_vendor_wallet_ledger`, `temsah_withdrawals`
- **لایسنس و دانلود**: `temsah_licenses`, `temsah_downloads`
- **پشتیبانی و تعامل**: `temsah_tickets`, `temsah_ticket_messages`, `temsah_vendor_reply_templates`, `temsah_product_feedback`, `temsah_wishlists`, `temsah_notifications`, `temsah_activity_logs`

---

## ۳. لایه قالب (`wp-content/themes/temsah-theme/`)
- `header.php` / `footer.php`: ناوبری مگامنوی یکپارچه، پنجره جست‌وجوی زنده REST (`#tmSearchOverlay`)، استایل‌ها و فونت `foroshyar.woff2`
- `front-page.php`, `archive-temsah_product.php`, `taxonomy-*.php`, `single-temsah_product.php`: صفحات ویترین و جزئیات محصول با اسلایدر V24 و تب‌های V26
- `templates/public/*`: صفحات فروشگاه‌ها، صفحه اختصاصی هر فروشنده، مقالات، درباره ما، تماس، پشتیبانی، قوانین و همکاری با ما
- `templates/auth/*`: صفحات ورود، ثبت‌نام و بازیابی رمز
- `templates/shop/*`: سبد خرید V31 (`.t12-*`)، تسویه‌حساب V31 (`.t13-*`) و درگاه/بازگشت پرداخت
- `templates/account/*`: ۱۴ صفحه کامل پنل خریدار (`.v9-*`, `.v10-*`, `.onboard`)
- `templates/vendor/*`: ۱۸ صفحه کامل پنل فروشنده (`.v11-*`)
