<?php
namespace PFS;

use WP_REST_Request;
use WP_REST_Server;

/**
 * Generic REST endpoints that are NOT catalogue-related.
 */
final class Rest {

    private static $instance;

    public static function instance(): self {
        return self::$instance ?: ( self::$instance = new self() );
    }

    private function __construct() {
        add_action( 'rest_api_init', [ $this, 'register_routes' ] );
    }

    public function register_routes(): void {

        /* ------------------------------------------------------------------
         *  /pfs/v1/price   (kept – server-side price calculation)
         * ---------------------------------------------------------------- */
        register_rest_route(
            'pfs/v1',
            '/price',
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'permission_callback' => '__return_true',
                'callback'            => [ $this, 'calculate_price' ],
                'args' => [
                    'width_in'  => [ 'type' => 'number', 'required' => true ],
                    'height_in' => [ 'type' => 'number', 'required' => true ],
                ],
            ]
        );

        /* ⚠️  REMOVED: the old /assets stub that returned [].
         * Assets_Catalogue::instance() now owns that route. */
    }

    /** POST /price */
    public function calculate_price( WP_REST_Request $req ) {
        // …  (price logic unchanged)
    }
}
