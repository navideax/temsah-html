<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
get_header();
get_template_part( 'template-parts/portal-sidebar-vendor' );
global $wpdb;
$vendor = Temsah_Security::get_vendor_by_user( get_current_user_id() );
$vid    = $vendor ? (int) $vendor->id : 0;
$rows   = $vid ? $wpdb->get_results(
    $wpdb->prepare(
        "SELECT c.*, oi.product_title, o.order_number FROM " . Temsah_DB::table( 'commissions' ) . " c INNER JOIN " . Temsah_DB::table( 'order_items' ) . " oi ON oi.id = c.order_item_id INNER JOIN " . Temsah_DB::table( 'orders' ) . " o ON o.id = c.order_id WHERE c.vendor_id = %d ORDER BY c.id DESC LIMIT 100",
        $vid
    )
) : array();
?>
<div class="v11-screen">
    <div class="v11-toolbar"><div><span class="v11-eyebrow">پنل فروشندگان / تراکنش‌ها</span><h1>تراکنش‌های فروش و کمیسیون</h1><p>ریز محاسبه سهم فروشنده و کارمزد پلتفرم در هر سفارش.</p></div></div>
    <section class="v11-surface">
        <?php if ( empty( $rows ) ) : ?>
            <div class="tm-empty-state"><p>هنوز فروش ثبت‌شده‌ای وجود ندارد.</p></div>
        <?php else : ?>
            <div class="v9-table-wrap"><table class="v9-table"><thead><tr><th>شماره سفارش</th><th>محصول</th><th>مبلغ فروش</th><th>نرخ سهم</th><th>سهم خالص شما</th><th>تاریخ</th></tr></thead><tbody>
                <?php foreach ( $rows as $r ) : ?>
                <tr>
                    <td><b><?php echo esc_html( $r->order_number ); ?></b></td>
                    <td><?php echo esc_html( $r->product_title ); ?></td>
                    <td><?php echo esc_html( temsah_format_toman( $r->gross_amount ) ); ?></td>
                    <td><?php echo esc_html( temsah_fa_digits( $r->vendor_rate ) ); ?>٪</td>
                    <td><strong style="color:#047857;"><?php echo esc_html( temsah_format_toman( $r->vendor_amount ) ); ?></strong></td>
                    <td><?php echo esc_html( temsah_format_date( $r->created_at ) ); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody></table></div>
        <?php endif; ?>
    </section>
</div>
</div></main></div></div>
<?php get_footer(); ?>
