<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
$user      = wp_get_current_user();
$cur_route = Temsah_Router::current_route();
$is_vendor = Temsah_Security::is_approved_vendor();
$initial   = mb_substr( trim( $user->display_name ?: $user->user_login ), 0, 1 );
$logout    = wp_nonce_url( add_query_arg( 'temsah_logout', 1, home_url( '/' ) ), 'temsah_logout' );
$settings  = get_option( 'temsah_settings', array() );
$is_dev    = ( $settings['environment_mode'] ?? 'production' ) === 'development';
?>
<div id="app"><div class="portal-layout">
<aside class="portal-sidebar" id="portalSidebar">
    <div class="portal-brand">
        <?php echo temsah_logo_html( home_url( '/' ) ); ?>
        <span class="portal-mark">حساب خریدار تمساح</span>
    </div>
    <div class="portal-profile">
        <span class="portal-avatar"><?php echo temsah_ico( 'profile.svg' ); ?></span>
        <div>
            <b><?php echo esc_html( $user->display_name ?: $user->user_login ); ?></b>
            <small>عضو تمساح · خریدار</small>
        </div>
    </div>
    <div class="portal-nav-caption">مدیریت حساب کاربری</div>
    <nav class="portal-links">
        <a class="<?php echo 'account' === $cur_route ? 'selected' : ''; ?>" href="<?php echo esc_url( temsah_url( 'account' ) ); ?>"><?php echo temsah_ico( 'home.svg' ); ?><span>داشبورد</span></a>
        <a class="<?php echo 'account/downloads' === $cur_route ? 'selected' : ''; ?>" href="<?php echo esc_url( temsah_url( 'account/downloads' ) ); ?>"><?php echo temsah_ico( 'download.svg' ); ?><span>دانلودها</span></a>
        <a class="<?php echo in_array( $cur_route, array( 'account/orders', 'account/order-detail' ), true ) ? 'selected' : ''; ?>" href="<?php echo esc_url( temsah_url( 'account/orders' ) ); ?>"><?php echo temsah_ico( 'wallet.svg' ); ?><span>مالی و سفارش‌ها</span></a>
        <a class="<?php echo 'account/licenses' === $cur_route ? 'selected' : ''; ?>" href="<?php echo esc_url( temsah_url( 'account/licenses' ) ); ?>"><?php echo temsah_ico( 'shield-done.svg' ); ?><span>لایسنس‌ها</span></a>
        <a class="<?php echo 'account/wishlist' === $cur_route ? 'selected' : ''; ?>" href="<?php echo esc_url( temsah_url( 'account/wishlist' ) ); ?>"><?php echo temsah_ico( 'heart.svg' ); ?><span>علاقه‌مندی</span></a>
        <a class="<?php echo 'account/customer-club' === $cur_route ? 'selected' : ''; ?>" href="<?php echo esc_url( temsah_url( 'account/customer-club' ) ); ?>"><?php echo temsah_ico( 'ticket.svg' ); ?><span>باشگاه مشتریان</span></a>
        <div class="portal-nav-group">
            <button class="portal-group-title" data-portalgroup="" type="button"><span><?php echo temsah_ico( 'chat.svg' ); ?></span><span>پشتیبانی</span><span class="portal-group-toggle">▾</span></button>
            <div class="portal-sub-links">
                <a class="<?php echo in_array( $cur_route, array( 'account/tickets', 'account/ticket-view' ), true ) ? 'selected' : ''; ?>" href="<?php echo esc_url( temsah_url( 'account/tickets' ) ); ?>"><?php echo temsah_ico( 'ticket.svg' ); ?><span>مشاهده تیکت‌ها</span></a>
                <a class="<?php echo 'account/ticket-new' === $cur_route ? 'selected' : ''; ?>" href="<?php echo esc_url( temsah_url( 'account/ticket-new' ) ); ?>"><?php echo temsah_ico( 'paper.svg' ); ?><span>ثبت تیکت جدید</span></a>
                <a class="<?php echo 'account/announcements' === $cur_route ? 'selected' : ''; ?>" href="<?php echo esc_url( temsah_url( 'account/announcements' ) ); ?>"><?php echo temsah_ico( 'notification.svg' ); ?><span>اعلانات</span></a>
            </div>
        </div>
        <div class="portal-nav-group">
            <button class="portal-group-title" data-portalgroup="" type="button"><span><?php echo temsah_ico( 'setting.svg' ); ?></span><span>تنظیمات</span><span class="portal-group-toggle">▾</span></button>
            <div class="portal-sub-links">
                <a class="<?php echo 'account/settings' === $cur_route ? 'selected' : ''; ?>" href="<?php echo esc_url( temsah_url( 'account/settings' ) ); ?>"><?php echo temsah_ico( 'edit.svg' ); ?><span>ویرایش پروفایل</span></a>
                <a class="<?php echo in_array( $cur_route, array( 'account/seller-application', 'account/seller-application-status' ), true ) ? 'selected' : ''; ?>" href="<?php echo esc_url( temsah_url( 'account/seller-application-status' ) ); ?>"><?php echo temsah_ico( 'work.svg' ); ?><span>وضعیت فروشندگی</span></a>
            </div>
        </div>
    </nav>
    <div class="portal-side-bottom">
        <?php if ( $is_vendor ) : ?>
            <a class="portal-switch" href="<?php echo esc_url( temsah_url( 'vendor' ) ); ?>"><?php echo temsah_ico( 'arrow---left.svg' ); ?> ورود به پنل فروشنده</a>
        <?php else : ?>
            <a class="portal-switch" href="<?php echo esc_url( temsah_url( 'account/seller-application' ) ); ?>"><?php echo temsah_ico( 'work.svg' ); ?> درخواست فروشندگی</a>
        <?php endif; ?>
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo temsah_ico( 'home.svg' ); ?> بازگشت به فروشگاه</a>
        <a href="<?php echo esc_url( $logout ); ?>"><?php echo temsah_ico( 'lock.svg' ); ?> خروج از حساب</a>
    </div>
</aside>
<div class="portal-main">
    <header class="portal-top">
        <button aria-label="بازکردن منوی پنل" class="portal-menu" id="portalMenu" data-portal-toggle="" type="button"><?php echo temsah_ico( 'category.svg' ); ?></button>
        <div>
            <b>حساب خریدار تمساح</b>
            <small>پیگیری خریدها، دانلودها، لایسنس‌ها، تیکت‌ها و تنظیمات حساب</small>
        </div>
        <div class="portal-top-actions">
            <?php if ( $is_dev ) : ?><span class="portal-demo-pill">محیط تست / آزمایشی فعال است</span><?php endif; ?>
            <?php if ( $is_vendor ) : ?>
                <a class="portal-role-link" href="<?php echo esc_url( temsah_url( 'vendor' ) ); ?>"><?php echo temsah_ico( 'arrow---left.svg' ); ?> پنل فروشنده</a>
            <?php endif; ?>
            <span class="portal-small-avatar"><?php echo esc_html( $initial ?: 'ک' ); ?></span>
        </div>
    </header>
    <main class="portal-content">
