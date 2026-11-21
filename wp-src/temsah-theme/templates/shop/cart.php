<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
get_header();
$summary  = Temsah_Cart::get_summary();
$services = Temsah_Cart::available_services();
?>
<main>
<section class="t12-cart-page wrap">
    <div class="t12-cart-shell">
        <div class="t12-cart-head">
            <h1><?php echo temsah_ico( 'bag.svg' ); ?> سبد خرید</h1>
            <span><?php echo temsah_ico( 'shield-done.svg' ); ?> دسترسی فوری به فایل‌ها پس از تأیید پرداخت</span>
        </div>

        <?php if ( empty( $summary['items'] ) ) : ?>
            <div class="t12-empty">
                <?php echo temsah_ico( 'bag.svg' ); ?>
                <h2>سبد خرید شما خالی است</h2>
                <p>می‌توانید محصولات دیجیتال مورد نیازتان را از فروشگاه انتخاب و به سبد خرید اضافه کنید.</p>
                <a class="t12-primary" href="<?php echo esc_url( temsah_url( 'shop' ) ); ?>">مشاهده محصولات فروشگاه</a>
            </div>
        <?php else : ?>
            <div class="t12-cart-body">
                <section class="t12-cart-list">
                    <?php foreach ( $summary['items'] as $row ) :
                        $p   = $row['product'];
                        $cat = Temsah_CPT::get_product_category_name( (int) $p->post_id );
                        $url = get_permalink( (int) $p->post_id );
                    ?>
                    <article class="t12-cart-item" data-item="<?php echo (int) $p->id; ?>">
                        <div class="t12-item-top">
                            <button aria-label="حذف <?php echo esc_attr( $p->post_title ); ?>" class="t12-remove" data-remove-cart="<?php echo (int) $p->id; ?>" type="button">×</button>
                            <a href="<?php echo esc_url( $url ); ?>"><span class="t12-product-icon t12-store"><?php echo temsah_ico( 'document.svg' ); ?></span></a>
                            <div class="t12-item-info">
                                <a class="t12-product-title" href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $p->post_title ); ?></a>
                                <span class="t12-product-meta">فروشنده: <?php echo esc_html( $p->store_name ?: 'فروشنده تمساح' ); ?> · <?php echo esc_html( $cat ); ?></span>
                                <div class="t12-price-line">
                                    <strong><?php echo esc_html( 0 === $row['effective_price'] ? 'رایگان' : temsah_format_number( $row['effective_price'] ) ); ?> <?php if ( $row['effective_price'] > 0 ) : ?><small>تومان</small><?php endif; ?></strong>
                                    <?php if ( $row['discount'] > 0 ) : ?><del><?php echo esc_html( temsah_format_number( $row['original_price'] ) ); ?></del><?php endif; ?>
                                    <span class="t12-quantity">تعداد: ۱</span>
                                    <button class="t12-more" data-cartmore="<?php echo (int) $p->id; ?>" type="button">خدمات تکمیلی <?php echo temsah_ico( 'arrow---down-2.svg' ); ?></button>
                                </div>
                            </div>
                        </div>
                        <div class="t12-extras" <?php echo empty( $row['selected_services'] ) ? 'hidden' : ''; ?>>
                            <div class="t12-extras-head"><b>سرویس‌های تکمیلی این محصول</b><small>انتخاب خدمات اختیاری است</small></div>
                            <div class="t12-services">
                                <?php foreach ( $services as $sid => $svc ) : $checked = in_array( $sid, $row['selected_services'], true ); ?>
                                <label class="t12-service">
                                    <input data-cart-service="1" data-product-id="<?php echo (int) $p->id; ?>" type="checkbox" value="<?php echo esc_attr( $sid ); ?>" <?php checked( $checked ); ?>/>
                                    <span><?php echo esc_html( $svc['label'] ); ?></span>
                                    <b>+<?php echo esc_html( temsah_format_number( $svc['price'] ) ); ?> <small>تومان</small></b>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </article>
                    <?php endforeach; ?>
                </section>

                <aside class="t12-cart-aside">
                    <div class="t12-price-box">
                        <div><span>قیمت محصولات (<?php echo esc_html( temsah_fa_digits( $summary['count'] ) ); ?>)</span><b><?php echo esc_html( temsah_format_toman( $summary['subtotal'] ) ); ?></b></div>
                        <?php if ( $summary['product_discount'] > 0 ) : ?>
                            <div><span>تخفیف محصولات</span><b>−<?php echo esc_html( temsah_format_toman( $summary['product_discount'] ) ); ?></b></div>
                        <?php endif; ?>
                        <?php if ( $summary['services_total'] > 0 ) : ?>
                            <div><span>سرویس‌های تکمیلی</span><b>+<?php echo esc_html( temsah_format_toman( $summary['services_total'] ) ); ?></b></div>
                        <?php endif; ?>
                        <?php if ( $summary['coupon_discount'] > 0 ) : ?>
                            <div><span>کد تخفیف (<?php echo esc_html( $summary['coupon_code'] ); ?>)</span><b>−<?php echo esc_html( temsah_format_toman( $summary['coupon_discount'] ) ); ?></b></div>
                        <?php endif; ?>
                        <div class="t12-total"><span>مبلغ قابل پرداخت</span><strong><?php echo esc_html( 0 === $summary['total'] ? 'رایگان' : temsah_format_number( $summary['total'] ) ); ?> <?php if ( $summary['total'] > 0 ) : ?><small>تومان</small><?php endif; ?></strong></div>
                    </div>

                    <a class="t12-primary" href="<?php echo esc_url( temsah_url( 'checkout' ) ); ?>"><?php echo temsah_ico( 'arrow---left.svg' ); ?> ادامه و ثبت سفارش</a>
                </aside>
            </div>

            <div class="t12-coupon">
                <div>
                    <b>کد تخفیف دارید؟</b>
                    <?php if ( ! empty( $summary['coupon_code'] ) ) : ?>
                        <span class="tm-coupon-applied">✓ کد تخفیف <?php echo esc_html( $summary['coupon_code'] ); ?> اعمال شد</span>
                    <?php else : ?>
                        <span>در صورت داشتن کد تخفیف، آن را وارد و ثبت کنید.</span>
                    <?php endif; ?>
                </div>
                <form id="tmCouponForm">
                    <input autocomplete="off" id="tmCouponInput" placeholder="کد تخفیف" value="<?php echo esc_attr( $summary['coupon_code'] ); ?>"/>
                    <button type="submit">ثبت کد</button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</section>
</main>
<?php get_footer(); ?>
