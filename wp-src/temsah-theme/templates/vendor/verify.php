<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
get_header();
get_template_part( 'template-parts/portal-sidebar-vendor' );
global $wpdb;
$vendor = Temsah_Security::get_vendor_by_user( get_current_user_id() );
$ver    = $vendor ? $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . Temsah_DB::table( 'vendor_verifications' ) . ' WHERE vendor_id = %d ORDER BY id DESC LIMIT 1', $vendor->id ) ) : null;
?>
<div class="v11-screen">
    <div class="v11-toolbar"><div><span class="v11-eyebrow">احراز هویت</span><h1>وضعیت تایید حساب فروشندگی</h1><p>مشخصات هویتی و بانکی ثبت‌شده جهت تسویه حساب.</p></div></div>
    <section class="v11-surface">
        <div class="spec-grid">
            <div><span>وضعیت فروشگاه</span><b><span class="v9-badge green"><?php echo esc_html( $vendor ? $vendor->status : '—' ); ?></span></b></div>
            <div><span>نام متقاضی</span><b><?php echo esc_html( $ver->full_name ?? '—' ); ?></b></div>
            <div><span>شماره موبایل</span><b><?php echo esc_html( $ver->mobile ?? '—' ); ?></b></div>
            <div><span>شماره شبا تاییدشده</span><b><code><?php echo esc_html( $ver->iban ?? '—' ); ?></code></b></div>
            <div><span>تاریخ تایید</span><b><?php echo esc_html( temsah_format_date( $ver->reviewed_at ?? null ) ); ?></b></div>
        </div>
        <div style="margin-top:16px;"><a class="btn-outline" href="<?php echo esc_url( temsah_url( 'account/seller-application' ) ); ?>">بروزرسانی مدارک هویتی</a></div>
    </section>
</div>
</div></main></div></div>
<?php get_footer(); ?>
