<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
get_header();
$articles = get_posts( array( 'post_type' => 'post', 'post_status' => 'publish', 'numberposts' => 24 ) );
$icons    = array( 'document.svg', 'work.svg', 'discovery.svg', 'paper.svg' );
?>
<main>
<section class="wrap page-pad">
    <div class="breadcrumbs"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">خانه</a><span>/</span><span>مجله تمساح</span></div>
    <div class="page-heading">
        <div>
            <h1>مجله تمساح</h1>
            <p>راهنماها، آموزش‌ها و نکات کاربردی توسعه وب.</p>
        </div>
    </div>
    <?php if ( empty( $articles ) ) : ?>
        <div class="tm-empty-state"><h3>هنوز مقاله‌ای منتشر نشده است</h3><p>مقالات آموزشی و راهنماهای توسعه وب پس از انتشار در پیشخوان وردپرس اینجا نمایش داده می‌شوند.</p></div>
    <?php else : ?>
        <div class="article-grid">
            <?php foreach ( $articles as $idx => $art ) : ?>
            <a class="surface article-card" href="<?php echo esc_url( get_permalink( $art ) ); ?>">
                <div class="article-image article-<?php echo $idx % 4; ?>"><?php echo temsah_ico( $icons[ $idx % 4 ] ); ?></div>
                <div>
                    <span>مجله تمساح</span>
                    <h3><?php echo esc_html( $art->post_title ); ?></h3>
                    <p><?php echo esc_html( wp_trim_words( $art->post_excerpt ?: wp_strip_all_tags( $art->post_content ), 16 ) ); ?></p>
                    <b>مطالعه مطلب <?php echo temsah_ico( 'arrow---left.svg' ); ?></b>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
</main>
<?php get_footer(); ?>
