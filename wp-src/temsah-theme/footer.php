<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
$ctx       = temsah_current_route_context();
$is_portal = str_starts_with( $ctx, 'account' ) || str_starts_with( $ctx, 'vendor' );
$is_auth   = in_array( $ctx, array( 'login', 'register', 'lost-password' ), true );
$is_vendor = class_exists( 'Temsah_Security' ) && Temsah_Security::is_approved_vendor();
?>
<?php if ( ! $is_portal && ! $is_auth ) : ?>
<footer class="footer">
    <div class="wrap footer-top">
        <div class="footer-brand">
            <?php echo temsah_logo_html( home_url( '/' ) ); ?>
            <p><?php echo esc_html( temsah_theme_opt( 'footer_about', 'تمساح بازار محصولات دیجیتال برای خرید و فروش قالب، افزونه و اسکریپت است.' ) ); ?></p>
            <div class="footer-pills"><span>خرید امن</span><span>فروشنده‌های حرفه‌ای</span><span>پشتیبانی</span></div>
        </div>
        <div>
            <h4>دسترسی سریع</h4>
            <a href="<?php echo esc_url( temsah_url( 'shop' ) ); ?>">محصولات</a>
            <a href="<?php echo esc_url( temsah_url( 'sellers' ) ); ?>">فروشندگان</a>
            <a href="<?php echo esc_url( temsah_url( 'support' ) ); ?>">پشتیبانی</a>
        </div>
        <div>
            <h4>راهنما</h4>
            <a href="<?php echo esc_url( temsah_url( 'about' ) ); ?>">درباره ما</a>
            <a href="<?php echo esc_url( temsah_url( 'terms' ) ); ?>">قوانین</a>
            <a href="<?php echo esc_url( temsah_url( 'contact' ) ); ?>">تماس با ما</a>
        </div>
        <div>
            <h4>تمساح برای سازنده‌ها</h4>
            <a href="<?php echo esc_url( temsah_url( 'become-seller' ) ); ?>">فروشنده شو</a>
            <a href="<?php echo esc_url( $is_vendor ? temsah_url( 'vendor/new-product' ) : temsah_url( 'account/seller-application' ) ); ?>">ثبت محصول</a>
            <a href="<?php echo esc_url( $is_vendor ? temsah_url( 'vendor' ) : temsah_url( 'become-seller' ) ); ?>">پنل فروشنده</a>
        </div>
    </div>
    <div class="wrap footer-bottom">
        <span><?php echo esc_html( temsah_theme_opt( 'footer_copyright', '© تمام حقوق برای تمساح محفوظ است.' ) ); ?></span>
        <span>TEMSAH.ME MARKETPLACE</span>
    </div>
</footer>
<?php endif; ?>
<div class="toast" id="toast" role="status" aria-live="polite"></div>
<?php wp_footer(); ?>
</body>
</html>
