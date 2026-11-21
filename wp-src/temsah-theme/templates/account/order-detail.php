<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
$order_id = absint( $_GET['id'] ?? 0 );
$order    = Temsah_Security::user_owns_order( $order_id );
if ( ! $order ) {
    status_header( 403 );
    wp_die( 'شما مجوز مشاهده این سفارش را ندارید.', 'خطای دسترسی (۴۰۳)', array( 'response' => 403 ) );
}

get_header();
get_template_part( 'template-parts/portal-sidebar-buyer' );

global $wpdb;
$items   = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . Temsah_DB::table( 'order_items' ) . ' WHERE order_id = %d', $order->id ) );
$payment = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . Temsah_DB::table( 'payments' ) . ' WHERE order_id = %d ORDER BY id DESC LIMIT 1', $order->id ) );
?>
<div class="v9-screen">
    <div class="v9-heading">
        <div><span class="v9-eyebrow">جزئیات سفارش</span><h1>فاکتور سفارش <?php echo esc_html( $order->order_number ); ?></h1><p>ثبت‌شده در <?php echo esc_html( temsah_format_date( $order->created_at ) ); ?></p></div>
        <a class="v9-action" href="<?php echo esc_url( temsah_url( 'account/orders' ) ); ?>">بازگشت به لیست سفارش‌ها</a>
    </div>

    <?php if ( ! empty( $_GET['paid'] ) ) : ?>
        <div class="tm-alert tm-alert-ok">سفارش شما با موفقیت تکمیل شد و فایل‌ها آماده دانلود هستند.</div>
    <?php endif; ?>

    <section class="v9-panel">
        <div class="v9-section-title"><h2>اقلام سفارش</h2><span class="v9-badge <?php echo 'paid' === $order->status ? 'green' : ''; ?>"><?php echo esc_html( 'paid' === $order->status ? 'پرداخت‌شده' : $order->status ); ?></span></div>
        <div class="v9-table-wrap"><table class="v9-table"><thead><tr><th>محصول</th><th>قیمت محصول</th><th>خدمات تکمیلی</th><th>جمع ردیف</th></tr></thead><tbody>
            <?php foreach ( $items as $it ) : ?>
            <tr>
                <td><b><?php echo esc_html( $it->product_title ); ?></b></td>
                <td><?php echo esc_html( temsah_format_toman( $it->unit_price ) ); ?></td>
                <td><?php echo esc_html( temsah_format_toman( $it->services_price ) ); ?></td>
                <td><strong><?php echo esc_html( temsah_format_toman( $it->line_total ) ); ?></strong></td>
            </tr>
            <?php endforeach; ?>
        </tbody></table></div>

        <div class="spec-grid" style="margin-top:18px;">
            <div><span>جمع محصولات</span><b><?php echo esc_html( temsah_format_toman( $order->subtotal ) ); ?></b></div>
            <div><span>تخفیف محصولات</span><b><?php echo esc_html( temsah_format_toman( $order->product_discount ) ); ?></b></div>
            <div><span>کد تخفیف</span><b><?php echo esc_html( $order->coupon_code ? $order->coupon_code . ' (−' . temsah_format_toman( $order->coupon_discount ) . ')' : 'ندارد' ); ?></b></div>
            <div><span>مبلغ نهایی پرداخت‌شده</span><b><?php echo esc_html( temsah_format_toman( $order->total ) ); ?></b></div>
            <?php if ( $payment && $payment->ref_id ) : ?><div><span>کد رهگیری بانکی</span><b><?php echo esc_html( $payment->ref_id ); ?></b></div><?php endif; ?>
        </div>

        <?php if ( 'paid' === $order->status ) : ?>
            <div style="margin-top:18px;"><a class="v9-action" href="<?php echo esc_url( temsah_url( 'account/downloads' ) ); ?>"><?php echo temsah_ico( 'download.svg' ); ?> دریافت فایل‌ها از کتابخانه دانلود</a></div>
        <?php endif; ?>
    </section>
</div>
</div></main></div></div>
<?php get_footer(); ?>
