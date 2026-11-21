<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
$ctx       = temsah_current_route_context();
$is_portal = str_starts_with( $ctx, 'account' ) || str_starts_with( $ctx, 'vendor' );
$is_auth   = in_array( $ctx, array( 'login', 'register', 'lost-password' ), true );

$fav_id    = absint( temsah_theme_opt( 'favicon_id', 0 ) );
$fav_url   = $fav_id ? wp_get_attachment_image_url( $fav_id, 'thumbnail' ) : get_template_directory_uri() . '/favicon.png';

$cart_summary = class_exists( 'Temsah_Cart' ) ? Temsah_Cart::get_summary() : array( 'count' => 0 );
$cart_count   = (int) ( $cart_summary['count'] ?? 0 );
$is_vendor    = class_exists( 'Temsah_Security' ) && Temsah_Security::is_approved_vendor();
?><!doctype html>
<html dir="rtl" <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="icon" type="image/png" href="<?php echo esc_url( $fav_url ); ?>">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<?php if ( ! $is_portal && ! $is_auth ) : ?>
<header class="site-header">
    <div class="wrap header-main">
        <?php echo temsah_logo_html( home_url( '/' ), '' ); ?>
        <form action="<?php echo esc_url( temsah_url( 'shop' ) ); ?>" class="header-search" method="get">
            <?php if ( ! get_option( 'permalink_structure' ) ) : ?><input type="hidden" name="post_type" value="temsah_product"><?php endif; ?>
            <button aria-label="جستجو" data-search-open="" type="button"><?php echo temsah_ico( 'search.svg' ); ?></button>
            <input autocomplete="off" name="q" placeholder="قالب، افزونه یا اسکریپت مورد نظرت رو پیدا کن..." value="<?php echo esc_attr( sanitize_text_field( wp_unslash( $_GET['q'] ?? '' ) ) ); ?>"/>
            <span class="search-hint">جستجوی محصولات</span>
        </form>
        <div class="header-buttons">
            <a class="seller-entry" href="<?php echo esc_url( $is_vendor ? temsah_url( 'vendor' ) : temsah_url( 'become-seller' ) ); ?>">
                <?php echo temsah_ico( 'work.svg' ); ?>
                <?php echo $is_vendor ? 'پنل فروشنده' : 'فروشنده شو'; ?>
            </a>
            <a aria-label="علاقه‌مندی‌ها" class="small-action" href="<?php echo esc_url( temsah_url( 'account/wishlist' ) ); ?>"><?php echo temsah_ico( 'heart.svg' ); ?></a>
            <a aria-label="سبد خرید" class="small-action cart-head" href="<?php echo esc_url( temsah_url( 'cart' ) ); ?>">
                <?php echo temsah_ico( 'bag.svg' ); ?>
                <span class="cart-count" id="headerCartCount"<?php echo $cart_count <= 0 ? ' hidden' : ''; ?>><?php echo esc_html( temsah_fa_digits( $cart_count ) ); ?></span>
            </a>
            <a class="login-action" href="<?php echo esc_url( is_user_logged_in() ? temsah_url( 'account' ) : temsah_url( 'login' ) ); ?>">
                <?php echo temsah_ico( 'profile.svg' ); ?>
                <?php echo is_user_logged_in() ? 'پنل کاربری' : 'ورود / عضویت'; ?>
            </a>
            <button class="hamburger" id="hamburger" type="button" aria-label="منو"><?php echo temsah_ico( 'category.svg' ); ?></button>
        </div>
    </div>
    <div class="nav-wrap">
        <div class="wrap navbar" id="mainNav">
            <div class="nav-mega-parent">
                <button class="nav-mega-btn" id="megaBtn" type="button" aria-expanded="false">
                    <?php echo temsah_ico( 'category.svg' ); ?> دسته‌بندی محصولات <?php echo temsah_ico( 'arrow---down-2.svg' ); ?>
                </button>
                <div class="mega-panel" id="megaPanel">
                    <a href="<?php echo esc_url( temsah_url( 'shop', array( 'category' => 'wordpress' ) ) ); ?>"><?php echo temsah_ico( 'category.svg' ); ?><div><b>قالب وردپرس</b><small>قالب‌های فروشگاهی، شرکتی و خدماتی</small></div></a>
                    <a href="<?php echo esc_url( temsah_url( 'shop', array( 'category' => 'plugin' ) ) ); ?>"><?php echo temsah_ico( 'work.svg' ); ?><div><b>افزونه وردپرس</b><small>ابزارهای حرفه‌ای وردپرس</small></div></a>
                    <a href="<?php echo esc_url( temsah_url( 'shop', array( 'category' => 'html' ) ) ); ?>"><?php echo temsah_ico( 'document.svg' ); ?><div><b>قالب HTML</b><small>قالب‌های آماده وب</small></div></a>
                    <a href="<?php echo esc_url( temsah_url( 'shop', array( 'category' => 'script' ) ) ); ?>"><?php echo temsah_ico( 'setting.svg' ); ?><div><b>اسکریپت PHP</b><small>سیستم‌های تحت وب</small></div></a>
                    <a href="<?php echo esc_url( temsah_url( 'shop', array( 'category' => 'dashboard' ) ) ); ?>"><?php echo temsah_ico( 'activity.svg' ); ?><div><b>پنل مدیریت</b><small>داشبورد و مدیریت</small></div></a>
                    <a href="<?php echo esc_url( temsah_url( 'shop', array( 'category' => 'app' ) ) ); ?>"><?php echo temsah_ico( 'discovery.svg' ); ?><div><b>سورس اپلیکیشن</b><small>موبایل و دسکتاپ</small></div></a>
                    <a href="<?php echo esc_url( temsah_url( 'shop', array( 'category' => 'ui' ) ) ); ?>"><?php echo temsah_ico( 'paper.svg' ); ?><div><b>رابط کاربری</b><small>کیت‌های UI و طراحی</small></div></a>
                </div>
            </div>
            <a href="<?php echo esc_url( temsah_url( 'shop' ) ); ?>">همه محصولات</a>
            <a href="<?php echo esc_url( temsah_url( 'sellers' ) ); ?>">فروشندگان</a>
            <a href="<?php echo esc_url( temsah_url( 'articles' ) ); ?>">مجله تمساح</a>
            <a href="<?php echo esc_url( temsah_url( 'support' ) ); ?>">پشتیبانی</a>
            <div class="nav-end"><span class="hot-dot"></span> <?php echo esc_html( temsah_theme_opt( 'utility_notice', 'مارکت‌پلیس رابط کاربری و محصولات دیجیتال' ) ); ?></div>
        </div>
    </div>
</header>

<div class="tm-static-search-overlay" hidden="" id="tmSearchOverlay">
    <button aria-label="بستن" class="tm-static-search-shade" data-search-close="" type="button"></button>
    <section aria-label="جستجوی محصولات" class="tm-static-search-panel" role="dialog">
        <button aria-label="بستن جستجو" class="tm-static-search-close" data-search-close="" type="button"><?php echo temsah_ico( 'close-square.svg' ); ?></button>
        <h2>جستجوی محصولات</h2>
        <form id="tmStaticSearchForm">
            <input autocomplete="off" id="tmStaticSearchInput" placeholder="عنوان محصول را جستجو کنید" type="search"/>
            <button type="submit">نمایش نتایج</button>
        </form>
        <div class="tm-static-search-results"></div>
    </section>
</div>
<?php endif; ?>
