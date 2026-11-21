<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Temsah_Cart {
    public const COOKIE_NAME = 'temsah_cart_token';

    /**
     * Whitelisted optional add-on services calculated strictly server-side.
     */
    public static function available_services(): array {
        return array(
            'support'  => array(
                'id'    => 'support',
                'label' => '۶ ماه پشتیبانی بیشتر',
                'price' => 215000,
            ),
            'install'  => array(
                'id'    => 'install',
                'label' => 'نصب و راه‌اندازی اولیه',
                'price' => 390000,
            ),
            'seo'      => array(
                'id'    => 'seo',
                'label' => 'بررسی تنظیمات اولیه و بهینه‌سازی',
                'price' => 265000,
            ),
            'training' => array(
                'id'    => 'training',
                'label' => 'آموزش ویدیویی نصب و استفاده',
                'price' => 95000,
            ),
        );
    }

    public static function init(): void {
        add_action( 'wp_login', array( __CLASS__, 'merge_guest_cart_on_login' ), 10, 2 );
    }

    public static function get_session_token( bool $create = false ): string {
        $token = isset( $_COOKIE[ self::COOKIE_NAME ] ) ? sanitize_key( wp_unslash( $_COOKIE[ self::COOKIE_NAME ] ) ) : '';
        if ( strlen( $token ) >= 24 ) {
            return $token;
        }
        if ( ! $create ) {
            return '';
        }
        $token = bin2hex( random_bytes( 16 ) );
        if ( ! headers_sent() ) {
            setcookie( self::COOKIE_NAME, $token, array(
                'expires'  => time() + 30 * DAY_IN_SECONDS,
                'path'     => COOKIEPATH ?: '/',
                'secure'   => is_ssl(),
                'httponly' => true,
                'samesite' => 'Lax',
            ) );
        }
        $_COOKIE[ self::COOKIE_NAME ] = $token;
        return $token;
    }

    public static function get_or_create_cart( bool $create = true ): ?object {
        global $wpdb;
        $t_carts = Temsah_DB::table( 'carts' );
        $uid     = is_user_logged_in() ? get_current_user_id() : null;
        $token   = self::get_session_token( $create );

        $cart = null;
        if ( $uid ) {
            $cart = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$t_carts} WHERE user_id = %d ORDER BY id DESC LIMIT 1", $uid ) );
        }
        if ( ! $cart && $token ) {
            $cart = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$t_carts} WHERE session_token = %s LIMIT 1", $token ) );
            if ( $cart && $uid && empty( $cart->user_id ) ) {
                $wpdb->update( $t_carts, array( 'user_id' => $uid ), array( 'id' => $cart->id ) );
                $cart->user_id = $uid;
            }
        }
        if ( ! $cart && $create ) {
            if ( ! $token ) {
                $token = self::get_session_token( true );
            }
            $now = current_time( 'mysql' );
            $exp = gmdate( 'Y-m-d H:i:s', time() + 30 * DAY_IN_SECONDS );
            $wpdb->insert(
                $t_carts,
                array(
                    'session_token' => $token,
                    'user_id'       => $uid,
                    'coupon_code'   => null,
                    'gateway_id'    => 'sandbox',
                    'updated_at'    => $now,
                    'expires_at'    => $exp,
                ),
                array( '%s', '%d', '%s', '%s', '%s', '%s' )
            );
            $cart = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$t_carts} WHERE id = %d", (int) $wpdb->insert_id ) );
        }
        return $cart ?: null;
    }

    public static function merge_guest_cart_on_login( string $user_login, WP_User $user ): void {
        global $wpdb;
        $token = self::get_session_token( false );
        if ( ! $token ) {
            return;
        }
        $t_carts = Temsah_DB::table( 'carts' );
        $t_items = Temsah_DB::table( 'cart_items' );

        $guest_cart = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$t_carts} WHERE session_token = %s AND (user_id IS NULL OR user_id = 0) LIMIT 1", $token ) );
        if ( ! $guest_cart ) {
            return;
        }
        $user_cart = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$t_carts} WHERE user_id = %d ORDER BY id DESC LIMIT 1", $user->ID ) );
        if ( ! $user_cart ) {
            $wpdb->update( $t_carts, array( 'user_id' => $user->ID ), array( 'id' => $guest_cart->id ) );
            return;
        }
        $items = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$t_items} WHERE cart_id = %d", $guest_cart->id ) );
        foreach ( $items as $item ) {
            $exists = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$t_items} WHERE cart_id = %d AND product_id = %d", $user_cart->id, $item->product_id ) );
            if ( ! $exists ) {
                $wpdb->insert(
                    $t_items,
                    array(
                        'cart_id'       => $user_cart->id,
                        'product_id'    => $item->product_id,
                        'services_json' => $item->services_json,
                        'created_at'    => current_time( 'mysql' ),
                    ),
                    array( '%d', '%d', '%s', '%s' )
                );
            }
        }
        $wpdb->delete( $t_items, array( 'cart_id' => $guest_cart->id ) );
        $wpdb->delete( $t_carts, array( 'id' => $guest_cart->id ) );
    }

    public static function add_item( int $product_id, array $services = array() ): array {
        global $wpdb;
        $product = Temsah_CPT::get_product_by_id( $product_id );
        if ( ! $product || 'published' !== $product->status ) {
            return array( 'ok' => false, 'message' => 'این محصول قابل خرید نیست یا یافت نشد.' );
        }

        $settings = get_option( 'temsah_settings', array() );
        if ( is_user_logged_in() && ! empty( $settings['prevent_duplicate_buy'] ) ) {
            if ( Temsah_Security::user_has_purchased_product( get_current_user_id(), $product_id ) ) {
                return array( 'ok' => false, 'message' => 'شما قبلاً این محصول را خریداری کرده‌اید و فایل آن در بخش دانلودها در دسترس است.' );
            }
        }

        $cart = self::get_or_create_cart( true );
        if ( ! $cart ) {
            return array( 'ok' => false, 'message' => 'خطا در ایجاد سبد خرید.' );
        }

        $valid_services = self::sanitize_services( $services );
        $t_items        = Temsah_DB::table( 'cart_items' );
        $existing       = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$t_items} WHERE cart_id = %d AND product_id = %d LIMIT 1", $cart->id, $product_id ) );

        if ( $existing ) {
            if ( ! empty( $services ) ) {
                $wpdb->update(
                    $t_items,
                    array( 'services_json' => wp_json_encode( $valid_services ) ),
                    array( 'id' => $existing->id )
                );
            }
            return array( 'ok' => true, 'message' => 'محصول در سبد خرید شما به‌روزرسانی شد.', 'summary' => self::get_summary() );
        }

        $wpdb->insert(
            $t_items,
            array(
                'cart_id'       => $cart->id,
                'product_id'    => $product_id,
                'services_json' => wp_json_encode( $valid_services ),
                'created_at'    => current_time( 'mysql' ),
            ),
            array( '%d', '%d', '%s', '%s' )
        );

        return array( 'ok' => true, 'message' => 'به سبد خرید اضافه شد.', 'summary' => self::get_summary() );
    }

    public static function remove_item( int $product_id ): array {
        global $wpdb;
        $cart = self::get_or_create_cart( false );
        if ( $cart ) {
            $wpdb->delete( Temsah_DB::table( 'cart_items' ), array( 'cart_id' => $cart->id, 'product_id' => $product_id ) );
        }
        return array( 'ok' => true, 'message' => 'از سبد خرید حذف شد.', 'summary' => self::get_summary() );
    }

    public static function update_services( int $product_id, array $services ): array {
        global $wpdb;
        $cart = self::get_or_create_cart( false );
        if ( ! $cart ) {
            return array( 'ok' => false, 'message' => 'سبد خرید یافت نشد.' );
        }
        $valid = self::sanitize_services( $services );
        $wpdb->update(
            Temsah_DB::table( 'cart_items' ),
            array( 'services_json' => wp_json_encode( $valid ) ),
            array( 'cart_id' => $cart->id, 'product_id' => $product_id )
        );
        return array( 'ok' => true, 'message' => 'خدمات محصول به‌روزرسانی شد.', 'summary' => self::get_summary() );
    }

    public static function sanitize_services( array $services ): array {
        $allowed = array_keys( self::available_services() );
        $clean   = array();
        foreach ( $services as $sid ) {
            $sid = sanitize_key( (string) $sid );
            if ( in_array( $sid, $allowed, true ) ) {
                $clean[] = $sid;
            }
        }
        return array_values( array_unique( $clean ) );
    }

    public static function apply_coupon( string $code ): array {
        global $wpdb;
        $code = strtoupper( sanitize_text_field( trim( $code ) ) );
        $cart = self::get_or_create_cart( true );
        if ( ! $cart ) {
            return array( 'ok' => false, 'message' => 'سبد خرید یافت نشد.' );
        }
        if ( '' === $code ) {
            $wpdb->update( Temsah_DB::table( 'carts' ), array( 'coupon_code' => null ), array( 'id' => $cart->id ) );
            return array( 'ok' => true, 'message' => 'کد تخفیف حذف شد.', 'summary' => self::get_summary() );
        }

        $coupon = self::validate_coupon_for_cart( $code, self::get_raw_subtotal( $cart->id ) );
        if ( is_wp_error( $coupon ) ) {
            return array( 'ok' => false, 'message' => $coupon->get_error_message(), 'summary' => self::get_summary() );
        }
        $wpdb->update( Temsah_DB::table( 'carts' ), array( 'coupon_code' => $coupon->code ), array( 'id' => $cart->id ) );
        return array( 'ok' => true, 'message' => 'کد تخفیف با موفقیت اعمال شد.', 'summary' => self::get_summary() );
    }

    public static function validate_coupon_for_cart( string $code, int $eligible_amount ): object {
        global $wpdb;
        $t_coupons = Temsah_DB::table( 'coupons' );
        $coupon    = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$t_coupons} WHERE UPPER(code) = UPPER(%s) LIMIT 1", $code ) );
        if ( ! $coupon || ! (int) $coupon->is_active ) {
            return new WP_Error( 'invalid_coupon', 'کد تخفیف وارد شده معتبر نیست.' );
        }
        $now = current_time( 'mysql' );
        if ( ! empty( $coupon->starts_at ) && $now < $coupon->starts_at ) {
            return new WP_Error( 'not_started', 'زمان استفاده از این کد تخفیف هنوز آغاز نشده است.' );
        }
        if ( ! empty( $coupon->expires_at ) && $now > $coupon->expires_at ) {
            return new WP_Error( 'expired', 'مهلت استفاده از این کد تخفیف به پایان رسیده است.' );
        }
        if ( null !== $coupon->usage_limit && (int) $coupon->usage_count >= (int) $coupon->usage_limit ) {
            return new WP_Error( 'limit_reached', 'ظرفیت استفاده از این کد تخفیف تکمیل شده است.' );
        }
        if ( $eligible_amount < (int) $coupon->min_order ) {
            return new WP_Error( 'min_order', 'حداقل مبلغ سبد برای این کد تخفیف ' . temsah_format_toman( $coupon->min_order ) . ' است.' );
        }
        return $coupon;
    }

    private static function get_raw_subtotal( int $cart_id ): int {
        global $wpdb;
        $rows = $wpdb->get_results( $wpdb->prepare( 'SELECT product_id FROM ' . Temsah_DB::table( 'cart_items' ) . ' WHERE cart_id = %d', $cart_id ) );
        $sum  = 0;
        foreach ( $rows as $row ) {
            $p = Temsah_CPT::get_product_by_id( (int) $row->product_id );
            if ( $p && 'published' === $p->status ) {
                $sum += Temsah_CPT::effective_price( $p );
            }
        }
        return $sum;
    }

    public static function get_summary(): array {
        global $wpdb;
        $cart = self::get_or_create_cart( false );
        $empty = array(
            'cart_id'          => 0,
            'count'            => 0,
            'items'            => array(),
            'subtotal'         => 0,
            'product_discount' => 0,
            'services_total'   => 0,
            'coupon_code'      => '',
            'coupon_discount'  => 0,
            'total'            => 0,
        );
        if ( ! $cart ) {
            return $empty;
        }
        $rows = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . Temsah_DB::table( 'cart_items' ) . ' WHERE cart_id = %d ORDER BY id ASC', $cart->id ) );
        if ( empty( $rows ) ) {
            return $empty;
        }

        $catalog_services = self::available_services();
        $items            = array();
        $subtotal_orig    = 0;
        $product_discount = 0;
        $services_total   = 0;
        $net_products     = 0;

        foreach ( $rows as $row ) {
            $p = Temsah_CPT::get_product_by_id( (int) $row->product_id );
            if ( ! $p || 'published' !== $p->status ) {
                continue;
            }
            $orig = (int) $p->price;
            $eff  = Temsah_CPT::effective_price( $p );
            $disc = max( 0, $orig - $eff );

            $selected_services = json_decode( (string) $row->services_json, true );
            $selected_services = is_array( $selected_services ) ? self::sanitize_services( $selected_services ) : array();
            $svc_price         = 0;
            foreach ( $selected_services as $sid ) {
                if ( isset( $catalog_services[ $sid ] ) ) {
                    $svc_price += (int) $catalog_services[ $sid ]['price'];
                }
            }

            $subtotal_orig    += $orig;
            $product_discount += $disc;
            $services_total   += $svc_price;
            $net_products     += $eff;

            $items[] = array(
                'product'           => $p,
                'original_price'    => $orig,
                'effective_price'   => $eff,
                'discount'          => $disc,
                'selected_services' => $selected_services,
                'services_price'    => $svc_price,
                'line_total'        => $eff + $svc_price,
            );
        }

        $base_for_coupon = $net_products + $services_total;
        $coupon_code     = (string) ( $cart->coupon_code ?? '' );
        $coupon_discount = 0;
        if ( '' !== $coupon_code && $base_for_coupon > 0 ) {
            $coupon = self::validate_coupon_for_cart( $coupon_code, $net_products );
            if ( ! is_wp_error( $coupon ) ) {
                if ( 'percent' === $coupon->type ) {
                    $coupon_discount = (int) round( $base_for_coupon * ( (int) $coupon->amount / 100 ) );
                    if ( ! empty( $coupon->max_discount ) && $coupon_discount > (int) $coupon->max_discount ) {
                        $coupon_discount = (int) $coupon->max_discount;
                    }
                } else {
                    $coupon_discount = min( $base_for_coupon, (int) $coupon->amount );
                }
            } else {
                $coupon_code = '';
            }
        }

        $total = max( 0, $base_for_coupon - $coupon_discount );

        return array(
            'cart_id'          => (int) $cart->id,
            'count'            => count( $items ),
            'items'            => $items,
            'subtotal'         => $subtotal_orig,
            'product_discount' => $product_discount,
            'services_total'   => $services_total,
            'coupon_code'      => $coupon_code,
            'coupon_discount'  => $coupon_discount,
            'total'            => $total,
        );
    }

    public static function clear_cart( int $cart_id ): void {
        global $wpdb;
        $wpdb->delete( Temsah_DB::table( 'cart_items' ), array( 'cart_id' => $cart_id ) );
        $wpdb->update( Temsah_DB::table( 'carts' ), array( 'coupon_code' => null ), array( 'id' => $cart_id ) );
    }

    public static function create_order_from_cart( int $user_id, array $buyer_data, string $gateway_id ): array {
        global $wpdb;
        $summary = self::get_summary();
        if ( empty( $summary['items'] ) ) {
            return array( 'ok' => false, 'message' => 'سبد خرید شما خالی است.' );
        }

        $buyer_name   = sanitize_text_field( $buyer_data['name'] ?? '' );
        $buyer_mobile = sanitize_text_field( $buyer_data['mobile'] ?? '' );
        $buyer_email  = sanitize_email( $buyer_data['email'] ?? '' );

        if ( '' === $buyer_name || '' === $buyer_email ) {
            return array( 'ok' => false, 'message' => 'نام و ایمیل خریدار الزامی است.' );
        }

        $settings         = get_option( 'temsah_settings', array() );
        $default_comm     = (float) ( $settings['default_commission_rate'] ?? 70 );
        $order_number     = 'TM-' . wp_rand( 100000, 999999 );
        $now              = current_time( 'mysql' );
        $t_orders         = Temsah_DB::table( 'orders' );
        $t_order_items    = Temsah_DB::table( 'order_items' );

        $wpdb->insert(
            $t_orders,
            array(
                'order_number'     => $order_number,
                'user_id'          => $user_id,
                'buyer_name'       => $buyer_name,
                'buyer_mobile'     => $buyer_mobile,
                'buyer_email'      => $buyer_email,
                'subtotal'         => $summary['subtotal'],
                'product_discount' => $summary['product_discount'],
                'services_total'   => $summary['services_total'],
                'coupon_code'      => $summary['coupon_code'] ?: null,
                'coupon_discount'  => $summary['coupon_discount'],
                'total'            => $summary['total'],
                'gateway_id'       => sanitize_key( $gateway_id ),
                'status'           => 'pending_payment',
                'created_at'       => $now,
            ),
            array( '%s', '%d', '%s', '%s', '%s', '%d', '%d', '%d', '%s', '%d', '%d', '%s', '%s', '%s' )
        );
        $order_id = (int) $wpdb->insert_id;
        if ( $order_id <= 0 ) {
            return array( 'ok' => false, 'message' => 'ثبت سفارش با خطا مواجه شد.' );
        }

        $total_before_coupon = max( 1, ( $summary['subtotal'] - $summary['product_discount'] ) + $summary['services_total'] );
        $coupon_ratio        = $summary['coupon_discount'] > 0 ? ( $summary['total'] / $total_before_coupon ) : 1.0;

        foreach ( $summary['items'] as $item ) {
            $p          = $item['product'];
            $vendor     = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . Temsah_DB::table( 'vendors' ) . ' WHERE id = %d', $p->vendor_id ) );
            $comm_rate  = $vendor ? (float) $vendor->commission_rate : $default_comm;
            $line_final = (int) round( $item['line_total'] * $coupon_ratio );
            $v_earning  = (int) round( $line_final * ( $comm_rate / 100 ) );
            $p_fee      = max( 0, $line_final - $v_earning );

            $wpdb->insert(
                $t_order_items,
                array(
                    'order_id'        => $order_id,
                    'product_id'      => $p->id,
                    'vendor_id'       => $p->vendor_id,
                    'product_title'   => $p->post_title,
                    'unit_price'      => $item['effective_price'],
                    'original_price'  => $item['original_price'],
                    'services_json'   => wp_json_encode( $item['selected_services'] ),
                    'services_price'  => $item['services_price'],
                    'line_total'      => $line_final,
                    'commission_rate' => $comm_rate,
                    'vendor_earning'  => $v_earning,
                    'platform_fee'    => $p_fee,
                ),
                array( '%d', '%d', '%d', '%s', '%d', '%d', '%s', '%d', '%d', '%f', '%d', '%d' )
            );
        }

        $order = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$t_orders} WHERE id = %d", $order_id ) );
        Temsah_Security::log_activity( 'order_created', 'order', $order_id, array( 'total' => $summary['total'] ), $user_id );
        return array( 'ok' => true, 'order' => $order, 'cart_id' => $summary['cart_id'] );
    }
}
