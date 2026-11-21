<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
get_header();

$wish_ids  = is_user_logged_in() ? Temsah_Buyer::get_wishlist_product_ids( get_current_user_id() ) : array();
$popular   = Temsah_CPT::query_products( array( 'sort' => 'popular', 'limit' => 8 ) );
$newest    = Temsah_CPT::query_products( array( 'sort' => 'new', 'limit' => 8 ) );
$discounts = Temsah_CPT::query_products( array( 'sale_only' => true, 'sort' => 'discount', 'limit' => 8 ) );
if ( empty( $discounts ) ) {
    $discounts = $popular;
}
$compact   = array_slice( $popular, 0, 6 );
$weekly    = Temsah_CPT::get_weekly_developer();
$weekly_ps = $weekly ? Temsah_CPT::query_products( array( 'vendor_id' => (int) $weekly->id, 'limit' => 3 ) ) : array();
$articles  = get_posts( array( 'post_type' => 'post', 'post_status' => 'publish', 'numberposts' => 4 ) );
$hero_img  = absint( temsah_theme_opt( 'hero_image_id', 0 ) );
$hero_src  = $hero_img ? wp_get_attachment_image_url( $hero_img, 'large' ) : '';

$stories = array(
    array( '01', 'قالب وردپرس', 'فروشگاهت رو متفاوت بساز', 'قالب‌های فروشگاهی حرفه‌ای', 'wordpress', 'story-01.svg' ),
    array( '02', 'افزونه وردپرس', 'ابزارهای قدرتمند وردپرس', 'افزونه‌های کاربردی برای رشد', 'plugin', 'story-02.svg' ),
    array( '03', 'پنل مدیریت', 'مدیریت پروژه در یک نگاه', 'داشبوردهای خوش‌ساخت', 'dashboard', 'story-03.svg' ),
    array( '04', 'اسکریپت PHP', 'سریع‌تر توسعه بده', 'سیستم‌های آماده تحت وب', 'script', 'story-04.svg' ),
    array( '05', 'قالب HTML', 'طراحی تمیز و استاندارد', 'شروع سریع برای طراحان وب', 'html', 'story-05.svg' ),
    array( '06', 'رابط کاربری', 'ایده‌های مدرن رابط کاربری', 'کیت‌ها و کامپوننت‌های آماده', 'ui', 'story-06.svg' ),
);
?>
<main>
<div class="wrap homepage">
    <div class="home-top">
        <div class="hero-main">
            <div class="hero-copy">
                <?php
                $heading = temsah_theme_opt( 'hero_heading', 'ابزار بهتر، پروژه حرفه‌ای‌تر.' );
                $parts   = explode( '،', $heading, 2 );
                ?>
                <h1><?php echo esc_html( $parts[0] . ( isset( $parts[1] ) ? '،' : '' ) ); ?><?php if ( isset( $parts[1] ) ) : ?><br/><span><?php echo esc_html( trim( $parts[1] ) ); ?></span><?php endif; ?></h1>
                <p><?php echo esc_html( temsah_theme_opt( 'hero_subtitle', 'قالب، افزونه و اسکریپت مورد نیازت رو از بین محصولات توسعه‌دهندگان ایرانی پیدا کن.' ) ); ?></p>
                <form action="<?php echo esc_url( temsah_url( 'shop' ) ); ?>" class="hero-search" method="get">
                    <?php if ( ! get_option( 'permalink_structure' ) ) : ?><input type="hidden" name="post_type" value="temsah_product"><?php endif; ?>
                    <input name="q" placeholder="دنبال چی می‌گردی؟ مثلاً قالب فروشگاهی"/>
                    <button type="submit">جستجو <?php echo temsah_ico( 'search.svg' ); ?></button>
                </form>
                <div class="hero-quick">
                    <span>جستجوهای پرطرفدار:</span>
                    <a href="<?php echo esc_url( temsah_url( 'shop', array( 'category' => 'wordpress' ) ) ); ?>">فروشگاهی</a>
                    <a href="<?php echo esc_url( temsah_url( 'shop', array( 'category' => 'plugin' ) ) ); ?>">افزونه وردپرس</a>
                    <a href="<?php echo esc_url( temsah_url( 'shop', array( 'category' => 'script' ) ) ); ?>">اسکریپت PHP</a>
                </div>
            </div>
            <div class="hero-illustration">
                <?php if ( $hero_src ) : ?>
                    <img src="<?php echo esc_url( $hero_src ); ?>" alt="TEMSAH Marketplace" style="width:100%;height:100%;object-fit:cover;border-radius:16px;">
                <?php else : ?>
                    <div class="hero-window"><div class="hw-bar"><span></span><span></span><span></span></div><div class="hw-layout"><aside><i></i><i></i><i></i><i></i></aside><div><span class="hw-title"></span><div class="hw-cards"><i></i><i></i><i></i></div><div class="hw-stats"><i></i><i></i></div></div></div></div>
                    <div class="floating-note floating-a"><?php echo temsah_ico( 'shield-done.svg' ); ?> انتخاب مطمئن‌تر</div>
                    <div class="floating-note floating-b"><?php echo temsah_ico( 'tick-square.svg' ); ?> محصولات متنوع</div>
                <?php endif; ?>
            </div>
        </div>
        <div class="hero-side">
            <a class="hero-promo promo-orange" href="<?php echo esc_url( temsah_url( 'shop', array( 'sort' => 'discount' ) ) ); ?>">
                <div><span>فرصت‌های خرید</span><h3>پیشنهادهای<br/>ویژه این هفته</h3><b>مشاهده محصولات ←</b></div>
                <div class="promo-decor">٪</div>
            </a>
            <a class="hero-promo promo-dark" href="<?php echo esc_url( temsah_url( 'become-seller' ) ); ?>">
                <span>برای توسعه‌دهندگان</span><h3>محصولت رو<br/>در تمساح بفروش</h3><b>شروع فروشندگی ←</b>
                <div class="promo-bars"><i></i><i></i><i></i><i></i><i></i></div>
            </a>
        </div>
    </div>

    <div class="trustbar">
        <div><?php echo temsah_ico( 'shield-done.svg' ); ?> <span>خرید محصولات دیجیتال</span></div>
        <div><?php echo temsah_ico( 'work.svg' ); ?> <span>فروشندگان مستقل</span></div>
        <div><?php echo temsah_ico( 'document.svg' ); ?> <span>اطلاعات شفاف محصول</span></div>
        <div><?php echo temsah_ico( 'chat.svg' ); ?> <span>مسیر پشتیبانی مشخص</span></div>
    </div>

    <div class="home-cats">
        <div class="section-head">
            <div><h2>از کجا شروع کنیم؟</h2><p>محصول مناسب پروژه‌ات رو سریع‌تر پیدا کن.</p></div>
            <a class="view-all" href="<?php echo esc_url( temsah_url( 'shop' ) ); ?>">تمام دسته‌ها <?php echo temsah_ico( 'arrow---left.svg' ); ?></a>
        </div>
        <div class="cat-grid">
            <a class="cat-card" href="<?php echo esc_url( temsah_url( 'shop', array( 'category' => 'wordpress' ) ) ); ?>"><span class="cat-icon cat-0"><?php echo temsah_ico( 'category.svg' ); ?></span><b>قالب وردپرس</b><small>قالب‌های فروشگاهی، شرکتی و خدماتی</small><?php echo temsah_ico( 'arrow---left.svg', 'ico cat-arr' ); ?></a>
            <a class="cat-card" href="<?php echo esc_url( temsah_url( 'shop', array( 'category' => 'plugin' ) ) ); ?>"><span class="cat-icon cat-1"><?php echo temsah_ico( 'work.svg' ); ?></span><b>افزونه وردپرس</b><small>ابزارهای حرفه‌ای وردپرس</small><?php echo temsah_ico( 'arrow---left.svg', 'ico cat-arr' ); ?></a>
            <a class="cat-card" href="<?php echo esc_url( temsah_url( 'shop', array( 'category' => 'html' ) ) ); ?>"><span class="cat-icon cat-2"><?php echo temsah_ico( 'document.svg' ); ?></span><b>قالب HTML</b><small>قالب‌های آماده وب</small><?php echo temsah_ico( 'arrow---left.svg', 'ico cat-arr' ); ?></a>
            <a class="cat-card" href="<?php echo esc_url( temsah_url( 'shop', array( 'category' => 'script' ) ) ); ?>"><span class="cat-icon cat-3"><?php echo temsah_ico( 'setting.svg' ); ?></span><b>اسکریپت PHP</b><small>سیستم‌های تحت وب</small><?php echo temsah_ico( 'arrow---left.svg', 'ico cat-arr' ); ?></a>
            <a class="cat-card" href="<?php echo esc_url( temsah_url( 'shop', array( 'category' => 'dashboard' ) ) ); ?>"><span class="cat-icon cat-4"><?php echo temsah_ico( 'activity.svg' ); ?></span><b>پنل مدیریت</b><small>داشبورد و مدیریت</small><?php echo temsah_ico( 'arrow---left.svg', 'ico cat-arr' ); ?></a>
            <a class="cat-card" href="<?php echo esc_url( temsah_url( 'shop', array( 'category' => 'app' ) ) ); ?>"><span class="cat-icon cat-5"><?php echo temsah_ico( 'discovery.svg' ); ?></span><b>سورس اپلیکیشن</b><small>موبایل و دسکتاپ</small><?php echo temsah_ico( 'arrow---left.svg', 'ico cat-arr' ); ?></a>
            <a class="cat-card" href="<?php echo esc_url( temsah_url( 'shop', array( 'category' => 'ui' ) ) ); ?>"><span class="cat-icon cat-6"><?php echo temsah_ico( 'paper.svg' ); ?></span><b>رابط کاربری</b><small>کیت‌های UI و طراحی</small><?php echo temsah_ico( 'arrow---left.svg', 'ico cat-arr' ); ?></a>
        </div>
    </div>
</div>

<section class="wrap ts-stories-section">
    <div class="section-head ts-section-head">
        <div><h2>ایده‌هایی برای پروژه بعدی</h2><p>مجموعه‌ای از مسیرهای محبوب برای ساخت و توسعه محصولات دیجیتال</p></div>
        <div class="ts-slider-controls">
            <button aria-label="قبلی" class="ts-slider-arrow" data-slide="prev" data-slider="temsah-story-slider" type="button"><?php echo temsah_ico( 'arrow---right.svg' ); ?></button>
            <button aria-label="بعدی" class="ts-slider-arrow" data-slide="next" data-slider="temsah-story-slider" type="button"><?php echo temsah_ico( 'arrow---left.svg' ); ?></button>
        </div>
    </div>
    <div aria-label="اسلایدر تصویری دسته‌بندی‌ها" class="ts-slider-viewport ts-stories-viewport" dir="rtl" id="temsah-story-slider" tabindex="0">
        <div class="ts-slider-track ts-story-track">
            <?php foreach ( $stories as $st ) : ?>
            <a aria-label="<?php echo esc_attr( $st[2] ); ?>" class="ts-story" href="<?php echo esc_url( temsah_url( 'shop', array( 'category' => $st[4] ) ) ); ?>">
                <img alt="<?php echo esc_attr( $st[1] ); ?>" loading="lazy" src="<?php echo temsah_asset( 'stories/' . $st[5] ); ?>"/>
                <span class="ts-story-shade"></span>
                <span class="ts-story-content">
                    <small><?php echo esc_html( $st[1] ); ?></small>
                    <strong><?php echo esc_html( $st[2] ); ?></strong>
                    <span><?php echo esc_html( $st[3] ); ?></span>
                    <span class="ts-story-link">مشاهده محصولات <?php echo temsah_ico( 'arrow---left.svg' ); ?></span>
                </span>
                <span class="ts-story-index"><?php echo esc_html( temsah_fa_digits( $st[0] ) ); ?></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="product-section wrap">
    <div class="section-head ts-section-head">
        <div><h2>پیشنهادهای منتخب</h2><p>محصولاتی برای شروع بهتر پروژه بعدی.</p></div>
        <div class="ts-slider-head-actions">
            <div class="ts-slider-controls">
                <button aria-label="محصول قبلی" class="ts-slider-arrow" data-slide="prev" data-slider="temsah-products-1" type="button"><?php echo temsah_ico( 'arrow---right.svg' ); ?></button>
                <button aria-label="محصول بعدی" class="ts-slider-arrow" data-slide="next" data-slider="temsah-products-1" type="button"><?php echo temsah_ico( 'arrow---left.svg' ); ?></button>
            </div>
            <a class="view-all" href="<?php echo esc_url( temsah_url( 'shop', array( 'sort' => 'popular' ) ) ); ?>">مشاهده همه <?php echo temsah_ico( 'arrow---left.svg' ); ?></a>
        </div>
    </div>
    <?php if ( empty( $popular ) ) : ?>
        <div class="tm-empty-state"><h3>هنوز محصولی منتشر نشده است</h3><p>محصولات تاییدشده فروشگاه‌های تمساح پس از انتشار در این بخش نمایش داده می‌شوند.</p></div>
    <?php else : ?>
        <div aria-label="پیشنهادهای منتخب" class="ts-slider-viewport" dir="rtl" id="temsah-products-1" tabindex="0">
            <div class="ts-slider-track">
                <?php foreach ( $popular as $p ) { get_template_part( 'template-parts/product-card', null, array( 'product' => $p, 'wish_ids' => $wish_ids ) ); } ?>
            </div>
        </div>
    <?php endif; ?>
</section>

<div class="wrap editorial-banner">
    <div>
        <h2><?php echo esc_html( temsah_theme_opt( 'promo_title', 'برای هر ایده، یک ابزار آماده وجود داره.' ) ); ?></h2>
        <p>با دسته‌بندی دقیق و اطلاعات محصول، انتخاب رو ساده‌تر کردیم.</p>
        <a href="<?php echo esc_url( temsah_url( 'shop' ) ); ?>">محصولات رو ببین <?php echo temsah_ico( 'arrow---left.svg' ); ?></a>
    </div>
    <div class="banner-shapes"><i></i><i></i><i></i></div>
</div>

<?php if ( ! empty( $popular ) ) : ?>
<section class="product-section wrap">
    <div class="section-head ts-section-head">
        <div><h2>پرفروش‌ترین محصولات</h2><p>محصولاتی که بیشترین استقبال و خرید را داشته‌اند.</p></div>
        <div class="ts-slider-head-actions">
            <div class="ts-slider-controls">
                <button aria-label="محصول قبلی" class="ts-slider-arrow" data-slide="prev" data-slider="temsah-products-2" type="button"><?php echo temsah_ico( 'arrow---right.svg' ); ?></button>
                <button aria-label="محصول بعدی" class="ts-slider-arrow" data-slide="next" data-slider="temsah-products-2" type="button"><?php echo temsah_ico( 'arrow---left.svg' ); ?></button>
            </div>
            <a class="view-all" href="<?php echo esc_url( temsah_url( 'shop', array( 'sort' => 'popular' ) ) ); ?>">مشاهده همه <?php echo temsah_ico( 'arrow---left.svg' ); ?></a>
        </div>
    </div>
    <div aria-label="پرفروش‌ترین محصولات" class="ts-slider-viewport" dir="rtl" id="temsah-products-2" tabindex="0">
        <div class="ts-slider-track">
            <?php foreach ( $popular as $p ) { get_template_part( 'template-parts/product-card', null, array( 'product' => $p, 'wish_ids' => $wish_ids ) ); } ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if ( ! empty( $newest ) ) : ?>
<section class="product-section wrap section-last">
    <div class="section-head ts-section-head">
        <div><h2>تازه به مارکت اضافه شدند</h2><p>کشف محصولات و ابزارهای جدید.</p></div>
        <div class="ts-slider-head-actions">
            <div class="ts-slider-controls">
                <button aria-label="محصول قبلی" class="ts-slider-arrow" data-slide="prev" data-slider="temsah-products-3" type="button"><?php echo temsah_ico( 'arrow---right.svg' ); ?></button>
                <button aria-label="محصول بعدی" class="ts-slider-arrow" data-slide="next" data-slider="temsah-products-3" type="button"><?php echo temsah_ico( 'arrow---left.svg' ); ?></button>
            </div>
            <a class="view-all" href="<?php echo esc_url( temsah_url( 'shop', array( 'sort' => 'new' ) ) ); ?>">مشاهده همه <?php echo temsah_ico( 'arrow---left.svg' ); ?></a>
        </div>
    </div>
    <div aria-label="تازه به مارکت اضافه شدند" class="ts-slider-viewport" dir="rtl" id="temsah-products-3" tabindex="0">
        <div class="ts-slider-track">
            <?php foreach ( $newest as $p ) { get_template_part( 'template-parts/product-card', null, array( 'product' => $p, 'wish_ids' => $wish_ids ) ); } ?>
        </div>
    </div>
</section>
<?php endif; ?>

<div class="wrap bottom-strip">
    <div><?php echo temsah_ico( 'work.svg' ); ?><span><b>توسعه‌دهنده‌ای؟</b><small>محصولت رو عرضه کن و فروشگاه خودت رو بساز.</small></span></div>
    <a href="<?php echo esc_url( temsah_url( 'become-seller' ) ); ?>">شروع فروشندگی <?php echo temsah_ico( 'arrow---left.svg' ); ?></a>
</div>

<?php if ( ! empty( $compact ) ) : ?>
<section class="wrap v28-compact-section">
    <div class="section-head">
        <div><h2>محصولات در یک نگاه</h2><p>پیشنهادهای کاربردی، کوتاه و سریع برای انتخاب بعدی</p></div>
        <a class="view-all" href="<?php echo esc_url( temsah_url( 'shop' ) ); ?>">همه محصولات <?php echo temsah_ico( 'arrow---left.svg' ); ?></a>
    </div>
    <div class="v28-compact-grid">
        <?php foreach ( $compact as $cp ) : ?>
        <a aria-label="مشاهده <?php echo esc_attr( $cp->post_title ); ?>" class="v28-compact-item" href="<?php echo esc_url( get_permalink( (int) $cp->post_id ) ); ?>">
            <span class="v28-compact-thumb"><?php echo temsah_cover_html( $cp, true ); ?></span>
            <strong><?php echo esc_html( $cp->post_title ); ?></strong>
        </a>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<?php if ( $weekly ) : ?>
<section class="wrap v28-weekly-developer">
    <div class="v28-developer-card">
        <div class="v28-developer-info">
            <span class="v28-developer-eyebrow"><?php echo temsah_ico( 'star.svg' ); ?> منتخب این هفته</span>
            <div class="v28-developer-heading">
                <span class="v28-developer-avatar"><?php echo temsah_ico( 'work.svg' ); ?></span>
                <div><h2>توسعه‌دهنده برتر هفته</h2><h3><?php echo esc_html( $weekly->store_name ); ?></h3></div>
            </div>
            <p><?php echo esc_html( $weekly->bio ?: 'با محصولات این توسعه‌دهنده آشنا شو و ابزار مناسب پروژه‌ات رو پیدا کن.' ); ?></p>
            <div class="v28-developer-metrics">
                <span><?php echo esc_html( temsah_format_number( count( $weekly_ps ) ) ); ?> محصول منتخب</span>
                <span><?php echo esc_html( temsah_format_number( $weekly->total_sales ) ); ?> فروش ثبت‌شده</span>
            </div>
            <a class="v28-developer-link" href="<?php echo esc_url( temsah_url( 'seller/' . $weekly->store_slug ) ); ?>">مشاهده صفحه توسعه‌دهنده <?php echo temsah_ico( 'arrow---left.svg' ); ?></a>
        </div>
        <div class="v28-developer-products">
            <?php foreach ( $weekly_ps as $wp_prod ) : ?>
            <a class="v28-developer-product" href="<?php echo esc_url( get_permalink( (int) $wp_prod->post_id ) ); ?>">
                <span class="v28-developer-product-cover"><?php echo temsah_cover_html( $wp_prod, true ); ?></span>
                <span><strong><?php echo esc_html( $wp_prod->post_title ); ?></strong><small><?php echo esc_html( Temsah_CPT::get_product_category_name( (int) $wp_prod->post_id ) ); ?></small></span>
                <?php echo temsah_ico( 'arrow---left.svg' ); ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="wrap v28-home-articles">
    <div class="section-head">
        <div><h2>تازه‌ترین مقالات مجله تمساح</h2><p>راهنماهای کاربردی برای انتخاب، طراحی و توسعه بهتر</p></div>
        <a class="view-all" href="<?php echo esc_url( temsah_url( 'articles' ) ); ?>">همه مقالات <?php echo temsah_ico( 'arrow---left.svg' ); ?></a>
    </div>
    <?php if ( empty( $articles ) ) : ?>
        <div class="tm-empty-state"><p>هنوز مقاله‌ای در مجله منتشر نشده است.</p></div>
    <?php else : ?>
        <div class="article-grid">
            <?php $icons = array( 'document.svg', 'work.svg', 'discovery.svg', 'paper.svg' ); foreach ( $articles as $idx => $art ) : ?>
            <a class="surface article-card" href="<?php echo esc_url( get_permalink( $art ) ); ?>">
                <div class="article-image article-<?php echo $idx % 4; ?>"><?php echo temsah_ico( $icons[ $idx % 4 ] ); ?></div>
                <div>
                    <span>مجله تمساح</span>
                    <h3><?php echo esc_html( $art->post_title ); ?></h3>
                    <p><?php echo esc_html( wp_trim_words( $art->post_excerpt ?: wp_strip_all_tags( $art->post_content ), 15 ) ); ?></p>
                    <b>مطالعه مطلب <?php echo temsah_ico( 'arrow---left.svg' ); ?></b>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
</main>
<?php get_footer(); ?>
