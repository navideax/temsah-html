<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Temsah_Roles {
    public static function buyer_caps(): array {
        return array(
            'read'                     => true,
            'temsah_buy_products'      => true,
            'temsah_manage_own_tickets'=> true,
            'temsah_download_purchased'=> true,
        );
    }

    public static function vendor_caps(): array {
        return array_merge( self::buyer_caps(), array(
            'temsah_access_vendor_portal' => true,
            'temsah_submit_products'      => true,
            'temsah_manage_own_products'  => true,
            'temsah_view_own_earnings'    => true,
            'temsah_request_withdrawal'   => true,
            'temsah_reply_own_tickets'    => true,
        ) );
    }

    public static function admin_caps(): array {
        return array(
            'temsah_manage_marketplace'  => true,
            'temsah_approve_vendors'     => true,
            'temsah_approve_products'    => true,
            'temsah_manage_orders'       => true,
            'temsah_manage_withdrawals'  => true,
            'temsah_manage_coupons'      => true,
            'temsah_view_logs'           => true,
        );
    }

    public static function add_roles_and_caps(): void {
        add_role( 'temsah_buyer', 'خریدار تمساح', self::buyer_caps() );
        add_role( 'temsah_vendor', 'فروشنده تمساح', self::vendor_caps() );

        $sub = get_role( 'subscriber' );
        if ( $sub ) {
            foreach ( self::buyer_caps() as $cap => $grant ) {
                $sub->add_cap( $cap, $grant );
            }
        }

        $vendor_role = get_role( 'temsah_vendor' );
        if ( $vendor_role ) {
            foreach ( self::vendor_caps() as $cap => $grant ) {
                $vendor_role->add_cap( $cap, $grant );
            }
        }

        $admin = get_role( 'administrator' );
        if ( $admin ) {
            foreach ( array_merge( self::vendor_caps(), self::admin_caps() ) as $cap => $grant ) {
                $admin->add_cap( $cap, $grant );
            }
        }
    }

    public static function register_runtime_filters(): void {
        // Hide WP admin bar for standard buyers and vendors on frontend so V31 layout stays pixel-accurate
        add_filter( 'show_admin_bar', function ( bool $show ): bool {
            if ( ! current_user_can( 'manage_options' ) ) {
                return false;
            }
            return $show;
        } );
    }
}
