<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
get_header();
?>
<main>
<section class="wrap page-pad">
    <div class="breadcrumbs"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">خانه</a><span>/</span><span>پشتیبانی</span></div>
    <div class="page-heading"><div><span class="eyebrow">مرکز راهنما</span><h1>راهنمای پشتیبانی تمساح</h1><p>پاسخ سوالات متداول درباره خرید، دانلود فایل‌ها، لایسنس و پشتیبانی محصولات</p></div></div>
    <div class="page-two-column">
        <div>
            <article class="surface article-detail">
                <h2>مسیر دریافت پشتیبانی فنی</h2>
                <p>پشتیبانی هر محصول به صورت مستقیم توسط توسعه‌دهنده همان محصول و از طریق سیستم تیکت در حساب کاربری انجام می‌شود.</p>
                <details class="faq"><summary>چطور برای محصول خریداری‌شده تیکت ثبت کنم؟</summary><p>به حساب کاربری خود وارد شوید، بخش پشتیبانی و سپس «ثبت تیکت جدید» را انتخاب کنید. محصولات خریداری‌شده شما به صورت خودکار در لیست تیکت نمایش داده می‌شوند.</p></details>
                <details class="faq"><summary>فایل‌ها تا چه زمانی در دسترس هستند؟</summary><p>فایل‌ها و نسخه‌های جدید منتشرشده توسط فروشنده پس از خرید در بخش دانلودهای حساب کاربری باقی می‌مانند.</p></details>
                <details class="faq"><summary>اگر قبل از خرید سوالی داشته باشم چه کنم؟</summary><p>در صفحه هر محصول تب «پرسش و پاسخ» برای پرسش‌های پیش از خرید در نظر گرفته شده است.</p></details>
            </article>
        </div>
        <aside class="page-side-note">
            <div class="surface summary-card">
                <h3>دسترسی سریع پنل</h3>
                <div class="side-links">
                    <a href="<?php echo esc_url( temsah_url( 'account/tickets' ) ); ?>">مشاهده تیکت‌های من</a>
                    <a href="<?php echo esc_url( temsah_url( 'account/ticket-new' ) ); ?>">ثبت تیکت جدید</a>
                    <a href="<?php echo esc_url( temsah_url( 'contact' ) ); ?>">تماس با مدیریت</a>
                </div>
            </div>
        </aside>
    </div>
</section>
</main>
<?php get_footer(); ?>
