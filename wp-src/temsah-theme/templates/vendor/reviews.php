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
        "SELECT f.*, wp.post_title, u.display_name FROM {$t_fb} f INNER JOIN {$t_p} p ON p.id = f.product_id INNER JOIN {$wpdb->posts} wp ON wp.ID = p.post_id LEFT JOIN {$wpdb->users} u ON u.ID = f.user_id WHERE p.vendor_id = %d AND f.kind = 'review' ORDER BY f.id DESC",
        $vid
    )
) : array();
?>
<div class="v11-screen">
    <div class="v11-toolbar"><div><span class="v11-eyebrow">بازخورد خریداران</span><h1>دیدگاه‌های محصولات</h1><p>نظرات و امتیازات ثبت‌شده توسط خریداران تأییدشده.</p></div></div>
    <section class="v11-surface">
        <?php if ( empty( $rows ) ) : ?>
            <div class="tm-empty-state"><p>هنوز دیدگاهی برای محصولات شما ثبت نشده است.</p></div>
        <?php else : ?>
            <div class="v9-table-wrap"><table class="v9-table"><thead><tr><th>محصول</th><th>خریدار</th><th>امتیاز</th><th>متن دیدگاه</th><th>تاریخ</th></tr></thead><tbody>
                <?php foreach ( $rows as $r ) : ?>
                <tr>
                    <td><b><?php echo esc_html( $r->post_title ); ?></b></td>
                    <td><?php echo esc_html( $r->display_name ?: 'خریدار' ); ?></td>
                    <td><?php echo esc_html( temsah_fa_digits( $r->rating ) ); ?> / ۵</td>
                    <td><?php echo esc_html( $r->content ); ?></td>
                    <td><?php echo esc_html( temsah_format_date( $r->created_at ) ); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody></table></div>
        <?php endif; ?>
    </section>
</div>
</div></main></div></div>
<?php get_footer(); ?>
