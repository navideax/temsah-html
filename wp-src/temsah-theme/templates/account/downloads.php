<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
get_header();
get_template_part( 'template-parts/portal-sidebar-buyer' );

global $wpdb;
$uid    = get_current_user_id();
$t_o    = Temsah_DB::table( 'orders' );
$t_oi   = Temsah_DB::table( 'order_items' );
$t_p    = Temsah_DB::table( 'products' );
$t_pf   = Temsah_DB::table( 'product_files' );
$t_v    = Temsah_DB::table( 'vendors' );

$items = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT DISTINCT p.id AS product_id, p.post_id, p.current_version, wp.post_title, v.store_name, o.paid_at,
                pf.id AS file_id, pf.file_label, pf.file_name, pf.file_size
         FROM {$t_oi} oi
         INNER JOIN {$t_o} o ON o.id = oi.order_id
         INNER JOIN {$t_p} p ON p.id = oi.product_id
         INNER JOIN {$wpdb->posts} wp ON wp.ID = p.post_id
         LEFT JOIN {$t_v} v ON v.id = p.vendor_id
         LEFT JOIN {$t_pf} pf ON pf.product_id = p.id AND pf.is_active = 1
         WHERE o.user_id = %d AND o.status = 'paid'
         ORDER BY o.id DESC",
        $uid
    )
);
?>
<div class="v9-screen">
    <div class="v9-heading">
        <div><span class="v9-eyebrow">سامانه خریداران / TEMSAH</span><h1>کتابخانه دانلودهای من</h1><p>فایل‌ها و نسخه‌های بروزرسانی محصولاتی که خریداری کرده‌اید.</p></div>
    </div>

    <?php if ( empty( $items ) ) : ?>
        <div class="tm-empty-state"><h3>هنوز محصولی خریداری نکرده‌اید</h3><p>پس از تکمیل خرید، فایل‌های قابل دانلود شما با لینک اختصاصی امن در این بخش قرار می‌گیرند.</p><a class="btn-primary" href="<?php echo esc_url( temsah_url( 'shop' ) ); ?>">مشاهده فروشگاه</a></div>
    <?php else : ?>
        <div class="v9-cards">
            <?php foreach ( $items as $it ) : $cat = Temsah_CPT::get_product_category_name( (int) $it->post_id ); ?>
            <article class="v9-product-record">
                <div class="v9-product-record-head">
                    <span class="v9-icon blue"><?php echo temsah_ico( 'document.svg' ); ?></span>
                    <div><h3><?php echo esc_html( $it->post_title ); ?></h3><small><?php echo esc_html( $cat ); ?> · <?php echo esc_html( $it->store_name ?: 'فروشنده تمساح' ); ?></small></div>
                </div>
                <div class="v9-product-meta">
                    <span>نسخه کنونی <b><?php echo esc_html( $it->current_version ?: '1.0.0' ); ?></b></span>
                    <span>تاریخ خرید <b><?php echo esc_html( temsah_format_date( $it->paid_at ) ); ?></b></span>
                    <span>فایل <b><?php echo esc_html( $it->file_name ?: 'در انتظار آپلود فروشنده' ); ?></b></span>
                </div>
                <div class="v9-product-footer">
                    <a href="<?php echo esc_url( get_permalink( (int) $it->post_id ) ); ?>">صفحه محصول</a>
                    <?php if ( ! empty( $it->file_id ) ) : ?>
                        <a class="v9-action" href="<?php echo esc_url( Temsah_Security::download_url( (int) $it->product_id, (int) $it->file_id ) ); ?>"><?php echo temsah_ico( 'download.svg' ); ?> دریافت نسخه <?php echo esc_html( $it->current_version ?: '1.0.0' ); ?></a>
                    <?php else : ?>
                        <span class="v9-badge">فایل توسط فروشنده بارگذاری نشده</span>
                    <?php endif; ?>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
</div></main></div></div>
<?php get_footer(); ?>
