<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
get_header();
get_template_part( 'template-parts/portal-sidebar-vendor' );
global $wpdb;
$vendor = Temsah_Security::get_vendor_by_user( get_current_user_id() );
$vid    = $vendor ? (int) $vendor->id : 0;
$t_l    = Temsah_DB::table( 'licenses' );
$t_p    = Temsah_DB::table( 'products' );
$rows   = $vid ? $wpdb->get_results(
    $wpdb->prepare(
        "SELECT l.*, wp.post_title FROM {$t_l} l INNER JOIN {$t_p} p ON p.id = l.product_id INNER JOIN {$wpdb->posts} wp ON wp.ID = p.post_id WHERE p.vendor_id = %d ORDER BY l.id DESC LIMIT 100",
        $vid
    )
) : array();
?>
<div class="v11-screen">
    <div class="v11-toolbar"><div><span class="v11-eyebrow">پنل فروشندگان / لایسنس‌ها</span><h1>لایسنس‌های صادرشده</h1><p>مشاهده کلیدهای لایسنس صادرشده برای خریداران محصولات شما.</p></div></div>
    <section class="v11-surface">
        <?php if ( empty( $rows ) ) : ?>
            <div class="tm-empty-state"><p>هنوز لایسنسی برای محصولات شما صادر نشده است.</p></div>
        <?php else : ?>
            <div class="v9-table-wrap"><table class="v9-table"><thead><tr><th>محصول</th><th>کلید لایسنس</th><th>دامنه ثبت‌شده</th><th>وضعیت</th><th>تاریخ</th></tr></thead><tbody>
                <?php foreach ( $rows as $r ) : ?>
                <tr>
                    <td><b><?php echo esc_html( $r->post_title ); ?></b></td>
                    <td><code><?php echo esc_html( $r->license_key ); ?></code></td>
                    <td><?php echo esc_html( $r->domain ?: 'ثبت نشده' ); ?></td>
                    <td><span class="v9-badge <?php echo 'active' === $r->status ? 'green' : ''; ?>"><?php echo esc_html( $r->status ); ?></span></td>
                    <td><?php echo esc_html( temsah_format_date( $r->created_at ) ); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody></table></div>
        <?php endif; ?>
    </section>
</div>
</div></main></div></div>
<?php get_footer(); ?>
