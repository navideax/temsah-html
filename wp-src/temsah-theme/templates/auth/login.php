<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
get_header();
$redirect_to = isset( $_GET['redirect_to'] ) ? esc_url_raw( wp_unslash( $_GET['redirect_to'] ) ) : '';
?>
<div id="app"><div class="auth-layout">
    <section class="auth-marketing">
        <div><?php echo temsah_logo_html( home_url( '/' ) ); ?></div>
        <div class="auth-marketing-copy">
            <span class="eyebrow">به بازار حرفه‌ای‌ها خوش آمدید</span>
            <h1>ایده‌های بزرگ،<br/><span>از یک انتخاب خوب شروع می‌شن.</span></h1>
            <p>فایل‌ها، قالب‌ها و ابزارهای توسعه رو پیدا کن و تمام خریدها و دانلودهات رو در یک پنل شخصی مدیریت کن.</p>
            <div class="auth-bullets">
                <span><?php echo temsah_ico( 'shield-done.svg' ); ?> خرید و دانلود فوری</span>
                <span><?php echo temsah_ico( 'document.svg' ); ?> کتابخانه محصولات و لایسنس</span>
                <span><?php echo temsah_ico( 'chat.svg' ); ?> ارتباط مستقیم با فروشنده</span>
            </div>
        </div>
        <div class="auth-bottom">TEMSAH.ME — بازار محصولات دیجیتال</div>
    </section>

    <section class="auth-form-side">
        <div class="auth-mobile-logo"><?php echo temsah_logo_html( home_url( '/' ) ); ?></div>
        <div class="auth-panel">
            <span class="auth-panel-kicker">ورود به حساب</span>
            <h1>به تمساح خوش اومدی!</h1>
            <p>با نام کاربری یا ایمیل و رمز عبور خود وارد پنل کاربری شوید.</p>

            <?php if ( ! empty( $_GET['auth_err'] ) ) : ?>
                <div class="tm-alert tm-alert-err"><?php echo esc_html( sanitize_text_field( wp_unslash( $_GET['auth_err'] ) ) ); ?></div>
            <?php elseif ( ! empty( $_GET['auth_ok'] ) ) : ?>
                <div class="tm-alert tm-alert-ok"><?php echo esc_html( sanitize_text_field( wp_unslash( $_GET['auth_ok'] ) ) ); ?></div>
            <?php endif; ?>

            <form class="demo-form" method="post" action="">
                <?php wp_nonce_field( 'temsah_login' ); ?>
                <input type="hidden" name="temsah_action" value="login">
                <?php if ( $redirect_to ) : ?><input type="hidden" name="redirect_to" value="<?php echo esc_attr( $redirect_to ); ?>"><?php endif; ?>
                <label>نام کاربری یا ایمیل<input name="user_login" placeholder="user@example.com" required type="text"/></label>
                <label>رمز عبور<input name="user_pass" placeholder="رمز عبور حساب" required type="password"/></label>
                <label style="display:flex;align-items:center;gap:8px;font-weight:400;"><input name="remember" type="checkbox" value="1" style="width:auto;"> مرا به خاطر بسپار</label>
                <button class="btn-primary auth-submit" type="submit">ورود به حساب <?php echo temsah_ico( 'arrow---left.svg' ); ?></button>
            </form>

            <p class="auth-agreement" style="margin-top:10px;"><a href="<?php echo esc_url( temsah_url( 'lost-password' ) ); ?>">رمز عبور خود را فراموش کرده‌اید؟</a></p>
            <div class="tm-alert tm-alert-info" style="font-size:12px;">ورود با کد یکبارمصرف پیامکی (OTP) نیازمند اتصال پنل پیامکی در تنظیمات سایت است.</div>
            <p class="auth-agreement">با ورود، <a href="<?php echo esc_url( temsah_url( 'terms' ) ); ?>">قوانین و شرایط استفاده</a> را می‌پذیرید.</p>
            <div class="auth-divide">حساب کاربری نداری؟</div>
            <a class="auth-secondary" href="<?php echo esc_url( temsah_url( 'register' ) ); ?>">ثبت‌نام در تمساح</a>
        </div>
        <a class="auth-back" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo temsah_ico( 'arrow---right.svg' ); ?> بازگشت به فروشگاه</a>
    </section>
</div></div>
<?php get_footer(); ?>
