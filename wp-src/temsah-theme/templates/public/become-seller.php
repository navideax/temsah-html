<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
get_header();
$settings = get_option( 'temsah_settings', array() );
$rate     = (int) ( $settings['default_commission_rate'] ?? 70 );
$app_url  = is_user_logged_in()
    ? ( Temsah_Security::is_approved_vendor() ? temsah_url( 'vendor' ) : temsah_url( 'account/seller-application' ) )
    : temsah_url( 'register' );
?>
<main>
<div class="seller-landing-hero">
    <div class="wrap seller-landing-grid">
        <div>
            <span class="seller-kicker">همکاری با تمساح</span>
            <h1>محصولت رو بساز،<br/><span>فروشگاهت رو گسترش بده.</span></h1>
            <p>برای توسعه‌دهنده‌ها و طراح‌هایی که می‌خوان قالب، افزونه، اسکریپت و ابزارهای خودشون رو در یک بازار تخصصی عرضه کنن.</p>
            <div class="seller-landing-cta">
                <a class="btn-primary" href="<?php echo esc_url( $app_url ); ?>">ثبت درخواست فروشندگی <?php echo temsah_ico( 'arrow---left.svg' ); ?></a>
                <a class="btn-outline" href="#seller-process">آشنایی با مراحل همکاری</a>
            </div>
            <small>سهم پیش‌فرض فروشنده در تمساح <?php echo esc_html( temsah_fa_digits( $rate ) ); ?>٪ از فروش هر محصول پس از تایید مدارک است.</small>
        </div>
        <div class="seller-landing-art">
            <div class="seller-art-top"><?php echo temsah_ico( 'work.svg' ); ?> مرکز فروشندگان تمساح</div>
            <div class="seller-art-kpi">
                <div><small>سهم فروشنده</small><strong><?php echo esc_html( temsah_fa_digits( $rate ) ); ?>٪</strong></div>
                <div><small>تسویه بانکی</small><strong>شبا</strong></div>
                <div><small>تحویل فایل</small><strong>فوری</strong></div>
            </div>
        </div>
    </div>
</div>

<section class="wrap page-pad" id="seller-process">
    <div class="page-heading"><div><span class="eyebrow">فرایند تایید و انتشار</span><h2>مراحل شروع همکاری</h2></div></div>
    <div class="sellers-grid">
        <div class="surface seller-tile"><span class="vendor-avatar">۱</span><h3>ثبت‌نام و تکمیل اطلاعات</h3><p>وارد حساب کاربری شوید و فرم درخواست فروشندگی را با مشخصات هویتی و شبا تکمیل کنید.</p></div>
        <div class="surface seller-tile"><span class="vendor-avatar">۲</span><h3>بررسی و تایید مدیریت</h3><p>مدارک و اطلاعات فروشگاه توسط تیم تمساح بررسی و پس از تایید دسترسی فروشنده فعال می‌شود.</p></div>
        <div class="surface seller-tile"><span class="vendor-avatar">۳</span><h3>انتشار محصول و کسب درآمد</h3><p>فایل‌های محصول در مخزن امن ذخیره شده و پس از هر فروش سهم شما به کیف پول واریز می‌شود.</p></div>
    </div>
</section>
</main>
<?php get_footer(); ?>
