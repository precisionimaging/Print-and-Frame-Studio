<?php
namespace PFS;

use WP_REST_Request;
use WP_REST_Server;

/**
 * Registers all REST endpoints used by the configurator
 *  – /pfs/v1/assets : catalogue of frames & mats
 *  – /pfs/v1/price  : canonical price calculation
 */
final class Rest {

    private static $instance;

    public static function instance() : self {
        return self::$instance ?: ( self::$instance = new self() );
    }

    private function __construct() {
        add_action( 'rest_api_init', [ $this, 'register_routes' ] );
    }

    public function register_routes() : void {

        register_rest_route(
            'pfs/v1',
            '/assets',
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_assets' ],
                'permission_callback' => '__return_true',
            ]
        );

        register_rest_route(
            'pfs/v1',
            '/price',
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'calculate_price' ],
                'permission_callback' => '__return_true',
                'args'                => [
                    'width_in'  => [ 'type' => 'number', 'required' => true ],
                    'height_in' => [ 'type' => 'number', 'required' => true ],
                ],
            ]
        );
    }

    /** GET /assets */
    public function get_assets( WP_REST_Request $request ) {
        // v-0.1 stub: empty catalogue
        // Next sprint: scan wp-content/uploads/pfs/frames/* for PNG sets, same for mats
        return rest_ensure_response( [
            'frames' => [],
            'mats'   => [],
        ] );
    }

    /** POST /price */
    public function calculate_price( WP_REST_Request $request ) {
        $p   = $request->get_json_params();
        $ui  = ( $p['width_in'] + $p['height_in'] ) * 2;          // united inches
        $ft2 = ( $p['width_in'] * $p['height_in'] ) / 144;        // square feet

        $print  = ( $p['print_base']       ?? 0 ) * $ft2;
        $frame  = ( $p['frame_rate_ui']    ?? 0 ) * $ui;
        $mat    = ( $p['mat_rate_ui']      ?? 0 ) * $ui  * ( $p['mat_layers']   ?? 1 );
        $glass  = ( $p['glass_rate_ft2']   ?? 0 ) * $ft2 * ( $p['glass_factor'] ?? 1 );
        $extras =   array_sum( $p['extras'] ?? [] );

        $subtotal = $print + $frame + $mat + $glass + $extras;
        $labour   = max( 0.10 * $subtotal, 10 );
        $total    = round( $subtotal + $labour, 2 );

        return rest_ensure_response( [
            'breakdown' => compact( 'print', 'frame', 'mat', 'glass', 'extras', 'labour' ),
            'total'     => $total,
        ] );
    }
}
