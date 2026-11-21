<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
get_header();
get_template_part( 'template-parts/portal-sidebar-buyer' );
global $wpdb;
$uid    = get_current_user_id();
$orders = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . Temsah_DB::table( 'orders' ) . ' WHERE user_id = %d ORDER BY id DESC', $uid ) );
?>
<div class="v9-screen">
    <div class="v9-heading">
        <div><span class="v9-eyebrow">سامانه خریداران / TEMSAH</span><h1>سفارش‌ها و امور مالی</h1><p>مشاهده لیست سفارش‌ها، مبلغ پرداخت‌شده و جزئیات فاکتور.</p></div>
        <a class="v9-action" href="<?php echo esc_url( temsah_url( 'shop' ) ); ?>"><?php echo temsah_ico( 'discovery.svg' ); ?> مشاهده فروشگاه</a>
    </div>

    <section class="v9-panel">
        <div class="v9-section-title"><h2>لیست سفارشات شما</h2></div>
        <?php if ( empty( $orders ) ) : ?>
            <div class="tm-empty-state"><p>هنوز سفارشی ثبت نکرده‌اید.</p></div>
        <?php else : ?>
            <div class="v9-table-wrap"><table class="v9-table"><thead><tr><th>شماره سفارش</th><th>درگاه</th><th>وضعیت</th><th>تاریخ</th><th>مبلغ</th><th>عملیات</th></tr></thead><tbody>
            <?php foreach ( $orders as $o ) : ?>
                <tr>
                    <td><b><?php echo esc_html( $o->order_number ); ?></b></td>
                    <td><?php echo esc_html( $o->gateway_id ); ?></td>
                    <td><span class="v9-badge <?php echo 'paid' === $o->status ? 'green' : ''; ?>"><?php echo esc_html( 'paid' === $o->status ? 'پرداخت‌شده' : ( 'pending_payment' === $o->status ? 'در انتظار پرداخت' : 'ناموفق' ) ); ?></span></td>
                    <td><?php echo esc_html( temsah_format_date( $o->created_at ) ); ?></td>
                    <td><?php echo esc_html( temsah_format_toman( $o->total ) ); ?></td>
                    <td><a class="v9-textbutton" href="<?php echo esc_url( temsah_url( 'account/order-detail', array( 'id' => $o->id ) ) ); ?>">مشاهده جزئیات</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody></table></div>
        <?php endif; ?>
    </section>
</div>
</div></main></div></div>
<?php get_footer(); ?>
