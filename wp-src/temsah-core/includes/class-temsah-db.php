<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Temsah_DB {
    public static function table( string $name ): string {
        global $wpdb;
        return $wpdb->prefix . 'temsah_' . $name;
    }

    public static function vault_dir(): string {
        $dir = WP_CONTENT_DIR . '/temsah-vault';
        if ( ! is_dir( $dir ) ) {
            wp_mkdir_p( $dir );
        }
        $htaccess = $dir . '/.htaccess';
        if ( ! file_exists( $htaccess ) ) {
            @file_put_contents( $htaccess, "Order Deny,Allow\nDeny from all\n<IfModule mod_authz_core.c>\nRequire all denied\n</IfModule>\n" );
        }
        $webconfig = $dir . '/web.config';
        if ( ! file_exists( $webconfig ) ) {
            @file_put_contents( $webconfig, "<configuration><system.webServer><authorization><deny users=\"*\" /></authorization></system.webServer></configuration>" );
        }
        $index = $dir . '/index.php';
        if ( ! file_exists( $index ) ) {
            @file_put_contents( $index, "<?php // Silence is golden.\n" );
        }
        return $dir;
    }

    public static function install_or_upgrade(): void {
        global $wpdb;
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $charset_collate = $wpdb->get_charset_collate();
        $installed_ver   = get_option( 'temsah_core_db_version', '0' );

        $tables = self::get_schema_sql( $charset_collate );
        foreach ( $tables as $sql ) {
            dbDelta( $sql );
        }

        if ( version_compare( $installed_ver, '1.0.0', '<' ) ) {
            self::migrate_1_0_0();
        }

        self::vault_dir();
        update_option( 'temsah_core_db_version', TEMSAH_CORE_DB_VERSION );
    }

    private static function migrate_1_0_0(): void {
        if ( false === get_option( 'temsah_settings' ) ) {
            add_option( 'temsah_settings', array(
                'environment_mode'         => 'production',
                'default_commission_rate'  => 70,
                'min_withdrawal_amount'    => 100000,
                'prevent_duplicate_buy'    => 1,
                'sms_provider_enabled'     => 0,
                'sms_provider_name'        => 'none',
                'sandbox_gateway_enabled'  => 1,
                'zarinpal_enabled'         => 0,
                'zarinpal_merchant_id'     => '',
                'saman_enabled'            => 0,
                'saman_terminal_id'        => '',
                'weekly_developer_mode'    => 'auto',
                'weekly_developer_id'      => 0,
            ) );
        }
    }

    public static function get_schema_sql( string $charset_collate ): array {
        $t_products      = self::table( 'products' );
        $t_versions      = self::table( 'product_versions' );
        $t_files         = self::table( 'product_files' );
        $t_gallery       = self::table( 'product_gallery' );
        $t_vendors       = self::table( 'vendors' );
        $t_verifs        = self::table( 'vendor_verifications' );
        $t_carts         = self::table( 'carts' );
        $t_cart_items    = self::table( 'cart_items' );
        $t_orders        = self::table( 'orders' );
        $t_order_items   = self::table( 'order_items' );
        $t_payments      = self::table( 'payments' );
        $t_transactions  = self::table( 'transactions' );
        $t_licenses      = self::table( 'licenses' );
        $t_downloads     = self::table( 'downloads' );
        $t_coupons       = self::table( 'coupons' );
        $t_campaigns     = self::table( 'campaigns' );
        $t_commissions   = self::table( 'commissions' );
        $t_ledger        = self::table( 'vendor_wallet_ledger' );
        $t_withdrawals   = self::table( 'withdrawals' );
        $t_tickets       = self::table( 'tickets' );
        $t_ticket_msgs   = self::table( 'ticket_messages' );
        $t_reply_tpls    = self::table( 'vendor_reply_templates' );
        $t_feedback      = self::table( 'product_feedback' );
        $t_wishlists     = self::table( 'wishlists' );
        $t_notifications = self::table( 'notifications' );
        $t_logs          = self::table( 'activity_logs' );

        return array(
            "CREATE TABLE {$t_products} (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                post_id bigint(20) unsigned NOT NULL,
                vendor_id bigint(20) unsigned NOT NULL,
                price bigint(20) unsigned NOT NULL DEFAULT 0,
                sale_price bigint(20) unsigned DEFAULT NULL,
                current_version varchar(32) NOT NULL DEFAULT '1.0.0',
                compatibility varchar(191) NOT NULL DEFAULT '',
                demo_url varchar(255) NOT NULL DEFAULT '',
                cover_style varchar(48) NOT NULL DEFAULT 'cover-store',
                sales_count int(11) unsigned NOT NULL DEFAULT 0,
                rating_avg decimal(3,2) NOT NULL DEFAULT 0.00,
                rating_count int(11) unsigned NOT NULL DEFAULT 0,
                support_terms text DEFAULT NULL,
                highlights_json longtext DEFAULT NULL,
                badges_json text DEFAULT NULL,
                status varchar(32) NOT NULL DEFAULT 'draft',
                is_demo tinyint(1) NOT NULL DEFAULT 0,
                created_at datetime NOT NULL,
                updated_at datetime NOT NULL,
                PRIMARY KEY  (id),
                UNIQUE KEY post_id (post_id),
                KEY vendor_status (vendor_id, status),
                KEY status_sales (status, sales_count)
            ) {$charset_collate};",

            "CREATE TABLE {$t_versions} (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                product_id bigint(20) unsigned NOT NULL,
                version varchar(32) NOT NULL,
                changelog longtext DEFAULT NULL,
                compatibility varchar(191) NOT NULL DEFAULT '',
                created_at datetime NOT NULL,
                PRIMARY KEY  (id),
                KEY product_created (product_id, created_at)
            ) {$charset_collate};",

            "CREATE TABLE {$t_files} (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                product_id bigint(20) unsigned NOT NULL,
                version_id bigint(20) unsigned DEFAULT NULL,
                file_label varchar(191) NOT NULL,
                file_name varchar(255) NOT NULL,
                storage_path varchar(512) NOT NULL,
                file_size bigint(20) unsigned NOT NULL DEFAULT 0,
                checksum_sha256 char(64) NOT NULL DEFAULT '',
                is_active tinyint(1) NOT NULL DEFAULT 1,
                created_at datetime NOT NULL,
                PRIMARY KEY  (id),
                KEY product_active (product_id, is_active)
            ) {$charset_collate};",

            "CREATE TABLE {$t_gallery} (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                product_id bigint(20) unsigned NOT NULL,
                attachment_id bigint(20) unsigned NOT NULL,
                sort_order int(11) NOT NULL DEFAULT 0,
                caption varchar(255) NOT NULL DEFAULT '',
                PRIMARY KEY  (id),
                KEY product_order (product_id, sort_order)
            ) {$charset_collate};",

            "CREATE TABLE {$t_vendors} (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                user_id bigint(20) unsigned NOT NULL,
                store_name varchar(191) NOT NULL,
                store_slug varchar(191) NOT NULL,
                specialty varchar(120) NOT NULL DEFAULT '',
                bio text DEFAULT NULL,
                portfolio_url varchar(255) NOT NULL DEFAULT '',
                support_email varchar(191) NOT NULL DEFAULT '',
                support_hours varchar(120) NOT NULL DEFAULT '',
                commission_rate decimal(5,2) NOT NULL DEFAULT 70.00,
                status varchar(32) NOT NULL DEFAULT 'pending',
                is_featured_weekly tinyint(1) NOT NULL DEFAULT 0,
                badges_json text DEFAULT NULL,
                total_sales int(11) unsigned NOT NULL DEFAULT 0,
                total_earnings bigint(20) unsigned NOT NULL DEFAULT 0,
                available_balance bigint(20) unsigned NOT NULL DEFAULT 0,
                is_demo tinyint(1) NOT NULL DEFAULT 0,
                created_at datetime NOT NULL,
                updated_at datetime NOT NULL,
                PRIMARY KEY  (id),
                UNIQUE KEY user_id (user_id),
                UNIQUE KEY store_slug (store_slug),
                KEY status_idx (status)
            ) {$charset_collate};",

            "CREATE TABLE {$t_verifs} (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                vendor_id bigint(20) unsigned NOT NULL,
                user_id bigint(20) unsigned NOT NULL,
                full_name varchar(191) NOT NULL,
                mobile varchar(32) NOT NULL,
                mobile_verified tinyint(1) NOT NULL DEFAULT 0,
                national_id varchar(20) NOT NULL,
                birth_date varchar(32) NOT NULL DEFAULT '',
                iban varchar(32) NOT NULL,
                id_document_path varchar(512) NOT NULL DEFAULT '',
                agreement_accepted tinyint(1) NOT NULL DEFAULT 0,
                agreement_accepted_at datetime DEFAULT NULL,
                review_status varchar(32) NOT NULL DEFAULT 'pending',
                rejection_reason text DEFAULT NULL,
                reviewed_by bigint(20) unsigned DEFAULT NULL,
                reviewed_at datetime DEFAULT NULL,
                created_at datetime NOT NULL,
                updated_at datetime NOT NULL,
                PRIMARY KEY  (id),
                KEY vendor_idx (vendor_id),
                KEY user_idx (user_id),
                KEY review_status_idx (review_status)
            ) {$charset_collate};",

            "CREATE TABLE {$t_carts} (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                session_token varchar(64) NOT NULL,
                user_id bigint(20) unsigned DEFAULT NULL,
                coupon_code varchar(64) DEFAULT NULL,
                gateway_id varchar(48) NOT NULL DEFAULT 'sandbox',
                updated_at datetime NOT NULL,
                expires_at datetime NOT NULL,
                PRIMARY KEY  (id),
                UNIQUE KEY session_token (session_token),
                KEY user_idx (user_id)
            ) {$charset_collate};",

            "CREATE TABLE {$t_cart_items} (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                cart_id bigint(20) unsigned NOT NULL,
                product_id bigint(20) unsigned NOT NULL,
                services_json text DEFAULT NULL,
                created_at datetime NOT NULL,
                PRIMARY KEY  (id),
                UNIQUE KEY cart_product (cart_id, product_id)
            ) {$charset_collate};",

            "CREATE TABLE {$t_orders} (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                order_number varchar(48) NOT NULL,
                user_id bigint(20) unsigned NOT NULL,
                buyer_name varchar(191) NOT NULL,
                buyer_mobile varchar(32) NOT NULL,
                buyer_email varchar(191) NOT NULL,
                subtotal bigint(20) unsigned NOT NULL DEFAULT 0,
                product_discount bigint(20) unsigned NOT NULL DEFAULT 0,
                services_total bigint(20) unsigned NOT NULL DEFAULT 0,
                coupon_code varchar(64) DEFAULT NULL,
                coupon_discount bigint(20) unsigned NOT NULL DEFAULT 0,
                total bigint(20) unsigned NOT NULL DEFAULT 0,
                gateway_id varchar(48) NOT NULL DEFAULT 'sandbox',
                status varchar(32) NOT NULL DEFAULT 'pending_payment',
                is_demo tinyint(1) NOT NULL DEFAULT 0,
                created_at datetime NOT NULL,
                paid_at datetime DEFAULT NULL,
                PRIMARY KEY  (id),
                UNIQUE KEY order_number (order_number),
                KEY user_status (user_id, status),
                KEY created_idx (created_at)
            ) {$charset_collate};",

            "CREATE TABLE {$t_order_items} (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                order_id bigint(20) unsigned NOT NULL,
                product_id bigint(20) unsigned NOT NULL,
                vendor_id bigint(20) unsigned NOT NULL,
                product_title varchar(255) NOT NULL,
                unit_price bigint(20) unsigned NOT NULL DEFAULT 0,
                original_price bigint(20) unsigned NOT NULL DEFAULT 0,
                services_json text DEFAULT NULL,
                services_price bigint(20) unsigned NOT NULL DEFAULT 0,
                line_total bigint(20) unsigned NOT NULL DEFAULT 0,
                commission_rate decimal(5,2) NOT NULL DEFAULT 70.00,
                vendor_earning bigint(20) unsigned NOT NULL DEFAULT 0,
                platform_fee bigint(20) unsigned NOT NULL DEFAULT 0,
                PRIMARY KEY  (id),
                KEY order_idx (order_id),
                KEY vendor_idx (vendor_id),
                KEY product_idx (product_id)
            ) {$charset_collate};",

            "CREATE TABLE {$t_payments} (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                order_id bigint(20) unsigned NOT NULL,
                user_id bigint(20) unsigned NOT NULL,
                gateway_id varchar(48) NOT NULL,
                authority varchar(128) NOT NULL,
                ref_id varchar(128) DEFAULT NULL,
                amount bigint(20) unsigned NOT NULL DEFAULT 0,
                status varchar(32) NOT NULL DEFAULT 'initiated',
                callback_payload longtext DEFAULT NULL,
                verified_at datetime DEFAULT NULL,
                created_at datetime NOT NULL,
                PRIMARY KEY  (id),
                UNIQUE KEY authority (authority),
                KEY order_idx (order_id),
                KEY user_idx (user_id)
            ) {$charset_collate};",

            "CREATE TABLE {$t_transactions} (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                user_id bigint(20) unsigned DEFAULT NULL,
                vendor_id bigint(20) unsigned DEFAULT NULL,
                order_id bigint(20) unsigned DEFAULT NULL,
                type varchar(48) NOT NULL,
                amount bigint(20) NOT NULL DEFAULT 0,
                description varchar(255) NOT NULL,
                created_at datetime NOT NULL,
                PRIMARY KEY  (id),
                KEY user_idx (user_id),
                KEY vendor_idx (vendor_id),
                KEY order_idx (order_id)
            ) {$charset_collate};",

            "CREATE TABLE {$t_licenses} (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                order_id bigint(20) unsigned NOT NULL,
                order_item_id bigint(20) unsigned NOT NULL,
                product_id bigint(20) unsigned NOT NULL,
                user_id bigint(20) unsigned NOT NULL,
                license_key char(36) NOT NULL,
                domain varchar(255) DEFAULT NULL,
                max_activations int(11) NOT NULL DEFAULT 1,
                status varchar(32) NOT NULL DEFAULT 'active',
                created_at datetime NOT NULL,
                PRIMARY KEY  (id),
                UNIQUE KEY license_key (license_key),
                KEY user_product (user_id, product_id),
                KEY order_idx (order_id)
            ) {$charset_collate};",

            "CREATE TABLE {$t_downloads} (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                user_id bigint(20) unsigned NOT NULL,
                product_id bigint(20) unsigned NOT NULL,
                file_id bigint(20) unsigned NOT NULL,
                order_id bigint(20) unsigned NOT NULL,
                ip_address varchar(64) NOT NULL DEFAULT '',
                user_agent varchar(255) NOT NULL DEFAULT '',
                downloaded_at datetime NOT NULL,
                PRIMARY KEY  (id),
                KEY user_product (user_id, product_id)
            ) {$charset_collate};",

            "CREATE TABLE {$t_coupons} (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                code varchar(64) NOT NULL,
                type varchar(16) NOT NULL DEFAULT 'percent',
                amount bigint(20) unsigned NOT NULL DEFAULT 0,
                min_order bigint(20) unsigned NOT NULL DEFAULT 0,
                max_discount bigint(20) unsigned DEFAULT NULL,
                usage_limit int(11) unsigned DEFAULT NULL,
                usage_count int(11) unsigned NOT NULL DEFAULT 0,
                starts_at datetime DEFAULT NULL,
                expires_at datetime DEFAULT NULL,
                is_active tinyint(1) NOT NULL DEFAULT 1,
                created_at datetime NOT NULL,
                PRIMARY KEY  (id),
                UNIQUE KEY code (code)
            ) {$charset_collate};",

            "CREATE TABLE {$t_campaigns} (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                title varchar(191) NOT NULL,
                discount_percent int(11) unsigned NOT NULL DEFAULT 0,
                product_ids_json text DEFAULT NULL,
                starts_at datetime DEFAULT NULL,
                expires_at datetime DEFAULT NULL,
                is_active tinyint(1) NOT NULL DEFAULT 1,
                created_at datetime NOT NULL,
                PRIMARY KEY  (id)
            ) {$charset_collate};",

            "CREATE TABLE {$t_commissions} (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                order_id bigint(20) unsigned NOT NULL,
                order_item_id bigint(20) unsigned NOT NULL,
                vendor_id bigint(20) unsigned NOT NULL,
                gross_amount bigint(20) unsigned NOT NULL DEFAULT 0,
                vendor_rate decimal(5,2) NOT NULL DEFAULT 70.00,
                vendor_amount bigint(20) unsigned NOT NULL DEFAULT 0,
                platform_amount bigint(20) unsigned NOT NULL DEFAULT 0,
                status varchar(32) NOT NULL DEFAULT 'credited',
                created_at datetime NOT NULL,
                PRIMARY KEY  (id),
                UNIQUE KEY order_item_id (order_item_id),
                KEY vendor_idx (vendor_id),
                KEY order_idx (order_id)
            ) {$charset_collate};",

            "CREATE TABLE {$t_ledger} (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                vendor_id bigint(20) unsigned NOT NULL,
                entry_type varchar(32) NOT NULL,
                amount bigint(20) NOT NULL DEFAULT 0,
                balance_after bigint(20) NOT NULL DEFAULT 0,
                reference_id bigint(20) unsigned DEFAULT NULL,
                note varchar(255) NOT NULL DEFAULT '',
                created_at datetime NOT NULL,
                PRIMARY KEY  (id),
                KEY vendor_created (vendor_id, created_at)
            ) {$charset_collate};",

            "CREATE TABLE {$t_withdrawals} (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                vendor_id bigint(20) unsigned NOT NULL,
                user_id bigint(20) unsigned NOT NULL,
                amount bigint(20) unsigned NOT NULL DEFAULT 0,
                iban varchar(32) NOT NULL,
                status varchar(32) NOT NULL DEFAULT 'pending',
                admin_note text DEFAULT NULL,
                reviewed_by bigint(20) unsigned DEFAULT NULL,
                reviewed_at datetime DEFAULT NULL,
                created_at datetime NOT NULL,
                PRIMARY KEY  (id),
                KEY vendor_status (vendor_id, status),
                KEY status_idx (status)
            ) {$charset_collate};",

            "CREATE TABLE {$t_tickets} (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                ticket_code varchar(32) NOT NULL,
                user_id bigint(20) unsigned NOT NULL,
                vendor_id bigint(20) unsigned DEFAULT NULL,
                product_id bigint(20) unsigned DEFAULT NULL,
                order_id bigint(20) unsigned DEFAULT NULL,
                subject varchar(255) NOT NULL,
                priority varchar(24) NOT NULL DEFAULT 'normal',
                status varchar(32) NOT NULL DEFAULT 'open',
                created_at datetime NOT NULL,
                updated_at datetime NOT NULL,
                PRIMARY KEY  (id),
                UNIQUE KEY ticket_code (ticket_code),
                KEY user_idx (user_id),
                KEY vendor_idx (vendor_id),
                KEY status_idx (status)
            ) {$charset_collate};",

            "CREATE TABLE {$t_ticket_msgs} (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                ticket_id bigint(20) unsigned NOT NULL,
                sender_id bigint(20) unsigned NOT NULL,
                sender_role varchar(24) NOT NULL DEFAULT 'buyer',
                message longtext NOT NULL,
                created_at datetime NOT NULL,
                PRIMARY KEY  (id),
                KEY ticket_created (ticket_id, created_at)
            ) {$charset_collate};",

            "CREATE TABLE {$t_reply_tpls} (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                vendor_id bigint(20) unsigned NOT NULL,
                title varchar(191) NOT NULL,
                body text NOT NULL,
                created_at datetime NOT NULL,
                PRIMARY KEY  (id),
                KEY vendor_idx (vendor_id)
            ) {$charset_collate};",

            "CREATE TABLE {$t_feedback} (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                product_id bigint(20) unsigned NOT NULL,
                user_id bigint(20) unsigned NOT NULL,
                kind varchar(16) NOT NULL DEFAULT 'question',
                rating tinyint(3) unsigned DEFAULT NULL,
                content text NOT NULL,
                vendor_reply text DEFAULT NULL,
                vendor_replied_at datetime DEFAULT NULL,
                is_verified_buyer tinyint(1) NOT NULL DEFAULT 0,
                status varchar(24) NOT NULL DEFAULT 'approved',
                created_at datetime NOT NULL,
                PRIMARY KEY  (id),
                KEY product_kind (product_id, kind, status),
                KEY user_idx (user_id)
            ) {$charset_collate};",

            "CREATE TABLE {$t_wishlists} (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                user_id bigint(20) unsigned NOT NULL,
                product_id bigint(20) unsigned NOT NULL,
                created_at datetime NOT NULL,
                PRIMARY KEY  (id),
                UNIQUE KEY user_product (user_id, product_id)
            ) {$charset_collate};",

            "CREATE TABLE {$t_notifications} (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                user_id bigint(20) unsigned DEFAULT NULL,
                audience varchar(24) NOT NULL DEFAULT 'user',
                title varchar(191) NOT NULL,
                body text NOT NULL,
                is_read tinyint(1) NOT NULL DEFAULT 0,
                created_at datetime NOT NULL,
                PRIMARY KEY  (id),
                KEY user_read (user_id, is_read),
                KEY audience_idx (audience)
            ) {$charset_collate};",

            "CREATE TABLE {$t_logs} (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                user_id bigint(20) unsigned DEFAULT NULL,
                action varchar(64) NOT NULL,
                object_type varchar(64) NOT NULL DEFAULT '',
                object_id bigint(20) unsigned DEFAULT NULL,
                ip_address varchar(64) NOT NULL DEFAULT '',
                details_json text DEFAULT NULL,
                created_at datetime NOT NULL,
                PRIMARY KEY  (id),
                KEY action_created (action, created_at),
                KEY user_idx (user_id)
            ) {$charset_collate};",
        );
    }
}
