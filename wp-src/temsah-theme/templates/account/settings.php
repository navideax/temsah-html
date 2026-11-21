<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
get_header();
get_template_part( 'template-parts/portal-sidebar-buyer' );
$user   = wp_get_current_user();
$mobile = get_user_meta( $user->ID, 'temsah_mobile', true );
$bio    = get_user_meta( $user->ID, 'temsah_bio', true );
?>
<div class="v9-screen">
    <div class="v9-heading"><div><span class="v9-eyebrow">تنظیمات حساب</span><h1>ویرایش پروفایل و امنیت</h1><p>مشخصات کاربری و رمز عبور خود را مدیریت کنید.</p></div></div>
    <?php if ( ! empty( $_GET['ok'] ) ) : ?><div class="tm-alert tm-alert-ok"><?php echo esc_html( sanitize_text_field( wp_unslash( $_GET['ok'] ) ) ); ?></div><?php endif; ?>
    <?php if ( ! empty( $_GET['err'] ) ) : ?><div class="tm-alert tm-alert-err"><?php echo esc_html( sanitize_text_field( wp_unslash( $_GET['err'] ) ) ); ?></div><?php endif; ?>

    <div class="page-two-column" style="grid-template-columns:1fr 1fr;gap:20px;">
        <section class="v9-panel">
            <div class="v9-section-title"><h2>مشخصات فردی</h2></div>
            <form class="demo-form" method="post" action="">
                <?php wp_nonce_field( 'temsah_buyer_save_settings' ); ?>
                <input type="hidden" name="temsah_action" value="buyer_save_settings">
                <label>نام نمایشی<input name="display_name" required type="text" value="<?php echo esc_attr( $user->display_name ); ?>"/></label>
                <label>ایمیل (غیرقابل تغییر)<input disabled type="email" value="<?php echo esc_attr( $user->user_email ); ?>"/></label>
                <label>شماره موبایل<input name="mobile" type="tel" value="<?php echo esc_attr( $mobile ); ?>"/></label>
                <label>درباره من<textarea name="bio" rows="3"><?php echo esc_textarea( $bio ); ?></textarea></label>
                <button class="btn-primary" type="submit">ذخیره تغییرات</button>
            </form>
        </section>

        <section class="v9-panel">
            <div class="v9-section-title"><h2>تغییر رمز عبور</h2></div>
            <form class="demo-form" method="post" action="">
                <?php wp_nonce_field( 'temsah_buyer_change_password' ); ?>
                <input type="hidden" name="temsah_action" value="buyer_change_password">
                <label>رمز عبور فعلی<input name="current_password" required type="password"/></label>
                <label>رمز عبور جدید (حداقل ۸ کاراکتر)<input minlength="8" name="new_password" required type="password"/></label>
                <label>تکرار رمز عبور جدید<input minlength="8" name="confirm_password" required type="password"/></label>
                <button class="btn-outline" type="submit">بروزرسانی رمز عبور</button>
            </form>
        </section>
    </div>
</div>
</div></main></div></div>
<?php get_footer(); ?>
