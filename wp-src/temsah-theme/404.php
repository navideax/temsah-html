<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
get_header();
?>
<main>
<section class="wrap page-pad">
    <div class="breadcrumbs"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">خانه</a><span>/</span><span>صفحه پیدا نشد</span></div>
    <div class="page-heading"><div><span class="eyebrow">تمساح مارکت</span><h1>صفحه پیدا نشد</h1><p>آدرسی که وارد کرده‌اید در دسترس نیست یا جابه‌جا شده است.</p></div></div>
    <div class="page-two-column">
        <div>
            <div class="surface empty-state">
                <h2>۴۰۴</h2>
                <p>می‌توانید به صفحه اصلی بازگردید یا در فروشگاه جستجو کنید.</p>
                <a class="btn-primary" href="<?php echo esc_url( home_url( '/' ) ); ?>">بازگشت به صفحه اصلی</a>
            </div>
        </div>
    </div>
</section>
</main>
<?php get_footer(); ?>
