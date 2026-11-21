<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
get_header();

$cur_cat = sanitize_title( wp_unslash( $_GET['category'] ?? 'all' ) );
if ( is_tax( 'temsah_product_cat' ) ) {
    $term    = get_queried_object();
    $cur_cat = $term->slug ?? 'all';
}
$cur_sort  = sanitize_key( wp_unslash( $_GET['sort'] ?? 'popular' ) );
$search_q  = sanitize_text_field( wp_unslash( $_GET['q'] ?? '' ) );
$free_only = ! empty( $_GET['free_only'] );
$sale_only = ! empty( $_GET['sale_only'] );

$products = Temsah_CPT::query_products( array(
    'category'  => $cur_cat,
    'sort'      => $cur_sort,
    'search'    => $search_q,
    'free_only' => $free_only,
    'sale_only' => $sale_only,
    'limit'     => 60,
) );
$wish_ids = is_user_logged_in() ? Temsah_Buyer::get_wishlist_product_ids( get_current_user_id() ) : array();
$cats     = array(
    'all'       => 'همه محصولات',
    'wordpress' => 'قالب وردپرس',
    'plugin'    => 'افزونه وردپرس',
    'html'      => 'قالب HTML',
    'script'    => 'اسکریپت PHP',
    'dashboard' => 'پنل مدیریت',
    'app'       => 'سورس اپلیکیشن',
    'ui'        => 'رابط کاربری',
);
?>
<main>
<section class="wrap page-pad">
    <div class="breadcrumbs"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">خانه</a><span>/</span><span>مارکت محصولات</span></div>
    <div class="page-heading">
        <div>
            <span class="eyebrow">بازار تمساح</span>
            <h1>محصولات دیجیتال</h1>
            <p>قالب‌ها، افزونه‌ها و اسکریپت‌های منتخب برای پروژه‌های مختلف</p>
        </div>
        <div class="catalog-count">
            <strong id="catalogCount"><?php echo esc_html( temsah_format_number( count( $products ) ) ); ?></strong>
            <span>محصول موجود</span>
        </div>
    </div>

    <div class="catalog-layout">
        <aside class="filter-box">
            <div class="filter-title">
                <?php echo temsah_ico( 'filter.svg' ); ?> فیلتر محصولات
                <a href="<?php echo esc_url( temsah_url( 'shop' ) ); ?>" id="resetFilters">حذف فیلترها</a>
            </div>
            <div class="filter-section">
                <h3>دسته‌بندی‌ها</h3>
                <div id="categoryFilters">
                    <?php foreach ( $cats as $slug => $label ) : ?>
                        <a class="<?php echo $cur_cat === $slug ? 'active' : ''; ?>" href="<?php echo esc_url( temsah_url( 'shop', array_filter( array( 'category' => 'all' === $slug ? null : $slug, 'sort' => $cur_sort, 'q' => $search_q ?: null ) ) ) ); ?>" style="display:block;width:100%;text-align:right;padding:8px 12px;border-radius:10px;text-decoration:none;">
                            <?php echo esc_html( $label ); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <form method="get" action="<?php echo esc_url( temsah_url( 'shop' ) ); ?>" class="filter-section">
                <?php if ( ! get_option( 'permalink_structure' ) ) : ?><input type="hidden" name="post_type" value="temsah_product"><?php endif; ?>
                <?php if ( 'all' !== $cur_cat ) : ?><input type="hidden" name="category" value="<?php echo esc_attr( $cur_cat ); ?>"><?php endif; ?>
                <?php if ( $search_q ) : ?><input type="hidden" name="q" value="<?php echo esc_attr( $search_q ); ?>"><?php endif; ?>
                <input type="hidden" name="sort" value="<?php echo esc_attr( $cur_sort ); ?>">
                <h3>محدوده قیمت</h3>
                <label class="filter-check"><input name="free_only" type="checkbox" value="1" <?php checked( $free_only ); ?> onchange="this.form.submit()"/> محصولات رایگان</label>
                <label class="filter-check"><input name="sale_only" type="checkbox" value="1" <?php checked( $sale_only ); ?> onchange="this.form.submit()"/> فقط تخفیف‌دارها</label>
            </form>
        </aside>

        <div class="catalog-main">
            <div class="catalog-toolbar">
                <div class="sort-items">
                    <span>مرتب‌سازی:</span>
                    <?php
                    $sorts = array( 'popular' => 'پرفروش‌ترین', 'new' => 'جدیدترین', 'cheap' => 'ارزان‌ترین', 'expensive' => 'گران‌ترین' );
                    foreach ( $sorts as $sk => $sl ) :
                    ?>
                        <a class="<?php echo $cur_sort === $sk ? 'active' : ''; ?>" href="<?php echo esc_url( temsah_url( 'shop', array_filter( array( 'category' => 'all' === $cur_cat ? null : $cur_cat, 'sort' => $sk, 'q' => $search_q ?: null, 'free_only' => $free_only ? 1 : null, 'sale_only' => $sale_only ? 1 : null ) ) ) ); ?>"><?php echo esc_html( $sl ); ?></a>
                    <?php endforeach; ?>
                </div>
                <span id="resultCount"><?php echo esc_html( temsah_format_number( count( $products ) ) ); ?> محصول</span>
            </div>

            <?php if ( empty( $products ) ) : ?>
                <div class="tm-empty-state">
                    <h3>محصولی با این فیلترها یافت نشد</h3>
                    <p>در حال حاضر محصولی مطابق جستجو یا فیلتر انتخابی در مارکت‌پلیس موجود نیست.</p>
                </div>
            <?php else : ?>
                <div class="cards-grid catalog-grid" id="catalogList">
                    <?php foreach ( $products as $p ) { get_template_part( 'template-parts/product-card', null, array( 'product' => $p, 'wish_ids' => $wish_ids ) ); } ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
</main>
<?php get_footer(); ?>
