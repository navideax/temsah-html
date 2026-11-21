<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
get_header();
global $wpdb;
$t_v = Temsah_DB::table( 'vendors' );
$t_p = Temsah_DB::table( 'products' );
$vendors = $wpdb->get_results(
    "SELECT v.*, (SELECT COUNT(*) FROM {$t_p} p WHERE p.vendor_id = v.id AND p.status = 'published') AS prod_count
     FROM {$t_v} v
     WHERE v.status = 'approved'
     ORDER BY v.total_sales DESC, v.id ASC"
);
?>
<main>
<section class="wrap page-pad">
    <div class="breadcrumbs"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">خانه</a><span>/</span><span>فروشندگان تمساح</span></div>
    <div class="page-heading">
        <div><span class="eyebrow">تمساح مارکت</span><h1>فروشندگان تمساح</h1><p>با توسعه‌دهندگان و تیم‌های طراحی مارکت آشنا شو.</p></div>
    </div>
    <div class="page-two-column">
        <div>
            <?php if ( empty( $vendors ) ) : ?>
                <div class="tm-empty-state"><h3>هنوز فروشنده‌ای تایید نشده است</h3><p>فروشندگان پس از احراز هویت و تایید مدیر در این فهرست نمایش داده می‌شوند.</p></div>
            <?php else : ?>
                <div class="sellers-grid">
                    <?php foreach ( $vendors as $v ) : $ini = mb_substr( trim( $v->store_name ), 0, 1 ); ?>
                    <a class="surface seller-tile" href="<?php echo esc_url( temsah_url( 'seller/' . $v->store_slug ) ); ?>">
                        <span class="vendor-avatar"><?php echo esc_html( $ini ?: 'ف' ); ?></span>
                        <h3><?php echo esc_html( $v->store_name ); ?></h3>
                        <p><?php echo esc_html( $v->specialty ?: 'توسعه‌دهنده محصولات دیجیتال' ); ?></p>
                        <div>
                            <span><?php echo esc_html( temsah_format_number( $v->prod_count ) ); ?> محصول</span>
                            <span>مشاهده فروشگاه ←</span>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <aside class="page-side-note">
            <div class="surface summary-card">
                <h3>فروشنده شوید</h3>
                <p>محصولات دیجیتال خود را در تمساح عرضه کنید و درآمد کسب کنید.</p>
                <a class="btn-primary" href="<?php echo esc_url( temsah_url( 'become-seller' ) ); ?>" style="display:inline-flex;">شروع فروشندگی</a>
            </div>
        </aside>
    </div>
</section>
</main>
<?php get_footer(); ?>
