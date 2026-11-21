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
$t_p     = Temsah_DB::table( 'products' );
$bought  = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT DISTINCT p.id, wp.post_title FROM {$t_oi} oi INNER JOIN {$t_o} o ON o.id = oi.order_id INNER JOIN {$t_p} p ON p.id = oi.product_id INNER JOIN {$wpdb->posts} wp ON wp.ID = p.post_id WHERE o.user_id = %d AND o.status = 'paid'",
        $uid
    )
);
$pre_pid = absint( $_GET['product_id'] ?? 0 );
?>
<div class="v9-screen">
    <div class="v9-heading"><div><span class="v9-eyebrow">پشتیبانی</span><h1>ثبت تیکت جدید</h1><p>سوال فنی یا درخواست پشتیبانی خود را ارسال کنید.</p></div></div>
    <?php if ( ! empty( $_GET['err'] ) ) : ?><div class="tm-alert tm-alert-err"><?php echo esc_html( sanitize_text_field( wp_unslash( $_GET['err'] ) ) ); ?></div><?php endif; ?>
    <section class="v9-panel">
        <form class="demo-form" method="post" action="">
            <?php wp_nonce_field( 'temsah_buyer_create_ticket' ); ?>
            <input type="hidden" name="temsah_action" value="buyer_create_ticket">
            <label>مربوط به محصول / بخش
                <select name="product_id">
                    <option value="0">پشتیبانی عمومی مارکت‌پلیس</option>
                    <?php foreach ( $bought as $b ) : ?>
                        <option value="<?php echo (int) $b->id; ?>" <?php selected( $pre_pid, (int) $b->id ); ?>><?php echo esc_html( $b->post_title ); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>عنوان تیکت<input name="subject" placeholder="مثلاً راهنمایی نصب و تنظیمات" required type="text"/></label>
            <label>اولویت<select name="priority"><option value="normal">عادی</option><option value="high">زیاد</option><option value="low">کم</option></select></label>
            <label>شرح پیام<textarea name="message" required rows="5" placeholder="توضیحات کامل را بنویسید..."></textarea></label>
            <button class="btn-primary" type="submit">ارسال تیکت</button>
        </form>
    </section>
</div>
</div></main></div></div>
<?php get_footer(); ?>
