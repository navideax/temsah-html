<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
get_header();
get_template_part( 'template-parts/portal-sidebar-vendor' );
global $wpdb;
$vendor  = Temsah_Security::get_vendor_by_user( get_current_user_id() );
$vid     = $vendor ? (int) $vendor->id : 0;
$t_tk    = Temsah_DB::table( 'tickets' );
$t_p     = Temsah_DB::table( 'products' );
$tickets = $vid ? $wpdb->get_results(
    $wpdb->prepare(
        "SELECT tk.*, wp.post_title AS product_title, u.display_name AS buyer_name
         FROM {$t_tk} tk
         LEFT JOIN {$t_p} p ON p.id = tk.product_id
         LEFT JOIN {$wpdb->posts} wp ON wp.ID = p.post_id
         LEFT JOIN {$wpdb->users} u ON u.ID = tk.user_id
         WHERE tk.vendor_id = %d
         ORDER BY tk.updated_at DESC",
        $vid
    )
) : array();
?>
<div class="v11-screen">
    <div class="v11-toolbar"><div><span class="v11-eyebrow">پشتیبانی خریداران</span><h1>تیکت‌های محصولات</h1><p>پاسخگویی به تیکت‌های خریداران محصولات فروشگاه شما.</p></div></div>
    <section class="v11-surface">
        <?php if ( empty( $tickets ) ) : ?>
            <div class="tm-empty-state"><p>تیکتی برای محصولات فروشگاه شما ثبت نشده است.</p></div>
        <?php else : ?>
            <div class="v10-ticket-list">
                <?php foreach ( $tickets as $tk ) : ?>
                <article class="v10-ticket-card">
                    <span class="v9-icon blue"><?php echo temsah_ico( 'chat.svg' ); ?></span>
                    <div style="flex:1;">
                        <h3><a href="<?php echo esc_url( temsah_url( 'vendor/ticket-view', array( 'id' => $tk->id ) ) ); ?>"><?php echo esc_html( $tk->subject ); ?></a></h3>
                        <small><?php echo esc_html( $tk->ticket_code ); ?> · خریدار: <?php echo esc_html( $tk->buyer_name ?: 'کاربر' ); ?> · <?php echo esc_html( $tk->product_title ?: '' ); ?></small>
                    </div>
                    <span class="v9-badge <?php echo 'vendor_replied' === $tk->status ? 'green' : ''; ?>"><?php echo esc_html( $tk->status ); ?></span>
                </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</div>
</div></main></div></div>
<?php get_footer(); ?>
