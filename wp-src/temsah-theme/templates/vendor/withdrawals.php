<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
get_header();
get_template_part( 'template-parts/portal-sidebar-vendor' );
global $wpdb;
$vendor   = Temsah_Security::get_vendor_by_user( get_current_user_id() );
$vid      = $vendor ? (int) $vendor->id : 0;
$t_w      = Temsah_DB::table( 'withdrawals' );
$rows     = $vid ? $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$t_w} WHERE vendor_id = %d ORDER BY id DESC", $vid ) ) : array();
$pending  = $vid ? (int) $wpdb->get_var( $wpdb->prepare( "SELECT COALESCE(SUM(amount),0) FROM {$t_w} WHERE vendor_id = %d AND status = 'pending'", $vid ) ) : 0;
$paid_tot = $vid ? (int) $wpdb->get_var( $wpdb->prepare( "SELECT COALESCE(SUM(amount),0) FROM {$t_w} WHERE vendor_id = %d AND status = 'paid'", $vid ) ) : 0;
$ver      = $vid ? $wpdb->get_row( $wpdb->prepare( 'SELECT iban FROM ' . Temsah_DB::table( 'vendor_verifications' ) . ' WHERE vendor_id = %d ORDER BY id DESC LIMIT 1', $vid ) ) : null;
$settings = get_option( 'temsah_settings', array() );
$min_wd   = (int) ( $settings['min_withdrawal_amount'] ?? 100000 );
?>
<div class="v11-screen">
    <div class="v11-toolbar"><div><span class="v11-eyebrow">پنل فروشندگان / تسویه‌های فروشنده</span><h1>تسویه‌های فروشنده</h1><p>ثبت درخواست برداشت وجه و مشاهده وضعیت پرداخت‌ها.</p></div></div>
    <?php if ( ! empty( $_GET['ok'] ) ) : ?><div class="tm-alert tm-alert-ok"><?php echo esc_html( sanitize_text_field( wp_unslash( $_GET['ok'] ) ) ); ?></div><?php endif; ?>
    <?php if ( ! empty( $_GET['err'] ) ) : ?><div class="tm-alert tm-alert-err"><?php echo esc_html( sanitize_text_field( wp_unslash( $_GET['err'] ) ) ); ?></div><?php endif; ?>

    <div class="v11-kpis report">
        <div class="v11-kpi"><span class="v9-icon green"><?php echo temsah_ico( 'wallet.svg' ); ?></span><small>موجودی قابل برداشت</small><strong><?php echo esc_html( temsah_format_toman( $vendor ? $vendor->available_balance : 0 ) ); ?></strong></div>
        <div class="v11-kpi"><span class="v9-icon amber"><?php echo temsah_ico( 'time-circle.svg' ); ?></span><small>در انتظار تسویه</small><strong><?php echo esc_html( temsah_format_toman( $pending ) ); ?></strong></div>
        <div class="v11-kpi"><span class="v9-icon blue"><?php echo temsah_ico( 'tick-square.svg' ); ?></span><small>پرداخت‌های قبلی</small><strong><?php echo esc_html( temsah_format_toman( $paid_tot ) ); ?></strong></div>
    </div>

    <section class="v11-surface" style="margin-bottom:20px;">
        <div class="v11-section-head"><h2>ثبت درخواست تسویه جدید</h2><small>حداقل مبلغ برداشت: <?php echo esc_html( temsah_format_toman( $min_wd ) ); ?></small></div>
        <form class="demo-form" method="post" action="">
            <?php wp_nonce_field( 'temsah_vendor_request_withdrawal' ); ?>
            <input type="hidden" name="temsah_action" value="vendor_request_withdrawal">
            <div class="input-grid">
                <label>مبلغ برداشت (تومان)<input min="<?php echo (int) $min_wd; ?>" max="<?php echo (int) ( $vendor ? $vendor->available_balance : 0 ); ?>" name="amount" required type="number" value="<?php echo (int) ( $vendor ? $vendor->available_balance : 0 ); ?>"/></label>
                <label>شماره شبا<input name="iban" pattern="IR[0-9]{24}" placeholder="IR120570000000000000000001" required type="text" value="<?php echo esc_attr( $ver->iban ?? '' ); ?>"/></label>
            </div>
            <button class="btn-primary" type="submit" <?php disabled( ! $vendor || (int) $vendor->available_balance < $min_wd ); ?>>ثبت درخواست برداشت</button>
        </form>
    </section>

    <section class="v11-surface">
        <div class="v11-section-head"><h2>تاریخچه تسویه‌ها</h2></div>
        <?php if ( empty( $rows ) ) : ?>
            <div class="tm-empty-state"><p>هنوز درخواست تسویه‌ای ثبت نشده است.</p></div>
        <?php else : ?>
            <div class="v9-table-wrap"><table class="v9-table"><thead><tr><th>شناسه</th><th>مبلغ</th><th>شماره شبا</th><th>وضعیت</th><th>توضیح مدیر</th><th>تاریخ</th></tr></thead><tbody>
                <?php foreach ( $rows as $r ) : ?>
                <tr>
                    <td>#<?php echo (int) $r->id; ?></td>
                    <td><b><?php echo esc_html( temsah_format_toman( $r->amount ) ); ?></b></td>
                    <td><code><?php echo esc_html( $r->iban ); ?></code></td>
                    <td><span class="v9-badge <?php echo 'paid' === $r->status ? 'green' : ''; ?>"><?php echo esc_html( $r->status ); ?></span></td>
                    <td><?php echo esc_html( $r->admin_note ?: '—' ); ?></td>
                    <td><?php echo esc_html( temsah_format_date( $r->created_at ) ); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody></table></div>
        <?php endif; ?>
    </section>
</div>
</div></main></div></div>
<?php get_footer(); ?>
