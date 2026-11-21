<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
get_header();
global $wpdb;
$slug   = sanitize_title( (string) get_query_var( 'temsah_slug', '' ) );
$t_v    = Temsah_DB::table( 'vendors' );
$vendor = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$t_v} WHERE store_slug = %s AND status = 'approved' LIMIT 1", $slug ) );

if ( ! $vendor ) {
    require get_template_directory() . '/404.php';
    return;
}

$products = Temsah_CPT::query_products( array( 'vendor_id' => (int) $vendor->id, 'limit' => 50 ) );
$wish_ids = is_user_logged_in() ? Temsah_Buyer::get_wishlist_product_ids( get_current_user_id() ) : array();
$badges   = json_decode( (string) $vendor->badges_json, true ) ?: array();
$ini      = mb_substr( trim( $vendor->store_name ), 0, 1 );
?>
<main>
<section class="wrap page-pad">
    <div class="breadcrumbs"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">خانه</a><span>/</span><a href="<?php echo esc_url( temsah_url( 'sellers' ) ); ?>">فروشندگان</a><span>/</span><span>فروشگاه <?php echo esc_html( $vendor->store_name ); ?></span></div>
    <div class="page-heading">
        <div>
            <span class="eyebrow">تمساح مارکت</span>
            <h1>فروشگاه <?php echo esc_html( $vendor->store_name ); ?></h1>
            <p><?php echo esc_html( $vendor->specialty ?: 'توسعه‌دهنده و ارائه‌دهنده محصولات دیجیتال' ); ?></p>
        </div>
    </div>
    <div class="page-two-column">
        <div>
            <div class="surface vendor-head">
                <span class="vendor-avatar"><?php echo esc_html( $ini ?: 'ف' ); ?></span>
                <div>
                    <h2><?php echo esc_html( $vendor->store_name ); ?></h2>
                    <p><?php echo esc_html( $vendor->bio ?: $vendor->specialty ); ?></p>
                    <small>فروش موفق ثبت‌شده: <?php echo esc_html( temsah_format_number( $vendor->total_sales ) ); ?> · محصولات فعال: <?php echo esc_html( temsah_format_number( count( $products ) ) ); ?></small>
                </div>
                <?php if ( ! empty( $badges ) ) : ?>
                <div class="tm-seller-hero-medals">
                    <span class="tm-seller-medals-heading">نشان‌های افتخار توسعه‌دهنده</span>
                    <?php echo temsah_render_medals( $badges, 'seller', $vendor->store_name, false ); ?>
                </div>
                <?php endif; ?>
            </div>

            <?php if ( empty( $products ) ) : ?>
                <div class="tm-empty-state"><h3>محصولی ثبت نشده است</h3><p>این فروشنده هنوز محصول فعالی منتشر نکرده است.</p></div>
            <?php else : ?>
                <div class="cards-grid catalog-grid" style="margin-top:20px;">
                    <?php foreach ( $products as $p ) { get_template_part( 'template-parts/product-card', null, array( 'product' => $p, 'wish_ids' => $wish_ids ) ); } ?>
                </div>
            <?php endif; ?>
        </div>
        <aside class="page-side-note">
            <div class="surface summary-card">
                <h3>اطلاعات پشتیبانی فروشگاه</h3>
                <p>ساعات پاسخگویی: <?php echo esc_html( $vendor->support_hours ?: 'شنبه تا چهارشنبه ۹ تا ۱۸' ); ?></p>
                <?php if ( ! empty( $vendor->portfolio_url ) ) : ?>
                    <p><a class="btn-outline" href="<?php echo esc_url( $vendor->portfolio_url ); ?>" target="_blank" rel="noopener noreferrer">مشاهده وب‌سایت نمونه‌کار</a></p>
                <?php endif; ?>
            </div>
        </aside>
    </div>
</section>
</main>
<?php get_footer(); ?>
