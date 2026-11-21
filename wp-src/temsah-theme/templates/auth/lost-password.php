<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
get_header();
?>
<div id="app"><div class="auth-layout">
    <section class="auth-marketing">
        <div><?php echo temsah_logo_html( home_url( '/' ) ); ?></div>
        <div class="auth-marketing-copy">
            <span class="eyebrow">بازیابی دسترسی</span>
            <h1>رمز عبورت رو فراموش کردی؟<br/><span>نگران نباش.</span></h1>
            <p>ایمیل یا نام کاربری خود را وارد کنید تا در صورت وجود حساب کاربری، راهنمای تغییر رمز عبور ارسال شود.</p>
        </div>
        <div class="auth-bottom">TEMSAH.ME — بازار محصولات دیجیتال</div>
    </section>

    <section class="auth-form-side">
        <div class="auth-mobile-logo"><?php echo temsah_logo_html( home_url( '/' ) ); ?></div>
        <div class="auth-panel">
            <span class="auth-panel-kicker">بازیابی رمز عبور</span>
            <h1>درخواست لینک بازیابی</h1>
            <?php if ( ! empty( $_GET['auth_ok'] ) ) : ?>
                <div class="tm-alert tm-alert-ok"><?php echo esc_html( sanitize_text_field( wp_unslash( $_GET['auth_ok'] ) ) ); ?></div>
            <?php elseif ( ! empty( $_GET['auth_err'] ) ) : ?>
                <div class="tm-alert tm-alert-err"><?php echo esc_html( sanitize_text_field( wp_unslash( $_GET['auth_err'] ) ) ); ?></div>
            <?php endif; ?>
            <form class="demo-form" method="post" action="">
                <?php wp_nonce_field( 'temsah_lost_password' ); ?>
                <input type="hidden" name="temsah_action" value="lost_password">
                <label>ایمیل یا نام کاربری<input name="user_login" placeholder="user@example.com" required type="text"/></label>
                <button class="btn-primary auth-submit" type="submit">ارسال درخواست بازیابی <?php echo temsah_ico( 'arrow---left.svg' ); ?></button>
            </form>
            <div class="auth-divide">یادآوری شد؟</div>
            <a class="auth-secondary" href="<?php echo esc_url( temsah_url( 'login' ) ); ?>">بازگشت به صفحه ورود</a>
        </div>
        <a class="auth-back" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo temsah_ico( 'arrow---right.svg' ); ?> بازگشت به فروشگاه</a>
    </section>
</div></div>
<?php get_footer(); ?>
