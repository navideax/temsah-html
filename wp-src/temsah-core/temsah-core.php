<?php
/**
 * Plugin Name:       TEMSAH Core
 * Plugin URI:        https://temsah.me
 * Description:       هسته تجاری مارکت‌پلیس چندفروشندگی تمساح (محصولات دیجیتال، سبد خرید، سفارش‌ها، درگاه پرداخت، کمیسیون، لایسنس، دانلود امن، تیکت و پنل فروشندگان).
 * Version:           1.0.0
 * Requires at least: 6.4
 * Requires PHP:      8.1
 * Author:            Navid Seifi (نوید سیفی)
 * Author URI:        https://temsah.me
 * Text Domain:       temsah-core
 * Domain Path:       /languages
 * License:           GPLv2 or later
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'TEMSAH_CORE_VERSION', '1.0.0' );
define( 'TEMSAH_CORE_DB_VERSION', '1.0.0' );
define( 'TEMSAH_CORE_FILE', __FILE__ );
define( 'TEMSAH_CORE_PATH', plugin_dir_path( __FILE__ ) );
define( 'TEMSAH_CORE_URL', plugin_dir_url( __FILE__ ) );

require_once TEMSAH_CORE_PATH . 'includes/class-temsah-db.php';
require_once TEMSAH_CORE_PATH . 'includes/class-temsah-roles.php';
require_once TEMSAH_CORE_PATH . 'includes/class-temsah-security.php';
require_once TEMSAH_CORE_PATH . 'includes/class-temsah-cpt.php';
require_once TEMSAH_CORE_PATH . 'includes/class-temsah-router.php';
require_once TEMSAH_CORE_PATH . 'includes/class-temsah-cart.php';
require_once TEMSAH_CORE_PATH . 'includes/class-temsah-payments.php';
require_once TEMSAH_CORE_PATH . 'includes/class-temsah-vendor.php';
require_once TEMSAH_CORE_PATH . 'includes/class-temsah-buyer.php';
require_once TEMSAH_CORE_PATH . 'includes/class-temsah-rest.php';
require_once TEMSAH_CORE_PATH . 'includes/class-temsah-admin.php';
require_once TEMSAH_CORE_PATH . 'includes/class-temsah-seeder.php';

final class Temsah_Core {
    private static ?Temsah_Core $instance = null;

    public static function instance(): Temsah_Core {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'plugins_loaded', array( $this, 'on_plugins_loaded' ) );
        add_action( 'init', array( $this, 'on_init' ), 5 );
    }

    public function on_plugins_loaded(): void {
        load_plugin_textdomain( 'temsah-core', false, dirname( plugin_basename( TEMSAH_CORE_FILE ) ) . '/languages' );

        if ( get_option( 'temsah_core_db_version' ) !== TEMSAH_CORE_DB_VERSION ) {
            Temsah_DB::install_or_upgrade();
        }
    }

    public function on_init(): void {
        Temsah_Roles::register_runtime_filters();
        Temsah_CPT::init();
        Temsah_Router::init();
        Temsah_Security::init();
        Temsah_Cart::init();
        Temsah_Payments::init();
        Temsah_Vendor::init();
        Temsah_Buyer::init();
        Temsah_REST::init();
        if ( is_admin() ) {
            Temsah_Admin::init();
        }
    }

    public static function activate(): void {
        Temsah_DB::install_or_upgrade();
        Temsah_Roles::add_roles_and_caps();
        Temsah_CPT::register_post_types();
        Temsah_CPT::register_taxonomies();
        Temsah_CPT::ensure_default_terms();
        Temsah_Router::register_rewrites();
        flush_rewrite_rules();
    }

    public static function deactivate(): void {
        flush_rewrite_rules();
    }
}

register_activation_hook( __FILE__, array( 'Temsah_Core', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Temsah_Core', 'deactivate' ) );

Temsah_Core::instance();
