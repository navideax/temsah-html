<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
get_header();
get_template_part( 'template-parts/portal-sidebar-vendor' );
global $wpdb;
$uid    = get_current_user_id();
$notifs = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . Temsah_DB::table( 'notifications' ) . " WHERE (user_id = %d OR (user_id IS NULL AND audience IN ('all','vendor'))) ORDER BY id DESC LIMIT 30", $uid ) );
?>
<div class="v11-screen">
    <div class="v11-toolbar"><div><span class="v11-eyebrow">اعلانات فروشنده</span><h1>اطلاعیه‌ها و اعلان‌های فروش</h1><p>اعلان‌های فروش، تسویه حساب و پیام‌های مدیریت تمساح.</p></div></div>
    <section class="v11-surface">
        <?php if ( empty( $notifs ) ) : ?>
            <div class="tm-empty-state"><p>اعلان جدیدی ثبت نشده است.</p></div>
        <?php else : foreach ( $notifs as $n ) : ?>
            <div style="padding:14px 0;border-bottom:1px solid var(--line);">
                <div style="display:flex;justify-content:space-between;"><b><?php echo esc_html( $n->title ); ?></b><small><?php echo esc_html( temsah_format_date( $n->created_at ) ); ?></small></div>
                <p style="margin:6px 0 0;color:var(--muted);"><?php echo esc_html( $n->body ); ?></p>
            </div>
        <?php endforeach; endif; ?>
    </section>
</div>
</div></main></div></div>
<?php get_footer(); ?>
