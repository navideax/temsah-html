# طراحی امنیتی تمساح (Security Architecture & Controls)

تمساح با رویکرد دفاع در عمق (Defense in Depth) پیاده‌سازی شده و همه بردارهای حمله رایج وردپرس و سامانه‌های فروشگاهی را پوشش می‌دهد:

## ۱. احراز هویت و کنترل دسترسی (Auth & RBAC)
- مسیرهای خصوصی (`/checkout`, `/account/*`, `/vendor/*`) برای مهمانان با کد `302` به صفحه ورود هدایت می‌شوند و هدرهای `Cache-Control: no-store, no-cache, must-revalidate, max-age=0`، `Pragma: no-cache`، `X-Robots-Tag: noindex, nofollow` و متاتگ `noindex,nofollow` دریافت می‌کنند.
- ورود و درخواست تغییر رمز دارای محدودیت نرخ (`Temsah_Security::check_rate_limit`) بر پایه Transient و IP برای مقابله با Brute-Force هستند.
- پنل فروشندگان (`/vendor/*` و REST `/temsah/v1/vendor/*`) مستلزم **هر دو** شرط زیر است:
  1. داشتن قابلیت `temsah_access_vendor_portal`
  2. وضعیت `approved` در جدول `wp_temsah_vendors`
  (خریداران و فروشندگان در انتظار تایید کد `403` دریافت می‌کنند).
- دسترسی مستقیم به فایل‌های `.html` داخل مسیر قالب با پاسخ `403` مسدود می‌شود.

---

## ۲. جلوگیری از دسترسی غیرمجاز افقی (IDOR Defense)
تمام عملیات و نمایش جزئیات در لایه `Temsah_Security` مالکیت را بررسی می‌کنند:
- `user_owns_order($order_id)`: صرفاً صاحب سفارش یا مدیر کل مجاز به مشاهده فاکتور (`/account/order-detail`) و پرداخت آن است.
- `user_owns_product($product_id)`: فروشنده تنها می‌تواند محصولات فروشگاه خود را ویرایش کند (`/vendor/edit-product`).
- `user_can_access_ticket($ticket_id, $role)`: خریدار فقط تیکت‌های خود و فروشنده فقط تیکت‌های مربوط به محصولات فروشگاه خود را مشاهده و پاسخ می‌دهند.
- `user_owns_product_license($product_id)`: دانلود فایل و ثبت دیدگاه تاییدشده (`review`) فقط پس از خرید موفق و پرداخت کامل (`paid`) مجاز است.

---

## ۳. محافظت CSRF، XSS و تزریق SQL
- **CSRF**: تمام فرم‌های POST سمت کاربر/فروشنده و ادمین با `wp_nonce_field` / `check_admin_referer` / `Temsah_Security::verify_nonce_or_die` و تمام مسیرهای REST API تغییردهنده وضعیت با هدر اجباری `X-WP-Nonce` کنترل می‌شوند (درخواست بدون Nonce معتبر کد `403` می‌گیرد).
- **XSS**: تمام ورودی‌های متنی با `sanitize_text_field` و `sanitize_textarea_field` پاک‌سازی می‌شوند (تگ‌های `<script>` حذف می‌شوند) و هنگام رندر تمام مقادیر با `esc_html`, `esc_attr`, `esc_url` و `esc_textarea` خروجی داده می‌شوند.
- **SQL Injection**: تمامی کوئری‌های پایگاه‌داده با `$wpdb->prepare()` یا متدهای امن `$wpdb->insert` / `$wpdb->update` / `$wpdb->delete` و مشخص‌کننده‌های نوع (`%d`, `%s`) اجرا می‌شوند.

---

## ۴. مخزن امن دانلود (Protected Vault)
- فایل‌های محصولات و مدارک احراز هویت در مسیر `wp-content/temsah-vault/` همراه با `.htaccess` و `index.php` نگهداری می‌شوند.
- لینک دانلود (`/ ?temsah_download=1&product_id=...&file_id=...&_wpnonce=...`) مختص کاربر جاری تولید شده و هنگام درخواست، ۴ بررسی متوالی انجام می‌شود: لاگین بودن کاربر، اعتبار Nonce، تایید خریدار/مالک محصول بودن، و `realpath` درون دایرکتوری Vault.
- فایل با هدر `X-Content-Type-Options: nosniff` استریم شده و رکورد دانلود در `wp_temsah_downloads` ثبت می‌شود.

---

## ۵. ایمنی مالی و خروجی CSV
- هیچ قیمت، تخفیف، نرخ کمیسیون یا وضعیت پرداختی از درخواست مرورگر اعتماد نمی‌شود.
- خروجی CSV کمیسیون‌های فروشنده (`stream_vendor_csv`) سلول‌های شروع‌شونده با کاراکترهای فرمولی (`=`, `+`, `-`, `@`, `\t`, `\r`) را خنثی‌سازی کرده و با `nosniff` ارسال می‌شود.
- کل رویدادهای امنیتی (خطاهای CSRF، رد پرداخت، ورود ناموفق، تلاش غیرمجاز فروشندگی، دانلودها، تغییرات مالی) در جدول `wp_temsah_activity_logs` ممیزی و ثبت می‌شوند.
