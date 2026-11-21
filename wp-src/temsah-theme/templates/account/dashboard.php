<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
get_header();
get_template_part( 'template-parts/portal-sidebar-buyer' );

global $wpdb;
$uid     = get_current_user_id();
$t_o     = Temsah_DB::table( 'orders' );
$t_oi    = Temsah_DB::table( 'order_items' );
$t_lic   = Temsah_DB::table( 'licenses' );
$t_tk    = Temsah_DB::table( 'tickets' );
$t_not   = Temsah_DB::table( 'notifications' );

$purchased_count = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(DISTINCT oi.product_id) FROM {$t_oi} oi INNER JOIN {$t_o} o ON o.id = oi.order_id WHERE o.user_id = %d AND o.status = 'paid'", $uid ) );
$orders_count    = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$t_o} WHERE user_id = %d", $uid ) );
$total_spent     = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COALESCE(SUM(total),0) FROM {$t_o} WHERE user_id = %d AND status = 'paid'", $uid ) );
$open_tickets    = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$t_tk} WHERE user_id = %d AND status != 'closed'", $uid ) );
$club_points     = (int) floor( $total_spent / 10000 );
$recent_orders   = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$t_o} WHERE user_id = %d ORDER BY id DESC LIMIT 5", $uid ) );
$notifs          = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$t_not} WHERE (user_id = %d OR (user_id IS NULL AND audience IN ('all','buyer'))) ORDER BY id DESC LIMIT 4", $uid ) );
?>
<div class="v9-screen">
    <div class="v9-heading">
        <div><span class="v9-eyebrow">سامانه خریداران / TEMSAH</span><h1>داشبورد خریدار</h1><p>مرور سریع خریدها، دانلودهای مجاز، تیکت‌ها و اعلانات حساب شما.</p></div>
        <a class="v9-action" href="<?php echo esc_url( temsah_url( 'shop' ) ); ?>"><?php echo temsah_ico( 'discovery.svg' ); ?> مشاهده محصولات</a>
    </div>

    <div class="v9-stats">
        <div class="v9-metric"><span class="v9-icon blue"><?php echo temsah_ico( 'download.svg' ); ?></span><small>محصولات خریداری‌شده</small><strong><?php echo esc_html( temsah_format_number( $purchased_count ) ); ?></strong><span class="v9-metric-sub">آماده دانلود در کتابخانه</span></div>
        <div class="v9-metric"><span class="v9-icon amber"><?php echo temsah_ico( 'ticket.svg' ); ?></span><small>امتیاز باشگاه مشتریان</small><strong><?php echo esc_html( temsah_format_number( $club_points ) ); ?></strong><span class="v9-metric-sub">بر اساس خریدهای تاییدشده</span></div>
        <div class="v9-metric"><span class="v9-icon green"><?php echo temsah_ico( 'wallet.svg' ); ?></span><small>سفارش‌های ثبت‌شده</small><strong><?php echo esc_html( temsah_format_number( $orders_count ) ); ?></strong><span class="v9-metric-sub">مجموع پرداختی: <?php echo esc_html( temsah_format_toman( $total_spent ) ); ?></span></div>
        <div class="v9-metric"><span class="v9-icon pink"><?php echo temsah_ico( 'chat.svg' ); ?></span><small>تیکت‌های باز</small><strong><?php echo esc_html( temsah_format_number( $open_tickets ) ); ?></strong><span class="v9-metric-sub"><a href="<?php echo esc_url( temsah_url( 'account/tickets' ) ); ?>">مشاهده تیکت‌ها</a></span></div>
    </div>

    <section class="v9-panel">
        <div class="v9-section-title"><h2>آخرین سفارش‌ها</h2><a class="v9-textbutton" href="<?php echo esc_url( temsah_url( 'account/orders' ) ); ?>">همه سفارش‌ها</a></div>
        <?php if ( empty( $recent_orders ) ) : ?>
            <div class="tm-empty-state"><p>هنوز سفارشی ثبت نکرده‌اید.</p></div>
        <?php else : ?>
            <div class="v9-table-wrap"><table class="v9-table"><thead><tr><th>شماره سفارش</th><th>وضعیت</th><th>تاریخ</th><th>مبلغ</th><th>جزئیات</th></tr></thead><tbody>
                <?php foreach ( $recent_orders as $o ) : ?>
                <tr>
                    <td><b><?php echo esc_html( $o->order_number ); ?></b></td>
                    <td><span class="v9-badge <?php echo 'paid' === $o->status ? 'green' : ''; ?>"><?php echo esc_html( 'paid' === $o->status ? 'پرداخت‌شده' : $o->status ); ?></span></td>
                    <td><?php echo esc_html( temsah_format_date( $o->created_at ) ); ?></td>
                    <td><?php echo esc_html( temsah_format_toman( $o->total ) ); ?></td>
                    <td><a class="v9-textbutton" href="<?php echo esc_url( temsah_url( 'account/order-detail', array( 'id' => $o->id ) ) ); ?>">مشاهده</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody></table></div>
        <?php endif; ?>
    </section>

    <?php if ( ! empty( $notifs ) ) : ?>
    <section class="v9-panel" style="margin-top:18px;">
        <div class="v9-section-title"><h2>اعلانات اخیر</h2><a class="v9-textbutton" href="<?php echo esc_url( temsah_url( 'account/announcements' ) ); ?>">مشاهده همه</a></div>
        <?php foreach ( $notifs as $n ) : ?>
            <div style="padding:10px 0;border-bottom:1px solid var(--line);"><b><?php echo esc_html( $n->title ); ?></b><p style="margin:4px 0 0;color:var(--muted);"><?php echo esc_html( $n->body ); ?></p></div>
        <?php endforeach; ?>
    </section>
    <?php endif; ?>
</div>
</div></main></div></div>
<?php get_footer(); ?>
