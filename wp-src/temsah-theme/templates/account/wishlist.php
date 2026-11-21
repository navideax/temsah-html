<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
get_header();
get_template_part( 'template-parts/portal-sidebar-buyer' );

global $wpdb;
$uid      = get_current_user_id();
$wish_ids = Temsah_Buyer::get_wishlist_product_ids( $uid );
$products = array();
foreach ( $wish_ids as $pid ) {
    $p = Temsah_CPT::get_product_by_id( $pid );
    if ( $p && 'published' === $p->status ) {
        $products[] = $p;
    }
}
?>
<div class="v9-screen">
    <div class="v9-heading"><div><span class="v9-eyebrow">سامانه خریداران / علاقه‌مندی‌ها</span><h1>لیست علاقه‌مندی‌ها</h1><p>محصولاتی که برای خرید یا بررسی بعدی ذخیره کرده‌اید.</p></div></div>
    <?php if ( empty( $products ) ) : ?>
        <div class="tm-empty-state"><h3>لیست علاقه‌مندی‌های شما خالی است</h3><p>با کلیک بر روی آیکون قلب روی کارت هر محصول، آن را به این فهرست اضافه کنید.</p><a class="btn-primary" href="<?php echo esc_url( temsah_url( 'shop' ) ); ?>">مشاهده فروشگاه</a></div>
    <?php else : ?>
        <div class="cards-grid catalog-grid">
            <?php foreach ( $products as $p ) { get_template_part( 'template-parts/product-card', null, array( 'product' => $p, 'wish_ids' => $wish_ids ) ); } ?>
        </div>
    <?php endif; ?>
</div>
</div></main></div></div>
<?php get_footer(); ?>
