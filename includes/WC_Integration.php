<?php
namespace PFS;

/**
 * Hooks that connect the configurator to WooCommerce
 * – injects JSON config & authoritative price
 * – post-checkout file shuffling
 */
final class WC_Integration {

    private static $instance;

    public static function instance() : self {
        return self::$instance ?: ( self::$instance = new self() );
    }

    private function __construct() {

        if ( ! class_exists( 'WooCommerce' ) ) {
            return;   // fail-soft for non-Woo sites
        }

        add_filter( 'woocommerce_add_cart_item_data',       [ $this, 'inject_config' ],       10, 3 );
        add_filter( 'woocommerce_get_cart_item_from_session',[ $this, 'load_config' ],        10, 2 );
        add_filter( 'woocommerce_cart_item_price',          [ $this, 'display_price' ],       10, 3 );
        add_action( 'woocommerce_checkout_order_processed', [ $this, 'move_original_upload' ],10, 3 );
    }

    /** Add our JSON blob when “Add to cart” fires. */
    public function inject_config( $cart_item_data, $product_id, $variation_id ) {

        if ( empty( $_POST['pfs_config'] ) ) {
            return $cart_item_data;
        }

        $cart_item_data['pfs_config'] = json_decode( wp_unslash( $_POST['pfs_config'] ), true );
        $cart_item_data['unique_key'] = md5( microtime( true ) . rand() );

        return $cart_item_data;
    }

    public function load_config( $cart_item, $item_key ) {
        return $cart_item; // placeholder for validation in a later sprint
    }

    public function display_price( $price_html, $cart_item, $cart_item_key ) {

        if ( isset( $cart_item['pfs_config']['total'] ) ) {
            $price_html = wc_price( $cart_item['pfs_config']['total'] );
        }
        return $price_html;
    }

    /** After checkout: move original upload into a per-order folder (TODO). */
    public function move_original_upload( $order_id, $posted_data, $order ) {
        // ./wp-content/uploads/pfs-originals/tmpXYZ.jpg → ./print-files/{order-id}/original.jpg
    }
}
