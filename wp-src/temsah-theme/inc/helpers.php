<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function temsah_asset( string $path ): string {
    return esc_url( get_template_directory_uri() . '/assets/' . ltrim( $path, '/' ) );
}

function temsah_ico( string $file, string $class = 'ico' ): string {
    return '<img alt="" class="' . esc_attr( $class ) . '" src="' . temsah_asset( 'icons/' . ltrim( $file, '/' ) ) . '"/>';
}

function temsah_theme_opt( string $key, $default = '' ) {
    $opts = get_option( 'temsah_theme_options', array() );
    return isset( $opts[ $key ] ) && '' !== $opts[ $key ] ? $opts[ $key ] : $default;
}

function temsah_logo_html( string $href = '', string $extra_class = '' ): string {
    $url     = $href ?: home_url( '/' );
    $logo_id = absint( temsah_theme_opt( 'custom_logo_id', 0 ) );
    $img_src = $logo_id ? wp_get_attachment_image_url( $logo_id, 'full' ) : temsah_asset( 'brand/temsah-tm.png' );
    $title   = temsah_theme_opt( 'brand_title_fa', 'تمساح' );
    $sub     = temsah_theme_opt( 'brand_title_en', 'TEMSAH' );
    return '<a aria-label="بازگشت به سایت تمساح" class="logo ' . esc_attr( $extra_class ) . '" href="' . esc_url( $url ) . '"><span class="logo-symbol tm-brand-icon"><img alt="" loading="eager" src="' . esc_url( $img_src ) . '"/></span><span class="logo-name">' . esc_html( $title ) . ' <em>' . esc_html( $sub ) . '</em></span></a>';
}

/**
 * Render exact V31 CSS cover illustration art (plus WP featured thumbnail if uploaded).
 */
function temsah_cover_html( object $product, bool $with_label = true ): string {
    $cat   = Temsah_CPT::get_product_category_name( (int) $product->post_id );
    $style = sanitize_html_class( $product->cover_style ?: 'cover-store' );
    $thumb = get_the_post_thumbnail_url( (int) $product->post_id, 'large' );

    ob_start();
    if ( $thumb ) {
        ?>
        <div class="cover <?php echo esc_attr( $style ); ?>" style="padding:0;overflow:hidden;">
            <img src="<?php echo esc_url( $thumb ); ?>" alt="<?php echo esc_attr( $product->post_title ); ?>" style="width:100%;height:100%;object-fit:cover;display:block;">
            <?php if ( $with_label ) : ?><div class="cover-label"><?php echo esc_html( $cat ); ?></div><?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    ?>
    <div class="cover <?php echo esc_attr( $style ); ?>">
        <div class="cover-window">
            <div class="mini-top"><i></i><i></i><i></i></div>
            <?php if ( in_array( $style, array( 'cover-store', 'cover-fashion', 'cover-agency' ), true ) ) : ?>
                <div class="art-shop">
                    <div class="art-side"><span>NEW COLLECTION</span><strong><?php echo esc_html( 'cover-fashion' === $style ? 'استایل متفاوت' : 'فروشگاه مدرن' ); ?></strong><i>SHOP NOW</i></div>
                    <div class="art-items"><div class="item-blob a"></div><div class="item-blob b"></div></div>
                </div>
            <?php elseif ( in_array( $style, array( 'cover-forms', 'cover-menu' ), true ) ) : ?>
                <div class="art-form"><div class="form-title"></div><div class="form-row"></div><div class="form-row short"></div><div class="form-cols"><i></i><i></i></div><div class="form-button"></div></div>
            <?php elseif ( 'cover-news' === $style ) : ?>
                <div class="art-news"><div class="newsa"></div><div class="newsb"><span></span><span></span><span></span></div></div>
            <?php elseif ( in_array( $style, array( 'cover-ui', 'cover-mobile' ), true ) ) : ?>
                <div class="art-ui"><div class="ui-top"></div><div class="ui-cards"><i></i><i></i></div><div class="ui-bar"></div></div>
            <?php else : ?>
                <div class="art-dashboard"><aside><i></i><i></i><i></i><i></i><i></i></aside><section><div class="dash-heading"></div><div class="dash-boxes"><i></i><i></i><i></i></div><div class="dash-chart"><b></b><b></b><b></b><b></b><b></b><b></b><b></b></div></section></div>
            <?php endif; ?>
        </div>
        <?php if ( $with_label ) : ?><div class="cover-label"><?php echo esc_html( $cat ); ?></div><?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}

function temsah_medal_definitions(): array {
    return array(
        'p-popular'  => array( 'class' => 'pink',  'icon' => 'buy.svg',         'title' => 'پرفروش',   'label' => 'محصول پرفروش' ),
        'p-rated'    => array( 'class' => 'gold',  'icon' => 'star.svg',        'title' => 'رضایت',    'label' => 'رضایت خریداران' ),
        'p-updated'  => array( 'class' => 'blue',  'icon' => 'download.svg',    'title' => 'بروزرسانی', 'label' => 'به‌روزرسانی فعال' ),
        'p-quality'  => array( 'class' => 'green', 'icon' => 'shield-done.svg', 'title' => 'کیفیت',    'label' => 'تأیید کیفیت' ),
        'v-verified' => array( 'class' => 'green', 'icon' => 'shield-done.svg', 'title' => 'احراز هویت', 'label' => 'فروشنده احرازشده' ),
        'v-top'      => array( 'class' => 'gold',  'icon' => 'star.svg',        'title' => 'برتر',     'label' => 'فروشنده برتر' ),
        'v-fast'     => array( 'class' => 'blue',  'icon' => 'chat.svg',        'title' => 'پاسخ‌گو',   'label' => 'پاسخ‌گویی سریع' ),
        'v-active'   => array( 'class' => 'pink',  'icon' => 'work.svg',        'title' => 'فعال',     'label' => 'فروشگاه فعال' ),
    );
}

function temsah_render_medals( array $keys, string $type = 'product', string $name = '', bool $small = true ): string {
    if ( empty( $keys ) ) {
        return '';
    }
    $defs = temsah_medal_definitions();
    ob_start();
    ?>
    <div class="tm-medal-region compact" data-tm-name="<?php echo esc_attr( $name ); ?>" data-tm-type="<?php echo esc_attr( $type ); ?>">
        <div class="tm-medals">
            <?php foreach ( $keys as $k ) :
                if ( ! isset( $defs[ $k ] ) ) { continue; }
                $d = $defs[ $k ];
            ?>
            <button aria-label="<?php echo esc_attr( $d['label'] ); ?>" class="tm-medal <?php echo esc_attr( $d['class'] ); ?><?php echo $small ? ' is-small' : ''; ?>" data-tm-kind="<?php echo esc_attr( $type ); ?>" data-tm-medal="<?php echo esc_attr( $k ); ?>" title="<?php echo esc_attr( $d['label'] ); ?>" type="button">
                <span class="tm-medal-body"><?php echo temsah_ico( $d['icon'], '' ); ?></span>
                <span class="tm-medal-legs"></span>
                <span class="tm-medal-title"><?php echo esc_html( $d['title'] ); ?></span>
            </button>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function temsah_current_route_context(): string {
    if ( class_exists( 'Temsah_Router' ) ) {
        $r = Temsah_Router::current_route();
        if ( '' !== $r ) {
            return $r;
        }
    }
    if ( is_front_page() ) {
        return 'home';
    }
    if ( is_singular( 'temsah_product' ) ) {
        return 'product';
    }
    if ( is_post_type_archive( 'temsah_product' ) || is_tax( 'temsah_product_cat' ) || is_tax( 'temsah_product_tag' ) ) {
        return 'shop';
    }
    if ( is_home() || is_singular( 'post' ) || is_category() || is_archive() ) {
        return 'articles';
    }
    return '';
}
