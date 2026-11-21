<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
$tid    = absint( $_GET['id'] ?? 0 );
$ticket = Temsah_Security::user_can_access_ticket( $tid, 'buyer' );
if ( ! $ticket ) {
    status_header( 403 );
    wp_die( 'دسترسی به این تیکت مجاز نیست.', 'خطای دسترسی (۴۰۳)', array( 'response' => 403 ) );
}
get_header();
get_template_part( 'template-parts/portal-sidebar-buyer' );

global $wpdb;
$msgs = $wpdb->get_results( $wpdb->prepare( 'SELECT m.*, u.display_name FROM ' . Temsah_DB::table( 'ticket_messages' ) . " m LEFT JOIN {$wpdb->users} u ON u.ID = m.sender_id WHERE m.ticket_id = %d ORDER BY m.id ASC", $ticket->id ) );
?>
<div class="v9-screen">
    <div class="v9-heading">
        <div><span class="v9-eyebrow">تیکت <?php echo esc_html( $ticket->ticket_code ); ?></span><h1><?php echo esc_html( $ticket->subject ); ?></h1></div>
        <a class="v9-action" href="<?php echo esc_url( temsah_url( 'account/tickets' ) ); ?>">بازگشت به تیکت‌ها</a>
    </div>
    <?php if ( ! empty( $_GET['ok'] ) ) : ?><div class="tm-alert tm-alert-ok"><?php echo esc_html( sanitize_text_field( wp_unslash( $_GET['ok'] ) ) ); ?></div><?php endif; ?>
    <?php if ( ! empty( $_GET['err'] ) ) : ?><div class="tm-alert tm-alert-err"><?php echo esc_html( sanitize_text_field( wp_unslash( $_GET['err'] ) ) ); ?></div><?php endif; ?>

    <section class="v9-panel">
        <?php foreach ( $msgs as $m ) : ?>
            <div style="padding:14px;border-radius:12px;margin-bottom:12px;background:<?php echo 'buyer' === $m->sender_role ? '#f8fafc' : '#ecfdf5'; ?>;border:1px solid var(--line);">
                <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
                    <b><?php echo esc_html( $m->display_name ?: ( 'vendor' === $m->sender_role ? 'فروشنده' : 'کاربر' ) ); ?></b>
                    <small><?php echo esc_html( $m->created_at ); ?></small>
                </div>
                <p style="margin:0;"><?php echo nl2br( esc_html( $m->message ) ); ?></p>
            </div>
        <?php endforeach; ?>

        <form class="demo-form" method="post" action="" style="margin-top:20px;">
            <?php wp_nonce_field( 'temsah_buyer_reply_ticket' ); ?>
            <input type="hidden" name="temsah_action" value="buyer_reply_ticket">
            <input type="hidden" name="ticket_id" value="<?php echo (int) $ticket->id; ?>">
            <label>ارسال پاسخ جدید<textarea name="message" required rows="4" placeholder="پاسخ خود را بنویسید..."></textarea></label>
            <button class="btn-primary" type="submit">ارسال پاسخ</button>
        </form>
    </section>
</div>
</div></main></div></div>
<?php get_footer(); ?>
