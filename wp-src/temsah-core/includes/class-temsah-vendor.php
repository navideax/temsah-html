<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Temsah_Vendor {
    public static function init(): void {
        add_action( 'template_redirect', array( __CLASS__, 'handle_post_actions' ), 6 );
    }

    public static function handle_post_actions(): void {
        if ( empty( $_POST['temsah_action'] ) ) {
            return;
        }
        $action = sanitize_key( wp_unslash( $_POST['temsah_action'] ) );

        $vendor_actions = array(
            'submit_seller_application',
            'vendor_save_product',
            'vendor_save_store_settings',
            'vendor_request_withdrawal',
            'vendor_save_reply_template',
            'vendor_delete_reply_template',
            'vendor_reply_ticket',
            'vendor_reply_question',
            'vendor_export_report',
        );
        if ( ! in_array( $action, $vendor_actions, true ) ) {
            return;
        }

        Temsah_Security::enforce_private_headers();
        if ( ! is_user_logged_in() ) {
            Temsah_Security::redirect_to_login();
        }
        Temsah_Security::verify_nonce_or_die( 'temsah_' . $action );

        $uid = get_current_user_id();

        if ( 'submit_seller_application' === $action ) {
            $res = self::process_seller_application( $uid, $_POST, $_FILES );
            $msg_key = $res['ok'] ? 'app_ok' : 'app_err';
            wp_safe_redirect( temsah_url( $res['ok'] ? 'account/seller-application-status' : 'account/seller-application', array( $msg_key => rawurlencode( $res['message'] ) ) ) );
            exit;
        }

        // All remaining actions require approved vendor capability
        if ( ! Temsah_Security::is_approved_vendor( $uid ) ) {
            wp_die( 'این عملیات فقط برای فروشندگان تأییدشده مجاز است.', 'عدم دسترسی', array( 'response' => 403 ) );
        }
        $vendor = Temsah_Security::get_vendor_by_user( $uid );
        if ( ! $vendor && current_user_can( 'temsah_manage_marketplace' ) ) {
            // Admin testing without vendor row: reject cleanly with clear hint
            wp_die( 'حساب مدیر کل ردیف فروشنده متصل ندارد؛ لطفاً با حساب فروشنده یا از پیشخوان اقدام کنید.', 'اطلاع', array( 'response' => 400 ) );
        }

        switch ( $action ) {
            case 'vendor_save_product':
                $res = self::save_vendor_product( $vendor, $_POST, $_FILES );
                $target = ! empty( $res['product_id'] )
                    ? temsah_url( 'vendor/edit-product', array( 'id' => $res['product_id'], $res['ok'] ? 'ok' : 'err' => rawurlencode( $res['message'] ) ) )
                    : temsah_url( 'vendor/new-product', array( 'err' => rawurlencode( $res['message'] ) ) );
                wp_safe_redirect( $target );
                exit;

            case 'vendor_save_store_settings':
                self::update_store_settings( $vendor, $_POST );
                wp_safe_redirect( temsah_url( 'vendor/store-settings', array( 'ok' => rawurlencode( 'تنظیمات فروشگاه با موفقیت ذخیره شد.' ) ) ) );
                exit;

            case 'vendor_request_withdrawal':
                $res = self::request_withdrawal( $vendor, $_POST );
                wp_safe_redirect( temsah_url( 'vendor/withdrawals', array( $res['ok'] ? 'ok' : 'err' => rawurlencode( $res['message'] ) ) ) );
                exit;

            case 'vendor_save_reply_template':
                self::save_reply_template( $vendor, $_POST );
                wp_safe_redirect( temsah_url( 'vendor/ready-replies', array( 'ok' => rawurlencode( 'پاسخ آماده ذخیره شد.' ) ) ) );
                exit;

            case 'vendor_delete_reply_template':
                self::delete_reply_template( $vendor, absint( $_POST['template_id'] ?? 0 ) );
                wp_safe_redirect( temsah_url( 'vendor/ready-replies', array( 'ok' => rawurlencode( 'پاسخ آماده حذف شد.' ) ) ) );
                exit;

            case 'vendor_reply_ticket':
                $tid = absint( $_POST['ticket_id'] ?? 0 );
                $res = self::reply_ticket( $vendor, $uid, $tid, (string) ( $_POST['message'] ?? '' ) );
                wp_safe_redirect( temsah_url( 'vendor/ticket-view', array( 'id' => $tid, $res['ok'] ? 'ok' : 'err' => rawurlencode( $res['message'] ) ) ) );
                exit;

            case 'vendor_reply_question':
                $qid = absint( $_POST['feedback_id'] ?? 0 );
                self::reply_question( $vendor, $qid, (string) ( $_POST['reply'] ?? '' ) );
                wp_safe_redirect( temsah_url( 'vendor/questions', array( 'ok' => rawurlencode( 'پاسخ شما ثبت شد.' ) ) ) );
                exit;

            case 'vendor_export_report':
                self::stream_vendor_csv( $vendor );
                exit;
        }
    }

    public static function validate_national_id( string $code ): bool {
        $code = preg_replace( '/\D/', '', $code );
        if ( 10 !== strlen( $code ) || preg_match( '/^(\d)\1{9}$/', $code ) ) {
            return false;
        }
        $sum = 0;
        for ( $i = 0; $i < 9; $i++ ) {
            $sum += (int) $code[ $i ] * ( 10 - $i );
        }
        $rem   = $sum % 11;
        $check = (int) $code[9];
        return ( $rem < 2 && $check === $rem ) || ( $rem >= 2 && $check === ( 11 - $rem ) );
    }

    public static function process_seller_application( int $user_id, array $post, array $files ): array {
        global $wpdb;
        $full_name   = sanitize_text_field( wp_unslash( $post['full_name'] ?? '' ) );
        $mobile      = preg_replace( '/\s+/', '', sanitize_text_field( wp_unslash( $post['mobile'] ?? '' ) ) );
        $national_id = preg_replace( '/\D/', '', sanitize_text_field( wp_unslash( $post['national_id'] ?? '' ) ) );
        $birth_date  = sanitize_text_field( wp_unslash( $post['birth_date'] ?? '' ) );
        $iban        = strtoupper( preg_replace( '/\s+/', '', sanitize_text_field( wp_unslash( $post['iban'] ?? '' ) ) ) );
        $store_name  = sanitize_text_field( wp_unslash( $post['store_name'] ?? '' ) );
        $specialty   = sanitize_text_field( wp_unslash( $post['specialty'] ?? '' ) );
        $bio         = sanitize_textarea_field( wp_unslash( $post['bio'] ?? '' ) );
        $portfolio   = esc_url_raw( wp_unslash( $post['portfolio_url'] ?? '' ) );
        $agreed      = ! empty( $post['agreement_accepted'] );

        if ( '' === $full_name || '' === $mobile || '' === $store_name ) {
            return array( 'ok' => false, 'message' => 'نام کامل، شماره موبایل و نام فروشگاه الزامی است.' );
        }
        if ( ! self::validate_national_id( $national_id ) ) {
            return array( 'ok' => false, 'message' => 'کد ملی ۱۰ رقمی معتبر نیست.' );
        }
        if ( ! preg_match( '/^IR[0-9]{24}$/', $iban ) ) {
            return array( 'ok' => false, 'message' => 'شماره شبا باید با IR شروع شود و ۲۴ رقم داشته باشد.' );
        }
        if ( ! $agreed ) {
            return array( 'ok' => false, 'message' => 'تایید توافق‌نامه فروشندگان برای ارسال درخواست الزامی است.' );
        }

        $doc_path = '';
        if ( ! empty( $files['id_document']['name'] ) ) {
            $up = Temsah_Security::store_vault_file( $files['id_document'], 'kyc', array( 'jpg', 'jpeg', 'png', 'pdf' ) );
            if ( is_wp_error( $up ) ) {
                return array( 'ok' => false, 'message' => $up->get_error_message() );
            }
            $doc_path = $up['storage_path'];
        }

        $t_v    = Temsah_DB::table( 'vendors' );
        $t_ver  = Temsah_DB::table( 'vendor_verifications' );
        $now    = current_time( 'mysql' );
        $vendor = self::get_or_init_vendor_record( $user_id, $store_name, $specialty, $bio, $portfolio );

        $wpdb->update(
            $t_v,
            array(
                'store_name'    => $store_name,
                'specialty'     => $specialty,
                'bio'           => $bio,
                'portfolio_url' => $portfolio,
                'status'        => ( 'approved' === $vendor->status ) ? 'approved' : 'pending',
                'updated_at'    => $now,
            ),
            array( 'id' => $vendor->id )
        );

        $existing_ver = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$t_ver} WHERE vendor_id = %d ORDER BY id DESC LIMIT 1", $vendor->id ) );
        $ver_data = array(
            'vendor_id'             => (int) $vendor->id,
            'user_id'               => $user_id,
            'full_name'             => $full_name,
            'mobile'                => $mobile,
            'national_id'           => $national_id,
            'birth_date'            => $birth_date,
            'iban'                  => $iban,
            'id_document_path'      => $doc_path ?: ( $existing_ver->id_document_path ?? '' ),
            'agreement_accepted'    => 1,
            'agreement_accepted_at' => $now,
            'review_status'         => 'pending',
            'rejection_reason'      => null,
            'updated_at'            => $now,
        );
        if ( $existing_ver ) {
            $wpdb->update( $t_ver, $ver_data, array( 'id' => $existing_ver->id ) );
        } else {
            $ver_data['created_at'] = $now;
            $wpdb->insert( $t_ver, $ver_data );
        }

        Temsah_Security::log_activity( 'vendor_application_submitted', 'vendor', (int) $vendor->id, array( 'store_name' => $store_name ), $user_id );
        return array( 'ok' => true, 'message' => 'درخواست فروشندگی و مدارک هویتی شما ثبت شد و در صف بررسی مدیر قرار گرفت.' );
    }

    private static function get_or_init_vendor_record( int $user_id, string $store_name, string $specialty, string $bio, string $portfolio ): object {
        global $wpdb;
        $t_v      = Temsah_DB::table( 'vendors' );
        $existing = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$t_v} WHERE user_id = %d LIMIT 1", $user_id ) );
        if ( $existing ) {
            return $existing;
        }
        $settings = get_option( 'temsah_settings', array() );
        $slug     = sanitize_title( $store_name ) ?: 'vendor-' . $user_id;
        if ( $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$t_v} WHERE store_slug = %s", $slug ) ) ) {
            $slug .= '-' . $user_id;
        }
        $now = current_time( 'mysql' );
        $wpdb->insert(
            $t_v,
            array(
                'user_id'         => $user_id,
                'store_name'      => $store_name,
                'store_slug'      => $slug,
                'specialty'       => $specialty,
                'bio'             => $bio,
                'portfolio_url'   => $portfolio,
                'commission_rate' => (float) ( $settings['default_commission_rate'] ?? 70 ),
                'status'          => 'pending',
                'created_at'      => $now,
                'updated_at'      => $now,
            ),
            array( '%d', '%s', '%s', '%s', '%s', '%s', '%f', '%s', '%s', '%s' )
        );
        return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$t_v} WHERE id = %d", (int) $wpdb->insert_id ) );
    }

    public static function admin_review_vendor( int $vendor_id, string $decision, string $reason = '', ?int $admin_id = null ): bool {
        global $wpdb;
        $t_v   = Temsah_DB::table( 'vendors' );
        $t_ver = Temsah_DB::table( 'vendor_verifications' );
        $v     = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$t_v} WHERE id = %d", $vendor_id ) );
        if ( ! $v ) {
            return false;
        }
        $now     = current_time( 'mysql' );
        $reviewer = $admin_id ?? get_current_user_id();
        $approved = ( 'approve' === $decision );

        $wpdb->update(
            $t_v,
            array( 'status' => $approved ? 'approved' : 'rejected', 'updated_at' => $now ),
            array( 'id' => $vendor_id )
        );
        $wpdb->update(
            $t_ver,
            array(
                'review_status'    => $approved ? 'approved' : 'rejected',
                'rejection_reason' => $approved ? null : sanitize_textarea_field( $reason ),
                'reviewed_by'      => $reviewer,
                'reviewed_at'      => $now,
                'updated_at'       => $now,
            ),
            array( 'vendor_id' => $vendor_id )
        );

        $user = get_user_by( 'id', (int) $v->user_id );
        if ( $user && $approved && ! user_can( $user, 'manage_options' ) ) {
            $user->add_role( 'temsah_vendor' );
        }

        $wpdb->insert(
            Temsah_DB::table( 'notifications' ),
            array(
                'user_id'    => (int) $v->user_id,
                'audience'   => $approved ? 'vendor' : 'buyer',
                'title'      => $approved ? 'درخواست فروشندگی شما تایید شد' : 'نیاز به بازبینی در درخواست فروشندگی',
                'body'       => $approved
                    ? 'حساب فروشندگی «' . $v->store_name . '» تایید شد. اکنون می‌توانید محصولات خود را از پنل فروشنده منتشر کنید.'
                    : ( 'درخواست شما تایید نشد. دلیل: ' . ( $reason ?: 'لطفاً مدارک خود را بررسی و مجدداً ارسال کنید.' ) ),
                'created_at' => $now,
            ),
            array( '%d', '%s', '%s', '%s', '%s' )
        );

        Temsah_Security::log_activity( $approved ? 'vendor_approved' : 'vendor_rejected', 'vendor', $vendor_id, array( 'reason' => $reason ), $reviewer );
        return true;
    }

    public static function save_vendor_product( object $vendor, array $post, array $files ): array {
        global $wpdb;
        $product_id    = absint( $post['product_id'] ?? 0 );
        $title         = sanitize_text_field( wp_unslash( $post['title'] ?? '' ) );
        $excerpt       = sanitize_text_field( wp_unslash( $post['excerpt'] ?? '' ) );
        $content       = wp_kses_post( wp_unslash( $post['content'] ?? '' ) );
        $price         = absint( $post['price'] ?? 0 );
        $sale_price    = '' !== trim( (string) ( $post['sale_price'] ?? '' ) ) ? absint( $post['sale_price'] ) : null;
        $category      = sanitize_title( wp_unslash( $post['category'] ?? 'wordpress' ) );
        $version       = sanitize_text_field( wp_unslash( $post['version'] ?? '1.0.0' ) );
        $changelog     = sanitize_textarea_field( wp_unslash( $post['changelog'] ?? '' ) );
        $compatibility = sanitize_text_field( wp_unslash( $post['compatibility'] ?? '' ) );
        $demo_url      = esc_url_raw( wp_unslash( $post['demo_url'] ?? '' ) );
        $cover_style   = sanitize_key( wp_unslash( $post['cover_style'] ?? 'cover-store' ) );
        $submit_mode   = sanitize_key( wp_unslash( $post['submit_mode'] ?? 'pending_review' ) );
        $new_status    = in_array( $submit_mode, array( 'draft', 'pending_review' ), true ) ? $submit_mode : 'pending_review';

        if ( '' === $title ) {
            return array( 'ok' => false, 'message' => 'عنوان محصول الزامی است.', 'product_id' => $product_id );
        }
        if ( null !== $sale_price && $sale_price >= $price ) {
            $sale_price = null;
        }

        $t_p  = Temsah_DB::table( 'products' );
        $now  = current_time( 'mysql' );
        $existing = null;

        if ( $product_id > 0 ) {
            $existing = Temsah_Security::user_owns_product( $product_id, (int) $vendor->user_id );
            if ( ! $existing ) {
                return array( 'ok' => false, 'message' => 'دسترسی به این محصول مجاز نیست.' );
            }
            $post_id = (int) $existing->post_id;
            wp_update_post( array(
                'ID'           => $post_id,
                'post_title'   => $title,
                'post_excerpt' => $excerpt,
                'post_content' => $content,
                'post_status'  => ( 'published' === $existing->status && 'draft' !== $new_status ) ? 'publish' : 'pending',
            ) );
        } else {
            $post_id = wp_insert_post( array(
                'post_type'    => 'temsah_product',
                'post_title'   => $title,
                'post_excerpt' => $excerpt,
                'post_content' => $content,
                'post_status'  => 'pending',
                'post_author'  => (int) $vendor->user_id,
            ) );
            if ( is_wp_error( $post_id ) || ! $post_id ) {
                return array( 'ok' => false, 'message' => 'خطا در ثبت رکورد محصول.' );
            }
        }

        wp_set_object_terms( $post_id, array( $category ), 'temsah_product_cat' );

        $keep_published = ( $existing && 'published' === $existing->status && 'draft' !== $new_status );
        $final_status   = $keep_published ? 'published' : $new_status;

        $pdata = array(
            'post_id'         => $post_id,
            'vendor_id'       => (int) $vendor->id,
            'price'           => $price,
            'sale_price'      => $sale_price,
            'current_version' => $version ?: '1.0.0',
            'compatibility'   => $compatibility,
            'demo_url'        => $demo_url,
            'cover_style'     => $cover_style ?: 'cover-store',
            'status'          => $final_status,
            'updated_at'      => $now,
        );

        if ( $existing ) {
            $wpdb->update( $t_p, $pdata, array( 'id' => $existing->id ) );
        } else {
            $pdata['created_at'] = $now;
            $wpdb->insert( $t_p, $pdata );
            $product_id = (int) $wpdb->insert_id;
        }

        if ( '' !== $changelog ) {
            $wpdb->insert(
                Temsah_DB::table( 'product_versions' ),
                array(
                    'product_id'    => $product_id,
                    'version'       => $version ?: '1.0.0',
                    'changelog'     => $changelog,
                    'compatibility' => $compatibility,
                    'created_at'    => $now,
                ),
                array( '%d', '%s', '%s', '%s', '%s' )
            );
        }

        // Upload protected product archive if provided
        if ( ! empty( $files['product_file']['name'] ) ) {
            $up = Temsah_Security::store_vault_file( $files['product_file'], 'products/' . $product_id, array( 'zip', 'rar', '7z', 'gz', 'pdf' ) );
            if ( is_wp_error( $up ) ) {
                return array( 'ok' => false, 'message' => 'اطلاعات ذخیره شد ولی آپلود فایل خطا داد: ' . $up->get_error_message(), 'product_id' => $product_id );
            }
            $wpdb->update( Temsah_DB::table( 'product_files' ), array( 'is_active' => 0 ), array( 'product_id' => $product_id ) );
            $wpdb->insert(
                Temsah_DB::table( 'product_files' ),
                array(
                    'product_id'      => $product_id,
                    'file_label'      => 'نسخه ' . ( $version ?: '1.0.0' ),
                    'file_name'       => $up['file_name'],
                    'storage_path'    => $up['storage_path'],
                    'file_size'       => $up['file_size'],
                    'checksum_sha256' => $up['checksum_sha256'],
                    'is_active'       => 1,
                    'created_at'      => $now,
                ),
                array( '%d', '%s', '%s', '%s', '%d', '%s', '%d', '%s' )
            );
        }

        Temsah_Security::log_activity( 'vendor_product_saved', 'product', $product_id, array( 'status' => $final_status ), (int) $vendor->user_id );
        return array(
            'ok'         => true,
            'product_id' => $product_id,
            'message'    => 'draft' === $final_status ? 'محصول به عنوان پیش‌نویس ذخیره شد.' : 'محصول ثبت شد و در انتظار تایید مدیر یا انتشار است.',
        );
    }

    public static function update_store_settings( object $vendor, array $post ): void {
        global $wpdb;
        $wpdb->update(
            Temsah_DB::table( 'vendors' ),
            array(
                'store_name'    => sanitize_text_field( wp_unslash( $post['store_name'] ?? $vendor->store_name ) ),
                'specialty'     => sanitize_text_field( wp_unslash( $post['specialty'] ?? $vendor->specialty ) ),
                'bio'           => sanitize_textarea_field( wp_unslash( $post['bio'] ?? $vendor->bio ) ),
                'portfolio_url' => esc_url_raw( wp_unslash( $post['portfolio_url'] ?? $vendor->portfolio_url ) ),
                'support_email' => sanitize_email( wp_unslash( $post['support_email'] ?? $vendor->support_email ) ),
                'support_hours' => sanitize_text_field( wp_unslash( $post['support_hours'] ?? $vendor->support_hours ) ),
                'updated_at'    => current_time( 'mysql' ),
            ),
            array( 'id' => $vendor->id )
        );
    }

    public static function request_withdrawal( object $vendor, array $post ): array {
        global $wpdb;
        $amount   = absint( $post['amount'] ?? 0 );
        $iban     = strtoupper( preg_replace( '/\s+/', '', sanitize_text_field( wp_unslash( $post['iban'] ?? '' ) ) ) );
        $settings = get_option( 'temsah_settings', array() );
        $min      = (int) ( $settings['min_withdrawal_amount'] ?? 100000 );

        if ( $amount < $min ) {
            return array( 'ok' => false, 'message' => 'حداقل مبلغ برداشت ' . temsah_format_toman( $min ) . ' است.' );
        }
        if ( $amount > (int) $vendor->available_balance ) {
            return array( 'ok' => false, 'message' => 'مبلغ درخواستی از موجودی قابل برداشت شما بیشتر است.' );
        }
        if ( ! preg_match( '/^IR[0-9]{24}$/', $iban ) ) {
            return array( 'ok' => false, 'message' => 'شماره شبا معتبر نیست (IR و ۲۴ رقم).' );
        }

        $now       = current_time( 'mysql' );
        $new_avail = (int) $vendor->available_balance - $amount;

        $wpdb->insert(
            Temsah_DB::table( 'withdrawals' ),
            array(
                'vendor_id'  => (int) $vendor->id,
                'user_id'    => (int) $vendor->user_id,
                'amount'     => $amount,
                'iban'       => $iban,
                'status'     => 'pending',
                'created_at' => $now,
            ),
            array( '%d', '%d', '%d', '%s', '%s', '%s' )
        );
        $wid = (int) $wpdb->insert_id;

        $wpdb->update(
            Temsah_DB::table( 'vendors' ),
            array( 'available_balance' => $new_avail, 'updated_at' => $now ),
            array( 'id' => $vendor->id )
        );

        $wpdb->insert(
            Temsah_DB::table( 'vendor_wallet_ledger' ),
            array(
                'vendor_id'     => (int) $vendor->id,
                'entry_type'    => 'debit_withdrawal',
                'amount'        => -$amount,
                'balance_after' => $new_avail,
                'reference_id'  => $wid,
                'note'          => 'درخواست تسویه حساب به شبا ' . $iban,
                'created_at'    => $now,
            ),
            array( '%d', '%s', '%d', '%d', '%d', '%s', '%s' )
        );

        Temsah_Security::log_activity( 'withdrawal_requested', 'withdrawal', $wid, array( 'amount' => $amount ), (int) $vendor->user_id );
        return array( 'ok' => true, 'withdrawal_id' => $wid, 'message' => 'درخواست برداشت ثبت شد و مبلغ از موجودی قابل برداشت رزرو گردید.' );
    }

    public static function admin_review_withdrawal( int $wid, string $decision, string $note = '', int $admin_id = 0 ): bool {
        global $wpdb;
        $status = 'paid' === $decision ? 'paid' : 'rejected';
        $w      = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . Temsah_DB::table( 'withdrawals' ) . ' WHERE id = %d', $wid ) );
        if ( ! $w || 'pending' !== $w->status ) {
            return false;
        }
        $now = current_time( 'mysql' );
        $wpdb->update(
            Temsah_DB::table( 'withdrawals' ),
            array(
                'status'      => $status,
                'admin_note'  => sanitize_textarea_field( $note ),
                'reviewed_by' => $admin_id ?: get_current_user_id(),
                'reviewed_at' => $now,
            ),
            array( 'id' => $wid )
        );
        if ( 'rejected' === $status ) {
            $vendor = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . Temsah_DB::table( 'vendors' ) . ' WHERE id = %d', $w->vendor_id ) );
            if ( $vendor ) {
                $new_bal = (int) $vendor->available_balance + (int) $w->amount;
                $wpdb->update( Temsah_DB::table( 'vendors' ), array( 'available_balance' => $new_bal, 'updated_at' => $now ), array( 'id' => $vendor->id ) );
                $wpdb->insert(
                    Temsah_DB::table( 'vendor_wallet_ledger' ),
                    array(
                        'vendor_id'     => (int) $vendor->id,
                        'entry_type'    => 'credit_reversal',
                        'amount'        => (int) $w->amount,
                        'balance_after' => $new_bal,
                        'reference_id'  => $wid,
                        'note'          => 'برگشت مبلغ درخواست برداشت ردشده #' . $wid,
                        'created_at'    => $now,
                    ),
                    array( '%d', '%s', '%d', '%d', '%d', '%s', '%s' )
                );
            }
        }
        Temsah_Security::log_activity( 'admin_withdrawal_' . $status, 'withdrawal', $wid, array( 'note' => $note ), $admin_id ?: get_current_user_id() );
        return true;
    }

    public static function save_reply_template( object $vendor, array $post ): void {
        global $wpdb;
        $wpdb->insert(
            Temsah_DB::table( 'vendor_reply_templates' ),
            array(
                'vendor_id'  => (int) $vendor->id,
                'title'      => sanitize_text_field( wp_unslash( $post['title'] ?? '' ) ),
                'body'       => sanitize_textarea_field( wp_unslash( $post['body'] ?? '' ) ),
                'created_at' => current_time( 'mysql' ),
            ),
            array( '%d', '%s', '%s', '%s' )
        );
    }

    public static function delete_reply_template( object $vendor, int $tid ): void {
        global $wpdb;
        $wpdb->delete( Temsah_DB::table( 'vendor_reply_templates' ), array( 'id' => $tid, 'vendor_id' => $vendor->id ) );
    }

    public static function reply_ticket( object $vendor, int $uid, int $ticket_id, string $message ): array {
        global $wpdb;
        $ticket = Temsah_Security::user_can_access_ticket( $ticket_id, 'vendor', $uid );
        if ( ! $ticket ) {
            return array( 'ok' => false, 'message' => 'تیکت یافت نشد یا متعلق به فروشگاه شما نیست.' );
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
                'sender_role' => 'vendor',
                'message'     => $clean,
                'created_at'  => $now,
            ),
            array( '%d', '%d', '%s', '%s', '%s' )
        );
        $wpdb->update( Temsah_DB::table( 'tickets' ), array( 'status' => 'vendor_replied', 'updated_at' => $now ), array( 'id' => $ticket_id ) );
        return array( 'ok' => true, 'message' => 'پاسخ شما به خریدار ارسال شد.' );
    }

    public static function reply_question( object $vendor, int $feedback_id, string $reply ): void {
        global $wpdb;
        $t_fb = Temsah_DB::table( 'product_feedback' );
        $fb   = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$t_fb} WHERE id = %d", $feedback_id ) );
        if ( ! $fb ) {
            return;
        }
        if ( ! Temsah_Security::user_owns_product( (int) $fb->product_id, (int) $vendor->user_id ) ) {
            return;
        }
        $wpdb->update(
            $t_fb,
            array(
                'vendor_reply'      => sanitize_textarea_field( wp_unslash( $reply ) ),
                'vendor_replied_at' => current_time( 'mysql' ),
            ),
            array( 'id' => $feedback_id )
        );
    }

    public static function stream_vendor_csv( object $vendor ): void {
        global $wpdb;
        $t_c  = Temsah_DB::table( 'commissions' );
        $t_oi = Temsah_DB::table( 'order_items' );
        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT c.id, c.order_id, oi.product_title, c.gross_amount, c.vendor_rate, c.vendor_amount, c.platform_amount, c.created_at
                 FROM {$t_c} c
                 INNER JOIN {$t_oi} oi ON oi.id = c.order_item_id
                 WHERE c.vendor_id = %d
                 ORDER BY c.id DESC",
                $vendor->id
            )
        );
        Temsah_Security::enforce_private_headers();
        header( 'X-Content-Type-Options: nosniff' );
        header( 'Content-Type: text/csv; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename="temsah-vendor-sales-' . (int) $vendor->id . '.csv"' );
        echo "\xEF\xBB\xBF";
        $out = fopen( 'php://output', 'w' );
        $safe_cell = static function ( $val ): string {
            $s = (string) $val;
            return preg_match( '/^[=+\-@\t\r]/', $s ) ? "'" . $s : $s;
        };
        fputcsv( $out, array( 'ID', 'OrderID', 'Product', 'Gross', 'Rate%', 'VendorAmount', 'PlatformFee', 'Date' ) );
        foreach ( $rows as $r ) {
            fputcsv( $out, array( $r->id, $r->order_id, $safe_cell( $r->product_title ), $r->gross_amount, $r->vendor_rate, $r->vendor_amount, $r->platform_amount, $r->created_at ) );
        }
        fclose( $out );
        exit;
    }
}
