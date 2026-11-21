<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
get_header();

$stage     = sanitize_key( wp_unslash( $_GET['stage'] ?? '' ) );
$gateway   = sanitize_key( wp_unslash( $_GET['gateway'] ?? '' ) );
$authority = sanitize_text_field( wp_unslash( $_GET['authority'] ?? $_GET['Authority'] ?? '' ) );
$sig       = sanitize_text_field( wp_unslash( $_GET['sig'] ?? '' ) );

$payment   = $authority ? Temsah_Payments::get_payment_by_authority( $authority ) : null;
?>
<main>
<section class="wrap page-pad" style="max-width:680px;margin:40px auto;">
<?php
if ( 'prompt' === $stage && 'sandbox' === $gateway && $payment ) :
    $expected = Temsah_Gateway_Sandbox::sign( $payment->authority, (int) $payment->order_id, (int) $payment->amount );
    if ( ! hash_equals( $expected, $sig ) || (int) $payment->user_id !== get_current_user_id() ) :
?>
    <div class="surface article-detail">
        <h1>خطای امضای امنیتی درگاه</h1>
        <p>امضای درخواست پرداخت آزمایشی نامعتبر است.</p>
        <a class="btn-outline" href="<?php echo esc_url( temsah_url( 'cart' ) ); ?>">بازگشت به سبد خرید</a>
    </div>
<?php else :
    $ok_url   = temsah_url( 'payment-callback', array( 'gateway' => 'sandbox', 'authority' => $authority, 'sig' => $sig, 'status' => 'ok' ) );
    $fail_url = temsah_url( 'payment-callback', array( 'gateway' => 'sandbox', 'authority' => $authority, 'sig' => $sig, 'status' => 'cancel' ) );
?>
    <div class="surface article-detail">
        <span class="eyebrow">شبیه‌ساز امن درگاه بانکی (Sandbox)</span>
        <h1>تایید پرداخت آزمایشی</h1>
        <p>این صفحه شبیه‌ساز درگاه آزمایشی است و <strong>تراکنش واقعی بانکی انجام نمی‌دهد</strong>. مبلغ زیر مستقیماً از رکورد سرور خوانده شده است:</p>
        <div class="spec-grid" style="margin:20px 0;">
            <div><span>شناسه تراکنش</span><b><?php echo esc_html( $payment->authority ); ?></b></div>
            <div><span>شماره سفارش</span><b>#<?php echo (int) $payment->order_id; ?></b></div>
            <div><span>مبلغ قابل پرداخت</span><b><?php echo esc_html( temsah_format_toman( $payment->amount ) ); ?></b></div>
        </div>
        <div style="display:flex;gap:12px;flex-wrap:wrap;">
            <a class="btn-primary" href="<?php echo esc_url( $ok_url ); ?>">تکمیل موفق پرداخت آزمایشی</a>
            <a class="btn-outline" href="<?php echo esc_url( $fail_url ); ?>">انصراف / شبیه‌سازی خطای پرداخت</a>
        </div>
    </div>
<?php
    endif;
else :
    $result = Temsah_Payments::process_callback( $_GET );
?>
    <div class="surface article-detail">
        <?php if ( ! empty( $result['ok'] ) ) : $order = $result['order']; ?>
            <span class="tm-badge-tag ok" style="margin-bottom:10px;">پرداخت تایید شد</span>
            <h1>سفارش <?php echo esc_html( $order->order_number ); ?> تکمیل شد</h1>
            <p><?php echo esc_html( $result['message'] ); ?></p>
            <div class="spec-grid" style="margin:20px 0;">
                <div><span>شماره سفارش</span><b><?php echo esc_html( $order->order_number ); ?></b></div>
                <div><span>کد رهگیری</span><b><?php echo esc_html( $result['ref_id'] ?? 'ثبت‌شده' ); ?></b></div>
                <div><span>مبلغ کل</span><b><?php echo esc_html( temsah_format_toman( $order->total ) ); ?></b></div>
                <div><span>وضعیت</span><b>پرداخت‌شده</b></div>
            </div>
            <div style="display:flex;gap:12px;flex-wrap:wrap;">
                <a class="btn-primary" href="<?php echo esc_url( temsah_url( 'account/downloads' ) ); ?>">دریافت فایل‌ها و دانلودها</a>
                <a class="btn-outline" href="<?php echo esc_url( temsah_url( 'account/order-detail', array( 'id' => $order->id ) ) ); ?>">مشاهده فاکتور سفارش</a>
            </div>
        <?php else : ?>
            <span class="tm-badge-tag err" style="margin-bottom:10px;">تراکنش ناموفق</span>
            <h1>پرداخت تایید نشد</h1>
            <p><?php echo esc_html( $result['message'] ?? 'پرداخت با خطا مواجه شد.' ); ?></p>
            <div style="display:flex;gap:12px;margin-top:16px;">
                <a class="btn-primary" href="<?php echo esc_url( temsah_url( 'checkout' ) ); ?>">تلاش مجدد</a>
                <a class="btn-outline" href="<?php echo esc_url( temsah_url( 'account/orders' ) ); ?>">مشاهده سفارش‌ها</a>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>
</section>
</main>
<?php get_footer(); ?>
