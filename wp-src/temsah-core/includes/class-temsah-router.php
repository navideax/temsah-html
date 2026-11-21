<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Temsah_Router {
    public static array $public_routes = array(
        'sellers',
        'seller',
        'articles',
        'about',
        'contact',
        'support',
        'terms',
        'become-seller',
        'cart',
        'login',
        'register',
        'lost-password',
        'payment-callback',
    );

    public static array $account_routes = array(
        'account',
        'account/downloads',
        'account/orders',
        'account/order-detail',
        'account/licenses',
        'account/wishlist',
        'account/customer-club',
        'account/tickets',
        'account/ticket-new',
        'account/ticket-view',
        'account/announcements',
        'account/settings',
        'account/seller-application',
        'account/seller-application-status',
    );

    public static array $vendor_routes = array(
        'vendor',
        'vendor/products',
        'vendor/product-drafts',
        'vendor/new-product',
        'vendor/submit-product',
        'vendor/edit-product',
        'vendor/license',
        'vendor/finance',
        'vendor/earnings',
        'vendor/withdrawals',
        'vendor/support-report',
        'vendor/tickets',
        'vendor/ticket-view',
        'vendor/questions',
        'vendor/reviews',
        'vendor/ready-replies',
        'vendor/announcements',
        'vendor/support-announcements',
        'vendor/profile',
        'vendor/store-settings',
        'vendor/verify',
    );

    public static function init(): void {
        self::register_rewrites();
        add_filter( 'query_vars', array( __CLASS__, 'register_query_vars' ) );
        add_action( 'template_redirect', array( __CLASS__, 'enforce_access_control' ), 2 );
        add_action( 'wp_head', array( __CLASS__, 'output_robots_meta' ), 1 );
    }

    public static function register_query_vars( array $vars ): array {
        $vars[] = 'temsah_route';
        $vars[] = 'temsah_slug';
        return $vars;
    }

    public static function register_rewrites(): void {
        add_rewrite_rule( '^shop/?$', 'index.php?post_type=temsah_product', 'top' );
        add_rewrite_rule( '^sellers/?$', 'index.php?temsah_route=sellers', 'top' );
        add_rewrite_rule( '^seller/([^/]+)/?$', 'index.php?temsah_route=seller&temsah_slug=$matches[1]', 'top' );
        add_rewrite_rule( '^articles/?$', 'index.php?temsah_route=articles', 'top' );
        add_rewrite_rule( '^about/?$', 'index.php?temsah_route=about', 'top' );
        add_rewrite_rule( '^contact/?$', 'index.php?temsah_route=contact', 'top' );
        add_rewrite_rule( '^support/?$', 'index.php?temsah_route=support', 'top' );
        add_rewrite_rule( '^terms/?$', 'index.php?temsah_route=terms', 'top' );
        add_rewrite_rule( '^become-seller/?$', 'index.php?temsah_route=become-seller', 'top' );
        add_rewrite_rule( '^cart/?$', 'index.php?temsah_route=cart', 'top' );
        add_rewrite_rule( '^checkout/?$', 'index.php?temsah_route=checkout', 'top' );
        add_rewrite_rule( '^login/?$', 'index.php?temsah_route=login', 'top' );
        add_rewrite_rule( '^register/?$', 'index.php?temsah_route=register', 'top' );
        add_rewrite_rule( '^lost-password/?$', 'index.php?temsah_route=lost-password', 'top' );
        add_rewrite_rule( '^temsah-payment-callback/?$', 'index.php?temsah_route=payment-callback', 'top' );

        add_rewrite_rule( '^account/?$', 'index.php?temsah_route=account', 'top' );
        add_rewrite_rule( '^account/([^/]+)/?$', 'index.php?temsah_route=account/$matches[1]', 'top' );

        add_rewrite_rule( '^vendor/?$', 'index.php?temsah_route=vendor', 'top' );
        add_rewrite_rule( '^vendor/([^/]+)/?$', 'index.php?temsah_route=vendor/$matches[1]', 'top' );
    }

    public static function current_route(): string {
        $route = get_query_var( 'temsah_route', '' );
        if ( ! $route && isset( $_GET['temsah_route'] ) ) {
            $route = sanitize_text_field( wp_unslash( $_GET['temsah_route'] ) );
        }
        return trim( (string) $route, '/' );
    }

    public static function is_private_route( string $route ): bool {
        if ( 'checkout' === $route || 'payment-callback' === $route ) {
            return true;
        }
        if ( str_starts_with( $route, 'account' ) || str_starts_with( $route, 'vendor' ) ) {
            return true;
        }
        return false;
    }

    public static function output_robots_meta(): void {
        $route = self::current_route();
        if ( self::is_private_route( $route ) || in_array( $route, array( 'cart', 'login', 'register', 'lost-password' ), true ) ) {
            echo '<meta name="robots" content="noindex,nofollow">' . "\n";
        }
    }

    public static function enforce_access_control(): void {
        $route = self::current_route();
        if ( '' === $route ) {
            return;
        }

        // Auth pages redirect logged-in users to account
        if ( in_array( $route, array( 'login', 'register', 'lost-password' ), true ) && is_user_logged_in() ) {
            wp_safe_redirect( self::url( 'account' ) );
            exit;
        }

        if ( ! self::is_private_route( $route ) ) {
            return;
        }

        Temsah_Security::enforce_private_headers();

        // Guest gate
        if ( ! is_user_logged_in() ) {
            Temsah_Security::redirect_to_login();
        }

        // Vendor portal gate (requires approved vendor status + capability OR marketplace admin)
        if ( str_starts_with( $route, 'vendor' ) ) {
            if ( ! Temsah_Security::is_approved_vendor() ) {
                Temsah_Security::log_activity( 'unapproved_vendor_access_blocked', 'route', null, array( 'route' => $route ) );
                status_header( 403 );
                wp_die(
                    'دسترسی به پنل فروشندگان فقط پس از ثبت و تأیید حساب فروشندگی امکان‌پذیر است. <br><br><a href="' . esc_url( self::url( 'account/seller-application-status' ) ) . '">مشاهده وضعیت درخواست فروشندگی</a>',
                    'دسترسی محدود به فروشندگان تأییدشده',
                    array( 'response' => 403 )
                );
            }

            // IDOR guard on vendor single product edit
            if ( 'vendor/edit-product' === $route && ! empty( $_GET['id'] ) ) {
                $pid = absint( $_GET['id'] );
                if ( ! Temsah_Security::user_owns_product( $pid ) ) {
                    status_header( 403 );
                    wp_die( 'شما مجوز مشاهده یا ویرایش این محصول را ندارید.', 'خطای دسترسی (۴۰۳)', array( 'response' => 403 ) );
                }
            }

            // IDOR guard on vendor single ticket view
            if ( 'vendor/ticket-view' === $route && ! empty( $_GET['id'] ) ) {
                $tid = absint( $_GET['id'] );
                if ( ! Temsah_Security::user_can_access_ticket( $tid, 'vendor' ) ) {
                    status_header( 403 );
                    wp_die( 'شما مجوز مشاهده این تیکت را ندارید.', 'خطای دسترسی (۴۰۳)', array( 'response' => 403 ) );
                }
            }
        }

        // IDOR guard on buyer order-detail and ticket-view
        if ( 'account/order-detail' === $route && ! empty( $_GET['id'] ) ) {
            $oid = absint( $_GET['id'] );
            if ( ! Temsah_Security::user_owns_order( $oid ) ) {
                status_header( 403 );
                wp_die( 'شما مجوز مشاهده این سفارش را ندارید.', 'خطای دسترسی (۴۰۳)', array( 'response' => 403 ) );
            }
        }

        if ( 'account/ticket-view' === $route && ! empty( $_GET['id'] ) ) {
            $tid = absint( $_GET['id'] );
            if ( ! Temsah_Security::user_can_access_ticket( $tid, 'buyer' ) ) {
                status_header( 403 );
                wp_die( 'شما مجوز مشاهده این تیکت را ندارید.', 'خطای دسترسی (۴۰۳)', array( 'response' => 403 ) );
            }
        }
    }

    public static function url( string $route, array $args = array() ): string {
        $route = trim( $route, '/' );
        if ( '' === $route || 'home' === $route ) {
            $base = home_url( '/' );
        } elseif ( 'shop' === $route ) {
            $base = get_option( 'permalink_structure' ) ? home_url( '/shop/' ) : add_query_arg( 'post_type', 'temsah_product', home_url( '/' ) );
        } elseif ( get_option( 'permalink_structure' ) ) {
            $base = home_url( '/' . $route . '/' );
        } else {
            $base = add_query_arg( 'temsah_route', $route, home_url( '/' ) );
        }
        return ! empty( $args ) ? add_query_arg( $args, $base ) : $base;
    }
}

function temsah_url( string $route, array $args = array() ): string {
    return Temsah_Router::url( $route, $args );
}
