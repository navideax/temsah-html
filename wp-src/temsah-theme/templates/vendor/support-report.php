<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
get_header();
get_template_part( 'template-parts/portal-sidebar-vendor' );
global $wpdb;
$vendor = Temsah_Security::get_vendor_by_user( get_current_user_id() );
$vid    = $vendor ? (int) $vendor->id : 0;
$t_tk   = Temsah_DB::table( 'tickets' );
$t_fb   = Temsah_DB::table( 'product_feedback' );
$t_p    = Temsah_DB::table( 'products' );

$total_tk   = $vid ? (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$t_tk} WHERE vendor_id = %d", $vid ) ) : 0;
$replied_tk = $vid ? (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$t_tk} WHERE vendor_id = %d AND status IN ('vendor_replied','closed')", $vid ) ) : 0;
$total_q    = $vid ? (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$t_fb} f INNER JOIN {$t_p} p ON p.id = f.product_id WHERE p.vendor_id = %d AND f.kind = 'question'", $vid ) ) : 0;
$avg_rating = $vid ? (float) $wpdb->get_var( $wpdb->prepare( "SELECT AVG(rating_avg) FROM {$t_p} WHERE vendor_id = %d AND rating_count > 0", $vid ) ) : 0.0;
?>
<div class="v11-screen">
    <div class="v11-toolbar"><div><span class="v11-eyebrow">گزارش پشتیبانی</span><h1>گزارش عملکرد پشتیبانی</h1><p>شاخص‌های پاسخگویی به تیکت‌ها، پرسش‌ها و رضایت خریداران.</p></div></div>
    <div class="v11-kpis report">
        <div class="v11-kpi"><span class="v9-icon blue"><?php echo temsah_ico( 'ticket.svg' ); ?></span><small>کل تیکت‌ها</small><strong><?php echo esc_html( temsah_format_number( $total_tk ) ); ?></strong></div>
        <div class="v11-kpi"><span class="v9-icon green"><?php echo temsah_ico( 'tick-square.svg' ); ?></span><small>پاسخ‌داده‌شده / بسته</small><strong><?php echo esc_html( temsah_format_number( $replied_tk ) ); ?></strong></div>
        <div class="v11-kpi"><span class="v9-icon amber"><?php echo temsah_ico( 'chat.svg' ); ?></span><small>پرسش‌های محصولات</small><strong><?php echo esc_html( temsah_format_number( $total_q ) ); ?></strong></div>
        <div class="v11-kpi"><span class="v9-icon pink"><?php echo temsah_ico( 'star.svg' ); ?></span><small>میانگین امتیاز</small><strong><?php echo esc_html( temsah_fa_digits( number_format( $avg_rating, 1 ) ) ); ?></strong></div>
    </div>
</div>
</div></main></div></div>
<?php get_footer(); ?>
