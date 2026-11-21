<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
get_header();
get_template_part( 'template-parts/portal-sidebar-vendor' );
global $wpdb;
$vendor = Temsah_Security::get_vendor_by_user( get_current_user_id() );
$vid    = $vendor ? (int) $vendor->id : 0;
$t_fb   = Temsah_DB::table( 'product_feedback' );
$t_p    = Temsah_DB::table( 'products' );
$rows   = $vid ? $wpdb->get_results(
    $wpdb->prepare(
        "SELECT f.*, wp.post_title, u.display_name FROM {$t_fb} f INNER JOIN {$t_p} p ON p.id = f.product_id INNER JOIN {$wpdb->posts} wp ON wp.ID = p.post_id LEFT JOIN {$wpdb->users} u ON u.ID = f.user_id WHERE p.vendor_id = %d AND f.kind = 'question' ORDER BY f.id DESC",
        $vid
    )
) : array();
?>
<div class="v11-screen">
    <div class="v11-toolbar"><div><span class="v11-eyebrow">پرسش‌ها</span><h1>پرسش‌های پیش از خرید</h1><p>پاسخ به سوالات کاربران در صفحات محصولات شما.</p></div></div>
    <?php if ( ! empty( $_GET['ok'] ) ) : ?><div class="tm-alert tm-alert-ok"><?php echo esc_html( sanitize_text_field( wp_unslash( $_GET['ok'] ) ) ); ?></div><?php endif; ?>
    <section class="v11-surface">
        <?php if ( empty( $rows ) ) : ?>
            <div class="tm-empty-state"><p>هنوز پرسشی برای محصولات شما ثبت نشده است.</p></div>
        <?php else : foreach ( $rows as $r ) : ?>
            <div style="padding:14px 0;border-bottom:1px solid var(--line);">
                <b><?php echo esc_html( $r->post_title ); ?></b> · <small><?php echo esc_html( $r->display_name ?: 'کاربر' ); ?></small>
                <p><?php echo nl2br( esc_html( $r->content ) ); ?></p>
                <?php if ( ! empty( $r->vendor_reply ) ) : ?>
                    <div class="tm-alert tm-alert-ok">پاسخ شما: <?php echo nl2br( esc_html( $r->vendor_reply ) ); ?></div>
                <?php else : ?>
                    <form class="demo-form" method="post" action="">
                        <?php wp_nonce_field( 'temsah_vendor_reply_question' ); ?>
                        <input type="hidden" name="temsah_action" value="vendor_reply_question">
                        <input type="hidden" name="feedback_id" value="<?php echo (int) $r->id; ?>">
                        <textarea name="reply" required rows="2" placeholder="پاسخ خود را بنویسید..."></textarea>
                        <button class="btn-outline" style="margin-top:6px;" type="submit">ارسال پاسخ</button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endforeach; endif; ?>
    </section>
</div>
</div></main></div></div>
<?php get_footer(); ?>
