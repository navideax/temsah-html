<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Temsah_Admin {
    public static function init(): void {
        add_action( 'admin_menu', array( __CLASS__, 'register_menus' ) );
        add_action( 'admin_post_temsah_admin_action', array( __CLASS__, 'handle_admin_action' ) );
        add_action( 'admin_head', array( __CLASS__, 'admin_styles' ) );
    }

    public static function register_menus(): void {
        $cap = 'temsah_manage_marketplace';
        add_menu_page( 'مارکت‌پلیس تمساح', 'تمساح', $cap, 'temsah-dashboard', array( __CLASS__, 'render_dashboard' ), 'dashicons-store', 3 );
        add_submenu_page( 'temsah-dashboard', 'داشبورد تمساح', 'داشبورد', $cap, 'temsah-dashboard', array( __CLASS__, 'render_dashboard' ) );
        add_submenu_page( 'temsah-dashboard', 'محصولات و بررسی‌ها', 'محصولات', $cap, 'temsah-products', array( __CLASS__, 'render_products' ) );
        add_submenu_page( 'temsah-dashboard', 'دسته‌بندی‌های محصول', 'دسته‌بندی‌ها', $cap, 'edit-tags.php?taxonomy=temsah_product_cat&post_type=temsah_product' );
        add_submenu_page( 'temsah-dashboard', 'فروشندگان و احراز هویت', 'فروشندگان', $cap, 'temsah-vendors', array( __CLASS__, 'render_vendors' ) );
        add_submenu_page( 'temsah-dashboard', 'سفارش‌ها و پرداخت‌ها', 'سفارش‌ها', $cap, 'temsah-orders', array( __CLASS__, 'render_orders' ) );
        add_submenu_page( 'temsah-dashboard', 'کمیسیون‌ها و تراکنش‌ها', 'تراکنش‌ها و کمیسیون', $cap, 'temsah-finance', array( __CLASS__, 'render_finance' ) );
        add_submenu_page( 'temsah-dashboard', 'درخواست‌های برداشت', 'برداشت‌ها', $cap, 'temsah-withdrawals', array( __CLASS__, 'render_withdrawals' ) );
        add_submenu_page( 'temsah-dashboard', 'کوپن‌ها و تخفیف‌ها', 'کوپن‌ها و کمپین‌ها', $cap, 'temsah-coupons', array( __CLASS__, 'render_coupons' ) );
        add_submenu_page( 'temsah-dashboard', 'تیکت‌ها و دیدگاه‌ها', 'تیکت‌ها و دیدگاه‌ها', $cap, 'temsah-support', array( __CLASS__, 'render_support' ) );
        add_submenu_page( 'temsah-dashboard', 'اعلان‌ها و اطلاعیه‌ها', 'اعلان‌ها', $cap, 'temsah-notifications', array( __CLASS__, 'render_notifications' ) );
        add_submenu_page( 'temsah-dashboard', 'گزارش امنیت و فعالیت', 'لاگ فعالیت‌ها', $cap, 'temsah-logs', array( __CLASS__, 'render_logs' ) );
        add_submenu_page( 'temsah-dashboard', 'تنظیمات هسته و درگاه‌ها', 'تنظیمات هسته', $cap, 'temsah-settings', array( __CLASS__, 'render_settings' ) );
        add_submenu_page( 'temsah-dashboard', 'ابزار محیط تست / Seed', 'ابزار آزمایشی (Dev)', $cap, 'temsah-dev-tools', array( __CLASS__, 'render_dev_tools' ) );
    }

    public static function admin_styles(): void {
        if ( empty( $_GET['page'] ) || ! str_starts_with( sanitize_key( wp_unslash( $_GET['page'] ) ), 'temsah-' ) ) {
            return;
        }
        echo '<style>
        .tm-admin-wrap{direction:rtl;max-width:1280px;margin:20px auto;font-family:Vazirmatn,Tahoma,sans-serif;}
        .tm-kpis{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:14px;margin:16px 0 24px;}
        .tm-kpi{background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:16px;}
        .tm-kpi small{color:#6b7280;display:block;margin-bottom:6px;}
        .tm-kpi strong{font-size:20px;color:#111827;}
        .tm-card{background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:20px;margin-bottom:20px;}
        .tm-badge{display:inline-block;padding:3px 10px;border-radius:999px;font-size:12px;font-weight:600;}
        .tm-badge-ok{background:#ecfdf5;color:#047857;}
        .tm-badge-warn{background:#fffbeb;color:#b45309;}
        .tm-badge-err{background:#fef2f2;color:#b91c1c;}
        .tm-badge-muted{background:#f3f4f6;color:#374151;}
        </style>';
    }

    public static function handle_admin_action(): void {
        if ( ! current_user_can( 'temsah_manage_marketplace' ) ) {
            wp_die( 'دسترسی غیرمجاز.', '۴۰۳', array( 'response' => 403 ) );
        }
        check_admin_referer( 'temsah_admin_action' );

        global $wpdb;
        $sub_action = sanitize_key( wp_unslash( $_POST['sub_action'] ?? '' ) );
        $back       = wp_get_referer() ?: admin_url( 'admin.php?page=temsah-dashboard' );

        switch ( $sub_action ) {
            case 'review_vendor':
                $vid      = absint( $_POST['vendor_id'] ?? 0 );
                $decision = 'approve' === ( $_POST['decision'] ?? '' ) ? 'approve' : 'reject';
                $reason   = sanitize_textarea_field( wp_unslash( $_POST['reason'] ?? '' ) );
                Temsah_Vendor::admin_review_vendor( $vid, $decision, $reason );
                break;

            case 'update_vendor_commission':
                $vid  = absint( $_POST['vendor_id'] ?? 0 );
                $rate = max( 0, min( 100, (float) ( $_POST['commission_rate'] ?? 70 ) ) );
                $wpdb->update( Temsah_DB::table( 'vendors' ), array( 'commission_rate' => $rate ), array( 'id' => $vid ) );
                break;

            case 'review_product':
                $pid    = absint( $_POST['product_id'] ?? 0 );
                $status = in_array( $_POST['status'] ?? '', array( 'published', 'rejected', 'draft' ), true ) ? $_POST['status'] : 'published';
                $p      = Temsah_CPT::get_product_by_id( $pid );
                if ( $p ) {
                    $wpdb->update( Temsah_DB::table( 'products' ), array( 'status' => $status, 'updated_at' => current_time( 'mysql' ) ), array( 'id' => $pid ) );
                    wp_update_post( array( 'ID' => $p->post_id, 'post_status' => 'published' === $status ? 'publish' : 'draft' ) );
                    Temsah_Security::log_activity( 'admin_product_review', 'product', $pid, array( 'status' => $status ) );
                }
                break;

            case 'review_withdrawal':
                $wid      = absint( $_POST['withdrawal_id'] ?? 0 );
                $decision = 'paid' === ( $_POST['decision'] ?? '' ) ? 'paid' : 'rejected';
                $note     = sanitize_textarea_field( wp_unslash( $_POST['admin_note'] ?? '' ) );
                Temsah_Vendor::admin_review_withdrawal( $wid, $decision, $note, get_current_user_id() );
                break;

            case 'create_coupon':
                $code = strtoupper( sanitize_text_field( wp_unslash( $_POST['code'] ?? '' ) ) );
                if ( '' !== $code ) {
                    $wpdb->insert(
                        Temsah_DB::table( 'coupons' ),
                        array(
                            'code'         => $code,
                            'type'         => 'fixed' === ( $_POST['type'] ?? '' ) ? 'fixed' : 'percent',
                            'amount'       => absint( $_POST['amount'] ?? 10 ),
                            'min_order'    => absint( $_POST['min_order'] ?? 0 ),
                            'max_discount' => ! empty( $_POST['max_discount'] ) ? absint( $_POST['max_discount'] ) : null,
                            'usage_limit'  => ! empty( $_POST['usage_limit'] ) ? absint( $_POST['usage_limit'] ) : null,
                            'is_active'    => 1,
                            'created_at'   => current_time( 'mysql' ),
                        )
                    );
                }
                break;

            case 'delete_coupon':
                $wpdb->delete( Temsah_DB::table( 'coupons' ), array( 'id' => absint( $_POST['coupon_id'] ?? 0 ) ) );
                break;

            case 'send_broadcast':
                $title    = sanitize_text_field( wp_unslash( $_POST['title'] ?? '' ) );
                $body     = sanitize_textarea_field( wp_unslash( $_POST['body'] ?? '' ) );
                $audience = in_array( $_POST['audience'] ?? 'all', array( 'all', 'vendor', 'buyer' ), true ) ? $_POST['audience'] : 'all';
                if ( '' !== $title && '' !== $body ) {
                    $wpdb->insert(
                        Temsah_DB::table( 'notifications' ),
                        array(
                            'user_id'    => null,
                            'audience'   => $audience,
                            'title'      => $title,
                            'body'       => $body,
                            'created_at' => current_time( 'mysql' ),
                        )
                    );
                }
                break;

            case 'save_settings':
                $s = get_option( 'temsah_settings', array() );
                $s['default_commission_rate'] = max( 0, min( 100, (int) ( $_POST['default_commission_rate'] ?? 70 ) ) );
                $s['min_withdrawal_amount']   = absint( $_POST['min_withdrawal_amount'] ?? 100000 );
                $s['prevent_duplicate_buy']   = ! empty( $_POST['prevent_duplicate_buy'] ) ? 1 : 0;
                $s['sandbox_gateway_enabled'] = ! empty( $_POST['sandbox_gateway_enabled'] ) ? 1 : 0;
                $s['zarinpal_enabled']        = ! empty( $_POST['zarinpal_enabled'] ) ? 1 : 0;
                $s['zarinpal_merchant_id']    = sanitize_text_field( wp_unslash( $_POST['zarinpal_merchant_id'] ?? '' ) );
                $s['saman_enabled']           = ! empty( $_POST['saman_enabled'] ) ? 1 : 0;
                $s['saman_terminal_id']       = sanitize_text_field( wp_unslash( $_POST['saman_terminal_id'] ?? '' ) );
                $s['weekly_developer_mode']   = 'manual' === ( $_POST['weekly_developer_mode'] ?? 'auto' ) ? 'manual' : 'auto';
                $s['weekly_developer_id']     = absint( $_POST['weekly_developer_id'] ?? 0 );
                update_option( 'temsah_settings', $s );
                break;

            case 'seed_demo_data':
                $res  = Temsah_Seeder::install_demo_data();
                $back = add_query_arg( 'seeded', $res['products'], $back );
                break;

            case 'purge_demo_data':
                $res  = Temsah_Seeder::purge_demo_data();
                $back = add_query_arg( 'purged', $res['deleted_posts'], $back );
                break;
        }

        wp_safe_redirect( add_query_arg( 'temsah_saved', 1, $back ) );
        exit;
    }

    private static function form_open( string $sub_action ): string {
        return '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">' .
            wp_nonce_field( 'temsah_admin_action', '_wpnonce', true, false ) .
            '<input type="hidden" name="action" value="temsah_admin_action">' .
            '<input type="hidden" name="sub_action" value="' . esc_attr( $sub_action ) . '">';
    }

    public static function render_dashboard(): void {
        global $wpdb;
        $settings     = get_option( 'temsah_settings', array() );
        $mode         = $settings['environment_mode'] ?? 'production';
        $paid_orders  = (int) $wpdb->get_var( 'SELECT COUNT(*) FROM ' . Temsah_DB::table( 'orders' ) . " WHERE status = 'paid'" );
        $total_rev    = (int) $wpdb->get_var( 'SELECT COALESCE(SUM(total),0) FROM ' . Temsah_DB::table( 'orders' ) . " WHERE status = 'paid'" );
        $platform_fee = (int) $wpdb->get_var( 'SELECT COALESCE(SUM(platform_amount),0) FROM ' . Temsah_DB::table( 'commissions' ) );
        $pub_prods    = (int) $wpdb->get_var( 'SELECT COUNT(*) FROM ' . Temsah_DB::table( 'products' ) . " WHERE status = 'published'" );
        $pend_prods   = (int) $wpdb->get_var( 'SELECT COUNT(*) FROM ' . Temsah_DB::table( 'products' ) . " WHERE status = 'pending_review'" );
        $pend_vendors = (int) $wpdb->get_var( 'SELECT COUNT(*) FROM ' . Temsah_DB::table( 'vendors' ) . " WHERE status = 'pending'" );
        $pend_with    = (int) $wpdb->get_var( 'SELECT COUNT(*) FROM ' . Temsah_DB::table( 'withdrawals' ) . " WHERE status = 'pending'" );
        $open_tickets = (int) $wpdb->get_var( 'SELECT COUNT(*) FROM ' . Temsah_DB::table( 'tickets' ) . " WHERE status != 'closed'" );
        ?>
        <div class="wrap tm-admin-wrap">
            <h1>داشبورد مدیریت مارکت‌پلیس تمساح
                <span class="tm-badge <?php echo 'production' === $mode ? 'tm-badge-ok' : 'tm-badge-warn'; ?>">
                    <?php echo 'production' === $mode ? 'حالت عملیاتی (Production)' : 'حالت آزمایشی (دارای داده دمو)'; ?>
                </span>
            </h1>
            <div class="tm-kpis">
                <div class="tm-kpi"><small>سفارش‌های موفق</small><strong><?php echo esc_html( temsah_format_number( $paid_orders ) ); ?></strong></div>
                <div class="tm-kpi"><small>فروش کل مارکت</small><strong><?php echo esc_html( temsah_format_toman( $total_rev ) ); ?></strong></div>
                <div class="tm-kpi"><small>سهم خالص پلتفرم</small><strong><?php echo esc_html( temsah_format_toman( $platform_fee ) ); ?></strong></div>
                <div class="tm-kpi"><small>محصولات منتشرشده</small><strong><?php echo esc_html( temsah_format_number( $pub_prods ) ); ?></strong></div>
                <div class="tm-kpi"><small>محصولات در انتظار تایید</small><strong><?php echo esc_html( temsah_format_number( $pend_prods ) ); ?></strong></div>
                <div class="tm-kpi"><small>فروشندگان منتظر بررسی</small><strong><?php echo esc_html( temsah_format_number( $pend_vendors ) ); ?></strong></div>
                <div class="tm-kpi"><small>برداشت‌های در انتظار</small><strong><?php echo esc_html( temsah_format_number( $pend_with ) ); ?></strong></div>
                <div class="tm-kpi"><small>تیکت‌های باز</small><strong><?php echo esc_html( temsah_format_number( $open_tickets ) ); ?></strong></div>
            </div>
            <div class="tm-card">
                <h2>وضعیت اتصال سرویس‌های بیرونی</h2>
                <p>• درگاه پرداخت زرین‌پال: <strong><?php echo ! empty( $settings['zarinpal_enabled'] ) && ! empty( $settings['zarinpal_merchant_id'] ) ? 'پیکربندی شده' : 'غیرفعال (نیازمند ثبت Merchant ID در تنظیمات)'; ?></strong></p>
                <p>• درگاه آزمایشی امن (Sandbox): <strong><?php echo ! empty( $settings['sandbox_gateway_enabled'] ) ? 'فعال' : 'غیرفعال'; ?></strong></p>
                <p>• ارسال پیامک ورود (OTP): <strong>غیرفعال (نیازمند اتصال پنل پیامکی خارجی)</strong></p>
            </div>
        </div>
        <?php
    }

    public static function render_products(): void {
        global $wpdb;
        $t_p = Temsah_DB::table( 'products' );
        $t_v = Temsah_DB::table( 'vendors' );
        $rows = $wpdb->get_results(
            "SELECT p.*, wp.post_title, v.store_name
             FROM {$t_p} p
             INNER JOIN {$wpdb->posts} wp ON wp.ID = p.post_id
             LEFT JOIN {$t_v} v ON v.id = p.vendor_id
             ORDER BY p.id DESC LIMIT 100"
        );
        ?>
        <div class="wrap tm-admin-wrap">
            <h1>مدیریت محصولات تمساح</h1>
            <div class="tm-card">
                <table class="widefat striped">
                    <thead><tr><th>شناسه</th><th>عنوان</th><th>فروشنده</th><th>قیمت</th><th>نسخه</th><th>فروش</th><th>وضعیت</th><th>عملیات</th></tr></thead>
                    <tbody>
                    <?php if ( empty( $rows ) ) : ?>
                        <tr><td colspan="8">هنوز محصولی در پایگاه داده ثبت نشده است.</td></tr>
                    <?php else : foreach ( $rows as $r ) : ?>
                        <tr>
                            <td>#<?php echo (int) $r->id; ?></td>
                            <td><strong><?php echo esc_html( $r->post_title ); ?></strong></td>
                            <td><?php echo esc_html( $r->store_name ?: '—' ); ?></td>
                            <td><?php echo esc_html( temsah_format_toman( Temsah_CPT::effective_price( $r ) ) ); ?></td>
                            <td><?php echo esc_html( $r->current_version ); ?></td>
                            <td><?php echo esc_html( temsah_format_number( $r->sales_count ) ); ?></td>
                            <td><?php echo esc_html( $r->status ); ?></td>
                            <td>
                                <?php echo self::form_open( 'review_product' ); ?>
                                    <input type="hidden" name="product_id" value="<?php echo (int) $r->id; ?>">
                                    <select name="status">
                                        <option value="published" <?php selected( $r->status, 'published' ); ?>>انتشار</option>
                                        <option value="draft" <?php selected( $r->status, 'draft' ); ?>>پیش‌نویس</option>
                                        <option value="rejected" <?php selected( $r->status, 'rejected' ); ?>>رد شده</option>
                                    </select>
                                    <button class="button button-small button-primary">ذخیره</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }

    public static function render_vendors(): void {
        global $wpdb;
        $t_v   = Temsah_DB::table( 'vendors' );
        $t_ver = Temsah_DB::table( 'vendor_verifications' );
        $rows  = $wpdb->get_results(
            "SELECT v.*, vr.full_name, vr.mobile, vr.national_id, vr.iban, vr.review_status
             FROM {$t_v} v
             LEFT JOIN {$t_ver} vr ON vr.vendor_id = v.id
             ORDER BY v.id DESC LIMIT 100"
        );
        ?>
        <div class="wrap tm-admin-wrap">
            <h1>فروشندگان و احراز هویت (KYC)</h1>
            <div class="tm-card">
                <table class="widefat striped">
                    <thead><tr><th>فروشگاه</th><th>متقاضی / موبایل / کد ملی</th><th>شبا</th><th>کمیسیون (%)</th><th>موجودی</th><th>وضعیت</th><th>بررسی</th></tr></thead>
                    <tbody>
                    <?php if ( empty( $rows ) ) : ?>
                        <tr><td colspan="7">هنوز فروشنده‌ای ثبت نشده است.</td></tr>
                    <?php else : foreach ( $rows as $r ) : ?>
                        <tr>
                            <td><strong><?php echo esc_html( $r->store_name ); ?></strong><br><small><?php echo esc_html( $r->specialty ); ?></small></td>
                            <td><?php echo esc_html( $r->full_name ?: '—' ); ?><br><small><?php echo esc_html( $r->mobile ?: '' ); ?> | <?php echo esc_html( $r->national_id ?: '' ); ?></small></td>
                            <td><code><?php echo esc_html( $r->iban ?: '—' ); ?></code></td>
                            <td>
                                <?php echo self::form_open( 'update_vendor_commission' ); ?>
                                    <input type="hidden" name="vendor_id" value="<?php echo (int) $r->id; ?>">
                                    <input type="number" step="0.5" min="0" max="100" name="commission_rate" value="<?php echo esc_attr( $r->commission_rate ); ?>" style="width:70px;">
                                    <button class="button button-small">ثبت</button>
                                </form>
                            </td>
                            <td><?php echo esc_html( temsah_format_toman( $r->available_balance ) ); ?></td>
                            <td><?php echo esc_html( $r->status ); ?></td>
                            <td>
                                <?php echo self::form_open( 'review_vendor' ); ?>
                                    <input type="hidden" name="vendor_id" value="<?php echo (int) $r->id; ?>">
                                    <select name="decision">
                                        <option value="approve" <?php selected( $r->status, 'approved' ); ?>>تایید فروشنده</option>
                                        <option value="reject" <?php selected( $r->status, 'rejected' ); ?>>رد درخواست</option>
                                    </select>
                                    <input type="text" name="reason" placeholder="توضیح (در صورت رد)" style="width:140px;">
                                    <button class="button button-small button-primary">اعمال</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }

    public static function render_orders(): void {
        global $wpdb;
        $rows = $wpdb->get_results( 'SELECT * FROM ' . Temsah_DB::table( 'orders' ) . ' ORDER BY id DESC LIMIT 100' );
        ?>
        <div class="wrap tm-admin-wrap">
            <h1>سفارش‌ها و پرداخت‌ها</h1>
            <div class="tm-card">
                <table class="widefat striped">
                    <thead><tr><th>شماره سفارش</th><th>خریدار</th><th>درگاه</th><th>مبلغ کل</th><th>وضعیت</th><th>تاریخ ثبت</th></tr></thead>
                    <tbody>
                    <?php if ( empty( $rows ) ) : ?>
                        <tr><td colspan="6">سفارشی ثبت نشده است.</td></tr>
                    <?php else : foreach ( $rows as $r ) : ?>
                        <tr>
                            <td><strong><?php echo esc_html( $r->order_number ); ?></strong></td>
                            <td><?php echo esc_html( $r->buyer_name ); ?><br><small><?php echo esc_html( $r->buyer_email ); ?></small></td>
                            <td><?php echo esc_html( $r->gateway_id ); ?></td>
                            <td><?php echo esc_html( temsah_format_toman( $r->total ) ); ?></td>
                            <td><?php echo esc_html( $r->status ); ?></td>
                            <td><?php echo esc_html( temsah_format_date( $r->created_at ) ); ?></td>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }

    public static function render_finance(): void {
        global $wpdb;
        $rows = $wpdb->get_results( 'SELECT * FROM ' . Temsah_DB::table( 'commissions' ) . ' ORDER BY id DESC LIMIT 100' );
        ?>
        <div class="wrap tm-admin-wrap">
            <h1>گزارش کمیسیون‌ها و تراکنش‌ها</h1>
            <div class="tm-card">
                <table class="widefat striped">
                    <thead><tr><th>شناسه</th><th>سفارش</th><th>فروشنده</th><th>مبلغ ناخالص</th><th>درصد فروشنده</th><th>سهم فروشنده</th><th>سهم تمساح</th><th>تاریخ</th></tr></thead>
                    <tbody>
                    <?php if ( empty( $rows ) ) : ?>
                        <tr><td colspan="8">تراکنش کمیسیونی ثبت نشده است.</td></tr>
                    <?php else : foreach ( $rows as $r ) : ?>
                        <tr>
                            <td>#<?php echo (int) $r->id; ?></td>
                            <td>#<?php echo (int) $r->order_id; ?></td>
                            <td>#<?php echo (int) $r->vendor_id; ?></td>
                            <td><?php echo esc_html( temsah_format_toman( $r->gross_amount ) ); ?></td>
                            <td><?php echo esc_html( $r->vendor_rate ); ?>%</td>
                            <td><?php echo esc_html( temsah_format_toman( $r->vendor_amount ) ); ?></td>
                            <td><?php echo esc_html( temsah_format_toman( $r->platform_amount ) ); ?></td>
                            <td><?php echo esc_html( temsah_format_date( $r->created_at ) ); ?></td>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }

    public static function render_withdrawals(): void {
        global $wpdb;
        $t_w = Temsah_DB::table( 'withdrawals' );
        $t_v = Temsah_DB::table( 'vendors' );
        $rows = $wpdb->get_results( "SELECT w.*, v.store_name FROM {$t_w} w LEFT JOIN {$t_v} v ON v.id = w.vendor_id ORDER BY w.id DESC LIMIT 100" );
        ?>
        <div class="wrap tm-admin-wrap">
            <h1>درخواست‌های برداشت فروشندگان</h1>
            <div class="tm-card">
                <table class="widefat striped">
                    <thead><tr><th>شناسه</th><th>فروشگاه</th><th>مبلغ</th><th>شماره شبا</th><th>وضعیت</th><th>تاریخ</th><th>عملیات</th></tr></thead>
                    <tbody>
                    <?php if ( empty( $rows ) ) : ?>
                        <tr><td colspan="7">درخواست برداشتی ثبت نشده است.</td></tr>
                    <?php else : foreach ( $rows as $r ) : ?>
                        <tr>
                            <td>#<?php echo (int) $r->id; ?></td>
                            <td><?php echo esc_html( $r->store_name ?: '—' ); ?></td>
                            <td><?php echo esc_html( temsah_format_toman( $r->amount ) ); ?></td>
                            <td><code><?php echo esc_html( $r->iban ); ?></code></td>
                            <td><?php echo esc_html( $r->status ); ?></td>
                            <td><?php echo esc_html( temsah_format_date( $r->created_at ) ); ?></td>
                            <td>
                                <?php if ( 'pending' === $r->status ) : ?>
                                    <?php echo self::form_open( 'review_withdrawal' ); ?>
                                        <input type="hidden" name="withdrawal_id" value="<?php echo (int) $r->id; ?>">
                                        <select name="decision">
                                            <option value="paid">پرداخت شد</option>
                                            <option value="rejected">رد و برگشت به کیف پول</option>
                                        </select>
                                        <input type="text" name="admin_note" placeholder="کد پیگیری / دلیل">
                                        <button class="button button-small button-primary">ثبت</button>
                                    </form>
                                <?php else : echo esc_html( $r->admin_note ?: 'تکمیل شده' ); endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }

    public static function render_coupons(): void {
        global $wpdb;
        $rows = $wpdb->get_results( 'SELECT * FROM ' . Temsah_DB::table( 'coupons' ) . ' ORDER BY id DESC' );
        ?>
        <div class="wrap tm-admin-wrap">
            <h1>مدیریت کدهای تخفیف</h1>
            <div class="tm-card">
                <h2>ایجاد کوپن جدید</h2>
                <?php echo self::form_open( 'create_coupon' ); ?>
                    <p>
                        <input type="text" name="code" placeholder="کد مثلاً SUMMER20" required>
                        <select name="type"><option value="percent">درصدی (%)</option><option value="fixed">مبلغ ثابت (تومان)</option></select>
                        <input type="number" name="amount" placeholder="مقدار" required>
                        <input type="number" name="min_order" placeholder="حداقل سبد (تومان)" value="0">
                        <input type="number" name="max_discount" placeholder="سقف تخفیف">
                        <input type="number" name="usage_limit" placeholder="سقف استفاده">
                        <button class="button button-primary">ایجاد کوپن</button>
                    </p>
                </form>
            </div>
            <div class="tm-card">
                <table class="widefat striped">
                    <thead><tr><th>کد</th><th>نوع</th><th>مقدار</th><th>حداقل خرید</th><th>استفاده</th><th>حذف</th></tr></thead>
                    <tbody>
                    <?php if ( empty( $rows ) ) : ?>
                        <tr><td colspan="6">کد تخفیفی تعریف نشده است.</td></tr>
                    <?php else : foreach ( $rows as $r ) : ?>
                        <tr>
                            <td><code><?php echo esc_html( $r->code ); ?></code></td>
                            <td><?php echo esc_html( $r->type ); ?></td>
                            <td><?php echo esc_html( temsah_format_number( $r->amount ) ); ?></td>
                            <td><?php echo esc_html( temsah_format_toman( $r->min_order ) ); ?></td>
                            <td><?php echo (int) $r->usage_count; ?> / <?php echo esc_html( $r->usage_limit ?: 'نامحدود' ); ?></td>
                            <td>
                                <?php echo self::form_open( 'delete_coupon' ); ?>
                                    <input type="hidden" name="coupon_id" value="<?php echo (int) $r->id; ?>">
                                    <button class="button button-small">حذف</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }

    public static function render_support(): void {
        global $wpdb;
        $tickets = $wpdb->get_results( 'SELECT * FROM ' . Temsah_DB::table( 'tickets' ) . ' ORDER BY id DESC LIMIT 50' );
        $fbs     = $wpdb->get_results( 'SELECT * FROM ' . Temsah_DB::table( 'product_feedback' ) . ' ORDER BY id DESC LIMIT 50' );
        ?>
        <div class="wrap tm-admin-wrap">
            <h1>تیکت‌ها و دیدگاه‌ها</h1>
            <div class="tm-card">
                <h2>آخرین تیکت‌ها</h2>
                <table class="widefat striped">
                    <thead><tr><th>کد</th><th>موضوع</th><th>اولویت</th><th>وضعیت</th><th>تاریخ</th></tr></thead>
                    <tbody>
                    <?php if ( empty( $tickets ) ) : ?><tr><td colspan="5">تیکتی ثبت نشده است.</td></tr>
                    <?php else : foreach ( $tickets as $t ) : ?>
                        <tr><td><?php echo esc_html( $t->ticket_code ); ?></td><td><?php echo esc_html( $t->subject ); ?></td><td><?php echo esc_html( $t->priority ); ?></td><td><?php echo esc_html( $t->status ); ?></td><td><?php echo esc_html( temsah_format_date( $t->created_at ) ); ?></td></tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="tm-card">
                <h2>آخرین پرسش‌ها و دیدگاه‌ها</h2>
                <table class="widefat striped">
                    <thead><tr><th>نوع</th><th>متن</th><th>امتیاز</th><th>خریدار تاییدشده</th><th>تاریخ</th></tr></thead>
                    <tbody>
                    <?php if ( empty( $fbs ) ) : ?><tr><td colspan="5">موردی ثبت نشده است.</td></tr>
                    <?php else : foreach ( $fbs as $f ) : ?>
                        <tr><td><?php echo esc_html( $f->kind ); ?></td><td><?php echo esc_html( wp_trim_words( $f->content, 16 ) ); ?></td><td><?php echo esc_html( $f->rating ?: '—' ); ?></td><td><?php echo $f->is_verified_buyer ? 'بله' : 'خیر'; ?></td><td><?php echo esc_html( temsah_format_date( $f->created_at ) ); ?></td></tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }

    public static function render_notifications(): void {
        global $wpdb;
        $rows = $wpdb->get_results( 'SELECT * FROM ' . Temsah_DB::table( 'notifications' ) . ' ORDER BY id DESC LIMIT 50' );
        ?>
        <div class="wrap tm-admin-wrap">
            <h1>اعلان‌ها و اطلاعیه‌ها</h1>
            <div class="tm-card">
                <h2>ارسال اطلاعیه جدید</h2>
                <?php echo self::form_open( 'send_broadcast' ); ?>
                    <p><input type="text" name="title" placeholder="عنوان اطلاعیه" style="width:320px;" required>
                    <select name="audience"><option value="all">همه کاربران</option><option value="vendor">فقط فروشندگان</option><option value="buyer">فقط خریداران</option></select></p>
                    <p><textarea name="body" rows="3" style="width:100%;max-width:600px;" placeholder="متن اطلاعیه" required></textarea></p>
                    <p><button class="button button-primary">ارسال اطلاعیه</button></p>
                </form>
            </div>
            <div class="tm-card">
                <table class="widefat striped">
                    <thead><tr><th>مخاطب</th><th>عنوان</th><th>متن</th><th>تاریخ</th></tr></thead>
                    <tbody>
                    <?php if ( empty( $rows ) ) : ?><tr><td colspan="4">اعلانی ثبت نشده است.</td></tr>
                    <?php else : foreach ( $rows as $r ) : ?>
                        <tr><td><?php echo esc_html( $r->audience ); ?></td><td><?php echo esc_html( $r->title ); ?></td><td><?php echo esc_html( $r->body ); ?></td><td><?php echo esc_html( temsah_format_date( $r->created_at ) ); ?></td></tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }

    public static function render_logs(): void {
        global $wpdb;
        $rows = $wpdb->get_results( 'SELECT * FROM ' . Temsah_DB::table( 'activity_logs' ) . ' ORDER BY id DESC LIMIT 100' );
        ?>
        <div class="wrap tm-admin-wrap">
            <h1>گزارش فعالیت‌ها و رویدادهای امنیتی</h1>
            <div class="tm-card">
                <table class="widefat striped">
                    <thead><tr><th>رویداد</th><th>کاربر</th><th>شیء</th><th>IP</th><th>جزئیات</th><th>زمان</th></tr></thead>
                    <tbody>
                    <?php if ( empty( $rows ) ) : ?><tr><td colspan="6">رویدادی ثبت نشده است.</td></tr>
                    <?php else : foreach ( $rows as $r ) : ?>
                        <tr><td><code><?php echo esc_html( $r->action ); ?></code></td><td><?php echo esc_html( $r->user_id ?: 'مهمان' ); ?></td><td><?php echo esc_html( $r->object_type . '#' . $r->object_id ); ?></td><td><?php echo esc_html( $r->ip_address ); ?></td><td><small><?php echo esc_html( $r->details_json ); ?></small></td><td><?php echo esc_html( $r->created_at ); ?></td></tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }

    public static function render_settings(): void {
        global $wpdb;
        $s       = get_option( 'temsah_settings', array() );
        $vendors = $wpdb->get_results( 'SELECT id, store_name FROM ' . Temsah_DB::table( 'vendors' ) . " WHERE status = 'approved'" );
        ?>
        <div class="wrap tm-admin-wrap">
            <h1>تنظیمات هسته تجاری و درگاه‌های پرداخت</h1>
            <div class="tm-card">
                <?php echo self::form_open( 'save_settings' ); ?>
                    <table class="form-table">
                        <tr><th>درصد پیش‌فرض سهم فروشنده</th><td><input type="number" name="default_commission_rate" value="<?php echo esc_attr( $s['default_commission_rate'] ?? 70 ); ?>" min="0" max="100">%</td></tr>
                        <tr><th>حداقل مبلغ برداشت (تومان)</th><td><input type="number" name="min_withdrawal_amount" value="<?php echo esc_attr( $s['min_withdrawal_amount'] ?? 100000 ); ?>"></td></tr>
                        <tr><th>جلوگیری از خرید تکراری</th><td><label><input type="checkbox" name="prevent_duplicate_buy" value="1" <?php checked( ! empty( $s['prevent_duplicate_buy'] ) ); ?>> فعال (اگر کاربر قبلاً خریده باشد مجدداً به سبد اضافه نشود)</label></td></tr>
                        <tr><th>درگاه آزمایشی (Sandbox)</th><td><label><input type="checkbox" name="sandbox_gateway_enabled" value="1" <?php checked( ! empty( $s['sandbox_gateway_enabled'] ) ); ?>> فعال (فقط جهت محیط تست)</label></td></tr>
                        <tr><th>درگاه زرین‌پال</th><td>
                            <label><input type="checkbox" name="zarinpal_enabled" value="1" <?php checked( ! empty( $s['zarinpal_enabled'] ) ); ?>> فعال</label><br>
                            <input type="text" name="zarinpal_merchant_id" value="<?php echo esc_attr( $s['zarinpal_merchant_id'] ?? '' ); ?>" placeholder="Merchant ID (36 chars)" style="width:320px;margin-top:6px;">
                        </td></tr>
                        <tr><th>درگاه سپ (سامان)</th><td>
                            <label><input type="checkbox" name="saman_enabled" value="1" <?php checked( ! empty( $s['saman_enabled'] ) ); ?>> فعال</label><br>
                            <input type="text" name="saman_terminal_id" value="<?php echo esc_attr( $s['saman_terminal_id'] ?? '' ); ?>" placeholder="Terminal ID" style="width:320px;margin-top:6px;">
                        </td></tr>
                        <tr><th>حالت انتخاب «توسعه‌دهنده برتر هفته»</th><td>
                            <select name="weekly_developer_mode">
                                <option value="auto" <?php selected( $s['weekly_developer_mode'] ?? 'auto', 'auto' ); ?>>خودکار بر اساس فروش</option>
                                <option value="manual" <?php selected( $s['weekly_developer_mode'] ?? 'auto', 'manual' ); ?>>انتخاب دستی مدیر</option>
                            </select>
                            <select name="weekly_developer_id">
                                <option value="0">— انتخاب فروشنده —</option>
                                <?php foreach ( $vendors as $v ) : ?>
                                    <option value="<?php echo (int) $v->id; ?>" <?php selected( (int) ( $s['weekly_developer_id'] ?? 0 ), (int) $v->id ); ?>><?php echo esc_html( $v->store_name ); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td></tr>
                    </table>
                    <p><button class="button button-primary">ذخیره تنظیمات</button></p>
                </form>
            </div>
        </div>
        <?php
    }

    public static function render_dev_tools(): void {
        $s = get_option( 'temsah_settings', array() );
        ?>
        <div class="wrap tm-admin-wrap">
            <h1>ابزار داده‌های آزمایشی (فقط محیط توسعه و تست)</h1>
            <div class="tm-card">
                <p>در نصب استاندارد عملیاتی (Production)، هیچ محصول، فروشنده یا سفارش جعلی به صورت خودکار ایجاد نمی‌شود و سایت با حالت خالی واقعی بالا می‌آید.</p>
                <p>وضعیت فعلی محیط: <strong><?php echo esc_html( $s['environment_mode'] ?? 'production' ); ?></strong></p>
                <div style="display:flex;gap:12px;margin-top:16px;">
                    <?php echo self::form_open( 'seed_demo_data' ); ?>
                        <button class="button button-primary" onclick="return confirm('داده‌های نمونه نمایشی (۱۲ محصول، ۴ فروشنده، ۴ مقاله، ۱ کوپن آزمایشی TEMSAH20) ایجاد شوند؟');">ایجاد داده‌های آزمایشی (Seed Test Data)</button>
                    </form>
                    <?php echo self::form_open( 'purge_demo_data' ); ?>
                        <button class="button" onclick="return confirm('تمام داده‌های آزمایشی پاک و سیستم به حالت Production خالص برگردد؟');">پاک‌سازی کامل داده‌های آزمایشی (Purge Demo Data)</button>
                    </form>
                </div>
            </div>
        </div>
        <?php
    }
}
