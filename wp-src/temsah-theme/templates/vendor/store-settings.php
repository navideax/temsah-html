<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
get_header();
get_template_part( 'template-parts/portal-sidebar-vendor' );
$vendor = Temsah_Security::get_vendor_by_user( get_current_user_id() );
?>
<div class="v11-screen">
    <div class="v11-toolbar"><div><span class="v11-eyebrow">تنظیمات فروشگاه</span><h1>ویرایش اطلاعات فروشگاه</h1><p>مشخصات عمومی نمایش‌داده‌شده در صفحه فروشگاه و کارت محصولات.</p></div></div>
    <?php if ( ! empty( $_GET['ok'] ) ) : ?><div class="tm-alert tm-alert-ok"><?php echo esc_html( sanitize_text_field( wp_unslash( $_GET['ok'] ) ) ); ?></div><?php endif; ?>
    <section class="v11-surface">
        <?php if ( ! $vendor ) : ?>
            <div class="tm-empty-state"><p>فروشگاهی برای این حساب ثبت نشده است.</p></div>
        <?php else : ?>
            <form class="demo-form" method="post" action="">
                <?php wp_nonce_field( 'temsah_vendor_save_store_settings' ); ?>
                <input type="hidden" name="temsah_action" value="vendor_save_store_settings">
                <label>نام فروشگاه<input name="store_name" required type="text" value="<?php echo esc_attr( $vendor->store_name ); ?>"/></label>
                <label>تخصص / عنوان فروشگاه<input name="specialty" type="text" value="<?php echo esc_attr( $vendor->specialty ); ?>"/></label>
                <label>لینک وب‌سایت نمونه‌کار<input name="portfolio_url" type="url" value="<?php echo esc_attr( $vendor->portfolio_url ); ?>"/></label>
                <div class="input-grid">
                    <label>ایمیل پشتیبانی فروشگاه<input name="support_email" type="email" value="<?php echo esc_attr( $vendor->support_email ); ?>"/></label>
                    <label>ساعات پاسخگویی<input name="support_hours" placeholder="شنبه تا چهارشنبه ۹ تا ۱۸" type="text" value="<?php echo esc_attr( $vendor->support_hours ); ?>"/></label>
                </div>
                <label>معرفی فروشگاه<textarea name="bio" rows="4"><?php echo esc_textarea( $vendor->bio ); ?></textarea></label>
                <button class="btn-primary" type="submit">ذخیره تنظیمات فروشگاه</button>
            </form>
        <?php endif; ?>
    </section>
</div>
</div></main></div></div>
<?php get_footer(); ?>
