<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
$tid    = absint( $_GET['id'] ?? 0 );
$ticket = Temsah_Security::user_can_access_ticket( $tid, 'vendor' );
if ( ! $ticket ) {
    status_header( 403 );
    wp_die( 'دسترسی به این تیکت مجاز نیست.', 'خطای دسترسی (۴۰۳)', array( 'response' => 403 ) );
}
get_header();
get_template_part( 'template-parts/portal-sidebar-vendor' );
global $wpdb;
$vendor  = Temsah_Security::get_vendor_by_user( get_current_user_id() );
$msgs    = $wpdb->get_results( $wpdb->prepare( 'SELECT m.*, u.display_name FROM ' . Temsah_DB::table( 'ticket_messages' ) . " m LEFT JOIN {$wpdb->users} u ON u.ID = m.sender_id WHERE m.ticket_id = %d ORDER BY m.id ASC", $ticket->id ) );
$tpls    = $vendor ? $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . Temsah_DB::table( 'vendor_reply_templates' ) . ' WHERE vendor_id = %d ORDER BY id DESC', $vendor->id ) ) : array();
?>
<div class="v11-screen">
    <div class="v11-toolbar">
        <div><span class="v11-eyebrow">تیکت <?php echo esc_html( $ticket->ticket_code ); ?></span><h1><?php echo esc_html( $ticket->subject ); ?></h1></div>
        <a class="v9-action" href="<?php echo esc_url( temsah_url( 'vendor/tickets' ) ); ?>">بازگشت به تیکت‌ها</a>
    </div>
    <?php if ( ! empty( $_GET['ok'] ) ) : ?><div class="tm-alert tm-alert-ok"><?php echo esc_html( sanitize_text_field( wp_unslash( $_GET['ok'] ) ) ); ?></div><?php endif; ?>
    <?php if ( ! empty( $_GET['err'] ) ) : ?><div class="tm-alert tm-alert-err"><?php echo esc_html( sanitize_text_field( wp_unslash( $_GET['err'] ) ) ); ?></div><?php endif; ?>

    <section class="v11-surface">
        <?php foreach ( $msgs as $m ) : ?>
            <div style="padding:14px;border-radius:12px;margin-bottom:12px;background:<?php echo 'vendor' === $m->sender_role ? '#ecfdf5' : '#f8fafc'; ?>;border:1px solid var(--line);">
                <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
                    <b><?php echo esc_html( $m->display_name ?: $m->sender_role ); ?></b>
                    <small><?php echo esc_html( $m->created_at ); ?></small>
                </div>
                <p style="margin:0;"><?php echo nl2br( esc_html( $m->message ) ); ?></p>
            </div>
        <?php endforeach; ?>

        <?php if ( ! empty( $tpls ) ) : ?>
            <div style="margin:14px 0;">
                <small style="display:block;margin-bottom:6px;color:var(--muted);">درج سریع از پاسخ‌های آماده:</small>
                <div style="display:flex;gap:8px;flex-wrap:wrap;">
                    <?php foreach ( $tpls as $tp ) : ?>
                        <button class="btn-outline" data-insert-reply="<?php echo esc_attr( $tp->body ); ?>" style="padding:5px 10px;font-size:12px;" type="button"><?php echo esc_html( $tp->title ); ?></button>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <form class="demo-form" method="post" action="" style="margin-top:14px;">
            <?php wp_nonce_field( 'temsah_vendor_reply_ticket' ); ?>
            <input type="hidden" name="temsah_action" value="vendor_reply_ticket">
            <input type="hidden" name="ticket_id" value="<?php echo (int) $ticket->id; ?>">
            <label>پاسخ شما به خریدار<textarea id="vendorTicketReplyBox" name="message" required rows="4" placeholder="پاسخ فنی خود را بنویسید..."></textarea></label>
            <button class="btn-primary" type="submit">ارسال پاسخ</button>
        </form>
    </section>
</div>
</div></main></div></div>
<?php get_footer(); ?>
