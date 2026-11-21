<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Temsah_Security {
    public static function init(): void {
        add_action( 'template_redirect', array( __CLASS__, 'handle_protected_download' ), 1 );
        add_action( 'init', array( __CLASS__, 'block_direct_theme_html_access' ), 0 );
    }

    /**
     * Deny direct access to any static HTML snapshot if placed accidentally inside theme folder.
     */
    public static function block_direct_theme_html_access(): void {
        $uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
        if ( preg_match( '#/wp-content/themes/[^/]+/.*\.html(\?|$)#i', $uri ) ) {
            status_header( 403 );
            wp_die( 'دسترسی مستقیم مجاز نیست.', 'خطای دسترسی', array( 'response' => 403 ) );
        }
    }

    public static function client_ip(): string {
        $ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '0.0.0.0';
        return substr( $ip, 0, 64 );
    }

    public static function log_activity( string $action, string $object_type = '', ?int $object_id = null, array $details = array(), ?int $user_id = null ): void {
        global $wpdb;
        $uid = $user_id ?? ( is_user_logged_in() ? get_current_user_id() : null );
        $wpdb->insert(
            Temsah_DB::table( 'activity_logs' ),
            array(
                'user_id'      => $uid,
                'action'       => sanitize_key( $action ),
                'object_type'  => sanitize_key( $object_type ),
                'object_id'    => $object_id,
                'ip_address'   => self::client_ip(),
                'details_json' => wp_json_encode( $details, JSON_UNESCAPED_UNICODE ),
                'created_at'   => current_time( 'mysql' ),
            ),
            array( '%d', '%s', '%s', '%d', '%s', '%s', '%s' )
        );
    }

    /**
     * Transient-based brute-force / rate limit helper.
     */
    public static function check_rate_limit( string $bucket, int $max_attempts = 5, int $window_seconds = 900 ): bool {
        $key  = 'temsah_rl_' . md5( $bucket . '|' . self::client_ip() );
        $hits = (int) get_transient( $key );
        return $hits < $max_attempts;
    }

    public static function hit_rate_limit( string $bucket, int $window_seconds = 900 ): void {
        $key  = 'temsah_rl_' . md5( $bucket . '|' . self::client_ip() );
        $hits = (int) get_transient( $key );
        set_transient( $key, $hits + 1, $window_seconds );
    }

    public static function reset_rate_limit( string $bucket ): void {
        $key = 'temsah_rl_' . md5( $bucket . '|' . self::client_ip() );
        delete_transient( $key );
    }

    public static function verify_nonce_or_die( string $action, string $field = '_wpnonce' ): void {
        $nonce = isset( $_REQUEST[ $field ] ) ? sanitize_text_field( wp_unslash( $_REQUEST[ $field ] ) ) : '';
        if ( ! wp_verify_nonce( $nonce, $action ) ) {
            self::log_activity( 'csrf_failed', 'security', null, array( 'action' => $action ) );
            wp_die( 'توکن امنیتی نامعتبر یا منقضی شده است. لطفاً صفحه را تازه کرده و دوباره تلاش کنید.', 'خطای امنیتی (CSRF)', array( 'response' => 403 ) );
        }
    }

    /**
     * Check both capability and verified DB approval status.
     */
    public static function get_vendor_by_user( int $user_id ): ?object {
        global $wpdb;
        if ( $user_id <= 0 ) {
            return null;
        }
        return $wpdb->get_row(
            $wpdb->prepare(
                'SELECT * FROM ' . Temsah_DB::table( 'vendors' ) . ' WHERE user_id = %d LIMIT 1',
                $user_id
            )
        ) ?: null;
    }

    public static function is_approved_vendor( ?int $user_id = null ): bool {
        $uid = $user_id ?? get_current_user_id();
        if ( $uid <= 0 ) {
            return false;
        }
        if ( user_can( $uid, 'temsah_manage_marketplace' ) ) {
            return true;
        }
        if ( ! user_can( $uid, 'temsah_access_vendor_portal' ) ) {
            return false;
        }
        $vendor = self::get_vendor_by_user( $uid );
        return $vendor && 'approved' === $vendor->status;
    }

    public static function can_access_buyer_portal(): bool {
        return is_user_logged_in();
    }

    public static function enforce_private_headers(): void {
        nocache_headers();
        header( 'Cache-Control: no-store, no-cache, must-revalidate, max-age=0', true );
        header( 'Pragma: no-cache', true );
        header( 'X-Robots-Tag: noindex, nofollow', true );
    }

    public static function redirect_to_login(): void {
        self::enforce_private_headers();
        $req_uri = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '/';
        $login   = add_query_arg( 'redirect_to', rawurlencode( home_url( $req_uri ) ), Temsah_Router::url( 'login' ) );
        wp_safe_redirect( $login );
        exit;
    }

    /**
     * Ownership assertions (IDOR defense).
     */
    public static function user_owns_order( int $order_id, ?int $user_id = null ): ?object {
        global $wpdb;
        $uid = $user_id ?? get_current_user_id();
        if ( $uid <= 0 || $order_id <= 0 ) {
            return null;
        }
        $order = $wpdb->get_row(
            $wpdb->prepare(
                'SELECT * FROM ' . Temsah_DB::table( 'orders' ) . ' WHERE id = %d LIMIT 1',
                $order_id
            )
        );
        if ( ! $order ) {
            return null;
        }
        if ( (int) $order->user_id === $uid || user_can( $uid, 'temsah_manage_orders' ) ) {
            return $order;
        }
        self::log_activity( 'idor_order_blocked', 'order', $order_id, array(), $uid );
        return null;
    }

    public static function user_owns_product( int $product_id, ?int $user_id = null ): ?object {
        global $wpdb;
        $uid = $user_id ?? get_current_user_id();
        if ( $uid <= 0 || $product_id <= 0 ) {
            return null;
        }
        $product = $wpdb->get_row(
            $wpdb->prepare(
                'SELECT * FROM ' . Temsah_DB::table( 'products' ) . ' WHERE id = %d LIMIT 1',
                $product_id
            )
        );
        if ( ! $product ) {
            return null;
        }
        if ( user_can( $uid, 'temsah_manage_marketplace' ) ) {
            return $product;
        }
        $vendor = self::get_vendor_by_user( $uid );
        if ( $vendor && 'approved' === $vendor->status && (int) $product->vendor_id === (int) $vendor->id ) {
            return $product;
        }
        self::log_activity( 'idor_product_blocked', 'product', $product_id, array(), $uid );
        return null;
    }

    public static function user_can_access_ticket( int $ticket_id, string $as = 'buyer', ?int $user_id = null ): ?object {
        global $wpdb;
        $uid = $user_id ?? get_current_user_id();
        if ( $uid <= 0 || $ticket_id <= 0 ) {
            return null;
        }
        $ticket = $wpdb->get_row(
            $wpdb->prepare(
                'SELECT * FROM ' . Temsah_DB::table( 'tickets' ) . ' WHERE id = %d LIMIT 1',
                $ticket_id
            )
        );
        if ( ! $ticket ) {
            return null;
        }
        if ( user_can( $uid, 'temsah_manage_marketplace' ) ) {
            return $ticket;
        }
        if ( 'buyer' === $as && (int) $ticket->user_id === $uid ) {
            return $ticket;
        }
        if ( 'vendor' === $as ) {
            $vendor = self::get_vendor_by_user( $uid );
            if ( $vendor && 'approved' === $vendor->status && (int) $ticket->vendor_id === (int) $vendor->id ) {
                return $ticket;
            }
        }
        self::log_activity( 'idor_ticket_blocked', 'ticket', $ticket_id, array( 'as' => $as ), $uid );
        return null;
    }

    public static function user_has_purchased_product( int $user_id, int $product_id ): ?object {
        global $wpdb;
        if ( $user_id <= 0 || $product_id <= 0 ) {
            return null;
        }
        $t_o  = Temsah_DB::table( 'orders' );
        $t_oi = Temsah_DB::table( 'order_items' );
        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT o.id AS order_id, o.order_number, oi.id AS order_item_id
                 FROM {$t_o} o
                 INNER JOIN {$t_oi} oi ON oi.order_id = o.id
                 WHERE o.user_id = %d AND oi.product_id = %d AND o.status = 'paid'
                 ORDER BY o.id DESC LIMIT 1",
                $user_id,
                $product_id
            )
        ) ?: null;
    }

    /**
     * Save an uploaded file safely into the protected vault outside direct HTTP access.
     */
    public static function store_vault_file( array $file, string $subdir, array $allowed_exts ): array|WP_Error {
        if ( empty( $file['name'] ) || ! isset( $file['tmp_name'] ) || (int) $file['error'] !== UPLOAD_ERR_OK ) {
            return new WP_Error( 'upload_failed', 'بارگذاری فایل انجام نشد.' );
        }
        $ext = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );
        if ( ! in_array( $ext, $allowed_exts, true ) ) {
            return new WP_Error( 'bad_ext', 'پسوند فایل مجاز نیست.' );
        }
        if ( (int) $file['size'] > 100 * 1024 * 1024 ) {
            return new WP_Error( 'too_large', 'حجم فایل بیش از حد مجاز است.' );
        }
        $base = Temsah_DB::vault_dir() . '/' . sanitize_file_name( $subdir );
        if ( ! is_dir( $base ) ) {
            wp_mkdir_p( $base );
        }
        $safe_name = wp_generate_uuid4() . '-' . sanitize_file_name( basename( $file['name'] ) );
        $dest      = $base . '/' . $safe_name;

        $moved = @move_uploaded_file( $file['tmp_name'], $dest );
        if ( ! $moved ) {
            $moved = @copy( $file['tmp_name'], $dest );
        }
        if ( ! $moved ) {
            return new WP_Error( 'move_failed', 'ذخیره فایل در مخزن امن با خطا مواجه شد.' );
        }
        return array(
            'file_name'       => sanitize_file_name( basename( $file['name'] ) ),
            'storage_path'    => $dest,
            'file_size'       => (int) filesize( $dest ),
            'checksum_sha256' => hash_file( 'sha256', $dest ) ?: '',
        );
    }

    public static function download_url( int $product_id, int $file_id ): string {
        return wp_nonce_url(
            add_query_arg(
                array(
                    'temsah_download' => 1,
                    'product_id'      => $product_id,
                    'file_id'         => $file_id,
                ),
                home_url( '/' )
            ),
            'temsah_download_' . $product_id . '_' . $file_id
        );
    }

    public static function handle_protected_download(): void {
        if ( empty( $_GET['temsah_download'] ) ) {
            return;
        }
        self::enforce_private_headers();

        if ( ! is_user_logged_in() ) {
            self::redirect_to_login();
        }

        $product_id = isset( $_GET['product_id'] ) ? absint( $_GET['product_id'] ) : 0;
        $file_id    = isset( $_GET['file_id'] ) ? absint( $_GET['file_id'] ) : 0;
        $nonce      = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';

        if ( ! wp_verify_nonce( $nonce, 'temsah_download_' . $product_id . '_' . $file_id ) ) {
            status_header( 403 );
            wp_die( 'لینک دانلود منقضی شده یا نامعتبر است.', 'دسترسی غیرمجاز', array( 'response' => 403 ) );
        }

        $uid        = get_current_user_id();
        $purchase   = self::user_has_purchased_product( $uid, $product_id );
        $owns_prod  = self::user_owns_product( $product_id, $uid );
        $is_admin   = current_user_can( 'temsah_manage_marketplace' );

        if ( ! $purchase && ! $owns_prod && ! $is_admin ) {
            self::log_activity( 'unauthorized_download', 'product', $product_id, array( 'file_id' => $file_id ), $uid );
            status_header( 403 );
            wp_die( 'شما مجوز دانلود فایل‌های این محصول را ندارید.', 'عدم دسترسی', array( 'response' => 403 ) );
        }

        global $wpdb;
        $file = $wpdb->get_row(
            $wpdb->prepare(
                'SELECT * FROM ' . Temsah_DB::table( 'product_files' ) . ' WHERE id = %d AND product_id = %d AND is_active = 1 LIMIT 1',
                $file_id,
                $product_id
            )
        );
        if ( ! $file || ! file_exists( $file->storage_path ) ) {
            status_header( 404 );
            wp_die( 'فایل محصول یافت نشد.', 'فایل ناموجود', array( 'response' => 404 ) );
        }

        $wpdb->insert(
            Temsah_DB::table( 'downloads' ),
            array(
                'user_id'       => $uid,
                'product_id'    => $product_id,
                'file_id'       => $file_id,
                'order_id'      => $purchase ? (int) $purchase->order_id : 0,
                'ip_address'    => self::client_ip(),
                'user_agent'    => isset( $_SERVER['HTTP_USER_AGENT'] ) ? substr( sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ), 0, 250 ) : '',
                'downloaded_at' => current_time( 'mysql' ),
            ),
            array( '%d', '%d', '%d', '%d', '%s', '%s', '%s' )
        );

        self::log_activity( 'file_downloaded', 'product_file', $file_id, array( 'product_id' => $product_id ), $uid );

        header( 'Content-Description: File Transfer' );
        header( 'Content-Type: application/octet-stream' );
        header( 'Content-Disposition: attachment; filename="' . rawurlencode( basename( $file->file_name ) ) . '"' );
        header( 'Content-Length: ' . filesize( $file->storage_path ) );
        header( 'X-Content-Type-Options: nosniff' );
        readfile( $file->storage_path );
        exit;
    }
}
