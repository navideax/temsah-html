<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
get_header();
the_post();
$related = get_posts( array( 'post_type' => 'post', 'post_status' => 'publish', 'numberposts' => 3, 'exclude' => array( get_the_ID() ) ) );
?>
<main>
<section class="wrap page-pad">
    <div class="breadcrumbs"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">خانه</a><span>/</span><a href="<?php echo esc_url( temsah_url( 'articles' ) ); ?>">مجله تمساح</a><span>/</span><span><?php the_title(); ?></span></div>
    <div class="page-heading">
        <div>
            <span class="eyebrow">تمساح مارکت</span>
            <h1><?php the_title(); ?></h1>
            <p><?php echo esc_html( get_the_excerpt() ); ?></p>
        </div>
    </div>
    <div class="page-two-column">
        <div>
            <article class="surface article-detail">
                <span class="eyebrow">مجله تمساح · <?php echo esc_html( temsah_fa_digits( get_the_date( 'Y/m/d' ) ) ); ?></span>
                <h2><?php the_title(); ?></h2>
                <div class="tm-article-content"><?php the_content(); ?></div>
            </article>
        </div>
        <aside class="page-side-note">
            <div class="surface summary-card">
                <h3>مطالب پیشنهادی</h3>
                <?php if ( empty( $related ) ) : ?>
                    <p>مقاله دیگری ثبت نشده است.</p>
                <?php else : ?>
                    <div class="side-links">
                        <?php foreach ( $related as $rel ) : ?>
                            <a href="<?php echo esc_url( get_permalink( $rel ) ); ?>"><?php echo esc_html( $rel->post_title ); ?></a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </aside>
    </div>
</section>
</main>
<?php get_footer(); ?>
