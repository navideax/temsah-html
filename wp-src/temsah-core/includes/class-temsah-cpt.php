<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Temsah_CPT {
    public static function init(): void {
        self::register_post_types();
        self::register_taxonomies();
    }

    public static function register_post_types(): void {
        register_post_type( 'temsah_product', array(
            'labels' => array(
                'name'          => 'محصولات تمساح',
                'singular_name' => 'محصول',
                'add_new_item'  => 'افزودن محصول جدید',
                'edit_item'     => 'ویرایش محصول',
                'view_item'     => 'مشاهده محصول',
                'search_items'  => 'جستجوی محصولات',
                'not_found'     => 'محصولی یافت نشد.',
            ),
            'public'       => true,
            'has_archive'  => 'shop',
            'rewrite'      => array( 'slug' => 'product', 'with_front' => false ),
            'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail', 'author' ),
            'show_in_rest' => true,
            'show_in_menu' => false,
        ) );
    }

    public static function register_taxonomies(): void {
        register_taxonomy( 'temsah_product_cat', array( 'temsah_product' ), array(
            'labels' => array(
                'name'          => 'دسته‌بندی محصولات',
                'singular_name' => 'دسته‌بندی محصول',
            ),
            'hierarchical' => true,
            'public'       => true,
            'rewrite'      => array( 'slug' => 'product-category', 'with_front' => false ),
            'show_in_rest' => true,
        ) );

        register_taxonomy( 'temsah_product_tag', array( 'temsah_product' ), array(
            'labels' => array(
                'name'          => 'برچسب‌های محصول',
                'singular_name' => 'برچسب محصول',
            ),
            'hierarchical' => false,
            'public'       => true,
            'rewrite'      => array( 'slug' => 'product-tag', 'with_front' => false ),
            'show_in_rest' => true,
        ) );
    }

    public static function ensure_default_terms(): void {
        $cats = array(
            'wordpress' => 'قالب وردپرس',
            'plugin'    => 'افزونه وردپرس',
            'html'      => 'قالب HTML',
            'script'    => 'اسکریپت PHP',
            'dashboard' => 'پنل مدیریت',
            'app'       => 'سورس اپلیکیشن',
            'ui'        => 'فایل رابط کاربری',
        );
        foreach ( $cats as $slug => $name ) {
            if ( ! term_exists( $slug, 'temsah_product_cat' ) ) {
                wp_insert_term( $name, 'temsah_product_cat', array( 'slug' => $slug ) );
            }
        }
    }

    public static function get_product_by_post( int $post_id ): ?object {
        global $wpdb;
        $t_p = Temsah_DB::table( 'products' );
        $t_v = Temsah_DB::table( 'vendors' );
        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT p.*, wp.post_title, wp.post_name, wp.post_excerpt, wp.post_content, wp.post_status, wp.post_date,
                        v.store_name, v.store_slug, v.user_id AS vendor_user_id, v.specialty AS vendor_specialty, v.badges_json AS vendor_badges_json, v.total_sales AS vendor_total_sales
                 FROM {$t_p} p
                 INNER JOIN {$wpdb->posts} wp ON wp.ID = p.post_id
                 LEFT JOIN {$t_v} v ON v.id = p.vendor_id
                 WHERE p.post_id = %d LIMIT 1",
                $post_id
            )
        ) ?: null;
    }

    public static function get_product_by_id( int $product_id ): ?object {
        global $wpdb;
        $t_p = Temsah_DB::table( 'products' );
        $t_v = Temsah_DB::table( 'vendors' );
        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT p.*, wp.post_title, wp.post_name, wp.post_excerpt, wp.post_content, wp.post_status, wp.post_date,
                        v.store_name, v.store_slug, v.user_id AS vendor_user_id, v.specialty AS vendor_specialty, v.badges_json AS vendor_badges_json, v.total_sales AS vendor_total_sales
                 FROM {$t_p} p
                 INNER JOIN {$wpdb->posts} wp ON wp.ID = p.post_id
                 LEFT JOIN {$t_v} v ON v.id = p.vendor_id
                 WHERE p.id = %d LIMIT 1",
                $product_id
            )
        ) ?: null;
    }

    public static function query_products( array $args = array() ): array {
        global $wpdb;
        $t_p = Temsah_DB::table( 'products' );
        $t_v = Temsah_DB::table( 'vendors' );

        $where  = array( "p.status = 'published'", "wp.post_status = 'publish'" );
        $params = array();

        if ( ! empty( $args['vendor_id'] ) ) {
            $where[]  = 'p.vendor_id = %d';
            $params[] = (int) $args['vendor_id'];
        }
        if ( ! empty( $args['category'] ) && 'all' !== $args['category'] ) {
            $term = get_term_by( 'slug', sanitize_title( $args['category'] ), 'temsah_product_cat' );
            if ( ! $term ) {
                $term = get_term_by( 'name', sanitize_text_field( $args['category'] ), 'temsah_product_cat' );
            }
            if ( $term ) {
                $obj_ids = get_objects_in_term( $term->term_id, 'temsah_product_cat' );
                if ( empty( $obj_ids ) ) {
                    return array();
                }
                $in      = implode( ',', array_map( 'absint', $obj_ids ) );
                $where[] = "p.post_id IN ({$in})";
            } else {
                return array();
            }
        }
        if ( ! empty( $args['free_only'] ) ) {
            $where[] = 'COALESCE(p.sale_price, p.price) = 0';
        }
        if ( ! empty( $args['sale_only'] ) ) {
            $where[] = 'p.sale_price IS NOT NULL AND p.sale_price < p.price';
        }
        if ( ! empty( $args['search'] ) ) {
            $like     = '%' . $wpdb->esc_like( sanitize_text_field( $args['search'] ) ) . '%';
            $where[]  = '(wp.post_title LIKE %s OR wp.post_excerpt LIKE %s OR v.store_name LIKE %s)';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }
        if ( ! empty( $args['post_ids'] ) && is_array( $args['post_ids'] ) ) {
            $in      = implode( ',', array_map( 'absint', $args['post_ids'] ) );
            $where[] = $in ? "p.post_id IN ({$in})" : '1=0';
        }

        $sort = $args['sort'] ?? 'popular';
        $order_by = match ( $sort ) {
            'new'       => 'p.id DESC',
            'cheap'     => 'COALESCE(p.sale_price, p.price) ASC, p.id DESC',
            'expensive' => 'COALESCE(p.sale_price, p.price) DESC, p.id DESC',
            'discount'  => '(CASE WHEN p.sale_price IS NOT NULL AND p.sale_price < p.price THEN 1 ELSE 0 END) DESC, p.sales_count DESC',
            default     => 'p.sales_count DESC, p.id DESC',
        };

        $limit  = isset( $args['limit'] ) ? max( 1, (int) $args['limit'] ) : 24;
        $offset = isset( $args['offset'] ) ? max( 0, (int) $args['offset'] ) : 0;

        $sql = "SELECT p.*, wp.post_title, wp.post_name, wp.post_excerpt, wp.post_content, wp.post_date,
                       v.store_name, v.store_slug, v.user_id AS vendor_user_id, v.specialty AS vendor_specialty, v.badges_json AS vendor_badges_json
                FROM {$t_p} p
                INNER JOIN {$wpdb->posts} wp ON wp.ID = p.post_id
                LEFT JOIN {$t_v} v ON v.id = p.vendor_id
                WHERE " . implode( ' AND ', $where ) . "
                ORDER BY {$order_by}
                LIMIT %d OFFSET %d";
        $params[] = $limit;
        $params[] = $offset;

        return $wpdb->get_results( $wpdb->prepare( $sql, ...$params ) ) ?: array();
    }

    public static function get_product_category_name( int $post_id ): string {
        $terms = get_the_terms( $post_id, 'temsah_product_cat' );
        if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
            return $terms[0]->name;
        }
        return 'محصول دیجیتال';
    }

    public static function get_product_category_slug( int $post_id ): string {
        $terms = get_the_terms( $post_id, 'temsah_product_cat' );
        if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
            return $terms[0]->slug;
        }
        return '';
    }

    public static function effective_price( object $product ): int {
        if ( null !== $product->sale_price && '' !== $product->sale_price && (int) $product->sale_price < (int) $product->price ) {
            return (int) $product->sale_price;
        }
        return (int) $product->price;
    }

    public static function discount_percent( object $product ): int {
        $orig = (int) $product->price;
        $sale = self::effective_price( $product );
        if ( $orig <= 0 || $sale >= $orig ) {
            return 0;
        }
        return (int) round( ( ( $orig - $sale ) / $orig ) * 100 );
    }

    public static function get_weekly_developer(): ?object {
        global $wpdb;
        $settings = get_option( 'temsah_settings', array() );
        $t_v      = Temsah_DB::table( 'vendors' );
        $mode     = $settings['weekly_developer_mode'] ?? 'auto';
        $manual   = (int) ( $settings['weekly_developer_id'] ?? 0 );

        if ( 'manual' === $mode && $manual > 0 ) {
            return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$t_v} WHERE id = %d AND status = 'approved' LIMIT 1", $manual ) ) ?: null;
        }
        // Auto: select approved vendor with highest real sales (> 0) or marked is_featured_weekly
        $featured = $wpdb->get_row( "SELECT * FROM {$t_v} WHERE status = 'approved' AND is_featured_weekly = 1 LIMIT 1" );
        if ( $featured ) {
            return $featured;
        }
        return $wpdb->get_row( "SELECT * FROM {$t_v} WHERE status = 'approved' AND total_sales > 0 ORDER BY total_sales DESC, id ASC LIMIT 1" ) ?: null;
    }
}

/* Global formatting helpers safe for theme templates */
function temsah_fa_digits( $value ): string {
    $en = array( '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', ',' );
    $fa = array( '۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹', '٬' );
    return str_replace( $en, $fa, (string) $value );
}

function temsah_format_number( $num ): string {
    return temsah_fa_digits( number_format( (float) $num, 0, '.', ',' ) );
}

function temsah_format_toman( $num, bool $with_unit = true ): string {
    $val = (int) $num;
    if ( 0 === $val ) {
        return $with_unit ? 'رایگان' : '۰';
    }
    return temsah_format_number( $val ) . ( $with_unit ? ' تومان' : '' );
}

function temsah_format_date( ?string $mysql_date ): string {
    if ( empty( $mysql_date ) ) {
        return '—';
    }
    $ts = strtotime( $mysql_date );
    if ( ! $ts ) {
        return '—';
    }
    return temsah_fa_digits( date_i18n( 'Y/m/d', $ts ) );
}
