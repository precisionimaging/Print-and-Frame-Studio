<?php
namespace PFS;

/**
 * Registers and versions JS/CSS for both the front-end Fabric canvas
 * and the React-powered admin screens.
 */
final class Assets {
    private static $instance;

    public static function instance() {
        return self::$instance ?: ( self::$instance = new self() );
    }

    private function __construct() {
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_frontend' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin' ] );
    }

    public function enqueue_frontend() {
        wp_register_script(
            'pfs-configurator',
            PFS_PLUGIN_URL . 'build/configurator.js',
            [ 'wp-element' ],
            PFS_VERSION,
            true
        );
        wp_register_style(
            'pfs-configurator',
            PFS_PLUGIN_URL . 'build/configurator.css',
            [],
            PFS_VERSION
        );
    }

    public function enqueue_admin() {
        wp_register_script(
            'pfs-admin',
            PFS_PLUGIN_URL . 'build/admin.js',
            [ 'wp-element', 'wp-components', 'wp-i18n' ],
            PFS_VERSION,
            true
        );
        wp_register_style(
            'pfs-admin',
            PFS_PLUGIN_URL . 'build/admin.css',
            [],
            PFS_VERSION
        );
    }
}
