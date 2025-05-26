<?php
/**
 * Plugin Name:  Print & Frame Studio
 * Plugin URI:   https://precisionimaging.ca/
 * Description:  Custom framing configurator powered by Fabric.js.
 * Version:      0.1.0
 * Author:       Precision Imaging
 * Author URI:   https://precisionimaging.ca/
 * License:      GPL-2.0+
 * License URI:  https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:  print-and-frame-studio
 */

namespace PFS; // ────────────────────────────────────────────────────────────────

if ( ! defined( 'ABSPATH' ) ) {
    exit; // nothing to see here
}

/* ────────────────────────────────────────────────────────────────────────────
 *  Constants
 * ───────────────────────────────────────────────────────────────────────── */
define( 'PFS_VERSION',      '0.1.0' );
define( 'PFS_PLUGIN_FILE',  __FILE__ );
define( 'PFS_PLUGIN_DIR',   plugin_dir_path(  __FILE__ ) );
define( 'PFS_PLUGIN_URL',   plugin_dir_url(   __FILE__ ) );

/* ────────────────────────────────────────────────────────────────────────────
 *  PSR-4 autoloader for everything in /includes/
 * ───────────────────────────────────────────────────────────────────────── */
spl_autoload_register( static function ( $class ) {

    $prefix = __NAMESPACE__ . '\\';
    $len    = strlen( $prefix );

    // ignore classes outside our namespace
    if ( strncmp( $class, $prefix, $len ) !== 0 ) {
        return;
    }

    $relative = substr( $class, $len );               // e.g. Assets
    $path     = PFS_PLUGIN_DIR . 'includes/' .
                str_replace( '\\', '/', $relative ) .
                '.php';                               // /includes/Assets.php

    if ( file_exists( $path ) ) {
        require $path;
    }
} );

/* ────────────────────────────────────────────────────────────────────────────
 *  Plugin orchestrator – boots the singletons
 * ───────────────────────────────────────────────────────────────────────── */
final class Plugin {

    /** @var self */
    private static $instance;

    public static function instance() : self {
        return self::$instance ?: ( self::$instance = new self() );
    }

    private function __construct() {
        Assets::instance();          // registers script & style handles
        Rest::instance();            // REST endpoints
        WC_Integration::instance();  // hooks WooCommerce (soft-fails if WC absent)
    }

    // stop cloning / unserialising
    private function __clone() {}
    public function __wakeup() { /* phpcs:ignore */ }
}

/* ────────────────────────────────────────────────────────────────────────────
 *  Activation / deactivation hooks
 * ───────────────────────────────────────────────────────────────────────── */
\register_activation_hook(   __FILE__, [ Setup::class, 'activate'   ] );
\register_deactivation_hook( __FILE__, [ Setup::class, 'deactivate' ] );

/* ────────────────────────────────────────────────────────────────────────────
 *  Boot the whole show
 * ───────────────────────────────────────────────────────────────────────── */
Plugin::instance();

/* ────────────────────────────────────────────────────────────────────────────
 *  Shortcode  [pfs_configurator]
 *  Drops an 800 × 600 canvas into the page and enqueues the Fabric bundle.
 * ───────────────────────────────────────────────────────────────────────── */
\add_shortcode( 'pfs_configurator', static function () {

    // These handles are registered inside Assets::enqueue_frontend()
    \wp_enqueue_style(  'pfs-configurator' );
    \wp_enqueue_script( 'pfs-configurator' );

    ob_start(); ?>
        <canvas id="pfs-canvas"
                width="800"
                height="600"
                style="max-width:100%; border:1px solid #ccc;"></canvas>
    <?php
    return ob_get_clean();
} );
