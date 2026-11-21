<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Temsah_REST {
    public static function init(): void {
        add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
    }

    public static function register_routes(): void {
        $ns = 'temsah/v1';

        // Public catalog & live search (read-only)
        register_rest_route( $ns, '/search', array(
            'methods'             => 'GET',
            'callback'            => array( __CLASS__, 'search_products' ),
            'permission_callback' => '__return_true',
        ) );

        // Cart endpoints (public guest token or logged-in user; protected by WP REST nonce)
        register_rest_route( $ns, '/cart', array(
            'methods'             => 'GET',
            'callback'            => array( __CLASS__, 'get_cart' ),
            'permission_callback' => '__return_true',
        ) );

        register_rest_route( $ns, '/cart/add', array(
            'methods'             => 'POST',
            'callback'            => array( __CLASS__, 'cart_add' ),
            'permission_callback' => array( __CLASS__, 'verify_rest_nonce' ),
        ) );

        register_rest_route( $ns, '/cart/remove', array(
            'methods'             => 'POST',
            'callback'            => array( __CLASS__, 'cart_remove' ),
            'permission_callback' => array( __CLASS__, 'verify_rest_nonce' ),
        ) );

        register_rest_route( $ns, '/cart/services', array(
            'methods'             => 'POST',
            'callback'            => array( __CLASS__, 'cart_services' ),
            'permission_callback' => array( __CLASS__, 'verify_rest_nonce' ),
        ) );

        register_rest_route( $ns, '/cart/coupon', array(
            'methods'             => 'POST',
            'callback'            => array( __CLASS__, 'cart_coupon' ),
            'permission_callback' => array( __CLASS__, 'verify_rest_nonce' ),
        ) );

        // Authenticated buyer wishlist
        register_rest_route( $ns, '/wishlist/toggle', array(
            'methods'             => 'POST',
            'callback'            => array( __CLASS__, 'wishlist_toggle' ),
            'permission_callback' => array( __CLASS__, 'require_logged_in_nonce' ),
        ) );

        // Authenticated buyer orders (strict ownership)
        register_rest_route( $ns, '/account/orders', array(
            'methods'             => 'GET',
            'callback'            => array( __CLASS__, 'account_orders' ),
            'permission_callback' => array( __CLASS__, 'require_logged_in_nonce' ),
        ) );

        // Approved vendor stats (strict approved vendor check)
        register_rest_route( $ns, '/vendor/stats', array(
            'methods'             => 'GET',
            'callback'            => array( __CLASS__, 'vendor_stats' ),
            'permission_callback' => array( __CLASS__, 'require_approved_vendor' ),
        ) );
    }

    public static function verify_rest_nonce( WP_REST_Request $req ): bool|WP_Error {
        $nonce = $req->get_header( 'X-WP-Nonce' ) ?: ( $req->get_param( '_wpnonce' ) ?? '' );
        if ( ! wp_verify_nonce( (string) $nonce, 'wp_rest' ) ) {
            return new WP_Error( 'rest_forbidden_nonce', 'توکن امنیتی REST نامعتبر است.', array( 'status' => 403 ) );
        }
        return true;
    }

    public static function require_logged_in_nonce( WP_REST_Request $req ): bool|WP_Error {
        if ( ! is_user_logged_in() ) {
            return new WP_Error( 'rest_unauthorized', 'نیاز به ورود به حساب کاربری است.', array( 'status' => 401 ) );
        }
        return self::verify_rest_nonce( $req );
    }

    public static function require_approved_vendor( WP_REST_Request $req ): bool|WP_Error {
        $check = self::require_logged_in_nonce( $req );
        if ( is_wp_error( $check ) ) {
            return $check;
        }
        if ( ! Temsah_Security::is_approved_vendor() ) {
            return new WP_Error( 'rest_vendor_forbidden', 'دسترسی فقط برای فروشندگان تأییدشده مجاز است.', array( 'status' => 403 ) );
        }
        return true;
    }

    public static function search_products( WP_REST_Request $req ): WP_REST_Response {
        $q     = sanitize_text_field( (string) $req->get_param( 'q' ) );
        $cat   = sanitize_title( (string) $req->get_param( 'category' ) );
        $items = Temsah_CPT::query_products( array(
            'search'   => $q,
            'category' => $cat,
            'limit'    => 12,
        ) );

        $data = array();
        foreach ( $items as $p ) {
            $eff    = Temsah_CPT::effective_price( $p );
            $data[] = array(
                'id'            => (int) $p->id,
                'title'         => $p->post_title,
                'url'           => get_permalink( (int) $p->post_id ),
                'category'      => Temsah_CPT::get_product_category_name( (int) $p->post_id ),
                'seller'        => $p->store_name ?: 'فروشنده',
                'price'         => $eff,
                'price_text'    => temsah_format_toman( $eff ),
                'original_text' => ( $eff < (int) $p->price ) ? temsah_format_toman( $p->price ) : '',
                'cover_style'   => $p->cover_style ?: 'cover-store',
                'rating'        => temsah_fa_digits( number_format( (float) $p->rating_avg, 1 ) ),
                'sales'         => temsah_format_number( $p->sales_count ),
            );
        }
        return new WP_REST_Response( array( 'items' => $data ), 200 );
    }

    public static function get_cart(): WP_REST_Response {
        return new WP_REST_Response( self::serialize_cart( Temsah_Cart::get_summary() ), 200 );
    }

    public static function cart_add( WP_REST_Request $req ): WP_REST_Response {
        $pid      = absint( $req->get_param( 'product_id' ) );
        $services = (array) ( $req->get_param( 'services' ) ?? array() );
        $res      = Temsah_Cart::add_item( $pid, $services );
        if ( isset( $res['summary'] ) ) {
            $res['summary'] = self::serialize_cart( $res['summary'] );
        }
        return new WP_REST_Response( $res, $res['ok'] ? 200 : 400 );
    }

    public static function cart_remove( WP_REST_Request $req ): WP_REST_Response {
        $pid = absint( $req->get_param( 'product_id' ) );
        $res = Temsah_Cart::remove_item( $pid );
        $res['summary'] = self::serialize_cart( $res['summary'] );
        return new WP_REST_Response( $res, 200 );
    }

    public static function cart_services( WP_REST_Request $req ): WP_REST_Response {
        $pid      = absint( $req->get_param( 'product_id' ) );
        $services = (array) ( $req->get_param( 'services' ) ?? array() );
        $res      = Temsah_Cart::update_services( $pid, $services );
        if ( isset( $res['summary'] ) ) {
            $res['summary'] = self::serialize_cart( $res['summary'] );
        }
        return new WP_REST_Response( $res, $res['ok'] ? 200 : 400 );
    }

    public static function cart_coupon( WP_REST_Request $req ): WP_REST_Response {
        $code = (string) ( $req->get_param( 'code' ) ?? '' );
        $res  = Temsah_Cart::apply_coupon( $code );
        if ( isset( $res['summary'] ) ) {
            $res['summary'] = self::serialize_cart( $res['summary'] );
        }
        return new WP_REST_Response( $res, 200 );
    }

    public static function wishlist_toggle( WP_REST_Request $req ): WP_REST_Response {
        $pid = absint( $req->get_param( 'product_id' ) );
        $res = Temsah_Buyer::toggle_wishlist( get_current_user_id(), $pid );
        return new WP_REST_Response( $res, 200 );
    }

    public static function account_orders(): WP_REST_Response {
        global $wpdb;
        $uid  = get_current_user_id();
        $rows = $wpdb->get_results(
            $wpdb->prepare(
                'SELECT id, order_number, total, status, created_at FROM ' . Temsah_DB::table( 'orders' ) . ' WHERE user_id = %d ORDER BY id DESC LIMIT 50',
                $uid
            )
        );
        return new WP_REST_Response( array( 'orders' => $rows ), 200 );
    }

    public static function vendor_stats(): WP_REST_Response {
        $vendor = Temsah_Security::get_vendor_by_user( get_current_user_id() );
        if ( ! $vendor ) {
            return new WP_REST_Response( array( 'vendor' => null ), 200 );
        }
        return new WP_REST_Response( array(
            'store_name'        => $vendor->store_name,
            'total_sales'       => (int) $vendor->total_sales,
            'total_earnings'    => (int) $vendor->total_earnings,
            'available_balance' => (int) $vendor->available_balance,
        ), 200 );
    }

    private static function serialize_cart( array $summary ): array {
        $items = array();
        foreach ( $summary['items'] as $row ) {
            $p       = $row['product'];
            $items[] = array(
                'product_id'        => (int) $p->id,
                'title'             => $p->post_title,
                'url'               => get_permalink( (int) $p->post_id ),
                'category'          => Temsah_CPT::get_product_category_name( (int) $p->post_id ),
                'seller'            => $p->store_name ?: 'فروشنده',
                'cover_style'       => $p->cover_style ?: 'cover-store',
                'original_price'    => $row['original_price'],
                'effective_price'   => $row['effective_price'],
                'services_price'    => $row['services_price'],
                'line_total'        => $row['line_total'],
                'selected_services' => $row['selected_services'],
                'line_total_text'   => temsah_format_toman( $row['line_total'] ),
            );
        }
        return array(
            'count'                 => $summary['count'],
            'items'                 => $items,
            'subtotal'              => $summary['subtotal'],
            'subtotal_text'         => temsah_format_toman( $summary['subtotal'] ),
            'product_discount'      => $summary['product_discount'],
            'product_discount_text' => temsah_format_toman( $summary['product_discount'] ),
            'services_total'        => $summary['services_total'],
            'services_total_text'   => temsah_format_toman( $summary['services_total'] ),
            'coupon_code'           => $summary['coupon_code'],
            'coupon_discount'       => $summary['coupon_discount'],
            'coupon_discount_text'  => temsah_format_toman( $summary['coupon_discount'] ),
            'total'                 => $summary['total'],
            'total_text'            => temsah_format_toman( $summary['total'] ),
        );
    }
}
