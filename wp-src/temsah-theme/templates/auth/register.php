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
            <span class="eyebrow">عضویت در تمساح</span>
            <h1>حساب خودت رو بساز،<br/><span>پروژه‌هات رو سریع‌تر پیش ببر.</span></h1>
            <p>با ساخت حساب کاربری به کتابخانه فایل‌ها، لایسنس‌ها، پشتیبانی تیکتی و امکان درخواست فروشندگی دسترسی خواهید داشت.</p>
            <div class="auth-bullets">
                <span><?php echo temsah_ico( 'shield-done.svg' ); ?> مدیریت یکپارچه سفارش‌ها</span>
                <span><?php echo temsah_ico( 'document.svg' ); ?> دریافت آپدیت محصولات</span>
                <span><?php echo temsah_ico( 'work.svg' ); ?> امکان درخواست فروشندگی</span>
            </div>
        </div>
        <div class="auth-bottom">TEMSAH.ME — بازار محصولات دیجیتال</div>
    </section>

    <section class="auth-form-side">
        <div class="auth-mobile-logo"><?php echo temsah_logo_html( home_url( '/' ) ); ?></div>
        <div class="auth-panel">
            <span class="auth-panel-kicker">ایجاد حساب جدید</span>
            <h1>ثبت‌نام در تمساح</h1>
            <p>مشخصات خود را برای ایجاد حساب خریدار وارد کنید.</p>

            <?php if ( ! empty( $_GET['auth_err'] ) ) : ?>
                <div class="tm-alert tm-alert-err"><?php echo esc_html( sanitize_text_field( wp_unslash( $_GET['auth_err'] ) ) ); ?></div>
            <?php endif; ?>

            <form class="demo-form" method="post" action="">
                <?php wp_nonce_field( 'temsah_register' ); ?>
                <input type="hidden" name="temsah_action" value="register">
                <label>نام و نام خانوادگی<input name="full_name" placeholder="نام کامل" required type="text"/></label>
                <label>ایمیل<input name="email" placeholder="you@example.com" required type="email"/></label>
                <label>شماره موبایل (اختیاری)<input name="mobile" placeholder="09123456789" type="tel"/></label>
                <label>رمز عبور (حداقل ۸ کاراکتر)<input minlength="8" name="password" placeholder="حداقل ۸ کاراکتر" required type="password"/></label>
                <label class="check-row" style="display:flex;align-items:center;gap:8px;font-weight:400;"><input name="accept_terms" required type="checkbox" value="1" style="width:auto;"/> <a href="<?php echo esc_url( temsah_url( 'terms' ) ); ?>">قوانین تمساح</a> را می‌پذیرم.</label>
                <button class="btn-primary auth-submit" type="submit">ایجاد حساب کاربری <?php echo temsah_ico( 'arrow---left.svg' ); ?></button>
            </form>

            <div class="auth-divide">قبلاً ثبت‌نام کرده‌اید؟</div>
            <a class="auth-secondary" href="<?php echo esc_url( temsah_url( 'login' ) ); ?>">ورود به حساب کاربری</a>
        </div>
        <a class="auth-back" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo temsah_ico( 'arrow---right.svg' ); ?> بازگشت به فروشگاه</a>
    </section>
</div></div>
<?php get_footer(); ?>
