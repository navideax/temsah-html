<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
get_header();
get_template_part( 'template-parts/portal-sidebar-vendor' );
$user   = wp_get_current_user();
$mobile = get_user_meta( $user->ID, 'temsah_mobile', true );
$vendor = Temsah_Security::get_vendor_by_user( $user->ID );
?>
<div class="v11-screen">
    <div class="v11-toolbar"><div><span class="v11-eyebrow">تنظیمات حساب</span><h1>پروفایل کاربری فروشنده</h1><p>مشخصات حساب کاربری متصل به فروشگاه شما.</p></div></div>
    <section class="v11-surface">
        <div class="spec-grid">
            <div><span>نام نمایشی</span><b><?php echo esc_html( $user->display_name ?: $user->user_login ); ?></b></div>
            <div><span>ایمیل</span><b><?php echo esc_html( $user->user_email ); ?></b></div>
            <div><span>شماره موبایل</span><b><?php echo esc_html( $mobile ?: 'ثبت نشده' ); ?></b></div>
            <div><span>نام فروشگاه</span><b><?php echo esc_html( $vendor ? $vendor->store_name : '—' ); ?></b></div>
        </div>
        <div style="margin-top:16px;"><a class="btn-outline" href="<?php echo esc_url( temsah_url( 'account/settings' ) ); ?>">ویرایش رمز عبور و مشخصات فردی در تنظیمات حساب</a></div>
    </section>
</div>
</div></main></div></div>
<?php get_footer(); ?>
