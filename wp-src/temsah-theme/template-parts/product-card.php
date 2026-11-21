<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/** @var object $p */
$p          = $args['product'] ?? null;
if ( ! $p ) {
    return;
}
$wish_ids   = $args['wish_ids'] ?? array();
$in_wish    = in_array( (int) $p->id, $wish_ids, true );
$url        = get_permalink( (int) $p->post_id );
$seller_url = ! empty( $p->store_slug ) ? temsah_url( 'seller/' . $p->store_slug ) : temsah_url( 'sellers' );
$cat        = Temsah_CPT::get_product_category_name( (int) $p->post_id );
$eff        = Temsah_CPT::effective_price( $p );
$orig       = (int) $p->price;
$disc_pct   = Temsah_CPT::discount_percent( $p );
?>
<article class="product-card" data-product-id="<?php echo (int) $p->id; ?>">
    <a aria-label="<?php echo esc_attr( $p->post_title ); ?>" class="card-image" href="<?php echo esc_url( $url ); ?>">
        <?php echo temsah_cover_html( $p, true ); ?>
        <?php if ( $disc_pct > 0 ) : ?>
            <span class="card-discount"><?php echo esc_html( temsah_fa_digits( $disc_pct ) ); ?>٪</span>
        <?php endif; ?>
    </a>
    <div class="card-content">
        <div class="card-type">
            <?php echo esc_html( $cat ); ?>
            <button aria-label="افزودن به علاقه‌مندی" class="fav-btn<?php echo $in_wish ? ' active' : ''; ?>" data-fav="<?php echo (int) $p->id; ?>" type="button"><?php echo temsah_ico( 'heart.svg' ); ?></button>
        </div>
        <a class="card-title" href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $p->post_title ); ?></a>
        <a class="card-seller" href="<?php echo esc_url( $seller_url ); ?>">توسط <?php echo esc_html( $p->store_name ?: 'فروشنده تمساح' ); ?></a>
        <div class="card-meta">
            <span><?php echo temsah_ico( 'star.svg' ); ?> <?php echo esc_html( temsah_fa_digits( number_format( (float) $p->rating_avg, 1 ) ) ); ?></span>
            <span><?php echo temsah_ico( 'buy.svg' ); ?> <?php echo esc_html( temsah_format_number( $p->sales_count ) ); ?> فروش</span>
        </div>
        <div class="card-price">
            <div>
                <?php if ( $disc_pct > 0 ) : ?>
                    <del><?php echo esc_html( temsah_format_toman( $orig ) ); ?></del>
                <?php endif; ?>
                <span>
                    <strong><?php echo esc_html( 0 === $eff ? 'رایگان' : temsah_format_number( $eff ) ); ?></strong>
                    <?php if ( $eff > 0 ) : ?><small>تومان</small><?php endif; ?>
                </span>
            </div>
            <button aria-label="افزودن به سبد" data-add="<?php echo (int) $p->id; ?>" type="button"><?php echo temsah_ico( 'bag.svg' ); ?></button>
        </div>
    </div>
</article>
