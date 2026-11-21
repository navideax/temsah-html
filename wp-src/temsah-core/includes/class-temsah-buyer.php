<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Temsah_Buyer {
    public static function init(): void {
        add_action( 'template_redirect', array( __CLASS__, 'handle_post_actions' ), 6 );
    }

    public static function handle_post_actions(): void {
        if ( isset( $_GET['temsah_logout'] ) ) {
            Temsah_Security::verify_nonce_or_die( 'temsah_logout' );
            wp_logout();
            wp_safe_redirect( home_url( '/' ) );
            exit;
        }

        if ( empty( $_POST['temsah_action'] ) ) {
            return;
        }
        $action = sanitize_key( wp_unslash( $_POST['temsah_action'] ) );

        // Public auth POST actions
        if ( in_array( $action, array( 'login', 'register', 'lost_password', 'sms_otp_request' ), true ) ) {
            Temsah_Security::enforce_private_headers();
            Temsah_Security::verify_nonce_or_die( 'temsah_' . $action );
            switch ( $action ) {
                case 'login':
                    self::handle_login( $_POST );
                    break;
                case 'register':
                    self::handle_register( $_POST );
                    break;
                case 'lost_password':
                    self::handle_lost_password( $_POST );
                    break;
                case 'sms_otp_request':
                    wp_safe_redirect( temsah_url( 'login', array( 'auth_err' => rawurlencode( 'ارسال کد پیامکی نیازمند اتصال درگاه پیامکی واقعی (مانند کاوه‌نگار یا فراز اس‌ام‌اس) در تنظیمات سایت است. لطفاً با ایمیل و رمز عبور وارد شوید.' ) ) ) );
                    exit;
            }
        }

        $buyer_actions = array(
            'buyer_save_settings',
            'buyer_change_password',
            'buyer_create_ticket',
            'buyer_reply_ticket',
            'buyer_bind_license_domain',
            'submit_product_feedback',
            'send_contact_message',
        );
        if ( ! in_array( $action, $buyer_actions, true ) ) {
            return;
        }

        if ( 'send_contact_message' === $action ) {
            Temsah_Security::verify_nonce_or_die( 'temsah_send_contact_message' );
            if ( ! Temsah_Security::check_rate_limit( 'contact', 5, 600 ) ) {
                wp_safe_redirect( temsah_url( 'contact', array( 'contact_err' => rawurlencode( 'تعداد درخواست‌های شما زیاد است. لطفاً کمی بعد تلاش کنید.' ) ) ) );
                exit;
            }
            Temsah_Security::hit_rate_limit( 'contact', 600 );
            $name    = sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) );
            $email   = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
            $subject = sanitize_text_field( wp_unslash( $_POST['subject'] ?? 'پیام تماس با تمساح' ) );
            $msg     = sanitize_textarea_field( wp_unslash( $_POST['message'] ?? '' ) );
            if ( '' === $name || '' === $email || '' === $msg ) {
                wp_safe_redirect( temsah_url( 'contact', array( 'contact_err' => rawurlencode( 'تمام فیلدهای الزامی را تکمیل کنید.' ) ) ) );
                exit;
            }
            wp_mail( get_option( 'admin_email' ), '[تمساح] ' . $subject, "از: {$name} ({$email})\n\n{$msg}" );
            Temsah_Security::log_activity( 'contact_message', 'contact', null, array( 'email' => $email, 'subject' => $subject ) );
            wp_safe_redirect( temsah_url( 'contact', array( 'contact_ok' => rawurlencode( 'پیام شما ثبت و برای پشتیبانی ارسال شد.' ) ) ) );
            exit;
        }

        Temsah_Security::enforce_private_headers();
        if ( ! is_user_logged_in() ) {
            Temsah_Security::redirect_to_login();
        }
        Temsah_Security::verify_nonce_or_die( 'temsah_' . $action );
        $uid = get_current_user_id();

        switch ( $action ) {
            case 'buyer_save_settings':
                wp_update_user( array(
                    'ID'           => $uid,
                    'display_name' => sanitize_text_field( wp_unslash( $_POST['display_name'] ?? '' ) ),
                ) );
                update_user_meta( $uid, 'temsah_mobile', sanitize_text_field( wp_unslash( $_POST['mobile'] ?? '' ) ) );
                update_user_meta( $uid, 'temsah_bio', sanitize_textarea_field( wp_unslash( $_POST['bio'] ?? '' ) ) );
                wp_safe_redirect( temsah_url( 'account/settings', array( 'ok' => rawurlencode( 'مشخصات حساب با موفقیت ذخیره شد.' ) ) ) );
                exit;

            case 'buyer_change_password':
                $user = wp_get_current_user();
                $curr = (string) ( $_POST['current_password'] ?? '' );
                $new1 = (string) ( $_POST['new_password'] ?? '' );
                $new2 = (string) ( $_POST['confirm_password'] ?? '' );
                if ( ! wp_check_password( $curr, $user->user_pass, $uid ) ) {
                    wp_safe_redirect( temsah_url( 'account/settings', array( 'err' => rawurlencode( 'رمز عبور فعلی اشتباه است.' ) ) ) );
                    exit;
                }
                if ( strlen( $new1 ) < 8 || $new1 !== $new2 ) {
                    wp_safe_redirect( temsah_url( 'account/settings', array( 'err' => rawurlencode( 'رمز عبور جدید باید حداقل ۸ کاراکتر و با تکرار آن یکسان باشد.' ) ) ) );
                    exit;
                }
                wp_set_password( $new1, $uid );
                wp_set_auth_cookie( $uid );
                wp_safe_redirect( temsah_url( 'account/settings', array( 'ok' => rawurlencode( 'رمز عبور شما با موفقیت تغییر یافت.' ) ) ) );
                exit;

            case 'buyer_create_ticket':
                $res = self::create_ticket( $uid, $_POST );
                if ( $res['ok'] ) {
                    wp_safe_redirect( temsah_url( 'account/ticket-view', array( 'id' => $res['ticket_id'], 'ok' => rawurlencode( $res['message'] ) ) ) );
                } else {
                    wp_safe_redirect( temsah_url( 'account/ticket-new', array( 'err' => rawurlencode( $res['message'] ) ) ) );
                }
                exit;

            case 'buyer_reply_ticket':
                $tid = absint( $_POST['ticket_id'] ?? 0 );
                $res = self::reply_ticket( $uid, $tid, (string) ( $_POST['message'] ?? '' ) );
                wp_safe_redirect( temsah_url( 'account/ticket-view', array( 'id' => $tid, $res['ok'] ? 'ok' : 'err' => rawurlencode( $res['message'] ) ) ) );
                exit;

            case 'buyer_bind_license_domain':
                $lid    = absint( $_POST['license_id'] ?? 0 );
                $domain = sanitize_text_field( wp_unslash( $_POST['domain'] ?? '' ) );
                self::bind_license_domain( $uid, $lid, $domain );
                wp_safe_redirect( temsah_url( 'account/licenses', array( 'ok' => rawurlencode( 'دامنه مجوز ثبت شد.' ) ) ) );
                exit;

            case 'submit_product_feedback':
                $pid  = absint( $_POST['product_id'] ?? 0 );
                $res  = self::submit_feedback( $uid, $pid, $_POST );
                $prod = Temsah_CPT::get_product_by_id( $pid );
                $url  = $prod ? get_permalink( (int) $prod->post_id ) : home_url( '/' );
                $url  = add_query_arg( $res['ok'] ? 'fb_ok' : 'fb_err', rawurlencode( $res['message'] ), $url );
                wp_safe_redirect( $url );
                exit;
        }
    }

    private static function handle_login( array $post ): void {
        if ( ! Temsah_Security::check_rate_limit( 'login', 6, 900 ) ) {
            wp_safe_redirect( temsah_url( 'login', array( 'auth_err' => rawurlencode( 'تعداد تلاش‌های ناموفق بیش از حد مجاز بوده است. لطفاً ۱۵ دقیقه دیگر مجدداً تلاش کنید.' ) ) ) );
            exit;
        }
        $login    = sanitize_text_field( wp_unslash( $post['user_login'] ?? '' ) );
        $password = (string) ( $post['user_pass'] ?? '' );
        $remember = ! empty( $post['remember'] );

        if ( '' === $login || '' === $password ) {
            wp_safe_redirect( temsah_url( 'login', array( 'auth_err' => rawurlencode( 'لطفاً ایمیل یا نام کاربری و رمز عبور را وارد کنید.' ) ) ) );
            exit;
        }

        if ( is_email( $login ) ) {
            $u = get_user_by( 'email', $login );
            if ( $u ) {
                $login = $u->user_login;
            }
        }

        $user = wp_signon( array(
            'user_login'    => $login,
            'user_password' => $password,
            'remember'      => $remember,
        ), is_ssl() );

        if ( is_wp_error( $user ) ) {
            Temsah_Security::hit_rate_limit( 'login', 900 );
            Temsah_Security::log_activity( 'login_failed', 'auth', null, array( 'login' => $login ) );
            wp_safe_redirect( temsah_url( 'login', array( 'auth_err' => rawurlencode( 'نام کاربری یا رمز عبور نادرست است.' ) ) ) );
            exit;
        }

        Temsah_Security::reset_rate_limit( 'login' );
        Temsah_Security::log_activity( 'login_success', 'auth', (int) $user->ID, array(), (int) $user->ID );
        $redirect = ! empty( $post['redirect_to'] ) ? wp_validate_redirect( wp_unslash( $post['redirect_to'] ), temsah_url( 'account' ) ) : temsah_url( 'account' );
        wp_safe_redirect( $redirect );
        exit;
    }

    private static function handle_register( array $post ): void {
        if ( ! Temsah_Security::check_rate_limit( 'register', 5, 900 ) ) {
            wp_safe_redirect( temsah_url( 'register', array( 'auth_err' => rawurlencode( 'محدودیت ثبت‌نام موقتاً فعال شده است. لطفاً کمی بعد تلاش کنید.' ) ) ) );
            exit;
        }
        $full_name = sanitize_text_field( wp_unslash( $post['full_name'] ?? '' ) );
        $email     = sanitize_email( wp_unslash( $post['email'] ?? '' ) );
        $mobile    = sanitize_text_field( wp_unslash( $post['mobile'] ?? '' ) );
        $password  = (string) ( $post['password'] ?? '' );
        $terms     = ! empty( $post['accept_terms'] );

        if ( '' === $full_name || ! is_email( $email ) ) {
            wp_safe_redirect( temsah_url( 'register', array( 'auth_err' => rawurlencode( 'نام کامل و ایمیل معتبر الزامی است.' ) ) ) );
            exit;
        }
        if ( strlen( $password ) < 8 ) {
            wp_safe_redirect( temsah_url( 'register', array( 'auth_err' => rawurlencode( 'رمز عبور باید حداقل ۸ کاراکتر باشد.' ) ) ) );
            exit;
        }
        if ( ! $terms ) {
            wp_safe_redirect( temsah_url( 'register', array( 'auth_err' => rawurlencode( 'پذیرش قوانین تمساح الزامی است.' ) ) ) );
            exit;
        }
        if ( email_exists( $email ) ) {
            wp_safe_redirect( temsah_url( 'register', array( 'auth_err' => rawurlencode( 'حسابی با این ایمیل از قبل ثبت شده است.' ) ) ) );
            exit;
        }

        $username = sanitize_user( current( explode( '@', $email ) ), true );
        if ( '' === $username || username_exists( $username ) ) {
            $username = 'tm_' . wp_rand( 10000, 999999 );
        }

        $uid = wp_create_user( $username, $password, $email );
        if ( is_wp_error( $uid ) ) {
            wp_safe_redirect( temsah_url( 'register', array( 'auth_err' => rawurlencode( $uid->get_error_message() ) ) ) );
            exit;
        }

        $user = new WP_User( $uid );
        $user->set_role( 'temsah_buyer' );
        wp_update_user( array( 'ID' => $uid, 'display_name' => $full_name ) );
        if ( '' !== $mobile ) {
            update_user_meta( $uid, 'temsah_mobile', $mobile );
        }

        Temsah_Security::hit_rate_limit( 'register', 900 );
        wp_set_current_user( $uid );
        wp_set_auth_cookie( $uid, true );
        Temsah_Cart::merge_guest_cart_on_login( $user->user_login, $user );
        Temsah_Security::log_activity( 'user_registered', 'user', $uid, array(), $uid );

        wp_safe_redirect( temsah_url( 'account' ) );
        exit;
    }

    private static function handle_lost_password( array $post ): void {
        if ( ! Temsah_Security::check_rate_limit( 'lostpw', 5, 900 ) ) {
            wp_safe_redirect( temsah_url( 'lost-password', array( 'auth_err' => rawurlencode( 'درخواست‌های بازیابی رمز بیش از حد مجاز بوده است.' ) ) ) );
            exit;
        }
        Temsah_Security::hit_rate_limit( 'lostpw', 900 );
        $login = sanitize_text_field( wp_unslash( $post['user_login'] ?? '' ) );
        if ( '' !== $login ) {
            retrieve_password( $login );
        }
        // Always show uniform neutral response to avoid user enumeration
        wp_safe_redirect( temsah_url( 'lost-password', array( 'auth_ok' => rawurlencode( 'در صورت وجود حساب کاربری با این مشخصات، لینک بازیابی رمز عبور ارسال می‌شود.' ) ) ) );
        exit;
    }

    public static function create_ticket( int $uid, array $post ): array {
        global $wpdb;
        $subject    = sanitize_text_field( wp_unslash( $post['subject'] ?? '' ) );
        $message    = sanitize_textarea_field( wp_unslash( $post['message'] ?? '' ) );
        $product_id = absint( $post['product_id'] ?? 0 );
        $priority   = in_array( $post['priority'] ?? 'normal', array( 'low', 'normal', 'high' ), true ) ? $post['priority'] : 'normal';

        if ( '' === $subject || '' === $message ) {
            return array( 'ok' => false, 'message' => 'عنوان و متن پیام تیکت الزامی است.' );
        }

        $vendor_id = null;
        $order_id  = null;
        if ( $product_id > 0 ) {
            $purchase = Temsah_Security::user_has_purchased_product( $uid, $product_id );
            if ( ! $purchase ) {
                return array( 'ok' => false, 'message' => 'برای ارسال تیکت پشتیبانی محصول، ابتدا باید آن را خریداری کرده باشید.' );
            }
            $prod      = Temsah_CPT::get_product_by_id( $product_id );
            $vendor_id = $prod ? (int) $prod->vendor_id : null;
            $order_id  = (int) $purchase->order_id;
        }

        $now  = current_time( 'mysql' );
        $code = 'TK-' . wp_rand( 10000, 99999 );

        $wpdb->insert(
            Temsah_DB::table( 'tickets' ),
            array(
                'ticket_code' => $code,
                'user_id'     => $uid,
                'vendor_id'   => $vendor_id,
                'product_id'  => $product_id ?: null,
                'order_id'    => $order_id,
                'subject'     => $subject,
                'priority'    => $priority,
                'status'      => 'open',
                'created_at'  => $now,
                'updated_at'  => $now,
            ),
            array( '%s', '%d', '%d', '%d', '%d', '%s', '%s', '%s', '%s', '%s' )
        );
        $tid = (int) $wpdb->insert_id;

        $wpdb->insert(
            Temsah_DB::table( 'ticket_messages' ),
            array(
                'ticket_id'   => $tid,
                'sender_id'   => $uid,
                'sender_role' => 'buyer',
                'message'     => $message,
                'created_at'  => $now,
            ),
            array( '%d', '%d', '%s', '%s', '%s' )
        );

        Temsah_Security::log_activity( 'ticket_created', 'ticket', $tid, array( 'product_id' => $product_id ), $uid );
        return array( 'ok' => true, 'ticket_id' => $tid, 'message' => 'تیکت جدید با کد ' . $code . ' ثبت شد.' );
    }

    public static function reply_ticket( int $uid, int $ticket_id, string $message ): array {
        global $wpdb;
        $ticket = Temsah_Security::user_can_access_ticket( $ticket_id, 'buyer', $uid );
        if ( ! $ticket ) {
            return array( 'ok' => false, 'message' => 'تیکت یافت نشد.' );
        }
        $clean = sanitize_textarea_field( wp_unslash( $message ) );
        if ( '' === $clean ) {
            return array( 'ok' => false, 'message' => 'متن پاسخ نمی‌تواند خالی باشد.' );
        }
        $now = current_time( 'mysql' );
        $wpdb->insert(
            Temsah_DB::table( 'ticket_messages' ),
            array(
                'ticket_id'   => $ticket_id,
                'sender_id'   => $uid,
                'sender_role' => 'buyer',
                'message'     => $clean,
                'created_at'  => $now,
            ),
            array( '%d', '%d', '%s', '%s', '%s' )
        );
        $wpdb->update( Temsah_DB::table( 'tickets' ), array( 'status' => 'buyer_replied', 'updated_at' => $now ), array( 'id' => $ticket_id ) );
        return array( 'ok' => true, 'message' => 'پاسخ شما در تیکت ثبت شد.' );
    }

    public static function bind_license_domain( int $uid, int $license_id, string $domain ): void {
        global $wpdb;
        $t_lic = Temsah_DB::table( 'licenses' );
        $lic   = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$t_lic} WHERE id = %d AND user_id = %d LIMIT 1", $license_id, $uid ) );
        if ( ! $lic || 'active' !== $lic->status ) {
            return;
        }
        $clean_domain = strtolower( preg_replace( '#^https?://#', '', trim( $domain ) ) );
        $clean_domain = explode( '/', $clean_domain )[0];
        $wpdb->update( $t_lic, array( 'domain' => sanitize_text_field( $clean_domain ) ), array( 'id' => $license_id ) );
    }

    public static function submit_feedback( int $uid, int $product_id, array $post ): array {
        global $wpdb;
        $kind    = in_array( $post['kind'] ?? 'question', array( 'question', 'review' ), true ) ? $post['kind'] : 'question';
        $content = sanitize_textarea_field( wp_unslash( $post['message'] ?? '' ) );
        $rating  = ( 'review' === $kind ) ? max( 1, min( 5, absint( $post['rating'] ?? 5 ) ) ) : null;

        if ( '' === $content ) {
            return array( 'ok' => false, 'message' => 'متن نمی‌تواند خالی باشد.' );
        }

        $purchased = (bool) Temsah_Security::user_has_purchased_product( $uid, $product_id );
        if ( 'review' === $kind && ! $purchased ) {
            return array( 'ok' => false, 'message' => 'ثبت امتیاز و دیدگاه خریدار فقط برای خریداران تأییدشده محصول امکان‌پذیر است.' );
        }

        $t_fb = Temsah_DB::table( 'product_feedback' );
        $wpdb->insert(
            $t_fb,
            array(
                'product_id'        => $product_id,
                'user_id'           => $uid,
                'kind'              => $kind,
                'rating'            => $rating,
                'content'           => $content,
                'is_verified_buyer' => $purchased ? 1 : 0,
                'status'            => 'approved',
                'created_at'        => current_time( 'mysql' ),
            ),
            array( '%d', '%d', '%s', '%d', '%s', '%d', '%s', '%s' )
        );

        if ( 'review' === $kind ) {
            $stats = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT AVG(rating) AS avg_r, COUNT(*) AS cnt FROM {$t_fb} WHERE product_id = %d AND kind = 'review' AND status = 'approved'",
                    $product_id
                )
            );
            $wpdb->update(
                Temsah_DB::table( 'products' ),
                array(
                    'rating_avg'   => round( (float) ( $stats->avg_r ?? 0 ), 2 ),
                    'rating_count' => (int) ( $stats->cnt ?? 0 ),
                ),
                array( 'id' => $product_id )
            );
        }

        return array( 'ok' => true, 'message' => 'review' === $kind ? 'دیدگاه شما ثبت شد.' : 'پرسش شما با موفقیت ثبت شد.' );
    }

    public static function get_wishlist_product_ids( int $uid ): array {
        global $wpdb;
        if ( $uid <= 0 ) {
            return array();
        }
        return array_map(
            'intval',
            $wpdb->get_col( $wpdb->prepare( 'SELECT product_id FROM ' . Temsah_DB::table( 'wishlists' ) . ' WHERE user_id = %d ORDER BY id DESC', $uid ) )
        );
    }

    public static function toggle_wishlist( int $uid, int $product_id ): array {
        global $wpdb;
        $t_w = Temsah_DB::table( 'wishlists' );
        $exists = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$t_w} WHERE user_id = %d AND product_id = %d", $uid, $product_id ) );
        if ( $exists ) {
            $wpdb->delete( $t_w, array( 'id' => $exists ) );
            return array( 'ok' => true, 'in_wishlist' => false, 'message' => 'از علاقه‌مندی‌ها حذف شد.' );
        }
        $wpdb->insert( $t_w, array( 'user_id' => $uid, 'product_id' => $product_id, 'created_at' => current_time( 'mysql' ) ), array( '%d', '%d', '%s' ) );
        return array( 'ok' => true, 'in_wishlist' => true, 'message' => 'به علاقه‌مندی‌ها اضافه شد.' );
    }
}
