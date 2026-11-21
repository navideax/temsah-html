<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

interface Temsah_Gateway_Interface {
    public function get_id(): string;
    public function get_title(): string;
    public function get_description(): string;
    public function is_enabled(): bool;
    public function is_sandbox(): bool;
    public function initiate( object $order, string $authority ): array;
    public function verify( object $payment, array $request ): array;
}

class Temsah_Gateway_Sandbox implements Temsah_Gateway_Interface {
    public function get_id(): string {
        return 'sandbox';
    }

    public function get_title(): string {
        return 'درگاه آزمایشی تمساح (Sandbox)';
    }

    public function get_description(): string {
        return 'شبیه‌ساز امن تست پرداخت با امضای سرور (مناسب محیط توسعه و بررسی — تراکنش واقعی بانکی انجام نمی‌دهد).';
    }

    public function is_enabled(): bool {
        $s = get_option( 'temsah_settings', array() );
        return ! isset( $s['sandbox_gateway_enabled'] ) || (bool) $s['sandbox_gateway_enabled'];
    }

    public function is_sandbox(): bool {
        return true;
    }

    public static function sign( string $authority, int $order_id, int $amount ): string {
        return hash_hmac( 'sha256', $authority . '|' . $order_id . '|' . $amount, wp_salt( 'auth' ) );
    }

    public function initiate( object $order, string $authority ): array {
        $sig = self::sign( $authority, (int) $order->id, (int) $order->total );
        $url = temsah_url( 'payment-callback', array(
            'gateway'   => 'sandbox',
            'authority' => $authority,
            'sig'       => $sig,
        ) );
        return array(
            'ok'           => true,
            'mode'         => 'sandbox_prompt',
            'redirect_url' => $url,
            'authority'    => $authority,
            'sig'          => $sig,
        );
    }

    public function verify( object $payment, array $request ): array {
        $sig    = sanitize_text_field( (string) ( $request['sig'] ?? '' ) );
        $status = sanitize_key( (string) ( $request['status'] ?? 'ok' ) );
        $expected = self::sign( $payment->authority, (int) $payment->order_id, (int) $payment->amount );

        if ( ! hash_equals( $expected, $sig ) ) {
            return array( 'ok' => false, 'message' => 'امضای امنیتی درگاه آزمایشی معتبر نیست.' );
        }
        if ( 'ok' !== $status ) {
            return array( 'ok' => false, 'message' => 'پرداخت در شبیه‌ساز درگاه توسط کاربر لغو یا ناموفق اعلام شد.' );
        }
        return array(
            'ok'     => true,
            'ref_id' => 'SBX-' . strtoupper( substr( md5( $payment->authority . '|' . time() ), 0, 10 ) ),
        );
    }
}

class Temsah_Gateway_Zarinpal implements Temsah_Gateway_Interface {
    public function get_id(): string {
        return 'zarinpal';
    }

    public function get_title(): string {
        return 'درگاه زرین‌پال (API واقعی)';
    }

    public function get_description(): string {
        $s = get_option( 'temsah_settings', array() );
        if ( empty( $s['zarinpal_enabled'] ) || empty( $s['zarinpal_merchant_id'] ) ) {
            return 'غیرفعال — نیازمند ثبت Merchant ID معتبر زرین‌پال در تنظیمات تمساح.';
        }
        return 'اتصال از طریق وب‌سرویس رسمی زرین‌پال (پرداخت و تأیید سمت سرور).';
    }

    public function is_enabled(): bool {
        $s = get_option( 'temsah_settings', array() );
        return ! empty( $s['zarinpal_enabled'] ) && ! empty( $s['zarinpal_merchant_id'] );
    }

    public function is_sandbox(): bool {
        return false;
    }

    public function initiate( object $order, string $authority ): array {
        if ( ! $this->is_enabled() ) {
            return array( 'ok' => false, 'message' => 'درگاه زرین‌پال پیکربندی نشده است و کلید API ثبت نشده است.' );
        }
        $s        = get_option( 'temsah_settings', array() );
        $merchant = (string) $s['zarinpal_merchant_id'];
        $callback = temsah_url( 'payment-callback', array( 'gateway' => 'zarinpal', 'authority_ref' => $authority ) );

        $resp = wp_remote_post( 'https://payment.zarinpal.com/pg/v4/payment/request.json', array(
            'timeout' => 15,
            'headers' => array( 'Content-Type' => 'application/json' ),
            'body'    => wp_json_encode( array(
                'merchant_id'  => $merchant,
                'amount'       => (int) $order->total * 10, // Rials
                'callback_url' => $callback,
                'description'  => 'سفارش تمساح ' . $order->order_number,
                'metadata'     => array( 'email' => $order->buyer_email, 'mobile' => $order->buyer_mobile ),
            ) ),
        ) );

        if ( is_wp_error( $resp ) ) {
            return array( 'ok' => false, 'message' => 'خطا در اتصال به درگاه زرین‌پال: ' . $resp->get_error_message() );
        }
        $body = json_decode( wp_remote_retrieve_body( $resp ), true );
        if ( ! empty( $body['data']['authority'] ) ) {
            global $wpdb;
            $zp_auth = sanitize_text_field( $body['data']['authority'] );
            $wpdb->update( Temsah_DB::table( 'payments' ), array( 'authority' => $zp_auth ), array( 'authority' => $authority ) );
            return array(
                'ok'           => true,
                'mode'         => 'redirect',
                'redirect_url' => 'https://payment.zarinpal.com/pg/StartPay/' . rawurlencode( $zp_auth ),
                'authority'    => $zp_auth,
            );
        }
        return array( 'ok' => false, 'message' => 'درگاه زرین‌پال تراکنش را نپذیرفت.' );
    }

    public function verify( object $payment, array $request ): array {
        if ( ! $this->is_enabled() ) {
            return array( 'ok' => false, 'message' => 'درگاه زرین‌پال فعال نیست.' );
        }
        $status = sanitize_text_field( (string) ( $request['Status'] ?? '' ) );
        if ( 'OK' !== strtoupper( $status ) ) {
            return array( 'ok' => false, 'message' => 'تراکنش توسط کاربر در درگاه لغو شد.' );
        }
        $s    = get_option( 'temsah_settings', array() );
        $resp = wp_remote_post( 'https://payment.zarinpal.com/pg/v4/payment/verify.json', array(
            'timeout' => 15,
            'headers' => array( 'Content-Type' => 'application/json' ),
            'body'    => wp_json_encode( array(
                'merchant_id' => (string) $s['zarinpal_merchant_id'],
                'amount'      => (int) $payment->amount * 10,
                'authority'   => $payment->authority,
            ) ),
        ) );
        if ( is_wp_error( $resp ) ) {
            return array( 'ok' => false, 'message' => 'خطا در تایید نهایی درگاه.' );
        }
        $body = json_decode( wp_remote_retrieve_body( $resp ), true );
        $code = (int) ( $body['data']['code'] ?? 0 );
        if ( 100 === $code || 101 === $code ) {
            return array(
                'ok'     => true,
                'ref_id' => sanitize_text_field( (string) ( $body['data']['ref_id'] ?? '' ) ),
            );
        }
        return array( 'ok' => false, 'message' => 'تراکنش توسط درگاه تایید نشد.' );
    }
}

class Temsah_Gateway_Saman implements Temsah_Gateway_Interface {
    public function get_id(): string {
        return 'saman';
    }

    public function get_title(): string {
        return 'درگاه پرداخت سپ (سامان)';
    }

    public function get_description(): string {
        $s = get_option( 'temsah_settings', array() );
        if ( empty( $s['saman_enabled'] ) || empty( $s['saman_terminal_id'] ) ) {
            return 'غیرفعال — نیازمند ثبت شماره ترمینال سپ در تنظیمات تمساح.';
        }
        return 'درگاه بانکی سپ با تایید توکن سمت سرور.';
    }

    public function is_enabled(): bool {
        $s = get_option( 'temsah_settings', array() );
        return ! empty( $s['saman_enabled'] ) && ! empty( $s['saman_terminal_id'] );
    }

    public function is_sandbox(): bool {
        return false;
    }

    public function initiate( object $order, string $authority ): array {
        return array( 'ok' => false, 'message' => 'اتصال زنده سپ نیازمند ثبت و پیکربندی کلیدهای پذیرنده در پیشخوان تمساح است.' );
    }

    public function verify( object $payment, array $request ): array {
        return array( 'ok' => false, 'message' => 'درگاه سپ فعال نیست.' );
    }
}

class Temsah_Payments {
    public static function init(): void {
        add_action( 'template_redirect', array( __CLASS__, 'handle_checkout_post' ), 5 );
    }

    public static function get_gateways(): array {
        $list = array(
            new Temsah_Gateway_Sandbox(),
            new Temsah_Gateway_Zarinpal(),
            new Temsah_Gateway_Saman(),
        );
        return apply_filters( 'temsah_payment_gateways', $list );
    }

    public static function get_gateway( string $id ): ?Temsah_Gateway_Interface {
        foreach ( self::get_gateways() as $gw ) {
            if ( $gw->get_id() === $id ) {
                return $gw;
            }
        }
        return null;
    }

    public static function handle_checkout_post(): void {
        if ( empty( $_POST['temsah_action'] ) || 'checkout_pay' !== $_POST['temsah_action'] ) {
            return;
        }
        Temsah_Security::enforce_private_headers();
        if ( ! is_user_logged_in() ) {
            Temsah_Security::redirect_to_login();
        }
        Temsah_Security::verify_nonce_or_die( 'temsah_checkout_pay' );

        $uid        = get_current_user_id();
        $gateway_id = sanitize_key( wp_unslash( $_POST['gateway_id'] ?? 'sandbox' ) );
        $gw         = self::get_gateway( $gateway_id );

        if ( ! $gw || ! $gw->is_enabled() ) {
            wp_safe_redirect( temsah_url( 'checkout', array( 'pay_err' => rawurlencode( 'درگاه پرداخت انتخاب‌شده فعال نیست.' ) ) ) );
            exit;
        }

        $buyer_data = array(
            'name'   => sanitize_text_field( wp_unslash( $_POST['buyer_name'] ?? '' ) ),
            'mobile' => sanitize_text_field( wp_unslash( $_POST['buyer_mobile'] ?? '' ) ),
            'email'  => sanitize_email( wp_unslash( $_POST['buyer_email'] ?? '' ) ),
        );

        $created = Temsah_Cart::create_order_from_cart( $uid, $buyer_data, $gateway_id );
        if ( empty( $created['ok'] ) ) {
            wp_safe_redirect( temsah_url( 'checkout', array( 'pay_err' => rawurlencode( $created['message'] ) ) ) );
            exit;
        }

        $order = $created['order'];
        $cart_id = (int) $created['cart_id'];

        // Free order (total === 0) can fulfill directly server-side without external bank
        if ( 0 === (int) $order->total ) {
            $authority = 'FREE-' . wp_generate_uuid4();
            self::insert_payment_record( $order, 'free', $authority );
            $payment = self::get_payment_by_authority( $authority );
            self::fulfill_paid_order( $order, $payment, 'FREE-' . $order->id, $cart_id );
            wp_safe_redirect( temsah_url( 'account/order-detail', array( 'id' => $order->id, 'paid' => 1 ) ) );
            exit;
        }

        $authority = 'AUTH-' . wp_generate_uuid4();
        self::insert_payment_record( $order, $gateway_id, $authority );
        $init = $gw->initiate( $order, $authority );

        if ( empty( $init['ok'] ) ) {
            wp_safe_redirect( temsah_url( 'checkout', array( 'pay_err' => rawurlencode( $init['message'] ) ) ) );
            exit;
        }

        if ( 'sandbox_prompt' === ( $init['mode'] ?? '' ) ) {
            // Forward to sandbox confirmation page where user chooses Success / Fail (never fake silent bank approval)
            $url = temsah_url( 'payment-callback', array(
                'gateway'   => 'sandbox',
                'stage'     => 'prompt',
                'authority' => $authority,
                'sig'       => $init['sig'],
            ) );
            wp_safe_redirect( $url );
            exit;
        }

        wp_redirect( $init['redirect_url'] );
        exit;
    }

    private static function insert_payment_record( object $order, string $gateway_id, string $authority ): void {
        global $wpdb;
        $wpdb->insert(
            Temsah_DB::table( 'payments' ),
            array(
                'order_id'   => (int) $order->id,
                'user_id'    => (int) $order->user_id,
                'gateway_id' => $gateway_id,
                'authority'  => $authority,
                'amount'     => (int) $order->total,
                'status'     => 'initiated',
                'created_at' => current_time( 'mysql' ),
            ),
            array( '%d', '%d', '%s', '%s', '%d', '%s', '%s' )
        );
    }

    public static function get_payment_by_authority( string $authority ): ?object {
        global $wpdb;
        return $wpdb->get_row(
            $wpdb->prepare( 'SELECT * FROM ' . Temsah_DB::table( 'payments' ) . ' WHERE authority = %s LIMIT 1', $authority )
        ) ?: null;
    }

    public static function process_callback( array $request ): array {
        global $wpdb;
        $authority = sanitize_text_field( (string) ( $request['authority'] ?? $request['Authority'] ?? '' ) );
        if ( '' === $authority ) {
            return array( 'ok' => false, 'message' => 'شناسه تراکنش (Authority) نامعتبر است.' );
        }

        $payment = self::get_payment_by_authority( $authority );
        if ( ! $payment ) {
            return array( 'ok' => false, 'message' => 'تراکنش در پایگاه داده یافت نشد.' );
        }

        $uid = get_current_user_id();
        if ( (int) $payment->user_id !== $uid && ! current_user_can( 'temsah_manage_orders' ) ) {
            Temsah_Security::log_activity( 'idor_payment_callback', 'payment', (int) $payment->id, array(), $uid );
            return array( 'ok' => false, 'message' => 'این تراکنش متعلق به حساب کاربری شما نیست.' );
        }

        $order = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . Temsah_DB::table( 'orders' ) . ' WHERE id = %d LIMIT 1', $payment->order_id ) );
        if ( ! $order ) {
            return array( 'ok' => false, 'message' => 'سفارش مرتبط یافت نشد.' );
        }

        // Duplicate / replay protection
        if ( 'verified' === $payment->status || 'paid' === $order->status ) {
            Temsah_Security::log_activity( 'duplicate_payment_callback_blocked', 'payment', (int) $payment->id, array( 'order_id' => $order->id ), $uid );
            return array( 'ok' => true, 'already_paid' => true, 'order' => $order, 'message' => 'این سفارش قبلاً تایید و ثبت شده است.' );
        }

        // Amount tampering guard
        if ( (int) $payment->amount !== (int) $order->total ) {
            Temsah_Security::log_activity( 'payment_amount_mismatch', 'payment', (int) $payment->id, array( 'pay' => $payment->amount, 'order' => $order->total ), $uid );
            return array( 'ok' => false, 'order' => $order, 'message' => 'مبلغ تراکنش با مبلغ سفارش مطابقت ندارد.' );
        }

        $gw = self::get_gateway( $payment->gateway_id );
        if ( ! $gw ) {
            return array( 'ok' => false, 'order' => $order, 'message' => 'درگاه پرداخت نامعتبر است.' );
        }

        $verify = $gw->verify( $payment, $request );
        if ( empty( $verify['ok'] ) ) {
            $wpdb->update(
                Temsah_DB::table( 'payments' ),
                array(
                    'status'           => 'failed',
                    'callback_payload' => wp_json_encode( $request ),
                ),
                array( 'id' => $payment->id )
            );
            $wpdb->update( Temsah_DB::table( 'orders' ), array( 'status' => 'failed' ), array( 'id' => $order->id ) );
            Temsah_Security::log_activity( 'payment_failed', 'order', (int) $order->id, array( 'reason' => $verify['message'] ), $uid );
            return array( 'ok' => false, 'order' => $order, 'message' => $verify['message'] );
        }

        $cart = Temsah_Cart::get_or_create_cart( false );
        self::fulfill_paid_order( $order, $payment, (string) $verify['ref_id'], $cart ? (int) $cart->id : 0, $request );

        $fresh_order = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . Temsah_DB::table( 'orders' ) . ' WHERE id = %d', $order->id ) );
        return array( 'ok' => true, 'order' => $fresh_order, 'ref_id' => $verify['ref_id'], 'message' => 'پرداخت با موفقیت تایید و فایل‌ها فعال شدند.' );
    }

    public static function fulfill_paid_order( object $order, object $payment, string $ref_id, int $cart_id = 0, array $payload = array() ): void {
        global $wpdb;
        $now = current_time( 'mysql' );

        // Update payment & order
        $wpdb->update(
            Temsah_DB::table( 'payments' ),
            array(
                'status'           => 'verified',
                'ref_id'           => $ref_id,
                'callback_payload' => wp_json_encode( $payload ),
                'verified_at'      => $now,
            ),
            array( 'id' => $payment->id )
        );

        $wpdb->update(
            Temsah_DB::table( 'orders' ),
            array(
                'status'  => 'paid',
                'paid_at' => $now,
            ),
            array( 'id' => $order->id )
        );

        // Increment coupon usage
        if ( ! empty( $order->coupon_code ) ) {
            $wpdb->query(
                $wpdb->prepare(
                    'UPDATE ' . Temsah_DB::table( 'coupons' ) . ' SET usage_count = usage_count + 1 WHERE code = %s',
                    $order->coupon_code
                )
            );
        }

        // Buyer payment transaction
        $wpdb->insert(
            Temsah_DB::table( 'transactions' ),
            array(
                'user_id'     => (int) $order->user_id,
                'order_id'    => (int) $order->id,
                'type'        => 'payment',
                'amount'      => (int) $order->total,
                'description' => 'پرداخت سفارش ' . $order->order_number . ' (کد رهگیری: ' . $ref_id . ')',
                'created_at'  => $now,
            ),
            array( '%d', '%d', '%s', '%d', '%s', '%s' )
        );

        // Process each order item: licenses, sales counts, vendor commissions, wallet ledger
        $items = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . Temsah_DB::table( 'order_items' ) . ' WHERE order_id = %d', $order->id ) );
        foreach ( $items as $item ) {
            // License
            $wpdb->insert(
                Temsah_DB::table( 'licenses' ),
                array(
                    'order_id'      => (int) $order->id,
                    'order_item_id' => (int) $item->id,
                    'product_id'    => (int) $item->product_id,
                    'user_id'       => (int) $order->user_id,
                    'license_key'   => strtoupper( wp_generate_uuid4() ),
                    'status'        => 'active',
                    'created_at'    => $now,
                ),
                array( '%d', '%d', '%d', '%d', '%s', '%s', '%s' )
            );

            // Product sales_count
            $wpdb->query(
                $wpdb->prepare(
                    'UPDATE ' . Temsah_DB::table( 'products' ) . ' SET sales_count = sales_count + 1 WHERE id = %d',
                    $item->product_id
                )
            );

            // Commission & Vendor Wallet
            $wpdb->insert(
                Temsah_DB::table( 'commissions' ),
                array(
                    'order_id'        => (int) $order->id,
                    'order_item_id'   => (int) $item->id,
                    'vendor_id'       => (int) $item->vendor_id,
                    'gross_amount'    => (int) $item->line_total,
                    'vendor_rate'     => (float) $item->commission_rate,
                    'vendor_amount'   => (int) $item->vendor_earning,
                    'platform_amount' => (int) $item->platform_fee,
                    'status'          => 'credited',
                    'created_at'      => $now,
                ),
                array( '%d', '%d', '%d', '%d', '%f', '%d', '%d', '%s', '%s' )
            );

            $vendor = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . Temsah_DB::table( 'vendors' ) . ' WHERE id = %d', $item->vendor_id ) );
            if ( $vendor ) {
                $new_avail = (int) $vendor->available_balance + (int) $item->vendor_earning;
                $new_total = (int) $vendor->total_earnings + (int) $item->vendor_earning;
                $wpdb->update(
                    Temsah_DB::table( 'vendors' ),
                    array(
                        'total_sales'       => (int) $vendor->total_sales + 1,
                        'total_earnings'    => $new_total,
                        'available_balance' => $new_avail,
                        'updated_at'        => $now,
                    ),
                    array( 'id' => $vendor->id )
                );

                $wpdb->insert(
                    Temsah_DB::table( 'vendor_wallet_ledger' ),
                    array(
                        'vendor_id'     => (int) $vendor->id,
                        'entry_type'    => 'credit_sale',
                        'amount'        => (int) $item->vendor_earning,
                        'balance_after' => $new_avail,
                        'reference_id'  => (int) $order->id,
                        'note'          => 'سهم فروش محصول «' . $item->product_title . '» در سفارش ' . $order->order_number,
                        'created_at'    => $now,
                    ),
                    array( '%d', '%s', '%d', '%d', '%d', '%s', '%s' )
                );

                $wpdb->insert(
                    Temsah_DB::table( 'transactions' ),
                    array(
                        'user_id'     => (int) $vendor->user_id,
                        'vendor_id'   => (int) $vendor->id,
                        'order_id'    => (int) $order->id,
                        'type'        => 'vendor_sale',
                        'amount'      => (int) $item->vendor_earning,
                        'description' => 'سهم فروش: ' . $item->product_title,
                        'created_at'  => $now,
                    ),
                    array( '%d', '%d', '%d', '%s', '%d', '%s', '%s' )
                );

                // Notify vendor
                $wpdb->insert(
                    Temsah_DB::table( 'notifications' ),
                    array(
                        'user_id'    => (int) $vendor->user_id,
                        'audience'   => 'vendor',
                        'title'      => 'فروش جدید ثبت شد',
                        'body'       => 'محصول «' . $item->product_title . '» در سفارش ' . $order->order_number . ' فروخته شد و مبلغ ' . temsah_format_toman( $item->vendor_earning ) . ' به کیف پول شما اضافه شد.',
                        'created_at' => $now,
                    ),
                    array( '%d', '%s', '%s', '%s', '%s' )
                );
            }
        }

        // Notify buyer
        $wpdb->insert(
            Temsah_DB::table( 'notifications' ),
            array(
                'user_id'    => (int) $order->user_id,
                'audience'   => 'buyer',
                'title'      => 'سفارش ' . $order->order_number . ' تکمیل شد',
                'body'       => 'پرداخت شما با موفقیت تایید شد. فایل‌ها و کلیدهای لایسنس در پنل کاربری آماده دانلود هستند.',
                'created_at' => $now,
            ),
            array( '%d', '%s', '%s', '%s', '%s' )
        );

        if ( $cart_id > 0 ) {
            Temsah_Cart::clear_cart( $cart_id );
        }

        Temsah_Security::log_activity( 'order_paid', 'order', (int) $order->id, array( 'ref_id' => $ref_id, 'total' => $order->total ), (int) $order->user_id );
    }
}
