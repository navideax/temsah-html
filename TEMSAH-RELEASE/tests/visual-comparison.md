# گزارش تطبیق ظاهری با مرجع V31 (Visual & DOM Parity)

قالب `temsah-theme` مستقیماً بر مبنای فایل‌های CSS، آیکون‌ها، فونت و ساختار DOM نسخه نهایی `V31` پیاده‌سازی شده و هیچ فایل قدیمی یا متعارضی (نظیر `modern.css`) در آن لود نمی‌شود.

## ۱. منابع گرافیکی و تایپوگرافی حفظ‌شده عیناً
- **فونت اصلی**: `assets/foroshyar.woff2` (لود از طریق `@font-face` بومی با نام `Foroshyar` و `dir="rtl"`)
- **لوگو و نماد**: `assets/brand/temsah-tm.png` و `favicon.png`
- **مجموعه آیکون‌ها**: تمامی ۳۵ فایل `assets/icons/*.svg` (Iconly)
- **بنرهای استوری**: فایل‌های `assets/stories/story-01..06.svg`
- **استایل‌های V31 (۹ فایل)**: `style.css`, `home-sliders.css`, `medals.css`, `product-gallery-v24.css`, `product-layout-v26.css`, `onboarding.css`, `portal-v2.css`, `portal-v9-fix.css`, `portal-v11.css`

---

## ۲. تطبیق کلاس‌های کلیدی DOM در صفحات اصلی
| بخش | ساختار DOM و کلاس‌های V31 | منبع داده در وردپرس |
|---|---|---|
| **هدر و جست‌وجو** | `.site-header`, `.nav-mega-parent`, `#megaPanel`, `#tmSearchOverlay`, `#headerCartCount` | منوها، دسته‌بندی‌ها و جست‌وجوی زنده `/temsah/v1/search` |
| **صفحه نخست** | `.promo-bar`, `.ts-stories`, `.ts-slider-viewport`, `.product-card`, `.cover-*`, نشان‌ها و مدال‌ها | `temsah_product` و `temsah_vendors` با اسکریپت `home-sliders.js` |
| **صفحه محصول** | `.tm-product-gallery`, `[data-product-tab]`, `.tm-tab-panel`, `.product-buy-box`, فرم نقد و پرسش | متادیتای محصول، گالری، نظرات تاییدشده خریداران و پرسش‌ها |
| **سبد و پرداخت** | `.t12-layout`, `.t12-cart-item`, `.t12-extras`, `[data-cartmore]`, `.t13-checkout-grid` | `Temsah_Cart`، خدمات جانبی (`support`, `install`, `seo`, `training`) و کوپن |
| **پنل خریدار (۱۴ صفحه)** | `body.is-portal`, `#portalSidebar`, `#portalMenu`, `.v9-screen`, `.v9-stats`, `.v9-panel`, `.v9-table`, `.v10-ticket-list`, `.onboard` | `Temsah_Buyer`، دانلود از Vault و تیکت‌ها |
| **پنل فروشنده (۱۸ صفحه)** | `body.is-portal.is-vendor-portal`, `.v11-screen`, `.v11-toolbar`, `.v11-kpis`, `.v11-surface`, `.v11-tabs` | `Temsah_Vendor`، دفتر کل مالی، برداشت‌ها و محصولات |
| **احراز هویت (۳ صفحه)** | `body.is-auth`, `.auth-layout`, `.auth-marketing`, `.auth-form-side`, `.auth-panel` | احراز هویت امن وردپرس (`wp_signon`, `wp_create_user`, بازیابی رمز) |

---

## ۳. تاییدیه خودکار تطبیق DOM
در اجرای `e2e-suite.mjs` بررسی شد که صفحه نخست و صفحه تک‌محصول به‌صورت واقعی استایل‌های V31 (`home-sliders.css`, `medals.css`, `product-gallery-v24.css`, `product-layout-v26.css`)، لوگوی `temsah-tm.png`، کارت‌های `.product-card` و اسکریپت `theme-ui.js` را بدون خطا رندر می‌کنند.
