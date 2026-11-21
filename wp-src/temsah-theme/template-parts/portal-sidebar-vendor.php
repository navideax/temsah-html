<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
$uid       = get_current_user_id();
$vendor    = Temsah_Security::get_vendor_by_user( $uid );
$cur_route = Temsah_Router::current_route();
$store_nm  = $vendor ? $vendor->store_name : wp_get_current_user()->display_name;
$initial   = mb_substr( trim( $store_nm ?: 'ف' ), 0, 1 );
$logout    = wp_nonce_url( add_query_arg( 'temsah_logout', 1, home_url( '/' ) ), 'temsah_logout' );
$settings  = get_option( 'temsah_settings', array() );
$is_dev    = ( $settings['environment_mode'] ?? 'production' ) === 'development';
?>
<div id="app"><div class="portal-layout">
<aside class="portal-sidebar" id="portalSidebar">
    <div class="portal-brand">
        <?php echo temsah_logo_html( home_url( '/' ) ); ?>
        <span class="portal-mark">مرکز فروشندگان تمساح</span>
    </div>
    <div class="portal-profile">
        <span class="portal-avatar"><?php echo temsah_ico( 'work.svg' ); ?></span>
        <div>
            <b><?php echo esc_html( $store_nm ); ?></b>
            <small>فروشنده تأییدشده</small>
        </div>
    </div>
    <div class="portal-nav-caption">مدیریت فروشگاه</div>
    <nav class="portal-links">
        <a class="<?php echo 'vendor' === $cur_route ? 'selected' : ''; ?>" href="<?php echo esc_url( temsah_url( 'vendor' ) ); ?>"><?php echo temsah_ico( 'home.svg' ); ?><span>داشبورد</span></a>
        <div class="portal-nav-group">
            <button class="portal-group-title" data-portalgroup="" type="button"><?php echo temsah_ico( 'category.svg' ); ?><span>محصولات</span><span class="portal-group-toggle">▾</span></button>
            <div class="portal-sub-links">
                <a class="<?php echo 'vendor/products' === $cur_route ? 'selected' : ''; ?>" href="<?php echo esc_url( temsah_url( 'vendor/products' ) ); ?>">محصولات من</a>
                <a class="<?php echo 'vendor/product-drafts' === $cur_route ? 'selected' : ''; ?>" href="<?php echo esc_url( temsah_url( 'vendor/product-drafts' ) ); ?>">پیش‌نویس‌ها</a>
                <a class="<?php echo in_array( $cur_route, array( 'vendor/new-product', 'vendor/submit-product' ), true ) ? 'selected' : ''; ?>" href="<?php echo esc_url( temsah_url( 'vendor/new-product' ) ); ?>">محصول جدید</a>
                <a class="<?php echo 'vendor/license' === $cur_route ? 'selected' : ''; ?>" href="<?php echo esc_url( temsah_url( 'vendor/license' ) ); ?>">لایسنس‌ها</a>
            </div>
        </div>
        <div class="portal-nav-group">
            <button class="portal-group-title" data-portalgroup="" type="button"><?php echo temsah_ico( 'activity.svg' ); ?><span>گزارش‌ها</span><span class="portal-group-toggle">▾</span></button>
            <div class="portal-sub-links">
                <a class="<?php echo 'vendor/finance' === $cur_route ? 'selected' : ''; ?>" href="<?php echo esc_url( temsah_url( 'vendor/finance' ) ); ?>">گزارش مالی</a>
                <a class="<?php echo 'vendor/support-report' === $cur_route ? 'selected' : ''; ?>" href="<?php echo esc_url( temsah_url( 'vendor/support-report' ) ); ?>">گزارش پشتیبانی</a>
                <a class="<?php echo 'vendor/earnings' === $cur_route ? 'selected' : ''; ?>" href="<?php echo esc_url( temsah_url( 'vendor/earnings' ) ); ?>">تراکنش‌های فروش</a>
                <a class="<?php echo 'vendor/withdrawals' === $cur_route ? 'selected' : ''; ?>" href="<?php echo esc_url( temsah_url( 'vendor/withdrawals' ) ); ?>">تسویه‌ها</a>
            </div>
        </div>
        <div class="portal-nav-group">
            <button class="portal-group-title" data-portalgroup="" type="button"><?php echo temsah_ico( 'chat.svg' ); ?><span>پشتیبانی</span><span class="portal-group-toggle">▾</span></button>
            <div class="portal-sub-links">
                <a class="<?php echo in_array( $cur_route, array( 'vendor/tickets', 'vendor/ticket-view' ), true ) ? 'selected' : ''; ?>" href="<?php echo esc_url( temsah_url( 'vendor/tickets' ) ); ?>">مشاهده تیکت‌ها</a>
                <a class="<?php echo 'vendor/ready-replies' === $cur_route ? 'selected' : ''; ?>" href="<?php echo esc_url( temsah_url( 'vendor/ready-replies' ) ); ?>">پاسخ‌های آماده</a>
                <a class="<?php echo 'vendor/questions' === $cur_route ? 'selected' : ''; ?>" href="<?php echo esc_url( temsah_url( 'vendor/questions' ) ); ?>">پرسش‌های محصولات</a>
                <a class="<?php echo 'vendor/reviews' === $cur_route ? 'selected' : ''; ?>" href="<?php echo esc_url( temsah_url( 'vendor/reviews' ) ); ?>">دیدگاه‌های محصولات</a>
                <a class="<?php echo in_array( $cur_route, array( 'vendor/announcements', 'vendor/support-announcements' ), true ) ? 'selected' : ''; ?>" href="<?php echo esc_url( temsah_url( 'vendor/announcements' ) ); ?>">اعلانات</a>
            </div>
        </div>
        <div class="portal-nav-group">
            <button class="portal-group-title" data-portalgroup="" type="button"><?php echo temsah_ico( 'setting.svg' ); ?><span>تنظیمات</span><span class="portal-group-toggle">▾</span></button>
            <div class="portal-sub-links">
                <a class="<?php echo 'vendor/profile' === $cur_route ? 'selected' : ''; ?>" href="<?php echo esc_url( temsah_url( 'vendor/profile' ) ); ?>">پروفایل کاربری</a>
                <a class="<?php echo 'vendor/store-settings' === $cur_route ? 'selected' : ''; ?>" href="<?php echo esc_url( temsah_url( 'vendor/store-settings' ) ); ?>">ویرایش فروشگاه</a>
                <a class="<?php echo 'vendor/verify' === $cur_route ? 'selected' : ''; ?>" href="<?php echo esc_url( temsah_url( 'vendor/verify' ) ); ?>">تأیید حساب کاربری</a>
            </div>
        </div>
    </nav>
    <div class="portal-side-bottom">
        <a class="portal-switch" href="<?php echo esc_url( temsah_url( 'account' ) ); ?>"><?php echo temsah_ico( 'arrow---left.svg' ); ?> ورود به پنل خریدار</a>
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo temsah_ico( 'home.svg' ); ?> بازگشت به فروشگاه</a>
        <a href="<?php echo esc_url( $logout ); ?>"><?php echo temsah_ico( 'lock.svg' ); ?> خروج از حساب</a>
    </div>
</aside>
<div class="portal-main">
    <header class="portal-top">
        <button aria-label="بازکردن منوی پنل" class="portal-menu" id="portalMenu" data-portal-toggle="" type="button"><?php echo temsah_ico( 'category.svg' ); ?></button>
        <div>
            <b>مرکز فروشندگان تمساح</b>
            <small>مدیریت فروش، محصولات، درآمد و پشتیبانی خریداران</small>
        </div>
        <div class="portal-top-actions">
            <?php if ( $is_dev ) : ?><span class="portal-demo-pill">محیط تست / آزمایشی فعال است</span><?php endif; ?>
            <a class="portal-role-link" href="<?php echo esc_url( temsah_url( 'account' ) ); ?>"><?php echo temsah_ico( 'arrow---left.svg' ); ?> پنل خریدار</a>
            <span class="portal-small-avatar"><?php echo esc_html( $initial ); ?></span>
        </div>
    </header>
    <main class="portal-content">
