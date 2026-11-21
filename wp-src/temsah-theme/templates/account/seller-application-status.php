<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
get_header();
get_template_part( 'template-parts/portal-sidebar-buyer' );
global $wpdb;
$uid    = get_current_user_id();
$vendor = Temsah_Security::get_vendor_by_user( $uid );
$ver    = $vendor ? $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . Temsah_DB::table( 'vendor_verifications' ) . ' WHERE vendor_id = %d ORDER BY id DESC LIMIT 1', $vendor->id ) ) : null;
?>
<div class="v9-screen onboard">
    <div class="onboard-top">
        <div><span class="v9-eyebrow">پیگیری احراز هویت</span><h1>وضعیت درخواست فروشندگی</h1><p>آخرین وضعیت بررسی مدارک و دسترسی پنل فروشنده.</p></div>
        <a class="btn soft" href="<?php echo esc_url( temsah_url( 'account/seller-application' ) ); ?>">ویرایش / ارسال مجدد فرم</a>
    </div>

    <?php if ( ! empty( $_GET['app_ok'] ) ) : ?>
        <div class="tm-alert tm-alert-ok"><?php echo esc_html( sanitize_text_field( wp_unslash( $_GET['app_ok'] ) ) ); ?></div>
    <?php endif; ?>

    <section class="v9-panel">
        <?php if ( ! $vendor ) : ?>
            <div class="tm-empty-state">
                <h3>هنوز درخواستی ثبت نکرده‌اید</h3>
                <p>برای فروش محصولات خود در تمساح، فرم درخواست فروشندگی را تکمیل کنید.</p>
                <a class="btn-primary" href="<?php echo esc_url( temsah_url( 'account/seller-application' ) ); ?>">ثبت درخواست فروشندگی</a>
            </div>
        <?php else : ?>
            <div class="spec-grid">
                <div><span>نام فروشگاه</span><b><?php echo esc_html( $vendor->store_name ); ?></b></div>
                <div><span>وضعیت درخواست</span><b>
                    <?php
                    echo match ( $vendor->status ) {
                        'approved'  => '<span class="v9-badge green">تاییدشده</span>',
                        'rejected'  => '<span class="tm-badge-tag err">رد شده</span>',
                        default     => '<span class="tm-badge-tag warn">در انتظار بررسی مدیر</span>',
                    };
                    ?>
                </b></div>
                <div><span>شماره شبا</span><b><?php echo esc_html( $ver->iban ?? '—' ); ?></b></div>
                <div><span>تاریخ ثبت</span><b><?php echo esc_html( temsah_format_date( $vendor->created_at ) ); ?></b></div>
            </div>
            <?php if ( 'rejected' === $vendor->status && ! empty( $ver->rejection_reason ) ) : ?>
                <div class="tm-alert tm-alert-err" style="margin-top:16px;"><b>توضیح مدیریت:</b> <?php echo esc_html( $ver->rejection_reason ); ?></div>
            <?php elseif ( 'approved' === $vendor->status ) : ?>
                <div style="margin-top:18px;"><a class="btn-primary" href="<?php echo esc_url( temsah_url( 'vendor' ) ); ?>">ورود به پنل فروشندگان</a></div>
            <?php endif; ?>
        <?php endif; ?>
    </section>
</div>
</div></main></div></div>
<?php get_footer(); ?>
