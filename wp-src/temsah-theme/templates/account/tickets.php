<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
get_header();
get_template_part( 'template-parts/portal-sidebar-buyer' );
global $wpdb;
$uid     = get_current_user_id();
$t_tk    = Temsah_DB::table( 'tickets' );
$t_p     = Temsah_DB::table( 'products' );
$tickets = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT tk.*, wp.post_title AS product_title
         FROM {$t_tk} tk
         LEFT JOIN {$t_p} p ON p.id = tk.product_id
         LEFT JOIN {$wpdb->posts} wp ON wp.ID = p.post_id
         WHERE tk.user_id = %d
         ORDER BY tk.updated_at DESC",
        $uid
    )
);
?>
<div class="v9-screen">
    <div class="v9-heading">
        <div><span class="v9-eyebrow">پشتیبانی خریدار</span><h1>تیکت‌های من</h1><p>مکاتبات مستقیم با فروشندگان و پشتیبانی تمساح.</p></div>
        <a class="v9-action" href="<?php echo esc_url( temsah_url( 'account/ticket-new' ) ); ?>"><?php echo temsah_ico( 'paper.svg' ); ?> ثبت تیکت جدید</a>
    </div>
    <?php if ( empty( $tickets ) ) : ?>
        <div class="tm-empty-state"><p>هنوز تیکتی ثبت نکرده‌اید.</p></div>
    <?php else : ?>
        <div class="v10-ticket-list">
            <?php foreach ( $tickets as $tk ) : ?>
            <article class="v10-ticket-card">
                <span class="v9-icon blue"><?php echo temsah_ico( 'chat.svg' ); ?></span>
                <div style="flex:1;">
                    <h3><a href="<?php echo esc_url( temsah_url( 'account/ticket-view', array( 'id' => $tk->id ) ) ); ?>"><?php echo esc_html( $tk->subject ); ?></a></h3>
                    <small><?php echo esc_html( $tk->ticket_code ); ?> · <?php echo esc_html( $tk->product_title ?: 'پشتیبانی عمومی' ); ?> · <?php echo esc_html( temsah_format_date( $tk->updated_at ) ); ?></small>
                </div>
                <span class="v9-badge <?php echo 'vendor_replied' === $tk->status ? 'green' : ''; ?>"><?php echo esc_html( $tk->status ); ?></span>
            </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
</div></main></div></div>
<?php get_footer(); ?>
