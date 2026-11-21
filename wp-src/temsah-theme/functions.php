<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'TEMSAH_THEME_VERSION', '1.0.0' );

require_once get_template_directory() . '/inc/helpers.php';
require_once get_template_directory() . '/inc/theme-options.php';

add_action( 'after_setup_theme', function (): void {
    load_theme_textdomain( 'temsah-theme', get_template_directory() . '/languages' );
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
    register_nav_menus( array(
        'primary'      => 'منوی اصلی بالای سایت',
        'footer_quick' => 'فوتر - دسترسی سریع',
        'footer_help'  => 'فوتر - راهنما',
        'footer_maker' => 'فوتر - تمساح برای سازنده‌ها',
    ) );
} );

add_action( 'wp_enqueue_scripts', function (): void {
    $uri = get_template_directory_uri();
    $ver = TEMSAH_THEME_VERSION;
    $ctx = temsah_current_route_context();

    wp_enqueue_style( 'temsah-style', get_stylesheet_uri(), array(), $ver );
    wp_enqueue_style( 'temsah-brand', $uri . '/assets/css/brand.css', array( 'temsah-style' ), $ver );
    wp_enqueue_style( 'temsah-cards', $uri . '/assets/css/square-cards-v27.css', array( 'temsah-style' ), $ver );
    wp_enqueue_style( 'temsah-v29', $uri . '/assets/css/v29-ui.css', array( 'temsah-style' ), $ver );
    wp_enqueue_style( 'temsah-v30', $uri . '/assets/css/v30-static.css', array( 'temsah-style' ), $ver );

    if ( 'home' === $ctx ) {
        wp_enqueue_style( 'temsah-home-sliders', $uri . '/assets/css/home-sliders.css', array( 'temsah-v29' ), $ver );
        wp_enqueue_script( 'temsah-home-sliders-js', $uri . '/assets/home-sliders.js', array(), $ver, true );
    }

    if ( in_array( $ctx, array( 'home', 'shop', 'product', 'sellers', 'seller' ), true ) ) {
        wp_enqueue_style( 'temsah-medals', $uri . '/assets/css/medals.css', array( 'temsah-v29' ), $ver );
    }

    if ( 'product' === $ctx ) {
        wp_enqueue_style( 'temsah-prod-gallery', $uri . '/assets/css/product-gallery-v24.css', array( 'temsah-v29' ), $ver );
        wp_enqueue_style( 'temsah-prod-layout', $uri . '/assets/css/product-layout-v26.css', array( 'temsah-prod-gallery' ), $ver );
    }

    if ( in_array( $ctx, array( 'account', 'account/wishlist', 'account/seller-application', 'account/seller-application-status', 'become-seller' ), true ) ) {
        wp_enqueue_style( 'temsah-onboarding', $uri . '/assets/css/onboarding.css', array( 'temsah-v30' ), $ver );
    }

    wp_enqueue_script( 'temsah-ui', $uri . '/assets/theme-ui.js', array(), $ver, true );

    $has_pretty = (bool) get_option( 'permalink_structure' );
    wp_localize_script( 'temsah-ui', 'TEMSAH_UI', array(
        'restBase'      => $has_pretty ? rest_url( 'temsah/v1/' ) : add_query_arg( 'rest_route', '/', home_url( '/' ) ),
        'restRouteMode' => ! $has_pretty,
        'nonce'         => wp_create_nonce( 'wp_rest' ),
        'isLoggedIn'    => is_user_logged_in(),
        'cartUrl'       => function_exists( 'temsah_url' ) ? temsah_url( 'cart' ) : home_url( '/cart/' ),
        'loginUrl'      => function_exists( 'temsah_url' ) ? temsah_url( 'login' ) : home_url( '/login/' ),
    ) );
} );

add_filter( 'body_class', function ( array $classes ): array {
    $ctx = temsah_current_route_context();
    if ( str_starts_with( $ctx, 'account' ) ) {
        $classes[] = 'is-portal';
    } elseif ( str_starts_with( $ctx, 'vendor' ) ) {
        $classes[] = 'is-portal';
        $classes[] = 'is-vendor-portal';
    } elseif ( in_array( $ctx, array( 'login', 'register', 'lost-password' ), true ) ) {
        $classes[] = 'is-auth';
    }
    return $classes;
} );

add_filter( 'template_include', function ( string $template ): string {
    if ( ! class_exists( 'Temsah_Router' ) ) {
        return $template;
    }
    $route = Temsah_Router::current_route();
    if ( '' === $route ) {
        return $template;
    }

    $map = array(
        'sellers'                          => 'templates/public/sellers.php',
        'seller'                           => 'templates/public/seller.php',
        'articles'                         => 'templates/public/articles.php',
        'about'                            => 'templates/public/about.php',
        'contact'                          => 'templates/public/contact.php',
        'support'                          => 'templates/public/support.php',
        'terms'                            => 'templates/public/terms.php',
        'become-seller'                    => 'templates/public/become-seller.php',
        'cart'                             => 'templates/shop/cart.php',
        'checkout'                         => 'templates/shop/checkout.php',
        'payment-callback'                 => 'templates/shop/payment-callback.php',
        'login'                            => 'templates/auth/login.php',
        'register'                         => 'templates/auth/register.php',
        'lost-password'                    => 'templates/auth/lost-password.php',
        'account'                          => 'templates/account/dashboard.php',
        'account/downloads'                => 'templates/account/downloads.php',
        'account/orders'                   => 'templates/account/orders.php',
        'account/order-detail'             => 'templates/account/order-detail.php',
        'account/licenses'                 => 'templates/account/licenses.php',
        'account/wishlist'                 => 'templates/account/wishlist.php',
        'account/customer-club'            => 'templates/account/customer-club.php',
        'account/tickets'                  => 'templates/account/tickets.php',
        'account/ticket-new'               => 'templates/account/ticket-new.php',
        'account/ticket-view'              => 'templates/account/ticket-view.php',
        'account/announcements'            => 'templates/account/announcements.php',
        'account/settings'                 => 'templates/account/settings.php',
        'account/seller-application'       => 'templates/account/seller-application.php',
        'account/seller-application-status'=> 'templates/account/seller-application-status.php',
        'vendor'                           => 'templates/vendor/dashboard.php',
        'vendor/products'                  => 'templates/vendor/products.php',
        'vendor/product-drafts'            => 'templates/vendor/product-drafts.php',
        'vendor/new-product'               => 'templates/vendor/product-form.php',
        'vendor/submit-product'            => 'templates/vendor/product-form.php',
        'vendor/edit-product'              => 'templates/vendor/product-form.php',
        'vendor/license'                   => 'templates/vendor/license.php',
        'vendor/finance'                   => 'templates/vendor/finance.php',
        'vendor/earnings'                  => 'templates/vendor/earnings.php',
        'vendor/withdrawals'               => 'templates/vendor/withdrawals.php',
        'vendor/support-report'            => 'templates/vendor/support-report.php',
        'vendor/tickets'                   => 'templates/vendor/tickets.php',
        'vendor/ticket-view'               => 'templates/vendor/ticket-view.php',
        'vendor/questions'                 => 'templates/vendor/questions.php',
        'vendor/reviews'                   => 'templates/vendor/reviews.php',
        'vendor/ready-replies'             => 'templates/vendor/ready-replies.php',
        'vendor/announcements'             => 'templates/vendor/announcements.php',
        'vendor/support-announcements'     => 'templates/vendor/announcements.php',
        'vendor/profile'                   => 'templates/vendor/profile.php',
        'vendor/store-settings'            => 'templates/vendor/store-settings.php',
        'vendor/verify'                    => 'templates/vendor/verify.php',
    );

    if ( isset( $map[ $route ] ) ) {
        $file = get_template_directory() . '/' . $map[ $route ];
        if ( file_exists( $file ) ) {
            return $file;
        }
    }
    return get_404_template();
} );

// SEO & Schema.org output (only on public indexable routes)
add_action( 'wp_head', function (): void {
    $ctx = temsah_current_route_context();
    if ( class_exists( 'Temsah_Router' ) && Temsah_Router::is_private_route( $ctx ) ) {
        return;
    }
    $desc = temsah_theme_opt( 'hero_subtitle', get_bloginfo( 'description' ) );
    if ( is_singular( 'temsah_product' ) ) {
        $p = class_exists( 'Temsah_CPT' ) ? Temsah_CPT::get_product_by_post( get_the_ID() ) : null;
        if ( $p ) {
            $desc = wp_strip_all_tags( $p->post_excerpt ?: $p->post_content );
            $schema = array(
                '@context'    => 'https://schema.org',
                '@type'       => 'Product',
                'name'        => $p->post_title,
                'description' => wp_trim_words( $desc, 35 ),
                'offers'      => array(
                    '@type'         => 'Offer',
                    'priceCurrency' => 'IRR',
                    'price'         => Temsah_CPT::effective_price( $p ) * 10,
                    'availability'  => 'https://schema.org/InStock',
                ),
            );
            echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
        }
    } elseif ( is_singular( 'post' ) ) {
        $desc = get_the_excerpt();
        $schema = array(
            '@context'      => 'https://schema.org',
            '@type'         => 'Article',
            'headline'      => get_the_title(),
            'datePublished' => get_the_date( 'c' ),
            'author'        => array( '@type' => 'Person', 'name' => get_the_author() ),
        );
        echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
    }
    echo '<meta name="description" content="' . esc_attr( wp_trim_words( (string) $desc, 30 ) ) . '">' . "\n";
}, 5 );
