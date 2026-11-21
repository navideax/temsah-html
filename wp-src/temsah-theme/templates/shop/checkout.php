<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
get_header();
$summary  = Temsah_Cart::get_summary();
$user     = wp_get_current_user();
$mobile   = get_user_meta( $user->ID, 'temsah_mobile', true );
$gateways = Temsah_Payments::get_gateways();
?>
<main>
<section class="t13-checkout wrap">
    <div class="t13-checkout-heading">
        <a href="<?php echo esc_url( temsah_url( 'cart' ) ); ?>"><?php echo temsah_ico( 'arrow---right.svg' ); ?> بازگشت به سبد خرید</a>
        <h1>تکمیل سفارش و پرداخت</h1>
        <span><?php echo esc_html( $user->user_email ); ?></span>
    </div>

    <?php if ( ! empty( $_GET['pay_err'] ) ) : ?>
        <div class="tm-alert tm-alert-err"><?php echo esc_html( sanitize_text_field( wp_unslash( $_GET['pay_err'] ) ) ); ?></div>
    <?php endif; ?>

    <?php if ( empty( $summary['items'] ) ) : ?>
        <div class="tm-empty-state"><h3>سبد خرید شما خالی است</h3><p>برای ثبت سفارش ابتدا حداقل یک محصول به سبد خرید اضافه کنید.</p><a class="btn-primary" href="<?php echo esc_url( temsah_url( 'shop' ) ); ?>">مشاهده فروشگاه</a></div>
    <?php else : ?>
        <div class="t13-checkout-grid">
            <div class="t13-checkout-main">
                <form class="demo-form t13-form" method="post" action="">
                    <?php wp_nonce_field( 'temsah_checkout_pay' ); ?>
                    <input type="hidden" name="temsah_action" value="checkout_pay">

                    <div class="t13-checkout-card" style="margin-bottom:20px;">
                        <div class="t13-checkout-title"><span class="t13-step">۱</span><div><h2>اطلاعات خریدار</h2><p>اطلاعات فاکتور و تحویل لایسنس را تایید کنید.</p></div></div>
                        <div class="input-grid">
                            <label>نام و نام خانوادگی<input name="buyer_name" required type="text" value="<?php echo esc_attr( $user->display_name ?: $user->user_login ); ?>"/></label>
                            <label>شماره موبایل<input name="buyer_mobile" placeholder="09123456789" type="tel" value="<?php echo esc_attr( $mobile ); ?>"/></label>
                        </div>
                        <label>ایمیل<input name="buyer_email" required type="email" value="<?php echo esc_attr( $user->user_email ); ?>"/></label>
                    </div>

                    <div class="t13-checkout-card">
                        <div class="t13-checkout-title"><span class="t13-step">۲</span><div><h2>انتخاب درگاه پرداخت</h2><p>مبلغ نهایی به صورت کامل در سرور محاسبه و به درگاه ارسال می‌شود.</p></div></div>
                        <div class="t13-methods">
                            <?php $first_enabled = true; foreach ( $gateways as $gw ) :
                                $enabled = $gw->is_enabled();
                                $checked = $enabled && $first_enabled;
                                if ( $checked ) { $first_enabled = false; }
                            ?>
                            <label class="t13-method<?php echo $checked ? ' chosen' : ''; ?>" style="<?php echo ! $enabled ? 'opacity:0.55;cursor:not-allowed;' : 'cursor:pointer;'; ?>">
                                <input type="radio" name="gateway_id" value="<?php echo esc_attr( $gw->get_id() ); ?>" <?php checked( $checked ); ?> <?php disabled( ! $enabled ); ?> style="width:auto;">
                                <?php echo temsah_ico( 'wallet.svg' ); ?>
                                <div>
                                    <b><?php echo esc_html( $gw->get_title() ); ?></b>
                                    <div style="font-size:12px;color:var(--muted);"><?php echo esc_html( $gw->get_description() ); ?></div>
                                </div>
                                <?php if ( $enabled ) : ?>
                                    <span class="v9-badge green"><?php echo $gw->is_sandbox() ? 'آزمایشی' : 'فعال'; ?></span>
                                <?php else : ?>
                                    <span class="v9-badge">غیرفعال</span>
                                <?php endif; ?>
                            </label>
                            <?php endforeach; ?>
                        </div>
                        <label class="check-row" style="margin-top:16px;"><input name="agree_terms" required type="checkbox" value="1"/> <a href="<?php echo esc_url( temsah_url( 'terms' ) ); ?>" target="_blank">قوانین و شرایط خرید محصولات دیجیتال</a> را مطالعه و قبول می‌کنم.</label>
                        <button class="t12-primary" type="submit" style="margin-top:14px;">انتقال به درگاه پرداخت <?php echo temsah_ico( 'arrow---left.svg' ); ?></button>
                    </div>
                </form>
            </div>

            <aside class="t13-checkout-card t13-checkout-sidebar">
                <h2>خلاصه سفارش</h2>
                <?php foreach ( $summary['items'] as $row ) : $p = $row['product']; ?>
                    <div class="t13-checkout-product">
                        <div><b><?php echo esc_html( $p->post_title ); ?></b><small><?php echo esc_html( $p->store_name ?: 'فروشنده تمساح' ); ?></small></div>
                        <strong><?php echo esc_html( temsah_format_toman( $row['line_total'] ) ); ?></strong>
                    </div>
                <?php endforeach; ?>
                <div class="t13-checkout-sums">
                    <div><span>مجموع قیمت اصلی</span><b><?php echo esc_html( temsah_format_toman( $summary['subtotal'] ) ); ?></b></div>
                    <?php if ( $summary['product_discount'] > 0 ) : ?><div><span>تخفیف محصولات</span><b>−<?php echo esc_html( temsah_format_toman( $summary['product_discount'] ) ); ?></b></div><?php endif; ?>
                    <?php if ( $summary['services_total'] > 0 ) : ?><div><span>خدمات تکمیلی</span><b>+<?php echo esc_html( temsah_format_toman( $summary['services_total'] ) ); ?></b></div><?php endif; ?>
                    <?php if ( $summary['coupon_discount'] > 0 ) : ?><div><span>کد تخفیف (<?php echo esc_html( $summary['coupon_code'] ); ?>)</span><b>−<?php echo esc_html( temsah_format_toman( $summary['coupon_discount'] ) ); ?></b></div><?php endif; ?>
                    <div class="t13-checkout-total"><span>مبلغ قابل پرداخت</span><strong><?php echo esc_html( temsah_format_toman( $summary['total'] ) ); ?></strong></div>
                </div>
                <p>محاسبه مالیات، تخفیف، کمیسیون فروشنده و تایید تراکنش به طور کامل در سرور انجام می‌شود.</p>
            </aside>
        </div>
    <?php endif; ?>
</section>
</main>
<?php get_footer(); ?>
