<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Isolated Development / Test Data Seeder & Cleaner.
 * NEVER invoked automatically in Production mode.
 */
class Temsah_Seeder {
    public static function install_demo_data(): array {
        global $wpdb;
        Temsah_CPT::ensure_default_terms();
        $now = current_time( 'mysql' );

        $sellers = array(
            array(
                'login'     => 'demo_arka',
                'email'     => 'arka@demo.temsah.local',
                'store'     => 'استودیو آرکا',
                'slug'      => 'arka-studio',
                'specialty' => 'طراحی قالب‌های وردپرس و مجله‌ای',
                'sales'     => 656,
                'badges'    => array( 'v-verified', 'v-top', 'v-fast', 'v-active' ),
            ),
            array(
                'login'     => 'demo_neocode',
                'email'     => 'neocode@demo.temsah.local',
                'store'     => 'نئوکد',
                'slug'      => 'neocode',
                'specialty' => 'توسعه افزونه‌های حرفه‌ای وردپرس',
                'sales'     => 1108,
                'featured'  => 1,
                'badges'    => array( 'v-verified', 'v-top', 'v-fast', 'v-active' ),
            ),
            array(
                'login'     => 'demo_designlab',
                'email'     => 'designlab@demo.temsah.local',
                'store'     => 'دیزاین‌لب',
                'slug'      => 'designlab',
                'specialty' => 'کیت‌های رابط کاربری و پنل مدیریت',
                'sales'     => 411,
                'badges'    => array( 'v-verified', 'v-fast', 'v-active' ),
            ),
            array(
                'login'     => 'demo_codenama',
                'email'     => 'codenama@demo.temsah.local',
                'store'     => 'کدنما',
                'slug'      => 'codenama',
                'specialty' => 'اسکریپت‌های PHP و اپلیکیشن‌ها',
                'sales'     => 504,
                'badges'    => array( 'v-verified', 'v-active' ),
            ),
        );

        $vendor_ids = array();
        foreach ( $sellers as $idx => $s ) {
            $user = get_user_by( 'login', $s['login'] );
            if ( ! $user ) {
                $uid = wp_create_user( $s['login'], wp_generate_password( 16, true ), $s['email'] );
                if ( is_wp_error( $uid ) ) {
                    continue;
                }
                $user = new WP_User( $uid );
                $user->set_role( 'temsah_vendor' );
                wp_update_user( array( 'ID' => $uid, 'display_name' => $s['store'] ) );
                update_user_meta( $uid, '_temsah_is_demo', 1 );
            } else {
                $uid = (int) $user->ID;
            }

            $t_v = Temsah_DB::table( 'vendors' );
            $vid = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$t_v} WHERE user_id = %d", $uid ) );
            if ( ! $vid ) {
                $wpdb->insert(
                    $t_v,
                    array(
                        'user_id'            => $uid,
                        'store_name'         => $s['store'],
                        'store_slug'         => $s['slug'],
                        'specialty'          => $s['specialty'],
                        'bio'                => 'فروشگاه نمونه تست برای بررسی چیدمان و قابلیت‌های تمساح.',
                        'commission_rate'    => 70.00,
                        'status'             => 'approved',
                        'is_featured_weekly' => $s['featured'] ?? 0,
                        'badges_json'        => wp_json_encode( $s['badges'] ),
                        'total_sales'        => $s['sales'],
                        'is_demo'            => 1,
                        'created_at'         => $now,
                        'updated_at'         => $now,
                    )
                );
                $vid = (int) $wpdb->insert_id;
            }
            $vendor_ids[ $idx ] = array( 'vid' => (int) $vid, 'uid' => $uid );
        }

        $products = array(
            array( 'قالب فروشگاهی آریو | Ario Shop', 'قالب فروشگاهی کامل با طراحی اختصاصی، صفحات محصول پیشرفته و تجربه خرید سریع.', 'wordpress', 0, 1240000, 985000, 'cover-store', 428, 4.9, array( 'p-popular', 'p-rated', 'p-updated', 'p-quality' ) ),
            array( 'افزونه فرم‌ساز پیشرفته فرمینو', 'فرم‌ساز کشیدنی و رهاکردنی با شرط‌های منطقی، اتصال به درگاه و خروجی اکسل.', 'plugin', 1, 485000, null, 'cover-forms', 316, 4.8, array( 'p-popular', 'p-rated', 'p-updated' ) ),
            array( 'قالب شرکتی HTML مدل آوان', 'قالب تک‌صفحه‌ای و چندصفحه‌ای سریع برای آژانس‌ها و شرکت‌های خدماتی.', 'html', 2, 520000, 380000, 'cover-agency', 142, 4.7, array( 'p-updated', 'p-quality' ) ),
            array( 'اسکریپت مدیریت پروژه تسکینو', 'سیستم تحت وب مدیریت وظایف، بورد کانبان، ثبت زمان و گزارش‌گیری تیمی.', 'script', 3, 1390000, null, 'cover-task', 214, 4.8, array( 'p-popular', 'p-quality' ) ),
            array( 'قالب خبری و مجله‌ای آوید', 'مناسب سایت‌های خبری، تحلیلی و مجلات آنلاین با ساختار سئوی بهینه.', 'wordpress', 0, 645000, null, 'cover-news', 228, 4.6, array( 'p-rated', 'p-updated' ) ),
            array( 'افزونه افزایش سرعت اسپارک کش', 'کش صفحات، فشرده‌سازی فایل‌ها، Lazy Load و بهبود هسته حیاتی وب.', 'plugin', 1, 395000, 295000, 'cover-speed', 512, 4.9, array( 'p-popular', 'p-rated', 'p-updated', 'p-quality' ) ),
            array( 'داشبورد مدیریتی نئوادمین', 'قالب پنل مدیریت واکنش‌گرا با نمودارها، جداول داده و فرم‌های آماده.', 'dashboard', 2, 765000, null, 'cover-admin', 112, 4.7, array( 'p-quality' ) ),
            array( 'اسکریپت رزرو آنلاین نوبان', 'سیستم نوبت‌دهی و رزرواسیون برای کلینیک‌ها و مجموعه‌های خدماتی.', 'script', 3, 1390000, 1090000, 'cover-booking', 194, 4.8, array( 'p-popular', 'p-updated' ) ),
            array( 'قالب فروشگاهی ویکان', 'طراحی مینیمال برای فروشگاه‌های مد، پوشاک و محصولات سبک زندگی.', 'wordpress', 2, 740000, null, 'cover-fashion', 157, 4.7, array( 'p-rated' ) ),
            array( 'سورس اپلیکیشن مدیریت وظایف', 'سورس کد موبایل تمیز و ماژولار برای اپلیکیشن یادداشت و برنامه‌ریزی.', 'app', 3, 690000, null, 'cover-mobile', 96, 4.6, array( 'p-updated' ) ),
            array( 'افزونه مگامنوی حرفه‌ای ویژن', 'ساخت منوهای چندستونه، آیکون‌دار و واکنش‌گرا برای وردپرس و فروشگاه‌ها.', 'plugin', 1, 510000, 390000, 'cover-menu', 280, 4.8, array( 'p-popular', 'p-rated' ) ),
            array( 'کیت رابط کاربری فلو | Flow UI', 'مجموعه کامپوننت‌های طراحی رابط کاربری، کارت‌ها، فرم‌ها و الگوهای مدرن.', 'ui', 2, 540000, null, 'cover-ui', 164, 4.8, array( 'p-quality', 'p-rated' ) ),
        );

        $t_p       = Temsah_DB::table( 'products' );
        $created_p = 0;

        foreach ( $products as $p ) {
            list( $title, $excerpt, $cat_slug, $v_idx, $price, $sale_price, $cover, $sales, $rating, $badges ) = $p;
            $vinfo = $vendor_ids[ $v_idx ] ?? reset( $vendor_ids );
            if ( ! $vinfo ) {
                continue;
            }
            $existing_post = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_title = %s AND post_type = 'temsah_product' AND post_status != 'trash' LIMIT 1", $title ) );
            if ( $existing_post ) {
                continue;
            }

            $post_id = wp_insert_post( array(
                'post_type'    => 'temsah_product',
                'post_title'   => $title,
                'post_excerpt' => $excerpt,
                'post_content' => '<p>' . esc_html( $excerpt ) . '</p><p>این محصول در حالت داده‌های آزمایشی ایجاد شده و تمامی امکانات سبد خرید، پرداخت آزمایشی و دانلود فایل واقعی را پشتیبانی می‌کند.</p>',
                'post_status'  => 'publish',
                'post_author'  => $vinfo['uid'],
            ) );
            if ( is_wp_error( $post_id ) || ! $post_id ) {
                continue;
            }
            update_post_meta( $post_id, '_temsah_is_demo', 1 );
            wp_set_object_terms( $post_id, array( $cat_slug ), 'temsah_product_cat' );

            $wpdb->insert(
                $t_p,
                array(
                    'post_id'         => $post_id,
                    'vendor_id'       => $vinfo['vid'],
                    'price'           => $price,
                    'sale_price'      => $sale_price,
                    'current_version' => '2.4.0',
                    'compatibility'   => 'WordPress 6.4+ / PHP 8.1+',
                    'cover_style'     => $cover,
                    'sales_count'     => $sales,
                    'rating_avg'      => $rating,
                    'rating_count'    => 12,
                    'badges_json'     => wp_json_encode( $badges ),
                    'status'          => 'published',
                    'is_demo'         => 1,
                    'created_at'      => $now,
                    'updated_at'      => $now,
                )
            );
            $prod_id = (int) $wpdb->insert_id;

            // Create a safe dummy ZIP archive inside protected vault so test download works cleanly
            $vault_sub = Temsah_DB::vault_dir() . '/products/' . $prod_id;
            wp_mkdir_p( $vault_sub );
            $sample_zip = $vault_sub . '/temsah-demo-package-' . $prod_id . '.zip';
            if ( ! file_exists( $sample_zip ) ) {
                @file_put_contents( $sample_zip, "PK\x05\x06" . str_repeat( "\x00", 18 ) );
            }
            $wpdb->insert(
                Temsah_DB::table( 'product_files' ),
                array(
                    'product_id'      => $prod_id,
                    'file_label'      => 'نسخه ۲.۴.۰ (فایل آزمایشی)',
                    'file_name'       => 'temsah-package-' . $prod_id . '.zip',
                    'storage_path'    => $sample_zip,
                    'file_size'       => (int) filesize( $sample_zip ),
                    'checksum_sha256' => hash_file( 'sha256', $sample_zip ) ?: '',
                    'is_active'       => 1,
                    'created_at'      => $now,
                )
            );
            $created_p++;
        }

        // Seed 4 demo articles
        $articles = array(
            array( 'چطور برای سایت خود قالب مناسب انتخاب کنیم؟', 'راهنمای کاربردی انتخاب قالب متناسب با نیاز پروژه و بررسی سرعت، واکنش‌گرایی و ساختار کد.' ),
            array( '۷ نکته قبل از خرید افزونه وردپرس', 'به سازگاری، نسخه PHP، پشتیبانی مستمر، سابقه توسعه‌دهنده و مستندات فنی توجه کنید.' ),
            array( 'قالب HTML یا سیستم آماده؟', 'بررسی کاربردها، هزینه نگهداری و تفاوت‌های اصلی دو مسیر توسعه وب برای پروژه‌های مختلف.' ),
            array( 'راهنمای انتشار اولین محصول دیجیتال', 'از آماده‌سازی مستندات فنی و بسته‌بندی فایل تا طراحی تصویر پیش‌نمایش و پشتیبانی خریداران.' ),
        );
        foreach ( $articles as $art ) {
            $existing_art = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_title = %s AND post_type = 'post' AND post_status != 'trash' LIMIT 1", $art[0] ) );
            if ( ! $existing_art ) {
                $aid = wp_insert_post( array(
                    'post_type'    => 'post',
                    'post_title'   => $art[0],
                    'post_excerpt' => $art[1],
                    'post_content' => '<p>' . esc_html( $art[1] ) . '</p><p>انتخاب ابزار مناسب در ابتدای پروژه، هزینه توسعه و نگهداری آینده را به شکل چشمگیری کاهش می‌دهد.</p>',
                    'post_status'  => 'publish',
                ) );
                if ( $aid && ! is_wp_error( $aid ) ) {
                    update_post_meta( $aid, '_temsah_is_demo', 1 );
                }
            }
        }

        // Seed 1 sample test coupon (TEMSAH20 -> 20% off)
        $t_c = Temsah_DB::table( 'coupons' );
        if ( ! $wpdb->get_var( "SELECT id FROM {$t_c} WHERE code = 'TEMSAH20'" ) ) {
            $wpdb->insert(
                $t_c,
                array(
                    'code'         => 'TEMSAH20',
                    'type'         => 'percent',
                    'amount'       => 20,
                    'min_order'    => 100000,
                    'max_discount' => 300000,
                    'usage_limit'  => 100,
                    'is_active'    => 1,
                    'created_at'   => $now,
                )
            );
        }

        $settings = get_option( 'temsah_settings', array() );
        $settings['environment_mode'] = 'development';
        update_option( 'temsah_settings', $settings );

        return array( 'products' => $created_p, 'vendors' => count( $vendor_ids ) );
    }

    public static function purge_demo_data(): array {
        global $wpdb;
        $demo_posts = get_posts( array(
            'post_type'   => array( 'temsah_product', 'post' ),
            'post_status' => 'any',
            'numberposts' => -1,
            'meta_key'    => '_temsah_is_demo',
            'meta_value'  => 1,
        ) );
        $deleted_posts = 0;
        foreach ( $demo_posts as $dp ) {
            wp_delete_post( $dp->ID, true );
            $deleted_posts++;
        }

        $t_p = Temsah_DB::table( 'products' );
        $demo_pids = $wpdb->get_col( "SELECT id FROM {$t_p} WHERE is_demo = 1" );
        if ( ! empty( $demo_pids ) ) {
            $in = implode( ',', array_map( 'absint', $demo_pids ) );
            $wpdb->query( "DELETE FROM " . Temsah_DB::table( 'product_files' ) . " WHERE product_id IN ({$in})" );
            $wpdb->query( "DELETE FROM " . Temsah_DB::table( 'product_versions' ) . " WHERE product_id IN ({$in})" );
            $wpdb->query( "DELETE FROM {$t_p} WHERE is_demo = 1" );
        }

        $t_v = Temsah_DB::table( 'vendors' );
        $wpdb->query( "DELETE FROM {$t_v} WHERE is_demo = 1" );

        require_once ABSPATH . 'wp-admin/includes/user.php';
        $demo_users = get_users( array( 'meta_key' => '_temsah_is_demo', 'meta_value' => 1 ) );
        foreach ( $demo_users as $du ) {
            wp_delete_user( $du->ID );
        }

        $wpdb->delete( Temsah_DB::table( 'coupons' ), array( 'code' => 'TEMSAH20' ) );

        $settings = get_option( 'temsah_settings', array() );
        $settings['environment_mode'] = 'production';
        update_option( 'temsah_settings', $settings );

        return array( 'deleted_posts' => $deleted_posts, 'deleted_users' => count( $demo_users ) );
    }
}
