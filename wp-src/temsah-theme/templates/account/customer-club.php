<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
get_header();
get_template_part( 'template-parts/portal-sidebar-buyer' );
global $wpdb;
$uid         = get_current_user_id();
$total_spent = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COALESCE(SUM(total),0) FROM ' . Temsah_DB::table( 'orders' ) . " WHERE user_id = %d AND status = 'paid'", $uid ) );
$points      = (int) floor( $total_spent / 10000 );
$tier        = $points >= 500 ? 'طلایی' : ( $points >= 150 ? 'نقره‌ای' : 'برنزی' );
$coupons     = $wpdb->get_results( 'SELECT * FROM ' . Temsah_DB::table( 'coupons' ) . ' WHERE is_active = 1 ORDER BY id DESC LIMIT 10' );
?>
<div class="v9-screen">
    <div class="v9-heading"><div><span class="v9-eyebrow">باشگاه مشتریان</span><h1>امتیاز و سطح کاربری</h1><p>به ازای هر ۱۰٬۰۰۰ تومان خرید موفق، ۱ امتیاز باشگاه دریافت می‌کنید.</p></div></div>
    <div class="v9-stats">
        <div class="v9-metric"><span class="v9-icon amber"><?php echo temsah_ico( 'star.svg' ); ?></span><small>امتیاز کل شما</small><strong><?php echo esc_html( temsah_format_number( $points ) ); ?></strong><span class="v9-metric-sub">سطح فعلی: <?php echo esc_html( $tier ); ?></span></div>
        <div class="v9-metric"><span class="v9-icon green"><?php echo temsah_ico( 'wallet.svg' ); ?></span><small>مجموع خرید موفق</small><strong><?php echo esc_html( temsah_format_toman( $total_spent ) ); ?></strong></div>
    </div>
    <section class="v9-panel">
        <div class="v9-section-title"><h2>کدهای تخفیف فعال مارکت‌پلیس</h2></div>
        <?php if ( empty( $coupons ) ) : ?>
            <div class="tm-empty-state"><p>در حال حاضر کد تخفیف عمومی فعالی تعریف نشده است.</p></div>
        <?php else : ?>
            <div class="v9-table-wrap"><table class="v9-table"><thead><tr><th>کد تخفیف</th><th>نوع / مقدار</th><th>حداقل خرید</th></tr></thead><tbody>
                <?php foreach ( $coupons as $c ) : ?>
                <tr><td><code><?php echo esc_html( $c->code ); ?></code></td><td><?php echo 'percent' === $c->type ? esc_html( temsah_fa_digits( $c->amount ) . '٪' ) : esc_html( temsah_format_toman( $c->amount ) ); ?></td><td><?php echo esc_html( temsah_format_toman( $c->min_order ) ); ?></td></tr>
                <?php endforeach; ?>
            </tbody></table></div>
        <?php endif; ?>
    </section>
</div>
</div></main></div></div>
<?php get_footer(); ?>
