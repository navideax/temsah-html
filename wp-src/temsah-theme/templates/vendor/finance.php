<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
get_header();
get_template_part( 'template-parts/portal-sidebar-vendor' );
global $wpdb;
$vendor = Temsah_Security::get_vendor_by_user( get_current_user_id() );
$vid    = $vendor ? (int) $vendor->id : 0;
$ledger = $vid ? $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . Temsah_DB::table( 'vendor_wallet_ledger' ) . ' WHERE vendor_id = %d ORDER BY id DESC LIMIT 50', $vid ) ) : array();
?>
<div class="v11-screen">
    <div class="v11-toolbar">
        <div><span class="v11-eyebrow">پنل فروشندگان / گزارش مالی</span><h1>گزارش مالی و دفتر کل کیف پول</h1><p>موجودی کل، نرخ کمیسیون و گردش مالی فروشگاه شما.</p></div>
        <form method="post" action="">
            <?php wp_nonce_field( 'temsah_vendor_export_report' ); ?>
            <input type="hidden" name="temsah_action" value="vendor_export_report">
            <button class="v9-action" type="submit"><?php echo temsah_ico( 'download.svg' ); ?> خروجی CSV گزارش فروش</button>
        </form>
    </div>

    <div class="v11-kpis report">
        <div class="v11-kpi"><span class="v9-icon green"><?php echo temsah_ico( 'wallet.svg' ); ?></span><small>موجودی قابل برداشت</small><strong><?php echo esc_html( temsah_format_toman( $vendor ? $vendor->available_balance : 0 ) ); ?></strong></div>
        <div class="v11-kpi"><span class="v9-icon blue"><?php echo temsah_ico( 'activity.svg' ); ?></span><small>درآمد تجمعی شما</small><strong><?php echo esc_html( temsah_format_toman( $vendor ? $vendor->total_earnings : 0 ) ); ?></strong></div>
        <div class="v11-kpi"><span class="v9-icon amber"><?php echo temsah_ico( 'star.svg' ); ?></span><small>سهم فروشنده از هر فروش</small><strong><?php echo esc_html( temsah_fa_digits( $vendor ? $vendor->commission_rate : 70 ) ); ?>٪</strong></div>
    </div>

    <section class="v11-surface">
        <div class="v11-section-head"><h2>گردش حساب کیف پول</h2><a class="v11-btn muted" href="<?php echo esc_url( temsah_url( 'vendor/withdrawals' ) ); ?>">ثبت درخواست تسویه</a></div>
        <?php if ( empty( $ledger ) ) : ?>
            <div class="tm-empty-state"><p>تراکنش مالی ثبت نشده است.</p></div>
        <?php else : ?>
            <div class="v9-table-wrap"><table class="v9-table"><thead><tr><th>شرح</th><th>نوع</th><th>مبلغ</th><th>مانده پس از تراکنش</th><th>تاریخ</th></tr></thead><tbody>
                <?php foreach ( $ledger as $l ) : ?>
                <tr>
                    <td><?php echo esc_html( $l->note ); ?></td>
                    <td><?php echo esc_html( $l->entry_type ); ?></td>
                    <td style="color:<?php echo $l->amount >= 0 ? '#047857' : '#b91c1c'; ?>;"><b><?php echo $l->amount >= 0 ? '+' : ''; ?><?php echo esc_html( temsah_format_toman( $l->amount ) ); ?></b></td>
                    <td><?php echo esc_html( temsah_format_toman( $l->balance_after ) ); ?></td>
                    <td><?php echo esc_html( temsah_format_date( $l->created_at ) ); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody></table></div>
        <?php endif; ?>
    </section>
</div>
</div></main></div></div>
<?php get_footer(); ?>
